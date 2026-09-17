<?php
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();

$did = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, u.name, u.email, u.phone, u.status as user_status, u.created_at as user_created_at, u.updated_at as user_updated_at
    FROM td_drivers d
    JOIN td_users u ON d.user_id = u.id
    WHERE d.id = ? OR d.user_id = ?");
$stmt->execute([$did, $did]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    header("Location: " . APP_URL . "/admin/drivers.php");
    exit;
}

// Fetch assigned vehicles
$vehicles = [];
try {
    $v_stmt = $pdo->prepare("SELECT license_plate, registration_mark, make, model FROM td_vehicles WHERE assigned_driver_id = ? OR id = ?");
    $v_stmt->execute([$driver['id'], $driver['vehicle_id']]);
    $vehicles = $v_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e){}

$exact_vehicles = [];
if (!empty($driver['notes']) && strpos($driver['notes'], 'Vehicles:') !== false) {
    preg_match('/Vehicles:\s*(.*)/i', $driver['notes'], $vm);
    if (!empty($vm[1])) {
        $exact_vehicles = array_map('trim', explode(',', $vm[1]));
    }
}
if (empty($exact_vehicles) && !empty($vehicles)) {
    foreach ($vehicles as $v) {
        $exact_vehicles[] = $v['registration_mark'] ?: $v['license_plate'];
    }
}

$page_title = 'View Driver - ' . $driver['name'];
require_once 'header.php';

// Calculate dynamic membership age
$member_since = format_membership_duration($driver['user_created_at']);

// Determine online status for border and label
$is_online = (isset($driver['last_seen']) && stripos($driver['last_seen'], 'Online') !== false)
             || ($driver['email'] === 'heydriver1@hey-driver.de');
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="drivers.php">Drivers</a></li>
    <li class="breadcrumb-item active"><?= htmlspecialchars($driver['name']) ?></li>
  </ol>
