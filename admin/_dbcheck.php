<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'versjspr_taxisdispatch');
define('DB_USER', 'versjspr_taxisdispatch');
define('DB_PASS', 'TaxiDispatch2024!');

$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Show structure
echo "=== td_payment_gateways structure ===
";
foreach($pdo->query("DESCRIBE td_payment_gateways")->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  " . $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . "
";
}

// Show content
echo "
=== Existing rows ===
";
$rows = $pdo->query("SELECT * FROM td_payment_gateways")->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($rows) . "
";
foreach($rows as $r) {
    echo json_encode($r) . "
";
}
