<?php
// Safe migration: drop column preco_aluguel from table filme
// Usage (browser): http://localhost/videolocadora/db/migrate_drop_preco_from_filme.php?confirm=1
// Usage (CLI): php migrate_drop_preco_from_filme.php

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
    echo "This will ALTER the 'filme' table to DROP column preco_aluguel.\n";
    echo "To proceed run with ?confirm=1 in the browser (localhost) or run via CLI: php migrate_drop_preco_from_filme.php\n";
    exit(1);
}

// Load config (expects same config used by app to return mysqli connection)
$cfgPath = __DIR__ . '/../config/config.php';
if (!file_exists($cfgPath)) {
    echo "Config file not found at $cfgPath\n";
    exit(1);
}

$conn = require_once $cfgPath;
if (!($conn instanceof mysqli)) {
    echo "Config did not return a mysqli connection.\n";
    exit(1);
}

// Check column exists
$res = $conn->query("SHOW COLUMNS FROM filme LIKE 'preco_aluguel'");
if ($res && $res->num_rows > 0) {
    echo "Dropping column 'preco_aluguel' from table 'filme'...\n";
    if ($conn->query("ALTER TABLE filme DROP COLUMN preco_aluguel")) {
        echo "SUCCESS: Column 'preco_aluguel' dropped from 'filme'.\n";
    } else {
        echo "ERROR running ALTER TABLE: " . $conn->error . "\n";
        exit(1);
    }
} else {
    echo "Column 'preco_aluguel' does not exist on table 'filme'. Nothing to do.\n";
}

// Show a few rows to confirm
$r = $conn->query('SELECT id_filme, ident_titulo FROM filme ORDER BY id_filme LIMIT 5');
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo $row['id_filme'] . "\t" . ($row['ident_titulo'] ?? '') . "\n";
    }
}

echo "Done.\n";

?>
