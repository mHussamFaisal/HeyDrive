<?php
// All POST/GET logic runs BEFORE any HTML output — fixes blank page on redirect
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();
$err = '';

// Fetch all available vehicles for dropdown
$vehicles_list = [];
try {
    $vehicles_list = $pdo->query("SELECT id, name, make, model, license_plate, type FROM td_vehicles ORDER BY make, model")->fetchAll();
} catch (Exception $e) {}

// ── POST: Add Driver / Update Status / Delete All ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_driver') {
        $display_name          = trim($_POST['display_name'] ?? '');
        $unique_id             = trim($_POST['unique_id'] ?? '');
        $email                 = strtolower(trim($_POST['email'] ?? ''));
        $password_raw          = $_POST['password'] ?? '';
        $confirm_password      = $_POST['confirm_password'] ?? '';
        $status                = trim($_POST['status'] ?? 'Approved');
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
        $photo_path            = '';

        if (empty($display_name) || empty($email) || empty($password_raw)) {
            $err = "Display name, email and password are required fields.";
        } elseif ($password_raw !== $confirm_password) {
            $err = "Passwords do not match. Please enter the same password in both fields.";
        } else {
            // Check for existing email
            $chk = $pdo->prepare("SELECT id FROM td_users WHERE email = ?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $err = "A user with this email address already exists. Please use a different email.";
            } else {
                // Handle Photo upload
                if (!empty($_FILES['photo']['name'])) {
                    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
                    $ftype   = mime_content_type($_FILES['photo']['tmp_name']);
                    if (!in_array($ftype, $allowed)) {
                        $err = "Invalid image type. Please upload JPG, PNG, WEBP or GIF.";
                    } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                        $err = "Image too large. Maximum size is 5MB.";
                    } else {
                        $ext        = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                        $filename   = 'driver_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
                        $upload_dir = dirname(__DIR__) . '/uploads/drivers/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename)) {
                            $photo_path = 'uploads/drivers/' . $filename;
                        } else {
                            $err = "Failed to save photo. Please try again.";
                        }
                    }
                }

                if (empty($err)) {
                    try {
                        $pdo->beginTransaction();
                        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
                        $user_status = ($status === 'Approved' || $status === 'active') ? 'active' : 'inactive';

                        // 1. Insert into td_users
                        $pdo->prepare("INSERT INTO td_users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'driver', ?)")
                            ->execute([$display_name, $email, $phone, $password_hash, $user_status]);
                        $uid = $pdo->lastInsertId();

                        if (empty($unique_id)) {
                            $unique_id = 'DRV-' . (1000 + $uid);
                        }

                        // 2. Insert into td_drivers
                        $pdo->prepare("INSERT INTO td_drivers 
                            (user_id, unique_id, photo, language, timezone, fleet_operator, 
                             address, city, country, dob, license_number, license_expiry_date, 
                             badge_number, badge_expiry_date, vehicle_id, emergency_contact_name, 
                             emergency_contact_phone, notes, status) 
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                            ->execute([
                                $uid, $unique_id, $photo_path, $language, $timezone, $fleet_operator,
                                $address, $city, $country, $dob, $license_number, $license_expiry_date,
                                $badge_number, $badge_expiry_date, $vehicle_id, $emergency_name,
                                $emergency_phone, $notes, $status
                            ]);

                        $pdo->commit();
                        header("Location: " . APP_URL . "/admin/drivers.php?msg=added");
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $err = "Database error: " . htmlspecialchars($e->getMessage());
                    }
                }
            }
        }

    } elseif ($action === 'update_status') {
        $did    = intval($_POST['driver_id']);
        $status = trim($_POST['status']);
        $pdo->prepare("UPDATE td_drivers SET status=? WHERE id=?")->execute([$status, $did]);
        header("Location: " . APP_URL . "/admin/drivers.php?msg=updated");
        exit;

    } elseif ($action === 'update_activity_status') {
        $did = intval($_POST['driver_id']);
        $act = trim($_POST['activity_status']);
        $pdo->prepare("UPDATE td_drivers SET activity_status=? WHERE id=?")->execute([$act, $did]);
        header("Location: " . APP_URL . "/admin/drivers.php?msg=updated");
        exit;

    } elseif ($action === 'delete_all_drivers') {
        // Delete all drivers & driver user accounts
        $uids = $pdo->query("SELECT user_id FROM td_drivers")->fetchAll(PDO::FETCH_COLUMN);
        $pdo->exec("DELETE FROM td_drivers");
        if (!empty($uids)) {
            $in = implode(',', array_map('intval', $uids));
            $pdo->exec("DELETE FROM td_users WHERE id IN ($in) AND role='driver'");
        }
        header("Location: " . APP_URL . "/admin/drivers.php?msg=all_deleted");
        exit;
    }
}

