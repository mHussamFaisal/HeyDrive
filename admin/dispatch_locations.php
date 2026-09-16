<?php
/**
 * dispatch_locations.php
 * Returns JSON array of driver positions for the live-tracking map.
 * Called by the dispatch page every 30 seconds via fetch().
 *
 * Each driver row must have  lat / lng  columns in td_drivers.
 * Run the SQL below once to add them if they don't exist:
 *
 *   ALTER TABLE td_drivers
 *     ADD COLUMN lat  DECIMAL(10,7) NULL DEFAULT NULL,
 *     ADD COLUMN lng  DECIMAL(10,7) NULL DEFAULT NULL,
 *     ADD COLUMN location_updated_at DATETIME NULL DEFAULT NULL;
 */

require_once __DIR__ . '/header.php';   // sets up db_connect() etc.
// Return JSON only
header('Content-Type: application/json');

$pdo = db_connect();

$rows = $pdo->query("
    SELECT d.id, u.name, u.phone, d.status, d.rating, d.total_trips,
           d.lat, d.lng, d.location_updated_at,
           v.make, v.model, v.license_plate, v.color
    FROM td_drivers d
    JOIN td_users u ON d.user_id = u.id
    LEFT JOIN td_vehicles v ON d.vehicle_id = v.id
    WHERE u.status = 'active'
    ORDER BY FIELD(d.status,'available','busy','offline'), u.name
")->fetchAll(PDO::FETCH_ASSOC);

$out = [];
foreach ($rows as $d) {
    $out[] = [
        'id'      => (int)$d['id'],
        'name'    => $d['name'],
        'phone'   => $d['phone'],
        'status'  => $d['status'],
        'plate'   => $d['license_plate'] ?? '',
        'vehicle' => trim(($d['color'] ?? '') . ' ' . ($d['make'] ?? '') . ' ' . ($d['model'] ?? '')),
        'rating'  => $d['rating'],
        'trips'   => $d['total_trips'],
        'lat'     => $d['lat'] !== null ? (float)$d['lat'] : null,
        'lng'     => $d['lng'] !== null ? (float)$d['lng'] : null,
        'updated' => $d['location_updated_at'],
    ];
}

echo json_encode($out);
exit;
