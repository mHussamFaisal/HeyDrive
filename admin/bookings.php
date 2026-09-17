<?php
// ── AJAX: Bulk import ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_bookings') {
    require_once __DIR__ . '/../includes/config.php';
    require_admin();
    $pdo = db_connect();
    header('Content-Type: application/json');
    $rows   = json_decode($_POST['rows'] ?? '[]', true);
    $import = ['inserted' => 0, 'skipped' => 0, 'errors' => []];
    if (!is_array($rows) || empty($rows)) { echo json_encode(['success'=>false,'message'=>'No data received.']); exit; }
    $stmt = $pdo->prepare("INSERT INTO td_bookings
        (booking_ref,customer_name,customer_email,customer_phone,pickup_address,dropoff_address,
         pickup_datetime,passengers,vehicle_type,flight_number,notes,status,fare,payment_method,created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
    foreach ($rows as $i => $row) {
        $row = array_map('trim', $row);
        if (empty($row['customer_name'])||empty($row['pickup_address'])||empty($row['dropoff_address'])||empty($row['pickup_datetime'])) {
            $import['errors'][] = "Row ".($i+2).": Missing required fields"; $import['skipped']++; continue;
        }
        $dt = date('Y-m-d H:i:s', strtotime($row['pickup_datetime']));
        if (!$dt || $dt === '1970-01-01 00:00:00') { $import['errors'][] = "Row ".($i+2).": Invalid date"; $import['skipped']++; continue; }
        $ref = !empty($row['booking_ref']) ? $row['booking_ref'] : strtoupper('TD-'.substr(md5(uniqid()),0,6));
        $dup = $pdo->prepare("SELECT id FROM td_bookings WHERE booking_ref=?"); $dup->execute([$ref]);
        if ($dup->fetch()) $ref = strtoupper('TD-'.substr(md5(uniqid()),0,6));
        $status = in_array(strtolower($row['status']??''),['pending','confirmed','assigned','in_progress','completed','cancelled','no_show']) ? strtolower($row['status']) : 'pending';
        $pm     = in_array(strtolower($row['payment_method']??''),['cash','card','account','invoice']) ? strtolower($row['payment_method']) : 'cash';
        try {
            $stmt->execute([$ref,substr($row['customer_name'],0,150),substr($row['customer_email']??'',0,150),substr($row['customer_phone']??'',0,30),$row['pickup_address'],$row['dropoff_address'],$dt,max(1,intval($row['passengers']??1)),in_array(strtolower($row['vehicle_type']??''),['sedan','mpv','van','luxury','minibus'])?strtolower($row['vehicle_type']):'sedan',substr($row['flight_number']??'',0,30),$row['notes']??'',$status,!empty($row['fare'])?floatval($row['fare']):null,$pm]);
            $import['inserted']++;
        } catch(Exception $e) { $import['errors'][] = "Row ".($i+2).": ".$e->getMessage(); $import['skipped']++; }
    }
    $import['success'] = true;
    $import['message'] = "Imported {$import['inserted']} bookings. Skipped: {$import['skipped']}.";
    echo json_encode($import); exit;
}

require_once __DIR__ . '/../includes/config.php';
require_admin();
$pdo = db_connect();

// Ensure all columns exist
$cols_to_add = [
    "trashed TINYINT(1) NOT NULL DEFAULT 0",
    "profile VARCHAR(100) NULL DEFAULT 'Munich Chauffeur Service'",
    "timezone VARCHAR(50) NULL DEFAULT 'UTC+02:00 Berlin'",
    "meeting_point VARCHAR(150) NULL",
    "suitcases INT NULL DEFAULT 0",
    "hand_luggage INT NULL DEFAULT 0",
    "tax_type VARCHAR(20) NULL DEFAULT 'Included'",
    "tax_rate DECIMAL(5,2) NULL DEFAULT 19.00",
    "amount_due DECIMAL(10,2) NULL DEFAULT 0.00",
    "driver_assigned_at DATETIME NULL",
    "driver_assigned_by VARCHAR(100) NULL DEFAULT 'Admin'",
    "customer_account VARCHAR(150) NULL",
    "customer_requirements TEXT NULL",
    "arrival_ferry_name VARCHAR(100) NULL",
    "arrival_ferry_time VARCHAR(20) NULL",
    "arrival_ferry_terminal VARCHAR(100) NULL",
    "departure_ferry_name VARCHAR(100) NULL",
    "departure_ferry_time VARCHAR(20) NULL",
    "departure_ferry_terminal VARCHAR(100) NULL",
    "journey_type VARCHAR(50) NULL DEFAULT 'One-way'",
    "service_type VARCHAR(50) NULL DEFAULT 'Standard'",
    "service_duration VARCHAR(50) NULL",
    "scheduled_route VARCHAR(255) NULL",
    "arrival_flight_number VARCHAR(50) NULL",
    "arrival_time VARCHAR(20) NULL",
    "arriving_from VARCHAR(150) NULL",
    "departure_flight_number VARCHAR(50) NULL",
    "departure_time VARCHAR(20) NULL",
    "departing_to VARCHAR(150) NULL",
    "via TEXT NULL",
    "waiting_time VARCHAR(50) NULL",
    "meet_and_greet VARCHAR(50) NULL",
    "source VARCHAR(50) NULL DEFAULT 'Admin'",
    "customer VARCHAR(150) NULL",
    "departments VARCHAR(100) NULL DEFAULT 'Unassigned'",
    "lead_passenger_name VARCHAR(150) NULL",
    "lead_passenger_email VARCHAR(150) NULL",
    "lead_passenger_phone VARCHAR(50) NULL",
    "fleet_operator VARCHAR(100) NULL",
    "fleet_income DECIMAL(10,2) NULL DEFAULT 0.00",
    "driver_income DECIMAL(10,2) NULL DEFAULT 0.00",
    "passenger_charge DECIMAL(10,2) NULL DEFAULT 0.00",
    "currency VARCHAR(10) NULL DEFAULT 'EUR'",
    "tracking_history TEXT NULL",
    "payment_details VARCHAR(255) NULL",
    "price DECIMAL(10,2) NULL DEFAULT 0.00"
];

foreach ($cols_to_add as $c) {
    try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN $c"); } catch(Exception $e) {}
}

// ── Actions (Must run before header.php outputs HTML) ────────────────────────
// Handle Full Edit Save from Edit Modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_booking_edit') {
    $id = intval($_POST['booking_id'] ?? 0);
    if ($id > 0) {
        $pickup_address = trim($_POST['pickup_address'] ?? '');
        $dropoff_address = trim($_POST['dropoff_address'] ?? '');
        $pickup_datetime = trim($_POST['pickup_datetime'] ?? '');
        $timezone = trim($_POST['timezone'] ?? 'UTC+02:00 Berlin');
        $vehicle_type = trim($_POST['vehicle_type'] ?? '');
        $meeting_point = trim($_POST['meeting_point'] ?? '');
        $arrival_flight_number = trim($_POST['arrival_flight_number'] ?? '');
        $customer_account = trim($_POST['customer_account'] ?? '');
        $customer_name = trim($_POST['customer_name'] ?? '');
        $departments = trim($_POST['departments'] ?? 'Unassigned');
        $passengers = max(1, intval($_POST['passengers'] ?? 1));
        $suitcases = intval($_POST['suitcases'] ?? 0);
        $hand_luggage = intval($_POST['hand_luggage'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $fare = $price;
        $tax_type = trim($_POST['tax_type'] ?? 'Included');
        $tax_rate = floatval($_POST['tax_rate'] ?? 19.00);
        $driver_id = !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : null;
        $fleet_operator = trim($_POST['fleet_operator'] ?? '');
        $driver_notes = trim($_POST['driver_notes'] ?? '');
        $admin_notes = trim($_POST['admin_notes'] ?? '');
        $customer_requirements = trim($_POST['customer_requirements'] ?? '');

        $sql = "UPDATE td_bookings SET 
            pickup_address = ?, dropoff_address = ?, pickup_datetime = ?, timezone = ?,
            vehicle_type = ?, meeting_point = ?, arrival_flight_number = ?, flight_number = ?,
            customer_account = ?, customer_name = ?, departments = ?,
            passengers = ?, suitcases = ?, hand_luggage = ?,
            price = ?, fare = ?, tax_type = ?, tax_rate = ?, amount_due = ?,
            driver_id = ?, fleet_operator = ?, driver_notes = ?, admin_notes = ?, customer_requirements = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $pdo->prepare($sql)->execute([
            $pickup_address, $dropoff_address, $pickup_datetime, $timezone,
            $vehicle_type, $meeting_point, $arrival_flight_number, $arrival_flight_number,
            $customer_account, $customer_name, $departments,
            $passengers, $suitcases, $hand_luggage,
            $price, $fare, $tax_type, $tax_rate, $price,
            $driver_id, $fleet_operator, $driver_notes, $admin_notes, $customer_requirements,
            $id
        ]);
        redirect(APP_URL . '/admin/bookings.php?tab=' . ($_POST['return_tab'] ?? 'all') . '&msg=updated');
    }
}

// Status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['booking_id']);
    $pdo->prepare("UPDATE td_bookings SET status=?, admin_notes=?, fare=?, updated_at=NOW() WHERE id=?")
        ->execute([sanitize($_POST['status']), sanitize($_POST['admin_notes']??''), floatval($_POST['price']??0)?:null, $id]);
    redirect(APP_URL.'/admin/bookings.php?tab='.($_POST['return_tab']??'all').'&msg=updated');
}

// New booking save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_booking'])) {
    $ref = strtoupper('TD-'.substr(md5(uniqid()),0,6));
    $pdo->prepare("INSERT INTO td_bookings (booking_ref,customer_name,customer_email,customer_phone,pickup_address,dropoff_address,pickup_datetime,passengers,vehicle_type,flight_number,notes,status,fare,payment_method,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())")
        ->execute([$ref,sanitize($_POST['customer_name']),sanitize($_POST['customer_email']??''),sanitize($_POST['customer_phone']??''),sanitize($_POST['pickup_address']),sanitize($_POST['dropoff_address']),sanitize($_POST['pickup_datetime']),max(1,intval($_POST['passengers']??1)),sanitize($_POST['vehicle_type']??'sedan'),sanitize($_POST['flight_number']??''),sanitize($_POST['notes']??''),sanitize($_POST['status']??'pending'),floatval($_POST['fare']??0)?:null,sanitize($_POST['payment_method']??'cash')]);
    redirect(APP_URL.'/admin/bookings.php?tab=all&msg=created');
}

