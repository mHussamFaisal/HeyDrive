<?php
$page_title = 'Pricing – Distance Modifier';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_distance_modifier (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  min_km DECIMAL(8,2) DEFAULT 0,
  max_km DECIMAL(8,2) DEFAULT 999,
  modifier_type ENUM('multiply','add','percentage') DEFAULT 'multiply',
  modifier_value DECIMAL(10,2) DEFAULT 1.00,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act = $_POST['action'] ?? '';
  if ($act==='save') {
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_distance_modifier SET name=?,min_km=?,max_km=?,modifier_type=?,modifier_value=?,is_active=? WHERE id=?")
          ->execute([$_POST['name'],floatval($_POST['min_km']),floatval($_POST['max_km']),$_POST['modifier_type'],floatval($_POST['modifier_value']),isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_distance_modifier (name,min_km,max_km,modifier_type,modifier_value,is_active) VALUES (?,?,?,?,?,?)")
          ->execute([$_POST['name'],floatval($_POST['min_km']),floatval($_POST['max_km']),$_POST['modifier_type'],floatval($_POST['modifier_value']),isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete' && !empty($_POST['id'])) {
    $pdo->prepare("DELETE FROM td_distance_modifier WHERE id=?")->execute([intval($_POST['id'])]);
  }
  redirect(APP_URL.'/admin/pricing_distance_modifier.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_distance_modifier ORDER BY min_km,id")->fetchAll();
$edit = null;
if (!empty($_GET['edit'])) $edit = $pdo->query("SELECT * FROM td_distance_modifier WHERE id=".intval($_GET['edit']))->fetch();
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Distance Modifier</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Name</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required placeholder="e.g. Long Distance Boost"></div>
        <div class="row g-2 mb-2">
          <div class="col"><label class="form-label">Min KM</label><input type="number" step="0.1" min="0" name="min_km" value="<?=$edit['min_km']??0?>" class="form-control"></div>
          <div class="col"><label class="form-label">Max KM</label><input type="number" step="0.1" min="0" name="max_km" value="<?=$edit['max_km']??999?>" class="form-control"></div>
        </div>
        <div class="mb-2"><label class="form-label">Modifier Type</label><select name="modifier_type" class="form-select">
          <option value="multiply" <?=($edit['modifier_type']??'')==='multiply'?'selected':''?>>Multiply (×)</option>
          <option value="add" <?=($edit['modifier_type']??'')==='add'?'selected':''?>>Add (€)</option>
          <option value="percentage" <?=($edit['modifier_type']??'')==='percentage'?'selected':''?>>Percentage (%)</option>
        </select></div>
        <div class="mb-2"><label class="form-label">Value</label><input type="number" step="0.01" name="modifier_value" value="<?=$edit['modifier_value']??1?>" class="form-control"></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_distance_modifier.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-8">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">🔧 Distance Modifiers</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No modifiers yet. Add one on the left.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm">
        <thead class="table-light"><tr><th>Name</th><th>KM Range</th><th>Type</th><th>Value</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=$r['min_km']?>–<?=$r['max_km']?> km</td>
            <td><?=$r['modifier_type']?></td>
            <td><?=$r['modifier_value']?></td>
            <td><?=$r['is_active']?'<span class="badge bg-success">Yes</span>':'<span class="badge bg-secondary">No</span>'?></td>
            <td>
              <a href="?edit=<?=$r['id']?>" class="btn btn-sm btn-outline-primary py-0">Edit</a>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>">
                <button class="btn btn-sm btn-outline-danger py-0">Del</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
</div>
<?php require_once 'footer.php'; ?>
