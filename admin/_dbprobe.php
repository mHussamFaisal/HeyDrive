<?php
require_once dirname(__DIR__) . '/includes/config.php';
$pdo = db_connect();
$cols = $pdo->query("DESCRIBE td_drivers")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols);
?>