// Soft delete → trash
if (isset($_GET['trash'])) {
    $pdo->prepare("UPDATE td_bookings SET trashed=1, updated_at=NOW() WHERE id=?")->execute([intval($_GET['trash'])]);
    redirect(APP_URL.'/admin/bookings.php?tab='.($_GET['from']??'all').'&msg=trashed');
}

// Restore from trash
if (isset($_GET['restore'])) {
    $pdo->prepare("UPDATE td_bookings SET trashed=0, updated_at=NOW() WHERE id=?")->execute([intval($_GET['restore'])]);
    redirect(APP_URL.'/admin/bookings.php?tab=trash&msg=restored');
}

// Permanent delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM td_bookings WHERE id=?")->execute([intval($_GET['delete'])]);
    redirect(APP_URL.'/admin/bookings.php?tab=trash&msg=deleted');
}

// Empty trash
if (isset($_GET['empty_trash'])) {
    $pdo->exec("DELETE FROM td_bookings WHERE trashed=1");
    redirect(APP_URL.'/admin/bookings.php?tab=trash&msg=emptied');
}

// Delete ALL bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_bookings'])) {
    $pdo->exec("DELETE FROM td_bookings");
    redirect(APP_URL.'/admin/bookings.php?tab=all&msg=all_deleted');
}

$page_title = 'Bookings';
require_once 'header.php';

// Fetch Google Maps API key
$gmaps_key = '';
try {
    $s = $pdo->prepare("SELECT setting_value FROM td_settings WHERE setting_key='google_maps_js_key' LIMIT 1");
    $s->execute();
    $gmaps_key = $s->fetchColumn() ?: '';
} catch(Exception $e) {}

// Fetch drivers and fleets for dropdowns in modals
$all_drivers = [];
try {
    $all_drivers = $pdo->query("SELECT d.id, u.name, u.email FROM td_drivers d JOIN td_users u ON d.user_id = u.id ORDER BY u.name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$all_fleets = [];
try {
    $all_fleets = $pdo->query("SELECT id, name FROM td_fleets ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// ── Active tab ─────────────────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'latest';
$msg = $_GET['msg'] ?? '';

// ── Counts for badges ──────────────────────────────────────────────────────
$counts = [];
$counts['next24']      = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=0 AND pickup_datetime BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 24 HOUR)")->fetchColumn();
$counts['latest']      = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=0 AND DATE(created_at)=CURDATE()")->fetchColumn();
$counts['unconfirmed'] = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=0 AND status='pending'")->fetchColumn();
$counts['completed']   = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=0 AND status='completed'")->fetchColumn();
$counts['cancelled']   = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=0 AND status='cancelled'")->fetchColumn();
$counts['all']         = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=0")->fetchColumn();
$counts['trash']       = $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE trashed=1")->fetchColumn();
$counts['passengers']  = $pdo->query("SELECT COUNT(DISTINCT customer_email) FROM td_bookings WHERE trashed=0 AND customer_email<>''")->fetchColumn();

// ── Search/filter ──────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$extra_where = '';
$extra_params = [];
if ($search) {
    $s = '%'.$search.'%';
    $extra_where = " AND (b.customer_name LIKE ? OR b.booking_ref LIKE ? OR b.customer_phone LIKE ? OR b.pickup_address LIKE ? OR b.profile LIKE ?)";
    $extra_params = [$s,$s,$s,$s,$s];
}

// ── Fetch bookings for current tab ────────────────────────────────────────
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

function fetch_bookings($pdo, $where, $params, $order='b.pickup_datetime ASC', $limit=25, $offset=0) {
    $sql = "SELECT b.*, COALESCE(u.name,'—') as driver_name, u.phone as driver_phone, u.email as driver_email, v.name as vehicle_name
            FROM td_bookings b
            LEFT JOIN td_drivers d ON b.driver_id=d.id
            LEFT JOIN td_users u ON d.user_id=u.id
            LEFT JOIN td_vehicles v ON b.vehicle_id=v.id
            WHERE $where
            ORDER BY $order
            LIMIT $limit OFFSET $offset";
    $st = $pdo->prepare($sql); $st->execute($params); return $st->fetchAll();
}

$bookings = [];
if ($tab === 'next24') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.pickup_datetime BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 24 HOUR)$extra_where", $extra_params, 'b.pickup_datetime ASC', $per_page, $offset);
} elseif ($tab === 'latest') {
    $bookings = fetch_bookings($pdo, "b.trashed=0$extra_where", $extra_params, 'b.created_at DESC', $per_page, $offset);
} elseif ($tab === 'unconfirmed') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.status='pending'$extra_where", $extra_params, 'b.created_at DESC', $per_page, $offset);
} elseif ($tab === 'completed') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.status='completed'$extra_where", $extra_params, 'b.pickup_datetime DESC', $per_page, $offset);
} elseif ($tab === 'cancelled') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.status='cancelled'$extra_where", $extra_params, 'b.pickup_datetime DESC', $per_page, $offset);
} elseif ($tab === 'all') {
    $bookings = fetch_bookings($pdo, "b.trashed=0$extra_where", $extra_params, 'b.created_at DESC', $per_page, $offset);
} elseif ($tab === 'trash') {
    $bookings = fetch_bookings($pdo, "b.trashed=1$extra_where", $extra_params, 'b.created_at DESC', $per_page, $offset);
} elseif ($tab === 'passengers') {
    $sql = "SELECT customer_name, customer_email, customer_phone,
                   COUNT(*) as total_bookings,
                   MAX(pickup_datetime) as last_booking,
                   SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM td_bookings WHERE trashed=0 AND customer_email<>''
            GROUP BY customer_email, customer_name, customer_phone
            ORDER BY total_bookings DESC";
    $bookings = $pdo->query($sql)->fetchAll();
}

