<?php
require_once '../includes/config.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$pdo = db_connect();

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// Handle POST: Add / Edit Vehicle
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_vehicle') {
        $vid = intval($_POST['vehicle_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $license_plate = trim($_POST['license_plate'] ?? '') ?: $name;
        $type = trim($_POST['type'] ?? '');
        $assigned_drivers = trim($_POST['assigned_drivers'] ?? '');
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $body_type = trim($_POST['body_type'] ?? '');
        $capacity = !empty($_POST['capacity']) ? intval($_POST['capacity']) : null;
        $status = trim($_POST['status'] ?? 'Activated');
        $registration_mark = trim($_POST['registration_mark'] ?? '');
        $technical_inspection = trim($_POST['technical_inspection'] ?? '');

        if (!$name) {
            $err = "Vehicle name is required.";
        } else {
            if ($vid > 0) {
                $stmt = $pdo->prepare("UPDATE td_vehicles SET 
                    name = ?, license_plate = ?, type = ?, assigned_drivers = ?, 
                    make = ?, model = ?, color = ?, body_type = ?, capacity = ?, 
                    status = ?, registration_mark = ?, technical_inspection = ?, 
                    updated_at = NOW() 
                    WHERE id = ?");
                $stmt->execute([
                    $name, $license_plate, $type, $assigned_drivers,
                    $make, $model, $color, $body_type, $capacity,
                    $status, $registration_mark, $technical_inspection,
                    $vid
                ]);
                header("Location: vehicles.php?msg=updated");
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO td_vehicles 
                    (name, license_plate, type, assigned_drivers, make, model, color, body_type, capacity, status, registration_mark, technical_inspection, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([
                    $name, $license_plate, $type, $assigned_drivers,
                    $make, $model, $color, $body_type, $capacity,
                    $status, $registration_mark, $technical_inspection
                ]);
                header("Location: vehicles.php?msg=added");
                exit;
            }
        }
    }
}

// Handle GET: Delete
if (isset($_GET['delete'])) {
    $vid = intval($_GET['delete']);
    if ($vid > 0) {
        $stmt = $pdo->prepare("DELETE FROM td_vehicles WHERE id = ?");
        $stmt->execute([$vid]);
        header("Location: vehicles.php?msg=deleted");
        exit;
    }
}

// Fetch all vehicles
$vehicles = $pdo->query("SELECT * FROM td_vehicles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all drivers for assigned drivers dropdown/helper
$drivers = [];
try {
    $drivers = $pdo->query("SELECT u.name FROM td_drivers d JOIN td_users u ON d.user_id = u.id ORDER BY u.name")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

$page_title = 'Settings > Vehicles';
require_once 'header.php';
?>

<!-- Breadcrumb matching sample -->
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb" style="font-size:16px;">
    <li class="breadcrumb-item"><a href="settings.php" class="text-decoration-none text-muted">Settings</a></li>
    <li class="breadcrumb-item active text-dark fw-normal" aria-current="page">Vehicles</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
  <?= $msg==='added' ? 'Vehicle created successfully.' : ($msg==='updated' ? 'Vehicle updated successfully.' : 'Vehicle deleted successfully.') ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($err): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4">
  <?= htmlspecialchars($err) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Top Actions Toolbar matching sample -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div class="d-flex align-items-center gap-2">
    <div class="btn-group border rounded bg-white shadow-sm">
      <button type="button" class="btn btn-light btn-sm text-muted px-2" title="View mode"><i class="fas fa-eye"></i></button>
      <button type="button" class="btn btn-light btn-sm text-muted px-2" onclick="location.reload();" title="Reset"><i class="fas fa-undo"></i></button>
      <button type="button" class="btn btn-light btn-sm text-muted px-2" onclick="location.reload();" title="Refresh"><i class="fas fa-sync-alt"></i></button>
      <button type="button" class="btn btn-light btn-sm text-muted px-2" title="Search"><i class="fas fa-search"></i></button>
    </div>

    <button type="button" class="btn btn-success btn-sm px-3 fw-semibold shadow-sm d-flex align-items-center gap-1" onclick="openVehicleModal()" style="background:#28a745;border-color:#28a745;height:31px;">
      <i class="fas fa-plus"></i> Add new
    </button>
  </div>

  <div class="d-flex align-items-center gap-2">
    <div class="input-group input-group-sm" style="width: 240px;">
      <input type="text" id="vehicleSearch" class="form-control" placeholder="Search..." onkeyup="filterVehiclesTable()">
      <span class="input-group-text bg-white text-muted"><i class="fas fa-search"></i></span>
    </div>
    <span class="text-muted" title="Help / Information" style="cursor:pointer;"><i class="far fa-question-circle"></i></span>
  </div>
</div>

<!-- Vehicles Table Card with Horizontal Scroll -->
<div class="card border-0 shadow-sm rounded-0">
  <div class="card-body p-0">
    <div class="table-responsive" style="overflow-x: auto;">
      <table class="table table-hover align-middle mb-0" id="vehiclesTable" style="min-width: 950px; white-space: nowrap; font-size:14px;">
        <thead style="background:#fdfdfd; border-bottom:1px solid #dee2e6;">
          <tr class="text-muted" style="font-size:13px; font-weight:600;">
            <th style="width: 45px;" class="ps-3"></th>
            <th style="width: 70px;">Photo</th>
            <th>Name</th>
            <th>Assign driver</th>
            <th>Vehicle type</th>
            <th>Registration mark</th>
            <th>Technical inspection</th>
            <th class="pe-3">Status</th>
          </tr>
        </thead>
        <tbody id="vehiclesTableBody">
          <?php foreach ($vehicles as $v): ?>
          <tr style="border-bottom: 1px solid #f2f2f2;">
            <!-- Actions dropdown with eye icon -->
            <td class="ps-3">
              <div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle py-0 px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:#6c757d; font-size:12px;">
                  <i class="fas fa-eye"></i>
                </button>
                <ul class="dropdown-menu shadow-sm border-0">
                  <li><a class="dropdown-item" href="vehicle_view.php?id=<?= $v['id'] ?>"><i class="fas fa-eye text-primary me-2"></i>View</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0)" onclick='editVehicleModal(<?= json_encode($v) ?>)'><i class="fas fa-edit text-info me-2"></i>Edit</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="vehicles.php?delete=<?= $v['id'] ?>" onclick="return confirm('Delete vehicle <?= htmlspecialchars($v['name']) ?>?')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                </ul>
              </div>
            </td>

            <!-- Photo Circle Icon -->
            <td>
              <div style="width:38px;height:38px;background:#e9ecef;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:16px;">
                <i class="fas fa-car"></i>
              </div>
            </td>

            <!-- Name (Clickable link to view) -->
            <td>
              <a href="vehicle_view.php?id=<?= $v['id'] ?>" class="text-decoration-none fw-semibold" style="color:#337ab7;">
                <?= htmlspecialchars($v['name']) ?>
              </a>
            </td>

            <!-- Assign driver -->
            <td class="text-dark" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis;">
              <?= htmlspecialchars($v['assigned_drivers'] ?: '-') ?>
            </td>

            <!-- Vehicle type -->
            <td class="text-muted">
              <?= htmlspecialchars($v['type'] ?: '') ?>
            </td>

            <!-- Registration mark -->
            <td class="text-muted">
              <?= htmlspecialchars($v['registration_mark'] ?: '') ?>
            </td>

            <!-- Technical inspection -->
            <td class="text-muted">
              <?= htmlspecialchars($v['technical_inspection'] ?: '') ?>
            </td>

            <!-- Status Badge -->
            <td class="pe-3">
              <?php if (strtolower($v['status']) === 'activated' || strtolower($v['status']) === 'active'): ?>
                <span class="badge bg-success px-2 py-1" style="font-weight:500;font-size:11px;">Activated</span>
              <?php else: ?>
                <span class="badge bg-secondary px-2 py-1" style="font-weight:500;font-size:11px;"><?= htmlspecialchars($v['status']) ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>

          <?php if (empty($vehicles)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              No vehicles found. Click "+ Add new" to create one.
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Add / Edit Vehicle Modal Dialog ── -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" id="vehicleForm">
        <input type="hidden" name="action" value="save_vehicle">
        <input type="hidden" name="vehicle_id" id="v_id" value="0">

        <div class="modal-header border-bottom py-3">
          <h5 class="modal-title fw-bold" id="vehicleModalLabel"><i class="fas fa-car text-success me-2"></i>Add Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label text-muted small fw-semibold">Vehicle Name / Plate <span class="text-danger">*</span></label>
              <input type="text" name="name" id="v_name" class="form-control" placeholder="e.g. M-QM 510" required>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small fw-semibold">Vehicle Type</label>
              <select name="type" id="v_type" class="form-select">
                <option value="">-- Select Type --</option>
                <option value="First Class">First Class</option>
                <option value="First Class XL">First Class XL</option>
                <option value="Business Class">Business Class</option>
                <option value="Business Class XL">Business Class XL</option>
                <option value="Business Class XXL">Business Class XXL</option>
                <option value="Economy Class">Economy Class</option>
                <option value="Economy Premium Class">Economy Premium Class</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label text-muted small fw-semibold">Assigned Drivers (comma separated)</label>
              <input type="text" name="assigned_drivers" id="v_assigned_drivers" class="form-control" placeholder="Alex, Belhassen, Zaidan, Colhon, Rached, Seleiman, Catalin, Ionut">
            </div>

            <div class="col-md-4">
              <label class="form-label text-muted small fw-semibold">Make</label>
              <input type="text" name="make" id="v_make" class="form-control" placeholder="e.g. Mercedes">
            </div>

            <div class="col-md-4">
              <label class="form-label text-muted small fw-semibold">Model</label>
              <input type="text" name="model" id="v_model" class="form-control" placeholder="e.g. Mercedes or E-Class">
            </div>

            <div class="col-md-4">
              <label class="form-label text-muted small fw-semibold">Colour</label>
              <input type="text" name="color" id="v_color" class="form-control" placeholder="e.g. Black">
            </div>

            <div class="col-md-4">
              <label class="form-label text-muted small fw-semibold">Body Type</label>
              <input type="text" name="body_type" id="v_body_type" class="form-control" placeholder="e.g. Limousine">
            </div>

            <div class="col-md-4">
              <label class="form-label text-muted small fw-semibold">Passenger Capacity</label>
              <input type="number" name="capacity" id="v_capacity" class="form-control" placeholder="e.g. 3 or 7">
            </div>

            <div class="col-md-4">
              <label class="form-label text-muted small fw-semibold">Status</label>
              <select name="status" id="v_status" class="form-select">
                <option value="Activated" selected>Activated</option>
                <option value="Deactivated">Deactivated</option>
                <option value="Maintenance">Maintenance</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-5 fw-bold" id="v_submit_btn">Save Vehicle</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openVehicleModal() {
  document.getElementById('vehicleForm').reset();
  document.getElementById('v_id').value = '0';
  document.getElementById('vehicleModalLabel').innerHTML = '<i class="fas fa-car text-success me-2"></i>Add Vehicle';
  document.getElementById('v_submit_btn').innerText = 'Save Vehicle';

  var el = document.getElementById('vehicleModal');
  var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  modal.show();
}

function editVehicleModal(data) {
  document.getElementById('vehicleForm').reset();
  document.getElementById('v_id').value = data.id || 0;
  document.getElementById('v_name').value = data.name || '';
  document.getElementById('v_type').value = data.type || '';
  document.getElementById('v_assigned_drivers').value = data.assigned_drivers || '';
  document.getElementById('v_make').value = data.make || '';
  document.getElementById('v_model').value = data.model || '';
  document.getElementById('v_color').value = data.color || '';
  document.getElementById('v_body_type').value = data.body_type || '';
  document.getElementById('v_capacity').value = data.capacity || '';
  document.getElementById('v_status').value = data.status || 'Activated';

  document.getElementById('vehicleModalLabel').innerHTML = '<i class="fas fa-edit text-primary me-2"></i>Edit Vehicle: ' + (data.name || '');
  document.getElementById('v_submit_btn').innerText = 'Save Changes';

  var el = document.getElementById('vehicleModal');
  var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  modal.show();
}

function filterVehiclesTable() {
  var input = document.getElementById('vehicleSearch');
  var filter = input.value.toLowerCase();
  var rows = document.getElementById('vehiclesTableBody').getElementsByTagName('tr');
  for (var i = 0; i < rows.length; i++) {
    var text = rows[i].textContent || rows[i].innerText;
    rows[i].style.display = (text.toLowerCase().indexOf(filter) > -1) ? '' : 'none';
  }
}
</script>

<?php require_once 'footer.php'; ?>
