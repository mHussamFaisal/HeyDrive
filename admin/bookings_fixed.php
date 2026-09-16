<?php
// ── AJAX: Bulk import ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_bookings') {
    require_once '../includes/config.php';
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
        $pm     = in_array(strtolower($row['payment_method']??''),['cash','card','account']) ? strtolower($row['payment_method']) : 'cash';
        try {
            $stmt->execute([$ref,substr($row['customer_name'],0,150),substr($row['customer_email']??'',0,150),substr($row['customer_phone']??'',0,30),$row['pickup_address'],$row['dropoff_address'],$dt,max(1,intval($row['passengers']??1)),in_array(strtolower($row['vehicle_type']??''),['sedan','mpv','van','luxury','minibus'])?strtolower($row['vehicle_type']):'sedan',substr($row['flight_number']??'',0,30),$row['notes']??'',$status,!empty($row['fare'])?floatval($row['fare']):null,$pm]);
            $import['inserted']++;
        } catch(Exception $e) { $import['errors'][] = "Row ".($i+2).": ".$e->getMessage(); $import['skipped']++; }
    }
    $import['success'] = true;
    $import['message'] = "Imported {$import['inserted']} bookings. Skipped: {$import['skipped']}.";
    echo json_encode($import); exit;
}
?>
<?php
$page_title = 'Bookings';
require_once 'header.php';
$pdo = db_connect();

// Fetch Google Maps API key
$gmaps_key = '';
try {
    $s = $pdo->prepare("SELECT setting_value FROM td_settings WHERE setting_key='google_maps_js_key' LIMIT 1");
    $s->execute();
    $gmaps_key = $s->fetchColumn() ?: '';
} catch(Exception $e) {}

// Ensure trashed column exists
try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN trashed TINYINT(1) NOT NULL DEFAULT 0"); } catch(Exception $e) {}

// ── Actions ────────────────────────────────────────────────────────────────
// Status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['booking_id']);
    $pdo->prepare("UPDATE td_bookings SET status=?, admin_notes=?, fare=? WHERE id=?")
        ->execute([sanitize($_POST['status']), sanitize($_POST['admin_notes']??''), floatval($_POST['price']??0)?:null, $id]);
    redirect(APP_URL.'/admin/bookings.php?tab='.($_POST['return_tab']??'all').'&msg=updated');
}
// New booking save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_booking'])) {
    $ref = strtoupper('TD-'.substr(md5(uniqid()),0,6));
    $pdo->prepare("INSERT INTO td_bookings (booking_ref,customer_name,customer_email,customer_phone,pickup_address,dropoff_address,pickup_datetime,passengers,vehicle_type,flight_number,notes,status,fare,payment_method,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")
        ->execute([$ref,sanitize($_POST['customer_name']),sanitize($_POST['customer_email']??''),sanitize($_POST['customer_phone']??''),sanitize($_POST['pickup_address']),sanitize($_POST['dropoff_address']),sanitize($_POST['pickup_datetime']),max(1,intval($_POST['passengers']??1)),sanitize($_POST['vehicle_type']??'sedan'),sanitize($_POST['flight_number']??''),sanitize($_POST['notes']??''),sanitize($_POST['status']??'pending'),floatval($_POST['fare']??0)?:null,sanitize($_POST['payment_method']??'cash')]);
    redirect(APP_URL.'/admin/bookings.php?tab=all&msg=created');
}
// Soft delete → trash
if (isset($_GET['trash'])) {
    $pdo->prepare("UPDATE td_bookings SET trashed=1 WHERE id=?")->execute([intval($_GET['trash'])]);
    redirect(APP_URL.'/admin/bookings.php?tab='.($_GET['from']??'all').'&msg=trashed');
}
// Restore from trash
if (isset($_GET['restore'])) {
    $pdo->prepare("UPDATE td_bookings SET trashed=0 WHERE id=?")->execute([intval($_GET['restore'])]);
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
    $extra_where = " AND (b.customer_name LIKE ? OR b.booking_ref LIKE ? OR b.customer_phone LIKE ? OR b.pickup_address LIKE ?)";
    $extra_params = [$s,$s,$s,$s];
}

// ── Fetch bookings for current tab ────────────────────────────────────────
function fetch_bookings($pdo, $where, $params, $order='b.pickup_datetime ASC') {
    $sql = "SELECT b.*, COALESCE(u.name,'—') as driver_name
            FROM td_bookings b
            LEFT JOIN td_drivers d ON b.driver_id=d.id
            LEFT JOIN td_users u ON d.user_id=u.id
            WHERE $where
            ORDER BY $order";
    $st = $pdo->prepare($sql); $st->execute($params); return $st->fetchAll();
}

