<?php
$page_title = 'Pricing';
require_once 'header.php';
require_once 'pricing_nav.php';
$pdo = db_connect();
$vehicles = $pdo->query("SELECT * FROM td_pricing ORDER BY id")->fetchAll();
?>
<div class="row g-3 mb-4">
<?php
$sections = [
  ['pricing_deposit.php',           'fa-money-bill-wave','warning', 'Deposit Payments',           'Configure deposit requirements for bookings'],
  ['pricing_distance_time.php',     'fa-route',         'info',    'Distance & Time',             'Base fares, per-km and per-minute rates'],
  ['pricing_distance_modifier.php', 'fa-sliders-h',     'primary', 'Distance Modifier',           'Adjust pricing based on distance ranges'],
  ['pricing_driver_income.php',     'fa-user-tie',      'success', 'Driver Income',               'Commission and income settings per vehicle type'],
  ['pricing_fixed.php',             'fa-thumbtack',     'danger',  'Fixed Prices',                'Set fixed prices for specific routes'],
  ['pricing_holiday.php',           'fa-calendar-alt',  'warning', 'Holiday / Rush Hours',        'Surcharges for holidays and peak hours'],
  ['pricing_item.php',              'fa-box-open',      'secondary','Item Surcharge',             'Additional charges for luggage, seats etc.'],
  ['pricing_location.php',          'fa-map-marker-alt','info',    'Location Surcharge',          'Extra fees for specific pickup/dropoff areas'],
  ['pricing_night.php',             'fa-moon',          'dark',    'Night Surcharge',             'Night-time pricing multipliers'],
  ['pricing_discounts.php',         'fa-percent',       'success', 'Other Discounts',             'General discount rules and promotions'],
  ['pricing_tax.php',               'fa-file-invoice-dollar','danger','Tax',                      'Tax rates and configuration'],
  ['pricing_vouchers.php',          'fa-ticket-alt',    'primary', 'Voucher Discounts',           'Promo codes and voucher management'],
];
foreach ($sections as [$url,$icon,$color,$title,$desc]):
?>
<div class="col-md-4 col-lg-3">
  <a href="<?=$url?>" class="text-decoration-none">
    <div class="card border-0 shadow-sm h-100 hover-shadow">
      <div class="card-body text-center py-4">
        <div class="mb-2"><span class="badge bg-<?=$color?> rounded-circle p-3"><i class="fas <?=$icon?> fa-lg"></i></span></div>
        <h6 class="fw-bold mb-1"><?=$title?></h6>
        <p class="text-muted small mb-0"><?=$desc?></p>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-white">💰 Current Base Fares (Quick View)</div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Vehicle</th><th>Base Fare</th><th>Per KM</th><th>Per Min</th><th>Min Fare</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($vehicles as $v): ?>
          <tr>
            <td><strong>🚗 <?=ucfirst($v['vehicle_type'])?></strong></td>
            <td>€<?=number_format($v['base_fare'],2)?></td>
            <td>€<?=number_format($v['per_km'],2)?></td>
            <td>€<?=number_format($v['per_minute'],2)?></td>
            <td>€<?=number_format($v['minimum_fare'],2)?></td>
            <td><a href="pricing_distance_time.php" class="btn btn-sm btn-outline-warning py-0">Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<style>.hover-shadow:hover{transform:translateY(-2px);box-shadow:0 .5rem 1rem rgba(0,0,0,.15)!important;transition:all .2s;}</style>
<?php require_once 'footer.php'; ?>
