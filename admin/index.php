<?php
$page_title = 'Dashboard';
require_once 'header.php';

$pdo = db_connect();

// ── Always-on totals (all-time) ────────────────────────────────────────────
$total_bookings   = $pdo->query("SELECT COUNT(*) FROM td_bookings")->fetchColumn();
$total_revenue    = $pdo->query("SELECT COALESCE(SUM(fare),0) FROM td_bookings WHERE status='completed'")->fetchColumn();
$active_drivers   = $pdo->query("SELECT COUNT(*) FROM td_drivers WHERE status IN ('available','busy')")->fetchColumn();
$drivers_avail    = $pdo->query("SELECT COUNT(*) FROM td_drivers WHERE status='available'")->fetchColumn();

// ── Status breakdown (all-time) for the default view ──────────────────────
$status_counts = [];
$sc = $pdo->query("SELECT status, COUNT(*) as cnt FROM td_bookings GROUP BY status");
foreach ($sc->fetchAll() as $row) $status_counts[$row['status']] = (int)$row['cnt'];

$all_statuses = ['pending','confirmed','assigned','in_progress','completed','cancelled','no_show'];
foreach ($all_statuses as $st) if (!isset($status_counts[$st])) $status_counts[$st] = 0;

// ── Last 30 days trend (for default chart) ─────────────────────────────────
$trend = $pdo->query("
    SELECT DATE(pickup_datetime) as d, COUNT(*) as cnt
    FROM td_bookings
    WHERE pickup_datetime >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
    GROUP BY DATE(pickup_datetime)
    ORDER BY d ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Build 30-day labels even for missing days
$trend_labels = [];
$trend_data   = [];
$trend_map    = [];
foreach ($trend as $row) $trend_map[$row['d']] = (int)$row['cnt'];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $trend_labels[] = date('d M', strtotime($day));
    $trend_data[]   = $trend_map[$day] ?? 0;
}

