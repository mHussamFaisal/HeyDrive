<?php
$page_title = 'Pricing – Driver Income';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_driver_income (
  id INT PRIMARY KEY AUTO_INCREMENT,
  vehicle_type VARCHAR(50) DEFAULT 'all',
  commission_type ENUM('percentage','fixed') DEFAULT 'percentage',
  commission_value DECIMAL(10,2) DEFAULT 80.00,
  minimum_income DECIMAL(10,2) DEFAULT 0.00,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$vehicles = ['all','sedan','suv','van'];
foreach ($vehicles as $vt) {
  $ex = $pdo->prepare("SELECT id FROM td_driver_income WHERE vehicle_type=?")->execute([$vt]);
  if (!$pdo->query("SELECT COUNT(*) FROM td_driver_income WHERE vehicle_type='$vt'")->fetchColumn()) {
    $pdo->prepare("INSERT INTO td_driver_income (vehicle_type,commission_type,commission_value,minimum_income) VALUES (?,?,?,?)")
        ->execute([$vt,'percentage',80.00,0.00]);
  }
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  foreach ($_POST['income'] as $id => $v) {
    $pdo->prepare("UPDATE td_driver_income SET commission_type=?,commission_value=?,minimum_income=? WHERE id=?")
        ->execute([$v['commission_type'],floatval($v['commission_value']),floatval($v['minimum_income']),intval($id)]);
  }
  redirect(APP_URL.'/admin/pricing_driver_income.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_driver_income ORDER BY id")->fetchAll();
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Driver income settings saved!</div><?php endif; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-white">👨‍💼 Driver Income / Commission</div>
  <div class="card-body">
    <form method="POST">
      <div class="table-responsive">
        <table class="table">
          <thead class="table-dark"><tr><th>Vehicle Type</th><th>Commission Type</th><th>Commission Value</th><th>Minimum Income (€)</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td><strong>🚗 <?=ucfirst($r['vehicle_type'])?></strong></td>
              <td><select name="income[<?=$r['id']?>][commission_type]" class="form-select form-select-sm" style="width:140px">
                <option value="percentage" <?=$r['commission_type']==='percentage'?'selected':''?>>Percentage (%)</option>
                <option value="fixed" <?=$r['commission_type']==='fixed'?'selected':''?>>Fixed (€)</option>
              </select></td>
              <td><input type="number" step="0.01" min="0" name="income[<?=$r['id']?>][commission_value]" value="<?=$r['commission_value']?>" class="form-control form-control-sm" style="width:100px">
                <small class="text-muted">If %: 80 = driver gets 80% of fare</small></td>
              <td><input type="number" step="0.01" min="0" name="income[<?=$r['id']?>][minimum_income]" value="<?=$r['minimum_income']?>" class="form-control form-control-sm" style="width:100px"></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-2"></i>Save</button>
    </form>
    <div class="mt-3 alert alert-info">Driver receives the commission % (or fixed amount) from each completed booking. Company keeps the remainder.</div>
  </div>
</div>
<?php require_once 'footer.php'; ?>
