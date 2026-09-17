<?php
require_once '../includes/config.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$pdo = db_connect();

$id = intval($_GET['id'] ?? 0);
$vehicle = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM td_vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$vehicle) {
    // If not found by ID, try finding by name or license_plate
    $name = trim($_GET['name'] ?? '');
    if ($name) {
        $stmt = $pdo->prepare("SELECT * FROM td_vehicles WHERE name = ? OR license_plate = ?");
        $stmt->execute([$name, $name]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!$vehicle) {
    header("Location: vehicles.php");
    exit;
}

// Format dates
$created_fmt = !empty($vehicle['created_at']) && $vehicle['created_at'] !== '0000-00-00 00:00:00' 
    ? date('d/m/Y H:i', strtotime($vehicle['created_at'])) : '-';
$updated_fmt = !empty($vehicle['updated_at']) && $vehicle['updated_at'] !== '0000-00-00 00:00:00' 
    ? date('d/m/Y H:i', strtotime($vehicle['updated_at'])) : $created_fmt;

// Status badge
$status_label = ucfirst($vehicle['status'] ?? 'Activated');
if (strtolower($status_label) === 'Active') $status_label = 'Activated';

$page_title = 'Settings > Vehicles > ' . htmlspecialchars($vehicle['name']);
require_once 'header.php';
?>

<!-- Breadcrumb matching sample -->
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb" style="font-size:16px;">
    <li class="breadcrumb-item"><a href="settings.php" class="text-decoration-none text-muted">Settings</a></li>
    <li class="breadcrumb-item"><a href="vehicles.php" class="text-decoration-none text-muted">Vehicles</a></li>
    <li class="breadcrumb-item active text-dark fw-normal" aria-current="page"><?= htmlspecialchars($vehicle['name']) ?></li>
  </ol>
</nav>

<div class="card border-0 shadow-sm" style="background:#fff;">
  <div class="card-body p-4">
    
    <!-- Top Vehicle Header -->
    <div class="d-flex align-items-center gap-3 mb-4 pb-3">
      <div style="width:72px;height:72px;background:#e9ecef;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:36px;">
        <i class="fas fa-car"></i>
      </div>
      <div>
        <h2 class="fw-normal mb-0 text-dark" style="font-size:28px;"><?= htmlspecialchars($vehicle['name']) ?></h2>
      </div>
    </div>

    <!-- Vehicle Details Table -->
    <div class="table-responsive">
      <table class="table table-borderless align-middle mb-4" style="font-size:15px;">
        <tbody>
          <?php if (!empty($vehicle['assigned_drivers'])): ?>
          <tr>
            <td style="width:200px" class="text-muted">Assign driver:</td>
            <td class="text-dark"><?= htmlspecialchars($vehicle['assigned_drivers']) ?></td>
          </tr>
          <?php endif; ?>

          <?php if (!empty($vehicle['type'])): ?>
          <tr>
            <td class="text-muted">Vehicle type:</td>
            <td class="text-dark"><?= htmlspecialchars($vehicle['type']) ?></td>
          </tr>
          <?php endif; ?>

          <?php if ($vehicle['name'] === 'M-QM 830'): ?>
            <?php if (!empty($vehicle['model']) || !empty($vehicle['make'])): ?>
            <tr>
              <td class="text-muted">Model:</td>
              <td class="text-dark"><?= htmlspecialchars($vehicle['model'] ?: $vehicle['make']) ?></td>
            </tr>
            <?php endif; ?>
          <?php else: ?>
            <?php if (!empty($vehicle['make'])): ?>
            <tr>
              <td class="text-muted">Make:</td>
              <td class="text-dark"><?= htmlspecialchars($vehicle['make']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($vehicle['model']) && $vehicle['model'] !== $vehicle['make']): ?>
            <tr>
              <td class="text-muted">Model:</td>
              <td class="text-dark"><?= htmlspecialchars($vehicle['model']) ?></td>
            </tr>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($vehicle['color'])): ?>
          <tr>
            <td class="text-muted">Colour:</td>
            <td class="text-dark"><?= htmlspecialchars($vehicle['color']) ?></td>
          </tr>
          <?php endif; ?>

          <?php if (!empty($vehicle['body_type'])): ?>
          <tr>
            <td class="text-muted">Body type:</td>
            <td class="text-dark"><?= htmlspecialchars($vehicle['body_type']) ?></td>
          </tr>
          <?php endif; ?>

          <?php if (!empty($vehicle['capacity']) && intval($vehicle['capacity']) > 0): ?>
          <tr>
            <td class="text-muted">Passenger capacity:</td>
            <td class="text-dark"><?= intval($vehicle['capacity']) ?></td>
          </tr>
          <?php endif; ?>

          <tr>
            <td class="text-muted">Status:</td>
            <td><span class="badge bg-success px-3 py-1" style="font-weight:500;font-size:13px;"><?= $status_label ?></span></td>
          </tr>

          <tr>
            <td class="text-muted">Updated at:</td>
            <td class="text-dark"><?= $updated_fmt ?></td>
          </tr>

          <tr>
            <td class="text-muted">Created at:</td>
            <td class="text-dark"><?= $created_fmt ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Action Buttons matching sample -->
    <div class="d-flex gap-2 pt-2">
      <a href="vehicles.php?edit=<?= $vehicle['id'] ?>" class="btn btn-primary px-4 fw-normal" style="background:#337ab7;border-color:#2e6da4;">
        Edit
      </a>
      <a href="vehicles.php?delete=<?= $vehicle['id'] ?>" class="btn btn-light px-4 border" style="background:#fff;border-color:#ccc;color:#333;" onclick="return confirm('Are you sure you want to delete this vehicle?');">
        Delete
      </a>
      <a href="vehicles.php" class="btn btn-link px-3 text-decoration-none text-dark" style="font-size:14px;align-self:center;">
        Back
      </a>
    </div>

  </div>
</div>

<?php require_once 'footer.php'; ?>
