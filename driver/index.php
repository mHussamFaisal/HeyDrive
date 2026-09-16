<?php
require_once '../includes/config.php';
require_driver();
$pdo = db_connect();

// Update driver last_seen timestamp
$pdo->prepare("UPDATE td_users SET last_seen=NOW() WHERE id=?")->execute([$_SESSION['user_id']]);
$pdo->prepare("UPDATE td_drivers SET last_seen=NOW() WHERE user_id=?")->execute([$_SESSION['user_id']]);

// Get driver profile
$driver_stmt = $pdo->prepare("SELECT d.*, v.make, v.model, v.license_plate, v.color FROM td_drivers d LEFT JOIN td_vehicles v ON d.vehicle_id=v.id WHERE d.user_id=?");
$driver_stmt->execute([$_SESSION['user_id']]);
$driver = $driver_stmt->fetch();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = sanitize($_POST['status']);
    $pdo->prepare("UPDATE td_drivers SET activity_status=?, status=? WHERE user_id=?")->execute([ucfirst($status), $status, $_SESSION['user_id']]);
    redirect(APP_URL . '/driver/');
}

// Handle trip update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_trip'])) {
    $bid = intval($_POST['booking_id']);
    $status = sanitize($_POST['trip_status']);
    $note = sanitize($_POST['driver_notes'] ?? '');
    $pdo->prepare("UPDATE td_bookings SET status=?,driver_notes=? WHERE id=? AND driver_id=?")->execute([$status,$note,$bid,$driver['id']]);
    if ($status === 'completed') {
        $pdo->prepare("UPDATE td_drivers SET status='available', total_trips=total_trips+1 WHERE id=?")->execute([$driver['id']]);
    }
    redirect(APP_URL . '/driver/');
}

// My trips
$my_trips = $pdo->prepare("SELECT * FROM td_bookings WHERE driver_id=? AND status NOT IN ('completed','cancelled') ORDER BY pickup_datetime ASC");
$my_trips->execute([$driver['id'] ?? 0]);
$active_trips = $my_trips->fetchAll();