$bookings = [];
if ($tab === 'next24') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.pickup_datetime BETWEEN NOW() AND DATE_ADD(NOW(),INTERVAL 24 HOUR)$extra_where", $extra_params, 'b.pickup_datetime ASC');
} elseif ($tab === 'latest') {
    $bookings = fetch_bookings($pdo, "b.trashed=0$extra_where", $extra_params, 'b.created_at DESC');
} elseif ($tab === 'unconfirmed') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.status='pending'$extra_where", $extra_params, 'b.created_at DESC');
} elseif ($tab === 'completed') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.status='completed'$extra_where", $extra_params, 'b.pickup_datetime DESC');
} elseif ($tab === 'cancelled') {
    $bookings = fetch_bookings($pdo, "b.trashed=0 AND b.status='cancelled'$extra_where", $extra_params, 'b.pickup_datetime DESC');
} elseif ($tab === 'all') {
    $bookings = fetch_bookings($pdo, "b.trashed=0$extra_where", $extra_params, 'b.created_at DESC');
} elseif ($tab === 'trash') {
    $bookings = fetch_bookings($pdo, "b.trashed=1$extra_where", $extra_params, 'b.created_at DESC');
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
$status_colors = ['pending'=>'warning','confirmed'=>'info','assigned'=>'primary','in_progress'=>'primary','completed'=>'success','cancelled'=>'danger','no_show'=>'secondary'];

// For calendar tab: fetch all non-trashed bookings as JSON
$calendar_events = [];
if ($tab === 'calendar') {
    $all = $pdo->query("SELECT id,booking_ref,customer_name,pickup_address,dropoff_address,pickup_datetime,status FROM td_bookings WHERE trashed=0 ORDER BY pickup_datetime")->fetchAll();
    $colors = ['pending'=>'#ffc107','confirmed'=>'#0dcaf0','assigned'=>'#0d6efd','in_progress'=>'#0d6efd','completed'=>'#198754','cancelled'=>'#dc3545','no_show'=>'#6c757d'];
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
<?php $msgs=['updated'=>['success','✅ Booking updated.'],'created'=>['success','✅ New booking created.'],'trashed'=>['warning','🗑 Booking moved to trash.'],'restored'=>['success','✅ Booking restored.'],'deleted'=>['danger','🗑 Booking permanently deleted.'],'emptied'=>['success','🗑 Trash emptied.']]; ?>
<?php if(isset($msgs[$msg])): ?>
<div class="alert alert-<?=$msgs[$msg][0]?> alert-dismissible fade show mb-3">
  <?=$msgs[$msg][1]?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ── Add New Booking Button ─────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="fas fa-calendar-check me-2 text-warning"></i>Bookings</h4>
    <div class="text-muted small mt-1">Manage all your taxi bookings in one place</div>
  </div>
  <a href="?tab=new" class="btn btn-warning btn-lg px-4 shadow-sm d-flex align-items-center gap-2 fw-semibold" style="border-radius:.6rem;">
    <i class="fas fa-plus-circle fa-lg"></i>
    Add New Booking
  </a>
</div>

<!-- ── Tab Navigation ──────────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap align-items-center gap-1 mb-4 border-bottom pb-3">
  <?php
  $tabs = [
    'next24'      => ['icon'=>'fa-clock',          'label'=>'Next 24h',    'color'=>'#ffc107'],
    'latest'      => ['icon'=>'fa-bolt',            'label'=>'Latest',      'color'=>'#0dcaf0'],
    'unconfirmed' => ['icon'=>'fa-hourglass-half',  'label'=>'Unconfirmed', 'color'=>'#fd7e14'],
    'completed'   => ['icon'=>'fa-check-circle',    'label'=>'Completed',   'color'=>'#198754'],
    'cancelled'   => ['icon'=>'fa-times-circle',    'label'=>'Cancelled',   'color'=>'#dc3545'],
    'all'         => ['icon'=>'fa-list',             'label'=>'All',         'color'=>'#0d6efd'],
    'trash'       => ['icon'=>'fa-trash-alt',        'label'=>'Trash',       'color'=>'#6c757d'],
    'calendar'    => ['icon'=>'fa-calendar-alt',     'label'=>'Calendar',    'color'=>'#6610f2'],
    'passengers'  => ['icon'=>'fa-users',            'label'=>'Passengers',  'color'=>'#20c997'],
    'new'         => ['icon'=>'fa-plus-circle',      'label'=>'New Booking', 'color'=>'#198754'],
  ];
  foreach ($tabs as $t => $info):
    $active = ($tab === $t);
    $badge_tabs = ['next24','latest','unconfirmed','completed','cancelled','all','trash','passengers'];
    $count = in_array($t, array_keys($counts)) ? ($counts[$t] ?? null) : null;
  ?>
  <a href="?tab=<?=$t?>"
     class="btn btn-sm d-flex align-items-center gap-2 <?= $active ? 'btn-dark' : 'btn-outline-secondary' ?>"
     style="<?= $active ? 'border-color:'.$info['color'].';background:'.$info['color'].';color:'.($t==='latest'||$t==='next24'||$t==='passengers'||$t==='calendar'?'#000':'#fff').';' : '' ?>">
    <i class="fas <?=$info['icon']?>"></i>
    <?=$info['label']?>
    <?php if ($count !== null && $count > 0): ?>
    <span class="badge rounded-pill <?= $active?'bg-white text-dark':'bg-secondary' ?>"><?=$count?></span>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>

  <!-- Search (not shown on calendar/passengers/new) -->
  <?php if (!in_array($tab,['calendar','passengers','new'])): ?>
  <form method="GET" class="ms-auto d-flex gap-2">
    <input type="hidden" name="tab" value="<?=htmlspecialchars($tab)?>">
    <input type="text" name="search" class="form-control form-control-sm" style="width:200px"
           placeholder="Search name, ref, phone…" value="<?=htmlspecialchars($search)?>">
    <button class="btn btn-sm btn-warning"><i class="fas fa-search"></i></button>
    <?php if($search): ?><a href="?tab=<?=htmlspecialchars($tab)?>" class="btn btn-sm btn-outline-secondary">✕</a><?php endif; ?>
  </form>
  <?php endif; ?>

  <!-- Action buttons (not on calendar/passengers/new/trash) -->
  <?php if (!in_array($tab,['calendar','new'])): ?>
  <div class="d-flex gap-2 <?= in_array($tab,['calendar','passengers','new'])?'':'ms-2' ?>">
    <?php if($tab==='trash' && $counts['trash']>0): ?>
    <a href="?empty_trash=1" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete ALL trashed bookings?')">
      <i class="fas fa-fire me-1"></i>Empty Trash
    </a>
    <?php endif; ?>
    <?php if($tab!=='trash' && $tab!=='passengers'): ?>
    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
      <i class="fas fa-file-import me-1"></i>Import
    </button>
    <a href="?tab=<?=$tab?>&export=csv<?=$search?'&search='.urlencode($search):''?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-download me-1"></i>Export CSV
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php
// ── CSV export ─────────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export']==='csv' && $tab!=='calendar' && $tab!=='passengers' && $tab!=='new') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bookings_'.$tab.'_'.date('Ymd').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['ID','Ref','Name','Email','Phone','Pickup','Dropoff','DateTime','Passengers','Vehicle','Status','Fare','Payment','Driver','Created']);
    foreach ($bookings as $b) fputcsv($out,[$b['id'],$b['booking_ref'],$b['customer_name'],$b['customer_email'],$b['customer_phone'],$b['pickup_address'],$b['dropoff_address'],$b['pickup_datetime'],$b['passengers'],$b['vehicle_type'],$b['status'],$b['fare'],$b['payment_method'],$b['driver_name'],$b['created_at']]);
    fclose($out); exit;
}
?>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- CALENDAR TAB                                                            -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php if ($tab === 'calendar'): ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<div id="bookings-calendar" style="background:#fff;padding:1.5rem;border-radius:.5rem;box-shadow:0 1px 6px rgba(0,0,0,.08)"></div>
<div class="mt-3 d-flex flex-wrap gap-3 small">
  <?php foreach(['pending'=>['#ffc107','Unconfirmed'],'confirmed'=>['#0dcaf0','Confirmed'],'assigned'=>['#0d6efd','Assigned'],'completed'=>['#198754','Completed'],'cancelled'=>['#dc3545','Cancelled']] as $k=>[$c,$l]): ?>
  <span class="d-flex align-items-center gap-1"><span style="width:12px;height:12px;border-radius:3px;background:<?=$c?>;display:inline-block"></span><?=$l?></span>
  <?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var cal = new FullCalendar.Calendar(document.getElementById('bookings-calendar'), {
    initialView: 'dayGridMonth',
    headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
    events: <?= json_encode(array_values($calendar_events)) ?>,
    eventClick: function(info) {
      var p = info.event.extendedProps;
      alert('Booking: ' + info.event.title + '\nStatus: ' + p.status + '\nPickup: ' + p.pickup + '\nDropoff: ' + p.dropoff);
    },
    height: 'auto',
    nowIndicator: true,
  });
  cal.render();
});
</script>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- PASSENGERS TAB                                                          -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php elseif ($tab === 'passengers'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover align-middle mb-0" id="passTable">
      <thead class="table-dark">
        <tr>
          <th class="ps-3">Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th class="text-center">Bookings</th>
          <th class="text-center">Completed</th>
          <th class="text-center">Cancelled</th>
          <th>Last Booking</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($bookings)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted">No passenger records found.</td></tr>
        <?php endif; ?>
        <?php foreach($bookings as $p): ?>
        <tr>
          <td class="ps-3 fw-semibold"><?=htmlspecialchars($p['customer_name'])?></td>
          <td><?=htmlspecialchars($p['customer_email'])?></td>
          <td><?=htmlspecialchars($p['customer_phone']??'—')?></td>
          <td class="text-center"><span class="badge bg-primary"><?=$p['total_bookings']?></span></td>
          <td class="text-center"><span class="badge bg-success"><?=$p['completed']?></span></td>
          <td class="text-center"><span class="badge bg-danger"><?=$p['cancelled']?></span></td>
          <td><?= $p['last_booking'] ? date('d M Y H:i', strtotime($p['last_booking'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  if(typeof $.fn!=='undefined' && typeof $.fn.DataTable!=='undefined'){
    $('#passTable').DataTable({pageLength:25,order:[[3,'desc']]});
  }
});
</script>

<!-- ════════════════════════════════════════════════════════════════════════ -->
<!-- NEW BOOKING TAB                                                         -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php elseif ($tab === 'new'): ?>
<div class="card border-0 shadow-sm" style="max-width:860px">
  <div class="card-header bg-white fw-bold"><i class="fas fa-plus-circle me-2 text-warning"></i>Create New Booking</div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="new_booking" value="1">
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
          <input type="text" name="customer_name" class="form-control" required placeholder="John Smith">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Phone</label>
          <input type="text" name="customer_phone" class="form-control" placeholder="+44 7700 900123">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="customer_email" class="form-control" placeholder="john@email.com">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Pickup Address <span class="text-danger">*</span></label>
          <input type="text" id="admin_pickup_address" name="pickup_address" class="form-control" autocomplete="off" required placeholder="Heathrow Terminal 2, London">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Dropoff Address <span class="text-danger">*</span></label>
          <input type="text" id="admin_dropoff_address" name="dropoff_address" class="form-control" autocomplete="off" required placeholder="45 Baker Street, London W1U 7BJ">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Pickup Date &amp; Time <span class="text-danger">*</span></label>
          <input type="datetime-local" name="pickup_datetime" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Passengers</label>
          <input type="number" name="passengers" class="form-control" value="1" min="1" max="20">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Vehicle Type</label>
          <select name="vehicle_type" class="form-select">
            <option value="sedan">Sedan</option>
            <option value="mpv">MPV</option>
            <option value="van">Van</option>
            <option value="luxury">Luxury</option>
            <option value="minibus">Minibus</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Flight Number</label>
          <input type="text" name="flight_number" class="form-control" placeholder="BA123">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Fare (<?= defined('CURRENCY_SYMBOL')?CURRENCY_SYMBOL:'$' ?>)</label>
          <input type="number" name="fare" step="0.01" class="form-control" placeholder="0.00">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Payment Method</label>
          <select name="payment_method" class="form-select">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="account">Account</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
            <option value="pending">Unconfirmed</option>
            <option value="confirmed">Confirmed</option>
            <option value="assigned">Assigned</option>
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
<!-- ALL OTHER TABS (table view)                                             -->
<!-- ════════════════════════════════════════════════════════════════════════ -->
<?php else: ?>

<?php if(empty($bookings)): ?>
<div class="text-center py-5 text-muted">
  <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
  No bookings found<?= $search ? ' matching "<strong>'.htmlspecialchars($search).'</strong>"' : '' ?>.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="bTable">
        <thead class="table-dark">
          <tr>
            <th class="ps-2" style="width:50px"></th>
            <th class="ps-3">Ref</th>
            <th>Customer</th>
            <th>Pickup</th>
            <th>Date &amp; Time</th>
            <th class="text-center">Pax</th>
            <th>Driver</th>
            <th>Status</th>
            <th>Fare</th>
            <th class="text-end pe-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
          <tr>
            <td class="text-center ps-2">
              <button class="btn btn-sm btn-outline-info" title="View Details" data-bs-toggle="modal" data-bs-target="#viewModal<?=$b['id']?>"><i class="fas fa-eye"></i></button>
            </td>
            <td class="ps-2">
              <span class="badge bg-dark font-monospace"><?=htmlspecialchars($b['booking_ref'])?></span>
            </td>
            <td>
              <div class="fw-semibold"><?=htmlspecialchars($b['customer_name'])?></div>
              <div class="small text-muted"><?=htmlspecialchars($b['customer_phone']??'')?></div>
            </td>
            <td>
              <div class="small"><?=htmlspecialchars(substr($b['pickup_address'],0,35)).(strlen($b['pickup_address'])>35?'…':'')?></div>
              <div class="small text-muted">→ <?=htmlspecialchars(substr($b['dropoff_address'],0,30)).(strlen($b['dropoff_address'])>30?'…':'')?></div>
            </td>
            <td>
              <div><?=date('d M Y', strtotime($b['pickup_datetime']))?></div>
              <div class="small text-muted"><?=date('H:i', strtotime($b['pickup_datetime']))?></div>
            </td>
            <td class="text-center"><i class="fas fa-user text-muted me-1"></i><?=$b['passengers']?></td>
            <td class="small"><?=htmlspecialchars($b['driver_name'])?></td>
            <td>
              <span class="badge bg-<?=$status_colors[$b['status']]??'secondary'?>"><?=$status_labels[$b['status']]??ucfirst($b['status'])?></span>
            </td>
            <td><?= $b['fare'] ? (defined('CURRENCY_SYMBOL')?CURRENCY_SYMBOL:'$').number_format($b['fare'],2) : '<span class="text-muted">—</span>' ?></td>
            <td class="text-end pe-3">
              <?php if($tab==='trash'): ?>
                <a href="?restore=<?=$b['id']?>" class="btn btn-sm btn-outline-success" title="Restore"><i class="fas fa-undo"></i></a>
                <a href="?delete=<?=$b['id']?>" class="btn btn-sm btn-outline-danger" title="Delete Forever" onclick="return confirm('Permanently delete this booking?')"><i class="fas fa-times"></i></a>
              <?php else: ?>
                <a href="booking_edit.php?id=<?=$b['id']?>" class="btn btn-sm btn-outline-warning" title="Edit Booking"><i class="fas fa-edit"></i></a>
                <a href="?trash=<?=$b['id']?>&from=<?=$tab?>" class="btn btn-sm btn-outline-secondary" title="Move to Trash" onclick="return confirm('Move to trash?')"><i class="fas fa-trash"></i></a>
              <?php endif; ?>
            </td>
          </tr>

          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  

  <div class="card-footer bg-white text-muted small d-flex justify-content-between">
    <span>Showing <strong><?=count($bookings)?></strong> booking<?=count($bookings)!=1?'s':''?></span>
    <?php if($search): ?><span>Filtered by: "<strong><?=htmlspecialchars($search)?></strong>"</span><?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ── Import Modal (unchanged) ───────────────────────────────────────────── -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Bookings — CSV / Excel</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="step-upload">
          <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
            <i class="fas fa-info-circle fa-2x"></i>
            <div><strong>Before uploading:</strong> Download our CSV template so your file has the correct column headers.<br>
              <button class="btn btn-sm btn-outline-primary mt-1" onclick="downloadTemplate()"><i class="fas fa-download me-1"></i>Download CSV Template</button>
            </div>
          </div>
          <div id="dropzone" class="border border-2 border-dashed rounded-3 p-5 text-center bg-light" style="border-color:#198754!important;cursor:pointer;" onclick="document.getElementById('fileInput').click()" ondragover="event.preventDefault();this.classList.add('bg-success','bg-opacity-10')" ondragleave="this.classList.remove('bg-success','bg-opacity-10')" ondrop="handleDrop(event)">
            <i class="fas fa-cloud-upload-alt fa-3x text-success mb-3 d-block"></i>
            <h5>Drag &amp; Drop your CSV or Excel file here</h5>
            <p class="text-muted">or click to browse</p>
            <p class="small text-muted">Supported: <strong>.csv</strong> &nbsp;|&nbsp; <strong>.xlsx</strong> &nbsp;|&nbsp; <strong>.xls</strong></p>
            <input type="file" id="fileInput" accept=".csv,.xlsx,.xls" style="display:none" onchange="handleFileSelect(this.files[0])">
          </div>
          <div id="file-info" class="mt-2 text-muted small"></div>
        </div>
        <div id="step-preview" style="display:none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="fas fa-table me-2 text-success"></i>Preview — <span id="preview-count">0</span> rows</h6>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetImport()"><i class="fas fa-redo me-1"></i>Choose Different File</button>
          </div>
          <div id="preview-errors" class="alert alert-warning d-none"></div>
          <div class="table-responsive" style="max-height:300px;overflow-y:auto">
            <table class="table table-sm table-bordered table-striped small" id="preview-table">
              <thead class="table-dark" id="preview-head"></thead>
              <tbody id="preview-body"></tbody>
            </table>
          </div>
        </div>
        <div id="step-result" style="display:none">
          <div class="text-center py-4">
            <div id="result-icon" class="mb-3"></div>
            <h4 id="result-message"></h4>
            <div id="result-details" class="text-muted"></div>
            <div id="result-errors" class="alert alert-warning text-start mt-3 d-none small" style="max-height:200px;overflow-y:auto"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success d-none" id="btn-import" onclick="runImport()">
          <i class="fas fa-check me-1"></i>Import <span id="btn-import-count">0</span> Bookings
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
let parsedRows=[];
function downloadTemplate(){const h=["booking_ref","customer_name","customer_email","customer_phone","pickup_address","dropoff_address","pickup_datetime","passengers","vehicle_type","flight_number","status","fare","payment_method","notes"],e=["","John Smith","john@example.com","+44 7700 900123","Heathrow Terminal 2, London","45 Baker Street, London W1U 7BJ","2025-06-15 14:30","1","sedan","BA456","confirmed","45.00","cash","Meet at arrivals"];const csv=[h.join(","),e.join(",")].join("\n");const a=document.createElement("a");a.href="data:text/csv;charset=utf-8,"+encodeURIComponent(csv);a.download="bookings_template.csv";a.click();}
function handleDrop(e){e.preventDefault();document.getElementById("dropzone").classList.remove("bg-success","bg-opacity-10");const f=e.dataTransfer.files[0];if(f)handleFileSelect(f);}
function handleFileSelect(file){if(!file)return;const ext=file.name.split(".").pop().toLowerCase();document.getElementById("file-info").textContent="📄 "+file.name+" ("+(file.size/1024).toFixed(1)+" KB)";if(ext==="csv"){const r=new FileReader();r.onload=e=>parseCSV(e.target.result);r.readAsText(file);}else if(ext==="xlsx"||ext==="xls"){const r=new FileReader();r.onload=e=>parseExcel(e.target.result);r.readAsArrayBuffer(file);}else{alert("Unsupported format.");}}
function parseCSV(text){const lines=text.trim().split("\n");if(lines.length<2){alert("CSV empty.");return;}const headers=lines[0].split(",").map(h=>h.trim().replace(/"/g,"").toLowerCase());const rows=[];for(let i=1;i<lines.length;i++){const vals=splitCSVLine(lines[i]);if(vals.every(v=>!v.trim()))continue;const obj={};headers.forEach((h,j)=>obj[h]=(vals[j]||"").replace(/^"|"$/g,"").trim());rows.push(obj);}parsedRows=rows;showPreview(headers,rows);}
function parseExcel(buf){const wb=XLSX.read(buf,{type:"array"});const ws=wb.Sheets[wb.SheetNames[0]];const json=XLSX.utils.sheet_to_json(ws,{defval:""});if(!json.length){alert("Sheet empty.");return;}parsedRows=json.map(r=>{const obj={};Object.keys(r).forEach(k=>obj[k.toLowerCase().trim()]=String(r[k]).trim());return obj;});showPreview(Object.keys(parsedRows[0]),parsedRows);}
function splitCSVLine(line){const re=/(?:,|\n|^)("(?:(?:"")*[^"]*)*"|[^",\n]*|(?:\n|$))/g;const fields=[];let m;line=","+line;while((m=re.exec(line))!==null)fields.push(m[1]);return fields;}
function showPreview(headers,rows){document.getElementById("step-upload").style.display="none";document.getElementById("step-preview").style.display="";document.getElementById("preview-count").textContent=rows.length;document.getElementById("btn-import").classList.remove("d-none");document.getElementById("btn-import-count").textContent=rows.length;const required=["customer_name","pickup_address","dropoff_address","pickup_datetime"];const missing=required.filter(r=>!headers.includes(r));if(missing.length){const d=document.getElementById("preview-errors");d.classList.remove("d-none");d.innerHTML="⚠️ <strong>Missing required columns:</strong> "+missing.map(m=>"<code>"+m+"</code>").join(", ");document.getElementById("btn-import").disabled=true;}const head=document.getElementById("preview-head");head.innerHTML="<tr>"+headers.map(h=>"<th>"+h+"</th>").join("")+"</tr>";const body=document.getElementById("preview-body");body.innerHTML=rows.slice(0,10).map(r=>"<tr>"+headers.map(h=>"<td>"+(r[h]||"")+"</td>").join("")+"</tr>").join("");if(rows.length>10)body.innerHTML+=`<tr><td colspan="${headers.length}" class="text-center text-muted fst-italic">... and ${rows.length-10} more rows</td></tr>`;}
function resetImport(){parsedRows=[];document.getElementById("step-upload").style.display="";document.getElementById("step-preview").style.display="none";document.getElementById("step-result").style.display="none";document.getElementById("btn-import").classList.add("d-none");document.getElementById("preview-errors").classList.add("d-none");document.getElementById("fileInput").value="";document.getElementById("file-info").textContent="";}
function runImport(){const btn=document.getElementById("btn-import");btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Importing…';fetch("bookings.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=import_bookings&rows="+encodeURIComponent(JSON.stringify(parsedRows))}).then(r=>r.json()).then(data=>{document.getElementById("step-preview").style.display="none";document.getElementById("step-result").style.display="";btn.classList.add("d-none");if(data.success){document.getElementById("result-icon").innerHTML='<i class="fas fa-check-circle fa-4x text-success"></i>';document.getElementById("result-message").textContent=data.message;document.getElementById("result-details").textContent="Inserted: "+data.inserted+" | Skipped: "+data.skipped;if(data.errors&&data.errors.length){const d=document.getElementById("result-errors");d.classList.remove("d-none");d.innerHTML="<strong>Skipped rows:</strong><ul class='mb-0'>"+data.errors.map(e=>"<li>"+e+"</li>").join("")+"</ul>";}if(data.inserted>0)setTimeout(()=>location.reload(),2200);}else{document.getElementById("result-icon").innerHTML='<i class="fas fa-times-circle fa-4x text-danger"></i>';document.getElementById("result-message").textContent=data.message||"Import failed.";}}).catch(err=>{btn.disabled=false;btn.innerHTML='<i class="fas fa-check me-1"></i>Import';alert("Error: "+err.message);});}
document.getElementById("importModal").addEventListener("hidden.bs.modal",resetImport);
</script>


  <?php foreach($bookings as $b): ?>
  <!-- View Modal -->
  <div class="modal fade" id="viewModal<?=$b['id']?>" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Booking Details — <?=htmlspecialchars($b['booking_ref'])?></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">

          <!-- Status Bar -->
          <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between">
            <span class="badge bg-<?=$status_colors[$b['status']]??'secondary'?> fs-6 px-3 py-2">
              <?=$status_labels[$b['status']]??ucfirst($b['status'])?>
            </span>
            <span class="text-muted small">Created: <?=isset($b['created_at'])?date('d M Y H:i', strtotime($b['created_at'])):'—'?></span>
          </div>

          <div class="p-4 row g-3">

            <!-- Customer -->
            <div class="col-12"><h6 class="fw-bold border-bottom pb-2"><i class="fas fa-user me-2 text-primary"></i>Customer Information</h6></div>
            <div class="col-md-4">
              <div class="text-muted small">Name</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['customer_name']??'—')?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Phone</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['customer_phone']??'—')?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Email</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['customer_email']??'—')?></div>
            </div>

            <!-- Trip -->
            <div class="col-12 mt-2"><h6 class="fw-bold border-bottom pb-2"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Trip Details</h6></div>
            <div class="col-md-6">
              <div class="text-muted small">Pickup Address</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['pickup_address']??'—')?></div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small">Dropoff Address</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['dropoff_address']??'—')?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Pickup Date & Time</div>
              <div class="fw-semibold"><?=isset($b['pickup_datetime'])?date('d M Y, H:i', strtotime($b['pickup_datetime'])):'—'?></div>
            </div>
            <div class="col-md-2">
              <div class="text-muted small">Passengers</div>
              <div class="fw-semibold"><i class="fas fa-users me-1 text-muted"></i><?=$b['passengers']??'—'?></div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">Vehicle Type</div>
              <div class="fw-semibold"><?=htmlspecialchars(ucfirst($b['vehicle_type']??'—'))?></div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">Flight Number</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['flight_number']??'—')?></div>
            </div>

            <!-- Dispatch -->
            <div class="col-12 mt-2"><h6 class="fw-bold border-bottom pb-2"><i class="fas fa-broadcast-tower me-2 text-success"></i>Dispatch</h6></div>
            <div class="col-md-6">
              <div class="text-muted small">Assigned Driver</div>
              <div class="fw-semibold"><?=htmlspecialchars($b['driver_name']??'Not assigned')?></div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small">Vehicle</div>
              <div class="fw-semibold"><?php $veh=trim(($b['vehicle_make']??'').' '.($b['vehicle_model']??'')); $plate=$b['vehicle_plate']??''; echo $veh||$plate ? htmlspecialchars($veh.($plate?' ['.$plate.']':'')) : '—'; ?></div>
            </div>

            <!-- Payment -->
            <div class="col-12 mt-2"><h6 class="fw-bold border-bottom pb-2"><i class="fas fa-credit-card me-2 text-warning"></i>Payment</h6></div>
            <div class="col-md-4">
              <div class="text-muted small">Fare</div>
              <div class="fw-semibold fs-5"><?= $b['fare'] ? (defined('CURRENCY_SYMBOL')?CURRENCY_SYMBOL:'$').number_format($b['fare'],2) : '—' ?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Payment Method</div>
              <div class="fw-semibold"><?=htmlspecialchars(ucfirst(str_replace('_',' ',$b['payment_method']??'—')))?></div>
            </div>
            <div class="col-md-4">
              <div class="text-muted small">Payment Status</div>
              <div class="fw-semibold"><?=htmlspecialchars(ucfirst($b['payment_status']??'—'))?></div>
            </div>

            <!-- Notes -->
            <?php if(!empty($b['notes']) || !empty($b['admin_notes'])): ?>
            <div class="col-12 mt-2"><h6 class="fw-bold border-bottom pb-2"><i class="fas fa-sticky-note me-2 text-info"></i>Notes</h6></div>
            <?php if(!empty($b['notes'])): ?>
            <div class="col-12">
              <div class="text-muted small">Customer Notes</div>
              <div class="p-2 bg-light rounded"><?=nl2br(htmlspecialchars($b['notes']))?></div>
            </div>
            <?php endif; ?>
            <?php if(!empty($b['admin_notes'])): ?>
            <div class="col-12">
              <div class="text-muted small">Admin Notes</div>
              <div class="p-2 bg-light rounded"><?=nl2br(htmlspecialchars($b['admin_notes']))?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="booking_edit.php?id=<?=$b['id']?>" class="btn btn-warning"><i class="fas fa-edit me-1"></i>Edit Booking</a>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

