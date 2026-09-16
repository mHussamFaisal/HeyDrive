<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/payment_config.php';
$pdo = db_connect();

$ref     = trim($_GET['ref'] ?? '');
$gateway = trim($_GET['gateway'] ?? '');
$booking = $ref ? get_booking_by_ref($ref) : false;

$gateway_labels = ['stripe'=>'Stripe','paypal'=>'PayPal','square'=>'Square','sumup'=>'SumUp','cash'=>'Cash on Pickup','invoice'=>'Invoice'];
$gateway_icons  = ['stripe'=>'fab fa-stripe-s','paypal'=>'fab fa-paypal','square'=>'fas fa-square','sumup'=>'fas fa-credit-card','cash'=>'fas fa-money-bill-wave','invoice'=>'fas fa-file-invoice'];
$gateway_colors = ['stripe'=>'#635bff','paypal'=>'#003087','square'=>'#00b140','sumup'=>'#131415','cash'=>'#198754','invoice'=>'#0d6efd'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment Confirmed - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="<?=SITE_URL?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/"><i class="fas fa-taxi me-2 text-warning"></i>TaxisDispatch</a>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow border-0 rounded-4 overflow-hidden text-center">
        <div class="card-body p-5">
          <?php if($gateway === 'cash'): ?>
          <div class="mb-3"><i class="fas fa-money-bill-wave fa-4x text-success"></i></div>
          <h2 class="fw-bold text-success">Booking Confirmed!</h2>
          <p class="text-muted">Your booking has been received. Please have cash ready for the driver.</p>
          <?php elseif($gateway === 'invoice'): ?>
          <div class="mb-3"><i class="fas fa-file-invoice fa-4x text-primary"></i></div>
          <h2 class="fw-bold text-primary">Invoice Sent!</h2>
          <p class="text-muted">An invoice has been created for your booking. Please complete payment before your journey.</p>
          <?php else: ?>
          <div class="mb-3">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width:80px;height:80px;background:<?=$gateway_colors[$gateway]??'#198754'?>20">
              <i class="fas fa-check-circle fa-3x" style="color:<?=$gateway_colors[$gateway]??'#198754'?>"></i>
            </div>
          </div>
          <h2 class="fw-bold" style="color:<?=$gateway_colors[$gateway]??'#198754'?>">Payment Received!</h2>
          <p class="text-muted">Your payment via <?=$gateway_labels[$gateway]??ucfirst($gateway)?> has been processed successfully.</p>
          <?php endif; ?>

          <?php if($booking): ?>
          <div class="alert alert-light border text-start my-4">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Booking Ref</span>
              <strong class="font-monospace text-warning"><?=htmlspecialchars($booking['booking_ref'])?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Name</span>
              <strong><?=htmlspecialchars($booking['customer_name'])?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">From</span>
              <span><?=htmlspecialchars($booking['pickup_address'])?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">To</span>
              <span><?=htmlspecialchars($booking['dropoff_address'])?></span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Pickup</span>
              <strong><?=date('D d M Y H:i', strtotime($booking['pickup_datetime']))?></strong>
            </div>
            <?php if(!empty($booking['fare']) && $booking['fare'] > 0): ?>
            <hr>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Amount</span>
              <strong class="text-success fs-5">£<?=number_format((float)$booking['fare'],2)?></strong>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="<?=SITE_URL?>/track.php?ref=<?=urlencode($ref)?>" class="btn btn-warning">
              <i class="fas fa-map-marker-alt me-1"></i>Track Booking
            </a>
            <?php if($gateway === 'invoice'): ?>
            <a href="<?=SITE_URL?>/payments/invoice.php?ref=<?=urlencode($ref)?>" class="btn btn-outline-primary">
              <i class="fas fa-file-pdf me-1"></i>View Invoice
            </a>
            <?php endif; ?>
            <a href="<?=SITE_URL?>/" class="btn btn-outline-secondary">
              <i class="fas fa-home me-1"></i>Home
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  <small>© <?=date('Y')?> TaxisDispatch — All rights reserved</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
