<?php
/**
 * Payment Configuration & Helper Functions
 * TaxisDispatch Payment System
 */

if(!defined('SITE_URL')) {
    // Detect site URL
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'taxisdispatch.com';
    define('SITE_URL', rtrim($proto.'://'.$host, '/'));
}

/**
 * Get all active payment gateways from DB
 */
function get_active_gateways() {
    global $pdo;
    if(empty($pdo)) {
        require_once __DIR__.'/../includes/config.php';
        $pdo = db_connect();
    }
    $stmt = $pdo->query("SELECT * FROM td_payment_gateways WHERE enabled=1 ORDER BY sort_order");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as &$r) {
        $r['config'] = json_decode($r['config'] ?? '{}', true);
    }
    return $rows;
}

/**
 * Get config for a single gateway
 */
function get_gateway_config(string $gateway): array {
    global $pdo;
    if(empty($pdo)) {
        require_once __DIR__.'/../includes/config.php';
        $pdo = db_connect();
    }
    $stmt = $pdo->prepare("SELECT * FROM td_payment_gateways WHERE gateway=? LIMIT 1");
    $stmt->execute([$gateway]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$row) return [];
    $row['config'] = json_decode($row['config'] ?? '{}', true);
    return $row;
}

/**
 * Log a payment attempt
 */
function log_payment(int $booking_id, string $booking_ref, string $gateway, float $amount, string $status='pending', ?string $txn_id=null, $response=null): int {
    global $pdo;
    if(empty($pdo)) {
        require_once __DIR__.'/../includes/config.php';
        $pdo = db_connect();
    }
    $stmt = $pdo->prepare("INSERT INTO td_payments (booking_id,booking_ref,gateway,amount,status,gateway_transaction_id,gateway_response) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$booking_id, $booking_ref, $gateway, $amount, $status, $txn_id, json_encode($response)]);
    return (int)$pdo->lastInsertId();
}

/**
 * Update payment status
 */
function update_payment_status(int $payment_id, string $status, ?string $txn_id=null, $response=null): void {
    global $pdo;
    $pdo->prepare("UPDATE td_payments SET status=?,gateway_transaction_id=COALESCE(?,gateway_transaction_id),gateway_response=?,updated_at=NOW() WHERE id=?")
        ->execute([$status, $txn_id, json_encode($response), $payment_id]);
}

/**
 * Update booking payment status
 */
function update_booking_payment(string $booking_ref, string $payment_status, string $gateway): void {
    global $pdo;
    $pdo->prepare("UPDATE td_bookings SET payment_status=?,payment_gateway=?,updated_at=NOW() WHERE booking_ref=?")
        ->execute([$payment_status, $gateway, $booking_ref]);
}

/**
 * Get booking by ref (safe)
 */
function get_booking_by_ref(string $ref) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM td_bookings WHERE booking_ref=? LIMIT 1");
    $stmt->execute([$ref]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
