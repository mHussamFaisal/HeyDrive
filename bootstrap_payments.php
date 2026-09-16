<?php
// Creates all payment system files and directories
$base = __DIR__;
$dirs = ['payments', 'payments/gateways'];
foreach($dirs as $d) {
    if(!is_dir("$base/$d")) mkdir("$base/$d", 0755, true);
}

$files = [];

// ─── payments/payment_config.php ───
$files['payments/payment_config.php'] = <<<'PHPEOF'
<?php
if(!defined('DB_HOST')) require_once __DIR__.'/../includes/config.php';

function get_gateway_config($gateway) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM td_payment_gateways WHERE gateway=? AND enabled=1");
    $stmt->execute([$gateway]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;
    $row['config'] = json_decode($row['config'] ?? '{}', true);
    return $row;
}
function get_active_gateways() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM td_payment_gateways WHERE enabled=1 ORDER BY sort_order");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) $r['config'] = json_decode($r['config'] ?? '{}', true);
    return $rows;
}
function create_payment_record($booking_id, $booking_ref, $gateway, $amount, $currency='GBP') {
    global $pdo;
    $inv = 'INV-'.strtoupper(substr(md5($booking_ref.time()),0,8));
    $stmt = $pdo->prepare("INSERT INTO td_payments (booking_id,booking_ref,gateway,amount,currency,status,invoice_number) VALUES (?,?,?,?,?,'pending',?)");
    $stmt->execute([$booking_id,$booking_ref,$gateway,$amount,$currency,$inv]);
    return ['payment_id'=>$pdo->lastInsertId(),'invoice_number'=>$inv];
}
function update_payment_status($payment_id, $status, $transaction_id=null, $response=null) {
    global $pdo;
    $pdo->prepare("UPDATE td_payments SET status=?,transaction_id=?,gateway_response=?,updated_at=NOW() WHERE id=?")->execute([$status,$transaction_id,json_encode($response),$payment_id]);
}
function format_money($amount, $currency='GBP') {
    $symbols=['GBP'=>'£','USD'=>'$','EUR'=>'€'];
    return ($symbols[$currency] ?? $currency.' ').number_format($amount,2);
}
PHPEOF;

foreach($files as $path => $content) {
    file_put_contents("$base/$path", $content);
    echo "Created: $path\n";
}
echo "DIRS AND CONFIG DONE\n";
?>
