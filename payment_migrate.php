<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'versjspr_taxisdispatch');
define('DB_USER', 'versjspr_taxisdispatch');
define('DB_PASS', 'TaxiDispatch2024!');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected OK\n";

    // Payment gateways config table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `td_payment_gateways` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `gateway` varchar(50) NOT NULL,
      `label` varchar(100) NOT NULL,
      `icon` varchar(100) DEFAULT NULL,
      `enabled` tinyint(1) NOT NULL DEFAULT 0,
      `test_mode` tinyint(1) NOT NULL DEFAULT 1,
      `config` longtext DEFAULT NULL COMMENT 'JSON config: API keys, secrets, etc.',
      `sort_order` int(3) NOT NULL DEFAULT 0,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `gateway` (`gateway`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table td_payment_gateways OK\n";

    // Payments / transactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `td_payments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `booking_id` int(11) NOT NULL,
      `booking_ref` varchar(20) NOT NULL,
      `gateway` varchar(50) NOT NULL,
      `transaction_id` varchar(255) DEFAULT NULL,
      `amount` decimal(10,2) NOT NULL,
      `currency` varchar(10) NOT NULL DEFAULT 'GBP',
      `status` enum('pending','processing','completed','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
      `gateway_response` longtext DEFAULT NULL,
      `invoice_number` varchar(30) DEFAULT NULL,
      `invoice_sent` tinyint(1) DEFAULT 0,
      `notes` text DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `booking_id` (`booking_id`),
      KEY `transaction_id` (`transaction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table td_payments OK\n";

    // Add payment columns to bookings if not exist
    try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN `payment_gateway` varchar(50) DEFAULT NULL AFTER payment_status"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN `payment_transaction_id` varchar(255) DEFAULT NULL AFTER payment_gateway"); } catch(Exception $e){}
    try { $pdo->exec("ALTER TABLE td_bookings ADD COLUMN `invoice_number` varchar(30) DEFAULT NULL AFTER payment_transaction_id"); } catch(Exception $e){}
    echo "Booking columns OK\n";

    // Insert default gateways
    $gateways = [
        ['stripe',  'Stripe (Credit/Debit Card)', 'fab fa-stripe-s', 1, 1, json_encode(['publishable_key'=>'','secret_key'=>'','webhook_secret'=>'']), 1],
        ['paypal',  'PayPal', 'fab fa-paypal', 1, 1, json_encode(['client_id'=>'','client_secret'=>'','mode'=>'sandbox']), 2],
        ['square',  'Square', 'fas fa-square', 0, 1, json_encode(['access_token'=>'','location_id'=>'','app_id'=>'']), 3],
        ['sumup',   'SumUp', 'fas fa-credit-card', 0, 1, json_encode(['api_key'=>'','merchant_code'=>'']), 4],
        ['cash',    'Cash Payment', 'fas fa-money-bill-wave', 1, 0, json_encode([]), 5],
        ['invoice', 'Invoice / Bank Transfer', 'fas fa-file-invoice', 1, 0, json_encode(['bank_name'=>'','account_name'=>'','account_number'=>'','sort_code'=>'','iban'=>'','payment_terms'=>'14']), 6],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO td_payment_gateways (gateway,label,icon,enabled,test_mode,config,sort_order) VALUES (?,?,?,?,?,?,?)");
    foreach ($gateways as $g) { $stmt->execute($g); }
    echo "Default gateways inserted OK\n";

    echo "\n✅ PAYMENT DB MIGRATION COMPLETE\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
