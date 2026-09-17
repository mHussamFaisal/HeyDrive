<?php
// All POST/GET logic runs BEFORE any HTML output — fixes blank page on redirect
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();
$err = '';

// Fetch all available drivers for the driver dropdown if needed
$drivers_list = [];
try {
    $drivers_list = $pdo->query("SELECT d.id as driver_id, u.name, u.email 
        FROM td_drivers d 
        JOIN td_users u ON d.user_id = u.id 
        ORDER BY u.name")->fetchAll();
} catch (Exception $e) {}

// ── POST: Update Status / Delete All ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
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
    <li class="breadcrumb-item active">Vehicles List</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
  <?= $msg==='deleted' ? '🗑️ Vehicle removed.' : ($msg==='all_deleted' ? '🗑️ All vehicles removed.' : '✅ Updated.') ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger mb-4">❌ <?= $err ?></div><?php endif; ?>

<!-- Top Action Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="fas fa-car me-2 text-warning"></i>Vehicles List</h4>
    <div class="text-muted small mt-1">Manage active fleet &amp; driver assignments</div>
  </div>
  <div class="d-flex gap-2">
    <a href="vehicle_types.php" class="btn btn-warning btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" style="border-radius:.6rem;">
      <i class="fas fa-cog"></i> Manage Types of Vehicles
    </a>
    <!-- Delete All Vehicles Button -->
    <button type="button" class="btn btn-outline-danger btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#deleteAllVehiclesModal" style="border-radius:.6rem;">
      <i class="fas fa-trash-alt"></i> Delete All
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

<!-- Vehicles Fleet Table Card with Horizontal Scroll -->
<div class="card border-0 shadow-sm rounded-3">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
    <span class="fs-5">🚗 Vehicles List (<?= count($vehicles) ?>)</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive" style="overflow-x: auto;">
      <table class="table table-hover mb-0 align-middle" style="min-width: 1000px; white-space: nowrap;">
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
              No vehicles found in fleet list.
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
