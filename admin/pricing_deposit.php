<?php
$page_title = 'Pricing – Deposit Payments';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_deposit_settings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  require_deposit TINYINT(1) DEFAULT 0,
  deposit_type ENUM('percentage','fixed') DEFAULT 'percentage',
  deposit_value DECIMAL(10,2) DEFAULT 20.00,
  apply_to_minimum TINYINT(1) DEFAULT 0,
  minimum_booking_amount DECIMAL(10,2) DEFAULT 0.00,
  refundable TINYINT(1) DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$row = $pdo->query("SELECT * FROM td_deposit_settings LIMIT 1")->fetch();
if (!$row) {
  $pdo->exec("INSERT INTO td_deposit_settings (require_deposit,deposit_type,deposit_value,apply_to_minimum,minimum_booking_amount,refundable) VALUES (0,'percentage',20.00,0,0.00,1)");
  $row = $pdo->query("SELECT * FROM td_deposit_settings LIMIT 1")->fetch();
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $p = $_POST;
  $pdo->prepare("UPDATE td_deposit_settings SET require_deposit=?,deposit_type=?,deposit_value=?,apply_to_minimum=?,minimum_booking_amount=?,refundable=? WHERE id=?")
      ->execute([isset($p['require_deposit'])?1:0,$p['deposit_type'],floatval($p['deposit_value']),isset($p['apply_to_minimum'])?1:0,floatval($p['minimum_booking_amount']),isset($p['refundable'])?1:0,$row['id']]);
  redirect(APP_URL.'/admin/pricing_deposit.php?msg=saved');
}
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Deposit settings saved!</div><?php endif; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-white">💳 Deposit Payments</div>
  <div class="card-body" style="max-width:600px">
    <form method="POST">
      <div class="mb-3 row"><label class="col-sm-5 col-form-label">Require Deposit</label>
        <div class="col-sm-7 d-flex align-items-center"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="require_deposit" <?=$row['require_deposit']?'checked':''?>></div></div></div>
      <div class="mb-3 row"><label class="col-sm-5 col-form-label">Deposit Type</label>
        <div class="col-sm-7"><select name="deposit_type" class="form-select">
          <option value="percentage" <?=$row['deposit_type']==='percentage'?'selected':''?>>Percentage (%)</option>
          <option value="fixed" <?=$row['deposit_type']==='fixed'?'selected':''?>>Fixed Amount (€)</option>
        </select></div></div>
      <div class="mb-3 row"><label class="col-sm-5 col-form-label">Deposit Value</label>
        <div class="col-sm-7"><input type="number" step="0.01" min="0" name="deposit_value" value="<?=$row['deposit_value']?>" class="form-control"><small class="text-muted">If percentage: 20 = 20% of total. If fixed: €20</small></div></div>
      <div class="mb-3 row"><label class="col-sm-5 col-form-label">Only for bookings above</label>
        <div class="col-sm-7 d-flex align-items-center gap-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="apply_to_minimum" <?=$row['apply_to_minimum']?'checked':''?>></div>
        <input type="number" step="0.01" min="0" name="minimum_booking_amount" value="<?=$row['minimum_booking_amount']?>" class="form-control" placeholder="Min amount €"></div></div>
      <div class="mb-3 row"><label class="col-sm-5 col-form-label">Deposit is Refundable</label>
        <div class="col-sm-7 d-flex align-items-center"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="refundable" <?=$row['refundable']?'checked':''?>></div></div></div>
      <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-2"></i>Save</button>
    </form>
  </div>
</div>
<?php require_once 'footer.php'; ?>
