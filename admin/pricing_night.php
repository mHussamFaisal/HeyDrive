<?php
$page_title = 'Pricing – Night Surcharge';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_night_surcharge (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) DEFAULT 'Night Surcharge',
  is_active TINYINT(1) DEFAULT 1,
  start_hour TINYINT DEFAULT 22,
  end_hour TINYINT DEFAULT 6,
  surcharge_type ENUM('multiply','add','percentage') DEFAULT 'multiply',
  surcharge_value DECIMAL(10,2) DEFAULT 1.25,
  applies_to VARCHAR(50) DEFAULT 'all',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$row = $pdo->query("SELECT * FROM td_night_surcharge LIMIT 1")->fetch();
if (!$row) {
  $pdo->exec("INSERT INTO td_night_surcharge (name,is_active,start_hour,end_hour,surcharge_type,surcharge_value,applies_to) VALUES ('Night Surcharge',1,22,6,'multiply',1.25,'all')");
  $row = $pdo->query("SELECT * FROM td_night_surcharge LIMIT 1")->fetch();
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $p = $_POST;
  $pdo->prepare("UPDATE td_night_surcharge SET name=?,is_active=?,start_hour=?,end_hour=?,surcharge_type=?,surcharge_value=?,applies_to=? WHERE id=?")
      ->execute([$p['name'],isset($p['is_active'])?1:0,intval($p['start_hour']),intval($p['end_hour']),$p['surcharge_type'],floatval($p['surcharge_value']),$p['applies_to'],$row['id']]);
  redirect(APP_URL.'/admin/pricing_night.php?msg=saved');
}
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Night surcharge saved!</div><?php endif; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-white">🌙 Night Surcharge Configuration</div>
  <div class="card-body" style="max-width:600px">
    <form method="POST">
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">Name</label>
        <div class="col-sm-8"><input type="text" name="name" value="<?=htmlspecialchars($row['name'])?>" class="form-control"></div></div>
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">Active</label>
        <div class="col-sm-8 d-flex align-items-center"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" <?=$row['is_active']?'checked':''?>></div></div></div>
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">Start Hour (22 = 10 PM)</label>
        <div class="col-sm-8"><input type="number" name="start_hour" min="0" max="23" value="<?=$row['start_hour']?>" class="form-control"></div></div>
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">End Hour (6 = 6 AM)</label>
        <div class="col-sm-8"><input type="number" name="end_hour" min="0" max="23" value="<?=$row['end_hour']?>" class="form-control"></div></div>
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">Surcharge Type</label>
        <div class="col-sm-8"><select name="surcharge_type" class="form-select">
          <option value="multiply" <?=$row['surcharge_type']==='multiply'?'selected':''?>>Multiply (×)</option>
          <option value="add" <?=$row['surcharge_type']==='add'?'selected':''?>>Add Fixed (€)</option>
          <option value="percentage" <?=$row['surcharge_type']==='percentage'?'selected':''?>>Percentage (%)</option>
        </select></div></div>
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">Surcharge Value</label>
        <div class="col-sm-8"><input type="number" step="0.01" name="surcharge_value" value="<?=$row['surcharge_value']?>" class="form-control"><small class="text-muted">Multiply: 1.25 = +25%, Add: 5.00 = €5 extra, Percentage: 25 = 25%</small></div></div>
      <div class="mb-3 row"><label class="col-sm-4 col-form-label">Applies To</label>
        <div class="col-sm-8"><select name="applies_to" class="form-select">
          <option value="all" <?=$row['applies_to']==='all'?'selected':''?>>All vehicles</option>
          <option value="sedan" <?=$row['applies_to']==='sedan'?'selected':''?>>Sedan only</option>
          <option value="suv" <?=$row['applies_to']==='suv'?'selected':''?>>SUV only</option>
          <option value="van" <?=$row['applies_to']==='van'?'selected':''?>>Van only</option>
        </select></div></div>
      <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-2"></i>Save</button>
    </form>
  </div>
</div>
<?php require_once 'footer.php'; ?>