// ── GET: Delete single driver ────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = intval($_GET['delete']);
    $d = $pdo->prepare("SELECT user_id, photo FROM td_drivers WHERE id=?");
    $d->execute([$did]);
    $dr = $d->fetch();
    if ($dr) {
        if ($dr['photo'] && file_exists(dirname(__DIR__) . '/' . $dr['photo'])) {
            unlink(dirname(__DIR__) . '/' . $dr['photo']);
        }
        $pdo->prepare("DELETE FROM td_drivers WHERE id=?")->execute([$did]);
        $pdo->prepare("DELETE FROM td_users WHERE id=? AND role='driver'")->execute([$dr['user_id']]);
    }
    header("Location: " . APP_URL . "/admin/drivers.php?msg=deleted");
    exit;
}

// ── Now safe to output HTML ──────────────────────────────────────────────────
$page_title = 'Drivers';
require_once 'header.php';

$drivers = $pdo->query("SELECT d.*, u.name, u.email, u.phone, u.status as user_status,
    v.make, v.model, v.license_plate, v.registration_mark
    FROM td_drivers d
    JOIN td_users u ON d.user_id = u.id
    LEFT JOIN td_vehicles v ON d.vehicle_id = v.id
    ORDER BY d.id DESC")->fetchAll();

$msg = $_GET['msg'] ?? '';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Users</a></li>
    <li class="breadcrumb-item"><a href="drivers.php">Drivers</a></li>
    <li class="breadcrumb-item active">Add &amp; Manage Drivers</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
  <?= $msg==='added' ? '✅ Driver added successfully!' : ($msg==='deleted' ? '🗑️ Driver removed.' : ($msg==='all_deleted' ? '🗑️ All drivers removed.' : '✅ Updated.')) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger mb-4">❌ <?= $err ?></div><?php endif; ?>

<!-- Top Action Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="fas fa-id-badge me-2 text-warning"></i>Users &rsaquo; Drivers</h4>
    <div class="text-muted small mt-1">Add and manage drivers, licenses, languages &amp; vehicle assignments</div>
  </div>
  <div class="d-flex gap-2">
    <!-- Delete All Drivers Button -->
    <button type="button" class="btn btn-outline-danger btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#deleteAllDriversModal" style="border-radius:.6rem;">
      <i class="fas fa-trash-alt"></i> Delete All Drivers
    </button>
    <!-- Add New Driver Toggle Button -->
    <button type="button" class="btn btn-warning btn-lg px-4 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="collapse" data-bs-target="#addDriverCollapse" aria-expanded="<?= (empty($drivers) || $err) ? 'true' : 'false' ?>" style="border-radius:.6rem;">
      <i class="fas fa-user-plus fa-lg"></i> Add New Driver
    </button>
  </div>
</div>

<!-- Delete All Drivers Modal -->
<div class="modal fade" id="deleteAllDriversModal" tabindex="-1" aria-labelledby="deleteAllDriversModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold" id="deleteAllDriversModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Delete All Drivers?
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-3">Are you sure you want to <strong>permanently delete ALL drivers</strong> from the system?</p>
        <div class="alert alert-warning small mb-0">
          <i class="fas fa-info-circle me-1"></i> <strong>Warning:</strong> This action cannot be undone.
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="action" value="delete_all_drivers">
          <button type="submit" class="btn btn-danger fw-bold">
            <i class="fas fa-trash-alt me-1"></i> Yes, Delete ALL Drivers
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
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
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="notif_app" checked>
          <label class="form-check-label fw-semibold" for="notif_app">Driver Mobile App Push Notifications</label>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save Preferences</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Driver Form (Exact Match to Client Screenshot with Tabs) -->
<div class="collapse <?= (empty($drivers) || $err) ? 'show' : '' ?> mb-4" id="addDriverCollapse">
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold py-3">
      <span class="fs-5 text-dark">Users &rsaquo; Drivers &rsaquo; Add</span>
    </div>
    <div class="card-body p-4" style="background:#fafafa;">
      
      <form method="POST" enctype="multipart/form-data" id="addDriverForm">
        <input type="hidden" name="action" value="add_driver">

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4 bg-white px-3 pt-2 rounded border" id="driverTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold text-dark" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-tab-pane" type="button" role="tab">General</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-dark" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal-tab-pane" type="button" role="tab">Personal</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-dark" id="other-tab" data-bs-toggle="tab" data-bs-target="#other-tab-pane" type="button" role="tab">Other</button>
          </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content" id="driverTabsContent">
          
          <!-- GENERAL TAB (Exact match to screenshot) -->
          <div class="tab-pane fade show active" id="general-tab-pane" role="tabpanel">
            <div class="row g-3">
              <!-- Display name & Unique ID -->
              <div class="col-md-6">
                <label class="form-label text-muted small">Display name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg bg-white" name="display_name" placeholder="Display name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Unique ID</label>
                <input type="text" class="form-control form-control-lg bg-white" name="unique_id" id="unique_id_input" placeholder="e.g. DRV-1001">
              </div>

              <!-- Email -->
              <div class="col-12">
                <label class="form-label text-muted small">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-lg bg-white" name="email" placeholder="driver@taxisdispatch.com" required>
              </div>

              <!-- Password & Confirm password -->
              <div class="col-md-6">
                <label class="form-label text-muted small">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg bg-white" name="password" id="pwd_input" placeholder="Password" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="generateRandomPwd()" title="Generate Random Password"><i class="fas fa-key"></i></button>
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePwdVisibility('pwd_input','pwd_eye')" title="Show/Hide Password"><i class="fas fa-eye" id="pwd_eye"></i></button>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Confirm password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg bg-white" name="confirm_password" id="cpwd_input" placeholder="Confirm password" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePwdVisibility('cpwd_input','cpwd_eye')" title="Show/Hide Password"><i class="fas fa-eye" id="cpwd_eye"></i></button>
                </div>
              </div>

              <!-- Upload photo -->
              <div class="col-12">
                <fieldset class="border p-3 rounded bg-white">
                  <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Upload photo (512x512px)</legend>
                  <input type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewDriverPhoto(this)">
                  <div id="photoPreview" class="mt-2" style="display:none">
                    <img id="previewPhotoImg" src="" alt="Preview" style="max-height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
                  </div>
                </fieldset>
              </div>

              <!-- Status -->
              <div class="col-12">
                <fieldset class="border p-3 rounded bg-white">
                  <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Status</legend>
                  <select class="form-select form-select-lg border-0 shadow-none" name="status">
                    <option value="Approved" selected>Approved</option>
                    <option value="Pending">Pending</option>
                    <option value="Suspended">Suspended</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </fieldset>
              </div>

              <!-- Language & Timezone -->
              <div class="col-md-6">
                <fieldset class="border p-3 rounded bg-white">
                  <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Language</legend>
                  <select class="form-select form-select-lg border-0 shadow-none" name="language">
                    <option value="English" selected>English</option>
                    <option value="German">German (Deutsch)</option>
                    <option value="French">French (Français)</option>
                    <option value="Spanish">Spanish (Español)</option>
                    <option value="Dutch">Dutch (Nederlands)</option>
                  </select>
                </fieldset>
              </div>
              <div class="col-md-6">
                <fieldset class="border p-3 rounded bg-white">
                  <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Timezone</legend>
                  <select class="form-select form-select-lg border-0 shadow-none" name="timezone">
                    <option value="UTC+02:00 Berlin" selected>UTC+02:00 Berlin</option>
                    <option value="UTC+00:00 London">UTC+00:00 London</option>
                    <option value="UTC+01:00 Paris">UTC+01:00 Paris</option>
                    <option value="UTC-05:00 New York">UTC-05:00 New York</option>
                  </select>
                </fieldset>
              </div>

              <!-- Fleet operator -->
              <div class="col-12">
                <fieldset class="border p-3 rounded bg-white">
                  <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Fleet operator</legend>
                  <select class="form-select form-select-lg border-0 shadow-none" name="fleet_operator">
                    <option value="Unassigned" selected>Unassigned</option>
                    <option value="Main Fleet">Main Fleet</option>
                    <option value="Partner Fleet">Partner Fleet</option>
                  </select>
                </fieldset>
              </div>

              <!-- Edit notifications button -->
              <div class="col-12 mt-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                  <i class="fas fa-bell me-1"></i> Edit notifications
                </button>
              </div>
            </div>
          </div>

          <!-- PERSONAL TAB (Exact match to User Image 1) -->
          <div class="tab-pane fade" id="personal-tab-pane" role="tabpanel">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label text-muted small">Title</label>
                <input type="text" class="form-control form-control-lg bg-white" name="title" placeholder="Title">
              </div>
              <div class="col-md-4">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">First name</legend>
                  <input type="text" class="form-control border-0 shadow-none" name="first_name" placeholder="First name">
                </fieldset>
              </div>
              <div class="col-md-4">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Last name</legend>
                  <input type="text" class="form-control border-0 shadow-none" name="last_name" placeholder="Last name">
                </fieldset>
              </div>

              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Date of birth</legend>
                  <input type="date" class="form-control border-0 shadow-none" name="dob">
                </fieldset>
              </div>
              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Mobile number</legend>
                  <input type="tel" class="form-control border-0 shadow-none" name="phone" placeholder="Mobile number">
                </fieldset>
              </div>

              <div class="col-md-6">
                <label class="form-label text-muted small">Telephone number</label>
                <input type="tel" class="form-control form-control-lg bg-white" name="telephone_number" placeholder="Telephone number">
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Emergency number</label>
                <input type="tel" class="form-control form-control-lg bg-white" name="emergency_number" placeholder="Emergency number">
              </div>

              <div class="col-12">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Address</legend>
                  <input type="text" class="form-control border-0 shadow-none" name="address" placeholder="Address">
                </fieldset>
              </div>

              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">City</legend>
                  <input type="text" class="form-control border-0 shadow-none" name="city" placeholder="City">
                </fieldset>
              </div>
              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Postcode</legend>
                  <input type="text" class="form-control border-0 shadow-none" name="postcode" placeholder="Postcode">
                </fieldset>
              </div>

              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">County</legend>
                  <input type="text" class="form-control border-0 shadow-none" name="county" placeholder="County">
                </fieldset>
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Country</label>
                <input type="text" class="form-control form-control-lg bg-white" name="country" placeholder="Country">
              </div>

              <div class="col-12">
                <label class="form-label text-muted small">Company name</label>
                <input type="text" class="form-control form-control-lg bg-white" name="company_name" placeholder="Company name">
              </div>

              <div class="col-md-6">
                <label class="form-label text-muted small">Company number</label>
                <input type="text" class="form-control form-control-lg bg-white" name="company_number" placeholder="Company number">
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Company VAT number</label>
                <input type="text" class="form-control form-control-lg bg-white" name="company_vat" placeholder="Company VAT number">
              </div>

              <div class="col-12">
                <label class="form-label text-muted small">Notes</label>
                <textarea class="form-control bg-white" name="notes" rows="3" placeholder="Notes"></textarea>
              </div>
            </div>
          </div>

          <!-- OTHER TAB (Exact match to User Image 2) -->
          <div class="tab-pane fade" id="other-tab-pane" role="tabpanel">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label text-muted small">National insurance number</label>
                <input type="text" class="form-control form-control-lg bg-white" name="national_insurance" placeholder="National insurance number">
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Bank account details</label>
                <textarea class="form-control bg-white" name="bank_details" rows="2" placeholder="Bank account details"></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label text-muted small">Insurance</label>
                <input type="text" class="form-control form-control-lg bg-white" name="insurance_policy" placeholder="Insurance">
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Insurance expiry date</label>
                <input type="date" class="form-control form-control-lg bg-white" name="insurance_expiry_date">
              </div>

              <div class="col-md-6">
                <label class="form-label text-muted small">Driving licence</label>
                <input type="text" class="form-control form-control-lg bg-white" name="license_number" placeholder="Driving licence">
              </div>
              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Driving licence expiry date</legend>
                  <input type="date" class="form-control border-0 shadow-none" name="license_expiry_date">
                </fieldset>
              </div>

              <div class="col-md-6">
                <label class="form-label text-muted small">Passenger transport driver licence</label>
                <input type="text" class="form-control form-control-lg bg-white" name="pt_driver_licence" placeholder="Passenger transport driver licence">
              </div>
              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Passenger transport driver licence expiry date</legend>
                  <input type="date" class="form-control border-0 shadow-none" name="pt_driver_licence_expiry">
                </fieldset>
              </div>

              <div class="col-md-6">
                <label class="form-label text-muted small">Passenger transport vehicle licence</label>
                <input type="text" class="form-control form-control-lg bg-white" name="pt_vehicle_licence" placeholder="Passenger transport vehicle licence">
              </div>
              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Passenger transport vehicle licence expiry date</legend>
                  <input type="date" class="form-control border-0 shadow-none" name="pt_vehicle_licence_expiry">
                </fieldset>
              </div>

              <div class="col-md-6">
                <fieldset class="border p-1 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Driver income (%)</legend>
                  <input type="number" step="0.1" class="form-control border-0 shadow-none" name="driver_income_pct" value="0">
                </fieldset>
              </div>
              <div class="col-md-6">
                <label class="form-label text-muted small">Base address</label>
                <input type="text" class="form-control form-control-lg bg-white" name="base_address" placeholder="Base address">
              </div>

              <div class="col-12">
                <fieldset class="border p-2 px-3 rounded bg-white">
                  <legend class="float-none w-auto px-1 fs-6 text-muted mb-0 d-flex justify-content-between align-items-center">
                    <span>Driver activity status</span>
                  </legend>
                  <div class="d-flex align-items-center gap-2">
                    <select class="form-select border-0 shadow-none" name="activity_status">
                      <option value="Available" selected>Available</option>
                      <option value="Unavailable">Unavailable</option>
                      <option value="On Job">On Job</option>
                    </select>
                    <i class="fas fa-info-circle text-secondary fs-5" title="Driver's real-time dispatch availability"></i>
                  </div>
                </fieldset>
              </div>

              <div class="col-12 mt-4">
                <label class="form-label fw-semibold text-dark">Additional files</label>
                <div class="table-responsive border rounded bg-white mb-2">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Title</th>
                        <th>File</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="text-muted small italic py-3" colspan="2">No files attached yet.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <button type="button" class="btn btn-success fw-bold btn-sm px-3">
                  <i class="fas fa-plus me-1"></i> New file
                </button>
              </div>
            </div>
          </div>

        </div>

        <!-- Add / Cancel Buttons (Exact match to screenshot) -->
        <div class="mt-4 pt-3 border-top d-flex gap-3 align-items-center">
          <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
            Add
          </button>
          <button type="button" class="btn btn-link text-decoration-none text-secondary" data-bs-toggle="collapse" data-bs-target="#addDriverCollapse">
            Cancel
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Drivers List Table -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
    <span class="fs-5">🚖 Drivers List (<?= count($drivers) ?>)</span>
    <div class="d-flex gap-2">
      <input type="text" id="driverSearch" class="form-control form-control-sm" placeholder="Search drivers..." style="width:200px">
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-dark">
          <tr>
            <th style="width:140px">Actions</th>
            <th style="width:60px">Photo</th>
            <th>Display name</th>
            <th>Email</th>
            <th>Mobile number</th>
            <th>Unique ID</th>
            <th>Driver activity status</th>
            <th>Fleet operator</th>
          </tr>
        </thead>
        <tbody id="driversTableBody">
          <?php foreach ($drivers as $d): ?>
          <tr>
            <!-- Action Button Group (Eye | Dropdown | Bell) -->
            <td>
              <div class="btn-group border rounded bg-light shadow-sm" role="group">
                <!-- View Profile Button (Eye) -->
                <a href="driver_view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-light border-end" title="View Driver Profile">
                  <i class="far fa-eye"></i>
                </a>
                <!-- Dropdown Toggle Button -->
                <button type="button" class="btn btn-sm btn-light border-end dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="visually-hidden">Toggle Actions</span>
                </button>
                <ul class="dropdown-menu shadow border-0">
                  <li><a class="dropdown-item" href="driver_edit.php?id=<?= $d['id'] ?>"><i class="fas fa-edit me-2 text-primary"></i> Edit</a></li>
                  <li><a class="dropdown-item" href="bookings.php?driver_id=<?= $d['id'] ?>"><i class="fas fa-calendar-alt me-2 text-warning"></i> Jobs</a></li>
                  <li><a class="dropdown-item" href="vehicles.php?driver_id=<?= $d['id'] ?>"><i class="fas fa-car me-2 text-info"></i> Vehicles</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="drivers.php?delete=<?= $d['id'] ?>" onclick="return confirm('Permanently delete this driver?')"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                  <li><a class="dropdown-item text-muted" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Log out</a></li>
                </ul>
                <!-- Bell Button (Notifications) -->
                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#notificationsModal" title="Edit Notifications">
                  <i class="far fa-bell"></i>
                </button>
              </div>
            </td>

            <!-- Driver Photo -->
            <td style="width:60px">
              <?php if (!empty($d['photo'])): ?>
                <img src="<?= APP_URL . '/' . htmlspecialchars($d['photo']) ?>"
                     alt="Driver" style="width:45px;height:45px;object-fit:cover;border-radius:50%;border:1px solid #ddd">
              <?php else: ?>
                <div style="width:45px;height:45px;background:#e5e5e5;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;">👤</div>
              <?php endif; ?>
            </td>

            <!-- Display Name -->
            <td>
              <a href="driver_view.php?id=<?= $d['id'] ?>" class="text-decoration-none text-dark fw-bold">
                <?= htmlspecialchars($d['name']) ?>
              </a>
            </td>

            <!-- Email -->
            <td><?= htmlspecialchars($d['email']) ?></td>

            <!-- Mobile Number -->
            <td><?= htmlspecialchars($d['phone'] ?: '-') ?></td>

            <!-- Unique ID -->
            <td><span class="badge bg-dark"><?= htmlspecialchars($d['unique_id'] ?: 'DRV-'.$d['id']) ?></span></td>

            <!-- Activity Status -->
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="update_activity_status">
                <input type="hidden" name="driver_id" value="<?= $d['id'] ?>">
                <?php 
                  $act = $d['activity_status'] ?: 'Available';
                  $badge_bg = ($act === 'Available') ? 'bg-success' : (($act === 'On Job') ? 'bg-warning text-dark' : 'bg-danger');
                ?>
                <select name="activity_status" class="form-select form-select-sm fw-bold text-white border-0 <?= $badge_bg ?>" style="width:120px; cursor:pointer; border-radius:6px; font-size:12px; padding:4px 8px;" onchange="this.form.submit()">
                  <option value="Available" class="bg-white text-dark" <?= $act==='Available'?'selected':'' ?>>Available</option>
                  <option value="Unavailable" class="bg-white text-dark" <?= $act==='Unavailable'?'selected':'' ?>>Unavailable</option>
                  <option value="On Job" class="bg-white text-dark" <?= $act==='On Job'?'selected':'' ?>>On Job</option>
                </select>
              </form>
            </td>

            <!-- Fleet Operator -->
            <td><small class="text-muted"><?= htmlspecialchars($d['fleet_operator'] ?: 'Unassigned') ?></small></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($drivers)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="fas fa-id-badge fa-2x mb-2 text-secondary"></i><br>
              No drivers added yet. Click <strong>"Add New Driver"</strong> above to add your first driver!
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function previewDriverPhoto(input) {
  var preview = document.getElementById('photoPreview');
  var img = document.getElementById('previewPhotoImg');
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.style.display = 'none';
  }
}

function generateRandomPwd() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
  let pwd = '';
  for (let i = 0; i < 10; i++) {
    pwd += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  document.getElementById('pwd_input').value = pwd;
  document.getElementById('cpwd_input').value = pwd;
}

function togglePwdVisibility(inputId, eyeId) {
  const input = document.getElementById(inputId);
  const eye = document.getElementById(eyeId);
  if (input.type === 'password') {
    input.type = 'text';
    eye.className = 'fas fa-eye-slash';
  } else {
    input.type = 'password';
    eye.className = 'fas fa-eye';
  }
}
</script>

<?php require_once 'footer.php'; ?>
