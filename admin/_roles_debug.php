<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

require_once '../includes/config.php';
$pdo = db_connect();

// Ensure td_roles table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS td_roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    permissions TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$pdo->exec("CREATE TABLE IF NOT EXISTS td_users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100),
    email      VARCHAR(150),
    role_id    INT DEFAULT NULL,
    password   VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = ''; $msg_type = 'success';

// Add new role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' && !empty($_POST['role_name'])) {
        $name = trim($_POST['role_name']);
        try {
            $pdo->prepare("INSERT INTO td_roles (name) VALUES (?)")->execute([$name]);
            $msg = '✅ Role "'.htmlspecialchars($name).'" added.';
        } catch (Exception $e) {
            $msg = '❌ Role name already exists.'; $msg_type='danger';
        }
    } elseif ($_POST['action'] === 'delete' && !empty($_POST['role_id'])) {
        $rid = (int)$_POST['role_id'];
        $pdo->prepare("DELETE FROM td_roles WHERE id=?")->execute([$rid]);
        $msg = '✅ Role deleted.';
    }
}

$roles = $pdo->query("SELECT r.*, (SELECT COUNT(*) FROM td_users u WHERE u.role_id=r.id) AS user_count FROM td_roles r ORDER BY r.name")->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Settings › Roles & Permissions';
require_once 'header.php';
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Roles &amp; Permissions</li>
  </ol>
</nav>
<?php if($msg): ?><div class="alert alert-<?=$msg_type?> alert-dismissible fade show"><?=$msg?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="d-flex align-items-center gap-2 mb-4">
  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
    <i class="fas fa-plus me-1"></i>Add New
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Name</th>
          <th>Users</th>
          <th class="text-end pe-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($roles)): ?>
        <tr><td colspan="3" class="text-center py-4 text-muted">No roles yet. Click <strong>Add New</strong> to create one.</td></tr>
        <?php endif; ?>
        <?php foreach($roles as $role): ?>
        <tr>
          <td class="ps-3 fw-semibold"><?= htmlspecialchars($role['name']) ?></td>
          <td>
            <span class="badge bg-secondary"><?= $role['user_count'] ?> user<?= $role['user_count']!=1?'s':'' ?></span>
          </td>
          <td class="text-end pe-3">
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete role <?= htmlspecialchars(addslashes($role['name'])) ?>?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="add">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-shield me-2 text-warning"></i>Add New Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label fw-semibold">Role Name</label>
        <input type="text" name="role_name" class="form-control" placeholder="e.g. Dispatcher, Manager, Driver" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning"><i class="fas fa-plus me-1"></i>Add Role</button>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
