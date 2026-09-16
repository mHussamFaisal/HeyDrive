<?php
$page_title = 'Pricing – Fixed Prices';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_fixed_prices (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  pickup_location VARCHAR(200) DEFAULT '',
  dropoff_location VARCHAR(200) DEFAULT '',
  vehicle_type VARCHAR(50) DEFAULT 'all',
  price DECIMAL(10,2) NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act = $_POST['action'] ?? '';
  if ($act==='save') {
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_fixed_prices SET name=?,pickup_location=?,dropoff_location=?,vehicle_type=?,price=?,is_active=? WHERE id=?")
          ->execute([$_POST['name'],$_POST['pickup_location'],$_POST['dropoff_location'],$_POST['vehicle_type'],floatval($_POST['price']),isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_fixed_prices (name,pickup_location,dropoff_location,vehicle_type,price,is_active) VALUES (?,?,?,?,?,?)")
          ->execute([$_POST['name'],$_POST['pickup_location'],$_POST['dropoff_location'],$_POST['vehicle_type'],floatval($_POST['price']),isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete') {
    $pdo->prepare("DELETE FROM td_fixed_prices WHERE id=?")->execute([intval($_POST['id'])]);
  }
  redirect(APP_URL.'/admin/pricing_fixed.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_fixed_prices ORDER BY id")->fetchAll();
$edit = !empty($_GET['edit']) ? $pdo->query("SELECT * FROM td_fixed_prices WHERE id=".intval($_GET['edit']))->fetch() : null;
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Fixed Price</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Name / Route Label</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required placeholder="e.g. City Centre → Airport"></div>
        <div class="mb-2"><label class="form-label">Pickup Location (keyword)</label><input type="text" name="pickup_location" value="<?=htmlspecialchars($edit['pickup_location']??'')?>" class="form-control" placeholder="e.g. Dublin Airport"></div>
        <div class="mb-2"><label class="form-label">Dropoff Location (keyword)</label><input type="text" name="dropoff_location" value="<?=htmlspecialchars($edit['dropoff_location']??'')?>" class="form-control" placeholder="e.g. City Centre"></div>
        <div class="mb-2"><label class="form-label">Vehicle Type</label><select name="vehicle_type" class="form-select">
          <option value="all" <?=($edit['vehicle_type']??'')==='all'?'selected':''?>>All</option>
          <option value="sedan" <?=($edit['vehicle_type']??'')==='sedan'?'selected':''?>>Sedan</option>
          <option value="suv" <?=($edit['vehicle_type']??'')==='suv'?'selected':''?>>SUV</option>
          <option value="van" <?=($edit['vehicle_type']??'')==='van'?'selected':''?>>Van</option>
        </select></div>
        <div class="mb-2"><label class="form-label">Fixed Price (€)</label><input type="number" step="0.01" min="0" name="price" value="<?=$edit['price']??''?>" class="form-control" required></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_fixed.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-8">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">📌 Fixed Prices List</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No fixed prices yet.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm" id="tbl"><thead class="table-light">
        <tr><th>Name</th><th>From</th><th>To</th><th>Vehicle</th><th>Price</th><th>Active</th><th></th></tr>
      </thead><tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['name'])?></td>
          <td><?=htmlspecialchars($r['pickup_location'])?:'-'?></td>
          <td><?=htmlspecialchars($r['dropoff_location'])?:'-'?></td>
          <td><?=$r['vehicle_type']?></td>
          <td>€<?=number_format($r['price'],2)?></td>
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
      </tbody></table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
</div>
<?php require_once 'footer.php'; ?>
