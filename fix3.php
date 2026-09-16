<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=versjspr_taxisdispatch;charset=utf8mb4",
    'versjspr_taxisdispatch', 'TaxiDispatch2024!');
foreach (['eto_cache', 'eto_sessions', 'eto_subscriptions', 'eto_subscription_modules'] as $t) {
    try { $pdo->exec("TRUNCATE TABLE `$t`"); echo "✓ Cleared $t
"; }
    catch(Exception $e) { echo "⚠ $t: " . substr($e->getMessage(),0,60) . "
"; }
}
echo "All done";
?>