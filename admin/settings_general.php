<?php
require_once '../includes/config.php';
$pdo = db_connect();

// Ensure td_settings table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS td_settings (
    setting_key   VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = ''; $msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'company_name','company_address','company_reg','company_email',
        'company_phone','home_url','booking_url','contact_url',
        'feedback_type','feedback_url','terms_enable','terms_type','terms_url'
    ];
    foreach ($keys as $k) {
        $v = $_POST[$k] ?? '';
        $pdo->prepare("INSERT INTO td_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")
            ->execute([$k, $v, $v]);
    }
    $msg = '✅ General settings saved successfully.';
}

$s = [];
foreach ($pdo->query("SELECT setting_key, setting_value FROM td_settings") as $row)
    $s[$row['setting_key']] = $row['setting_value'];

function sv($s, $k, $d='') { return htmlspecialchars($s[$k] ?? $d); }

$page_title = 'Settings › General';
require_once 'header.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">General</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
    <?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST">

<!-- Company Details -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-building me-2 text-warning"></i>Company Details</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Company Name</label>
        <input type="text" name="company_name" class="form-control" value="<?= sv($s,'company_name') ?>" placeholder="TaxisDispatch GmbH">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Registration Number</label>
        <input type="text" name="company_reg" class="form-control" value="<?= sv($s,'company_reg') ?>" placeholder="HRB 123456">
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Address</label>
        <textarea name="company_address" class="form-control" rows="2" placeholder="Street, City, Country"><?= sv($s,'company_address') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="company_email" class="form-control" value="<?= sv($s,'company_email') ?>" placeholder="info@taxisdispatch.com">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Phone Number</label>
        <input type="text" name="company_phone" class="form-control" value="<?= sv($s,'company_phone') ?>" placeholder="+49 170 000 0000">
      </div>
    </div>
  </div>
</div>

<!-- URLs -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-link me-2 text-warning"></i>URLs</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Home URL</label>
        <input type="url" name="home_url" class="form-control" value="<?= sv($s,'home_url','https://taxisdispatch.com') ?>" placeholder="https://taxisdispatch.com">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Booking URL</label>
        <input type="url" name="booking_url" class="form-control" value="<?= sv($s,'booking_url','https://taxisdispatch.com/book') ?>" placeholder="https://taxisdispatch.com/book">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Contact URL</label>
        <input type="url" name="contact_url" class="form-control" value="<?= sv($s,'contact_url') ?>" placeholder="https://taxisdispatch.com/contact">
      </div>
    </div>
  </div>
</div>

<!-- Feedback -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-star me-2 text-warning"></i>Feedback</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold">Feedback Type</label>
        <div class="d-flex gap-4">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="feedback_type" value="internal" id="fb_int" <?= (($s['feedback_type']??'internal')==='internal')?'checked':'' ?>>
            <label class="form-check-label" for="fb_int">Internal</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="feedback_type" value="external" id="fb_ext" <?= (($s['feedback_type']??'')==='external')?'checked':'' ?>>
            <label class="form-check-label" for="fb_ext">External</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="feedback_type" value="none" id="fb_none" <?= (($s['feedback_type']??'')==='none')?'checked':'' ?>>
            <label class="form-check-label" for="fb_none">None</label>
          </div>
        </div>
      </div>
      <div class="col-md-8" id="fb_url_wrap">
        <label class="form-label fw-semibold">Feedback URL <small class="text-muted">(for External)</small></label>
        <input type="url" name="feedback_url" class="form-control" value="<?= sv($s,'feedback_url') ?>" placeholder="https://g.page/r/...">
      </div>
    </div>
  </div>
</div>

<!-- Terms & Conditions -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-file-contract me-2 text-warning"></i>Terms &amp; Conditions</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="terms_enable" value="1" id="terms_en" <?= !empty($s['terms_enable'])?'checked':'' ?>>
          <label class="form-check-label fw-semibold" for="terms_en">Enable Terms &amp; Conditions</label>
        </div>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Type</label>
        <div class="d-flex gap-4">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="terms_type" value="external" id="t_ext" <?= (($s['terms_type']??'external')==='external')?'checked':'' ?>>
            <label class="form-check-label" for="t_ext">External URL</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="terms_type" value="internal" id="t_int" <?= (($s['terms_type']??'')==='internal')?'checked':'' ?>>
            <label class="form-check-label" for="t_int">Internal page</label>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <label class="form-label fw-semibold">Terms URL</label>
        <input type="url" name="terms_url" class="form-control" value="<?= sv($s,'terms_url') ?>" placeholder="https://taxisdispatch.com/terms">
      </div>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-warning px-5"><i class="fas fa-save me-2"></i>Save General Settings</button>
</form>

<?php include 'footer.php'; ?>
