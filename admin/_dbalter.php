<?php
require_once dirname(__DIR__) . '/includes/config.php';
$pdo = db_connect();
$results = [];
$queries = [
    "ALTER TABLE td_drivers ADD COLUMN notes text DEFAULT NULL",
    "ALTER TABLE td_drivers ADD COLUMN license_expiry date DEFAULT NULL",
    "ALTER TABLE td_drivers ADD COLUMN current_lat decimal(10,7) DEFAULT NULL",
    "ALTER TABLE td_drivers ADD COLUMN current_lng decimal(10,7) DEFAULT NULL",
];
foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        $results[] = "OK: " . $q;
    } catch (Exception $e) {
        $results[] = "ERR: " . $e->getMessage();
    }
}
echo json_encode($results);
?>