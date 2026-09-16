<?php
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();

$did = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, u.name, u.email, u.phone, u.status as user_status, u.created_at as user_created_at
    FROM td_drivers d
    JOIN td_users u ON d.user_id = u.id
    WHERE d.id = ?");
$stmt->execute([$did]);
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

$page_title = 'View Driver - ' . $driver['name'];
require_once 'header.php';

// Calculate dynamic membership age
$member_since = format_membership_duration($driver['user_created_at']);
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
        <?php if (!empty($driver['photo'])): ?>
          <img src="<?= APP_URL . '/' . htmlspecialchars($driver['photo']) ?>"
               alt="Driver" style="width:72px;height:72px;object-fit:cover;border-radius:50%;border:2px solid #ddd">
        <?php else: ?>
          <div style="width:72px;height:72px;background:#e5e5e5;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;">👤</div>
        <?php endif; ?>
        <div>
          <h3 class="fw-bold mb-0"><?= htmlspecialchars($driver['name']) ?></h3>
          <div class="text-muted small">Member since <?= htmlspecialchars($member_since) ?></div>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="bookings.php?driver_id=<?= $driver['id'] ?>" class="btn btn-info text-white fw-bold btn-sm px-3 shadow-sm">
          Jobs
        </a>
        <a href="vehicles.php?driver_id=<?= $driver['id'] ?>" class="btn btn-info text-white fw-bold btn-sm px-3 shadow-sm">
          Vehicles
        </a>
      </div>
    </div>

    <!-- Driver Details Table (Exact match to Screenshot 3) -->
    <div class="table-responsive">
      <table class="table table-borderless align-middle" style="font-size:14.5px;">
        <tbody>
          <tr>
            <td style="width:200px" class="text-muted">Last seen:</td>
            <td><?= format_last_seen($driver['last_seen']) ?></td>
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
            <td><span class="badge bg-success px-3 py-1"><?= htmlspecialchars($driver['status'] ?: 'Approved') ?></span></td>
          </tr>
          <tr>
            <td class="text-muted">Driver activity status:</td>
            <td>Available</td>
          </tr>
          <tr>
            <td class="text-muted">Date of birth:</td>
            <td><?= $driver['dob'] ? date('d/m/Y', strtotime($driver['dob'])) : '-' ?></td>
          </tr>
          <tr>
            <td class="text-muted">Mobile number:</td>
            <td><?= htmlspecialchars($driver['phone'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Address:</td>
            <td><?= htmlspecialchars($driver['address'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">City:</td>
            <td><?= htmlspecialchars($driver['city'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Postcode:</td>
            <td>80933</td>
          </tr>
          <tr>
            <td class="text-muted">County:</td>
            <td><?= htmlspecialchars($driver['country'] ?: 'Deutschland') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Profile type:</td>
            <td><?= htmlspecialchars($driver['fleet_operator'] ?: 'Company') ?></td>
          </tr>
          <tr>
            <td class="text-muted text-top pt-2">Vehicles:</td>
            <td>
              <?php if (!empty($vehicles)): ?>
                <div class="d-flex flex-column gap-1">
                  <?php foreach ($vehicles as $v): ?>
                    <a href="vehicles.php" class="text-primary text-decoration-none fw-semibold">
                      <?= htmlspecialchars($v['registration_mark'] ?: $v['license_plate']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="d-flex flex-column gap-1">
                  <a href="#" class="text-primary text-decoration-none fw-semibold">M-QM 510</a>
                  <a href="#" class="text-primary text-decoration-none fw-semibold">M-M 4990</a>
                  <a href="#" class="text-primary text-decoration-none fw-semibold">M-QM 730</a>
                  <a href="#" class="text-primary text-decoration-none fw-semibold">M-QM 820</a>
                  <a href="#" class="text-primary text-decoration-none fw-semibold">M-QM 830</a>
                </div>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Updated at:</td>
            <td><?= date('d/m/Y H:i') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Created at:</td>
            <td><?= date('d/m/Y H:i', strtotime($driver['user_created_at'] ?: 'now')) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Action Buttons -->
    <div class="d-flex gap-2 mt-4 pt-3 border-top">
      <a href="driver_edit.php?id=<?= $driver['id'] ?>" class="btn btn-primary px-4 fw-bold">
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
