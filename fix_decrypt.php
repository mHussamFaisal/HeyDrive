<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=versjspr_taxisdispatch;charset=utf8mb4",
    'versjspr_taxisdispatch', 'TaxiDispatch2024!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tables with encrypted data that cause MAC errors
$tables_to_clear = ['eto_subscription', 'eto_sessions'];
foreach ($tables_to_clear as $t) {
    try {
        $pdo->exec("TRUNCATE TABLE `$t`");
        echo "✓ Cleared $t
";
    } catch(Exception $e) {
        echo "⚠ $t: " . $e->getMessage() . "
";
    }
}

// Also clear the eto_config license/subscription fields  
try {
    $pdo->exec("UPDATE eto_config SET value = '' WHERE `key` IN ('license_key', 'subscription_params', 'activation_code')");
    echo "✓ Cleared license config
";
} catch(Exception $e) {}

echo "Done";
?>