<?php
// All POST/GET logic runs BEFORE any HTML output — fixes blank page on redirect
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();

// Auto-migrate missing columns for td_vehicles to prevent SQL errors
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
$err = '';

// Fetch all available drivers for the driver dropdown
$drivers_list = [];
try {
    $drivers_list = $pdo->query("SELECT d.id as driver_id, u.name, u.email 
        FROM td_drivers d 
        JOIN td_users u ON d.user_id = u.id 
        ORDER BY u.name")->fetchAll();
} catch (Exception $e) {}

// ── POST: Add / Update Status / Delete All ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name                     = trim($_POST['name'] ?? '');
        $type                     = trim($_POST['type'] ?? 'sedan');
        $assigned_driver_id       = !empty($_POST['assigned_driver_id']) ? intval($_POST['assigned_driver_id']) : null;
        $registration_mark        = trim($_POST['registration_mark'] ?? '');
        $technical_inspection     = trim($_POST['technical_inspection'] ?? '');
        $inspection_expiry_date   = !empty($_POST['inspection_expiry_date']) ? $_POST['inspection_expiry_date'] : null;
        $make                     = trim($_POST['make'] ?? '');
        $color                    = trim($_POST['color'] ?? '');
        $model                    = trim($_POST['model'] ?? '');
        $body_type                = trim($_POST['body_type'] ?? '');
        $capacity                 = intval($_POST['capacity'] ?? 4);
        $keeper_name              = trim($_POST['keeper_name'] ?? '');
        $keeper_address           = trim($_POST['keeper_address'] ?? '');
        $notes                    = trim($_POST['notes'] ?? '');
        $status                   = trim($_POST['status'] ?? 'active');
        $image_path               = '';

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $ftype   = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($ftype, $allowed)) {
                $err = "Invalid image type. Please upload JPG, PNG, WEBP or GIF.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $err = "Image too large. Maximum size is 5MB.";
            } else {
                $ext        = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename   = 'vehicle_' . time() . '_' . rand(100,999) . '.' . strtolower($ext);
                $upload_dir = dirname(__DIR__) . '/uploads/vehicles/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                    $image_path = 'uploads/vehicles/' . $filename;
                } else {
                    $err = "Failed to save image. Please try again.";
                }
            }
        }

        if (empty($err)) {
            try {
                $pdo->prepare("INSERT INTO td_vehicles 
                    (name, type, assigned_driver_id, registration_mark, license_plate, technical_inspection, inspection_expiry_date, make, color, model, body_type, capacity, keeper_name, keeper_address, notes, status, image) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([
                        $name, $type, $assigned_driver_id, $registration_mark, $registration_mark, 
                        $technical_inspection, $inspection_expiry_date, $make, $color, 
                        $model, $body_type, $capacity, $keeper_name, $keeper_address, 
                        $notes, $status, $image_path
                    ]);
                
                header("Location: " . APP_URL . "/admin/vehicles.php?msg=added");
                exit;
            } catch (Exception $e) {
                $err = "Database error: " . htmlspecialchars($e->getMessage());
            }
        }

    } elseif ($action === 'update_status') {
        $pdo->prepare("UPDATE td_vehicles SET status=? WHERE id=?")
            ->execute([trim($_POST['status']), intval($_POST['vid'])]);
        header("Location: " . APP_URL . "/admin/vehicles.php?msg=updated");
        exit;
    } elseif ($action === 'delete_all_vehicles') {
        $pdo->exec("DELETE FROM td_vehicles");
        header("Location: " . APP_URL . "/admin/vehicles.php?msg=all_deleted");
        exit;
    }
}

// ── GET: Delete single vehicle ───────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $vid = intval($_GET['delete']);
    $row = $pdo->prepare("SELECT image FROM td_vehicles WHERE id=?");
    $row->execute([$vid]);
    $vdata = $row->fetch();
    if ($vdata && $vdata['image'] && file_exists(dirname(__DIR__) . '/' . $vdata['image'])) {
        unlink(dirname(__DIR__) . '/' . $vdata['image']);
    }
    $pdo->prepare("DELETE FROM td_vehicles WHERE id=?")->execute([$vid]);
    header("Location: " . APP_URL . "/admin/vehicles.php?msg=deleted");
    exit;
}

// ── Safe to output HTML ──────────────────────────────────────────────────────
$page_title = 'Vehicles';
require_once 'header.php';

