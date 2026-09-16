<?php
require_once '../includes/config.php';
$pdo = db_connect();

$pdo->exec("CREATE TABLE IF NOT EXISTS td_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = ''; $msg_type = 'success';

// Test email action
if (isset($_GET['test_email']) && !empty($_GET['test_email'])) {
    $to = filter_var($_GET['test_email'], FILTER_VALIDATE_EMAIL);
    if ($to) {
        $sent = mail($to, 'Test Email from TaxisDispatch', 'This is a test email from your TaxisDispatch admin panel.', "From: noreply@taxisdispatch.com");
        $msg = $sent ? '✅ Test email sent to '.$to : '❌ Failed to send test email. Check your SMTP settings.';
        $msg_type = $sent ? 'success' : 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['test_email'])) {
    $keys = ['mail_from_name','mail_from_email','mail_reply_to','mail_admin_email',
             'mail_connection_type','mail_provider',
             'smtp_host','smtp_port','smtp_username','smtp_password','smtp_security'];
    foreach ($keys as $k) {
        $v = $_POST[$k] ?? '';
        $pdo->prepare("INSERT INTO td_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$k,$v,$v]);
    }
    $msg = '✅ Email settings saved.';
}

$s = [];
foreach ($pdo->query("SELECT setting_key,setting_value FROM td_settings") as $r)
    $s[$r['setting_key']] = $r['setting_value'];
function sv($s,$k,$d=''){return htmlspecialchars($s[$k]??$d);}

$page_title = 'Settings › Email';
require_once 'header.php';
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Email</li>
  </ol>
</nav>
<?php if($msg): ?><div class="alert alert-<?=$msg_type?> alert-dismissible fade show"><?=$msg?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<form method="POST">

<!-- From / Admin -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-envelope me-2 text-warning"></i>Sender Configuration</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">From Name</label>
        <input type="text" name="mail_from_name" class="form-control" value="<?= sv($s,'mail_from_name','TaxisDispatch') ?>" placeholder="TaxisDispatch">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">From Email</label>
        <input type="email" name="mail_from_email" class="form-control" value="<?= sv($s,'mail_from_email') ?>" placeholder="bookings@taxisdispatch.com">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Reply-to Email <small class="text-muted">(optional)</small></label>
        <input type="email" name="mail_reply_to" class="form-control" value="<?= sv($s,'mail_reply_to') ?>" placeholder="Optional">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Admin Email <small class="text-muted">(receives notifications)</small></label>
        <input type="email" name="mail_admin_email" class="form-control" value="<?= sv($s,'mail_admin_email') ?>" placeholder="info@taxisdispatch.com">
      </div>
    </div>
  </div>
</div>

<!-- SMTP -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-cogs me-2 text-warning"></i>SMTP Configuration</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Connection Type</label>
        <select name="mail_connection_type" class="form-select">
          <option value="smtp" <?= sv($s,'mail_connection_type','smtp')==='smtp'?'selected':'' ?>>SMTP – Your mail server</option>
          <option value="php_mail" <?= sv($s,'mail_connection_type')==='php_mail'?'selected':'' ?>>PHP mail() (server default)</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Provider</label>
        <select name="mail_provider" class="form-select">
          <option value="other"     <?= sv($s,'mail_provider','other')==='other'?'selected':'' ?>>Other</option>
          <option value="gmail"     <?= sv($s,'mail_provider')==='gmail'?'selected':'' ?>>Gmail</option>
          <option value="sendgrid"  <?= sv($s,'mail_provider')==='sendgrid'?'selected':'' ?>>SendGrid</option>
          <option value="mailgun"   <?= sv($s,'mail_provider')==='mailgun'?'selected':'' ?>>Mailgun</option>
          <option value="ses"       <?= sv($s,'mail_provider')==='ses'?'selected':'' ?>>Amazon SES</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">SMTP Host</label>
        <input type="text" name="smtp_host" class="form-control" value="<?= sv($s,'smtp_host') ?>" placeholder="mail.privateemail.com">
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold">SMTP Port</label>
        <input type="number" name="smtp_port" class="form-control" value="<?= sv($s,'smtp_port','587') ?>" placeholder="587">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">SMTP Security</label>
        <div class="d-flex gap-3 mt-2">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="smtp_security" value="none" id="sec_none" <?= sv($s,'smtp_security','tls')==='none'?'checked':'' ?>>
            <label class="form-check-label" for="sec_none">None</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="smtp_security" value="ssl" id="sec_ssl" <?= sv($s,'smtp_security')==='ssl'?'checked':'' ?>>
            <label class="form-check-label" for="sec_ssl">SSL</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="smtp_security" value="tls" id="sec_tls" <?= sv($s,'smtp_security','tls')==='tls'?'checked':'' ?>>
            <label class="form-check-label" for="sec_tls">TLS</label>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">SMTP Username</label>
        <input type="text" name="smtp_username" class="form-control" value="<?= sv($s,'smtp_username') ?>" placeholder="your@email.com">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">SMTP Password</label>
        <div class="input-group">
          <input type="password" name="smtp_password" class="form-control" id="smtp_pwd" value="<?= sv($s,'smtp_password') ?>">
          <button class="btn btn-outline-secondary" type="button" onclick="var e=document.getElementById('smtp_pwd');e.type=e.type==='password'?'text':'password'"><i class="fas fa-eye"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Test Email -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-paper-plane me-2 text-warning"></i>Send a Test Email</div>
  <div class="card-body">
    <div class="input-group" style="max-width:500px">
      <input type="email" id="test_addr" class="form-control" placeholder="Enter your email address">
      <button class="btn btn-outline-warning" type="button" onclick="window.location=window.location.pathname+'?test_email='+encodeURIComponent(document.getElementById('test_addr').value)">Send</button>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-warning px-5"><i class="fas fa-save me-2"></i>Save Email Settings</button>
</form>
<?php include 'footer.php'; ?>
