<?php
require_once '../includes/config.php';
$pdo = db_connect();

$pdo->exec("CREATE TABLE IF NOT EXISTS td_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'booking_ref_format','currency_symbol','currency_code',
        'fixed_price_priority','round_price',
        'remove_decimal_zeros','show_price_breakdown',
        'auto_delete_incomplete','auto_delete_hours',
        'min_booking_hours','enable_cancellation','min_cancel_hours',
        'meeting_board_enable','meeting_board_font_size',
        'meeting_board_header','meeting_board_content','meeting_board_footer',
        'auto_refresh','auto_refresh_seconds','time_counter'
    ];
    foreach ($keys as $k) {
        $v = $_POST[$k] ?? '';
        $pdo->prepare("INSERT INTO td_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")
            ->execute([$k,$v,$v]);
    }
    $msg = '✅ Booking settings saved.';
}

$s = [];
foreach ($pdo->query("SELECT setting_key,setting_value FROM td_settings") as $row)
    $s[$row['setting_key']] = $row['setting_value'];
function sv($s,$k,$d=''){return htmlspecialchars($s[$k]??$d);}
function sc($s,$k){return !empty($s[$k]);}

$page_title = 'Settings › Booking';
require_once 'header.php';
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Booking</li>
  </ol>
</nav>
<?php if($msg): ?><div class="alert alert-success alert-dismissible fade show"><?=$msg?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<form method="POST">

<!-- Reference & Currency -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-bookmark me-2 text-warning"></i>Reference &amp; Currency</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Booking Reference Format
          <i class="fas fa-info-circle text-muted ms-1" title="Use {rand6} for a 6-digit random number, {id} for booking ID"></i>
        </label>
        <input type="text" name="booking_ref_format" class="form-control" value="<?= sv($s,'booking_ref_format','TD-{rand6}') ?>" placeholder="TD-{rand6}">
        <div class="form-text">Variables: <code>{rand6}</code> = 6-digit random, <code>{id}</code> = booking ID, <code>{date}</code> = today</div>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Currency Symbol</label>
        <input type="text" name="currency_symbol" class="form-control" value="<?= sv($s,'currency_symbol','€') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Currency Code</label>
        <input type="text" name="currency_code" class="form-control" value="<?= sv($s,'currency_code','EUR') ?>" placeholder="EUR">
      </div>
    </div>
  </div>
</div>

