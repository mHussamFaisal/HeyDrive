<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'versjspr_taxisdispatch');
define('DB_USER', 'versjspr_taxisdispatch');
define('DB_PASS', 'TaxiDispatch2024!');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected OK\n";
    
    $tables = [
        "CREATE TABLE IF NOT EXISTS `td_users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(150) NOT NULL,
          `email` varchar(150) NOT NULL,
          `phone` varchar(30) DEFAULT NULL,
          `password` varchar(255) NOT NULL,
          `role` enum('admin','driver','customer') NOT NULL DEFAULT 'customer',
          `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `td_vehicles` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `driver_id` int(11) DEFAULT NULL,
          `type` varchar(50) NOT NULL DEFAULT 'sedan',
          `make` varchar(100) NOT NULL,
          `model` varchar(100) NOT NULL,
          `year` int(4) DEFAULT NULL,
          `license_plate` varchar(30) NOT NULL,
          `color` varchar(50) DEFAULT NULL,
          `capacity` int(2) NOT NULL DEFAULT 4,
          `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `td_drivers` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `vehicle_id` int(11) DEFAULT NULL,
          `license_number` varchar(100) DEFAULT NULL,
          `status` enum('available','busy','offline') NOT NULL DEFAULT 'offline',
          `rating` decimal(3,2) DEFAULT 5.00,
          `total_trips` int(11) DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `td_bookings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `booking_ref` varchar(20) NOT NULL,
          `customer_name` varchar(150) NOT NULL,
          `customer_email` varchar(150) DEFAULT NULL,
          `customer_phone` varchar(30) NOT NULL,
          `pickup_address` text NOT NULL,
          `dropoff_address` text NOT NULL,
          `pickup_datetime` datetime NOT NULL,
          `passengers` int(2) NOT NULL DEFAULT 1,
          `vehicle_type` varchar(50) DEFAULT 'sedan',
          `flight_number` varchar(30) DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `driver_id` int(11) DEFAULT NULL,
          `status` enum('pending','confirmed','assigned','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
          `fare` decimal(10,2) DEFAULT NULL,
          `payment_method` enum('cash','card','account') DEFAULT 'cash',
          `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `booking_ref` (`booking_ref`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `td_pricing` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `vehicle_type` varchar(50) NOT NULL,
          `base_fare` decimal(10,2) NOT NULL DEFAULT 3.00,
          `per_mile` decimal(10,2) NOT NULL DEFAULT 2.50,
          `per_minute` decimal(10,2) NOT NULL DEFAULT 0.30,
          `min_fare` decimal(10,2) NOT NULL DEFAULT 5.00,
          `airport_surcharge` decimal(10,2) NOT NULL DEFAULT 10.00,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        "CREATE TABLE IF NOT EXISTS `td_settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `setting_key` varchar(100) NOT NULL,
          `setting_value` text DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
        echo "Table created OK\n";
    }
    
    // Insert admin user
    $hash = password_hash('Admin2024!', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO td_users (name, email, phone, password, role, status) VALUES ('Admin', 'admin@taxisdispatch.com', '+1-555-0100', '$hash', 'admin', 'active')");
    echo "Admin inserted OK\n";
    
    // Insert pricing defaults
    $pdo->exec("INSERT IGNORE INTO td_pricing (vehicle_type, base_fare, per_mile, per_minute, min_fare, airport_surcharge) VALUES ('sedan', 3.00, 2.50, 0.30, 5.00, 10.00), ('suv', 5.00, 3.50, 0.40, 8.00, 15.00), ('van', 6.00, 4.00, 0.50, 10.00, 20.00), ('luxury', 10.00, 5.00, 0.60, 20.00, 25.00)");
    echo "Pricing inserted OK\n";
    
    // Insert default settings
    $pdo->exec("INSERT IGNORE INTO td_settings (setting_key, setting_value) VALUES ('company_name', 'TaxisDispatch'), ('company_phone', '+1-555-0100'), ('company_email', 'info@taxisdispatch.com'), ('currency', 'USD'), ('currency_symbol', '$'), ('google_maps_key', ''), ('timezone', 'America/New_York')");
    echo "Settings inserted OK\n";
    
    echo "INSTALLATION COMPLETE! Admin: admin@taxisdispatch.com / Admin2024!\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
