<?php
/**
 * driver/update_location.php
 * Called by the driver app (JS Geolocation API) to push current GPS position.
 * POST body: { lat: float, lng: float }
 */
require_once '../includes/config.php';
require_driver();
header('Content-Type: application/json');

$pdo  = db_connect();
$data = json_decode(file_get_contents('php://input'), true);

$lat = isset($data['lat']) ? (float)$data['lat'] : null;
$lng = isset($data['lng']) ? (float)$data['lng'] : null;

if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid coordinates']);
    exit;
}

$pdo->prepare("
    UPDATE td_drivers
    SET lat = ?, lng = ?, location_updated_at = NOW(), last_seen = NOW()
    WHERE user_id = ?
")->execute([$lat, $lng, $_SESSION['user_id']]);

$pdo->prepare("
    UPDATE td_users
    SET last_seen = NOW()
    WHERE id = ?
")->execute([$_SESSION['user_id']]);

echo json_encode(['ok' => true]);
exit;
