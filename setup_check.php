<?php
// Database setup script for taxisdispatch.com
$config = include('/home/versjspr/taxisdispatch.com/config.php');
$host = $config['DB_HOST'];
$db   = $config['DB_DATABASE'];
$user = $config['DB_USERNAME'];
$pass = $config['DB_PASSWORD'];
$prefix = $config['DB_PREFIX'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if already installed
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    echo json_encode(['status' => 'connected', 'tables' => count($tables), 'db' => $db]);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>