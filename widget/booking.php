<?php
require_once __DIR__ . '/../includes/config.php';
$pdo = db_connect();

// Ensure trip_type and return_datetime columns exist in td_bookings
try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN trip_type VARCHAR(20) DEFAULT 'one_way'"); } catch(Exception $e){}
try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN return_datetime DATETIME NULL"); } catch(Exception $e){}

// Fetch settings from td_settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM td_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch(Exception $e){}

// Google Maps Key
$gmaps_key = $settings['google_maps_js_key'] ?? ($settings['google_places_key'] ?? '');

// Color & Styling Config (URL parameters can override DB defaults)
$bg_color          = htmlspecialchars($_GET['bg'] ?? ($settings['widget_bg_color'] ?? '#0a0a0a'));
$accent_color      = htmlspecialchars($_GET['accent'] ?? ($settings['widget_accent_color'] ?? '#d98a39'));
$inactive_color    = htmlspecialchars($_GET['inactive'] ?? ($settings['widget_inactive_color'] ?? '#ffffff'));
$input_bg          = htmlspecialchars($_GET['input_bg'] ?? ($settings['widget_input_bg'] ?? '#e5e5e5'));
$input_text        = htmlspecialchars($_GET['input_text'] ?? ($settings['widget_input_text_color'] ?? '#111111'));
$btn_bg            = htmlspecialchars($_GET['btn_bg'] ?? ($settings['widget_btn_bg'] ?? '#000000'));
$btn_text          = htmlspecialchars($_GET['btn_text'] ?? ($settings['widget_btn_text_color'] ?? '#ffffff'));
$border_radius     = htmlspecialchars($_GET['radius'] ?? ($settings['widget_border_radius'] ?? '20px'));
$card_radius       = htmlspecialchars($_GET['card_radius'] ?? ($settings['widget_card_radius'] ?? '16px'));

// Default date/time
$default_date = date('Y-m-d');
$default_time = date('H:i', ceil(time() / 1800) * 1800);

