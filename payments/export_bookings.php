<?php
require_once __DIR__.'/../includes/config.php';
require_admin();
$pdo = db_connect();

$stmt = $pdo->query("SELECT b.booking_ref, b.customer_name, b.customer_email, b.customer_phone,
    b.pickup_address, b.dropoff_address, b.pickup_datetime, b.passengers, b.vehicle_type,
    b.flight_number, b.status, b.fare, b.payment_method, b.notes, b.created_at
    FROM td_bookings b ORDER BY b.created_at DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="bookings_export_'.date('Ymd_His').'.csv"');

$out = fopen('php://output','w');
fputcsv($out, ['booking_ref','customer_name','customer_email','customer_phone',
               'pickup_address','dropoff_address','pickup_datetime','passengers',
               'vehicle_type','flight_number','status','fare','payment_method','notes','created_at']);
foreach($rows as $r) {
    fputcsv($out, array_values($r));
}
fclose($out);
exit;
?>
