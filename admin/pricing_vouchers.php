<?php
$page_title = 'Pricing – Voucher Discounts';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_vouchers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
  discount_value DECIMAL(10,2) NOT NULL,
  min_booking_amount DECIMAL(10,2) DEFAULT 0,
  max_uses INT DEFAULT 0,
  used_count INT DEFAULT 0,
  expires_at DATE NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act=$_POST['action']??'';
  if ($act==='save') {
    $exp = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_vouchers SET code=?,name=?,discount_type=?,discount_value=?,min_booking_amount=?,max_uses=?,expires_at=?,is_active=? WHERE id=?")
          ->execute([strtoupper(trim($_POST['code'])),$_POST['name'],$_POST['discount_type'],floatval($_POST['discount_value']),floatval($_POST['min_booking_amount']),intval($_POST['max_uses']),$exp,isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_vouchers (code,name,discount_type,discount_value,min_booking_amount,max_uses,expires_at,is_active) VALUES (?,?,?,?,?,?,?,?)")
          ->execute([strtoupper(trim($_POST['code'])),$_POST['name'],$_POST['discount_type'],floatval($_POST['discount_value']),floatval($_POST['min_booking_amount']),intval($_POST['max_uses']),$exp,isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete') {
    $pdo->prepare("DELETE FROM td_vouchers WHERE id=?")->execute([intval($_POST['id'])]);
  } elseif ($act==='generate') {
    $code = strtoupper(substr(bin2hex(random_bytes(4)),0,8));
    echo json_encode(['code'=>$code]); exit;
  }
  redirect(APP_URL.'/admin/pricing_vouchers.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_vouchers ORDER BY id DESC")->fetchAll();
$edit = !empty($_GET['edit']) ? $pdo->query("SELECT * FROM td_vouchers WHERE id=".intval($_GET['edit']))->fetch() : null;
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Voucher</div>
    <div class="card-body">
      <form method="POST" id="vform">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Voucher Code</label>
          <div class="input-group">
            <input type="text" name="code" id="vcode" value="<?=htmlspecialchars($edit['code']??'')?>" class="form-control text-uppercase" required placeholder="e.g. SAVE20">
            <button type="button" class="btn btn-outline-secondary" onclick="genCode()"><i class="fas fa-sync-alt"></i></button>
          </div></div>
        <div class="mb-2"><label class="form-label">Description / Name</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Discount Type</label><select name="discount_type" class="form-select">
          <option value="percentage" <?=($edit['discount_type']??'')==='percentage'?'selected':''?>>Percentage (%)</option>
          <option value="fixed" <?=($edit['discount_type']??'')==='fixed'?'selected':''?>>Fixed Amount (€)</option>
        </select></div>
        <div class="mb-2"><label class="form-label">Discount Value</label><input type="number" step="0.01" min="0" name="discount_value" value="<?=$edit['discount_value']??''?>" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Min Booking Amount (€)</label><input type="number" step="0.01" min="0" name="min_booking_amount" value="<?=$edit['min_booking_amount']??0?>" class="form-control"></div>
        <div class="mb-2"><label class="form-label">Max Uses (0 = unlimited)</label><input type="number" min="0" name="max_uses" value="<?=$edit['max_uses']??0?>" class="form-control"></div>
        <div class="mb-2"><label class="form-label">Expires At (optional)</label><input type="date" name="expires_at" value="<?=$edit['expires_at']??''?>" class="form-control"></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_vouchers.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-8">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">🎟️ Voucher Codes</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No vouchers yet.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm" id="vtbl">
        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Value</th><th>Used</th><th>Expires</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <?php $exp=$r['expires_at']&&$r['expires_at']<date('Y-m-d'); ?>
          <tr class="<?=$exp?'table-danger':''?>">
            <td><code><?=htmlspecialchars($r['code'])?></code></td>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=$r['discount_type']==='percentage'?$r['discount_value'].'%':'€'.$r['discount_value']?></td>
            <td><?=$r['used_count']?><?=$r['max_uses']>0?'/'.$r['max_uses']:'/∞'?></td>
            <td><?=$r['expires_at']?($exp?'<span class="text-danger">'.$r['expires_at'].' (expired)</span>':$r['expires_at']):'-'?></td>
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
<script>
function genCode() {
  var c=''; var chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  for(var i=0;i<8;i++) c+=chars.charAt(Math.floor(Math.random()*chars.length));
  document.getElementById('vcode').value=c;
}
</script>
<?php require_once 'footer.php'; ?>
