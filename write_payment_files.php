<?php
$base = __DIR__;

// Ensure dirs exist
foreach(['payments','payments/gateways'] as $d) {
    if(!is_dir("$base/$d")) mkdir("$base/$d",0755,true);
}

// ── STRIPE HANDLER ────────────────────────────────────────────────────────────
file_put_contents("$base/payments/gateways/stripe.php", <<<'EOF'
<?php
require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../payment_config.php';

// Install Stripe via composer or use manual HTTP calls
// We use Stripe's API directly via cURL (no composer needed)

function stripe_create_checkout($booking, $amount, $currency='GBP') {
    $cfg = get_gateway_config('stripe');
    if(!$cfg) return ['error'=>'Stripe not configured'];
    $c = $cfg['config'];
    $sk = $cfg['test_mode'] ? ($c['secret_key_test'] ?? $c['secret_key'] ?? '') : ($c['secret_key'] ?? '');
    if(empty($sk)) return ['error'=>'Stripe secret key not set'];

    $params = http_build_query([
        'payment_method_types[]'       => 'card',
        'line_items[0][price_data][currency]'                  => strtolower($currency),
        'line_items[0][price_data][product_data][name]'        => 'Taxi Booking '.$booking['booking_ref'],
        'line_items[0][price_data][product_data][description]' => $booking['pickup_address'].' → '.$booking['dropoff_address'],
        'line_items[0][price_data][unit_amount]'               => intval($amount*100),
        'line_items[0][quantity]'                              => 1,
        'mode'                         => 'payment',
        'success_url'                  => SITE_URL.'/payments/success.php?ref='.$booking['booking_ref'].'&gateway=stripe&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'                   => SITE_URL.'/payments/cancel.php?ref='.$booking['booking_ref'],
        'customer_email'               => $booking['customer_email'] ?? '',
        'metadata[booking_ref]'        => $booking['booking_ref'],
    ]);
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$params,
        CURLOPT_USERPWD=>"$sk:",
        CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $res = json_decode(curl_exec($ch),true);
    curl_close($ch);
    if(isset($res['url'])) return ['redirect_url'=>$res['url'],'session_id'=>$res['id']];
    return ['error'=>$res['error']['message'] ?? 'Stripe error'];
}

function stripe_verify_webhook($payload, $sig_header, $secret) {
    $parts = explode(',', $sig_header);
    $ts = null; $sigs = [];
    foreach($parts as $p) {
        [$k,$v] = explode('=',$p,2);
        if($k==='t') $ts=$v;
        if($k==='v1') $sigs[]=$v;
    }
    $signed = "$ts.$payload";
    $expected = hash_hmac('sha256',$signed,$secret);
    return in_array($expected,$sigs);
}
EOF
);

// ── PAYPAL HANDLER ────────────────────────────────────────────────────────────
file_put_contents("$base/payments/gateways/paypal.php", <<<'EOF'
<?php
require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../payment_config.php';

function paypal_get_token($client_id, $secret, $sandbox=true) {
    $url = $sandbox ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token'
                    : 'https://api-m.paypal.com/v1/oauth2/token';
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>'grant_type=client_credentials',
        CURLOPT_USERPWD=>"$client_id:$secret",
        CURLOPT_HTTPHEADER=>['Accept: application/json','Content-Type: application/x-www-form-urlencoded'],
    ]);
    $res = json_decode(curl_exec($ch),true); curl_close($ch);
    return $res['access_token'] ?? null;
}

function paypal_create_order($booking, $amount, $currency='GBP') {
    $cfg = get_gateway_config('paypal');
    if(!$cfg) return ['error'=>'PayPal not configured'];
    $c = $cfg['config'];
    $sandbox = (bool)$cfg['test_mode'];
    $cid = $sandbox ? ($c['client_id_sandbox'] ?? $c['client_id'] ?? '') : ($c['client_id'] ?? '');
    $sec = $sandbox ? ($c['secret_sandbox'] ?? $c['client_secret'] ?? '') : ($c['client_secret'] ?? '');
    if(empty($cid)) return ['error'=>'PayPal client_id not set'];

    $token = paypal_get_token($cid,$sec,$sandbox);
    if(!$token) return ['error'=>'Could not get PayPal token'];

    $base_api = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    $body = json_encode([
        'intent'=>'CAPTURE',
        'purchase_units'=>[[
            'reference_id'=>$booking['booking_ref'],
            'description'=>'Taxi: '.$booking['pickup_address'].' → '.$booking['dropoff_address'],
            'amount'=>['currency_code'=>strtoupper($currency),'value'=>number_format($amount,2,'.','')],
        ]],
        'application_context'=>[
            'return_url'=>SITE_URL.'/payments/success.php?ref='.$booking['booking_ref'].'&gateway=paypal',
            'cancel_url'=>SITE_URL.'/payments/cancel.php?ref='.$booking['booking_ref'],
            'brand_name'=>'TaxisDispatch',
            'landing_page'=>'BILLING',
            'user_action'=>'PAY_NOW',
        ]
    ]);
    $ch = curl_init("$base_api/v2/checkout/orders");
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$body,
        CURLOPT_HTTPHEADER=>["Authorization: Bearer $token","Content-Type: application/json"],
    ]);
    $res = json_decode(curl_exec($ch),true); curl_close($ch);
    foreach(($res['links'] ?? []) as $link) {
        if($link['rel']==='approve') return ['redirect_url'=>$link['href'],'order_id'=>$res['id']];
    }
    return ['error'=>$res['message'] ?? 'PayPal error'];
}