$vehicles = $pdo->query("SELECT v.*, u.name as driver_name
    FROM td_vehicles v
    LEFT JOIN td_drivers d ON v.assigned_driver_id = d.id OR v.id = d.vehicle_id
    LEFT JOIN td_users u ON d.user_id = u.id
    GROUP BY v.id
    ORDER BY v.id DESC")->fetchAll();

$msg = $_GET['msg'] ?? '';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item"><a href="vehicles.php">Vehicles</a></li>
    <li class="breadcrumb-item active">Add &amp; Manage Fleet</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
  <?= $msg==='added' ? '✅ Vehicle added successfully!' : ($msg==='deleted' ? '🗑️ Vehicle removed.' : ($msg==='all_deleted' ? '🗑️ All vehicles removed.' : '✅ Updated.')) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger mb-4">❌ <?= $err ?></div><?php endif; ?>

<!-- Top Action Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="fas fa-car me-2 text-warning"></i>Vehicles &amp; Fleet</h4>
    <div class="text-muted small mt-1">Settings &rsaquo; Vehicles &rsaquo; Add &amp; Manage</div>
  </div>
  <div class="d-flex gap-2">
    <!-- Delete All Vehicles Button -->
    <button type="button" class="btn btn-outline-danger btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#deleteAllVehiclesModal" style="border-radius:.6rem;">
      <i class="fas fa-trash-alt"></i> Delete All Vehicles
    </button>
    <!-- Add New Vehicle Toggle Button -->
    <button type="button" class="btn btn-warning btn-lg px-4 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="collapse" data-bs-target="#addVehicleCollapse" aria-expanded="<?= (empty($vehicles) || $err) ? 'true' : 'false' ?>" style="border-radius:.6rem;">
      <i class="fas fa-plus-circle fa-lg"></i> Add New Vehicle
    </button>
  </div>
</div>

<!-- Delete All Vehicles Modal -->
<div class="modal fade" id="deleteAllVehiclesModal" tabindex="-1" aria-labelledby="deleteAllVehiclesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold" id="deleteAllVehiclesModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Delete All Vehicles?
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-3">Are you sure you want to <strong>permanently delete ALL vehicles</strong> from the fleet list?</p>
        <div class="alert alert-warning small mb-0">
          <i class="fas fa-info-circle me-1"></i> <strong>Warning:</strong> This action cannot be undone.
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="action" value="delete_all_vehicles">
          <button type="submit" class="btn btn-danger fw-bold">
            <i class="fas fa-trash-alt me-1"></i> Yes, Delete ALL Vehicles
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add Vehicle Form (Exact Match to Client Screenshot) -->
<div class="collapse <?= (empty($vehicles) || $err) ? 'show' : '' ?> mb-4" id="addVehicleCollapse">
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
      <span class="fs-5 text-dark">Settings &rsaquo; Vehicles &rsaquo; Add</span>
      <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#addVehicleCollapse"></button>
    </div>
    <div class="card-body p-4" style="background:#fafafa;">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div class="row g-3">
          <!-- Row 1: Name & Vehicle type -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Name</label>
            <input type="text" class="form-control form-control-lg bg-white" name="name" placeholder="Vehicle Name / Identifier">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small d-flex justify-content-between align-items-center">
              <span>Vehicle type</span>
              <i class="fas fa-info-circle text-secondary" title="Select the category of the vehicle"></i>
            </label>
            <select class="form-select form-select-lg bg-white" name="type">
              <option value="sedan">Saloon / Sedan</option>
              <option value="estate">Estate</option>
              <option value="executive">Executive</option>
              <option value="van">MPV / Van</option>
              <option value="luxury">Luxury / Business</option>
              <option value="minibus">Minibus</option>
            </select>
          </div>

          <!-- Row 2: Assign driver -->
          <div class="col-12">
            <label class="form-label text-muted small">Assign driver</label>
            <select class="form-select form-select-lg bg-white" name="assigned_driver_id">
              <option value="">-- Select Driver (Optional) --</option>
              <?php foreach ($drivers_list as $d): ?>
                <option value="<?= $d['driver_id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Row 3: Upload photo -->
          <div class="col-12">
            <fieldset class="border p-3 rounded bg-white">
              <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Upload photo (512x512px)</legend>
              <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewImage(this)">
              <div id="imgPreview" class="mt-2" style="display:none">
                <img id="previewImg" src="" alt="Preview" style="max-height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
              </div>
            </fieldset>
          </div>

          <!-- Row 4: Registration mark & Technical inspection -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Registration mark</label>
            <input type="text" class="form-control form-control-lg bg-white" name="registration_mark" placeholder="License Plate / Reg Mark">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small">Technical inspection</label>
            <input type="text" class="form-control form-control-lg bg-white" name="technical_inspection" placeholder="MOT / Inspection Serial No.">
          </div>

          <!-- Row 5: Technical inspection expiry date & Make -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Technical inspection expiry date</label>
            <input type="date" class="form-control form-control-lg bg-white" name="inspection_expiry_date">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small">Make</label>
            <input type="text" class="form-control form-control-lg bg-white" name="make" placeholder="e.g. Mercedes-Benz">
          </div>

          <!-- Row 6: Model & Colour -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Model</label>
            <input type="text" class="form-control form-control-lg bg-white" name="model" placeholder="e.g. E-Class / E220d">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small">Colour</label>
            <input type="text" class="form-control form-control-lg bg-white" name="color" placeholder="e.g. Black / Silver">
          </div>

          <!-- Row 7: Body type & Passenger capacity -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Body type</label>
            <input type="text" class="form-control form-control-lg bg-white" name="body_type" placeholder="e.g. Saloon, Estate, MPV">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small">Passenger capacity</label>
            <input type="number" class="form-control form-control-lg bg-white" name="capacity" min="1" max="30" value="4">
          </div>

          <!-- Row 8: Registered keeper name & Registered keeper address -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Registered keeper name</label>
            <input type="text" class="form-control form-control-lg bg-white" name="keeper_name" placeholder="Keeper Name">
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small">Registered keeper address</label>
            <textarea class="form-control bg-white" name="keeper_address" rows="2" placeholder="Keeper Address..."></textarea>
          </div>

          <!-- Row 9: Notes & Status -->
          <div class="col-md-6">
            <label class="form-label text-muted small">Notes</label>
            <textarea class="form-control bg-white" name="notes" rows="3" placeholder="Notes..."></textarea>
          </div>
          <div class="col-md-6">
            <fieldset class="border p-3 rounded bg-white">
              <legend class="float-none w-auto px-2 fs-6 text-muted mb-0">Status</legend>
              <select class="form-select form-select-lg border-0 shadow-none" name="status">
                <option value="active" selected>Activated</option>
                <option value="inactive">Deactivated</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </fieldset>
          </div>

          <!-- Buttons -->
          <div class="col-12 mt-4 d-flex gap-3 align-items-center">
            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
              Add
            </button>
            <button type="button" class="btn btn-link text-decoration-none text-secondary" data-bs-toggle="collapse" data-bs-target="#addVehicleCollapse">
              Cancel
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Vehicles Fleet Table -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
    <span class="fs-5">🚗 Fleet List (<?= count($vehicles) ?>)</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="table-dark">
          <tr>
            <th>Photo</th>
            <th>Name / Vehicle</th>
            <th>Reg Mark</th>
            <th>Type</th>
            <th>Cap.</th>
            <th>Inspection / Expiry</th>
            <th>Keeper</th>
            <th>Assigned Driver</th>
            <th>Status</th>
            <th class="text-end pe-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vehicles as $v): ?>
          <tr>
            <td style="width:70px">
              <?php if (!empty($v['image'])): ?>
                <img src="<?= APP_URL . '/' . htmlspecialchars($v['image']) ?>"
                     alt="Vehicle" style="width:60px;height:45px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
              <?php else: ?>
                <div style="width:60px;height:45px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:22px;">🚗</div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= htmlspecialchars($v['name'] ?: ($v['color'] . ' ' . $v['make'] . ' ' . $v['model'])) ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?></small>
            </td>
            <td><span class="badge bg-dark fs-6"><?= htmlspecialchars($v['registration_mark'] ?: $v['license_plate']) ?></span></td>
            <td><span class="badge bg-secondary"><?= ucfirst($v['type']) ?></span></td>
            <td><?= $v['capacity'] ?> pax</td>
            <td>
              <small>
                <?= $v['technical_inspection'] ? htmlspecialchars($v['technical_inspection']) : '-' ?><br>
                <span class="text-muted">Exp: <?= $v['inspection_expiry_date'] ? date('d.m.Y', strtotime($v['inspection_expiry_date'])) : '-' ?></span>
              </small>
            </td>
            <td><small><?= $v['keeper_name'] ? htmlspecialchars($v['keeper_name']) : '-' ?></small></td>
            <td>
              <?php if (!empty($v['assigned_driver_ids'])): ?>
                <small class="text-dark fw-semibold"><?= htmlspecialchars($v['assigned_driver_ids']) ?></small>
              <?php elseif (!empty($v['driver_name'])): ?>
                <small class="text-dark fw-semibold"><?= htmlspecialchars($v['driver_name']) ?></small>
              <?php else: ?>
                <small class="text-muted">Unassigned</small>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="vid" value="<?= $v['id'] ?>">
                <select name="status" class="form-select form-select-sm" style="width:130px" onchange="this.form.submit()">
                  <option value="active" <?= ($v['status']==='active'||$v['status']==='activated') ? 'selected' : '' ?>>Activated</option>
                  <option value="inactive" <?= ($v['status']==='inactive'||$v['status']==='deactivated') ? 'selected' : '' ?>>Deactivated</option>
                  <option value="maintenance" <?= $v['status']==='maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
              </form>
            </td>
            <td class="text-end pe-3">
              <a href="vehicles.php?delete=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Permanently remove this vehicle?')">
                <i class="fas fa-trash-alt"></i> Delete
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($vehicles)): ?>
          <tr>
            <td colspan="10" class="text-center text-muted py-5">
              <i class="fas fa-car fa-2x mb-2 text-secondary"></i><br>
              No vehicles in the fleet yet. Click <strong>"Add New Vehicle"</strong> above to add your first vehicle!
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function previewImage(input) {
  var preview = document.getElementById('imgPreview');
  var img = document.getElementById('previewImg');
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
</script>

<?php require_once 'footer.php'; ?>
