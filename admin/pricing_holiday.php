<?php
$page_title = 'Pricing – Holiday / Rush Hours';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS td_rush_surcharge (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  type ENUM('holiday','rush') DEFAULT 'rush',
  start_time TIME DEFAULT '07:00:00',
  end_time TIME DEFAULT '09:00:00',
  days_of_week VARCHAR(20) DEFAULT '1,2,3,4,5',
  start_date DATE NULL,
  end_date DATE NULL,
  surcharge_type ENUM('multiply','add','percentage') DEFAULT 'multiply',
  surcharge_value DECIMAL(10,2) DEFAULT 1.50,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $act = $_POST['action'] ?? '';
  if ($act==='save') {
    $sd = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $ed = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    if (!empty($_POST['id'])) {
      $pdo->prepare("UPDATE td_rush_surcharge SET name=?,type=?,start_time=?,end_time=?,days_of_week=?,start_date=?,end_date=?,surcharge_type=?,surcharge_value=?,is_active=? WHERE id=?")
          ->execute([$_POST['name'],$_POST['type'],$_POST['start_time'],$_POST['end_time'],$_POST['days_of_week'],$sd,$ed,$_POST['surcharge_type'],floatval($_POST['surcharge_value']),isset($_POST['is_active'])?1:0,intval($_POST['id'])]);
    } else {
      $pdo->prepare("INSERT INTO td_rush_surcharge (name,type,start_time,end_time,days_of_week,start_date,end_date,surcharge_type,surcharge_value,is_active) VALUES (?,?,?,?,?,?,?,?,?,?)")
          ->execute([$_POST['name'],$_POST['type'],$_POST['start_time'],$_POST['end_time'],$_POST['days_of_week'],$sd,$ed,$_POST['surcharge_type'],floatval($_POST['surcharge_value']),isset($_POST['is_active'])?1:0]);
    }
  } elseif ($act==='delete') {
    $pdo->prepare("DELETE FROM td_rush_surcharge WHERE id=?")->execute([intval($_POST['id'])]);
  }
  redirect(APP_URL.'/admin/pricing_holiday.php?msg=saved');
}
$rows = $pdo->query("SELECT * FROM td_rush_surcharge ORDER BY type,id")->fetchAll();
$edit = !empty($_GET['edit']) ? $pdo->query("SELECT * FROM td_rush_surcharge WHERE id=".intval($_GET['edit']))->fetch() : null;
$days_map = ['1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat','7'=>'Sun'];
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Saved!</div><?php endif; ?>
<div class="row g-4">
<div class="col-lg-5">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white"><?=$edit?'✏️ Edit':'➕ Add'?> Surcharge Rule</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?=$edit['id']??''?>">
        <div class="mb-2"><label class="form-label">Name</label><input type="text" name="name" value="<?=htmlspecialchars($edit['name']??'')?>" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Type</label><select name="type" class="form-select">
          <option value="rush" <?=($edit['type']??'')==='rush'?'selected':''?>>Rush Hour</option>
          <option value="holiday" <?=($edit['type']??'')==='holiday'?'selected':''?>>Holiday</option>
        </select></div>
        <div class="row g-2 mb-2">
          <div class="col"><label class="form-label">Start Time</label><input type="time" name="start_time" value="<?=$edit['start_time']??'07:00'?>" class="form-control"></div>
          <div class="col"><label class="form-label">End Time</label><input type="time" name="end_time" value="<?=$edit['end_time']??'09:00'?>" class="form-control"></div>
        </div>
        <div class="mb-2"><label class="form-label">Days of Week (1=Mon…7=Sun, comma-sep)</label><input type="text" name="days_of_week" value="<?=htmlspecialchars($edit['days_of_week']??'1,2,3,4,5')?>" class="form-control" placeholder="e.g. 1,2,3,4,5"></div>
        <div class="row g-2 mb-2">
          <div class="col"><label class="form-label">Start Date (optional)</label><input type="date" name="start_date" value="<?=$edit['start_date']??''?>" class="form-control"></div>
          <div class="col"><label class="form-label">End Date (optional)</label><input type="date" name="end_date" value="<?=$edit['end_date']??''?>" class="form-control"></div>
        </div>
        <div class="mb-2"><label class="form-label">Surcharge Type</label><select name="surcharge_type" class="form-select">
          <option value="multiply" <?=($edit['surcharge_type']??'')==='multiply'?'selected':''?>>Multiply (×)</option>
          <option value="add" <?=($edit['surcharge_type']??'')==='add'?'selected':''?>>Add (€)</option>
          <option value="percentage" <?=($edit['surcharge_type']??'')==='percentage'?'selected':''?>>Percentage (%)</option>
        </select></div>
        <div class="mb-2"><label class="form-label">Value</label><input type="number" step="0.01" min="0" name="surcharge_value" value="<?=$edit['surcharge_value']??1.5?>" class="form-control"></div>
        <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" name="is_active" <?=($edit['is_active']??1)?'checked':''?>><label class="form-check-label">Active</label></div>
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Save</button>
        <?php if ($edit): ?><a href="pricing_holiday.php" class="btn btn-secondary w-100 mt-1">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<div class="col-lg-7">
  <div class="card border-0 shadow-sm">
    <div class="card-header fw-bold bg-white">🎉 Holiday / Rush Hours Rules</div>
    <div class="card-body">
      <?php if (empty($rows)): ?><p class="text-muted">No rules yet.</p>
      <?php else: ?>
      <div class="table-responsive"><table class="table table-hover table-sm">
        <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Time</th><th>Days</th><th>Surcharge</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><span class="badge bg-<?=$r['type']==='holiday'?'warning text-dark':'info text-dark'?>"><?=ucfirst($r['type'])?></span></td>
            <td><?=substr($r['start_time'],0,5)?>–<?=substr($r['end_time'],0,5)?></td>
            <td class="small"><?php $ds=explode(',',$r['days_of_week']); echo implode(',',array_map(fn($d)=>$days_map[$d]??$d,$ds)); ?></td>
            <td><?=$r['surcharge_type']==='multiply'?'×':($r['surcharge_type']==='add'?'+€':'+')?><?=$r['surcharge_value']?></td>
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