function paypal_capture_order($order_id, $sandbox=true) {
    $cfg = get_gateway_config('paypal');
    $c = $cfg['config'];
    $cid = $sandbox?($c['client_id_sandbox']??$c['client_id']??''):($c['client_id']??'');
    $sec = $sandbox?($c['secret_sandbox']??$c['client_secret']??''):($c['client_secret']??'');
    $token = paypal_get_token($cid,$sec,$sandbox);
    $base_api = $sandbox?'https://api-m.sandbox.paypal.com':'https://api-m.paypal.com';
    $ch = curl_init("$base_api/v2/checkout/orders/$order_id/capture");
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>'{}',
        CURLOPT_HTTPHEADER=>["Authorization: Bearer $token","Content-Type: application/json"]]);
    $res = json_decode(curl_exec($ch),true); curl_close($ch);
    return $res;
}
EOF
);

// ── SQUARE HANDLER ────────────────────────────────────────────────────────────
file_put_contents("$base/payments/gateways/square.php", <<<'EOF'
<?php
require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../payment_config.php';

function square_create_payment_link($booking, $amount, $currency='GBP') {
    $cfg = get_gateway_config('square');
    if(!$cfg) return ['error'=>'Square not configured'];
    $c = $cfg['config'];
    $sandbox = (bool)$cfg['test_mode'];
    $token = $c['access_token_sandbox'] ?? ($sandbox ? '' : ($c['access_token'] ?? ''));
    if(!$sandbox) $token = $c['access_token'] ?? '';
    if(empty($token)) return ['error'=>'Square access token not set'];
    $location_id = $c['location_id'] ?? '';

    $base_api = $sandbox ? 'https://connect.squareupsandbox.com' : 'https://connect.squareup.com';
    $body = json_encode([
        'idempotency_key' => uniqid($booking['booking_ref']),
        'order' => [
            'location_id' => $location_id,
            'reference_id' => $booking['booking_ref'],
            'line_items' => [[
                'name' => 'Taxi Booking '.$booking['booking_ref'],
                'quantity' => '1',
                'base_price_money' => [
                    'amount' => intval($amount * 100),
                    'currency' => strtoupper($currency),
                ],
            ]],
        ],
        'checkout_options' => [
            'redirect_url' => SITE_URL.'/payments/success.php?ref='.$booking['booking_ref'].'&gateway=square',
        ],
    ]);
    $ch = curl_init("$base_api/v2/online-checkout/payment-links");
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$body,
        CURLOPT_HTTPHEADER=>["Authorization: Bearer $token","Content-Type: application/json","Square-Version: 2024-01-18"],
    ]);
    $res = json_decode(curl_exec($ch),true); curl_close($ch);
    if(isset($res['payment_link']['url'])) return ['redirect_url'=>$res['payment_link']['url'],'link_id'=>$res['payment_link']['id']];
    return ['error'=>$res['errors'][0]['detail'] ?? 'Square error'];
}
EOF
);

