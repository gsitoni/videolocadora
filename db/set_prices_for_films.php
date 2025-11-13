<?php
// Script utility to ensure each film has a preco_aluguel set.
// Behavior:
// - If table `filme` has column `preco_aluguel`, update rows where preco_aluguel IS NULL OR preco_aluguel = 0.00
// - If column does not exist, create a helper table `preco_filme (id_filme INT PRIMARY KEY, preco DECIMAL(10,2))` and insert defaults for films without an entry
// Safety: only runs from localhost (browser) or CLI. Requires ?confirm=1 in browser.

if (php_sapi_name() !== 'cli' && ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1' && ($_SERVER['REMOTE_ADDR'] ?? '') !== '::1') {
    echo "This script can only run from localhost for safety.\n";
    exit(1);
}

$confirm = false;
if (php_sapi_name() === 'cli') {
    $confirm = true;
} else {
    $confirm = isset($_GET['confirm']) && $_GET['confirm'] == '1';
}

if (!$confirm) {
    echo "This will populate prices for films. Run with ?confirm=1 in the browser (localhost) or run via CLI.\n";
    exit(1);
}

$cfgPath = __DIR__ . '/../config/config.php';
if (!file_exists($cfgPath)) { echo "Config file not found\n"; exit(1); }
$conn = require_once $cfgPath;
if (!($conn instanceof mysqli)) { echo "Config did not return mysqli connection\n"; exit(1); }

// Helper to pick a default price based on simple heuristics
function default_price_for_title($title) {
    $t = strtolower($title);
    // Newer releases or words like 'lanc' get higher price
    if (strpos($t, 'lanc') !== false || strpos($t, 'novo') !== false) return 9.90;
    // Classics cheaper
    if (strpos($t, 'cláss') !== false || strpos($t, 'class') !== false) return 3.90;
    // Default mid-tier
    return 5.90;
}

// Check if coluna exists
$colCheck = $conn->query("SHOW COLUMNS FROM filme LIKE 'preco_aluguel'");
if ($colCheck && $colCheck->num_rows > 0) {
    echo "Table 'filme' has column preco_aluguel — updating missing prices...\n";
    $r = $conn->query("SELECT id_filme, ident_titulo, preco_aluguel FROM filme ORDER BY id_filme");
    $updates = 0;
    while ($row = $r->fetch_assoc()) {
        $id = (int)$row['id_filme'];
        $cur = isset($row['preco_aluguel']) ? floatval($row['preco_aluguel']) : 0.0;
        if ($cur <= 0.0) {
            $def = default_price_for_title($row['ident_titulo'] ?? '');
            $stmt = $conn->prepare('UPDATE filme SET preco_aluguel = ? WHERE id_filme = ?');
            $stmt->bind_param('di', $def, $id);
            if ($stmt->execute()) $updates++;
            $stmt->close();
        }
    }
    echo "Updated $updates rows.\n";
} else {
    echo "Column preco_aluguel not found on filme — creating helper table preco_filme and inserting defaults...\n";
    // create helper table if not exists
    $conn->query('CREATE TABLE IF NOT EXISTS preco_filme (id_filme INT PRIMARY KEY, preco DECIMAL(10,2) NOT NULL)');

    $r = $conn->query('SELECT id_filme, ident_titulo FROM filme ORDER BY id_filme');
    $inserts = 0;
    while ($row = $r->fetch_assoc()) {
        $id = (int)$row['id_filme'];
        // check if exists
        $exists = $conn->prepare('SELECT preco FROM preco_filme WHERE id_filme = ? LIMIT 1');
        $exists->bind_param('i', $id);
        $exists->execute();
        $res = $exists->get_result();
        $has = $res && $res->num_rows > 0;
        $exists->close();
        if (!$has) {
            $def = default_price_for_title($row['ident_titulo'] ?? '');
            $ins = $conn->prepare('INSERT INTO preco_filme (id_filme, preco) VALUES (?, ?)');
            $ins->bind_param('id', $id, $def);
            if ($ins->execute()) $inserts++;
            $ins->close();
        }
    }
    echo "Inserted $inserts price rows into preco_filme.\n";
}

echo "Done.\n";

?>