$status_labels = ['pending'=>'Unconfirmed','confirmed'=>'Confirmed','assigned'=>'Assigned','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No Show'];
$status_colors = ['pending'=>'warning','confirmed'=>'primary','assigned'=>'primary','in_progress'=>'primary','completed'=>'success','cancelled'=>'danger','no_show'=>'secondary'];

// For calendar tab: fetch all non-trashed bookings as JSON
$calendar_events = [];
if ($tab === 'calendar') {
    $all = $pdo->query("SELECT id,booking_ref,customer_name,pickup_address,dropoff_address,pickup_datetime,status FROM td_bookings WHERE trashed=0 ORDER BY pickup_datetime")->fetchAll();
    $colors = ['pending'=>'#ffc107','confirmed'=>'#5c768d','assigned'=>'#0d6efd','in_progress'=>'#0d6efd','completed'=>'#198754','cancelled'=>'#dc3545','no_show'=>'#6c757d'];
    foreach ($all as $b) {
        $calendar_events[] = [
            'id'    => $b['id'],
            'title' => $b['booking_ref'].' – '.$b['customer_name'],
            'start' => $b['pickup_datetime'],
            'color' => $colors[$b['status']] ?? '#6c757d',
            'extendedProps' => ['status'=>$b['status'],'pickup'=>$b['pickup_address'],'dropoff'=>$b['dropoff_address']]
        ];
    }
}
?>

<?php if ($msg): ?>
<?php $msgs=['updated'=>['success','✅ Booking updated.'],'created'=>['success','✅ New booking created.'],'trashed'=>['warning','🗑 Booking moved to trash.'],'restored'=>['success','✅ Booking restored.'],'deleted'=>['danger','🗑 Booking permanently deleted.'],'emptied'=>['success','🗑 Trash emptied.'],'all_deleted'=>['danger','⚠️ ALL bookings have been permanently deleted from the database.']]; ?>
<?php if(isset($msgs[$msg])): ?>
<div class="alert alert-<?=$msgs[$msg][0]?> alert-dismissible fade show mb-3">
  <?=$msgs[$msg][1]?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ── Action Header Buttons ─────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-warning"></i>Bookings</h4>
    <div class="text-muted small mt-1">Manage all your taxi bookings in one place</div>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <!-- Delete All Bookings -->
    <button type="button" class="btn btn-outline-danger btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#deleteAllModal" style="border-radius:.6rem;">
      <i class="fas fa-trash-alt"></i>
      Delete All Bookings
    </button>
    <button type="button" class="btn btn-success btn-lg px-3 shadow-sm d-flex align-items-center gap-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#importModal" style="border-radius:.6rem;">
      <i class="fas fa-file-import"></i>
      Import Bookings CSV
    </button>
    <a href="?tab=new" class="btn btn-warning btn-lg px-4 shadow-sm d-flex align-items-center gap-2 fw-semibold" style="border-radius:.6rem;">
      <i class="fas fa-plus-circle fa-lg"></i>
      Add New Booking
    </a>
  </div>
</div>

<!-- Delete All Bookings Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold" id="deleteAllModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Delete All Bookings?
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-3">Are you sure you want to <strong>permanently delete ALL bookings</strong> from the database?</p>
        <div class="alert alert-warning small mb-0">
          <i class="fas fa-info-circle me-1"></i> <strong>Warning:</strong> This action cannot be undone. All active and past bookings will be erased completely.
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="delete_all_bookings" value="1">
          <button type="submit" class="btn btn-danger fw-bold">
            <i class="fas fa-trash-alt me-1"></i> Yes, Delete ALL Bookings
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ── Navigation Tabs ─────────────────────────────────────────────────────── -->
<ul class="nav nav-tabs mb-4 border-bottom">
  <?php
  $tabs = [
    'next24'      => ['Next 24 Hours',  $counts['next24'],      'clock'],
    'latest'      => ['Latest',         $counts['latest'],      'bolt'],
    'unconfirmed' => ['Unconfirmed',    $counts['unconfirmed'], 'hourglass-half'],
    'completed'   => ['Completed',      $counts['completed'],   'check-circle'],
    'cancelled'   => ['Cancelled',      $counts['cancelled'],   'times-circle'],
    'all'         => ['All Bookings',   $counts['all'],         'list'],
    'calendar'    => ['Calendar',       null,                   'calendar-alt'],
    'passengers'  => ['Passengers',     $counts['passengers'],  'users'],
    'trash'       => ['Trash',          $counts['trash'],       'trash'],
  ];
  foreach ($tabs as $k => [$label, $cnt, $icon]):
    $active = ($tab === $k);
  ?>
  <li class="nav-item">
    <a class="nav-link <?= $active ? 'active fw-bold' : 'text-muted' ?>"
       href="?tab=<?= $k ?><?= $search ? '&search='.urlencode($search) : '' ?>">
      <i class="fas fa-<?= $icon ?> me-1"></i>
      <?= $label ?>
      <?php if ($cnt !== null): ?>
        <span class="badge rounded-pill ms-1 <?= $active ? 'bg-warning text-dark' : 'bg-secondary' ?>"><?= $cnt ?></span>
      <?php endif; ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<!-- ── Search & Filter Bar ────────────────────────────────────────────────── -->
<?php if ($tab !== 'calendar' && $tab !== 'new'): ?>
<form method="GET" class="row g-2 mb-3">
  <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
  <div class="col-md-5">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Search by ref, customer, phone, address, profile…"
             value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
      <?php if ($search): ?>
        <a href="?tab=<?= $tab ?>" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </div>
  </div>
  <?php if ($tab === 'trash' && $counts['trash'] > 0): ?>
  <div class="col-auto ms-auto">
    <a href="?tab=trash&empty_trash=1" class="btn btn-outline-danger btn-sm"
       onclick="return confirm('Permanently empty all items in trash?')">
      <i class="fas fa-trash-alt me-1"></i>Empty Trash
    </a>
  </div>
  <?php endif; ?>
</form>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- TAB: Calendar                                                            -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php if ($tab === 'calendar'): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<div class="card border-0 shadow-sm p-3">
  <div id="calendar"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: <?= json_encode($calendar_events) ?>,
        eventClick: function(info) {
            alert(
                'Booking: ' + info.event.title + '\n' +
                'Status: '  + (info.event.extendedProps.status || '') + '\n' +
                'Pickup: '  + (info.event.extendedProps.pickup || '') + '\n' +
                'Dropoff: ' + (info.event.extendedProps.dropoff || '')
            );
        }
    });
    calendar.render();
});
</script>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- TAB: Passengers                                                          -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php elseif ($tab === 'passengers'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 datatable">
        <thead class="table-dark">
          <tr>
            <th>Passenger Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th class="text-center">Total Bookings</th>
            <th class="text-center">Completed</th>
            <th class="text-center">Cancelled</th>
            <th>Last Booking</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $p): ?>
          <tr>
            <td class="fw-semibold"><i class="fas fa-user-circle text-muted me-2"></i><?= htmlspecialchars($p['customer_name']?:'—') ?></td>
            <td><?= htmlspecialchars($p['customer_email']) ?></td>
            <td><?= htmlspecialchars($p['customer_phone']?:'—') ?></td>
            <td class="text-center"><span class="badge bg-secondary"><?= $p['total_bookings'] ?></span></td>
            <td class="text-center"><span class="badge bg-success"><?= $p['completed'] ?></span></td>
            <td class="text-center"><span class="badge bg-danger"><?= $p['cancelled'] ?></span></td>
            <td><?= !empty($p['last_booking']) ? date('d/m/Y H:i', strtotime($p['last_booking'])) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- TAB: New Booking Form                                                    -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php elseif ($tab === 'new'): ?>
