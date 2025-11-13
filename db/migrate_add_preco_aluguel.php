<?php
// Migration helper (development only)
// Usage (browser): http://localhost/videolocadora/db/migrate_add_preco_aluguel.php?confirm=1
// Usage (CLI): php migrate_add_preco_aluguel.php

// Safety: only run from localhost or CLI, and requires explicit confirm=1 when run via web.
if (php_sapi_name() !== 'cli') {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($remote, ['127.0.0.1', '::1'])) {
        http_response_code(403);
        echo "Forbidden: migration can only be run from localhost.\n";
        exit;
    }
    if (empty($_GET['confirm']) || $_GET['confirm'] !== '1') {
        echo "This will ALTER the 'filme' table to add column preco_aluguel.\n";
        echo "To run, reload with ?confirm=1\n";
        exit;
    }
}

// include DB config
$conn = include __DIR__ . '/../config/config.php';
if (!($conn instanceof mysqli)) {
    echo "Failed to obtain mysqli connection.\n";
    var_export($conn);
    exit(1);
}

// Check if column exists
$res = $conn->query("SHOW COLUMNS FROM filme LIKE 'preco_aluguel'");
if ($res && $res->num_rows > 0) {
    echo "Column 'preco_aluguel' already exists on table 'filme'. No action taken.\n";
    exit(0);
}

// Perform ALTER
$sql = "ALTER TABLE filme ADD COLUMN preco_aluguel DECIMAL(10,2) NOT NULL DEFAULT 0.00";
if ($conn->query($sql)) {
    echo "SUCCESS: Column 'preco_aluguel' added to 'filme'.\n";
    // Optional: show a sample
    $r = $conn->query('SELECT id_filme, ident_titulo, preco_aluguel FROM filme ORDER BY id_filme LIMIT 5');
    if ($r) {
        echo "Sample rows:\n";
        while ($row = $r->fetch_assoc()) {
            echo $row['id_filme'] . "\t" . ($row['ident_titulo'] ?? '') . "\t" . $row['preco_aluguel'] . "\n";
        }
    }
    exit(0);
} else {
    echo "FAILED to alter table: " . $conn->error . "\n";
    exit(1);
}

?>