// Handle Form Submission from Widget
$error = '';
$success_ref = '';
$created_booking_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['widget_submit'])) {
    $pickup      = sanitize($_POST['pickup_address'] ?? '');
    $dropoff     = sanitize($_POST['dropoff_address'] ?? '');
    $pdate       = sanitize($_POST['pickup_date'] ?? '');
    $ptime       = sanitize($_POST['pickup_time'] ?? '');
    $trip_type   = sanitize($_POST['trip_type'] ?? 'one_way');
    $rdate       = sanitize($_POST['return_date'] ?? '');
    $rtime       = sanitize($_POST['return_time'] ?? '');
    
    if ($pickup && $dropoff && $pdate && $ptime) {
        $pickup_datetime = $pdate . ' ' . $ptime . ':00';
        $return_datetime = ($trip_type === 'two_way' && $rdate && $rtime) ? ($rdate . ' ' . $rtime . ':00') : null;
        
        $ref = 'TD' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Estimate fare from pricing
        $fare = 0.00;
        try {
            $pr = $pdo->query("SELECT min_fare FROM td_pricing LIMIT 1");
            if ($pr) {
                $f_val = $pr->fetchColumn();
                if ($f_val) $fare = (float)$f_val;
            }
        } catch(Exception $e){}

        try {
            $stmt = $pdo->prepare("INSERT INTO td_bookings 
                (booking_ref, customer_name, customer_email, customer_phone, 
                 pickup_address, dropoff_address, pickup_datetime, trip_type, return_datetime,
                 passengers, vehicle_type, notes, payment_method, fare, status, created_at) 
                VALUES (?, 'Website Customer', '', '', ?, ?, ?, ?, ?, 1, 'sedan', 'Booked via Web Widget', 'pending', ?, 'pending', NOW())");
            
            $stmt->execute([
                $ref, $pickup, $dropoff, $pickup_datetime, 
                $trip_type, $return_datetime, $fare
            ]);
            
            $created_booking_id = $pdo->lastInsertId();
            $success_ref = $ref;

            // Construct redirect URL to complete passenger info / vehicle selection
            $target_url = APP_URL . '/index.php?ref=' . urlencode($ref) . '&pickup_address=' . urlencode($pickup) . '&dropoff_address=' . urlencode($dropoff) . '&pickup_date=' . urlencode($pdate) . '&pickup_time=' . urlencode($ptime) . '#book';
            
            // Auto redirect if requested, or present booking reference
            if (isset($_POST['auto_redirect']) && $_POST['auto_redirect'] == '1') {
                echo "<script>if (window.top !== window.self) { window.top.location.href = " . json_encode($target_url) . "; } else { window.location.href = " . json_encode($target_url) . "; }</script>";
                exit;
            }

        } catch (Exception $e) {
            $error = "Failed to store booking: " . $e->getMessage();
        }
    } else {
        $error = "Please enter pickup address, destination, date and time.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Taxi Booking Widget</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
:root {
  --bg-color: <?= $bg_color ?>;
  --accent-color: <?= $accent_color ?>;
  --inactive-color: <?= $inactive_color ?>;
  --input-bg: <?= $input_bg ?>;
  --input-text: <?= $input_text ?>;
  --btn-bg: <?= $btn_bg ?>;
  --btn-text: <?= $btn_text ?>;
  --border-radius: <?= $border_radius ?>;
  --card-radius: <?= $card_radius ?>;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

body {
  background: transparent;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 12px;
  min-height: 100vh;
}

.widget-card {
  background: var(--bg-color);
  width: 100%;
  max-width: 480px;
  border-radius: var(--border-radius);
  padding: 28px 24px;
  box-shadow: 0 16px 40px rgba(0,0,0,0.35);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

#widgetForm {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Success Card */
.success-box {
  background: #111e16;
  border: 1.5px solid #198754;
  border-radius: var(--card-radius);
  padding: 20px;
  text-align: center;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: center;
}

.success-icon {
  font-size: 38px;
  color: #2ec4b6;
}

.ref-badge {
  background: var(--accent-color);
  color: #111111;
  font-size: 18px;
  font-weight: 800;
  padding: 6px 16px;
  border-radius: 999px;
  letter-spacing: 1px;
}

.continue-btn {
  background: var(--accent-color);
  color: #111111;
  text-decoration: none;
  font-weight: 800;
  font-size: 14px;
  padding: 12px 24px;
  border-radius: 999px;
  display: inline-block;
  margin-top: 6px;
  transition: all 0.2s;
}

.continue-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(217, 138, 57, 0.4);
}

/* Top Toggle Pill Buttons */
.trip-type-toggle {
  display: flex;
  gap: 12px;
  margin-bottom: 6px;
}

.toggle-btn {
  flex: 1;
  padding: 12px 18px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 15px;
  text-align: center;
  cursor: pointer;
  transition: all 0.25s ease;
  user-select: none;
}

.toggle-btn.active {
  background: var(--accent-color);
  color: #111111;
  border: 2px solid var(--accent-color);
  box-shadow: 0 4px 14px rgba(217, 138, 57, 0.35);
}

.toggle-btn.inactive {
  background: transparent;
  color: var(--inactive-color);
  border: 1.5px solid rgba(255, 255, 255, 0.35);
}

.toggle-btn.inactive:hover {
  border-color: var(--accent-color);
  color: var(--accent-color);
}

/* Form Input Group Cards */
.input-card {
  background: var(--input-bg);
  border-radius: var(--card-radius);
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: transform 0.15s, box-shadow 0.15s;
}

.input-card:focus-within {
  box-shadow: 0 0 0 2px var(--accent-color);
}

.input-label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1px;
  color: #666666;
  text-transform: uppercase;
}

.input-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.input-icon {
  font-size: 17px;
  color: #222222;
  width: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.form-control {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 15px;
  font-weight: 600;
  color: var(--input-text);
  outline: none;
}

.form-control::placeholder {
  color: #888888;
  font-weight: 500;
}

/* Date & Time Grid */
.datetime-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

/* Submit Button */
.submit-btn {
  background: var(--btn-bg);
  color: var(--btn-text);
  border: 1.5px solid rgba(255,255,255,0.4);
  border-radius: 999px;
  padding: 16px;
  font-size: 15px;
  font-weight: 800;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  cursor: pointer;
  width: 100%;
  margin-top: 4px;
  transition: all 0.25s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.submit-btn:hover {
  background: var(--accent-color);
  color: #111111;
  border-color: var(--accent-color);
  box-shadow: 0 6px 20px rgba(217, 138, 57, 0.4);
  transform: translateY(-1px);
}

.alert-danger {
  background: #ff4d4d;
  color: #ffffff;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
}

#return_section {
  display: none;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}

.pac-container {
  border-radius: 12px !important;
  box-shadow: 0 10px 30px rgba(0,0,0,0.25) !important;
  border: none !important;
  font-family: inherit !important;
  z-index: 999999 !important;
}

.pac-item {
  padding: 10px 14px !important;
  font-size: 14px !important;
}
</style>
</head>
<body>

<div class="widget-card">

  <?php if ($success_ref): ?>
    <!-- Booking Successfully Stored in Database -->
    <div class="success-box">
      <div class="success-icon"><i class="fas fa-check-circle"></i></div>
      <h3 style="margin:0;font-weight:800;">Booking Received!</h3>
      <p style="font-size:13px;color:#cccccc;margin:0;">Your ride request has been saved in our dispatch system.</p>
      <div class="ref-badge">REF: <?= htmlspecialchars($success_ref) ?></div>
      
      <?php 
        $target_final = APP_URL . '/index.php?ref=' . urlencode($success_ref) . '#book';
      ?>
      <a href="<?= $target_final ?>" target="_top" class="continue-btn">
        <i class="fas fa-taxi me-1"></i> Proceed to Vehicle & Payment →
      </a>
    </div>

  <?php else: ?>

    <?php if ($error): ?>
      <div class="alert-danger"><i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="widgetForm">
      <input type="hidden" name="trip_type" id="trip_type" value="one_way">
      <input type="hidden" name="auto_redirect" value="0">

      <!-- Top Toggle Buttons -->
      <div class="trip-type-toggle">
        <div class="toggle-btn active" id="btn_one_way" onclick="setTripType('one_way')">
          Transfer
        </div>
        <div class="toggle-btn inactive" id="btn_two_way" onclick="setTripType('two_way')">
          Hourly / Return
        </div>
      </div>

      <!-- Pickup Location -->
      <div class="input-card">
        <span class="input-label">PICKUP</span>
        <div class="input-row">
          <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
          <input type="text" id="pickup_address" name="pickup_address" class="form-control" 
                 placeholder="Kennedystraße, 51147 Cologne, Germany" autocomplete="off" required>
        </div>
      </div>

      <!-- Destination Location -->
      <div class="input-card">
        <span class="input-label">DESTINATION</span>
        <div class="input-row">
          <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
          <input type="text" id="dropoff_address" name="dropoff_address" class="form-control" 
                 placeholder="Johannisstraße 76-80, 50668 Cologne, Germany" autocomplete="off" required>
        </div>
      </div>

      <!-- Date & Time Row -->
      <div class="datetime-grid">
        <div class="input-card">
          <span class="input-label">PICKUP DATE</span>
          <div class="input-row">
            <span class="input-icon"><i class="far fa-calendar-alt"></i></span>
            <input type="date" id="pickup_date" name="pickup_date" class="form-control" 
                   value="<?= $default_date ?>" min="<?= $default_date ?>" required>
          </div>
        </div>

        <div class="input-card">
          <span class="input-label">PICKUP TIME</span>
          <div class="input-row">
            <span class="input-icon"><i class="far fa-clock"></i></span>
            <input type="time" id="pickup_time" name="pickup_time" class="form-control" 
                   value="<?= $default_time ?>" required>
          </div>
        </div>
      </div>

      <!-- Return Date & Time Section -->
      <div id="return_section" class="datetime-grid">
        <div class="input-card">
          <span class="input-label">RETURN DATE</span>
          <div class="input-row">
            <span class="input-icon"><i class="far fa-calendar-alt"></i></span>
            <input type="date" id="return_date" name="return_date" class="form-control" 
                   value="<?= $default_date ?>" min="<?= $default_date ?>">
          </div>
        </div>

        <div class="input-card">
          <span class="input-label">RETURN TIME</span>
          <div class="input-row">
            <span class="input-icon"><i class="far fa-clock"></i></span>
            <input type="time" id="return_time" name="return_time" class="form-control" 
                   value="<?= $default_time ?>">
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <button type="submit" name="widget_submit" class="submit-btn">
        BOOK NOW
      </button>
    </form>

  <?php endif; ?>

</div>

<script>
function setTripType(type) {
  document.getElementById('trip_type').value = type;
  const btnOne = document.getElementById('btn_one_way');
  const btnTwo = document.getElementById('btn_two_way');
  const retSection = document.getElementById('return_section');

  if (type === 'one_way') {
    btnOne.className = 'toggle-btn active';
    btnTwo.className = 'toggle-btn inactive';
    retSection.style.display = 'none';
  } else {
    btnOne.className = 'toggle-btn inactive';
    btnTwo.className = 'toggle-btn active';
    retSection.style.display = 'grid';
  }
}
</script>

<?php if ($gmaps_key): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode($gmaps_key) ?>&libraries=places"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  if (typeof google !== 'undefined' && google.maps && google.maps.places) {
    const pickupInput = document.getElementById('pickup_address');
    const dropoffInput = document.getElementById('dropoff_address');

    if (pickupInput) {
      new google.maps.places.Autocomplete(pickupInput, { types: ['geocode', 'establishment'] });
    }
    if (dropoffInput) {
      new google.maps.places.Autocomplete(dropoffInput, { types: ['geocode', 'establishment'] });
    }
  }
});
</script>
<?php endif; ?>

</body>
</html>