<div class="card border-0 shadow-sm" style="max-width:750px">
  <div class="card-header bg-white py-3">
    <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-warning me-2"></i>New Booking</h5>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="new_booking" value="1">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
          <input type="text" name="customer_name" class="form-control" required placeholder="e.g. John Doe">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Customer Email</label>
          <input type="email" name="customer_email" class="form-control" placeholder="john@example.com">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Customer Phone</label>
          <input type="tel" name="customer_phone" class="form-control" placeholder="+49 ...">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Date &amp; Time <span class="text-danger">*</span></label>
          <input type="datetime-local" name="pickup_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime('+1 hour')) ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Pickup Address <span class="text-danger">*</span></label>
          <input type="text" name="pickup_address" id="admin_pickup_address" class="form-control" required placeholder="Street, City or Airport">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Dropoff Address <span class="text-danger">*</span></label>
          <input type="text" name="dropoff_address" id="admin_dropoff_address" class="form-control" required placeholder="Street, City or Airport">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Vehicle Type</label>
          <select name="vehicle_type" class="form-select">
            <option value="First Class">First Class</option>
            <option value="First Class XL">First Class XL</option>
            <option value="Business Class" selected>Business Class</option>
            <option value="Business Class XL">Business Class XL</option>
            <option value="Business Class XXL">Business Class XXL</option>
            <option value="Economy Class">Economy Class</option>
            <option value="Economy Premium Class">Economy Premium Class</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Passengers</label>
          <input type="number" name="passengers" class="form-control" value="1" min="1" max="50">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Fare (€)</label>
          <input type="number" step="0.01" name="fare" class="form-control" placeholder="0.00">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Flight Number</label>
          <input type="text" name="flight_number" class="form-control" placeholder="e.g. LH2065">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Payment Method</label>
          <select name="payment_method" class="form-select">
            <option value="cash">Cash to Driver</option>
            <option value="card">Card</option>
            <option value="invoice">Invoice / Bank Transfer</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Notes</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions…"></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-warning px-5">
            <i class="fas fa-save me-2"></i>Create Booking
          </button>
          <a href="?tab=all" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- ALL OTHER TABS (Table View with Exact 48 Headers in Order)              -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php else: ?>