$completed = $pdo->prepare("SELECT * FROM td_bookings WHERE driver_id=? AND status='completed' ORDER BY pickup_datetime DESC LIMIT 20");
$completed->execute([$driver['id'] ?? 0]);
$completed_trips = $completed->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Driver Portal - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
.nav-driver { background: #1a1a2e; }
</style>
</head>
<body>
<nav class="navbar navbar-dark nav-driver px-4 py-2">
  <span class="navbar-brand fw-bold"><i class="fas fa-taxi me-2 text-warning"></i>Driver Portal</span>
  <div class="d-flex align-items-center gap-3">
    <span class="text-white"><?= $_SESSION['user_name'] ?></span>
    <?php if ($driver): ?>
    <form method="POST" class="d-inline">
      <input type="hidden" name="update_status" value="1">
      <select name="status" class="form-select form-select-sm d-inline-block" style="width:120px" onchange="this.form.submit()">
        <?php foreach (['available','busy','offline'] as $s): ?>
        <option value="<?=$s?>" <?= ($driver['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-outline-warning btn-sm">Logout</a>
  </div>
</nav>

<div class="container-fluid p-4">
  <!-- Driver Info Card -->
  <?php if ($driver): ?>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-4">
      <div class="display-4">🚖</div>
      <div class="flex-fill">
        <h5 class="mb-1"><?= $_SESSION['user_name'] ?></h5>
        <span class="badge bg-<?= ['available'=>'success','busy'=>'warning','offline'=>'secondary'][$driver['status']] ?? 'secondary' ?>">
          <?= ucfirst($driver['status']) ?>
        </span>
        <?php if ($driver['license_plate']): ?>
        <span class="ms-2 badge bg-dark"><?= $driver['color'] ?> <?= $driver['make'] ?> <?= $driver['model'] ?> · <?= $driver['license_plate'] ?></span>
        <?php endif; ?>
      </div>
      <div class="text-end">
        <div>⭐ <?= $driver['rating'] ?></div>
        <div class="text-muted small"><?= $driver['total_trips'] ?> total trips</div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Active Assignments -->
  <h5 class="fw-bold mb-3">📋 My Active Trips (<?= count($active_trips) ?>)</h5>
  <?php if (empty($active_trips)): ?>
  <div class="alert alert-info">No active trips. You're currently <?= $driver['status'] ?? 'offline' ?>.</div>
  <?php else: ?>
  <?php foreach ($active_trips as $t): ?>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-warning d-flex justify-content-between align-items-center">
      <span class="fw-bold"><?= $t['booking_ref'] ?></span>
      <?= status_badge($t['status']) ?>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <strong>👤 Customer</strong><br>
          <?= htmlspecialchars($t['customer_name']) ?><br>
          <a href="tel:<?= $t['customer_phone'] ?>" class="btn btn-sm btn-success mt-1">
            📞 <?= $t['customer_phone'] ?>
          </a>
        </div>
        <div class="col-md-4">
          <strong>🗓️ Pickup</strong><br>
          <?= format_datetime($t['pickup_datetime']) ?><br>
          👥 <?= $t['passengers'] ?> passengers
          <?php if ($t['flight_number']): ?><br>✈️ <?= $t['flight_number'] ?><?php endif; ?>
        </div>
        <div class="col-md-4">
          <strong>💶 Payment</strong><br>
          <?= $t['price'] ? format_price($t['price']) : 'TBD' ?><br>
          <?= ucfirst($t['payment_method']) ?>
        </div>
        <div class="col-12">
          <div class="p-3 bg-light rounded">
            <div><span class="text-success fw-bold">↑ PICKUP:</span> <?= htmlspecialchars($t['pickup_address']) ?></div>
            <div class="mt-1"><span class="text-danger fw-bold">↓ DROPOFF:</span> <?= htmlspecialchars($t['dropoff_address']) ?></div>
          </div>
        </div>
        <?php if ($t['notes']): ?>
        <div class="col-12"><div class="alert alert-light mb-0"><strong>📝 Notes:</strong> <?= htmlspecialchars($t['notes']) ?></div></div>
        <?php endif; ?>
        <div class="col-12">
          <form method="POST" class="d-flex gap-2 align-items-end">
            <input type="hidden" name="update_trip" value="1">
            <input type="hidden" name="booking_id" value="<?= $t['id'] ?>">
            <div>
              <label class="form-label small">Update Status</label>
              <select name="trip_status" class="form-select">
                <?php foreach (['confirmed','assigned','in_progress','completed','no_show'] as $s): ?>
                <option value="<?=$s?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="flex-fill">
              <label class="form-label small">Driver Notes</label>
              <input type="text" class="form-control" name="driver_notes" value="<?= htmlspecialchars($t['driver_notes'] ?? '') ?>" placeholder="Notes for dispatcher...">
            </div>
            <button type="submit" class="btn btn-warning">Update</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <!-- Completed trips -->
  <?php if (!empty($completed_trips)): ?>
  <h5 class="fw-bold mt-4 mb-3">✅ Recent Completed Trips</h5>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Ref</th><th>Date</th><th>Customer</th><th>Route</th><th>Price</th></tr></thead>
        <tbody>
          <?php foreach ($completed_trips as $t): ?>
          <tr>
            <td><?= $t['booking_ref'] ?></td>
            <td><?= format_datetime($t['pickup_datetime']) ?></td>
            <td><?= htmlspecialchars($t['customer_name']) ?></td>
            <td class="small"><?= htmlspecialchars(substr($t['pickup_address'],0,20)) ?>... → <?= htmlspecialchars(substr($t['dropoff_address'],0,20)) ?>...</td>
            <td><?= $t['price'] ? format_price($t['price']) : '-' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Driver GPS push ──────────────────────────────────────────────────────────
(function() {
    if (!navigator.geolocation) return; // browser doesn't support GPS

    function pushLocation(pos) {
        fetch('update_location.php', {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
        });
    }

    function onError(err) {
        console.warn('GPS error:', err.message);
    }

    var options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 };

    // Push once immediately
    navigator.geolocation.getCurrentPosition(pushLocation, onError, options);

    // Then push every 20 seconds while the tab is open
    setInterval(function() {
        navigator.geolocation.getCurrentPosition(pushLocation, onError, options);
    }, 20000);
})();
</script>

</body>
</html>
