<?php
require_once 'includes/config.php';

$booking = null;
$error = '';
$ref = sanitize($_GET['ref'] ?? $_POST['ref'] ?? '');

if ($ref) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT b.*, d.user_id as driver_user_id, u.name as driver_name, u.phone as driver_phone,
                               v.make, v.model, v.license_plate, v.color
                               FROM td_bookings b
                               LEFT JOIN td_drivers d ON b.driver_id = d.id
                               LEFT JOIN td_users u ON d.user_id = u.id
                               LEFT JOIN td_vehicles v ON b.vehicle_id = v.id
                               WHERE b.booking_ref = ?");
        $stmt->execute([$ref]);
        $booking = $stmt->fetch();
        if (!$booking) $error = 'Booking not found. Please check your reference number.';
    } catch (Exception $e) {
        $error = 'Error fetching booking.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Track Booking - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/"><i class="fas fa-taxi me-2 text-warning"></i>TaxisDispatch</a>
    <a href="/" class="btn btn-outline-warning btn-sm">← Back to Booking</a>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <h2 class="fw-bold mb-4 text-center"><i class="fas fa-search me-2 text-warning"></i>Track Your Booking</h2>
      
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
          <form method="GET">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-ticket-alt"></i></span>
              <input type="text" class="form-control form-control-lg" name="ref" 
                     value="<?= htmlspecialchars($ref) ?>" placeholder="Enter booking reference (e.g. TD1A2B3C4D)">
              <button class="btn btn-warning px-4" type="submit"><i class="fas fa-search"></i> Track</button>
            </div>
          </form>
        </div>
      </div>

      <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
      <?php endif; ?>

      <?php if ($booking): ?>
      <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <span><i class="fas fa-clipboard-list me-2"></i>Booking #<?= $booking['booking_ref'] ?></span>
          <?= status_badge($booking['status']) ?>
        </div>
        <div class="card-body p-4">
          <!-- Progress Tracker -->
          <?php
          $steps = ['pending' => 1, 'confirmed' => 2, 'assigned' => 3, 'in_progress' => 4, 'completed' => 5];
          $current = $steps[$booking['status']] ?? 1;
          if ($booking['status'] === 'cancelled') $current = 0;
          ?>
          <?php if ($booking['status'] !== 'cancelled'): ?>
          <div class="progress-steps mb-4">
            <div class="d-flex justify-content-between text-center">
              <?php $step_labels = ['Received','Confirmed','Driver Assigned','En Route','Completed']; ?>
              <?php foreach ($step_labels as $i => $label): ?>
              <div class="flex-fill">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center <?= ($i+1 <= $current) ? 'bg-warning' : 'bg-light border' ?>" style="width:36px;height:36px;">
                  <?= ($i+1 < $current) ? '✔' : ($i+1) ?>
                </div>
                <div class="small mt-1 <?= ($i+1 <= $current) ? 'fw-bold' : 'text-muted' ?>"><?= $label ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php else: ?>
          <div class="alert alert-danger">This booking has been cancelled.</div>
          <?php endif; ?>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="info-box p-3 bg-light rounded">
                <div class="small text-muted mb-1"><i class="fas fa-user me-1"></i>Customer</div>
                <div class="fw-bold"><?= htmlspecialchars($booking['customer_name']) ?></div>
                <div><?= htmlspecialchars($booking['customer_phone']) ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-box p-3 bg-light rounded">
                <div class="small text-muted mb-1"><i class="fas fa-calendar me-1"></i>Pickup Time</div>
                <div class="fw-bold"><?= format_datetime($booking['pickup_datetime']) ?></div>
                <div><?= $booking['passengers'] ?> passenger(s) • <?= ucfirst($booking['vehicle_type']) ?></div>
              </div>
            </div>
            <div class="col-12">
              <div class="info-box p-3 bg-light rounded">
                <div class="small text-muted mb-1"><i class="fas fa-route me-1"></i>Route</div>
                <div><span class="text-success">●</span> <?= htmlspecialchars($booking['pickup_address']) ?></div>
                <div class="ms-2 text-muted">↓</div>
                <div><span class="text-danger">●</span> <?= htmlspecialchars($booking['dropoff_address']) ?></div>
              </div>
            </div>
            <?php if ($booking['driver_name']): ?>
            <div class="col-12">
              <div class="info-box p-3 bg-warning bg-opacity-10 border border-warning rounded">
                <div class="small text-muted mb-1"><i class="fas fa-id-badge me-1"></i>Your Driver</div>
                <div class="fw-bold"><?= htmlspecialchars($booking['driver_name']) ?></div>
                <div><?= htmlspecialchars($booking['driver_phone']) ?></div>
                <?php if ($booking['license_plate']): ?>
                <div>🚕 <?= $booking['color'] ?> <?= $booking['make'] ?> <?= $booking['model'] ?> • <?= $booking['license_plate'] ?></div>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($booking['price']): ?>
            <div class="col-md-6">
              <div class="info-box p-3 bg-light rounded">
                <div class="small text-muted mb-1"><i class="fas fa-euro-sign me-1"></i>Price</div>
                <div class="fw-bold fs-5"><?= format_price($booking['price']) ?></div>
                <div class="small"><?= ucfirst($booking['payment_method']) ?> • <?= ucfirst($booking['payment_status']) ?></div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
