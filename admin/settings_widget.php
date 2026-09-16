<?php
$page_title = 'Settings › Web Widget & Integration';
require_once 'header.php';
$pdo = db_connect();

$pdo->exec("CREATE TABLE IF NOT EXISTS td_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'widget_bg_color', 'widget_accent_color', 'widget_inactive_color',
        'widget_input_bg', 'widget_input_text_color', 'widget_btn_bg',
        'widget_btn_text_color', 'widget_border_radius', 'widget_card_radius'
    ];
    foreach ($keys as $k) {
        $v = $_POST[$k] ?? '';
        $pdo->prepare("INSERT INTO td_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$k,$v,$v]);
    }
    $msg = '✅ Web Widget design and color settings saved successfully!';
}

$s = [];
foreach ($pdo->query("SELECT setting_key,setting_value FROM td_settings") as $r) {
    $s[$r['setting_key']] = $r['setting_value'];
}
function sv_w($s, $k, $d) { return htmlspecialchars($s[$k] ?? $d); }

$widget_url = APP_URL . '/widget/booking.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="settings.php">Settings</a></li>
    <li class="breadcrumb-item active">Web Widget &amp; Integration</li>
  </ol>
</nav>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show">
  <?= $msg ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Left Side: Color & Design Configuration -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold py-3">
        <i class="fas fa-palette me-2 text-warning"></i>Widget Color &amp; Style Customization
      </div>
      <div class="card-body">
        <form method="POST" id="widgetConfigForm">
          
          <div class="row g-3">
            <!-- Widget Background Color -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Card Background Color</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="bg_picker" value="<?= sv_w($s,'widget_bg_color','#0a0a0a') ?>" onchange="syncColor('bg_picker','bg_text')">
                <input type="text" name="widget_bg_color" id="bg_text" class="form-control" value="<?= sv_w($s,'widget_bg_color','#0a0a0a') ?>" oninput="syncColor('bg_text','bg_picker')">
              </div>
            </div>

            <!-- Accent Button / Active Pill -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Active Button / Accent Color</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="accent_picker" value="<?= sv_w($s,'widget_accent_color','#d98a39') ?>" onchange="syncColor('accent_picker','accent_text')">
                <input type="text" name="widget_accent_color" id="accent_text" class="form-control" value="<?= sv_w($s,'widget_accent_color','#d98a39') ?>" oninput="syncColor('accent_text','accent_picker')">
              </div>
            </div>

            <!-- Inactive Button Text/Border -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Inactive Pill Color</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="inactive_picker" value="<?= sv_w($s,'widget_inactive_color','#ffffff') ?>" onchange="syncColor('inactive_picker','inactive_text')">
                <input type="text" name="widget_inactive_color" id="inactive_text" class="form-control" value="<?= sv_w($s,'widget_inactive_color','#ffffff') ?>" oninput="syncColor('inactive_text','inactive_picker')">
              </div>
            </div>

            <!-- Input Cards Background -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Input Fields Background</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="input_bg_picker" value="<?= sv_w($s,'widget_input_bg','#e5e5e5') ?>" onchange="syncColor('input_bg_picker','input_bg_text')">
                <input type="text" name="widget_input_bg" id="input_bg_text" class="form-control" value="<?= sv_w($s,'widget_input_bg','#e5e5e5') ?>" oninput="syncColor('input_bg_text','input_bg_picker')">
              </div>
            </div>

            <!-- Input Text Color -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Input Text Color</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="input_text_picker" value="<?= sv_w($s,'widget_input_text_color','#111111') ?>" onchange="syncColor('input_text_picker','input_text_text')">
                <input type="text" name="widget_input_text_color" id="input_text_text" class="form-control" value="<?= sv_w($s,'widget_input_text_color','#111111') ?>" oninput="syncColor('input_text_text','input_text_picker')">
              </div>
            </div>

            <!-- Submit Button Background -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Submit Button Background</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="btn_bg_picker" value="<?= sv_w($s,'widget_btn_bg','#000000') ?>" onchange="syncColor('btn_bg_picker','btn_bg_text')">
                <input type="text" name="widget_btn_bg" id="btn_bg_text" class="form-control" value="<?= sv_w($s,'widget_btn_bg','#000000') ?>" oninput="syncColor('btn_bg_text','btn_bg_picker')">
              </div>
            </div>

            <!-- Submit Button Text Color -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Submit Button Text Color</label>
              <div class="input-group">
                <input type="color" class="form-control form-control-color" id="btn_text_picker" value="<?= sv_w($s,'widget_btn_text_color','#ffffff') ?>" onchange="syncColor('btn_text_picker','btn_text_text')">
                <input type="text" name="widget_btn_text_color" id="btn_text_text" class="form-control" value="<?= sv_w($s,'widget_btn_text_color','#ffffff') ?>" oninput="syncColor('btn_text_text','btn_text_picker')">
              </div>
            </div>

            <!-- Outer Border Radius -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Outer Border Radius</label>
              <select name="widget_border_radius" id="border_radius" class="form-select" onchange="updatePreview()">
                <option value="12px" <?= sv_w($s,'widget_border_radius','')==='12px'?'selected':'' ?>>12px (Slightly Rounded)</option>
                <option value="16px" <?= sv_w($s,'widget_border_radius','')==='16px'?'selected':'' ?>>16px (Medium Rounded)</option>
                <option value="20px" <?= sv_w($s,'widget_border_radius','20px')==='20px'?'selected':'' ?>>20px (Modern Rounded - Default)</option>
                <option value="28px" <?= sv_w($s,'widget_border_radius','')==='28px'?'selected':'' ?>>28px (Extra Soft)</option>
              </select>
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-warning px-4 fw-bold">
              <i class="fas fa-save me-2"></i>Save Colors &amp; Design
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Embed Snippets & Integration -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-bold py-3">
        <i class="fas fa-code me-2 text-warning"></i>Embed Code &amp; WordPress Shortcode
      </div>
      <div class="card-body">
        
        <!-- HTML / Iframe Embed Code -->
        <h6 class="fw-bold"><i class="fab fa-html5 me-1 text-danger"></i> 1. HTML / iframe Embed Code</h6>
        <p class="text-muted small">Copy and paste this snippet into any website HTML page:</p>
        <div class="position-relative mb-3">
          <textarea id="iframeSnippet" class="form-control bg-dark text-light font-monospace small" rows="3" readonly><iframe src="<?= $widget_url ?>" width="100%" height="520" frameborder="0" style="border:none; max-width:480px; width:100%; border-radius:20px; overflow:hidden;"></iframe></textarea>
          <button class="btn btn-sm btn-warning position-absolute top-0 end-0 m-2" onclick="copySnippet('iframeSnippet')">
            <i class="fas fa-copy me-1"></i>Copy Embed
          </button>
        </div>

        <hr class="my-4">

        <!-- WordPress Integration -->
        <h6 class="fw-bold"><i class="fab fa-wordpress me-1 text-primary"></i> 2. WordPress Plugin &amp; Shortcode Integration</h6>
        <p class="text-muted small">Upload the ready-made WordPress plugin or use shortcode <code>[taxi_booking_form]</code> on any page.</p>

        <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 mb-3 d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold text-primary mb-1"><i class="fas fa-file-archive me-1"></i> Ready-to-Install WordPress Plugin (.zip)</div>
            <div class="text-muted small">Upload via <strong>Plugins &rarr; Add New &rarr; Upload Plugin</strong> in WordPress.</div>
          </div>
          <a href="<?= APP_URL ?>/wordpress-plugin/taxi-booking-widget.zip" class="btn btn-primary btn-sm fw-bold shadow-sm" download>
            <i class="fas fa-download me-1"></i> Download Plugin .ZIP
          </a>
        </div>

        <div class="alert alert-light border small mb-2">
          Or add this PHP code to your WordPress theme's <code>functions.php</code> file:
        </div>
        <div class="position-relative">
          <textarea id="wpSnippet" class="form-control bg-dark text-light font-monospace small" rows="7" readonly><?php echo htmlspecialchars("<?php
// Taxi Dispatch WordPress Booking Widget Shortcode
add_shortcode('taxi_booking_form', function(\$atts) {
    \$url = '" . $widget_url . "';
    return '<div style=\"display:flex;justify-content:center;width:100%;\"><iframe src=\"'.esc_url(\$url).'\" width=\"100%\" height=\"450\" frameborder=\"0\" style=\"border:none;max-width:480px;width:100%;border-radius:20px;overflow:hidden;\"></iframe></div>';
});
"); ?></textarea>
          <button class="btn btn-sm btn-warning position-absolute top-0 end-0 m-2" onclick="copySnippet('wpSnippet')">
            <i class="fas fa-copy me-1"></i>Copy WP Code
          </button>
        </div>

      </div>
    </div>
  </div>

  <!-- Right Side: Live Interactive Widget Preview -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
      <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-eye me-2 text-warning"></i>Live Interactive Widget Preview</span>
        <button class="btn btn-sm btn-outline-secondary" onclick="reloadPreview()">
          <i class="fas fa-sync-alt me-1"></i>Refresh
        </button>
      </div>
      <div class="card-body bg-light text-center p-4">
        <iframe id="previewIframe" src="<?= $widget_url ?>" width="100%" height="450" frameborder="0" style="border:none; max-width:480px; width:100%; border-radius:20px; box-shadow: 0 12px 30px rgba(0,0,0,0.15);"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
function syncColor(fromId, toId) {
    document.getElementById(toId).value = document.getElementById(fromId).value;
    updatePreview();
}

function updatePreview() {
    const bg = encodeURIComponent(document.getElementById('bg_text').value);
    const accent = encodeURIComponent(document.getElementById('accent_text').value);
    const inactive = encodeURIComponent(document.getElementById('inactive_text').value);
    const inputBg = encodeURIComponent(document.getElementById('input_bg_text').value);
    const inputText = encodeURIComponent(document.getElementById('input_text_text').value);
    const btnBg = encodeURIComponent(document.getElementById('btn_bg_text').value);
    const btnText = encodeURIComponent(document.getElementById('btn_text_text').value);
    const radius = encodeURIComponent(document.getElementById('border_radius').value);

    const newUrl = "<?= $widget_url ?>?bg=" + bg + "&accent=" + accent + "&inactive=" + inactive + "&input_bg=" + inputBg + "&input_text=" + inputText + "&btn_bg=" + btnBg + "&btn_text=" + btnText + "&radius=" + radius;
    document.getElementById('previewIframe').src = newUrl;
}

function reloadPreview() {
    document.getElementById('previewIframe').src = document.getElementById('previewIframe').src;
}

function copySnippet(id) {
    const el = document.getElementById(id);
    el.select();
    document.execCommand('copy');
    alert('Snippet copied to clipboard!');
}
</script>

<?php include 'footer.php'; ?>