// ── Recent bookings ────────────────────────────────────────────────────────
$recent = $pdo->query("
    SELECT b.*, d_u.name as driver_name
    FROM td_bookings b
    LEFT JOIN td_drivers d ON b.driver_id = d.id
    LEFT JOIN td_users d_u ON d.user_id = d_u.id
    ORDER BY b.created_at DESC LIMIT 10
")->fetchAll();

// ── Today list ─────────────────────────────────────────────────────────────
$today_list = $pdo->query("
    SELECT b.*, d_u.name as driver_name
    FROM td_bookings b
    LEFT JOIN td_drivers d ON b.driver_id = d.id
    LEFT JOIN td_users d_u ON d.user_id = d_u.id
    WHERE DATE(b.pickup_datetime) = CURDATE()
    ORDER BY b.pickup_datetime ASC
")->fetchAll();
?>

<!-- ══════════════════════════════════════════════════════════════════════════
     PERIOD FILTER TABS
════════════════════════════════════════════════════════════════════════════ -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
  <h5 class="mb-0 fw-bold">📊 Dashboard Overview</h5>
  <div class="btn-group shadow-sm" id="period-tabs" role="group">
    <button class="btn btn-sm btn-primary active" data-period="today">Today</button>
    <button class="btn btn-sm btn-outline-primary" data-period="week">This Week</button>
    <button class="btn btn-sm btn-outline-primary" data-period="month">This Month</button>
    <button class="btn btn-sm btn-outline-primary" data-period="year">This Year</button>
    <button class="btn btn-sm btn-outline-primary" data-period="all">All Time</button>
  </div>
  <span class="text-muted small" id="period-label">Today · <?= date('d M Y') ?></span>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     DYNAMIC STAT CARDS  (updated by JS on period change)
════════════════════════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-4" id="stat-cards">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100 stat-card bookings">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="display-6">📋</div>
        <div>
          <div class="h3 mb-0 fw-bold" id="sc-total">…</div>
          <div class="text-muted small">Total Bookings</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100 stat-card completed">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="display-6">✅</div>
        <div>
          <div class="h3 mb-0 fw-bold text-success" id="sc-completed">…</div>
          <div class="text-muted small">Completed</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100 stat-card pending">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="display-6">⏳</div>
        <div>
          <div class="h3 mb-0 fw-bold text-danger" id="sc-pending">…</div>
          <div class="text-muted small">Pending</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100 stat-card drivers">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="display-6">💶</div>
        <div>
          <div class="h3 mb-0 fw-bold text-success" id="sc-revenue">…</div>
          <div class="text-muted small">Revenue (€)</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     CHARTS ROW
════════════════════════════════════════════════════════════════════════════ -->
<div class="row g-4 mb-4">

  <!-- Trend chart (bar) -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span>📈 Bookings Trend</span>
        <span class="badge bg-primary" id="trend-badge">Last 30 days</span>
      </div>
      <div class="card-body">
        <canvas id="trendChart" height="120"></canvas>
      </div>
    </div>
  </div>

  <!-- Status doughnut -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-bold">🍩 Status Breakdown</div>
      <div class="card-body d-flex flex-column align-items-center justify-content-center">
        <canvas id="statusChart" style="max-height:200px"></canvas>
        <div class="mt-3 w-100" id="status-legend"></div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     STATUS TABLE + QUICK ACTIONS
════════════════════════════════════════════════════════════════════════════ -->
<div class="row g-4 mb-4">

  <!-- Status count table -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-bold">📊 Bookings by Status</div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0" id="status-table">
          <thead class="table-light">
            <tr><th>Status</th><th class="text-end">Count</th><th class="text-end">%</th></tr>
          </thead>
          <tbody id="status-tbody">
            <tr><td colspan="3" class="text-center text-muted p-3">Loading…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Quick actions + permanent stats -->
  <div class="col-md-6 d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-bold">⚡ Quick Actions</div>
      <div class="card-body d-flex flex-wrap gap-2">
        <a href="bookings.php?action=new" class="btn btn-warning"><i class="fas fa-plus me-1"></i>New Booking</a>
        <a href="dispatch.php"            class="btn btn-primary"><i class="fas fa-broadcast-tower me-1"></i>Dispatch</a>
        <a href="drivers.php?action=new"  class="btn btn-info text-white"><i class="fas fa-id-badge me-1"></i>Add Driver</a>
        <a href="vehicles.php?action=new" class="btn btn-secondary"><i class="fas fa-car me-1"></i>Add Vehicle</a>
        <a href="reports.php"             class="btn btn-outline-dark"><i class="fas fa-chart-bar me-1"></i>Reports</a>
      </div>
    </div>
    <div class="card border-0 shadow-sm flex-fill">
      <div class="card-header bg-white fw-bold">🔢 All-time Totals</div>
      <div class="card-body">
        <div class="d-flex justify-content-between border-bottom py-2">
          <span>Total bookings ever</span><strong><?= number_format($total_bookings) ?></strong>
        </div>
        <div class="d-flex justify-content-between border-bottom py-2">
          <span>Total revenue (completed)</span><strong class="text-success"><?= number_format($total_revenue,2,',','.') ?> €</strong>
        </div>
        <div class="d-flex justify-content-between border-bottom py-2">
          <span>Drivers active now</span><strong><?= $active_drivers ?></strong>
        </div>
        <div class="d-flex justify-content-between py-2">
          <span>Drivers available now</span><strong class="text-success"><?= $drivers_avail ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     TODAY'S BOOKINGS TABLE
════════════════════════════════════════════════════════════════════════════ -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span class="fw-bold">📅 Today's Bookings — <?= date('d M Y') ?> (<?= count($today_list) ?>)</span>
    <a href="bookings.php" class="btn btn-sm btn-outline-dark">View All</a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($today_list)): ?>
    <div class="text-center p-4 text-muted">No bookings for today yet.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Ref</th><th>Time</th><th>Customer</th><th>Pickup</th><th>Dropoff</th><th>Driver</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($today_list as $b): ?>
          <tr>
            <td><strong><?= $b['booking_ref'] ?></strong></td>
            <td><?= date('H:i', strtotime($b['pickup_datetime'])) ?></td>
            <td><?= htmlspecialchars($b['customer_name']) ?><br><small class="text-muted"><?= $b['customer_phone'] ?></small></td>
            <td class="small"><?= htmlspecialchars(mb_substr($b['pickup_address'],0,30)) ?>…</td>
            <td class="small"><?= htmlspecialchars(mb_substr($b['dropoff_address'],0,30)) ?>…</td>
            <td><?= $b['driver_name'] ? htmlspecialchars($b['driver_name']) : '<span class="text-muted">—</span>' ?></td>
            <td><?= status_badge($b['status']) ?></td>
            <td><a href="booking_edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     RECENT BOOKINGS TABLE
════════════════════════════════════════════════════════════════════════════ -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold">🕐 Recent Bookings</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Ref</th><th>Customer</th><th>Pickup Date</th><th>Route</th><th>Vehicle</th><th>Status</th><th>Fare</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $b): ?>
          <tr>
            <td><strong><?= $b['booking_ref'] ?></strong></td>
            <td><?= htmlspecialchars($b['customer_name']) ?></td>
            <td><?= format_datetime($b['pickup_datetime']) ?></td>
            <td class="small"><?= htmlspecialchars(mb_substr($b['pickup_address'],0,20)) ?>… → <?= htmlspecialchars(mb_substr($b['dropoff_address'],0,20)) ?>…</td>
            <td><?= ucfirst($b['vehicle_type']) ?></td>
            <td><?= status_badge($b['status']) ?></td>
            <td><?= $b['fare'] ? number_format($b['fare'],2,',','.').' €' : '—' ?></td>
            <td><a href="booking_edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════
     CHART.JS + AJAX PERIOD LOGIC
════════════════════════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Static PHP data injected for first render ─────────────────────────────
<?php
// Pre-compute today's stats for instant first render (no AJAX flash)
$today_where = "DATE(pickup_datetime) = CURDATE()";
$t_total     = (int)$pdo->query("SELECT COUNT(*) FROM td_bookings WHERE $today_where")->fetchColumn();
$t_revenue   = (float)$pdo->query("SELECT COALESCE(SUM(fare),0) FROM td_bookings WHERE $today_where AND status='completed'")->fetchColumn();
$t_statuses  = [];
$all_st = ['pending','confirmed','assigned','in_progress','completed','cancelled','no_show'];
foreach ($all_st as $st) $t_statuses[$st] = 0;
$t_sc = $pdo->query("SELECT status, COUNT(*) as cnt FROM td_bookings WHERE $today_where GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
foreach ($t_sc as $row) $t_statuses[$row['status']] = (int)$row['cnt'];
// Hourly trend
$t_hours = $pdo->query("SELECT HOUR(pickup_datetime) as h, COUNT(*) as cnt FROM td_bookings WHERE $today_where GROUP BY h")->fetchAll(PDO::FETCH_ASSOC);
$hmap = []; foreach ($t_hours as $r) $hmap[(int)$r['h']] = (int)$r['cnt'];
$t_labels = []; $t_data = [];
for ($h=0;$h<24;$h++) { $t_labels[]=sprintf('%02d:00',$h); $t_data[]=$hmap[$h]??0; }
?>
var INIT_STATS = {
    total    : <?= $t_total ?>,
    revenue  : <?= round($t_revenue,2) ?>,
    statuses : <?= json_encode($t_statuses) ?>,
    trend_labels: <?= json_encode($t_labels) ?>,
    trend_data  : <?= json_encode($t_data) ?>
};
var DEFAULT_TREND_LABELS = <?= json_encode($trend_labels) ?>;
var DEFAULT_TREND_DATA   = <?= json_encode($trend_data) ?>;
var DEFAULT_STATUS       = <?= json_encode($status_counts) ?>;

// Status colours
var STATUS_META = {
  pending     : { label:'Pending',     color:'#dc3545' },
  confirmed   : { label:'Confirmed',   color:'#0d6efd' },
  assigned    : { label:'Assigned',    color:'#fd7e14' },
  in_progress : { label:'In Progress', color:'#6f42c1' },
  completed   : { label:'Completed',   color:'#198754' },
  cancelled   : { label:'Cancelled',   color:'#6c757d' },
  no_show     : { label:'No Show',     color:'#adb5bd' }
};

// ── Chart instances ───────────────────────────────────────────────────────
var trendChart, statusChart;

function buildTrendChart(labels, data) {
  var ctx = document.getElementById('trendChart').getContext('2d');
  if (trendChart) trendChart.destroy();
  trendChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Bookings',
        data: data,
        backgroundColor: 'rgba(13,110,253,0.65)',
        borderColor: '#0d6efd',
        borderWidth: 1,
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { ticks: { maxTicksLimit: 14, maxRotation: 45 } }
      }
    }
  });
}