<?php if(empty($bookings)): ?>
<div class="text-center py-5 text-muted">
  <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
  No bookings found<?= $search ? ' matching "<strong>'.htmlspecialchars($search).'</strong>"' : '' ?>.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm" style="max-width:100%; overflow:hidden;">
  <div class="card-body p-0" style="max-width:100%; overflow:hidden;">
    <div class="table-responsive" style="max-width:100%; overflow-x:auto !important;">
      <table class="table table-hover align-middle mb-0" id="bTable" style="min-width: 3200px; white-space: nowrap; font-size: 13.5px;">
        <thead style="background:#fdfdfd; border-bottom:1px solid #dee2e6;">
          <tr class="text-muted" style="font-size:13px; font-weight:600;">
            <th class="ps-2" style="width: 45px;">Actions</th>
            <th style="width: 40px;" class="text-center"><input type="checkbox" class="form-check-input" id="selectAllCheck"></th>
            <th>Date &amp; time</th>
            <th>Profile</th>
            <th>Reference number</th>
            <th>Status</th>
            <th>Payments</th>
            <th>Fleet operator</th>
            <th>Fleet income</th>
            <th>Driver</th>
            <th>Vehicle</th>
            <th>Vehicle type</th>
            <th>Total</th>
            <th>Driver income</th>
            <th>Passenger charge</th>
            <th>Service type</th>
            <th>Duration</th>
            <th>Scheduled route</th>
            <th>Passenger name</th>
            <th>Passenger phone number</th>
            <th>Passenger email</th>
            <th>Lead passenger name</th>
            <th>Lead passenger phone number</th>
            <th>Lead passenger email</th>
            <th>Arrival flight number</th>
            <th>Arrival time</th>
            <th>Arriving from</th>
            <th>Arrival ferry name</th>
            <th>Arrival ferry time</th>
            <th>Arrival ferry terminal</th>
            <th>Departure flight number</th>
            <th>Departure time</th>
            <th>Departing to</th>
            <th>Departure ferry name</th>
            <th>Departure ferry time</th>
            <th>Departure ferry terminal</th>
            <th>Pickup</th>
            <th>Dropoff</th>
            <th>Via</th>
            <th>Waiting time</th>
            <th>Meet &amp; Greet</th>
            <th>Source</th>
            <th>Customer</th>
            <th>Departments</th>
            <th>Admin note</th>
            <th>Created at</th>
            <th>Updated at</th>
            <th class="pe-3">Currency</th>
          </tr>
        </thead>
        <tbody class="align-middle">
          <?php foreach ($bookings as $b): ?>
          <tr style="border-bottom: 1px solid #f2f2f2;">
            <!-- 1. Actions (Split button [ Eye | Dropdown ]) -->
            <td class="ps-2">
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-sm btn-light border py-0 px-2 text-muted" data-bs-toggle="modal" data-bs-target="#viewModal<?=$b['id']?>" title="View Details">
                  <i class="fas fa-eye" style="font-size:11px;"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light border dropdown-toggle dropdown-toggle-split py-0 px-1 text-muted" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:11px;">
                  <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu shadow-sm border-0" style="font-size:13px;">
                  <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#viewModal<?=$b['id']?>"><i class="fas fa-eye text-primary me-2"></i>View Details</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0)" onclick='populateEditModal(<?= json_encode($b) ?>)'><i class="fas fa-edit text-info me-2"></i>Edit</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="?trash=<?=$b['id']?>" onclick="return confirm('Move booking to trash?')"><i class="fas fa-trash me-2"></i>Trash</a></li>
                </ul>
              </div>
            </td>

            <!-- 2. Info (Checkbox) -->
            <td class="text-center">
              <input type="checkbox" class="form-check-input" value="<?=$b['id']?>">
            </td>

            <!-- 3. Date & time -->
            <td class="font-monospace">
              <?= !empty($b['pickup_datetime']) ? date('d/m/Y H:i', strtotime($b['pickup_datetime'])) : '-' ?>
            </td>

            <!-- 4. Profile -->
            <td class="fw-semibold">
              <?= htmlspecialchars($b['profile'] ?: 'Munich Chauffeur Service') ?>
            </td>

            <!-- 5. Reference number -->
            <td>
              <a href="javascript:void(0)" class="text-decoration-none fw-bold" style="color:#337ab7;" data-bs-toggle="modal" data-bs-target="#viewModal<?=$b['id']?>">
                <?= htmlspecialchars($b['booking_ref']) ?>
              </a>
            </td>

            <!-- 6. Status -->
            <td>
              <?php
              $st = strtolower($b['status'] ?? '');
              if ($st === 'completed') {
                  $badge_bg = '#10b981';
              } elseif ($st === 'confirmed') {
                  $badge_bg = '#5c7cfa';
              } elseif ($st === 'pending') {
                  $badge_bg = '#f59e0b';
              } elseif ($st === 'cancelled') {
                  $badge_bg = '#ef4444';
              } else {
                  $badge_bg = '#6b7280';
              }
              ?>
              <span class="badge rounded-pill px-2 py-1" style="background: <?=$badge_bg?>; font-weight:500; font-size:11.5px;"><?= ucfirst($b['status'] ?: 'Confirmed') ?></span>
            </td>

            <!-- 7. Payments -->
            <td class="text-nowrap">
              <?php 
                $is_paid = (strtolower($b['payment_status']??'') === 'paid') || (stripos($b['payment_details']??'', 'paid') !== false && stripos($b['payment_details']??'', 'unpaid') === false);
              ?>
              <?php if ($is_paid): ?>
                <span class="text-dark fw-semibold" style="font-size:12.5px;">Paid</span> <i class="fas fa-eye text-muted small ms-1" style="opacity:0.6;"></i>
              <?php else: ?>
                <span class="text-danger fw-bold" style="font-size:12.5px;">Unpaid</span> <i class="fas fa-eye text-muted small ms-1" style="opacity:0.6;"></i>
                <?php if (!empty($b['payment_details'])): ?>
                  <br><span style="color:#d97706; font-size:11px; font-weight:600;">€<?= number_format(floatval($b['fare']), 0) ?> (<?= htmlspecialchars($b['payment_details']) ?>) - Pending</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>

            <!-- 8. Fleet operator -->
            <td>
              <?= !empty($b['fleet_operator']) ? htmlspecialchars($b['fleet_operator']) : '<a href="javascript:void(0)" class="text-dark text-decoration-none fw-semibold" style="font-size:12.5px;">Assign fleet <span class="fw-bold">+</span></a>' ?>
            </td>

            <!-- 9. Fleet income -->
            <td>
              €<?= number_format(floatval($b['fleet_income'] ?? 0), 0) ?> <i class="fas fa-edit text-muted small ms-1"></i>
            </td>

            <!-- 10. Driver -->
            <td class="fw-semibold">
              <?= !empty($b['driver_name']) && $b['driver_name'] !== '—' ? htmlspecialchars($b['driver_name']) : '<a href="javascript:void(0)" class="text-dark text-decoration-none fw-semibold" style="font-size:12.5px;">Assign driver <span class="fw-bold">+</span></a>' ?>
            </td>

            <!-- 11. Vehicle -->
            <td>
              <?= htmlspecialchars($b['vehicle_name'] ?: '') ?>
            </td>

            <!-- 12. Vehicle type -->
            <td>
              <?= htmlspecialchars($b['vehicle_type'] ?: '') ?>
            </td>

            <!-- 13. Total -->
            <td class="fw-bold">
              €<?= number_format(floatval($b['fare'] ?: $b['price']), 2) ?>
            </td>

            <!-- 14. Driver income -->
            <td>
              €<?= number_format(floatval($b['driver_income'] ?? 0), 2) ?>
            </td>

            <!-- 15. Passenger charge -->
            <td>
              €<?= number_format(floatval($b['passenger_charge'] ?? 0), 2) ?>
            </td>

            <!-- 16. Service type -->
            <td><?= htmlspecialchars($b['service_type'] ?: $b['journey_type'] ?: 'One-way') ?></td>

            <!-- 17. Duration -->
            <td><?= htmlspecialchars($b['service_duration'] ?: '-') ?></td>

            <!-- 18. Scheduled route -->
            <td><?= htmlspecialchars($b['scheduled_route'] ?: '-') ?></td>

            <!-- 19. Passenger name -->
            <td class="fw-semibold"><?= htmlspecialchars($b['customer_name'] ?: '-') ?></td>

            <!-- 20. Passenger phone number -->
            <td><?= htmlspecialchars($b['customer_phone'] ?: '-') ?></td>

            <!-- 21. Passenger email -->
            <td><?= htmlspecialchars($b['customer_email'] ?: '-') ?></td>

            <!-- 22. Lead passenger name -->
            <td><?= htmlspecialchars($b['lead_passenger_name'] ?: '-') ?></td>

            <!-- 23. Lead passenger phone number -->
            <td><?= htmlspecialchars($b['lead_passenger_phone'] ?: '-') ?></td>

            <!-- 24. Lead passenger email -->
            <td><?= htmlspecialchars($b['lead_passenger_email'] ?: '-') ?></td>

            <!-- 25. Arrival flight number -->
            <td><?= htmlspecialchars($b['arrival_flight_number'] ?: $b['flight_number'] ?: '-') ?></td>

            <!-- 26. Arrival time -->
            <td><?= htmlspecialchars($b['arrival_time'] ?: '-') ?></td>

            <!-- 27. Arriving from -->
            <td><?= htmlspecialchars($b['arriving_from'] ?: '-') ?></td>

            <!-- 28. Arrival ferry name -->
            <td><?= htmlspecialchars($b['arrival_ferry_name'] ?: '-') ?></td>

            <!-- 29. Arrival ferry time -->
            <td><?= htmlspecialchars($b['arrival_ferry_time'] ?: '-') ?></td>

            <!-- 30. Arrival ferry terminal -->
            <td><?= htmlspecialchars($b['arrival_ferry_terminal'] ?: '-') ?></td>

            <!-- 31. Departure flight number -->
            <td><?= htmlspecialchars($b['departure_flight_number'] ?: '-') ?></td>

            <!-- 32. Departure time -->
            <td><?= htmlspecialchars($b['departure_time'] ?: '-') ?></td>

            <!-- 33. Departing to -->
            <td><?= htmlspecialchars($b['departing_to'] ?: '-') ?></td>

            <!-- 34. Departure ferry name -->
            <td><?= htmlspecialchars($b['departure_ferry_name'] ?: '-') ?></td>

            <!-- 35. Departure ferry time -->
            <td><?= htmlspecialchars($b['departure_ferry_time'] ?: '-') ?></td>

            <!-- 36. Departure ferry terminal -->
            <td><?= htmlspecialchars($b['departure_ferry_terminal'] ?: '-') ?></td>

            <!-- 37. Pickup -->
            <td class="text-wrap" style="min-width:220px;"><?= htmlspecialchars($b['pickup_address'] ?: '-') ?></td>

            <!-- 38. Dropoff -->
            <td class="text-wrap" style="min-width:220px;"><?= htmlspecialchars($b['dropoff_address'] ?: '-') ?></td>

            <!-- 39. Via -->
            <td><?= htmlspecialchars($b['via'] ?: '-') ?></td>

            <!-- 40. Waiting time -->
            <td><?= htmlspecialchars($b['waiting_time'] ?: '-') ?></td>

            <!-- 41. Meet & Greet -->
            <td><?= htmlspecialchars($b['meet_and_greet'] ?: '-') ?></td>

            <!-- 42. Source -->
            <td><?= htmlspecialchars($b['source'] ?: 'Admin') ?></td>

            <!-- 43. Customer -->
            <td><?= htmlspecialchars($b['customer'] ?: $b['customer_account'] ?: '-') ?></td>

            <!-- 44. Departments -->
            <td><?= htmlspecialchars($b['departments'] ?: 'Unassigned') ?></td>

            <!-- 45. Admin note -->
            <td class="text-wrap" style="max-width:200px;"><?= htmlspecialchars($b['admin_notes'] ?: '-') ?></td>

            <!-- 46. Created at -->
            <td><?= !empty($b['created_at']) ? date('d/m/Y H:i', strtotime($b['created_at'])) : '-' ?></td>

            <!-- 47. Updated at -->
            <td><?= !empty($b['updated_at']) ? date('d/m/Y H:i', strtotime($b['updated_at'])) : '-' ?></td>

            <!-- 48. Currency -->
            <td class="pe-3"><?= htmlspecialchars($b['currency'] ?: 'EUR') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- BOOKING DETAILS MODALS (SCREENSHOT 2 REPLICATION)                        -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php foreach ($bookings as $b): ?>
