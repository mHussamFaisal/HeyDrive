<?php
/**
 * dashboard_stats.php  – AJAX JSON endpoint
 * Returns booking stats for the selected period.
 */
require_once '../includes/config.php';
require_admin();          // checks session, redirects if not logged in - no HTML output
header('Content-Type: application/json');

$pdo    = db_connect();
$period = $_GET['period'] ?? 'today';

// ── Date filter SQL fragment ───────────────────────────────────────────────
switch ($period) {
    case 'today':
        $where = "DATE(pickup_datetime) = CURDATE()";
        $fmt   = '%H';
        break;
    case 'week':
        $where = "YEARWEEK(pickup_datetime, 1) = YEARWEEK(CURDATE(), 1)";
        $fmt   = '%Y-%m-%d';
        break;
    case 'month':
        $where = "YEAR(pickup_datetime) = YEAR(CURDATE()) AND MONTH(pickup_datetime) = MONTH(CURDATE())";
        $fmt   = '%Y-%m-%d';
        break;
    case 'year':
        $where = "YEAR(pickup_datetime) = YEAR(CURDATE())";
        $fmt   = '%Y-%m';
        break;
    default: // all
        $where = "1=1";
        $fmt   = '%Y-%m';
        break;
}

// ── Totals ─────────────────────────────────────────────────────────────────
$total   = (int)   $pdo->query("SELECT COUNT(*) FROM td_bookings WHERE $where")->fetchColumn();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(fare),0) FROM td_bookings WHERE $where AND status='completed'")->fetchColumn();

// ── Status breakdown ───────────────────────────────────────────────────────
$all_statuses = ['pending','confirmed','assigned','in_progress','completed','cancelled','no_show'];
$statuses = array_fill_keys($all_statuses, 0);
$sc = $pdo->query("SELECT status, COUNT(*) as cnt FROM td_bookings WHERE $where GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sc as $row) if (isset($statuses[$row['status']])) $statuses[$row['status']] = (int)$row['cnt'];

// ── Trend data ─────────────────────────────────────────────────────────────
$labels = [];
$data   = [];

if ($period === 'today') {
    // Hourly 00-23
    $rows = $pdo->query("
        SELECT HOUR(pickup_datetime) as h, COUNT(*) as cnt
        FROM td_bookings WHERE $where
        GROUP BY h ORDER BY h ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $r) $map[(int)$r['h']] = (int)$r['cnt'];
    for ($h = 0; $h < 24; $h++) {
        $labels[] = sprintf('%02d:00', $h);
        $data[]   = $map[$h] ?? 0;
    }

} elseif ($period === 'week') {
    // 7 days Mon-Sun
    $rows = $pdo->query("
        SELECT DATE(pickup_datetime) as d, COUNT(*) as cnt
        FROM td_bookings WHERE $where
        GROUP BY d ORDER BY d ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $r) $map[$r['d']] = (int)$r['cnt'];
    $mon = strtotime('monday this week');
    for ($i = 0; $i < 7; $i++) {
        $day = date('Y-m-d', $mon + $i * 86400);
        $labels[] = date('D d M', $mon + $i * 86400);
        $data[]   = $map[$day] ?? 0;
    }

} elseif ($period === 'month') {
    // Every day in current month
    $rows = $pdo->query("
        SELECT DATE(pickup_datetime) as d, COUNT(*) as cnt
        FROM td_bookings WHERE $where
        GROUP BY d ORDER BY d ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $r) $map[$r['d']] = (int)$r['cnt'];
    $days = (int) date('t');
    for ($d = 1; $d <= $days; $d++) {
        $day = date('Y-m-') . sprintf('%02d', $d);
        $labels[] = $d . ' ' . date('M');
        $data[]   = $map[$day] ?? 0;
    }

} else {
    // year / all – monthly buckets
    $rows = $pdo->query("
        SELECT DATE_FORMAT(pickup_datetime,'%Y-%m') as ym,
               DATE_FORMAT(pickup_datetime,'%b %Y') as lbl,
               COUNT(*) as cnt
        FROM td_bookings WHERE $where
        GROUP BY ym, lbl ORDER BY ym ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $labels[] = $r['lbl'];
        $data[]   = (int)$r['cnt'];
    }
    if (empty($labels)) { $labels = [date('M Y')]; $data = [0]; }
}

echo json_encode([
    'period'       => $period,
    'total'        => $total,
    'revenue'      => round($revenue, 2),
    'statuses'     => $statuses,
    'trend_labels' => $labels,
    'trend_data'   => $data,
]);
exit;