function buildStatusChart(statusObj) {
  var labels = [], colors = [], vals = [];
  Object.keys(STATUS_META).forEach(function(k) {
    labels.push(STATUS_META[k].label);
    colors.push(STATUS_META[k].color);
    vals.push(statusObj[k] || 0);
  });

  var ctx = document.getElementById('statusChart').getContext('2d');
  if (statusChart) statusChart.destroy();
  statusChart = new Chart(ctx, {
    type: 'doughnut',
    data: { labels: labels, datasets: [{ data: vals, backgroundColor: colors, borderWidth: 2 }] },
    options: {
      responsive: true,
      plugins: { legend: { display: false } }
    }
  });

  // Custom legend
  var total = vals.reduce(function(a,b){ return a+b; }, 0);
  var legend = document.getElementById('status-legend');
  legend.innerHTML = labels.map(function(l, i) {
    var pct = total ? Math.round(vals[i]/total*100) : 0;
    return '<div class="d-flex justify-content-between align-items-center py-1 border-bottom">' +
      '<span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:'+colors[i]+';margin-right:6px"></span>'+l+'</span>' +
      '<strong>'+vals[i]+'</strong></div>';
  }).join('');
}

function buildStatusTable(statusObj) {
  var total = Object.values(statusObj).reduce(function(a,b){ return a+b; }, 0);
  var rows = Object.keys(STATUS_META).map(function(k) {
    var cnt = statusObj[k] || 0;
    var pct = total ? (cnt/total*100).toFixed(1) : '0.0';
    var badge = '<span style="background:'+STATUS_META[k].color+';color:#fff;padding:2px 8px;border-radius:99px;font-size:12px">'+STATUS_META[k].label+'</span>';
    return '<tr><td>'+badge+'</td><td class="text-end fw-bold">'+cnt+'</td><td class="text-end text-muted small">'+pct+'%</td></tr>';
  });
  document.getElementById('status-tbody').innerHTML = rows.join('');
}

