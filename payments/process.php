<?php
/**
 * Payment Processor - Routes to correct gateway
 * /payments/process.php?ref=TD123&gateway=stripe
 */
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/payment_config.php';
$pdo = db_connect();

$ref     = trim($_GET['ref'] ?? '');
$gateway = trim($_GET['gateway'] ?? '');

if(!$ref || !$gateway) {
    die('<div style="font-family:sans-serif;text-align:center;padding:60px"><h2>❌ Invalid payment link</h2><p>Missing booking reference or gateway.</p><a href="/">Go Home</a></div>');
}

// Validate booking
$booking = get_booking_by_ref($ref);
if(!$booking) {
    die('<div style="font-family:sans-serif;text-align:center;padding:60px"><h2>❌ Booking not found</h2><p>Reference: <strong>'.htmlspecialchars($ref).'</strong></p><a href="/">Go Home</a></div>');
}

// Check gateway is active
$gw_cfg = get_gateway_config($gateway);
if(!$gw_cfg || !$gw_cfg['enabled']) {
    die('<div style="font-family:sans-serif;text-align:center;padding:60px"><h2>❌ Payment method unavailable</h2><p>Please choose another payment method.</p><a href="/">Go Home</a></div>');
}

$amount   = (float)($booking['fare'] ?? 0);
$currency = 'GBP';

// Handle cash & invoice immediately
if($gateway === 'cash') {
    log_payment((int)$booking['id'], $ref, 'cash', $amount, 'pending');
    update_booking_payment($ref, 'cash_on_pickup', 'cash');
    header("Location: ".SITE_URL."/payments/success.php?ref=".urlencode($ref)."&gateway=cash");
    exit;
}
if($gateway === 'invoice') {
    log_payment((int)$booking['id'], $ref, 'invoice', $amount, 'pending');
    update_booking_payment($ref, 'invoiced', 'invoice');
    header("Location: ".SITE_URL."/payments/invoice.php?ref=".urlencode($ref));
    exit;
}

// Route to gateway handler
$gateway_file = __DIR__."/gateways/{$gateway}.php";
if(!file_exists($gateway_file)) {
    die('<div style="font-family:sans-serif;text-align:center;padding:60px"><h2>⚠️ Gateway handler not found</h2></div>');
}
require $gateway_file;
?>
