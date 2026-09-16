<?php
/**
 * Super Admin Panel - TaxisDispatch SaaS Manager
 * Upload this file to /superadmin/index.php on your server
 */

define('SA_PASSWORD', password_hash('SuperAdmin2024!', PASSWORD_DEFAULT));
define('SA_VERSION', '1.0');

session_start();

// ─── AUTH ────────────────────────────────────────────────────────────────────
function isLoggedIn() { return !empty($_SESSION['superadmin_auth']); }
function logout() { session_destroy(); header('Location: ?page=login'); exit; }

if (isset($_POST['sa_login'])) {
    if (password_verify($_POST['password'] ?? '', SA_PASSWORD) && 
        ($_POST['username'] ?? '') === 'superadmin') {
        $_SESSION['superadmin_auth'] = true;
        header('Location: ?page=dashboard'); exit;
    }
    $loginError = 'Invalid username or password.';
}

if (isset($_GET['logout'])) logout();

$page = $_GET['page'] ?? (isLoggedIn() ? 'dashboard' : 'login');
if (!isLoggedIn() && $page !== 'login') { header('Location: ?page=login'); exit; }

// ─── MULTI-TENANT DB CONFIG ───────────────────────────────────────────────────
// Read master config from parent site
$parentConfig = dirname(dirname(__FILE__)) . '/admin/config.php';
$dbHost = 'localhost'; $dbUser = ''; $dbPass = ''; $dbName = '';

if (file_exists($parentConfig)) {
    $configContent = file_get_contents($parentConfig);
    preg_match("/define\s*\(\s*['"]DB_HOST['"]\s*,\s*['"](.*?)['"]\s*\)/", $configContent, $m);
    $dbHost = $m[1] ?? 'localhost';
    preg_match("/define\s*\(\s*['"]DB_USER['"]\s*,\s*['"](.*?)['"]\s*\)/", $configContent, $m);
    $dbUser = $m[1] ?? '';
    preg_match("/define\s*\(\s*['"]DB_PASS['"]\s*,\s*['"](.*?)['"]\s*\)/", $configContent, $m);
    $dbPass = $m[1] ?? '';
    preg_match("/define\s*\(\s*['"]DB_NAME['"]\s*,\s*['"](.*?)['"]\s*\)/", $configContent, $m);
    $dbName = $m[1] ?? '';
}

