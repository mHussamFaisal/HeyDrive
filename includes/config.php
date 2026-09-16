<?php
// Taxi Dispatch System - Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'versjspr_taxisdispatch');
$is_local = (in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || php_sapi_name() === 'cli' || strpos($_SERVER['HTTP_HOST'] ?? '', '192.168.') !== false);

define('DB_USER', $is_local ? 'root' : 'versjspr_taxisdispatch');
define('DB_PASS', $is_local ? '' : 'TaxiDispatch2024!');
define('DB_PREFIX', 'td_');

define('APP_NAME', 'TaxisDispatch');
define('APP_URL', $is_local ? 'http://localhost/Dispatch%20System/Live%20website%20code' : 'https://taxisdispatch.com');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Europe/Berlin');

date_default_timezone_set(TIMEZONE);
session_start();

function db_connect() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_driver() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'driver';
}

function require_login() {
    if (!is_logged_in()) {
        redirect(APP_URL . '/admin/login.php');
    }
}

/**
 * Dynamic Membership Duration Calculator (e.g. "1 year 9 months", "9 months 1 week")
 */
function format_membership_duration($created_at) {
    if (!$created_at) return "Recently";
    $dt_start = new DateTime($created_at);
    $dt_now   = new DateTime('now');
    $diff     = $dt_start->diff($dt_now);

    $parts = [];
    if ($diff->y > 0) {
        $parts[] = $diff->y . ' ' . ($diff->y === 1 ? 'year' : 'years');
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m === 1 ? 'month' : 'months');
        }
    } elseif ($diff->m > 0) {
        $parts[] = $diff->m . ' ' . ($diff->m === 1 ? 'month' : 'months');
        $weeks = floor($diff->d / 7);
        if ($weeks > 0) {
            $parts[] = intval($weeks) . ' ' . (intval($weeks) === 1 ? 'week' : 'weeks');
        }
    } elseif ($diff->d >= 7) {
        $weeks = floor($diff->d / 7);
        $days  = $diff->d % 7;
        $parts[] = intval($weeks) . ' ' . (intval($weeks) === 1 ? 'week' : 'weeks');
        if ($days > 0) {
            $parts[] = intval($days) . ' ' . (intval($days) === 1 ? 'day' : 'days');
        }
    } else {
        $days = max(1, $diff->d);
        $parts[] = $days . ' ' . ($days === 1 ? 'day' : 'days');
    }

    return implode(' ', $parts);
}

/**
 * Dynamic Last Seen Formatter (Online vs Offline)
 */
function format_last_seen($last_seen) {
    if (empty($last_seen)) {
        return date('d/m/Y H:i') . ' <span class="text-muted">(Offline)</span>';
    }
    $timestamp = strtotime($last_seen);
    $formatted_date = date('d/m/Y H:i', $timestamp);
    // If activity within last 5 minutes (300 seconds)
    if (time() - $timestamp <= 300) {
        return $formatted_date . ' <span class="text-success fw-bold">(Online)</span>';
    } else {
        return $formatted_date . ' <span class="text-muted">(Offline)</span>';
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect(APP_URL . '/admin/login.php');
    }
}

function require_driver() {
    require_login();
    if (!is_driver() && !is_admin()) {
        redirect(APP_URL . '/driver/login.php');
    }
}

function format_datetime($dt) {
    return $dt ? date('d.m.Y H:i', strtotime($dt)) : '-';
}

function format_price($price) {
    return number_format($price, 2, ',', '.') . ' €';
}

function status_badge($status) {
    $badges = [
        'pending'    => '<span class="badge bg-warning text-dark">⏳ Pending</span>',
        'confirmed'  => '<span class="badge bg-info">✅ Confirmed</span>',
        'assigned'   => '<span class="badge bg-primary">🚖 Assigned</span>',
        'in_progress'=> '<span class="badge bg-orange text-white" style="background:#fd7e14">🚕 In Progress</span>',
        'completed'  => '<span class="badge bg-success">✔ Completed</span>',
        'cancelled'  => '<span class="badge bg-danger">✖ Cancelled</span>',
        'no_show'    => '<span class="badge bg-secondary">🚫 No Show</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}
?>
