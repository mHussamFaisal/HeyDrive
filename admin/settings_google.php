<?php
$page_title = 'Settings › Google';
require_once 'header.php';
$pdo = db_connect();

$pdo->exec("CREATE TABLE IF NOT EXISTS td_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['google_maps_js_key','google_maps_embed_key','google_directions_key','google_places_key','google_geocoding_key'];
    foreach ($keys as $k) {
        $v = $_POST[$k] ?? '';
        $pdo->prepare("INSERT INTO td_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$k,$v,$v]);
    }
    $msg = '✅ Google API settings saved.';
}

$s = [];
foreach ($pdo->query("SELECT setting_key,setting_value FROM td_settings") as $r)
    $s[$r['setting_key']] = $r['setting_value'];
function sv($s,$k,$d=''){return htmlspecialchars($s[$k]??$d);}
?>
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Google</li>
  </ol>
</nav>
<?php if($msg): ?><div class="alert alert-success alert-dismissible fade show"><?=$msg?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="alert alert-info mb-4">
  <i class="fab fa-google me-2"></i>
  To make the booking software fully operational you must set your Google API keys. 
  <a href="https://console.cloud.google.com/apis" target="_blank" class="alert-link">Open Google Console →</a>
</div>

<form method="POST">

<!-- Browser Keys -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-globe me-2 text-warning"></i>Browser Key <small class="text-muted fw-normal">(client-side, Maps JavaScript &amp; Embed)</small></div>
  <div class="card-body">
    <div class="alert alert-light border mb-3 small">
      Add these <strong>HTTP referrer restrictions</strong> in Google Console:<br>
      <code><?= htmlspecialchars((isset($_SERVER['HTTP_HOST'])?'https://'.$_SERVER['HTTP_HOST']:'https://taxisdispatch.com')) ?>/*</code>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Maps JavaScript API Key</label>
        <div class="input-group">
          <input type="password" name="google_maps_js_key" class="form-control" id="maps_js" value="<?= sv($s,'google_maps_js_key') ?>" placeholder="AIza...">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('maps_js')"><i class="fas fa-eye"></i></button>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Maps Embed API Key</label>
        <div class="input-group">
          <input type="password" name="google_maps_embed_key" class="form-control" id="maps_embed" value="<?= sv($s,'google_maps_embed_key') ?>" placeholder="AIza...">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('maps_embed')"><i class="fas fa-eye"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Server Keys -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white fw-bold"><i class="fas fa-server me-2 text-warning"></i>Server Key <small class="text-muted fw-normal">(server-side, Directions / Places / Geocoding)</small></div>
  <div class="card-body">
    <div class="alert alert-light border mb-3 small">
      Add your <strong>server IP address restriction</strong> in Google Console for these keys.
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Directions API Key</label>
        <div class="input-group">
          <input type="password" name="google_directions_key" class="form-control" id="dir_key" value="<?= sv($s,'google_directions_key') ?>" placeholder="AIza...">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('dir_key')"><i class="fas fa-eye"></i></button>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Places API Key</label>
        <div class="input-group">
          <input type="password" name="google_places_key" class="form-control" id="places_key" value="<?= sv($s,'google_places_key') ?>" placeholder="AIza...">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('places_key')"><i class="fas fa-eye"></i></button>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Geocoding API Key</label>
        <div class="input-group">
          <input type="password" name="google_geocoding_key" class="form-control" id="geo_key" value="<?= sv($s,'google_geocoding_key') ?>" placeholder="AIza...">
          <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('geo_key')"><i class="fas fa-eye"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-warning px-5"><i class="fas fa-save me-2"></i>Save Google Settings</button>
</form>

<script>
function togglePwd(id) {
    var el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
<?php include 'footer.php'; ?>