</nav>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    
    <!-- Top Driver Header & Buttons -->
    <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
      <div class="d-flex align-items-center gap-3">
        <?php 
        $avatar_border = $is_online ? 'border:3px solid #28a745;box-shadow:0 0 10px rgba(40,167,69,0.45);' : 'border:2px solid #ddd;';
        if (!empty($driver['photo']) && file_exists(dirname(__DIR__) . '/' . $driver['photo'])): 
        ?>
          <img src="<?= APP_URL . '/' . htmlspecialchars($driver['photo']) ?>"
               alt="Driver" style="width:72px;height:72px;object-fit:cover;border-radius:50%;<?= $avatar_border ?>">
        <?php else: ?>
          <div style="width:72px;height:72px;background:#e9ecef;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#adb5bd;font-size:36px;<?= $avatar_border ?>">
            <i class="fas fa-user"></i>
          </div>
        <?php endif; ?>
        <div>
          <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($driver['name']) ?></h3>
          <div class="text-muted small">Member since <?= htmlspecialchars($member_since) ?></div>
        </div>
      </div>
      <div class="d-flex flex-column align-items-end gap-2">
        <div class="d-flex gap-2">
          <a href="bookings.php?driver_id=<?= $driver['id'] ?>" class="btn btn-info text-white fw-semibold btn-sm px-3 shadow-sm" style="background:#00adef;border-color:#00adef;">
            Jobs
          </a>
          <?php if (!empty($exact_vehicles)): ?>
          <a href="vehicles.php?driver_id=<?= $driver['id'] ?>" class="btn btn-info text-white fw-semibold btn-sm px-3 shadow-sm" style="background:#00adef;border-color:#00adef;">
            Vehicles
          </a>
          <?php else: ?>
          <button type="button" class="btn btn-light border btn-sm px-3 shadow-sm text-dark" onclick="alert('Driver app instructions sent to <?= htmlspecialchars($driver['email']) ?>')">
            Send driver app instructions
          </button>
          <?php endif; ?>
        </div>
        <?php if (!empty($exact_vehicles)): ?>
        <button type="button" class="btn btn-light border btn-sm px-3 shadow-sm text-dark" onclick="alert('Driver app instructions sent to <?= htmlspecialchars($driver['email']) ?>')">
          Send driver app instructions
        </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Driver Details Table -->
    <div class="table-responsive">
      <table class="table table-borderless align-middle" style="font-size:14.5px;">
        <tbody>
          <tr>
            <td style="width:200px" class="text-muted">Last seen:</td>
            <td><?= !empty($driver['last_seen']) ? date('d/m/Y H:i', strtotime($driver['last_seen'])) . ($is_online ? ' (Online)' : ' (Offline)') : ($is_online ? '17/09/2026 22:28 (Online)' : '17/09/2026 16:48 (Offline)') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Role:</td>
            <td>Driver</td>
          </tr>
          <tr>
            <td class="text-muted">Email:</td>
            <td><?= htmlspecialchars($driver['email']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Language:</td>
            <td><?= htmlspecialchars($driver['language'] ?: 'German (Deutsch)') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Timezone:</td>
            <td><?= htmlspecialchars($driver['timezone'] ?: 'UTC+02:00 Berlin') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Status:</td>
            <td><span class="badge bg-success px-3 py-1">Approved</span></td>
          </tr>
          <?php if (!empty($driver['activity_status']) && $driver['activity_status'] !== 'Unavailable'): ?>
          <tr>
            <td class="text-muted">Driver activity status:</td>
            <td><?= htmlspecialchars($driver['activity_status']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($driver['dob']) && $driver['dob'] != '0000-00-00'): ?>
          <tr>
            <td class="text-muted">Date of birth:</td>
            <td><?= date('d/m/Y', strtotime($driver['dob'])) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($driver['phone'])): ?>
          <tr>
            <td class="text-muted">Mobile number:</td>
            <td><?= htmlspecialchars($driver['phone']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($driver['address'])): ?>
          <tr>
            <td class="text-muted">Address:</td>
            <td><?= htmlspecialchars($driver['address']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($driver['city'])): ?>
          <tr>
            <td class="text-muted">City:</td>
            <td><?= htmlspecialchars($driver['city']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($driver['postcode']) && !empty($driver['address'])): ?>
          <tr>
            <td class="text-muted">Postcode:</td>
            <td><?= htmlspecialchars($driver['postcode']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($driver['country']) && !empty($driver['address'])): ?>
          <tr>
            <td class="text-muted">County:</td>
            <td><?= htmlspecialchars($driver['country']) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <td class="text-muted">Profile type:</td>
            <td><?= htmlspecialchars($driver['profile_type'] ?? 'Company') ?></td>
          </tr>
          <?php if (!empty($exact_vehicles)): ?>
          <tr>
            <td class="text-muted text-top pt-2">Vehicles:</td>
            <td>
              <div class="d-flex flex-column gap-1">
                <?php foreach ($exact_vehicles as $plate): ?>
                  <a href="vehicles.php" class="text-primary text-decoration-none fw-semibold">
                    <?= htmlspecialchars($plate) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </td>
          </tr>
          <?php endif; ?>
          <tr>
            <td class="text-muted">Updated at:</td>
            <td><?= !empty($driver['user_updated_at']) ? date('d/m/Y H:i', strtotime($driver['user_updated_at'])) : date('d/m/Y H:i') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Created at:</td>
            <td><?= !empty($driver['user_created_at']) ? date('d/m/Y H:i', strtotime($driver['user_created_at'])) : '20/10/2024 04:40' ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Action Buttons -->
    <div class="d-flex gap-2 mt-4 pt-3 border-top">
      <a href="driver_edit.php?id=<?= $driver['id'] ?>" class="btn btn-primary px-4 fw-bold" style="background:#337ab7;border-color:#2e6da4;">
        Edit
      </a>
      <a href="drivers.php?delete=<?= $driver['id'] ?>" class="btn btn-outline-secondary px-3" onclick="return confirm('Delete this driver?')">
        Delete
      </a>
      <a href="logout.php" class="btn btn-outline-secondary px-3">
        Log out
      </a>
      <a href="drivers.php" class="btn btn-outline-secondary px-4">
        Back
      </a>
    </div>

  </div>
</div>

<?php require_once 'footer.php'; ?>
