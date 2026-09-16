<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=versjspr_taxisdispatch;charset=utf8mb4",
    'versjspr_taxisdispatch', 'TaxiDispatch2024!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Clear subscription tables with encrypted data
foreach (['eto_subscriptions', 'eto_subscription_modules', 'eto_sessions'] as $t) {
    try {
        $pdo->exec("TRUNCATE TABLE `$t`");
        echo "✓ Cleared $t
";
    } catch(Exception $e) { echo "⚠ $t: " . $e->getMessage() . "
"; }
}

// Check if subscriptions table has encrypted params column
$cols = $pdo->query("SHOW COLUMNS FROM eto_subscriptions")->fetchAll(PDO::FETCH_ASSOC);
echo "Columns: " . implode(", ", array_column($cols, 'Field')) . "
";
echo "Done";
?>