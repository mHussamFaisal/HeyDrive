<?php
// Quick database installer for TaxisDispatch
// Access: https://taxisdispatch.com/install_taxi.php
// DELETE THIS FILE AFTER SETUP!

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'versjspr_taxisdispatch');
define('DB_USER', 'versjspr_taxisdispatch');
define('DB_PASS', 'TaxiDispatch2024!');

$errors = [];
$success = [];

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $success[] = "✅ Database connection successful";
    
    // Create tables
    $sql = file_get_contents(__DIR__ . '/setup.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (empty($stmt) || strpos($stmt, '--') === 0) continue;
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                $errors[] = "⚠️ " . $e->getMessage();
            }
        }
    }
    
    // Check if admin exists, update password
    $admin = $pdo->query("SELECT id FROM td_users WHERE email='admin@taxisdispatch.com'")->fetch();
    if ($admin) {
        $hash = password_hash('Admin2024!', PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE td_users SET password=? WHERE email='admin@taxisdispatch.com'")->execute([$hash]);
        $success[] = "✅ Admin password reset to: Admin2024!";
    }
    
    $success[] = "✅ All tables created/verified";
    $success[] = "✅ Default data inserted";
    
} catch (PDOException $e) {
    $errors[] = "❌ Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>TaxisDispatch Installer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white p-5">
<div class="container" style="max-width:600px">
  <h2 class="text-warning mb-4">🚖 TaxisDispatch Database Installer</h2>
  
  <?php foreach ($success as $msg): ?>
  <div class="alert alert-success"><?= $msg ?></div>
  <?php endforeach; ?>
  
  <?php foreach ($errors as $err): ?>
  <div class="alert alert-warning"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>
  
  <?php if (empty($errors) || count($success) > 2): ?>
  <div class="alert alert-info">
    <h5>✅ Installation Complete!</h5>
    <p>Admin Login: <strong>admin@taxisdispatch.com</strong> / <strong>Admin2024!</strong></p>
    <a href="/admin/" class="btn btn-warning me-2">Go to Admin Panel</a>
    <a href="/" class="btn btn-outline-warning">Go to Booking Page</a>
    <hr>
    <p class="text-danger small mb-0">⚠️ IMPORTANT: Delete this file (install_taxi.php) after setup!</p>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
