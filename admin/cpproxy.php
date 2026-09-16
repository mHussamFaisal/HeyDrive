<?php
$CPANEL_BASE = 'https://business166.web-hosting.com:2083';
$CPANEL_USER = 'versjspr';
$CPANEL_PASS = '$sD&QkZ3ApgC215Anq8$';
$PROXY_PATH  = '/admin/cpproxy.php';

session_start();

function cp_login_get_sess() {
    global $CPANEL_BASE, $CPANEL_USER, $CPANEL_PASS;
    $cf = sys_get_temp_dir() . '/cps_' . session_id() . '.txt';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $CPANEL_BASE . '/login/',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['user'=>$CPANEL_USER,'pass'=>$CPANEL_PASS,'goto_uri'=>'/']),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cf,
        CURLOPT_COOKIEFILE     => $cf,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    curl_exec($ch);
    $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    preg_match('#/(cpsess\d+)/#', $url, $m);
    if (!empty($m[1])) {
        $_SESSION['cps']  = $m[1];
        $_SESSION['cpcf'] = $cf;
    }
    return $m[1] ?? null;
}

// Auto-login if no session
if (empty($_SESSION['cps'])) cp_login_get_sess();

$sess = $_SESSION['cps'] ?? '';
$cf   = $_SESSION['cpcf'] ?? (sys_get_temp_dir() . '/cps_' . session_id() . '.txt');

// Determine target path
// Requests come as ?p=/path/...  OR  as PATH_INFO
$path = $_GET['p'] ?? '';
if (empty($path)) {
    $path = $_SERVER['PATH_INFO'] ?? '';
}
if (empty($path) || $path === '/') {
    // Go straight to Jupiter dashboard
    $path = "/$sess/frontend/jupiter/index.html";
}
// Inject session into path if missing
if (!preg_match('#^/cpsess#', $path)) {
    $path = "/$sess$path";
}

$target = $CPANEL_BASE . $path;
// Forward query string except our own 'p' param
$qs = $_SERVER['QUERY_STRING'] ?? '';
$qs = preg_replace('/(^|&)p=[^&]*/','', $qs);
if (!empty($qs)) $target .= '?' . ltrim($qs, '&');

// Fetch from cPanel
$ch = curl_init();
$opts = [
    CURLOPT_URL            => $target,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEJAR      => $cf,
    CURLOPT_COOKIEFILE     => $cf,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_HEADER         => true,
    CURLOPT_HTTPHEADER     => [
        'Host: business166.web-hosting.com',
        'User-Agent: Mozilla/5.0',
        'Accept: */*',
    ],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opts[CURLOPT_POST]       = true;
    $opts[CURLOPT_POSTFIELDS] = file_get_contents('php://input') ?: http_build_query($_POST);
}
curl_setopt_array($ch, $opts);
$resp  = curl_exec($ch);
$code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$hs    = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$hdrs = substr($resp, 0, $hs);
$body = substr($resp, $hs);

// Handle redirects
if ($code >= 300 && $code < 400) {
    preg_match('/^Location:\s*(.+)$/im', $hdrs, $loc);
    if (!empty($loc[1])) {
        $nl = trim($loc[1]);
        // If redirecting to login page, refresh session then retry
        if (strpos($nl, '/login') !== false) {
            cp_login_get_sess();
            $nl = "/$_SESSION[cps]/frontend/jupiter/index.html";
        }
        $nl = str_replace($CPANEL_BASE, $PROXY_PATH . '?p=', $nl);
        $nl = preg_replace('#https?://business166\.web-hosting\.com:2083#', $PROXY_PATH . '?p=', $nl);
        header("Location: $nl", true, 302); exit;
    }
}

// Rewrite body
preg_match('/^Content-Type:\s*(.+)$/im', $hdrs, $ct);
$ctype = strtolower(trim($ct[1] ?? ''));
$rewrite = strpos($ctype,'html') !== false || strpos($ctype,'javascript') !== false || strpos($ctype,'css') !== false;
if ($rewrite) {
    $body = str_replace($CPANEL_BASE, $PROXY_PATH . '?p=', $body);
    $body = preg_replace('#https?://business166\.web-hosting\.com:2083#', $PROXY_PATH . '?p=', $body);
    // Rewrite absolute paths like href="/cpsess..."  action="/login/"  src="/cPanel-theme..."
    $body = preg_replace('#(href|src|action)="(/[^"]+)"#', '$1="'.$PROXY_PATH.'?p=$2"', $body);
}

http_response_code($code);
$skip = ['transfer-encoding','connection','content-encoding','content-length','location','set-cookie'];
foreach (explode("\r\n", $hdrs) as $l) {
    if (strpos($l, ':') === false) continue;
    [$n] = explode(':', $l, 2);
    if (!in_array(strtolower(trim($n)), $skip)) header($l, false);
}
echo $body;
?>