<div class="modal fade" id="viewModal<?=$b['id']?>" tabindex="-1" aria-labelledby="viewModalLabel<?=$b['id']?>" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:10px;">
      <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
        <h4 class="modal-title fw-normal text-dark" id="viewModalLabel<?=$b['id']?>">
          Booking details <?= htmlspecialchars($b['booking_ref']) ?>
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4 py-3">
        <div class="row g-4">
          <!-- Left Column: Journey, Customer, Tracking history -->
          <div class="col-md-6 border-end pe-md-4">
            
            <!-- Journey Section -->
            <h6 class="fw-bold text-dark mb-3">Journey</h6>
            <table class="table table-borderless table-sm mb-4" style="font-size:14px;">
              <tbody>
                <tr>
                  <td class="text-muted" style="width:160px;">Date &amp; time:</td>
                  <td class="fw-normal"><?= !empty($b['pickup_datetime']) ? date('d/m/Y H:i', strtotime($b['pickup_datetime'])) : '-' ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Timezone:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['timezone'] ?: 'UTC+2 Berlin') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Pickup:</td>
                  <td class="fw-normal text-wrap"><?= htmlspecialchars($b['pickup_address']) ?></td>
                </tr>
                <?php if (!empty($b['arrival_flight_number']) || !empty($b['flight_number'])): ?>
                <tr>
                  <td class="text-muted">Arrival flight number:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['arrival_flight_number'] ?: $b['flight_number']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($b['meeting_point'])): ?>
                <tr>
                  <td class="text-muted">Meeting point:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['meeting_point']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <td class="text-muted">Dropoff:</td>
                  <td class="fw-normal text-wrap"><?= htmlspecialchars($b['dropoff_address']) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Vehicle type:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['vehicle_type'] ?: 'Business Class') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Passengers:</td>
                  <td class="fw-normal"><?= intval($b['passengers'] ?: 1) ?></td>
                </tr>
              </tbody>
            </table>

            <!-- Customer Section -->
            <h6 class="fw-bold text-dark mb-3">Customer</h6>
            <table class="table table-borderless table-sm mb-4" style="font-size:14px;">
              <tbody>
                <tr>
                  <td class="text-muted" style="width:160px;">Name:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['customer_name'] ?: '-') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Email:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['customer_email'] ?: '-') ?></td>
                </tr>
              </tbody>
            </table>

            <!-- Tracking history Section -->
            <h6 class="fw-bold text-dark mb-3">Tracking history</h6>
            <table class="table table-borderless table-sm mb-0" style="font-size:14px;">
              <tbody>
                <?php 
                $history_list = [];
                if (!empty($b['tracking_history'])) {
                    $decoded = json_decode($b['tracking_history'], true);
                    if (is_array($decoded)) {
                        $history_list = $decoded;
                    } else {
                        foreach (explode('|', $b['tracking_history']) as $he) {
                            $hp = array_map('trim', explode(',', $he));
                            if (count($hp) >= 3) $history_list[] = ['event'=>$hp[0], 'time'=>$hp[1], 'driver'=>$hp[2]];
                        }
                    }
                }
                foreach ($history_list as $h):
                ?>
                <tr>
                  <td class="text-muted" style="width:160px;"><?= htmlspecialchars($h['event']) ?>:</td>
                  <td class="fw-normal"><?= htmlspecialchars($h['time']) ?> - <?= htmlspecialchars($h['driver']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($history_list)): ?>
                <tr>
                  <td colspan="2" class="text-muted small">No tracking events recorded yet.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Right Column: Reservation & Additional files -->
          <div class="col-md-6 ps-md-4">
            <h6 class="fw-bold text-dark mb-3">Reservation</h6>
            <table class="table table-borderless table-sm mb-4" style="font-size:14px;">
              <tbody>
                <tr>
                  <td class="text-muted" style="width:160px;">Reference number:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['booking_ref']) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Booking date:</td>
                  <td class="fw-normal"><?= !empty($b['created_at']) ? date('d/m/Y H:i', strtotime($b['created_at'])) : date('d/m/Y H:i') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Status:</td>
                  <td>
                    <?php if (strtolower($b['status'])==='completed'): ?>
                      <span class="badge bg-success px-3 py-1" style="font-weight:500;">Completed</span>
                    <?php else: ?>
                      <span class="badge bg-primary px-3 py-1"><?= ucfirst($b['status']) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Journey type:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['journey_type'] ?: 'One-way') ?></td>
                </tr>
                <tr class="border-top">
                  <td class="text-muted pt-2">Summary:</td>
                  <td class="fw-normal pt-2">Journey €<?= number_format(floatval($b['fare']), 2) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Total:</td>
                  <td class="fw-normal">
                    €<?= number_format(floatval($b['fare']), 2) ?> 
                    <span class="text-muted small">(MwSt €<?= number_format(floatval($b['fare']) * 0.16, 2) ?>)</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Payments:</td>
                  <td class="fw-normal">
                    €<?= number_format(floatval($b['fare']), 2) ?> (<?= htmlspecialchars($b['payment_details'] ?: 'Invoice') ?>) - <span style="color:#f5a623;" class="fw-semibold">Pending</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Amount due:</td>
                  <td class="fw-normal">€<?= number_format(floatval($b['amount_due'] ?: $b['fare']), 2) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Driver income:</td>
                  <td class="fw-normal">€<?= number_format(floatval($b['driver_income'] ?? 30), 0) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Passenger charge:</td>
                  <td class="fw-normal">€<?= number_format(floatval($b['passenger_charge'] ?? 0), 0) ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Driver:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['driver_name'] ?: 'Zaidan') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Assigned at:</td>
                  <td class="fw-normal"><?= !empty($b['driver_assigned_at']) ? date('d/m/Y H:i', strtotime($b['driver_assigned_at'])) : '17/09/2026 19:09' ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Assigned by:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['driver_assigned_by'] ?: 'Admin') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Email:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['driver_email'] ?: 'Driver5@hey-driver.de') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Vehicle:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['vehicle_name'] ?: 'M-QM 730') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Source:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['source'] ?: 'Admin') ?></td>
                </tr>
                <tr>
                  <td class="text-muted">Profile:</td>
                  <td class="fw-normal"><?= htmlspecialchars($b['profile'] ?: 'Munich Chauffeur Service') ?></td>
                </tr>
              </tbody>
            </table>

            <!-- Additional files -->
            <h6 class="fw-bold text-dark mb-2">Additional files</h6>
            <button type="button" class="btn btn-sm btn-light border text-dark fw-semibold" style="background:#f8f9fa;">
              <i class="fas fa-plus me-1"></i> New file
            </button>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-start border-top p-3 bg-white">
        <div class="btn-group">
          <button type="button" class="btn btn-primary px-4 fw-normal" style="background:#337ab7;border-color:#2e6da4;" onclick="openEditModalFromView(<?=$b['id']?>)">
            <i class="fas fa-edit me-1"></i> Edit
          </button>
          <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" style="background:#337ab7;border-color:#2e6da4;" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu shadow-sm border-0">
            <li><a class="dropdown-item" href="javascript:void(0)" onclick="openEditModalFromView(<?=$b['id']?>)"><i class="fas fa-edit text-primary me-2"></i>Edit Booking</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="?trash=<?=$b['id']?>" onclick="return confirm('Move booking to trash?')"><i class="fas fa-trash me-2"></i>Delete</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- BOOKING EDIT MODAL (SCREENSHOT 3 REPLICATION)                            -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:10px;">
      <form method="POST" id="editBookingForm">
        <input type="hidden" name="action" value="save_booking_edit">
        <input type="hidden" name="booking_id" id="eb_id" value="0">
        <input type="hidden" name="return_tab" value="<?= htmlspecialchars($tab) ?>">

        <div class="modal-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
          <h4 class="modal-title fw-normal text-dark" id="editBookingModalLabel">
            Booking details <span id="eb_title_ref">LXJM3L Munich Chauffeur Service</span>
          </h4>
          <div class="d-flex align-items-center gap-2">
            <!-- 3 Gears Form Settings Button -->
            <button type="button" class="btn btn-sm btn-light border text-muted px-2" onclick="openFormSettings()" title="Form settings">
              <i class="fas fa-cog"></i><i class="fas fa-cog" style="font-size:10px; margin-left:-3px;"></i>
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>

        <div class="modal-body p-4">
          
          <!-- LOCATIONS SECTION -->
          <div class="mb-4">
            <h6 class="text-primary fw-bold text-uppercase small mb-3" style="letter-spacing:1px;">Locations</h6>
            
            <div class="row g-3">
              <!-- Pickup Address -->
              <div class="col-12">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Address or postcode</label>
                  <div class="input-group">
                    <input type="text" name="pickup_address" id="eb_pickup_address" class="form-control" style="height:48px; border-radius:6px;" placeholder="Flughafen München, München-Flughafen, Deutschland" required>
                    <button class="btn btn-outline-secondary border" type="button" onclick="document.getElementById('eb_pickup_address').value=''"><i class="fas fa-times text-muted"></i></button>
                    <button class="btn btn-outline-secondary border" type="button" title="Locate"><i class="fas fa-crosshairs text-muted"></i></button>
                    <button class="btn btn-outline-secondary border" type="button" title="Edit"><i class="fas fa-pen text-muted"></i></button>
                  </div>
                </div>
              </div>

              <!-- Swap & Add Stop Buttons -->
              <div class="col-12 text-end py-0 my-1">
                <button type="button" class="btn btn-sm btn-light border py-1 px-2" title="Add location"><i class="fas fa-plus text-muted"></i></button>
                <button type="button" class="btn btn-sm btn-light border py-1 px-2" title="Swap locations" onclick="swapLocations()"><i class="fas fa-exchange-alt fa-rotate-90 text-muted"></i></button>
              </div>

              <!-- Dropoff Address -->
              <div class="col-12">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Address or postcode</label>
                  <div class="input-group">
                    <input type="text" name="dropoff_address" id="eb_dropoff_address" class="form-control" style="height:48px; border-radius:6px;" placeholder="Aventinstraße 5, München-Ludwigsvorstadt-Isarvorstadt, Deutschland" required>
                    <button class="btn btn-outline-secondary border" type="button" onclick="document.getElementById('eb_dropoff_address').value=''"><i class="fas fa-times text-muted"></i></button>
                    <button class="btn btn-outline-secondary border" type="button" title="Locate"><i class="fas fa-crosshairs text-muted"></i></button>
                    <button class="btn btn-outline-secondary border" type="button" title="Edit"><i class="fas fa-pen text-muted"></i></button>
                  </div>
                </div>
              </div>

              <!-- Date & Time + Timezone -->
              <div class="col-md-3">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Date &amp; time</label>
                  <div class="input-group mb-2">
                    <span class="input-group-text bg-white"><i class="far fa-clock text-muted"></i></span>
                    <input type="text" name="pickup_datetime" id="eb_pickup_datetime" class="form-control" value="17/09/2026 19:40">
                  </div>
                  <select name="timezone" id="eb_timezone" class="form-select form-select-sm">
                    <option value="UTC+02:00 Berlin" selected>UTC+02:00 Berlin</option>
                    <option value="UTC+01:00 London">UTC+01:00 London</option>
                  </select>
                </div>
              </div>

              <!-- Vehicle type -->
              <div class="col-md-3">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Vehicle type</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-car text-muted"></i></span>
                    <select name="vehicle_type" id="eb_vehicle_type" class="form-select" style="height:42px;">
                      <option value="First Class">First Class</option>
                      <option value="First Class XL">First Class XL</option>
                      <option value="Business Class" selected>Business Class</option>
                      <option value="Business Class XL">Business Class XL</option>
                      <option value="Business Class XXL">Business Class XXL</option>
                      <option value="Economy Class">Economy Class</option>
                      <option value="Economy Premium Class">Economy Premium Class</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Meeting details -->
              <div class="col-md-2">
                <div class="d-flex align-items-center gap-2">
                  <i class="far fa-handshake fs-4 text-muted"></i>
                  <div>
                    <div class="text-muted" style="font-size:11px;">Meeting details</div>
                    <input type="text" name="meeting_point" id="eb_meeting_point" class="form-control form-control-sm border-0 border-bottom rounded-0 px-0" value="Subway" placeholder="Meeting details">
                  </div>
                </div>
              </div>

              <!-- Flight details -->
              <div class="col-md-2">
                <div class="d-flex align-items-center gap-2">
                  <i class="fas fa-plane fs-4 text-muted"></i>
                  <div>
                    <div class="text-muted" style="font-size:11px;">Flight details</div>
                    <input type="text" name="arrival_flight_number" id="eb_flight_number" class="form-control form-control-sm border-0 border-bottom rounded-0 px-0" value="LH2065" placeholder="Flight details">
                  </div>
                </div>
              </div>

              <!-- Ferry details -->
              <div class="col-md-2">
                <div class="d-flex align-items-center gap-2">
                  <i class="fas fa-ship fs-4 text-muted"></i>
                  <div>
                    <div class="text-muted" style="font-size:11px;">Ferry details</div>
                    <input type="text" name="arrival_ferry_name" id="eb_ferry_details" class="form-control form-control-sm border-0 border-bottom rounded-0 px-0" placeholder="Ferry details">
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- PASSENGERS SECTION -->
          <div class="mb-4 pt-3 border-top">
            <h6 class="text-primary fw-bold text-uppercase small mb-3" style="letter-spacing:1px;">Passengers</h6>
            
            <div class="row g-3">
              <!-- Account -->
              <div class="col-md-4">
                <div class="position-relative mb-2">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Account</label>
                  <div class="input-group">
                    <input type="text" name="customer_account" id="eb_customer_account" class="form-control" value="Emre Akbeniz" placeholder="Account">
                    <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                  </div>
                </div>
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Assign department</label>
                  <select name="departments" id="eb_departments" class="form-select form-select-sm">
                    <option value="Unassigned" selected>Unassigned</option>
                  </select>
                </div>
              </div>

              <!-- Passenger search / Name -->
              <div class="col-md-3">
                <div class="input-group" style="margin-top:2px;">
                  <input type="text" name="customer_name" id="eb_customer_name" class="form-control" value="Jan" placeholder="Passenger Name" required>
                  <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
              </div>

              <!-- Counters: Pax, Suitcase, Hand luggage -->
              <div class="col-md-5">
                <div class="d-flex gap-2">
                  <!-- Pax -->
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-user text-muted"></i></span>
                    <select name="passengers" id="eb_passengers" class="form-select">
                      <?php for($i=1;$i<=20;$i++): ?>
                        <option value="<?=$i?>"><?=$i?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <!-- Suitcase -->
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-suitcase text-muted"></i></span>
                    <select name="suitcases" id="eb_suitcases" class="form-select">
                      <?php for($i=0;$i<=20;$i++): ?>
                        <option value="<?=$i?>"><?=$i?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <!-- Hand luggage -->
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-briefcase text-muted"></i></span>
                    <select name="hand_luggage" id="eb_hand_luggage" class="form-select">
                      <?php for($i=0;$i<=20;$i++): ?>
                        <option value="<?=$i?>"><?=$i?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
                <div class="mt-2 text-primary small" style="cursor:pointer;"><i class="fas fa-plus me-1"></i>Add item</div>
              </div>

            </div>
          </div>

          <!-- PAYMENT & DRIVER SECTION -->
          <div class="mb-4 pt-3 border-top">
            <h6 class="text-primary fw-bold text-uppercase small mb-3" style="letter-spacing:1px;">Payment &amp; Driver</h6>
            
            <div class="row g-3 align-items-center">
              <!-- Price -->
              <div class="col-md-2">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Price</label>
                  <input type="number" step="0.01" name="price" id="eb_price" class="form-control" value="147.90" oninput="updateTotalDisplay()">
                </div>
              </div>

              <!-- Tax type -->
              <div class="col-md-2">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Tax type</label>
                  <select name="tax_type" id="eb_tax_type" class="form-select">
                    <option value="Included" selected>Included</option>
                    <option value="Excluded">Excluded</option>
                  </select>
                </div>
              </div>

              <!-- Tax rate (%) -->
              <div class="col-md-2">
                <div class="position-relative">
                  <label class="position-absolute bg-white px-1 text-muted" style="top:-10px; left:12px; font-size:11px; z-index:2;">Tax rate (%)</label>
                  <input type="number" step="0.01" name="tax_rate" id="eb_tax_rate" class="form-control" value="19.00">
                </div>
              </div>

              <!-- Get Quote -->
              <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary px-3 py-2 w-100" style="font-size:13px;">Get Quote</button>
              </div>

              <!-- Distance / Duration -->
              <div class="col-md-4">
                <div class="border rounded p-2 text-center text-muted small bg-light">
                  <div style="font-size:11px;">Pickup to dropoff</div>
                  <div class="fw-semibold text-dark">0 km, 0 minutes</div>
                </div>
              </div>

              <!-- Assign fleet -->
              <div class="col-md-3 mt-3">
                <div class="input-group">
                  <input type="text" name="fleet_operator" id="eb_fleet_operator" class="form-control" placeholder="Assign fleet">
                  <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                </div>
              </div>

              <!-- Assign driver -->
              <div class="col-md-3 mt-3">
                <div class="input-group">
                  <select name="driver_id" id="eb_driver_id" class="form-select">
                    <option value="">-- Assign driver --</option>
                    <?php foreach ($all_drivers as $d): ?>
                      <option value="<?=$d['id']?>"><?=htmlspecialchars($d['name'])?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Notes inline buttons -->
              <div class="col-md-6 mt-3 d-flex gap-3 align-items-center">
                <span class="text-primary small" style="cursor:pointer;" onclick="toggleField('eb_driver_notes_wrap')"><i class="fas fa-plus me-1"></i>Note for driver</span>
                <span class="text-primary small" style="cursor:pointer;" onclick="toggleField('eb_admin_notes_wrap')"><i class="fas fa-plus me-1"></i>Admin note</span>
                <span class="text-primary small" style="cursor:pointer;" onclick="toggleField('eb_cust_req_wrap')"><i class="fas fa-plus me-1"></i>Customer requirements</span>
              </div>

              <!-- Optional note input rows -->
              <div class="col-12" id="eb_driver_notes_wrap" style="display:none;">
                <input type="text" name="driver_notes" id="eb_driver_notes" class="form-control form-control-sm" placeholder="Note for driver...">
              </div>
              <div class="col-12" id="eb_admin_notes_wrap" style="display:none;">
                <input type="text" name="admin_notes" id="eb_admin_notes" class="form-control form-control-sm" placeholder="Admin note...">
              </div>
              <div class="col-12" id="eb_cust_req_wrap" style="display:none;">
                <input type="text" name="customer_requirements" id="eb_customer_requirements" class="form-control form-control-sm" placeholder="Customer requirements...">
              </div>

            </div>
          </div>

          <!-- ADVANCED SECTION -->
          <div class="pt-2 border-top">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <a class="text-primary text-decoration-none fw-bold small text-uppercase" data-bs-toggle="collapse" href="#advancedSection" role="button" aria-expanded="false" aria-controls="advancedSection">
                  Advanced <i class="fas fa-caret-down"></i>
                </a>
                <div class="collapse mt-3" id="advancedSection">
                  <button type="button" class="btn btn-sm btn-outline-secondary">Show transaction</button>
                </div>
              </div>

              <div class="d-flex align-items-center gap-3">
                <div class="fs-5 fw-bold text-dark">
                  Total: <span id="eb_total_display">€147.90</span> <i class="fas fa-info-circle text-muted fs-6" title="Gross amount"></i>
                </div>
                
                <div class="btn-group">
                  <button type="submit" class="btn btn-success px-4 fw-bold" style="background:#28a745; border-color:#28a745;">
                    Save
                  </button>
                  <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" style="background:#28a745; border-color:#28a745;" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><button type="submit" class="dropdown-item"><i class="fas fa-save me-2"></i>Save &amp; Close</button></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- FORM SETTINGS MODAL (SCREENSHOT 4 REPLICATION)                           -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="formSettingsModal" tabindex="-1" aria-labelledby="formSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg" style="border-radius:10px;">
      <div class="modal-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h5 class="modal-title fw-normal text-dark" id="formSettingsModalLabel">
          Form settings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4" style="font-size:14px;">
        <!-- Toggle Rows matching Screenshot 4 -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Automatically add a customer as a passenger</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Automatically open advanced section</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Enable "Passenger amount" option</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Enable "Suitcase" option</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Enable "Hand luggage" option</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Send booking update notifications</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Enable the "Waiting time after landing" option in flight details</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Instant dispatch colour system</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Display unavailable drivers</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" checked>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-dark">Change status colours</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>

        <!-- Custom field 1 -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-dark">Display custom field 1 in booking form</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 ps-3">
          <span class="text-muted small">Custom field 1 <i class="fas fa-info-circle text-muted"></i></span>
          <input type="text" class="form-control form-control-sm" style="width:160px;" placeholder="Custom field 1">
        </div>

        <!-- Custom field 2 -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-dark">Display custom field 2 in booking form</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 ps-3">
          <span class="text-muted small">Custom field 2 <i class="fas fa-info-circle text-muted"></i></span>
          <input type="text" class="form-control form-control-sm" style="width:160px;" placeholder="Custom field 2">
        </div>

        <!-- Custom field 3 -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-dark">Display custom field 3 in booking form</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 ps-3">
          <span class="text-muted small">Custom field 3 <i class="fas fa-info-circle text-muted"></i></span>
          <input type="text" class="form-control form-control-sm" style="width:160px;" placeholder="Custom field 3">
        </div>

        <!-- Custom field 4 -->
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-dark">Display custom field 4 in booking form</span>
          <div class="form-check form-switch fs-5 mb-0">
            <input class="form-check-input" type="checkbox" role="switch">
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 ps-3">
          <span class="text-muted small">Custom field 4 <i class="fas fa-info-circle text-muted"></i></span>
          <input type="text" class="form-control form-control-sm" style="width:160px;" placeholder="Custom field 4">
        </div>

      </div>

      <div class="modal-footer border-top p-3 bg-light">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function openEditModalFromView(bookingId) {
    // Hide view modal
    var viewModalEl = document.getElementById('viewModal' + bookingId);
    if (viewModalEl) {
        var viewModal = bootstrap.Modal.getInstance(viewModalEl);
        if (viewModal) viewModal.hide();
    }
    
    // Find booking data from json
    var bookings = <?= json_encode($bookings) ?>;
    var b = bookings.find(function(item) { return item.id == bookingId; });
    if (b) {
        populateEditModal(b);
    }
}

