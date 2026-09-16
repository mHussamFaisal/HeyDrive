<?php
$page_title = 'Pricing – Distance & Time';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  foreach ($_POST['pricing'] as $id => $p) {
    $pdo->prepare("UPDATE td_pricing SET base_fare=?,per_km=?,per_minute=?,minimum_fare=?,night_surcharge=?,airport_surcharge=? WHERE id=?")
        ->execute([floatval($p['base_fare']),floatval($p['per_km']),floatval($p['per_minute']),floatval($p['minimum_fare']),floatval($p['night_surcharge']),floatval($p['airport_surcharge']),$id]);
  }
  redirect(APP_URL.'/admin/pricing_distance_time.php?msg=saved');
}
$pricing = $pdo->query("SELECT * FROM td_pricing ORDER BY id")->fetchAll();
?>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Pricing updated!</div><?php endif; ?>
<div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-white">📏 Distance & Time Pricing</div>
  <div class="card-body">
    <form method="POST">
      <div class="table-responsive">
        <table class="table">
          <thead class="table-dark">
            <tr><th>Vehicle</th><th>Base Fare (€)</th><th>Per KM (€)</th><th>Per Minute (€)</th><th>Min Fare (€)</th><th>Night Surcharge (×)</th><th>Airport (€ extra)</th></tr>
          </thead>
          <tbody>
            <?php foreach ($pricing as $p): ?>
            <tr>
              <td><strong>🚗 <?=ucfirst($p['vehicle_type'])?></strong></td>
              <?php foreach (['base_fare','per_km','per_minute','minimum_fare','night_surcharge','airport_surcharge'] as $f): ?>
              <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="pricing[<?=$p['id']?>][<?=$f?>]" value="<?=$p[$f]?>" style="width:90px"></td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" class="btn btn-warning px-4"><i class="fas fa-save me-2"></i>Save</button>
    </form>
    <div class="mt-3 alert alert-info">
      <strong>Formula:</strong> Final price = Base Fare + (Distance × Per KM) + (Duration × Per Minute). Night surcharge is a multiplier. Airport surcharge is added for airport rides.
    </div>
  </div>
</div>
<?php require_once 'footer.php'; ?>
