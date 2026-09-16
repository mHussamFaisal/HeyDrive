<?php
$page_title = 'Settings › Localization';
require_once 'header.php';
$pdo = db_connect();

$pdo->exec("CREATE TABLE IF NOT EXISTS td_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['default_language','language_switcher','language_switcher_style','language_switcher_display',
             'show_language_code','phone_prefix','timezone','date_format','time_format','week_starts_on'];
    foreach ($keys as $k) {
        $v = $_POST[$k] ?? '';
        $pdo->prepare("INSERT INTO td_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$k,$v,$v]);
    }
    $msg = '✅ Localization settings saved.';
}

$s = [];
foreach ($pdo->query("SELECT setting_key,setting_value FROM td_settings") as $r)
    $s[$r['setting_key']] = $r['setting_value'];
function sv($s,$k,$d=''){return htmlspecialchars($s[$k]??$d);}

$languages = [
    'en'=>'English','es'=>'Spanish (Español)','pt'=>'Portuguese (Português)',
    'pt_BR'=>'Portuguese (Brazil)','fr'=>'French (Français)','it'=>'Italian (Italiano)',
    'el'=>'Greek (Ελληνικά)','nl'=>'Dutch (Nederlands)','fi'=>'Finnish (Suomi)',
    'de'=>'German (Deutsch)','pl'=>'Polish (Polski)','cs'=>'Czech (Čeština)',
    'sk'=>'Slovak (Slovenský)','hu'=>'Hungarian (Magyar)','sr'=>'Serbian (Srpski)',
    'ru'=>'Russian (Русский)','ar'=>'Arabic (Egypt)'
];

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
$date_formats = ['d/m/Y'=>'31/12/2025','m/d/Y'=>'12/31/2025','Y-m-d'=>'2025-12-31','d.m.Y'=>'31.12.2025'];
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Localization</li>
  </ol>
</nav>
<?php if($msg): ?><div class="alert alert-success alert-dismissible fade show"><?=$msg?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<form method="POST">

<!-- Language -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-language me-2 text-warning"></i>Language</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Default Language</label>
        <select name="default_language" class="form-select">
          <?php foreach($languages as $code=>$label): ?>
          <option value="<?=$code?>" <?= sv($s,'default_language','en')===$code?'selected':'' ?>><?=htmlspecialchars($label)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="language_switcher" value="1" <?= !empty($s['language_switcher'])?'checked':'' ?> id="ls">
          <label class="form-check-label fw-semibold" for="ls">Enable Language Switcher (front-end)</label>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Style</label>
        <select name="language_switcher_style" class="form-select">
          <option value="horizontal" <?= sv($s,'language_switcher_style','horizontal')==='horizontal'?'selected':'' ?>>Horizontal</option>
          <option value="dropdown"   <?= sv($s,'language_switcher_style')==='dropdown'?'selected':'' ?>>Dropdown</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Display</label>
        <select name="language_switcher_display" class="form-select">
          <option value="flags" <?= sv($s,'language_switcher_display','flags')==='flags'?'selected':'' ?>>Flags</option>
          <option value="names" <?= sv($s,'language_switcher_display')==='names'?'selected':'' ?>>Names</option>
          <option value="codes" <?= sv($s,'language_switcher_display')==='codes'?'selected':'' ?>>Codes</option>
        </select>
      </div>
      <div class="col-md-3">
        <div class="form-check form-switch mt-4">
          <input class="form-check-input" type="checkbox" name="show_language_code" value="1" <?= !empty($s['show_language_code'])?'checked':'' ?>>
          <label class="form-check-label">Show names as language code</label>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Date, Time & Region -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-clock me-2 text-warning"></i>Date, Time &amp; Region</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Phone Number Prefix</label>
        <select name="phone_prefix" class="form-select">
          <option value="auto"  <?= sv($s,'phone_prefix','auto')==='auto'?'selected':'' ?>>Auto detection</option>
          <option value="manual"<?= sv($s,'phone_prefix')==='manual'?'selected':'' ?>>Manual</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Timezone</label>
        <select name="timezone" class="form-select">
          <?php foreach($timezones as $tz): ?>
          <option value="<?=htmlspecialchars($tz)?>" <?= sv($s,'timezone','Europe/Berlin')===$tz?'selected':'' ?>><?=htmlspecialchars($tz)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Date Format</label>
        <select name="date_format" class="form-select">
          <?php foreach($date_formats as $fmt=>$ex): ?>
          <option value="<?=$fmt?>" <?= sv($s,'date_format','d/m/Y')===$fmt?'selected':'' ?>><?=htmlspecialchars($ex)?> (<?=$fmt?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Time Format</label>
        <select name="time_format" class="form-select">
          <option value="H:i"   <?= sv($s,'time_format','H:i')==='H:i'?'selected':'' ?>>15:00 (24h)</option>
          <option value="h:i A" <?= sv($s,'time_format')==='h:i A'?'selected':'' ?>>03:00 PM (12h)</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Week Starts On</label>
        <select name="week_starts_on" class="form-select">
          <option value="Monday" <?= sv($s,'week_starts_on','Monday')==='Monday'?'selected':'' ?>>Monday</option>
          <option value="Sunday" <?= sv($s,'week_starts_on')==='Sunday'?'selected':'' ?>>Sunday</option>
          <option value="Saturday"<?= sv($s,'week_starts_on')==='Saturday'?'selected':'' ?>>Saturday</option>
        </select>
      </div>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-warning px-5"><i class="fas fa-save me-2"></i>Save Localization Settings</button>
</form>
<?php include 'footer.php'; ?>
