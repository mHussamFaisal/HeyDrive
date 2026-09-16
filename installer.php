<?php
// TaxisDispatch.com Installer
// This script sets up the database and creates the super admin user
// DELETE THIS FILE AFTER RUNNING

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$DB_HOST = '127.0.0.1';
$DB_NAME = 'versjspr_taxisdispatch';
$DB_USER = 'versjspr_taxisdispatch';
$DB_PASS = 'TaxiDispatch2024!';
$DB_PREFIX = 'eto_';

$ADMIN_EMAIL = 'Info@techoftsystem.com';
$ADMIN_NAME  = 'Super Admin';
$ADMIN_PASS  = '$2b$12$tg7s4X1okbat4TvfOOI0CerU/JLR.qGrEStkNCa7jsPmwYrBSuUWm';

$results = [];

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $results[] = "✓ Connected to database $DB_NAME";

    // Check current tables
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $results[] = "Current tables: " . count($existingTables);

    // Import the SQL schema
    $sqlFile = __DIR__ . '/install_db.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($s) { return !empty($s) && substr($s, 0, 2) !== '--'; }
        );
        
        $imported = 0;
        $errors = 0;
        foreach ($statements as $stmt) {
            if (empty(trim($stmt))) continue;
            try {
                $pdo->exec($stmt);
                $imported++;
            } catch (Exception $e) {
                $errors++;
                // ignore duplicate table errors
            }
        }
        
        $results[] = "✓ SQL import: $imported statements, $errors skipped";
    } else {
        $results[] = "⚠ install_db.sql not found";
    }
    
    // Check tables after import
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $results[] = "Tables after import: " . count($tables) . " (" . implode(', ', array_slice($tables, 0, 5)) . "...)";

    // Create super admin user
    // Check if users table exists
    $usersTable = $DB_PREFIX . 'users';
    if (in_array($usersTable, $tables)) {
        // Check if admin already exists
        $check = $pdo->prepare("SELECT id FROM `$usersTable` WHERE email = ?");
        $check->execute([$ADMIN_EMAIL]);
        $existing = $check->fetch();
        
        if ($existing) {
            // Update password
            $upd = $pdo->prepare("UPDATE `$usersTable` SET password = ?, name = ? WHERE email = ?");
            $upd->execute([$ADMIN_PASS, $ADMIN_NAME, $ADMIN_EMAIL]);
            $adminId = $existing['id'];
            $results[] = "✓ Updated existing admin user (ID: $adminId)";
        } else {
            // Insert new admin
            $ins = $pdo->prepare("INSERT INTO `$usersTable` (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $ins->execute([$ADMIN_NAME, $ADMIN_EMAIL, $ADMIN_PASS]);
            $adminId = $pdo->lastInsertId();
            $results[] = "✓ Created admin user (ID: $adminId)";
        }
        
        // Assign admin role
        $rolesTable = $DB_PREFIX . 'roles';
        $roleUserTable = $DB_PREFIX . 'role_user';
        
        if (in_array($rolesTable, $tables)) {
            $adminRole = $pdo->query("SELECT id FROM `$rolesTable` WHERE slug LIKE '%admin%' LIMIT 1")->fetch();
            if ($adminRole) {
                // Check if role already attached
                $checkRole = $pdo->prepare("SELECT * FROM `$roleUserTable` WHERE role_id = ? AND user_id = ?");
                $checkRole->execute([$adminRole['id'], $adminId]);
                if (!$checkRole->fetch()) {
                    $pdo->prepare("INSERT INTO `$roleUserTable` (role_id, user_id) VALUES (?, ?)")
                        ->execute([$adminRole['id'], $adminId]);
                }
                $results[] = "✓ Admin role assigned (role_id: {$adminRole['id']})";
            } else {
                $results[] = "⚠ No admin role found in roles table";
            }
        }
        
        // Set all permissions
        $permTable = $DB_PREFIX . 'permissions';
        $permUserTable = $DB_PREFIX . 'permission_user';
        if (in_array($permTable, $tables)) {
            $perms = $pdo->query("SELECT id FROM `$permTable`")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($perms as $permId) {
                try {
                    $pdo->prepare("INSERT IGNORE INTO `$permUserTable` (permission_id, user_id) VALUES (?, ?)")
                        ->execute([$permId, $adminId]);
                } catch (Exception $e) {}
            }
            $results[] = "✓ Assigned " . count($perms) . " permissions to admin";
        }
        
    } else {
        $results[] = "⚠ Users table ($usersTable) not found";
    }
    
    // Update config settings in DB if config table exists
    $configTable = $DB_PREFIX . 'config';
    if (in_array($configTable, $tables)) {
        $settings = [
            ['company_name', 'TaxisDispatch'],
            ['company_url', 'https://taxisdispatch.com'],
            ['currency', 'GBP'],
            ['timezone', 'Europe/London'],
        ];
        foreach ($settings as [$key, $val]) {
            $pdo->prepare("UPDATE `$configTable` SET `value` = ? WHERE `key` = ?")
                ->execute([$val, $key]);
        }
        $results[] = "✓ Updated config settings";
    }
    
} catch (Exception $e) {
    $results[] = "✗ ERROR: " . $e->getMessage();
}

echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:14px;'>";
echo "=== TaxisDispatch.com Database Installer ===\n\n";
foreach ($results as $r) {
    echo $r . "\n";
}
echo "\n=== DONE ===\n";
echo "Login URL: https://taxisdispatch.com/login\n";
echo "Email: $ADMIN_EMAIL\n";
echo "Password: Tech12345%\$#@\n";
echo "</pre>";
?>