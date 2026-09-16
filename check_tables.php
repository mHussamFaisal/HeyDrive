<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=versjspr_taxisdispatch;charset=utf8mb4",
    'versjspr_taxisdispatch', 'TaxiDispatch2024!');
$tables = $pdo->query("SHOW TABLES LIKE '%subscri%'")->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $tables) . "
";

// Get subscriptions table name  
$all = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$sub_tables = array_filter($all, fn($t) => strpos($t, 'subscri') !== false || strpos($t, 'licens') !== false);
echo "Sub/License tables: " . implode(", ", $sub_tables) . "
";
?>