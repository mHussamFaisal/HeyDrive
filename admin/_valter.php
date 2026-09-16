<?php
require_once dirname(__DIR__) . '/includes/config.php';
$pdo = db_connect();
$results = [];
$queries = [
    "ALTER TABLE td_vehicles ADD COLUMN notes text DEFAULT NULL",
    "ALTER TABLE td_vehicles ADD COLUMN image varchar(255) DEFAULT NULL",
];
foreach ($queries as $q) {
    try { $pdo->exec($q); $results[] = "OK: $q"; }
    catch (Exception $e) { $results[] = "ERR: " . $e->getMessage(); }
}
echo json_encode($results);
?>