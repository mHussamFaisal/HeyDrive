<?php
$_pricing_sections = [
  'pricing.php'                   => ['fa-th-large',           'Overview'],
  'pricing_deposit.php'           => ['fa-money-bill-wave',    'Deposit Payments'],
  'pricing_distance_time.php'     => ['fa-route',              'Distance & Time'],
  'pricing_distance_modifier.php' => ['fa-sliders-h',          'Distance Modifier'],
  'pricing_driver_income.php'     => ['fa-user-tie',           'Driver Income'],
  'pricing_fixed.php'             => ['fa-thumbtack',          'Fixed Prices'],
  'pricing_holiday.php'           => ['fa-calendar-alt',       'Holiday / Rush Hours'],
  'pricing_item.php'              => ['fa-box-open',           'Item Surcharge'],
  'pricing_location.php'          => ['fa-map-marker-alt',     'Location Surcharge'],
  'pricing_night.php'             => ['fa-moon',               'Night Surcharge'],
  'pricing_discounts.php'         => ['fa-percent',            'Other Discounts'],
  'pricing_tax.php'               => ['fa-file-invoice-dollar','Tax'],
  'pricing_vouchers.php'          => ['fa-ticket-alt',         'Voucher Discounts'],
];
$_cur = basename($_SERVER['PHP_SELF']);
?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-2 px-3">
    <div class="d-flex flex-wrap gap-1 align-items-center">
      <span class="text-muted small fw-bold me-1"><i class="fas fa-tags"></i></span>
      <?php foreach ($_pricing_sections as $_f => $_s): ?>
      <a href="<?=$_f?>" class="btn btn-sm <?=$_cur===$_f?'btn-warning text-dark':'btn-outline-secondary'?> py-0 px-2" style="font-size:.78rem">
        <i class="fas <?=$_s[0]?> me-1"></i><?=$_s[1]?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