<?php if ($gmaps_key): ?>
<style>
/* Google Maps Autocomplete dropdown styling for admin */
.pac-container {
    z-index: 99999 !important;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    font-family: inherit;
    margin-top: 2px;
}
.pac-item {
    padding: 8px 14px;
    cursor: pointer;
    font-size: 14px;
    border-top: 1px solid #f0f0f0;
}
.pac-item:hover, .pac-item-selected { background: #fff8e1; }
.pac-icon { margin-top: 8px; }
.pac-item-query { font-weight: 600; color: #333; }
.pac-matched { color: #f5a623; }
</style>
<script>
function initAdminGoogleMaps() {
    var opts = {
        types: ['geocode', 'establishment'],
        fields: ['formatted_address', 'geometry', 'name']
    };

    var pickupEl  = document.getElementById('admin_pickup_address');
    var dropoffEl = document.getElementById('admin_dropoff_address');

    if (pickupEl) {
        var pickupAC = new google.maps.places.Autocomplete(pickupEl, opts);
        pickupAC.addListener('place_changed', function() {
            var place = pickupAC.getPlace();
            if (place.formatted_address) pickupEl.value = place.formatted_address;
        });
        pickupEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.querySelector('.pac-container:not([style*="display: none"])')) {
                e.preventDefault();
            }
        });
    }

    if (dropoffEl) {
        var dropoffAC = new google.maps.places.Autocomplete(dropoffEl, opts);
        dropoffAC.addListener('place_changed', function() {
            var place = dropoffAC.getPlace();
            if (place.formatted_address) dropoffEl.value = place.formatted_address;
        });
        dropoffEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.querySelector('.pac-container:not([style*="display: none"])')) {
                e.preventDefault();
            }
        });
    }
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($gmaps_key); ?>&libraries=places&callback=initAdminGoogleMaps" async defer></script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
