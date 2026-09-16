<?php
set_time_limit(600);
ini_set('display_errors', 1);

$src_db = 'versjspr_test';
$src_user = 'versjspr_test';
$src_pass = 'NRXJ.f+t?lmf';

$dst_db = 'versjspr_taxisdispatch';
$dst_user = 'versjspr_taxisdispatch';
$dst_pass = 'TaxiDispatch2024!';
$host = '127.0.0.1';

$ADMIN_EMAIL = 'Info@techoftsystem.com';
$ADMIN_PASS_PLAIN = 'Tech12345%$#@';

echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:14px;'>";
echo "=== TaxisDispatch DB Setup ===

";

// Method 1: Direct PDO copy
try {
    $src_pdo = new PDO("mysql:host=$host;dbname=$src_db;charset=utf8mb4", $src_user, $src_pass);
    $src_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to source DB
";
    
    $dst_pdo = new PDO("mysql:host=$host;dbname=$dst_db;charset=utf8mb4", $dst_user, $dst_pass);
    $dst_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to destination DB
";
    
    // Get all tables from source
    $tables = $src_pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Source tables: " . count($tables) . "

";
    
    $dst_pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    $copied = 0;
    foreach ($tables as $table) {
        // Get CREATE TABLE
        $create = $src_pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $createSQL = $create["Create Table"] ?? $create[1] ?? null;
        if (!$createSQL) continue;
        
        // Drop and recreate
        try {
            $dst_pdo->exec("DROP TABLE IF EXISTS `$table`");
            $dst_pdo->exec($createSQL);
            
            // Copy data
            $rows = $src_pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                $cols = implode("`,`", array_keys($rows[0]));
                $placeholders = implode(",", array_fill(0, count($rows[0]), "?"));
                $stmt = $dst_pdo->prepare("INSERT INTO `$table` (`$cols`) VALUES ($placeholders)");
                foreach ($rows as $row) {
                    $stmt->execute(array_values($row));
                }
            }
            $copied++;
        } catch (Exception $e) {
            echo "  ⚠ $table: " . $e->getMessage() . "
";
        }
    }
    
    $dst_pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "✓ Copied $copied tables to destination DB

";
    
    // Now create/update admin user
    $usersTable = "eto_users";
    $check = $dst_pdo->prepare("SELECT id FROM `$usersTable` WHERE email = ?");
    $check->execute([$ADMIN_EMAIL]);
    $existing = $check->fetch();
    
    $hashedPass = password_hash($ADMIN_PASS_PLAIN, PASSWORD_BCRYPT);
    
    if ($existing) {
        $dst_pdo->prepare("UPDATE `$usersTable` SET password = ?, name = ?, updated_at = NOW() WHERE email = ?")
            ->execute([$hashedPass, 'Super Admin', $ADMIN_EMAIL]);
        $adminId = $existing['id'];
        echo "✓ Updated admin user (ID: $adminId)
";
    } else {
        $ins = $dst_pdo->prepare("INSERT INTO `$usersTable` (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $ins->execute(['Super Admin', $ADMIN_EMAIL, $hashedPass]);
        $adminId = $dst_pdo->lastInsertId();
        echo "✓ Created admin user (ID: $adminId)
";
    }
    
    // Assign admin.*  role
    $adminRole = $dst_pdo->query("SELECT id FROM `eto_roles` WHERE slug LIKE '%admin%' LIMIT 1")->fetch();
    if ($adminRole) {
        $dst_pdo->exec("DELETE FROM `eto_role_user` WHERE user_id = $adminId");
        $dst_pdo->prepare("INSERT INTO `eto_role_user` (role_id, user_id) VALUES (?,?)")
            ->execute([$adminRole['id'], $adminId]);
        echo "✓ Admin role assigned
";
    }
    
    // Assign all permissions
    $perms = $dst_pdo->query("SELECT id FROM `eto_permissions`")->fetchAll(PDO::FETCH_COLUMN);
    $dst_pdo->exec("DELETE FROM `eto_permission_user` WHERE user_id = $adminId");
    foreach ($perms as $pId) {
        try {
            $dst_pdo->prepare("INSERT INTO `eto_permission_user` (permission_id, user_id) VALUES (?,?)")
                ->execute([$pId, $adminId]);
        } catch(Exception $e) {}
    }
    echo "✓ Assigned " . count($perms) . " permissions
";
    
    // Update config
    $dst_pdo->query("UPDATE `eto_config` SET `value` = 'TaxisDispatch' WHERE `key` = 'company_name'");
    $dst_pdo->query("UPDATE `eto_config` SET `value` = 'https://taxisdispatch.com' WHERE `key` = 'company_url'");
    echo "✓ Config updated
";
    
    echo "
=== SUCCESS ===
";
    echo "Login URL: https://taxisdispatch.com/login
";
    echo "Email: $ADMIN_EMAIL
";
    echo "Password: $ADMIN_PASS_PLAIN
";
    
} catch (Exception $e) {
    echo "✗ FATAL ERROR: " . $e->getMessage() . "
";
}

echo "</pre>";
?>