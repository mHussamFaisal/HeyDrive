<?php
$page_title = 'Pricing – Tax';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_tax (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  rate DECIMAL(8,4) NOT NULL,
  is_inclusive TINYINT(1) DEFAULT 0,
  applies_to VARCHAR(50) DEFAULT 'all',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act=$_POST['action']??'';
  if ($act==='save') {
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_tax SET name=?,rate=?,is_inclusive=?,applies_to=?,is_active=? WHERE id=?")
          ->execute([$_POST['name'],floatval($_POST['rate']),isset($_POST['is_inclusive'])?1:0,$_POST['applies_to'],isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_tax (name,rate,is_inclusive,applies_to,is_active) VALUES (?,?,?,?,?)")
          ->execute([$_POST['name'],floatval($_POST['rate']),isset($_POST['is_inclusive'])?1:0,$_POST['applies_to'],isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete') {
    $pdo->prepare("DELETE FROM td_tax WHERE id=?")->execute([intval($_POST['id'])]);
  }
  redirect(APP_URL.'/admin/pricing_tax.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_tax ORDER BY id")->fetchAll();
$edit = !empty($_GET['edit']) ? $pdo->query("SELECT * FROM td_tax WHERE id=".intval($_GET['edit']))->fetch() : null;
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Tax</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Tax Name</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required placeholder="e.g. VAT 23%"></div>
        <div class="mb-2"><label class="form-label">Rate (%)</label><input type="number" step="0.0001" min="0" max="100" name="rate" value="<?=$edit['rate']??''?>" class="form-control" required placeholder="e.g. 23"></div>
        <div class="mb-2 form-check"><input type="checkbox" class="form-check-input" name="is_inclusive" <?=($edit['is_inclusive']??0)?'checked':''?>><label class="form-check-label">Inclusive (tax already included in price)</label></div>
        <div class="mb-2"><label class="form-label">Applies To</label><select name="applies_to" class="form-select">
          <option value="all" <?=($edit['applies_to']??'')==='all'?'selected':''?>>All bookings</option>
          <option value="non-fixed" <?=($edit['applies_to']??'')==='non-fixed'?'selected':''?>>Non-fixed price only</option>
          <option value="fixed" <?=($edit['applies_to']??'')==='fixed'?'selected':''?>>Fixed price only</option>
        </select></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_tax.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-8">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">💼 Tax Rates</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No tax rules yet.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm">
        <thead class="table-light"><tr><th>Name</th><th>Rate</th><th>Inclusive</th><th>Applies To</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=$r['rate']?>%</td>
            <td><?=$r['is_inclusive']?'<span class="badge bg-info text-dark">Yes</span>':'<span class="badge bg-secondary">No</span>'?></td>
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
