<?php
require_once '../includes/config.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $page_title ?? 'Admin' ?> - TaxisDispatch</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
html, body { overflow-x: hidden; max-width: 100vw; }
.sidebar { min-height: 100vh; background:#1a1a2e; flex-shrink: 0; }
.sidebar .nav-link { color: rgba(255,255,255,.7); padding:.6rem 1rem; border-radius:.4rem; margin:.15rem 0; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { color:#fff; background:rgba(255,193,7,.2); }
.sidebar .nav-link i { width:22px; }
.main-content { background:#f0f2f5; min-height:100vh; min-width: 0; width: 100%; overflow-x: hidden; }
.stat-card { border-left: 4px solid; }
.stat-card.bookings { border-color:#ffc107; }
.stat-card.drivers  { border-color:#0dcaf0; }
.stat-card.today    { border-color:#198754; }
.stat-card.pending  { border-color:#dc3545; }
.sidebar .sub-nav .nav-link { padding:.35rem .6rem; font-size:.8rem; color:rgba(255,255,255,.55); }
.sidebar .sub-nav .nav-link:hover,
.sidebar .sub-nav .nav-link.active { color:#ffc107; background:rgba(255,193,7,.1); }
</style>
</head>
<body>
<div class="d-flex">
<!-- Sidebar -->
<div class="sidebar col-md-2 p-3">
  <div class="text-white fw-bold mb-4 d-flex align-items-center">
    <span class="fs-4 me-2">🚖</span>
    <div>
      <div>TaxisDispatch</div>
      <div class="small text-muted fw-normal">Admin Panel</div>
    </div>
  </div>
  <?php
  $cur         = basename($_SERVER['PHP_SELF']);
  $on_pricing  = strpos($cur, 'pricing') !== false;
  $on_bookings = in_array($cur, ['bookings.php','booking_edit.php','booking_new.php']);
  $settings_pages = [
      'settings.php','settings_general.php','settings_booking.php',
      'settings_google.php','settings_localization.php','settings_email.php',
      'settings_roles.php','settings_widget.php','payments.php'
  ];
  $on_settings = in_array($cur, $settings_pages);
  // Detect current booking tab from URL
  $cur_tab = $_GET['tab'] ?? 'latest';
  ?>
  <nav class="nav flex-column">

    <a href="index.php" class="nav-link <?= $cur==='index.php'?'active':'' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <!-- Bookings collapsible -->
    <a href="bookings.php"
       class="nav-link d-flex justify-content-between align-items-center <?= $on_bookings?'active':'' ?>"
       data-bs-toggle="collapse" data-bs-target="#bookingsMenu"
       aria-expanded="<?= $on_bookings?'true':'false' ?>">
      <span><i class="fas fa-calendar-check"></i> Bookings</span>
      <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse <?= $on_bookings?'show':'' ?>" id="bookingsMenu">
      <nav class="nav flex-column sub-nav ms-2 border-start border-secondary ps-1">
        <a href="bookings.php?tab=new"
           class="nav-link fw-semibold d-flex align-items-center gap-1 <?= ($on_bookings && $cur_tab==='new')?'active':'' ?>"
           style="color:#ffc107;margin-bottom:.35rem;">
          <i class="fas fa-plus-circle"></i> Add New Booking
        </a>
        <a href="bookings.php?tab=next24"      class="nav-link <?= ($on_bookings && $cur_tab==='next24')?'active':'' ?>">
          <i class="fas fa-clock"></i> Next 24 Hours
        </a>
        <a href="bookings.php?tab=latest"      class="nav-link <?= ($on_bookings && $cur_tab==='latest')?'active':'' ?>">
          <i class="fas fa-bolt"></i> Latest
        </a>
        <a href="bookings.php?tab=unconfirmed" class="nav-link <?= ($on_bookings && $cur_tab==='unconfirmed')?'active':'' ?>">
          <i class="fas fa-hourglass-half"></i> Unconfirmed
        </a>
        <a href="bookings.php?tab=completed"   class="nav-link <?= ($on_bookings && $cur_tab==='completed')?'active':'' ?>">
          <i class="fas fa-check-circle"></i> Completed
        </a>
        <a href="bookings.php?tab=cancelled"   class="nav-link <?= ($on_bookings && $cur_tab==='cancelled')?'active':'' ?>">
          <i class="fas fa-times-circle"></i> Cancelled
        </a>
        <a href="bookings.php?tab=all"         class="nav-link <?= ($on_bookings && $cur_tab==='all')?'active':'' ?>">
          <i class="fas fa-list"></i> All Bookings
        </a>
        <a href="bookings.php?tab=calendar"    class="nav-link <?= ($on_bookings && $cur_tab==='calendar')?'active':'' ?>">
          <i class="fas fa-calendar-alt"></i> Calendar
        </a>
        <a href="bookings.php?tab=passengers"  class="nav-link <?= ($on_bookings && $cur_tab==='passengers')?'active':'' ?>">
          <i class="fas fa-users"></i> Passengers
        </a>
        <a href="bookings.php?tab=trash"       class="nav-link <?= ($on_bookings && $cur_tab==='trash')?'active':'' ?>">
          <i class="fas fa-trash"></i> Trash
        </a>
      </nav>
    </div>

    <a href="dispatch.php" class="nav-link <?= $cur==='dispatch.php'?'active':'' ?>">
      <i class="fas fa-broadcast-tower"></i> Dispatch
    </a>

    <a href="drivers.php" class="nav-link <?= $cur==='drivers.php'?'active':'' ?>">
      <i class="fas fa-id-badge"></i> Drivers
    </a>

    <a href="vehicles.php" class="nav-link <?= $cur==='vehicles.php'?'active':'' ?>">
      <i class="fas fa-car"></i> Vehicles
    </a>

    <!-- Pricing collapsible -->
    <a href="pricing.php"
       class="nav-link d-flex justify-content-between align-items-center <?= $on_pricing?'active':'' ?>"
       data-bs-toggle="collapse" data-bs-target="#pricingMenu"
       aria-expanded="<?= $on_pricing?'true':'false' ?>">
      <span><i class="fas fa-tags"></i> Pricing</span>
      <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse <?= $on_pricing?'show':'' ?>" id="pricingMenu">
      <nav class="nav flex-column sub-nav ms-2 border-start border-secondary ps-1">
        <?php
        $psubs = [
          'pricing.php'                   => 'Overview',
          'pricing_deposit.php'           => 'Deposit Payments',
          'pricing_distance_time.php'     => 'Distance & Time',
          'pricing_distance_modifier.php' => 'Distance Modifier',
          'pricing_driver_income.php'     => 'Driver Income',
          'pricing_fixed.php'             => 'Fixed Prices',
          'pricing_holiday.php'           => 'Holiday / Rush Hours',
          'pricing_item.php'              => 'Item Surcharge',
          'pricing_location.php'          => 'Location Surcharge',
          'pricing_night.php'             => 'Night Surcharge',
          'pricing_discounts.php'         => 'Other Discounts',
          'pricing_tax.php'               => 'Tax',
          'pricing_vouchers.php'          => 'Voucher Discounts',
        ];
        foreach ($psubs as $f => $l):
        ?>
        <a href="<?=$f?>" class="nav-link <?=$cur===$f?'active':''?>"><?=$l?></a>
        <?php endforeach; ?>
      </nav>
    </div>

    <a href="reports.php" class="nav-link <?= $cur==='reports.php'?'active':'' ?>">
      <i class="fas fa-chart-bar"></i> Reports
    </a>

    <!-- Settings collapsible -->
    <a href="settings.php"
       class="nav-link d-flex justify-content-between align-items-center <?= $on_settings?'active':'' ?>"
       data-bs-toggle="collapse" data-bs-target="#settingsMenu"
       aria-expanded="<?= $on_settings?'true':'false' ?>">
      <span><i class="fas fa-cog"></i> Settings</span>
      <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse <?= $on_settings?'show':'' ?>" id="settingsMenu">
      <nav class="nav flex-column sub-nav ms-2 border-start border-secondary ps-1">
        <a href="settings_general.php"      class="nav-link <?= $cur==='settings_general.php'?'active':'' ?>">
          <i class="fas fa-building"></i> General
        </a>
        <a href="settings_booking.php"      class="nav-link <?= $cur==='settings_booking.php'?'active':'' ?>">
          <i class="fas fa-calendar-check"></i> Booking
        </a>
        <a href="settings_google.php"       class="nav-link <?= $cur==='settings_google.php'?'active':'' ?>">
          <i class="fab fa-google"></i> Google
        </a>
        <a href="settings_localization.php" class="nav-link <?= $cur==='settings_localization.php'?'active':'' ?>">
          <i class="fas fa-language"></i> Localization
        </a>
        <a href="settings_email.php"        class="nav-link <?= $cur==='settings_email.php'?'active':'' ?>">
          <i class="fas fa-envelope"></i> Email
        </a>
        <a href="settings_roles.php"        class="nav-link <?= $cur==='settings_roles.php'?'active':'' ?>">
          <i class="fas fa-user-shield"></i> Roles &amp; Permissions
        </a>
        <a href="settings_widget.php"       class="nav-link <?= $cur==='settings_widget.php'?'active':'' ?>">
          <i class="fas fa-code"></i> Web Widget &amp; Integration
        </a>
        <a href="payments.php"              class="nav-link <?= $cur==='payments.php'?'active':'' ?>">
          <i class="fas fa-credit-card"></i> Payment Gateways
        </a>
      </nav>
    </div>

    <hr class="border-secondary my-2">
    <a href="../"        class="nav-link"><i class="fas fa-globe"></i> View Site</a>
    <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>

  </nav>
</div>
<!-- Main Content -->
<div class="main-content flex-fill">
  <div class="bg-white shadow-sm px-4 py-2 d-flex justify-content-between align-items-center sticky-top">
    <h5 class="mb-0 fw-bold"><?= $page_title ?? 'Dashboard' ?></h5>
    <div class="d-flex align-items-center gap-3">
      <span class="badge bg-success">● Live</span>
      <div class="dropdown">
        <a href="#" class="dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
          <i class="fas fa-user-circle me-1"></i><?= $_SESSION['user_name'] ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="p-4">

