<?php
require_once dirname(__DIR__) . '/includes/config.php';
$pdo = db_connect();
$cols = $pdo->query("DESCRIBE td_vehicles")->fetchAll(PDO::FETCH_ASSOC);
$result = ['columns' => $cols];

// Check uploads directory
$uploads = dirname(__DIR__) . '/uploads/vehicles';
$result['uploads_dir_exists'] = is_dir($uploads);
$result['uploads_writable'] = is_dir($uploads) ? is_writable($uploads) : false;
$result['parent_writable'] = is_writable(dirname(__DIR__) . '/uploads');
echo json_encode($result);
?>