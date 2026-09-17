<?php
// All POST logic runs before HTML output to avoid blank page or headers issues
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();
$err = '';
$msg = $_GET['msg'] ?? '';

// Self-healing schema check for td_users and td_deletion_requests
try {
    $pdo->exec("ALTER TABLE `td_users` 
        MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'customer',
        ADD COLUMN IF NOT EXISTS `phone` VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `avatar` VARCHAR(255) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
        ADD COLUMN IF NOT EXISTS `deletion_requested` TINYINT(1) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS `deletion_reason` TEXT DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `deletion_requested_at` DATETIME DEFAULT NULL");
} catch (Exception $e) {}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `td_deletion_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `reason` TEXT DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'pending',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (`user_id`),
        INDEX (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {}

// Current active filter
$cur_role = trim($_GET['role'] ?? '');
$cur_tab  = trim($_GET['tab'] ?? '');

// ── POST Handlers ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_user') {
        $user_id          = intval($_POST['user_id'] ?? 0);
        $name             = trim($_POST['name'] ?? '');
        $email            = strtolower(trim($_POST['email'] ?? ''));
        $phone            = trim($_POST['phone'] ?? '');
        $role             = trim($_POST['role'] ?? 'customer');
        $status           = trim($_POST['status'] ?? 'active');
        $password_raw     = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!$name || !$email) {
            $err = "Name and Email are required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = "Invalid email address format.";
        } elseif ($user_id === 0 && empty($password_raw)) {
            $err = "Password is required for new users.";
        } elseif (!empty($password_raw) && $password_raw !== $confirm_password) {
            $err = "Passwords do not match.";
        } else {
            // Check for duplicate email
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
                // Handle Avatar upload
                $avatar_path = $_POST['current_avatar'] ?? '';
                if (!empty($_FILES['avatar']['name'])) {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $ftype   = mime_content_type($_FILES['avatar']['tmp_name']);
                    if (in_array($ftype, $allowed)) {
                        $ext        = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                        $filename   = 'user_' . time() . '_' . rand(100, 999) . '.' . strtolower($ext);
                        $upload_dir = dirname(__DIR__) . '/uploads/avatars/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $filename)) {
                            $avatar_path = 'uploads/avatars/' . $filename;
                        }
                    }
                }

                try {
                    $pdo->beginTransaction();
                    if ($user_id > 0) {
                        if (!empty($password_raw)) {
                            $hash = password_hash($password_raw, PASSWORD_DEFAULT);
                            $pdo->prepare("UPDATE td_users SET name=?, email=?, phone=?, role=?, status=?, avatar=?, password=? WHERE id=?")
                                ->execute([$name, $email, $phone, $role, $status, $avatar_path, $hash, $user_id]);
                        } else {
                            $pdo->prepare("UPDATE td_users SET name=?, email=?, phone=?, role=?, status=?, avatar=? WHERE id=?")
                                ->execute([$name, $email, $phone, $role, $status, $avatar_path, $user_id]);
                        }

                        // If role changed to driver, ensure td_drivers record exists
                        if ($role === 'driver') {
                            $dchk = $pdo->prepare("SELECT id FROM td_drivers WHERE user_id=?");
                            $dchk->execute([$user_id]);
                            if (!$dchk->fetch()) {
                                $pdo->prepare("INSERT INTO td_drivers (user_id, unique_id, photo, activity_status, status) VALUES (?, CONCAT('DRV-', 1000 + ?), ?, 'Available', 'active')")
                                    ->execute([$user_id, $user_id, $avatar_path]);
                            }
                        }
                    } else {
                        $hash = password_hash($password_raw, PASSWORD_DEFAULT);
                        $pdo->prepare("INSERT INTO td_users (name, email, phone, role, status, avatar, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())")
                            ->execute([$name, $email, $phone, $role, $status, $avatar_path, $hash]);
                        $new_uid = $pdo->lastInsertId();

                        if ($role === 'driver') {
                            $pdo->prepare("INSERT INTO td_drivers (user_id, unique_id, photo, activity_status, status) VALUES (?, CONCAT('DRV-', 1000 + ?), ?, 'Available', 'active')")
                                ->execute([$new_uid, $new_uid, $avatar_path]);
                        }
                    }
                    $pdo->commit();
                    header("Location: " . APP_URL . "/admin/users.php?msg=" . ($user_id > 0 ? 'updated' : 'added') . ($cur_role ? "&role=$cur_role" : ""));
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $err = "Database error: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    } elseif ($action === 'delete_user') {
        $uid = intval($_POST['user_id'] ?? 0);
        $my_id = $_SESSION['admin_user']['id'] ?? 0;
        if ($uid > 0 && $uid !== $my_id) {
            $pdo->prepare("DELETE FROM td_drivers WHERE user_id=?")->execute([$uid]);
            $pdo->prepare("DELETE FROM td_deletion_requests WHERE user_id=?")->execute([$uid]);
            $pdo->prepare("DELETE FROM td_users WHERE id=?")->execute([$uid]);
            header("Location: " . APP_URL . "/admin/users.php?msg=deleted" . ($cur_role ? "&role=$cur_role" : ""));
            exit;
        } elseif ($uid === $my_id) {
            $err = "You cannot delete your own logged-in admin account!";
        }
    } elseif ($action === 'toggle_status') {
        $uid = intval($_POST['user_id'] ?? 0);
        $st  = trim($_POST['status'] ?? 'active');
        $pdo->prepare("UPDATE td_users SET status=? WHERE id=?")->execute([$st, $uid]);
        header("Location: " . APP_URL . "/admin/users.php?msg=status_updated" . ($cur_role ? "&role=$cur_role" : ""));
        exit;
    } elseif ($action === 'approve_deletion') {
        $req_id  = intval($_POST['request_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? 0);
        $my_id   = $_SESSION['admin_user']['id'] ?? 0;
        if ($user_id > 0 && $user_id !== $my_id) {
            $pdo->prepare("DELETE FROM td_drivers WHERE user_id=?")->execute([$user_id]);
            $pdo->prepare("UPDATE td_deletion_requests SET status='approved' WHERE id=?")->execute([$req_id]);
            $pdo->prepare("DELETE FROM td_users WHERE id=?")->execute([$user_id]);
            header("Location: " . APP_URL . "/admin/users.php?tab=deletion_requests&msg=deletion_approved");
            exit;
        }
    } elseif ($action === 'reject_deletion') {
        $req_id  = intval($_POST['request_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? 0);
        $pdo->prepare("UPDATE td_deletion_requests SET status='rejected' WHERE id=?")->execute([$req_id]);
        $pdo->prepare("UPDATE td_users SET deletion_requested=0, deletion_reason=NULL WHERE id=?")->execute([$user_id]);
        header("Location: " . APP_URL . "/admin/users.php?tab=deletion_requests&msg=deletion_rejected");
        exit;
    }
}

// ── GET: Fetch Counts & Data ──────────────────────────────────────────────────
$counts = [
    'all'             => 0,
    'customer'        => 0,
    'driver'          => 0,
    'fleet_operator'  => 0,
    'admin'           => 0,
    'deletion'        => 0
];

try {
    $counts['all'] = (int)$pdo->query("SELECT COUNT(*) FROM td_users")->fetchColumn();
    $counts['customer'] = (int)$pdo->query("SELECT COUNT(*) FROM td_users WHERE role='customer'")->fetchColumn();
    $counts['driver'] = (int)$pdo->query("SELECT COUNT(*) FROM td_users WHERE role='driver'")->fetchColumn();
    $counts['fleet_operator'] = (int)$pdo->query("SELECT COUNT(*) FROM td_users WHERE role='fleet_operator'")->fetchColumn();
    $counts['admin'] = (int)$pdo->query("SELECT COUNT(*) FROM td_users WHERE role='admin'")->fetchColumn();
    $counts['deletion'] = (int)$pdo->query("SELECT COUNT(*) FROM td_deletion_requests WHERE status='pending'")->fetchColumn();
} catch (Exception $e) {}

// Fetch List
$users = [];
$deletion_requests = [];

if ($cur_tab === 'deletion_requests') {
    try {
        $deletion_requests = $pdo->query("SELECT r.*, u.name, u.email, u.role, u.phone 
            FROM td_deletion_requests r 
            LEFT JOIN td_users u ON r.user_id = u.id 
            ORDER BY r.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
} else {
    $where = [];
    $params = [];

    if ($cur_role) {
        $where[] = "u.role = ?";
        $params[] = $cur_role;
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    try {
        $stmt = $pdo->prepare("SELECT 
            u.*,
            (SELECT COUNT(*) FROM td_bookings b WHERE LOWER(b.customer_email) = LOWER(u.email) OR b.driver_id = u.id) as bookings_count
            FROM td_users u
            $where_sql
            ORDER BY u.id DESC");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $err = "Database query error: " . htmlspecialchars($e->getMessage());
    }
}

$page_title = 'Users';
require_once 'header.php';
?>

<!-- Breadcrumb / Header Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="users.php" class="text-decoration-none">Users</a></li>
        <li class="breadcrumb-item active" aria-current="page">
          <?= $cur_tab === 'deletion_requests' ? 'Deletion Requests' : ($cur_role ? ucfirst(str_replace('_', ' ', $cur_role)) . 's' : 'All Users') ?>
        </li>
      </ol>
    </nav>
    <h4 class="fw-bold mb-0 text-dark">
      <i class="fas fa-users text-warning me-2"></i>Users Management
    </h4>
  </div>

  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="btn-group btn-group-sm">
      <a href="users.php<?= $cur_role ? '?role='.$cur_role : ($cur_tab ? '?tab='.$cur_tab : '') ?>" class="btn btn-outline-secondary btn-sm" title="Refresh">
        <i class="fas fa-redo"></i>
      </a>
      <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="exportUsersCSV()">
        <i class="fas fa-download me-1"></i> Export
      </button>
    </div>
    <button type="button" class="btn btn-success btn-sm px-3 shadow-sm d-flex align-items-center gap-1 fw-semibold" onclick="openUserModal()" style="border-radius:.4rem; height:32px;">
      <i class="fas fa-user-plus"></i> Add User
    </button>
    <div class="position-relative">
      <input type="text" id="userSearch" class="form-control form-control-sm ps-3 pe-4" placeholder="Search users..." style="width:190px; border-radius:.4rem;" onkeyup="filterUsersTable()">
      <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted small"></i>
    </div>
  </div>
</div>

<!-- Success / Error Alerts -->
<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm">
  <?= $msg==='added' ? '✅ User created successfully!' : ($msg==='updated' ? '✅ User details updated!' : ($msg==='deleted' ? '🗑️ User account deleted.' : ($msg==='deletion_approved' ? '✅ User deletion request approved and account removed.' : ($msg==='deletion_rejected' ? 'ℹ️ Deletion request rejected.' : '✅ Updated.')))) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger mb-4 shadow-sm">❌ <?= $err ?></div><?php endif; ?>

<!-- Role & Filter Navigation Tabs matching Image 2 submenu items -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap border-bottom pb-2">
  <a href="users.php" class="btn btn-sm <?= (empty($cur_role) && empty($cur_tab)) ? 'btn-dark fw-bold' : 'btn-outline-secondary' ?> rounded-pill px-3">
    <i class="fas fa-users me-1"></i> All Users <span class="badge bg-secondary ms-1"><?= $counts['all'] ?></span>
  </a>
  <a href="users.php?role=customer" class="btn btn-sm <?= ($cur_role === 'customer') ? 'btn-primary fw-bold' : 'btn-outline-secondary' ?> rounded-pill px-3">
    <i class="fas fa-user me-1"></i> Customers <span class="badge bg-secondary ms-1"><?= $counts['customer'] ?></span>
  </a>
  <a href="users.php?role=driver" class="btn btn-sm <?= ($cur_role === 'driver') ? 'btn-warning fw-bold text-dark' : 'btn-outline-secondary' ?> rounded-pill px-3">
    <i class="fas fa-id-badge me-1"></i> Drivers <span class="badge bg-secondary ms-1"><?= $counts['driver'] ?></span>
  </a>
  <a href="users.php?role=fleet_operator" class="btn btn-sm <?= ($cur_role === 'fleet_operator') ? 'btn-info fw-bold text-white' : 'btn-outline-secondary' ?> rounded-pill px-3">
    <i class="fas fa-building me-1"></i> Fleet Operators <span class="badge bg-secondary ms-1"><?= $counts['fleet_operator'] ?></span>
  </a>
  <a href="users.php?role=admin" class="btn btn-sm <?= ($cur_role === 'admin') ? 'btn-danger fw-bold' : 'btn-outline-secondary' ?> rounded-pill px-3">
    <i class="fas fa-user-shield me-1"></i> Admins <span class="badge bg-secondary ms-1"><?= $counts['admin'] ?></span>
  </a>
  <a href="users.php?tab=deletion_requests" class="btn btn-sm <?= ($cur_tab === 'deletion_requests') ? 'btn-danger fw-bold' : 'btn-outline-secondary' ?> rounded-pill px-3 ms-auto">
    <i class="fas fa-user-times me-1"></i> Deletion Requests 
    <?php if ($counts['deletion'] > 0): ?>
      <span class="badge bg-danger ms-1"><?= $counts['deletion'] ?></span>
    <?php else: ?>
      <span class="badge bg-secondary ms-1">0</span>
    <?php endif; ?>
  </a>
</div>

<?php if ($cur_role === 'driver'): ?>
<div class="alert alert-info py-2 px-3 d-flex align-items-center justify-content-between mb-3 shadow-sm">
  <div><i class="fas fa-info-circle me-1"></i> Looking for full driver licenses, vehicles, and live activity tracking?</div>
  <a href="drivers.php" class="btn btn-sm btn-primary fw-bold">Open Full Drivers Panel <i class="fas fa-arrow-right ms-1"></i></a>
</div>
<?php endif; ?>

<!-- ── TAB: Deletion Requests ── -->
<?php if ($cur_tab === 'deletion_requests'): ?>
<div class="card border-0 shadow-sm rounded-3">
  <div class="card-header bg-white py-3">
    <h5 class="fw-bold mb-0 text-danger"><i class="fas fa-user-times me-2"></i>Account Deletion Requests</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
      <table class="table table-hover align-middle mb-0" id="usersTable" style="min-width: 1000px; white-space: nowrap;">
        <thead class="table-light small text-muted text-uppercase">
          <tr>
            <th class="ps-3" style="width: 140px;">Actions</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Reason</th>
            <th>Requested At</th>
            <th class="pe-3">Status</th>
          </tr>
        </thead>
        <tbody class="small" id="usersTableBody">
          <?php if (empty($deletion_requests)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <i class="fas fa-check-circle fa-2x text-success mb-2"></i><br>
              No pending deletion requests found.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($deletion_requests as $dr): ?>
          <tr>
            <td class="ps-3">
              <?php if ($dr['status'] === 'pending'): ?>
              <div class="btn-group btn-group-sm">
                <form method="POST" onsubmit="return confirm('Permanently delete this user account?');" style="display:inline;">
                  <input type="hidden" name="action" value="approve_deletion">
                  <input type="hidden" name="request_id" value="<?= $dr['id'] ?>">
                  <input type="hidden" name="user_id" value="<?= $dr['user_id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" title="Approve & Delete Account">
                    <i class="fas fa-trash me-1"></i> Approve
                  </button>
                </form>
                <form method="POST" onsubmit="return confirm('Reject this deletion request?');" style="display:inline;">
                  <input type="hidden" name="action" value="reject_deletion">
                  <input type="hidden" name="request_id" value="<?= $dr['id'] ?>">
                  <input type="hidden" name="user_id" value="<?= $dr['user_id'] ?>">
                  <button type="submit" class="btn btn-outline-secondary btn-sm" title="Reject Request">
                    <i class="fas fa-times me-1"></i> Reject
                  </button>
                </form>
              </div>
              <?php else: ?>
                <span class="text-muted fst-italic">Completed</span>
              <?php endif; ?>
            </td>
            <td class="fw-bold"><?= htmlspecialchars($dr['name'] ?? 'Unknown') ?></td>
            <td><?= htmlspecialchars($dr['email'] ?? '-') ?></td>
            <td><span class="badge bg-light text-dark border text-capitalize"><?= htmlspecialchars($dr['role'] ?? 'user') ?></span></td>
            <td><?= htmlspecialchars($dr['reason'] ?: 'None specified') ?></td>
            <td><?= htmlspecialchars($dr['created_at']) ?></td>
            <td class="pe-3">
              <?php if ($dr['status'] === 'pending'): ?>
                <span class="badge bg-warning text-dark px-2 py-1">Pending</span>
              <?php elseif ($dr['status'] === 'approved'): ?>
                <span class="badge bg-danger px-2 py-1">Approved</span>
              <?php else: ?>
                <span class="badge bg-secondary px-2 py-1"><?= ucfirst($dr['status']) ?></span>
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

<?php else: ?>

<!-- ── TAB: Users List Table (Horizontal Scroll) ── -->
<div class="card border-0 shadow-sm rounded-3">
  <div class="card-body p-0">
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
      <table class="table table-hover align-middle mb-0" id="usersTable" style="min-width: 1250px; white-space: nowrap;">
        <thead class="table-light small text-muted text-uppercase">
          <tr>
            <th class="ps-3" style="width: 120px; min-width: 120px;">Actions</th>
            <th style="width: 70px; min-width: 70px;">Photo</th>
            <th style="min-width: 180px;">Name <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 220px;">Email <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 160px;">Mobile number <i class="fas fa-sort text-muted small"></i></th>
            <th style="min-width: 140px;">Role</th>
            <th style="min-width: 120px;">Status</th>
            <th style="min-width: 110px;">Bookings</th>
            <th class="pe-3" style="min-width: 150px;">Registered Date</th>
          </tr>
        </thead>
        <tbody class="small" id="usersTableBody">
          <?php if (empty($users)): ?>
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="fas fa-users fa-2x mb-2 text-secondary"></i><br>
              No users found matching this filter. Click <strong>+ Add User</strong> to create one!
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($users as $u): ?>
          <tr>
            <!-- Actions -->
            <td class="ps-3">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light border px-2 text-primary" title="Edit User" onclick='editUserModal(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                  <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-light border dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                  <span class="visually-hidden">Toggle</span>
                </button>
                <ul class="dropdown-menu shadow border-0">
                  <li>
                    <button type="button" class="dropdown-item" onclick='editUserModal(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                      <i class="fas fa-pen text-primary me-2"></i> Edit Details
                    </button>
                  </li>
                  <?php if ($u['role'] === 'driver'): ?>
                  <li>
                    <a class="dropdown-item" href="drivers.php">
                      <i class="fas fa-id-badge text-warning me-2"></i> Driver Profile
                    </a>
                  </li>
                  <?php endif; ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-trash-alt me-2"></i> Delete
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </td>

            <!-- Photo -->
            <td>
              <?php 
              $avatar_url = '';
              if (!empty($u['avatar']) && file_exists(dirname(__DIR__) . '/' . $u['avatar'])) {
                  $avatar_url = APP_URL . '/' . htmlspecialchars($u['avatar']);
              }
              ?>
              <?php if ($avatar_url): ?>
                <img src="<?= $avatar_url ?>" alt="Avatar" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover;">
              <?php else: ?>
                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-secondary" style="width:40px;height:40px;font-size:16px;">
                  <i class="fas fa-user"></i>
                </div>
              <?php endif; ?>
            </td>

            <!-- Name -->
            <td>
              <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($u['name']) ?></span>
              <?php if ($u['role'] === 'admin'): ?>
                <span class="badge bg-danger ms-1" style="font-size:10px;"><i class="fas fa-crown"></i> Admin</span>
              <?php endif; ?>
            </td>

            <!-- Email -->
            <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>

            <!-- Mobile number -->
            <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>

            <!-- Role Badge -->
            <td>
              <?php 
              $role_badge = 'bg-secondary';
              $r = strtolower($u['role']);
              if ($r === 'admin') $role_badge = 'bg-danger';
              elseif ($r === 'driver') $role_badge = 'bg-warning text-dark';
              elseif ($r === 'fleet_operator') $role_badge = 'bg-info text-white';
              elseif ($r === 'customer') $role_badge = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
              ?>
              <span class="badge <?= $role_badge ?> px-2 py-1 text-capitalize fw-semibold">
                <?= htmlspecialchars(str_replace('_', ' ', $u['role'])) ?>
              </span>
            </td>

            <!-- Status Badge -->
            <td>
              <?php 
              $st = strtolower($u['status']);
              if ($st === 'active') {
                  echo '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fw-semibold">Active</span>';
              } elseif ($st === 'suspended') {
                  echo '<span class="badge bg-danger px-2 py-1 fw-semibold">Suspended</span>';
              } else {
                  echo '<span class="badge bg-secondary px-2 py-1 fw-semibold">Inactive</span>';
              }
              ?>
            </td>

            <!-- Bookings Count -->
            <td>
              <span class="badge bg-light text-dark border px-2 py-1">
                <i class="fas fa-calendar-check text-muted me-1"></i><?= (int)($u['bookings_count'] ?? 0) ?>
              </span>
            </td>

            <!-- Registered Date -->
            <td class="pe-3 text-muted">
              <?= htmlspecialchars($u['created_at'] ? date('M d, Y H:i', strtotime($u['created_at'])) : '-') ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Add / Edit User Modal Dialog ── -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" enctype="multipart/form-data" id="userForm">
        <input type="hidden" name="action" value="save_user">
        <input type="hidden" name="user_id" id="usr_user_id" value="0">
        <input type="hidden" name="current_avatar" id="usr_current_avatar" value="">

        <div class="modal-header border-bottom py-3">
          <h5 class="modal-title fw-bold" id="userModalLabel"><i class="fas fa-user-plus text-warning me-2"></i>Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label text-muted small fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="usr_name" class="form-control" placeholder="e.g. John Doe" required>
            </div>

            <div class="col-12">
              <label class="form-label text-muted small fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="usr_email" class="form-control" placeholder="user@example.com" required>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role" id="usr_role" class="form-select" required>
                <option value="customer" selected>Customer</option>
                <option value="driver">Driver</option>
                <option value="fleet_operator">Fleet Operator</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small fw-semibold">Status <span class="text-danger">*</span></label>
              <select name="status" id="usr_status" class="form-select" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label text-muted small fw-semibold">Mobile Number</label>
              <input type="text" name="phone" id="usr_phone" class="form-control" placeholder="+49 175 ...">
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small fw-semibold" id="usr_pass_label">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" id="usr_password" class="form-control" placeholder="Password">
              <div class="form-text small text-muted" id="usr_pass_help" style="display:none;">Leave blank to keep current password.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small fw-semibold">Confirm Password</label>
              <input type="password" name="confirm_password" id="usr_confirm_password" class="form-control" placeholder="Confirm password">
            </div>

            <div class="col-12">
              <label class="form-label text-muted small fw-semibold">Avatar / Photo</label>
              <input type="file" name="avatar" id="usr_avatar" class="form-control" accept="image/*">
            </div>
          </div>
        </div>

        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-5 fw-bold" id="usr_submit_btn">Save User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openUserModal() {
  document.getElementById('userForm').reset();
  document.getElementById('usr_user_id').value = '0';
  document.getElementById('usr_current_avatar').value = '';
  document.getElementById('userModalLabel').innerHTML = '<i class="fas fa-user-plus text-warning me-2"></i>Add User';
  document.getElementById('usr_submit_btn').innerText = 'Save User';
  document.getElementById('usr_pass_label').innerHTML = 'Password <span class="text-danger">*</span>';
  document.getElementById('usr_pass_help').style.display = 'none';
  document.getElementById('usr_password').required = true;

  var el = document.getElementById('userModal');
  var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  modal.show();
}

function editUserModal(data) {
  document.getElementById('userForm').reset();
  document.getElementById('usr_user_id').value = data.id || 0;
  document.getElementById('usr_current_avatar').value = data.avatar || '';
  document.getElementById('usr_name').value = data.name || '';
  document.getElementById('usr_email').value = data.email || '';
  document.getElementById('usr_phone').value = data.phone || '';
  document.getElementById('usr_role').value = data.role || 'customer';
  document.getElementById('usr_status').value = data.status || 'active';

  document.getElementById('userModalLabel').innerHTML = '<i class="fas fa-edit text-primary me-2"></i>Edit User: ' + (data.name || '');
  document.getElementById('usr_submit_btn').innerText = 'Save Changes';
  document.getElementById('usr_pass_label').innerText = 'Password (Optional)';
  document.getElementById('usr_pass_help').style.display = 'block';
  document.getElementById('usr_password').required = false;

  var el = document.getElementById('userModal');
  var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  modal.show();
}

function filterUsersTable() {
  var input = document.getElementById('userSearch');
  var filter = input.value.toLowerCase();
  var rows = document.getElementById('usersTableBody').getElementsByTagName('tr');
  for (var i = 0; i < rows.length; i++) {
    var text = rows[i].textContent || rows[i].innerText;
    rows[i].style.display = (text.toLowerCase().indexOf(filter) > -1) ? '' : 'none';
  }
}

function exportUsersCSV() {
  var rows = document.querySelectorAll("#usersTable tr");
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
  downloadLink.download = "users_list.csv";
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
}
</script>

<?php require_once 'footer.php'; ?>
