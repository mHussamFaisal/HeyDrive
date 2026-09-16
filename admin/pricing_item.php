<?php
$page_title = 'Pricing – Item Surcharge';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_item_surcharge (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  description VARCHAR(255) DEFAULT '',
  amount DECIMAL(10,2) NOT NULL,
  is_percentage TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act=$_POST['action']??'';
  if ($act==='save') {
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_item_surcharge SET name=?,description=?,amount=?,is_percentage=?,is_active=? WHERE id=?")
          ->execute([$_POST['name'],$_POST['description'],floatval($_POST['amount']),isset($_POST['is_percentage'])?1:0,isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_item_surcharge (name,description,amount,is_percentage,is_active) VALUES (?,?,?,?,?)")
          ->execute([$_POST['name'],$_POST['description'],floatval($_POST['amount']),isset($_POST['is_percentage'])?1:0,isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete') {
    $pdo->prepare("DELETE FROM td_item_surcharge WHERE id=?")->execute([intval($_POST['id'])]);
  }
  redirect(APP_URL.'/admin/pricing_item.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_item_surcharge ORDER BY id")->fetchAll();
$edit = !empty($_GET['edit']) ? $pdo->query("SELECT * FROM td_item_surcharge WHERE id=".intval($_GET['edit']))->fetch() : null;
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Item Surcharge</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Item Name</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required placeholder="e.g. Extra Luggage"></div>
        <div class="mb-2"><label class="form-label">Description</label><input type="text" name="description" value="<?=htmlspecialchars($edit['description']??'')?>" class="form-control" placeholder="Optional note"></div>
        <div class="mb-2"><label class="form-label">Amount</label><input type="number" step="0.01" min="0" name="amount" value="<?=$edit['amount']??''?>" class="form-control" required></div>
        <div class="mb-2 form-check"><input type="checkbox" class="form-check-input" name="is_percentage" id="isp" <?=($edit['is_percentage']??0)?'checked':''?>><label class="form-check-label" for="isp">Percentage (%) instead of fixed (€)</label></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_item.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-8">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">📦 Item Surcharges</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No item surcharges yet.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm">
        <thead class="table-light"><tr><th>Name</th><th>Description</th><th>Amount</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td class="text-muted small"><?=htmlspecialchars($r['description'])?></td>
            <td><?=$r['is_percentage']?$r['amount'].'%':'€'.$r['amount']?></td>
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
