<?php
// Clear all Laravel caches
$cachePaths = [
    __DIR__ . '/bootstrap/cache/',
    __DIR__ . '/storage/framework/cache/',
    __DIR__ . '/storage/framework/sessions/',
    __DIR__ . '/storage/framework/views/',
];

$cleared = [];
foreach ($cachePaths as $path) {
    if (is_dir($path)) {
        $files = glob($path . '*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
                $count++;
            }
        }
        $cleared[] = "$path ($count files)";
    }
}

// Also remove config.php cache
@unlink(__DIR__ . '/bootstrap/cache/config.php');
@unlink(__DIR__ . '/bootstrap/cache/services.php');
@unlink(__DIR__ . '/bootstrap/cache/packages.php');
@unlink(__DIR__ . '/bootstrap/cache/routes.php');

echo json_encode(['cleared' => $cleared, 'status' => 'ok']);
?>