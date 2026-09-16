<?php
$page_title = 'Dispatch Center';
require_once 'header.php';

$pdo = db_connect();

// Get unassigned/pending bookings
$pending = $pdo->query("SELECT * FROM td_bookings WHERE status IN ('pending','confirmed') ORDER BY pickup_datetime ASC")->fetchAll();

// Get available drivers with vehicles
$drivers = $pdo->query("SELECT d.id, u.name, u.phone, d.status, d.rating, d.total_trips,
    v.make, v.model, v.license_plate, v.color, v.type as vtype
    FROM td_drivers d 
    JOIN td_users u ON d.user_id = u.id
    LEFT JOIN td_vehicles v ON d.vehicle_id = v.id
    WHERE u.status = 'active'
    ORDER BY FIELD(d.status,'available','busy','offline'), u.name")->fetchAll();

// Handle quick assign
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $bid = intval($_POST['booking_id']);
    $did = intval($_POST['driver_id']);
    $driver_info = $pdo->prepare("SELECT d.*, v.id as vid FROM td_drivers d LEFT JOIN td_vehicles v ON d.vehicle_id = v.id WHERE d.id=?");
    $driver_info->execute([$did]);
    $driver = $driver_info->fetch();
    $pdo->prepare("UPDATE td_bookings SET driver_id=?, vehicle_id=?, status='assigned' WHERE id=?")->execute([$did, $driver['vid'], $bid]);
    $pdo->prepare("UPDATE td_drivers SET status='busy' WHERE id=?")->execute([$did]);
    redirect(APP_URL . '/admin/dispatch.php?msg=assigned');
}
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success alert-dismissible fade show">✅ Driver assigned successfully!
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- Pending Bookings -->
  <div class="col-md-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-danger text-white fw-bold">
        ⏳ Unassigned Bookings (<?= count($pending) ?>)
      </div>
      <div class="card-body p-0">
        <?php if (empty($pending)): ?>
        <div class="text-center p-4 text-muted">🎉 All bookings are assigned!</div>
        <?php else: ?>
        <?php foreach ($pending as $b): ?>
        <div class="border-bottom p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <strong class="text-primary"><?= $b['booking_ref'] ?></strong>
              <?= status_badge($b['status']) ?>
              <div class="mt-1">
                <strong><?= htmlspecialchars($b['customer_name']) ?></strong> · <?= $b['customer_phone'] ?>
              </div>
              <div class="small text-muted mt-1">
                📅 <?= format_datetime($b['pickup_datetime']) ?> · 
                👥 <?= $b['passengers'] ?> pax · 
                🚗 <?= ucfirst($b['vehicle_type']) ?>
              </div>
              <div class="small mt-1">
                <span class="text-success">↑</span> <?= htmlspecialchars($b['pickup_address']) ?><br>
                <span class="text-danger">↓</span> <?= htmlspecialchars($b['dropoff_address']) ?>
              </div>
            </div>
            <form method="POST" class="ms-3 d-flex flex-column gap-2" style="min-width:180px">
              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <select name="driver_id" class="form-select form-select-sm" required>
                <option value="">Assign Driver...</option>
                <?php
                $grouped = ['available'=>[], 'busy'=>[], 'offline'=>[]];
                foreach ($drivers as $d) $grouped[$d['status'] ?? 'offline'][] = $d;
                $labels = ['available'=>'✅ Available', 'busy'=>'🟡 Busy (can still assign)', 'offline'=>'⚫ Offline'];
                foreach ($grouped as $status => $grp):
                  if (empty($grp)) continue;
                ?>
                <optgroup label="<?= $labels[$status] ?>">
                <?php foreach ($grp as $d): ?>
                <option value="<?=$d['id']?>" <?= $status !== 'available' ? 'style="color:#b8860b"' : '' ?>>
                  <?= htmlspecialchars($d['name']) ?> (<?= ucfirst($status) ?>) - <?= $d['license_plate'] ?? 'No Vehicle' ?>
                </option>
                <?php endforeach; ?>
                </optgroup>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="assign" class="btn btn-sm btn-warning">
                <i class="fas fa-paper-plane me-1"></i>Dispatch
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Driver Status Panel -->
  <div class="col-md-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-dark text-white fw-bold">
        🚖 Driver Status (<?= count($drivers) ?>)
      </div>
      <div class="card-body p-0">
        <?php foreach ($drivers as $d): ?>
        <div class="border-bottom p-3 d-flex align-items-center gap-3">
          <div>
            <?php $dot = ['available'=>'success','busy'=>'warning','offline'=>'secondary']; ?>
            <span class="badge bg-<?= $dot[$d['status']] ?? 'secondary' ?>"><?= ucfirst($d['status']) ?></span>
          </div>
          <div class="flex-fill">
            <div class="fw-bold"><?= htmlspecialchars($d['name']) ?></div>
            <div class="small text-muted"><?= $d['phone'] ?></div>
            <?php if ($d['license_plate']): ?>
            <div class="small">🚗 <?= $d['color'] ?> <?= $d['make'] ?> <?= $d['model'] ?> [<?= $d['license_plate'] ?>]</div>
            <?php endif; ?>
          </div>
          <div class="text-end">
            <div class="small">⭐ <?= $d['rating'] ?></div>
            <div class="small text-muted"><?= $d['total_trips'] ?> trips</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Active Trips -->
    <?php
    $active_trips = $pdo->query("SELECT b.*, u.name as driver_name 
        FROM td_bookings b 
        JOIN td_drivers d ON b.driver_id = d.id 
        JOIN td_users u ON d.user_id = u.id
        WHERE b.status IN ('assigned','in_progress')
        ORDER BY b.pickup_datetime ASC")->fetchAll();
    ?>
    <?php if ($active_trips): ?>
    <div class="card border-0 shadow-sm mt-4">
      <div class="card-header bg-primary text-white fw-bold">
        🚕 Active Trips (<?= count($active_trips) ?>)
      </div>
      <div class="card-body p-0">
        <?php foreach ($active_trips as $t): ?>
        <div class="border-bottom p-3">
          <div class="d-flex justify-content-between">
            <div>
              <strong><?= $t['booking_ref'] ?></strong> <?= status_badge($t['status']) ?>
              <div class="small"><?= htmlspecialchars($t['customer_name']) ?></div>
              <div class="small text-muted">Driver: <?= htmlspecialchars($t['driver_name']) ?></div>
            </div>
            <a href="booking_edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

</div><!-- end .row -->

<!-- ===== Google Maps Driver Tracking ===== -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-map-marked-alt me-2"></i>Live Driver Tracking Map</span>
        <div class="d-flex align-items-center gap-2">
          <span id="map-last-updated" class="small opacity-75"></span>
          <button class="btn btn-sm btn-light" onclick="refreshDriverMarkers()">
            <i class="fas fa-sync-alt me-1"></i>Refresh
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div id="dispatch-map" style="height:500px; width:100%;"></div>
      </div>
      <div class="card-footer bg-light small text-muted">
        <i class="fas fa-circle text-success me-1"></i>Available &nbsp;
        <i class="fas fa-circle text-warning me-1"></i>Busy &nbsp;
        <i class="fas fa-circle text-secondary me-1"></i>Offline &nbsp;&nbsp;
        Map auto-refreshes every 30 seconds. Driver app must share location for pins to appear.
      </div>
    </div>
  </div>
</div>

<script>
// ── Google Maps Driver Tracking ──────────────────────────────────────────────
var dispatchMap, markerCluster;
var driverMarkers = {};

// Colour per status
var STATUS_COLOUR = {
  available : '#28a745',
  busy      : '#ffc107',
  offline   : '#6c757d'
};

// Driver data injected by PHP
var DRIVER_DATA = <?php
$driver_map_data = [];
foreach ($drivers as $d) {
    $driver_map_data[] = [
        'id'     => (int)$d['id'],
        'name'   => htmlspecialchars($d['name'], ENT_QUOTES),
        'phone'  => $d['phone'],
        'status' => $d['status'],
        'plate'  => $d['license_plate'] ?? '',
        'vehicle'=> trim(($d['color'] ?? '').' '.($d['make'] ?? '').' '.($d['model'] ?? '')),
        'rating' => $d['rating'],
        'trips'  => $d['total_trips'],
        'lat'    => isset($d['lat']) ? (float)$d['lat'] : null,
        'lng'    => isset($d['lng']) ? (float)$d['lng'] : null,
    ];
}
echo json_encode($driver_map_data);
?>;

function initDispatchMap() {
    // Default centre – change to your city coordinates
    var defaultCenter = { lat: 25.2048, lng: 55.2708 }; // Dubai – adjust as needed

    dispatchMap = new google.maps.Map(document.getElementById('dispatch-map'), {
        zoom: 12,
        center: defaultCenter,
        mapTypeControl: false,
        styles: [
            { featureType: 'poi', stylers: [{ visibility: 'off' }] }
        ]
    });

    plotDriverMarkers(DRIVER_DATA);
    startAutoRefresh();
}

function makeMarkerIcon(status) {
    return {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 12,
        fillColor: STATUS_COLOUR[status] || '#6c757d',
        fillOpacity: 0.95,
        strokeColor: '#ffffff',
        strokeWeight: 2
    };
}

function plotDriverMarkers(drivers) {
    drivers.forEach(function(d) {
        var pos = null;

        if (d.lat && d.lng) {
            pos = { lat: d.lat, lng: d.lng };
        }

        var infoContent =
            '<div style="min-width:180px">' +
            '<strong>' + d.name + '</strong><br>' +
            '<span class="badge" style="background:' + (STATUS_COLOUR[d.status]||'#999') + ';color:#fff;padding:2px 6px;border-radius:4px;font-size:11px">' + d.status.toUpperCase() + '</span><br>' +
            '<small>📞 ' + d.phone + '</small><br>' +
            (d.plate ? '<small>🚗 ' + d.vehicle + ' [' + d.plate + ']</small><br>' : '') +
            '<small>⭐ ' + d.rating + ' &nbsp;|&nbsp; ' + d.trips + ' trips</small>' +
            '</div>';

        var infoWindow = new google.maps.InfoWindow({ content: infoContent });

        if (pos) {
            if (driverMarkers[d.id]) {
                driverMarkers[d.id].marker.setPosition(pos);
                driverMarkers[d.id].marker.setIcon(makeMarkerIcon(d.status));
            } else {
                var marker = new google.maps.Marker({
                    position : pos,
                    map      : dispatchMap,
                    icon     : makeMarkerIcon(d.status),
                    title    : d.name,
                    animation: google.maps.Animation.DROP
                });
                marker.addListener('click', function() {
                    // Close any open info windows
                    Object.values(driverMarkers).forEach(function(dm){ dm.info.close(); });
                    infoWindow.open(dispatchMap, marker);
                });
                driverMarkers[d.id] = { marker: marker, info: infoWindow };
            }
        } else {
            // No location yet – show placeholder marker at city centre with grey icon
            if (!driverMarkers[d.id]) {
                var noLocContent = infoContent + '<br><small class="text-muted"><i>📍 Location not yet shared</i></small>';
                var noLocInfo = new google.maps.InfoWindow({ content: noLocContent });
                var faded = makeMarkerIcon('offline');
                faded.scale = 8;
                faded.fillOpacity = 0.35;
                var ghost = new google.maps.Marker({
                    position : { lat: 25.2048 + (Math.random()-0.5)*0.05, lng: 55.2708 + (Math.random()-0.5)*0.05 },
                    map      : dispatchMap,
                    icon     : faded,
                    title    : d.name + ' (no GPS)'
                });
                ghost.addListener('click', function() {
                    Object.values(driverMarkers).forEach(function(dm){ dm.info.close(); });
                    noLocInfo.open(dispatchMap, ghost);
                });
                driverMarkers[d.id] = { marker: ghost, info: noLocInfo };
            }
        }
    });

    updateTimestamp();
}

function refreshDriverMarkers() {
    fetch('dispatch_locations.php')
        .then(function(r){ return r.json(); })
        .then(function(data){ plotDriverMarkers(data); })
        .catch(function(){ updateTimestamp('Error refreshing'); });
}

function startAutoRefresh() {
    setInterval(refreshDriverMarkers, 30000); // every 30 s
}

function updateTimestamp(msg) {
    var el = document.getElementById('map-last-updated');
    if (el) el.textContent = msg || ('Updated: ' + new Date().toLocaleTimeString());
}
</script>

<!-- Load Google Maps – using API key from settings -->
<?php
$stmt_dk = $pdo->query("SELECT setting_value FROM td_settings WHERE setting_key = 'google_maps_js_key' LIMIT 1");
$dispatch_maps_key = $stmt_dk ? $stmt_dk->fetchColumn() : '';
if (!$dispatch_maps_key) $dispatch_maps_key = 'YOUR_GOOGLE_MAPS_API_KEY';
?>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($dispatch_maps_key); ?>&callback=initDispatchMap">
</script>

<?php require_once 'footer.php'; ?>