<!-- Pricing Display -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-tags me-2 text-warning"></i>Pricing Display</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Fixed Prices Priority</label>
        <select name="fixed_price_priority" class="form-select">
          <option value="zones"   <?= sv($s,'fixed_price_priority','zones')==='zones'?'selected':'' ?>>Zones</option>
          <option value="routes"  <?= sv($s,'fixed_price_priority')  ==='routes'?'selected':'' ?>>Routes</option>
          <option value="highest" <?= sv($s,'fixed_price_priority')  ==='highest'?'selected':'' ?>>Highest price</option>
          <option value="lowest"  <?= sv($s,'fixed_price_priority')  ==='lowest'?'selected':'' ?>>Lowest price</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Round Price</label>
        <select name="round_price" class="form-select">
          <option value="none"    <?= sv($s,'round_price','none')==='none'?'selected':'' ?>>No rounding</option>
          <option value="half"    <?= sv($s,'round_price')==='half'?'selected':'' ?>>Up to nearest integer or half (…0 or …5)</option>
          <option value="integer" <?= sv($s,'round_price')==='integer'?'selected':'' ?>>Up to nearest integer</option>
        </select>
      </div>
      <div class="col-md-6">
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="remove_decimal_zeros" value="1" <?= sc($s,'remove_decimal_zeros')?'checked':'' ?> id="rdz">
          <label class="form-check-label" for="rdz">Remove decimal zeros from prices</label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="show_price_breakdown" value="1" <?= sc($s,'show_price_breakdown')?'checked':'' ?> id="spb">
          <label class="form-check-label" for="spb">Enable booking price breakdown</label>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Booking Rules -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-clock me-2 text-warning"></i>Booking Rules</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="auto_delete_incomplete" value="1" <?= sc($s,'auto_delete_incomplete')?'checked':'' ?> id="adi">
          <label class="form-check-label fw-semibold" for="adi">Auto delete incomplete bookings after</label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group input-group-sm">
          <input type="number" name="auto_delete_hours" class="form-control" value="<?= sv($s,'auto_delete_hours','72') ?>" min="1">
          <span class="input-group-text">h</span>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Min Booking Time</label>
        <div class="input-group input-group-sm">
          <input type="number" name="min_booking_hours" class="form-control" value="<?= sv($s,'min_booking_hours','2') ?>" min="0">
          <span class="input-group-text">h</span>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="enable_cancellation" value="1" <?= sc($s,'enable_cancellation')?'checked':'' ?> id="ec">
          <label class="form-check-label fw-semibold" for="ec">Enable booking cancellation</label>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Min cancellation time</label>
        <div class="input-group input-group-sm">
          <input type="number" name="min_cancel_hours" class="form-control" value="<?= sv($s,'min_cancel_hours','24') ?>" min="0">
          <span class="input-group-text">h</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Admin Booking Listing -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-list me-2 text-warning"></i>Admin Booking Listing</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Auto Refresh</label>
        <select name="auto_refresh" class="form-select">
          <option value="active"   <?= sv($s,'auto_refresh','active')==='active'?'selected':'' ?>>Active</option>
          <option value="inactive" <?= sv($s,'auto_refresh')==='inactive'?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Refresh Time (seconds)</label>
        <input type="number" name="auto_refresh_seconds" class="form-control" value="<?= sv($s,'auto_refresh_seconds','60') ?>" min="10">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Time Counter</label>
        <select name="time_counter" class="form-select">
          <option value="hide" <?= sv($s,'time_counter','hide')==='hide'?'selected':'' ?>>Hide</option>
          <option value="show" <?= sv($s,'time_counter')==='show'?'selected':'' ?>>Show</option>
        </select>
      </div>
    </div>
  </div>
</div>

<!-- Meeting Board -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-tv me-2 text-warning"></i>Meeting Board</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="meeting_board_enable" value="1" <?= sc($s,'meeting_board_enable')?'checked':'' ?> id="mb_en">
          <label class="form-check-label fw-semibold" for="mb_en">Enable Meeting Board</label>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Name Font Size (px)</label>
        <input type="number" name="meeting_board_font_size" class="form-control" value="<?= sv($s,'meeting_board_font_size','80') ?>" min="20" max="200">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Show in Header</label>
        <select name="meeting_board_header" class="form-select">
          <option value="company_logo"    <?= sv($s,'meeting_board_header','company_logo')==='company_logo'?'selected':'' ?>>Company logo</option>
          <option value="company_name"    <?= sv($s,'meeting_board_header')==='company_name'?'selected':'' ?>>Company name</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Show in Content</label>
        <select name="meeting_board_content" class="form-select">
          <option value="passenger_name"  <?= sv($s,'meeting_board_content','passenger_name')==='passenger_name'?'selected':'' ?>>Passenger name</option>
          <option value="booking_ref"     <?= sv($s,'meeting_board_content')==='booking_ref'?'selected':'' ?>>Booking reference</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Show in Footer</label>
        <select name="meeting_board_footer" class="form-select">
          <option value="journey_details" <?= sv($s,'meeting_board_footer','journey_details')==='journey_details'?'selected':'' ?>>Journey details</option>
          <option value="none"            <?= sv($s,'meeting_board_footer')==='none'?'selected':'' ?>>None</option>
        </select>
      </div>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-warning px-5"><i class="fas fa-save me-2"></i>Save Booking Settings</button>
</form>
<?php include 'footer.php'; ?>