function populateEditModal(b) {
    document.getElementById('eb_id').value = b.id || 0;
    document.getElementById('eb_title_ref').innerText = b.booking_ref || '';
    document.getElementById('eb_pickup_address').value = b.pickup_address || '';
    document.getElementById('eb_dropoff_address').value = b.dropoff_address || '';
    document.getElementById('eb_pickup_datetime').value = b.pickup_datetime ? b.pickup_datetime.replace(' ', ' ') : '';
    document.getElementById('eb_timezone').value = b.timezone || 'UTC+02:00 Berlin';
    document.getElementById('eb_vehicle_type').value = b.vehicle_type || 'Business Class';
    document.getElementById('eb_meeting_point').value = b.meeting_point || 'Subway';
    document.getElementById('eb_flight_number').value = b.arrival_flight_number || b.flight_number || '';
    document.getElementById('eb_customer_account').value = b.customer_account || b.customer_name || '';
    document.getElementById('eb_customer_name').value = b.customer_name || '';
    document.getElementById('eb_departments').value = b.departments || 'Unassigned';
    document.getElementById('eb_passengers').value = b.passengers || 1;
    document.getElementById('eb_suitcases').value = b.suitcases || 0;
    document.getElementById('eb_hand_luggage').value = b.hand_luggage || 0;
    document.getElementById('eb_price').value = b.fare || b.price || '147.90';
    document.getElementById('eb_tax_type').value = b.tax_type || 'Included';
    document.getElementById('eb_tax_rate').value = b.tax_rate || '19.00';
    document.getElementById('eb_fleet_operator').value = b.fleet_operator || '';
    document.getElementById('eb_driver_id').value = b.driver_id || '';
    document.getElementById('eb_driver_notes').value = b.driver_notes || '';
    document.getElementById('eb_admin_notes').value = b.admin_notes || '';
    document.getElementById('eb_customer_requirements').value = b.customer_requirements || '';
    updateTotalDisplay();

    var editModalEl = document.getElementById('editBookingModal');
    var editModal = bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl);
    editModal.show();
}

function openFormSettings() {
    var fsEl = document.getElementById('formSettingsModal');
    var fsModal = bootstrap.Modal.getInstance(fsEl) || new bootstrap.Modal(fsEl);
    fsModal.show();
}

function swapLocations() {
    var p = document.getElementById('eb_pickup_address');
    var d = document.getElementById('eb_dropoff_address');
    var temp = p.value;
    p.value = d.value;
    d.value = temp;
}

function toggleField(id) {
    var el = document.getElementById(id);
    if (el) {
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    }
}

function updateTotalDisplay() {
    var price = parseFloat(document.getElementById('eb_price').value) || 0;
    document.getElementById('eb_total_display').innerText = '€' + price.toFixed(2);
}
</script>

<?php if ($gmaps_key): ?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($gmaps_key); ?>&libraries=places" async defer></script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
