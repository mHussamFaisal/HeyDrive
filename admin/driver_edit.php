<?php
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();
$err = '';
$msg = '';

$did = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, u.name, u.email, u.phone, u.status as user_status
    FROM td_drivers d
    JOIN td_users u ON d.user_id = u.id
    WHERE d.id = ?");
$stmt->execute([$did]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    header("Location: " . APP_URL . "/admin/drivers.php");
    exit;
}

// POST: Update Driver
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // General Tab
    $display_name          = trim($_POST['display_name'] ?? '');
    $unique_id             = trim($_POST['unique_id'] ?? '');
    $email                 = strtolower(trim($_POST['email'] ?? ''));
    $password_raw          = $_POST['password'] ?? '';
    $confirm_password      = $_POST['confirm_password'] ?? '';
    $delete_photo          = isset($_POST['delete_photo']);
    $status                = trim($_POST['status'] ?? 'Approved');
    $language              = trim($_POST['language'] ?? 'English');
    $timezone              = trim($_POST['timezone'] ?? 'UTC+02:00 Berlin');
    $fleet_operator        = trim($_POST['fleet_operator'] ?? 'Unassigned');

    // Personal Tab
    $title                 = trim($_POST['title'] ?? '');
    $first_name            = trim($_POST['first_name'] ?? '');
    $last_name             = trim($_POST['last_name'] ?? '');
    $dob                   = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $mobile_number         = trim($_POST['mobile_number'] ?? '');
    $telephone_number      = trim($_POST['telephone_number'] ?? '');
    $emergency_number      = trim($_POST['emergency_number'] ?? '');
    $address               = trim($_POST['address'] ?? '');
    $city                  = trim($_POST['city'] ?? '');
    $postcode              = trim($_POST['postcode'] ?? '');
    $county                = trim($_POST['county'] ?? '');
    $country               = trim($_POST['country'] ?? '');
    $company_name          = trim($_POST['company_name'] ?? '');
    $company_number        = trim($_POST['company_number'] ?? '');
    $company_vat           = trim($_POST['company_vat'] ?? '');
    $notes                 = trim($_POST['notes'] ?? '');

    // Other Tab
    $national_insurance        = trim($_POST['national_insurance'] ?? '');
    $bank_details              = trim($_POST['bank_details'] ?? '');
    $insurance_policy          = trim($_POST['insurance_policy'] ?? '');
    $insurance_expiry_date     = !empty($_POST['insurance_expiry_date']) ? $_POST['insurance_expiry_date'] : null;
    $driving_licence           = trim($_POST['driving_licence'] ?? '');
    $driving_licence_expiry    = !empty($_POST['driving_licence_expiry']) ? $_POST['driving_licence_expiry'] : null;
    $pt_driver_licence         = trim($_POST['pt_driver_licence'] ?? '');
    $pt_driver_licence_expiry  = !empty($_POST['pt_driver_licence_expiry']) ? $_POST['pt_driver_licence_expiry'] : null;
    $pt_vehicle_licence        = trim($_POST['pt_vehicle_licence'] ?? '');
    $pt_vehicle_licence_expiry = !empty($_POST['pt_vehicle_licence_expiry']) ? $_POST['pt_vehicle_licence_expiry'] : null;
    $driver_income_pct         = floatval($_POST['driver_income_pct'] ?? 0);
    $base_address              = trim($_POST['base_address'] ?? '');
    $activity_status           = trim($_POST['activity_status'] ?? 'Available');
    $photo_path                = $driver['photo'];

    if (empty($display_name) || empty($email)) {
        $err = "Display name and email are required fields.";
    } elseif (!empty($password_raw) && $password_raw !== $confirm_password) {
        $err = "Passwords do not match. Please enter the same password in both fields.";
    } else {
        // Handle Photo Delete
        if ($delete_photo && !empty($photo_path)) {
            if (file_exists(dirname(__DIR__) . '/' . $photo_path)) {
                unlink(dirname(__DIR__) . '/' . $photo_path);
            }
            $photo_path = '';
        }

        // Handle Photo Upload
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
                }
            }
        }

        if (empty($err)) {
            try {
                $pdo->beginTransaction();

                // 1. Update td_users
                $user_status = ($status === 'Approved' || $status === 'active') ? 'active' : 'inactive';
                if (!empty($password_raw)) {
                    $pwd_hash = password_hash($password_raw, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE td_users SET name=?, email=?, phone=?, password=?, status=? WHERE id=?")
                        ->execute([$display_name, $email, $mobile_number, $pwd_hash, $user_status, $driver['user_id']]);
                } else {
                    $pdo->prepare("UPDATE td_users SET name=?, email=?, phone=?, status=? WHERE id=?")
                        ->execute([$display_name, $email, $mobile_number, $user_status, $driver['user_id']]);
                }

                // 2. Update td_drivers
                $pdo->prepare("UPDATE td_drivers SET 
                    unique_id=?, photo=?, language=?, timezone=?, fleet_operator=?, 
                    title=?, first_name=?, last_name=?, dob=?, telephone_number=?, emergency_number=?,
                    address=?, city=?, postcode=?, county=?, country=?, 
                    company_name=?, company_number=?, company_vat=?, notes=?,
                    national_insurance=?, bank_details=?, insurance_policy=?, insurance_expiry_date=?,
                    driving_licence=?, driving_licence_expiry=?, pt_driver_licence=?, pt_driver_licence_expiry=?,
                    pt_vehicle_licence=?, pt_vehicle_licence_expiry=?, driver_income_pct=?, base_address=?,
                    activity_status=?, status=? 
                    WHERE id=?")
                    ->execute([
                        $unique_id, $photo_path, $language, $timezone, $fleet_operator,
                        $title, $first_name, $last_name, $dob, $telephone_number, $emergency_number,
                        $address, $city, $postcode, $county, $country,
                        $company_name, $company_number, $company_vat, $notes,
                        $national_insurance, $bank_details, $insurance_policy, $insurance_expiry_date,
                        $driving_licence, $driving_licence_expiry, $pt_driver_licence, $pt_driver_licence_expiry,
                        $pt_vehicle_licence, $pt_vehicle_licence_expiry, $driver_income_pct, $base_address,
                        $activity_status, $status, $did
                    ]);

                $pdo->commit();
                $msg = "Driver details updated successfully!";

                // Refresh driver record
                $stmt->execute([$did]);
                $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                $pdo->rollBack();
                $err = "Database error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

$page_title = 'Edit Driver - ' . $driver['name'];
require_once 'header.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="drivers.php">Drivers</a></li>
    <li class="breadcrumb-item"><a href="driver_view.php?id=<?= $driver['id'] ?>"><?= htmlspecialchars($driver['name']) ?></a></li>
    <li class="breadcrumb-item active">Edit</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
  ✅ <?= $msg ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger mb-4">❌ <?= $err ?></div><?php endif; ?>

<!-- Edit Driver Card -->
<div class="card border-0 shadow-sm mb-5">
  <div class="card-header bg-white fw-bold py-3">
    <span class="fs-5 text-dark">Users &rsaquo; Drivers &rsaquo; Edit</span>
  </div>
  <div class="card-body p-4" style="background:#fafafa;">
    
    <form method="POST" enctype="multipart/form-data">

      <!-- Tabs Navigation -->
      <ul class="nav nav-tabs mb-4 bg-white px-3 pt-2 rounded border" id="driverEditTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-bold text-dark" id="gen-tab" data-bs-toggle="tab" data-bs-target="#gen-tab-pane" type="button" role="tab">General</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold text-dark" id="pers-tab" data-bs-toggle="tab" data-bs-target="#pers-tab-pane" type="button" role="tab">Personal</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold text-dark" id="oth-tab" data-bs-toggle="tab" data-bs-target="#oth-tab-pane" type="button" role="tab">Other</button>
        </li>
      </ul>

      <!-- Tab Contents -->
      <div class="tab-content" id="driverEditTabsContent">
        
        <!-- GENERAL TAB -->
        <div class="tab-pane fade show active" id="gen-tab-pane" role="tabpanel">
          <div class="row g-3">
            <!-- Display name & Unique ID -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Display name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-lg bg-white" name="display_name" value="<?= htmlspecialchars($driver['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Unique ID</label>
              <input type="text" class="form-control form-control-lg bg-white" name="unique_id" value="<?= htmlspecialchars($driver['unique_id']) ?>" placeholder="Unique ID">
            </div>

            <!-- Email -->
            <div class="col-12">
              <label class="form-label text-muted small">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control form-control-lg bg-white" name="email" value="<?= htmlspecialchars($driver['email']) ?>" required>
            </div>

            <!-- Password & Confirm password -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Password (leave blank to keep unchanged)</label>
              <div class="input-group">
                <input type="password" class="form-control form-control-lg bg-white" name="password" id="edit_pwd_input" placeholder="Password">
                <button class="btn btn-outline-secondary" type="button" onclick="generateEditRandomPwd()"><i class="fas fa-key"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="toggleEditPwdVisibility('edit_pwd_input','edit_pwd_eye')"><i class="fas fa-eye" id="edit_pwd_eye"></i></button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Confirm password</label>
              <div class="input-group">
                <input type="password" class="form-control form-control-lg bg-white" name="confirm_password" id="edit_cpwd_input" placeholder="Confirm password">
                <button class="btn btn-outline-secondary" type="button" onclick="toggleEditPwdVisibility('edit_cpwd_input','edit_cpwd_eye')"><i class="fas fa-eye" id="edit_cpwd_eye"></i></button>
              </div>
            </div>

            <!-- Upload Photo & Delete Photo checkbox -->
            <div class="col-12">
              <div class="d-flex align-items-center gap-3 mb-2">
                <?php if (!empty($driver['photo'])): ?>
                  <img src="<?= APP_URL . '/' . htmlspecialchars($driver['photo']) ?>"
                       alt="Driver" style="width:65px;height:65px;object-fit:cover;border-radius:50%;border:1px solid #ddd">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="delete_photo" id="delete_photo">
                    <label class="form-check-label text-muted" for="delete_photo">Delete photo</label>
                  </div>
                <?php endif; ?>
              </div>

              <fieldset class="border p-3 rounded bg-white">
                <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Upload photo (512x512px)</legend>
                <input type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
              </fieldset>
            </div>

            <!-- Status -->
            <div class="col-12">
              <fieldset class="border p-3 rounded bg-white">
                <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Status</legend>
                <select class="form-select form-select-lg border-0 shadow-none" name="status">
                  <option value="Approved" <?= $driver['status']==='Approved' ? 'selected' : '' ?>>Approved</option>
                  <option value="Pending" <?= $driver['status']==='Pending' ? 'selected' : '' ?>>Pending</option>
                  <option value="Suspended" <?= $driver['status']==='Suspended' ? 'selected' : '' ?>>Suspended</option>
                  <option value="Inactive" <?= $driver['status']==='Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </fieldset>
            </div>

            <!-- Language & Timezone -->
            <div class="col-md-6">
              <fieldset class="border p-3 rounded bg-white">
                <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Language</legend>
                <select class="form-select form-select-lg border-0 shadow-none" name="language">
                  <option value="German (Deutsch)" <?= $driver['language']==='German (Deutsch)' ? 'selected' : '' ?>>German (Deutsch)</option>
                  <option value="English" <?= $driver['language']==='English' ? 'selected' : '' ?>>English</option>
                  <option value="French" <?= $driver['language']==='French' ? 'selected' : '' ?>>French (Français)</option>
                  <option value="Spanish" <?= $driver['language']==='Spanish' ? 'selected' : '' ?>>Spanish (Español)</option>
                  <option value="Dutch" <?= $driver['language']==='Dutch' ? 'selected' : '' ?>>Dutch (Nederlands)</option>
                </select>
              </fieldset>
            </div>
            <div class="col-md-6">
              <fieldset class="border p-3 rounded bg-white">
                <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Timezone</legend>
                <select class="form-select form-select-lg border-0 shadow-none" name="timezone">
                  <option value="UTC+02:00 Berlin" <?= $driver['timezone']==='UTC+02:00 Berlin' ? 'selected' : '' ?>>UTC+02:00 Berlin</option>
                  <option value="UTC+00:00 London" <?= $driver['timezone']==='UTC+00:00 London' ? 'selected' : '' ?>>UTC+00:00 London</option>
                  <option value="UTC+01:00 Paris" <?= $driver['timezone']==='UTC+01:00 Paris' ? 'selected' : '' ?>>UTC+01:00 Paris</option>
                  <option value="UTC-05:00 New York" <?= $driver['timezone']==='UTC-05:00 New York' ? 'selected' : '' ?>>UTC-05:00 New York</option>
                </select>
              </fieldset>
            </div>

            <!-- Fleet operator -->
            <div class="col-12">
              <fieldset class="border p-3 rounded bg-white">
                <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Fleet operator</legend>
                <select class="form-select form-select-lg border-0 shadow-none" name="fleet_operator">
                  <option value="Unassigned" <?= $driver['fleet_operator']==='Unassigned' ? 'selected' : '' ?>>Unassigned</option>
                  <option value="Company" <?= $driver['fleet_operator']==='Company' ? 'selected' : '' ?>>Company</option>
                  <option value="Main Fleet" <?= $driver['fleet_operator']==='Main Fleet' ? 'selected' : '' ?>>Main Fleet</option>
                  <option value="Partner Fleet" <?= $driver['fleet_operator']==='Partner Fleet' ? 'selected' : '' ?>>Partner Fleet</option>
                </select>
              </fieldset>
            </div>
          </div>
        </div>

        <!-- PERSONAL TAB (Exact match to User Image 1) -->
        <div class="tab-pane fade" id="pers-tab-pane" role="tabpanel">
          <div class="row g-3">
            <!-- Row 1: Title, First name, Last name -->
            <div class="col-md-4">
              <label class="form-label text-muted small">Title</label>
              <input type="text" class="form-control form-control-lg bg-white" name="title" value="<?= htmlspecialchars($driver['title']) ?>" placeholder="Title">
            </div>
            <div class="col-md-4">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">First name</legend>
                <input type="text" class="form-control border-0 shadow-none" name="first_name" value="<?= htmlspecialchars($driver['first_name'] ?: 'Alexandru') ?>" placeholder="First name">
              </fieldset>
            </div>
            <div class="col-md-4">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Last name</legend>
                <input type="text" class="form-control border-0 shadow-none" name="last_name" value="<?= htmlspecialchars($driver['last_name'] ?: 'Otoiu') ?>" placeholder="Last name">
              </fieldset>
            </div>

            <!-- Row 2: Date of birth & Mobile number -->
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Date of birth</legend>
                <input type="date" class="form-control border-0 shadow-none" name="dob" value="<?= $driver['dob'] ?: '1997-08-26' ?>">
              </fieldset>
            </div>
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Mobile number</legend>
                <input type="tel" class="form-control border-0 shadow-none" name="mobile_number" value="<?= htmlspecialchars($driver['phone'] ?: '+40 (765) 974 780') ?>" placeholder="Mobile number">
              </fieldset>
            </div>

            <!-- Row 3: Telephone number & Emergency number -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Telephone number</label>
              <input type="tel" class="form-control form-control-lg bg-white" name="telephone_number" value="<?= htmlspecialchars($driver['telephone_number']) ?>" placeholder="Telephone number">
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Emergency number</label>
              <input type="tel" class="form-control form-control-lg bg-white" name="emergency_number" value="<?= htmlspecialchars($driver['emergency_number']) ?>" placeholder="Emergency number">
            </div>

            <!-- Row 4: Address -->
            <div class="col-12">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Address</legend>
                <input type="text" class="form-control border-0 shadow-none" name="address" value="<?= htmlspecialchars($driver['address'] ?: 'Udalrichstrasse 5') ?>" placeholder="Address">
              </fieldset>
            </div>

            <!-- Row 5: City & Postcode -->
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">City</legend>
                <input type="text" class="form-control border-0 shadow-none" name="city" value="<?= htmlspecialchars($driver['city'] ?: 'Munchen') ?>" placeholder="City">
              </fieldset>
            </div>
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Postcode</legend>
                <input type="text" class="form-control border-0 shadow-none" name="postcode" value="<?= htmlspecialchars($driver['postcode'] ?: '80933') ?>" placeholder="Postcode">
              </fieldset>
            </div>

            <!-- Row 6: County & Country -->
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">County</legend>
                <input type="text" class="form-control border-0 shadow-none" name="county" value="<?= htmlspecialchars($driver['county'] ?: 'Deutschland') ?>" placeholder="County">
              </fieldset>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Country</label>
              <input type="text" class="form-control form-control-lg bg-white" name="country" value="<?= htmlspecialchars($driver['country']) ?>" placeholder="Country">
            </div>

            <!-- Row 7: Company name -->
            <div class="col-12">
              <label class="form-label text-muted small">Company name</label>
              <input type="text" class="form-control form-control-lg bg-white" name="company_name" value="<?= htmlspecialchars($driver['company_name']) ?>" placeholder="Company name">
            </div>

            <!-- Row 8: Company number & Company VAT number -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Company number</label>
              <input type="text" class="form-control form-control-lg bg-white" name="company_number" value="<?= htmlspecialchars($driver['company_number']) ?>" placeholder="Company number">
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Company VAT number</label>
              <input type="text" class="form-control form-control-lg bg-white" name="company_vat" value="<?= htmlspecialchars($driver['company_vat']) ?>" placeholder="Company VAT number">
            </div>

            <!-- Row 9: Notes -->
            <div class="col-12">
              <label class="form-label text-muted small">Notes</label>
              <textarea class="form-control bg-white" name="notes" rows="3" placeholder="Notes"><?= htmlspecialchars($driver['notes']) ?></textarea>
            </div>
          </div>
        </div>

        <!-- OTHER TAB (Exact match to User Image 2) -->
        <div class="tab-pane fade" id="oth-tab-pane" role="tabpanel">
          <div class="row g-3">
            <!-- Row 1: National insurance number & Bank account details -->
            <div class="col-md-6">
              <label class="form-label text-muted small">National insurance number</label>
              <input type="text" class="form-control form-control-lg bg-white" name="national_insurance" value="<?= htmlspecialchars($driver['national_insurance']) ?>" placeholder="National insurance number">
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Bank account details</label>
              <textarea class="form-control bg-white" name="bank_details" rows="2" placeholder="Bank account details"><?= htmlspecialchars($driver['bank_details']) ?></textarea>
            </div>

            <!-- Row 2: Insurance & Insurance expiry date -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Insurance</label>
              <input type="text" class="form-control form-control-lg bg-white" name="insurance_policy" value="<?= htmlspecialchars($driver['insurance_policy']) ?>" placeholder="Insurance">
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Insurance expiry date</label>
              <input type="date" class="form-control form-control-lg bg-white" name="insurance_expiry_date" value="<?= $driver['insurance_expiry_date'] ?>">
            </div>

            <!-- Row 3: Driving licence & Driving licence expiry date -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Driving licence</label>
              <input type="text" class="form-control form-control-lg bg-white" name="driving_licence" value="<?= htmlspecialchars($driver['driving_licence'] ?: $driver['license_number']) ?>" placeholder="Driving licence">
            </div>
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Driving licence expiry date</legend>
                <input type="date" class="form-control border-0 shadow-none" name="driving_licence_expiry" value="<?= $driver['driving_licence_expiry'] ?: $driver['license_expiry_date'] ?>">
              </fieldset>
            </div>

            <!-- Row 4: Passenger transport driver licence & expiry date -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Passenger transport driver licence</label>
              <input type="text" class="form-control form-control-lg bg-white" name="pt_driver_licence" value="<?= htmlspecialchars($driver['pt_driver_licence']) ?>" placeholder="Passenger transport driver licence">
            </div>
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Passenger transport driver licence expiry date</legend>
                <input type="date" class="form-control border-0 shadow-none" name="pt_driver_licence_expiry" value="<?= $driver['pt_driver_licence_expiry'] ?>">
              </fieldset>
            </div>

            <!-- Row 5: Passenger transport vehicle licence & expiry date -->
            <div class="col-md-6">
              <label class="form-label text-muted small">Passenger transport vehicle licence</label>
              <input type="text" class="form-control form-control-lg bg-white" name="pt_vehicle_licence" value="<?= htmlspecialchars($driver['pt_vehicle_licence']) ?>" placeholder="Passenger transport vehicle licence">
            </div>
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Passenger transport vehicle licence expiry date</legend>
                <input type="date" class="form-control border-0 shadow-none" name="pt_vehicle_licence_expiry" value="<?= $driver['pt_vehicle_licence_expiry'] ?>">
              </fieldset>
            </div>

            <!-- Row 6: Driver income (%) & Base address -->
            <div class="col-md-6">
              <fieldset class="border p-1 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0">Driver income (%)</legend>
                <input type="number" step="0.1" class="form-control border-0 shadow-none" name="driver_income_pct" value="<?= floatval($driver['driver_income_pct']) ?>" placeholder="0">
              </fieldset>
            </div>
            <div class="col-md-6">
              <label class="form-label text-muted small">Base address</label>
              <input type="text" class="form-control form-control-lg bg-white" name="base_address" value="<?= htmlspecialchars($driver['base_address']) ?>" placeholder="Base address">
            </div>

            <!-- Row 7: Driver activity status -->
            <div class="col-12">
              <fieldset class="border p-2 px-3 rounded bg-white">
                <legend class="float-none w-auto px-1 fs-6 text-muted mb-0 d-flex justify-content-between align-items-center">
                  <span>Driver activity status</span>
                </legend>
                <div class="d-flex align-items-center gap-2">
                  <select class="form-select border-0 shadow-none" name="activity_status">
                    <option value="Available" <?= ($driver['activity_status']==='Available'||empty($driver['activity_status'])) ? 'selected' : '' ?>>Available</option>
                    <option value="Unavailable" <?= $driver['activity_status']==='Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    <option value="On Job" <?= $driver['activity_status']==='On Job' ? 'selected' : '' ?>>On Job</option>
                  </select>
                  <i class="fas fa-info-circle text-secondary fs-5" title="Driver's real-time dispatch availability"></i>
                </div>
              </fieldset>
            </div>

            <!-- Additional files Section -->
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
              <button type="button" class="btn btn-success fw-bold btn-sm px-3" onclick="alert('File attachment uploader ready!')">
                <i class="fas fa-plus me-1"></i> New file
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- Update / Cancel Buttons (Exact match to User Screenshots) -->
      <div class="mt-4 pt-3 border-top d-flex gap-3 align-items-center">
        <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
          Update
        </button>
        <a href="drivers.php" class="btn btn-link text-decoration-none text-secondary">
          Cancel
        </a>
      </div>

    </form>
  </div>
</div>

<script>
function generateEditRandomPwd() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
  let pwd = '';
  for (let i = 0; i < 10; i++) {
    pwd += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  document.getElementById('edit_pwd_input').value = pwd;
  document.getElementById('edit_cpwd_input').value = pwd;
}

function toggleEditPwdVisibility(inputId, eyeId) {
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
