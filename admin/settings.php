<?php
$page_title = 'Settings';
require_once 'header.php';
?>

<div class="row g-4">

  <!-- General -->
  <div class="col-md-4">
    <a href="settings_general.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-building"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">General</h6>
            <p class="text-muted small mb-0">Company details, address, contact info, URLs, feedback &amp; terms.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Booking -->
  <div class="col-md-4">
    <a href="settings_booking.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Booking</h6>
            <p class="text-muted small mb-0">Reference format, currency, pricing display, capacity &amp; meeting board.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Google -->
  <div class="col-md-4">
    <a href="settings_google.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fab fa-google"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Google</h6>
            <p class="text-muted small mb-0">Maps JavaScript, Embed, Directions, Places &amp; Geocoding API keys.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Localization -->
  <div class="col-md-4">
    <a href="settings_localization.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-language"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Localization</h6>
            <p class="text-muted small mb-0">Default language, language switcher, timezone, date &amp; time format.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Email -->
  <div class="col-md-4">
    <a href="settings_email.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-envelope"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Email</h6>
            <p class="text-muted small mb-0">SMTP host, port, security, credentials &amp; test email sender.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Roles & Permissions -->
  <div class="col-md-4">
    <a href="settings_roles.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-user-shield"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Roles &amp; Permissions</h6>
            <p class="text-muted small mb-0">Create and manage admin roles for your team members.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Types of Vehicles -->
  <div class="col-md-4">
    <a href="vehicle_types.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-car"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Types of Vehicles</h6>
            <p class="text-muted small mb-0">Manage fleet vehicle types, passenger &amp; luggage capacities, display options &amp; pricing factors.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Payment Gateways -->
  <div class="col-md-4">
    <a href="payments.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-credit-card"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Payment Gateways</h6>
            <p class="text-muted small mb-0">Configure Stripe, PayPal, cash &amp; other payment methods.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Web Widget & Integration -->
  <div class="col-md-4">
    <a href="settings_widget.php" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100 card-hover">
        <div class="card-body d-flex align-items-start gap-3 p-4">
          <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
            <i class="fas fa-code"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-1">Web Widget &amp; Integration</h6>
            <p class="text-muted small mb-0">Embeddable booking widget design, custom colors &amp; WordPress shortcode.</p>
          </div>
        </div>
      </div>
    </a>
  </div>

</div>

<style>
.card-hover { transition: transform .15s, box-shadow .15s; cursor: pointer; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12) !important; }
</style>

<?php include 'footer.php'; ?>
