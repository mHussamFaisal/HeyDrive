<?php
require_once '../includes/config.php';
$pdo = db_connect();
$id = intval($_GET['id'] ?? 0);

if (!$id) redirect(APP_URL . '/admin/bookings.php');

$booking = $pdo->prepare("SELECT * FROM td_bookings WHERE id=?");
$booking->execute([$id]);
$booking = $booking->fetch();
if (!$booking) redirect(APP_URL . '/admin/bookings.php');

// Get drivers
$drivers = $pdo->query("SELECT d.id, u.name, d.status, d.vehicle_id, v.make, v.model, v.license_plate 
    FROM td_drivers d 
    JOIN td_users u ON d.user_id = u.id
    LEFT JOIN td_vehicles v ON d.vehicle_id = v.id
    WHERE d.status IN ('available','busy') AND u.status = 'active'
    ORDER BY u.name")->fetchAll();

// Get all vehicles
$vehicles = $pdo->query("SELECT * FROM td_vehicles WHERE status='active' ORDER BY make,model")->fetchAll();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        sanitize($_POST['customer_name']),
        sanitize($_POST['customer_phone']),
        sanitize($_POST['customer_email']),
        sanitize($_POST['pickup_address']),
        sanitize($_POST['dropoff_address']),
        $_POST['pickup_datetime'],
        intval($_POST['passengers']),
        sanitize($_POST['vehicle_type']),
        sanitize($_POST['flight_number'] ?? ''),
        sanitize($_POST['notes'] ?? ''),
        sanitize($_POST['payment_method']),
        sanitize($_POST['payment_status']),
        sanitize($_POST['status']),
        $_POST['driver_id'] ? intval($_POST['driver_id']) : null,
        $_POST['vehicle_id'] ? intval($_POST['vehicle_id']) : null,
        floatval($_POST['price'] ?: 0) ?: null,
        sanitize($_POST['admin_notes'] ?? ''),
        $id
    ];
    $pdo->prepare("UPDATE td_bookings SET 
        customer_name=?, customer_phone=?, customer_email=?,
        pickup_address=?, dropoff_address=?, pickup_datetime=?,
        passengers=?, vehicle_type=?, flight_number=?, notes=?,
        payment_method=?, payment_status=?, status=?,
        driver_id=?, vehicle_id=?, fare=?, admin_notes=?
        WHERE id=?")->execute($data);
    redirect(APP_URL . '/admin/bookings.php?msg=updated');
}

$page_title = 'Edit Booking';
require_once 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between">
        <span class="fw-bold">✏️ Edit Booking #<?= $booking['booking_ref'] ?></span>
        <a href="bookings.php" class="btn btn-sm btn-outline-secondary">← Back</a>
      </div>
      <div class="card-body p-4">
        <form method="POST">
          <div class="row g-3">
            <!-- Status Bar -->
            <div class="col-12">
              <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                <div>Current Status: <?= status_badge($booking['status']) ?></div>
                <div class="ms-auto">
                  <label class="me-2 fw-bold">Update Status:</label>
                  <select name="status" class="form-select d-inline-block" style="width:auto">
                    <?php foreach (['pending','confirmed','assigned','in_progress','completed','cancelled','no_show'] as $s): ?>
                    <option value="<?=$s?>" <?= $booking['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <!-- Customer Info -->
            <div class="col-12"><h6 class="border-bottom pb-2">👤 Customer Information</h6></div>
            <div class="col-md-4">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="customer_name" required value="<?= htmlspecialchars($booking['customer_name']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="customer_phone" required value="<?= htmlspecialchars($booking['customer_phone']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="customer_email" value="<?= htmlspecialchars($booking['customer_email'] ?? '') ?>">
            </div>

            <!-- Trip -->
            <div class="col-12 mt-2"><h6 class="border-bottom pb-2">🗺️ Trip Details</h6></div>
            <div class="col-md-6">
              <label class="form-label">Pickup Address</label>
              <input type="text" class="form-control" name="pickup_address" value="<?= htmlspecialchars($booking['pickup_address']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dropoff Address</label>
              <input type="text" class="form-control" name="dropoff_address" value="<?= htmlspecialchars($booking['dropoff_address']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Pickup Date & Time</label>
              <input type="datetime-local" class="form-control" name="pickup_datetime" 
                     value="<?= date('Y-m-d\TH:i', strtotime($booking['pickup_datetime'])) ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">Passengers</label>
              <input type="number" class="form-control" name="passengers" min="1" max="16" value="<?= $booking['passengers'] ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Vehicle Type</label>
              <select class="form-select" name="vehicle_type">
                <?php foreach (['sedan','van','luxury','minibus'] as $vt): ?>
                <option value="<?=$vt?>" <?= $booking['vehicle_type']===$vt?'selected':'' ?>><?= ucfirst($vt) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Flight Number</label>
              <input type="text" class="form-control" name="flight_number" value="<?= htmlspecialchars($booking['flight_number'] ?? '') ?>">
            </div>

            <!-- Dispatch -->
            <div class="col-12 mt-2"><h6 class="border-bottom pb-2">🚖 Dispatch Assignment</h6></div>
            <div class="col-md-6">
              <label class="form-label">Assign Driver</label>
              <select class="form-select" name="driver_id">
                <option value="">-- No Driver --</option>
                <?php foreach ($drivers as $d): ?>
                <option value="<?=$d['id']?>" <?= $booking['driver_id']==$d['id']?'selected':'' ?>>
                  <?= htmlspecialchars($d['name']) ?> (<?= ucfirst($d['status']) ?>) 
                  <?= $d['license_plate'] ? '- ' . $d['make'] . ' ' . $d['model'] . ' [' . $d['license_plate'] . ']' : '' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Assign Vehicle</label>
              <select class="form-select" name="vehicle_id">
                <option value="">-- No Vehicle --</option>
                <?php foreach ($vehicles as $v): ?>
                <option value="<?=$v['id']?>" <?= $booking['vehicle_id']==$v['id']?'selected':'' ?>>
                  <?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?> [<?= $v['license_plate'] ?>] - <?= ucfirst($v['type']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Payment -->
            <div class="col-12 mt-2"><h6 class="border-bottom pb-2">💶 Payment</h6></div>
            <div class="col-md-4">
              <label class="form-label">Price (€)</label>
              <input type="number" step="0.01" class="form-control" name="price" value="<?= $booking['fare'] ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Payment Method</label>
              <select class="form-select" name="payment_method">
                <?php foreach (['cash','card','invoice','paypal'] as $pm): ?>
                <option value="<?=$pm?>" <?= $booking['payment_method']===$pm?'selected':'' ?>><?= ucfirst($pm) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Payment Status</label>
              <select class="form-select" name="payment_status">
                <?php foreach (['pending','paid','refunded'] as $ps): ?>
                <option value="<?=$ps?>" <?= $booking['payment_status']===$ps?'selected':'' ?>><?= ucfirst($ps) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Notes -->
            <div class="col-md-6">
              <label class="form-label">Customer Notes</label>
              <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($booking['notes'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Admin Notes (internal)</label>
              <textarea class="form-control" name="admin_notes" rows="3"><?= htmlspecialchars($booking['admin_notes'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-2"></i>Save Changes</button>
              <a href="bookings.php" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once 'footer.php'; ?>
