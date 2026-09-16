<?php
$dirs = [
    __DIR__.'/payments',
    __DIR__.'/payments/gateways',
];
foreach($dirs as $d) {
    if(!is_dir($d)) {
        mkdir($d, 0755, true);
        echo "Created: $d\n";
    } else {
        echo "Exists: $d\n";
    }
}
?>