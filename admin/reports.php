<?php
$page_title = 'Reports';
require_once 'header.php';
$pdo = db_connect();

$period = $_GET['period'] ?? '30';
$stats = [];
$stats['total'] = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE pickup_datetime >= DATE_SUB(NOW(), INTERVAL $period DAY)")->fetchColumn();
$stats['completed'] = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE status='completed' AND pickup_datetime >= DATE_SUB(NOW(), INTERVAL $period DAY)")->fetchColumn();
$stats['cancelled'] = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE status='cancelled' AND pickup_datetime >= DATE_SUB(NOW(), INTERVAL $period DAY)")->fetchColumn();
$stats['revenue'] = $pdo->query("SELECT COALESCE(SUM(price),0) FROM td_bookings WHERE status='completed' AND pickup_datetime >= DATE_SUB(NOW(), INTERVAL $period DAY)")->fetchColumn();

$by_status = $pdo->query("SELECT status, COUNT(*) as cnt FROM td_bookings GROUP BY status")->fetchAll();
$by_vehicle = $pdo->query("SELECT vehicle_type, COUNT(*) as cnt FROM td_bookings GROUP BY vehicle_type ORDER BY cnt DESC")->fetchAll();
$top_drivers = $pdo->query("SELECT u.name, COUNT(b.id) as trips, AVG(b.rating) as avg_rating, SUM(b.price) as revenue
    FROM td_bookings b JOIN td_drivers d ON b.driver_id=d.id JOIN td_users u ON d.user_id=u.id
    WHERE b.status='completed' GROUP BY d.id ORDER BY trips DESC LIMIT 10")->fetchAll();
$daily = $pdo->query("SELECT DATE(pickup_datetime) as day, COUNT(*) as cnt, COALESCE(SUM(price),0) as revenue 
    FROM td_bookings WHERE pickup_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day")->fetchAll();
?>
<div class="d-flex justify-content-between mb-4">
  <h5 class="fw-bold">📊 Reports</h5>
  <form method="GET" class="d-flex gap-2">
    <select name="period" class="form-select" onchange="this.form.submit()">
      <option value="7" <?=$period==7?'selected':''?>>Last 7 days</option>
      <option value="30" <?=$period==30?'selected':''?>>Last 30 days</option>
      <option value="90" <?=$period==90?'selected':''?>>Last 90 days</option>
      <option value="365" <?=$period==365?'selected':''?>>Last year</option>
    </select>
  </form>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center p-3">
      <div class="h2 fw-bold"><?= $stats['total'] ?></div>
      <div class="text-muted">Total Bookings</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center p-3 border-success">
      <div class="h2 fw-bold text-success"><?= $stats['completed'] ?></div>
      <div class="text-muted">Completed</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center p-3">
      <div class="h2 fw-bold text-danger"><?= $stats['cancelled'] ?></div>
      <div class="text-muted">Cancelled</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center p-3 bg-success text-white">
      <div class="h2 fw-bold"><?= format_price($stats['revenue']) ?></div>
      <div>Revenue</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-bold">Bookings by Status</div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <?php foreach ($by_status as $row): ?>
          <tr>
            <td><?= status_badge($row['status']) ?></td>
            <td class="text-end fw-bold"><?= $row['cnt'] ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-bold">Bookings by Vehicle Type</div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <?php foreach ($by_vehicle as $row): ?>
          <tr><td>🚗 <?= ucfirst($row['vehicle_type']) ?></td><td class="text-end fw-bold"><?= $row['cnt'] ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-bold">🏆 Top Drivers</div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light"><tr><th>Driver</th><th>Completed Trips</th><th>Avg Rating</th><th>Revenue</th></tr></thead>
          <tbody>
            <?php foreach ($top_drivers as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['name']) ?></td>
              <td><?= $d['trips'] ?></td>
              <td><?= $d['avg_rating'] ? '⭐ ' . number_format($d['avg_rating'],1) : '-' ?></td>
              <td><?= format_price($d['revenue'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($top_drivers)): ?><tr><td colspan="4" class="text-center text-muted p-3">No completed trips yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once 'footer.php'; ?>