function updateStatCards(d) {
  document.getElementById('sc-total').textContent     = d.total;
  document.getElementById('sc-completed').textContent = d.statuses.completed || 0;
  document.getElementById('sc-pending').textContent   = d.statuses.pending   || 0;
  var rev = parseFloat(d.revenue || 0);
  document.getElementById('sc-revenue').textContent   = rev.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2});
}

// ── Period labels ─────────────────────────────────────────────────────────
var PERIOD_LABELS = {
  today: 'Today · <?= date("d M Y") ?>',
  week : 'This Week (Mon–Sun)',
  month: 'This Month · <?= date("M Y") ?>',
  year : 'This Year · <?= date("Y") ?>',
  all  : 'All Time'
};
var TREND_BADGES = {
  today: 'Hourly today',
  week : 'Daily this week',
  month: 'Daily this month',
  year : 'Monthly this year',
  all  : 'Monthly all time'
};

// ── Load data from server ─────────────────────────────────────────────────
function loadPeriod(period) {
  fetch('/admin/dashboard_stats.php?period=' + period)
    .then(function(r){ return r.json(); })
    .then(function(d) {
      updateStatCards(d);
      buildTrendChart(d.trend_labels, d.trend_data);
      buildStatusChart(d.statuses);
      buildStatusTable(d.statuses);
      document.getElementById('period-label').textContent = PERIOD_LABELS[period] || '';
      document.getElementById('trend-badge').textContent  = TREND_BADGES[period]  || '';
    })
    .catch(function(e){ console.error('Stats load error', e); });
}

// ── Tab buttons ───────────────────────────────────────────────────────────
document.querySelectorAll('#period-tabs .btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('#period-tabs .btn').forEach(function(b){
      b.classList.remove('btn-primary');
      b.classList.add('btn-outline-primary');
    });
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-primary');
    loadPeriod(btn.dataset.period);
  });
});

// ── Initial render with pre-computed PHP data (no AJAX flash) ─────────────
updateStatCards(INIT_STATS);
buildTrendChart(INIT_STATS.trend_labels, INIT_STATS.trend_data);
buildStatusChart(INIT_STATS.statuses);
buildStatusTable(INIT_STATS.statuses);
</script>

<?php require_once 'footer.php'; ?>