// ── SUMUP HANDLER ────────────────────────────────────────────────────────────
file_put_contents("$base/payments/gateways/sumup.php", <<<'EOF'
<?php
require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../payment_config.php';

function sumup_create_checkout($booking, $amount, $currency='GBP') {
    $cfg = get_gateway_config('sumup');
    if(!$cfg) return ['error'=>'SumUp not configured'];
    $c = $cfg['config'];
    $key = $c['api_key'] ?? '';
    if(empty($key)) return ['error'=>'SumUp API key not set'];

    $body = json_encode([
        'checkout_reference' => $booking['booking_ref'].'-'.time(),
        'amount'             => (float)$amount,
        'currency'           => strtoupper($currency),
        'merchant_code'      => $c['merchant_code'] ?? '',
        'description'        => 'Taxi Booking '.$booking['booking_ref'],
        'return_url'         => SITE_URL.'/payments/success.php?ref='.$booking['booking_ref'].'&gateway=sumup',
    ]);
    $ch = curl_init('https://api.sumup.com/v0.1/checkouts');
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$body,
        CURLOPT_HTTPHEADER=>["Authorization: Bearer $key","Content-Type: application/json"],
    ]);
    $res = json_decode(curl_exec($ch),true); curl_close($ch);
    if(isset($res['id'])) {
        // SumUp hosted checkout URL
        $checkout_url = 'https://pay.sumup.com/b2c/'.($res['id']);
        return ['redirect_url'=>$checkout_url,'checkout_id'=>$res['id']];
    }
    return ['error'=>$res['message'] ?? 'SumUp error'];
}
EOF
);

echo "Gateway files created OK\n";

// ── PAYMENT ROUTER (process.php) ────────────────────────────────────────────
file_put_contents("$base/payments/process.php", <<<'EOF'
<?php
session_start();
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/payment_config.php';

$booking_ref = trim($_GET['ref'] ?? $_POST['ref'] ?? '');
$gateway     = trim($_GET['gateway'] ?? $_POST['gateway'] ?? '');

if(!$booking_ref || !$gateway) { header('Location: /'); exit; }

// Load booking
$stmt = $pdo->prepare("SELECT * FROM td_bookings WHERE booking_ref=?");
$stmt->execute([$booking_ref]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$booking) { die('Booking not found'); }

// Get fare — use stored fare or compute from pricing
$amount = (float)($booking['fare'] ?? 0);
if($amount <= 0) {
    $pstmt = $pdo->prepare("SELECT * FROM td_pricing WHERE vehicle_type=? LIMIT 1");
    $pstmt->execute([$booking['vehicle_type'] ?? 'sedan']);
    $pricing = $pstmt->fetch(PDO::FETCH_ASSOC);
    $amount = $pricing ? (float)$pricing['min_fare'] : 20.00;
}

$cfg = get_gateway_config($gateway);
if(!$cfg) { die('Payment method not available'); }

// Create payment record
$pay = create_payment_record($booking['id'], $booking_ref, $gateway, $amount);
$_SESSION['payment_id'] = $pay['payment_id'];
$_SESSION['payment_ref'] = $booking_ref;

switch($gateway) {
    case 'stripe':
        require_once __DIR__.'/gateways/stripe.php';
        $result = stripe_create_checkout($booking, $amount);
        break;
    case 'paypal':
        require_once __DIR__.'/gateways/paypal.php';
        $result = paypal_create_order($booking, $amount);
        break;
    case 'square':
        require_once __DIR__.'/gateways/square.php';
        $result = square_create_payment_link($booking, $amount);
        break;
    case 'sumup':
        require_once __DIR__.'/gateways/sumup.php';
        $result = sumup_create_checkout($booking, $amount);
        break;
    case 'cash':
        // Cash — just mark pending, show confirmation
        $pdo->prepare("UPDATE td_bookings SET payment_method='cash', payment_status='pending' WHERE booking_ref=?")->execute([$booking_ref]);
        update_payment_status($pay['payment_id'],'pending',null,['note'=>'Cash on arrival']);
        header("Location: /payments/success.php?ref=$booking_ref&gateway=cash&inv=".$pay['invoice_number']); exit;
    case 'invoice':
        // Invoice — mark pending, send invoice
        $pdo->prepare("UPDATE td_bookings SET payment_method='account',payment_status='pending',invoice_number=? WHERE booking_ref=?")->execute([$pay['invoice_number'],$booking_ref]);
        update_payment_status($pay['payment_id'],'pending',null,['note'=>'Invoice issued']);
        header("Location: /payments/invoice.php?ref=$booking_ref&inv=".$pay['invoice_number']); exit;
    default:
        die('Unknown payment method');
}

if(isset($result['redirect_url'])) {
    // Save transaction ref
    $pdo->prepare("UPDATE td_payments SET transaction_id=?,status='processing' WHERE id=?")->execute([$result['session_id']??$result['order_id']??$result['checkout_id']??'', $pay['payment_id']]);
    header('Location: '.$result['redirect_url']); exit;
}

// Error
$err = htmlspecialchars($result['error'] ?? 'Payment error');
?><!DOCTYPE html><html><head><title>Payment Error</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light p-5"><div class="container" style="max-width:500px">
<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Payment Error: <?=$err?></div>
<a href="/" class="btn btn-warning">Try Again</a></div></body></html>
EOF
);
echo "process.php created\n";
EOF
);