function getDB($host='localhost',$user='',$pass='',$name='') {
    try { 
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(Exception $e) { return null; }
}

// Try to connect with parent config
global $dbHost, $dbUser, $dbPass, $dbName;
$db = getDB($dbHost, $dbUser, $dbPass, $dbName);

// ─── PAGE HANDLERS ────────────────────────────────────────────────────────────
ob_start();

if ($page === 'login') {
    ?>
    <div class="d-flex justify-content-center align-items-center" style="min-height:80vh">
    <div class="card shadow" style="width:380px">
      <div class="card-header bg-danger text-white text-center py-3">
        <h4 class="mb-0">🔐 Super Admin Login</h4>
        <small>TaxisDispatch SaaS Manager</small>
      </div>
      <div class="card-body p-4">
        <?php if(isset($loginError)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="superadmin" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" name="sa_login" class="btn btn-danger w-100 py-2">
            🚀 Login to Super Admin
          </button>
        </form>
      </div>
    </div></div>
    <?php
}

elseif ($page === 'dashboard') {
    $stats = [];
    if ($db) {
        $stats['bookings'] = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $stats['drivers'] = $db->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
        $stats['vehicles'] = $db->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
        $stats['revenue'] = $db->query("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE status='completed'")->fetchColumn();
    }
    ?>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card bg-warning text-dark">
          <div class="card-body text-center">
            <h2><?= $stats['bookings'] ?? 'N/A' ?></h2>
            <div>📅 Total Bookings</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body text-center">
            <h2><?= $stats['drivers'] ?? 'N/A' ?></h2>
            <div>🚗 Active Drivers</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body text-center">
            <h2><?= $stats['vehicles'] ?? 'N/A' ?></h2>
            <div>🚕 Vehicles</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body text-center">
            <h2>€<?= number_format($stats['revenue'] ?? 0, 2) ?></h2>
            <div>💰 Total Revenue</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="card">
      <div class="card-header bg-dark text-white"><h5 class="mb-0">📋 Recent Bookings</h5></div>
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>ID</th><th>Customer</th><th>From</th><th>To</th><th>Date</th><th>Status</th><th>Price</th></tr></thead>
          <tbody>
          <?php if($db):
            $bookings = $db->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            foreach($bookings as $b): ?>
            <tr>
              <td>#<?= $b['id'] ?></td>
              <td><?= htmlspecialchars($b['passenger_name'] ?? $b['customer_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars(substr($b['pickup_location'] ?? $b['pickup'] ?? '', 0, 30)) ?></td>
              <td><?= htmlspecialchars(substr($b['dropoff_location'] ?? $b['dropoff'] ?? '', 0, 30)) ?></td>
              <td><?= htmlspecialchars($b['pickup_date'] ?? $b['date'] ?? '') ?></td>
              <td><span class="badge bg-<?= $b['status']==='completed'?'success':($b['status']==='pending'?'warning':'info') ?>"><?= $b['status'] ?></span></td>
              <td>€<?= number_format($b['total_price'] ?? 0, 2) ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center text-muted">Database not connected</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
}

elseif ($page === 'bookings') {
    $filterStatus = $_GET['status'] ?? '';
    $search = $_GET['q'] ?? '';
    $query = "SELECT * FROM bookings WHERE 1=1";
    $params = [];
    if ($filterStatus) { $query .= " AND status = ?"; $params[] = $filterStatus; }
    if ($search) { $query .= " AND (passenger_name LIKE ? OR pickup_location LIKE ? OR dropoff_location LIKE ?)"; 
                   $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
    $query .= " ORDER BY id DESC";
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>📅 All Bookings</h4>
      <form class="d-flex gap-2">
        <input type="hidden" name="page" value="bookings">
        <input type="text" name="q" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <select name="status" class="form-select" style="width:150px">
          <option value="">All Status</option>
          <?php foreach(['pending','confirmed','assigned','completed','cancelled'] as $s): ?>
          <option value="<?=$s?>" <?=$filterStatus===$s?'selected':''?>><?=ucfirst($s)?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary">Filter</button>
      </form>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>ID</th><th>Customer</th><th>Phone</th><th>Route</th><th>Date/Time</th><th>Status</th><th>Price</th></tr></thead>
          <tbody>
          <?php if($db):
            $stmt = $db->prepare($query); $stmt->execute($params);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($bookings as $b): ?>
            <tr>
              <td><strong>#<?=$b['id']?></strong></td>
              <td><?=htmlspecialchars($b['passenger_name']??$b['customer_name']??'N/A')?></td>
              <td><?=htmlspecialchars($b['passenger_phone']??$b['phone']??'')?></td>
              <td>
                <small>📍 <?=htmlspecialchars(substr($b['pickup_location']??$b['pickup']??'',0,25))?></small><br>
                <small>🏁 <?=htmlspecialchars(substr($b['dropoff_location']??$b['dropoff']??'',0,25))?></small>
              </td>
              <td><?=htmlspecialchars($b['pickup_date']??$b['date']??'')?> <?=htmlspecialchars($b['pickup_time']??$b['time']??'')?></td>
              <td><span class="badge bg-<?=$b['status']==='completed'?'success':($b['status']==='pending'?'warning':'info')?>"><?=$b['status']?></span></td>
              <td>€<?=number_format($b['total_price']??0,2)?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center text-muted">Database not connected</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
}

elseif ($page === 'drivers') {
    ?>
    <h4 class="mb-3">🚗 Drivers Management</h4>
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th>Rating</th><th>Trips</th></tr></thead>
          <tbody>
          <?php if($db):
            $drivers = $db->query("SELECT * FROM drivers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach($drivers as $d): ?>
            <tr>
              <td><?=$d['id']?></td>
              <td><?=htmlspecialchars($d['name']??$d['full_name']??'N/A')?></td>
              <td><?=htmlspecialchars($d['phone']??'')?></td>
              <td><?=htmlspecialchars($d['email']??'')?></td>
              <td><span class="badge bg-<?=$d['status']==='available'||$d['status']==='active'?'success':'secondary'?>"><?=$d['status']??'N/A'?></span></td>
              <td>⭐ <?=number_format($d['rating']??5,1)?></td>
              <td><?=$d['total_trips']??0?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" class="text-center">Database not connected</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
}

elseif ($page === 'system') {
    ?>
    <h4 class="mb-3">⚙️ System Information</h4>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-dark text-white">Server Info</div>
          <div class="card-body">
            <table class="table table-sm mb-0">
              <tr><th>PHP Version</th><td><?=PHP_VERSION?></td></tr>
              <tr><th>Server</th><td><?=$_SERVER['SERVER_SOFTWARE']??'N/A'?></td></tr>
              <tr><th>Document Root</th><td><?=$_SERVER['DOCUMENT_ROOT']??'N/A'?></td></tr>
              <tr><th>Server IP</th><td><?=$_SERVER['SERVER_ADDR']??'N/A'?></td></tr>
              <tr><th>DB Connected</th><td><?=$db?'✅ Yes':'❌ No'?></td></tr>
              <tr><th>DB Host</th><td><?=$dbHost?></td></tr>
              <tr><th>DB Name</th><td><?=$dbName?></td></tr>
              <tr><th>Disk Free</th><td><?=number_format(disk_free_space('/')/1073741824,2)?> GB</td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-dark text-white">PHP Extensions</div>
          <div class="card-body">
            <table class="table table-sm mb-0">
              <?php foreach(['pdo','pdo_mysql','gd','curl','json','mbstring','openssl'] as $ext): ?>
              <tr><th><?=$ext?></th><td><?=extension_loaded($ext)?'✅':'❌'?></td></tr>
              <?php endforeach; ?>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php
}

$content = ob_get_clean();

// ─── LAYOUT ───────────────────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Super Admin - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
.sidebar { min-height:100vh; background:#0d1117; width:220px; flex-shrink:0; }
.sidebar .nav-link { color:rgba(255,255,255,.7); padding:.6rem 1rem; border-radius:.4rem; margin:.1rem 0; font-size:.9rem; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { color:#fff; background:rgba(220,53,69,.3); }
.sidebar .nav-link i { width:20px; }
.main-content { background:#f0f2f5; min-height:100vh; flex:1; }
</style>
</head>
<body>
<div class="d-flex">
<?php if(isLoggedIn()): ?>
<!-- Sidebar -->
<div class="sidebar p-3">
  <div class="text-white fw-bold mb-4 d-flex align-items-center gap-2">
    <span class="fs-4">🛡️</span>
    <div>
      <div>Super Admin</div>
      <div class="small text-muted fw-normal">TaxisDispatch</div>
    </div>
  </div>
  <nav class="nav flex-column">
    <a href="?page=dashboard" class="nav-link <?=$page==='dashboard'?'active':''?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="?page=bookings" class="nav-link <?=$page==='bookings'?'active':''?>"><i class="fas fa-calendar-check"></i> Bookings</a>
    <a href="?page=drivers" class="nav-link <?=$page==='drivers'?'active':''?>"><i class="fas fa-id-badge"></i> Drivers</a>
    <a href="?page=system" class="nav-link <?=$page==='system'?'active':''?>"><i class="fas fa-server"></i> System</a>
    <hr class="border-secondary">
    <a href="?logout=1" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>
  <div class="mt-auto pt-4 text-muted small text-center">v<?=SA_VERSION?></div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="main-content p-4 flex-grow-1">
<?php if(isLoggedIn()): ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">
      <?php 
        $titles = ['dashboard'=>'📊 Dashboard','bookings'=>'📅 Bookings',
                   'drivers'=>'🚗 Drivers','system'=>'⚙️ System'];
        echo $titles[$page] ?? ucfirst($page);
      ?>
    </h3>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-success">● Live</span>
      <a href="?logout=1" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>
  </div>
<?php endif; ?>
  <?= $content ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
