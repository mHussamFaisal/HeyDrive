<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load config
$config = include('/home/versjspr/taxisdispatch.com/config.php');
echo "APP_KEY: " . substr($config['APP_KEY'], 0, 30) . "...
";
echo "DB: " . $config['DB_DATABASE'] . "
";

// Test DB connection
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=" . $config['DB_DATABASE'] . ";charset=utf8mb4",
        $config['DB_USERNAME'], $config['DB_PASSWORD']);
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "DB OK - " . count($tables) . " tables
";
    
    // Check users
    $users = $pdo->query("SELECT id, email, name FROM eto_users LIMIT 5")->fetchAll();
    foreach ($users as $u) {
        echo "User: " . $u['email'] . " (ID:" . $u['id'] . ")
";
    }
} catch(Exception $e) {
    echo "DB Error: " . $e->getMessage() . "
";
}
?>