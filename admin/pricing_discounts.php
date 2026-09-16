<?php
$page_title = 'Pricing – Other Discounts';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_discounts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
  discount_value DECIMAL(10,2) NOT NULL,
  min_booking_amount DECIMAL(10,2) DEFAULT 0,
  applies_to VARCHAR(50) DEFAULT 'all',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act=$_POST['action']??'';
  if ($act==='save') {
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_discounts SET name=?,discount_type=?,discount_value=?,min_booking_amount=?,applies_to=?,is_active=? WHERE id=?")
          ->execute([$_POST['name'],$_POST['discount_type'],floatval($_POST['discount_value']),floatval($_POST['min_booking_amount']),$_POST['applies_to'],isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_discounts (name,discount_type,discount_value,min_booking_amount,applies_to,is_active) VALUES (?,?,?,?,?,?)")
          ->execute([$_POST['name'],$_POST['discount_type'],floatval($_POST['discount_value']),floatval($_POST['min_booking_amount']),$_POST['applies_to'],isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete') {
    $pdo->prepare("DELETE FROM td_discounts WHERE id=?")->execute([intval($_POST['id'])]);
  }
  redirect(APP_URL.'/admin/pricing_discounts.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_discounts ORDER BY id")->fetchAll();
$edit = !empty($_GET['edit']) ? $pdo->query("SELECT * FROM td_discounts WHERE id=".intval($_GET['edit']))->fetch() : null;
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Discount</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Name</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required placeholder="e.g. Senior Discount"></div>
        <div class="mb-2"><label class="form-label">Discount Type</label><select name="discount_type" class="form-select">
          <option value="percentage" <?=($edit['discount_type']??'')==='percentage'?'selected':''?>>Percentage (%)</option>
          <option value="fixed" <?=($edit['discount_type']??'')==='fixed'?'selected':''?>>Fixed Amount (€)</option>
        </select></div>
        <div class="mb-2"><label class="form-label">Discount Value</label><input type="number" step="0.01" min="0" name="discount_value" value="<?=$edit['discount_value']??''?>" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Min Booking Amount (€)</label><input type="number" step="0.01" min="0" name="min_booking_amount" value="<?=$edit['min_booking_amount']??0?>" class="form-control"></div>
        <div class="mb-2"><label class="form-label">Applies To</label><select name="applies_to" class="form-select">
          <option value="all" <?=($edit['applies_to']??'')==='all'?'selected':''?>>All vehicles</option>
          <option value="sedan" <?=($edit['applies_to']??'')==='sedan'?'selected':''?>>Sedan only</option>
          <option value="suv" <?=($edit['applies_to']??'')==='suv'?'selected':''?>>SUV only</option>
          <option value="van" <?=($edit['applies_to']??'')==='van'?'selected':''?>>Van only</option>
        </select></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_discounts.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-8">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">🏷️ Other Discounts</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No discounts yet.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm">
        <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Value</th><th>Min Booking</th><th>Applies To</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=ucfirst($r['discount_type'])?></td>
            <td><?=$r['discount_type']==='percentage'?$r['discount_value'].'%':'€'.$r['discount_value']?></td>
            <td><?=$r['min_booking_amount']>0?'€'.$r['min_booking_amount']:'-'?></td>
            <td><?=ucfirst($r['applies_to'])?></td>
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
