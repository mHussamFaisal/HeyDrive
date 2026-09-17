<?php
// All POST/GET logic runs BEFORE any HTML output — fixes blank page on redirect
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();
$err = '';
$msg = $_GET['msg'] ?? '';

// Self-healing schema migration to guarantee missing columns never cause SQL errors
try {
    $pdo->exec("ALTER TABLE `td_vehicles`
        ADD COLUMN IF NOT EXISTS `name` varchar(255) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `assigned_driver_id` int(11) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `registration_mark` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `technical_inspection` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `inspection_expiry_date` date DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `body_type` varchar(50) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `keeper_name` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `keeper_address` varchar(255) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `image` varchar(255) DEFAULT ''");
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE `td_drivers`
        ADD COLUMN IF NOT EXISTS `unique_id` varchar(50) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `photo` varchar(255) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `activity_status` enum('Available','Unavailable','On Job','Break') DEFAULT 'Available',
        ADD COLUMN IF NOT EXISTS `fleet_operator` varchar(100) DEFAULT 'Unassigned',
        ADD COLUMN IF NOT EXISTS `language` varchar(50) DEFAULT 'English',
        ADD COLUMN IF NOT EXISTS `timezone` varchar(100) DEFAULT 'UTC+02:00 Berlin',
        ADD COLUMN IF NOT EXISTS `address` text,
        ADD COLUMN IF NOT EXISTS `city` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `country` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `dob` date DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `license_expiry_date` date DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `badge_number` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `badge_expiry_date` date DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `emergency_contact_name` varchar(100) DEFAULT '',
        ADD COLUMN IF NOT EXISTS `emergency_contact_phone` varchar(50) DEFAULT ''");
} catch (Exception $e) {}

// Fetch available vehicles for dropdown
$vehicles_list = [];
try {
    $vehicles_list = $pdo->query("SELECT id, name, make, model, license_plate, type FROM td_vehicles ORDER BY make, model")->fetchAll();
} catch (Exception $e) {}

// ── POST: Add / Edit / Delete Driver ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_driver') {
        $driver_id             = intval($_POST['driver_id'] ?? 0);
        $user_id               = intval($_POST['user_id'] ?? 0);
        $display_name          = trim($_POST['display_name'] ?? '');
        $unique_id             = trim($_POST['unique_id'] ?? '');
        $email                 = strtolower(trim($_POST['email'] ?? ''));
        $password_raw          = $_POST['password'] ?? '';
        $confirm_password      = $_POST['confirm_password'] ?? '';
        $status                = trim($_POST['status'] ?? 'active');
        $activity_status       = trim($_POST['activity_status'] ?? 'Available');
        $language              = trim($_POST['language'] ?? 'English');
        $timezone              = trim($_POST['timezone'] ?? 'UTC+02:00 Berlin');
        $fleet_operator        = trim($_POST['fleet_operator'] ?? 'Unassigned');
        $phone                 = trim($_POST['phone'] ?? '');
        $address               = trim($_POST['address'] ?? '');
        $city                  = trim($_POST['city'] ?? '');
        $country               = trim($_POST['country'] ?? '');
        $dob                   = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $license_number        = trim($_POST['license_number'] ?? '');
        $license_expiry_date   = !empty($_POST['license_expiry_date']) ? $_POST['license_expiry_date'] : null;
        $badge_number          = trim($_POST['badge_number'] ?? '');
        $badge_expiry_date     = !empty($_POST['badge_expiry_date']) ? $_POST['badge_expiry_date'] : null;
        $vehicle_id            = !empty($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null;
        $emergency_name        = trim($_POST['emergency_contact_name'] ?? '');
        $emergency_phone       = trim($_POST['emergency_contact_phone'] ?? '');
        $notes                 = trim($_POST['notes'] ?? '');
        $photo_path            = trim($_POST['current_photo'] ?? '');

        if (empty($display_name) || empty($email)) {
            $err = "Display name and email are required.";
        } elseif ($driver_id === 0 && empty($password_raw)) {
            $err = "Password is required for new drivers.";
        } elseif (!empty($password_raw) && $password_raw !== $confirm_password) {
            $err = "Passwords do not match.";
        } else {
            // Check unique email
            if ($user_id > 0) {
                $chk = $pdo->prepare("SELECT id FROM td_users WHERE email = ? AND id != ?");
                $chk->execute([$email, $user_id]);
            } else {
                $chk = $pdo->prepare("SELECT id FROM td_users WHERE email = ?");
                $chk->execute([$email]);
            }
            if ($chk->fetch()) {
                $err = "A user with this email address already exists.";
            } else {
                // Photo upload
                if (!empty($_FILES['photo']['name'])) {
                    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
                    $ftype   = mime_content_type($_FILES['photo']['tmp_name']);
                    if (in_array($ftype, $allowed)) {
                        $ext        = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                        $filename   = 'driver_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
                        $upload_dir = dirname(__DIR__) . '/uploads/drivers/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename)) {
                            $photo_path = 'uploads/drivers/' . $filename;
                        }
                    }
                }

                try {
                    $pdo->beginTransaction();
                    $user_status = ($status === 'active' || $status === 'Approved') ? 'active' : 'inactive';

                    if ($user_id > 0) {
                        // Update td_users
                        if (!empty($password_raw)) {
                            $pdo->prepare("UPDATE td_users SET name=?, email=?, phone=?, password=?, status=? WHERE id=?")
                                ->execute([$display_name, $email, $phone, password_hash($password_raw, PASSWORD_DEFAULT), $user_status, $user_id]);
                        } else {
                            $pdo->prepare("UPDATE td_users SET name=?, email=?, phone=?, status=? WHERE id=?")
                                ->execute([$display_name, $email, $phone, $user_status, $user_id]);
                        }

                        // Update or insert td_drivers
                        if ($driver_id > 0) {
                            $pdo->prepare("UPDATE td_drivers SET 
                                unique_id=?, photo=?, language=?, timezone=?, fleet_operator=?, 
                                address=?, city=?, country=?, dob=?, license_number=?, license_expiry_date=?, 
                                badge_number=?, badge_expiry_date=?, vehicle_id=?, emergency_contact_name=?, 
                                emergency_contact_phone=?, notes=?, status=?, activity_status=?
                                WHERE id=?")
                                ->execute([
                                    $unique_id, $photo_path, $language, $timezone, $fleet_operator,
                                    $address, $city, $country, $dob, $license_number, $license_expiry_date,
                                    $badge_number, $badge_expiry_date, $vehicle_id, $emergency_name,
                                    $emergency_phone, $notes, $status, $activity_status, $driver_id
                                ]);
                        } else {
                            $pdo->prepare("INSERT INTO td_drivers 
                                (user_id, unique_id, photo, language, timezone, fleet_operator, 
                                 address, city, country, dob, license_number, license_expiry_date, 
                                 badge_number, badge_expiry_date, vehicle_id, emergency_contact_name, 
                                 emergency_contact_phone, notes, status, activity_status) 
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                                ->execute([
                                    $user_id, $unique_id, $photo_path, $language, $timezone, $fleet_operator,
                                    $address, $city, $country, $dob, $license_number, $license_expiry_date,
                                    $badge_number, $badge_expiry_date, $vehicle_id, $emergency_name,
                                    $emergency_phone, $notes, $status, $activity_status
                                ]);
                        }
                    } else {
                        // New user & driver
                        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
                        $pdo->prepare("INSERT INTO td_users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'driver', ?)")
                            ->execute([$display_name, $email, $phone, $password_hash, $user_status]);
                        $new_uid = $pdo->lastInsertId();

                        if (empty($unique_id)) {
                            $unique_id = 'DRV-' . (1000 + $new_uid);
                        }

                        $pdo->prepare("INSERT INTO td_drivers 
                            (user_id, unique_id, photo, language, timezone, fleet_operator, 
                             address, city, country, dob, license_number, license_expiry_date, 
                             badge_number, badge_expiry_date, vehicle_id, emergency_contact_name, 
                             emergency_contact_phone, notes, status, activity_status) 
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                            ->execute([
                                $new_uid, $unique_id, $photo_path, $language, $timezone, $fleet_operator,
                                $address, $city, $country, $dob, $license_number, $license_expiry_date,
                                $badge_number, $badge_expiry_date, $vehicle_id, $emergency_name,
                                $emergency_phone, $notes, $status, $activity_status
                            ]);
                    }

                    $pdo->commit();
                    header("Location: " . APP_URL . "/admin/drivers.php?msg=" . ($driver_id > 0 ? 'updated' : 'added'));
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $err = "Database error: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    } elseif ($action === 'delete_driver') {
        $did = intval($_POST['driver_id'] ?? 0);
        if ($did > 0) {
            $row = $pdo->prepare("SELECT user_id, photo FROM td_drivers WHERE id=?");
            $row->execute([$did]);
            $dr = $row->fetch();
            if ($dr) {
                if (!empty($dr['photo']) && file_exists(dirname(__DIR__) . '/' . $dr['photo'])) {
                    @unlink(dirname(__DIR__) . '/' . $dr['photo']);
                }
                $pdo->prepare("DELETE FROM td_drivers WHERE id=?")->execute([$did]);
                $pdo->prepare("DELETE FROM td_users WHERE id=? AND role='driver'")->execute([$dr['user_id']]);
            }
            header("Location: " . APP_URL . "/admin/drivers.php?msg=deleted");
            exit;
        }
    } elseif ($action === 'toggle_activity') {
        $did = intval($_POST['driver_id'] ?? 0);
        $act = trim($_POST['activity_status'] ?? 'Available');
        $pdo->prepare("UPDATE td_drivers SET activity_status=? WHERE id=?")->execute([$act, $did]);
        header("Location: " . APP_URL . "/admin/drivers.php?msg=status_updated");
        exit;
    }
}

// ── GET: Fetch Drivers List ──────────────────────────────────────────────────
$drivers = [];
try {
    $drivers = $pdo->query("SELECT 
        COALESCE(d.id, 0) as driver_id,
        COALESCE(d.unique_id, CONCAT('DRV-', 1000 + u.id)) as unique_id,
        COALESCE(d.photo, '') as photo,
        COALESCE(d.language, 'English') as language,
        COALESCE(d.timezone, 'UTC+02:00 Berlin') as timezone,
        COALESCE(d.fleet_operator, '') as fleet_operator,
        COALESCE(d.license_number, '') as license_number,
        COALESCE(d.license_expiry_date, '') as license_expiry_date,
        COALESCE(d.activity_status, 'Available') as activity_status,
        COALESCE(d.address, '') as address,
        COALESCE(d.city, '') as city,
        COALESCE(d.country, '') as country,
        COALESCE(d.dob, '') as dob,
        COALESCE(d.badge_number, '') as badge_number,
        COALESCE(d.badge_expiry_date, '') as badge_expiry_date,
        COALESCE(d.emergency_contact_name, '') as emergency_contact_name,
        COALESCE(d.emergency_contact_phone, '') as emergency_contact_phone,
        COALESCE(d.vehicle_id, 0) as vehicle_id,
        COALESCE(d.notes, '') as notes,
        u.id as user_id, u.name as display_name, u.email, u.phone, u.status as user_status, COALESCE(u.role, 'driver') as role,
        COALESCE(v.name, CONCAT(v.make, ' ', v.model)) as vehicle_name,
        COALESCE(v.registration_mark, v.license_plate, '') as registration_mark
        FROM td_users u
        LEFT JOIN td_drivers d ON d.user_id = u.id
        LEFT JOIN td_vehicles v ON d.vehicle_id = v.id
        WHERE u.role = 'driver'
        ORDER BY u.id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $err = "Database query error: " . htmlspecialchars($e->getMessage());
}

$page_title = 'Drivers';
require_once 'header.php';
?>

<!-- Breadcrumb / Toolbar Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Users</a></li>
        <li class="breadcrumb-item active" aria-current="page">Drivers</li>
      </ol>
    </nav>
    <h4 class="fw-bold mb-0 text-dark">
      <i class="fas fa-id-badge text-warning me-2"></i>Drivers
    </h4>
  </div>

  <!-- Actions / Buttons Bar -->
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="btn-group btn-group-sm">
      <button type="button" class="btn btn-outline-secondary btn-sm" title="View Options"><i class="far fa-eye"></i></button>
      <a href="drivers.php" class="btn btn-outline-secondary btn-sm" title="Refresh"><i class="fas fa-redo"></i></a>
      <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="exportDriversCSV()"><i class="fas fa-download me-1"></i> Export</button>
    </div>
    <button type="button" class="btn btn-success btn-sm px-3 shadow-sm d-flex align-items-center gap-1 fw-semibold" onclick="openDriverModal()" style="border-radius:.4rem; height:32px;">
      <i class="fas fa-plus"></i> Add new
    </button>
    <div class="position-relative">
      <input type="text" id="driverSearch" class="form-control form-control-sm ps-3 pe-4" placeholder="Search..." style="width:180px; border-radius:.4rem;" onkeyup="filterDriversTable()">
      <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted small"></i>
    </div>
  </div>
</div>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm">
  <?= $msg==='added' ? '✅ Driver registered successfully!' : ($msg==='updated' ? '✅ Driver updated successfully!' : ($msg==='deleted' ? '🗑️ Driver removed.' : '✅ Updated.')) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger mb-4 shadow-sm">❌ <?= $err ?></div><?php endif; ?>

<!-- Drivers List Table Card with Horizontal Scroll -->
<div class="card border-0 shadow-sm rounded-3">
  <div class="card-body p-0">
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
      <table class="table table-hover align-middle mb-0" id="driversTable" style="min-width: 1350px; white-space: nowrap;">
        <thead class="table-light small text-muted text-uppercase">
          <tr>
            <th class="ps-3" style="width: 110px; min-width: 110px;">Actions</th>
            <th style="width: 70px; min-width: 70px;">Photo</th>
            <th style="min-width: 160px;">Display name <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 200px;">Email <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 150px;">Mobile number <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 130px;">Unique ID <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 160px;">Driver activity status <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 140px;">Fleet operator <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 110px;">Role</th>
            <th class="pe-3" style="min-width: 110px;">Status</th>
          </tr>
        </thead>
        <tbody class="small" id="driversTableBody">
          <?php if (empty($drivers)): ?>
          <tr>
            <td colspan="10" class="text-center py-5 text-muted">
              <i class="fas fa-id-badge fa-2x mb-2 text-secondary"></i><br>
              No drivers found. Click <strong>+ Add new</strong> above to register your first driver!
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($drivers as $d): ?>
          <tr>
            <!-- Actions matching Image 3: [Eye | Dropdown | Bell] -->
            <td class="ps-3">
              <div class="btn-group btn-group-sm border rounded bg-white shadow-sm" role="group">
                <!-- Preview / View Profile -->
                <a href="driver_view.php?id=<?= $d['driver_id'] ?: $d['user_id'] ?>" class="btn btn-light px-2" title="View Profile">
                  <i class="far fa-eye text-muted"></i>
                </a>
                <!-- Actions Dropdown -->
                <button type="button" class="btn btn-light px-2 dropdown-toggle dropdown-toggle-split border-start border-end" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="visually-hidden">Toggle Actions</span>
                </button>
                <ul class="dropdown-menu shadow border-0">
                  <li>
                    <button type="button" class="dropdown-item" onclick='editDriverModal(<?= json_encode($d, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                      <i class="fas fa-edit me-2 text-primary"></i> Edit
                    </button>
                  </li>
                  <li>
                    <a class="dropdown-item" href="bookings.php?driver_id=<?= $d['driver_id'] ?>">
                      <i class="fas fa-calendar-alt me-2 text-warning"></i> Assigned Jobs
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this driver?');">
                      <input type="hidden" name="action" value="delete_driver">
                      <input type="hidden" name="driver_id" value="<?= $d['driver_id'] ?>">
                      <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-trash-alt me-2"></i> Delete
                      </button>
                    </form>
                  </li>
                </ul>
                <!-- Bell Notification button -->
                <button type="button" class="btn btn-light px-2" title="Notifications" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                  <i class="far fa-bell text-muted"></i>
                </button>
              </div>
            </td>

            <!-- Photo -->
            <td>
              <?php 
              $photo_url = '';
              if (!empty($d['photo']) && file_exists(dirname(__DIR__) . '/' . $d['photo'])) {
                  $photo_url = APP_URL . '/' . htmlspecialchars($d['photo']);
              }
              ?>
              <?php if ($photo_url): ?>
                <img src="<?= $photo_url ?>" alt="Avatar" class="rounded-circle border" style="width:42px;height:42px;object-fit:cover;">
              <?php else: ?>
                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-secondary" style="width:42px;height:42px;font-size:18px;">
                  <i class="fas fa-user"></i>
                </div>
              <?php endif; ?>
            </td>

            <!-- Display Name -->
            <td class="fw-bold text-dark fs-6"><?= htmlspecialchars($d['display_name']) ?></td>

            <!-- Email -->
            <td class="text-muted"><?= htmlspecialchars($d['email']) ?></td>

            <!-- Mobile number -->
            <td><?= htmlspecialchars($d['phone'] ?: '-') ?></td>

            <!-- Unique ID -->
            <td><span class="text-muted"><?= htmlspecialchars($d['unique_id']) ?></span></td>

            <!-- Driver activity status badge -->
            <td>
              <?php if (strtolower($d['activity_status']) === 'available'): ?>
                <span class="badge bg-success px-2 py-1 fw-semibold">Available</span>
              <?php else: ?>
                <span class="badge bg-danger px-2 py-1 fw-semibold">Unavailable</span>
              <?php endif; ?>
            </td>

            <!-- Fleet operator / assigned fleet -->
            <td class="text-muted">
              <?= htmlspecialchars($d['fleet_operator'] ?: ($d['vehicle_name'] ?: '-')) ?>
            </td>

            <!-- Role -->
            <td>
              <span class="badge bg-light text-dark border text-capitalize px-2 py-1"><?= htmlspecialchars($d['role'] ?: 'Driver') ?></span>
            </td>

            <!-- Status -->
            <td class="pe-3">
              <?php if (strtolower($d['user_status']) === 'active'): ?>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fw-semibold">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 fw-semibold"><?= ucfirst($d['user_status'] ?: 'Inactive') ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add / Edit Driver Modal Popup (Matching Image 2 Tabs & Styling) -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" enctype="multipart/form-data" id="driverForm">
        <input type="hidden" name="action" value="save_driver">
        <input type="hidden" name="driver_id" id="drv_driver_id" value="0">
        <input type="hidden" name="user_id" id="drv_user_id" value="0">
        <input type="hidden" name="current_photo" id="drv_current_photo" value="">

        <div class="modal-header border-bottom py-3">
          <h5 class="modal-title fw-bold" id="driverModalLabel"><i class="fas fa-id-badge text-warning me-2"></i>Add new driver</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Nav Tabs (General | Personal | Other) -->
          <ul class="nav nav-pills mb-4 border-bottom pb-2" id="driverModalTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link active fw-bold px-4" id="tab-general" data-bs-toggle="pill" data-bs-target="#content-general" type="button">General</button>
            </li>
            <li class="nav-item">
              <button class="nav-link fw-bold px-4" id="tab-personal" data-bs-toggle="pill" data-bs-target="#content-personal" type="button">Personal</button>
            </li>
            <li class="nav-item">
              <button class="nav-link fw-bold px-4" id="tab-other" data-bs-toggle="pill" data-bs-target="#content-other" type="button">Other</button>
            </li>
          </ul>

          <div class="tab-content" id="driverModalContent">
            <!-- ── TAB 1: General ── -->
            <div class="tab-pane fade show active" id="content-general">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Display name <span class="text-danger">*</span></label>
                  <input type="text" name="display_name" id="drv_display_name" class="form-control" placeholder="Display name" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Unique ID</label>
                  <input type="text" name="unique_id" id="drv_unique_id" class="form-control" placeholder="e.g. DRV-1001">
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small fw-semibold">Email <span class="text-danger">*</span></label>
                  <input type="email" name="email" id="drv_email" class="form-control" placeholder="driver@taxisdispatch.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold" id="drv_pass_label">Password <span class="text-danger">*</span></label>
                  <input type="password" name="password" id="drv_password" class="form-control" placeholder="Password">
                  <div class="form-text small text-muted" id="drv_pass_help" style="display:none;">Leave blank to keep current password.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Confirm password</label>
                  <input type="password" name="confirm_password" id="drv_confirm_password" class="form-control" placeholder="Confirm password">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Driver activity status</label>
                  <select name="activity_status" id="drv_activity_status" class="form-select">
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Fleet operator</label>
                  <input type="text" name="fleet_operator" id="drv_fleet_operator" class="form-control" placeholder="Fleet Operator or Unassigned">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Role</label>
                  <select name="role" id="drv_role" class="form-select">
                    <option value="driver" selected>Driver</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Status</label>
                  <select name="status" id="drv_status" class="form-select">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Language</label>
                  <select name="language" id="drv_language" class="form-select">
                    <option value="English">English</option>
                    <option value="German">German</option>
                    <option value="French">French</option>
                    <option value="Spanish">Spanish</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Timezone</label>
                  <input type="text" name="timezone" id="drv_timezone" class="form-control" value="UTC+02:00 Berlin">
                </div>
              </div>
            </div>

            <!-- ── TAB 2: Personal ── -->
            <div class="tab-pane fade" id="content-personal">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Mobile number</label>
                  <input type="text" name="phone" id="drv_phone" class="form-control" placeholder="+40 755 ...">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Date of birth</label>
                  <input type="date" name="dob" id="drv_dob" class="form-control">
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small fw-semibold">Photo (Avatar)</label>
                  <input type="file" name="photo" id="drv_photo" class="form-control" accept="image/*">
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small fw-semibold">Address</label>
                  <input type="text" name="address" id="drv_address" class="form-control" placeholder="Street Address">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">City</label>
                  <input type="text" name="city" id="drv_city" class="form-control" placeholder="City">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Country</label>
                  <input type="text" name="country" id="drv_country" class="form-control" placeholder="Country">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Emergency contact name</label>
                  <input type="text" name="emergency_contact_name" id="drv_emergency_name" class="form-control" placeholder="Name">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Emergency contact phone</label>
                  <input type="text" name="emergency_contact_phone" id="drv_emergency_phone" class="form-control" placeholder="Phone">
                </div>
              </div>
            </div>

            <!-- ── TAB 3: Other ── -->
            <div class="tab-pane fade" id="content-other">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Driving license number</label>
                  <input type="text" name="license_number" id="drv_license_number" class="form-control" placeholder="License Number">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Driving license expiry</label>
                  <input type="date" name="license_expiry_date" id="drv_license_expiry" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Badge / PCO license</label>
                  <input type="text" name="badge_number" id="drv_badge_number" class="form-control" placeholder="Badge / Serial No.">
                </div>
                <div class="col-md-6">
                  <label class="form-label text-muted small fw-semibold">Badge expiry date</label>
                  <input type="date" name="badge_expiry_date" id="drv_badge_expiry" class="form-control">
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small fw-semibold">Assign vehicle</label>
                  <select name="vehicle_id" id="drv_vehicle_id" class="form-select">
                    <option value="">-- None (Unassigned) --</option>
                    <?php foreach ($vehicles_list as $vl): ?>
                      <option value="<?= $vl['id'] ?>"><?= htmlspecialchars($vl['make'] . ' ' . $vl['model']) ?> (<?= htmlspecialchars($vl['license_plate']) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small fw-semibold">Notes</label>
                  <textarea name="notes" id="drv_notes" class="form-control" rows="2" placeholder="Driver notes or instructions..."></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-5 fw-bold" id="drv_submit_btn">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Notification Settings Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="notificationsModalLabel"><i class="fas fa-bell me-2 text-warning"></i>Edit Driver Notifications</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="notif_email" checked>
          <label class="form-check-label fw-semibold" for="notif_email">Email Notifications (New Ride Dispatch)</label>
        </div>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="notif_sms" checked>
          <label class="form-check-label fw-semibold" for="notif_sms">SMS Alerts for Urgent Bookings</label>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function openDriverModal() {
  document.getElementById('driverForm').reset();
  document.getElementById('drv_driver_id').value = '0';
  document.getElementById('drv_user_id').value = '0';
  document.getElementById('drv_current_photo').value = '';
  document.getElementById('drv_role').value = 'driver';
  document.getElementById('drv_status').value = 'active';
  document.getElementById('driverModalLabel').innerHTML = '<i class="fas fa-user-plus text-warning me-2"></i>Add new driver';
  document.getElementById('drv_submit_btn').innerText = 'Add';
  document.getElementById('drv_pass_label').innerHTML = 'Password <span class="text-danger">*</span>';
  document.getElementById('drv_pass_help').style.display = 'none';
  document.getElementById('drv_password').required = true;

  // Reset to first tab
  var firstTab = new bootstrap.Tab(document.getElementById('tab-general'));
  firstTab.show();

  var el = document.getElementById('driverModal');
  var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  modal.show();
}

function editDriverModal(data) {
  document.getElementById('driverForm').reset();
  document.getElementById('drv_driver_id').value = data.driver_id || 0;
  document.getElementById('drv_user_id').value = data.user_id || 0;
  document.getElementById('drv_current_photo').value = data.photo || '';
  document.getElementById('drv_display_name').value = data.display_name || '';
  document.getElementById('drv_unique_id').value = data.unique_id || '';
  document.getElementById('drv_email').value = data.email || '';
  document.getElementById('drv_phone').value = data.phone || '';
  document.getElementById('drv_role').value = data.role || 'driver';
  document.getElementById('drv_status').value = data.user_status || 'active';
  document.getElementById('drv_activity_status').value = data.activity_status || 'Available';
  document.getElementById('drv_fleet_operator').value = data.fleet_operator || '';
  document.getElementById('drv_language').value = data.language || 'English';
  document.getElementById('drv_timezone').value = data.timezone || 'UTC+02:00 Berlin';
  document.getElementById('drv_address').value = data.address || '';
  document.getElementById('drv_city').value = data.city || '';
  document.getElementById('drv_country').value = data.country || '';
  document.getElementById('drv_dob').value = data.dob || '';
  document.getElementById('drv_license_number').value = data.license_number || '';
  document.getElementById('drv_license_expiry').value = data.license_expiry_date || '';
  document.getElementById('drv_badge_number').value = data.badge_number || '';
  document.getElementById('drv_badge_expiry').value = data.badge_expiry_date || '';
  document.getElementById('drv_vehicle_id').value = data.vehicle_id || '';
  document.getElementById('drv_emergency_name').value = data.emergency_contact_name || '';
  document.getElementById('drv_emergency_phone').value = data.emergency_contact_phone || '';
  document.getElementById('drv_notes').value = data.notes || '';

  document.getElementById('driverModalLabel').innerHTML = '<i class="fas fa-edit text-primary me-2"></i>Edit driver: ' + (data.display_name || '');
  document.getElementById('drv_submit_btn').innerText = 'Save changes';
  document.getElementById('drv_pass_label').innerText = 'Password (Optional)';
  document.getElementById('drv_pass_help').style.display = 'block';
  document.getElementById('drv_password').required = false;

  var firstTab = new bootstrap.Tab(document.getElementById('tab-general'));
  firstTab.show();

  var el = document.getElementById('driverModal');
  var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  modal.show();
}

function filterDriversTable() {
  var input = document.getElementById('driverSearch');
  var filter = input.value.toLowerCase();
  var rows = document.getElementById('driversTableBody').getElementsByTagName('tr');
  for (var i = 0; i < rows.length; i++) {
    var text = rows[i].textContent || rows[i].innerText;
    rows[i].style.display = (text.toLowerCase().indexOf(filter) > -1) ? '' : 'none';
  }
}

function exportDriversCSV() {
  var rows = document.querySelectorAll("#driversTable tr");
  var csv = [];
  for (var i = 0; i < rows.length; i++) {
    var row = [], cols = rows[i].querySelectorAll("td, th");
    for (var j = 2; j < cols.length; j++) {
      var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
      data = data.replace(/"/g, '""');
      row.push('"' + data + '"');
    }
    csv.push(row.join(","));
  }
  var csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
  var downloadLink = document.createElement("a");
  downloadLink.download = "drivers_list.csv";
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}
</script>

<?php require_once 'footer.php'; ?>
