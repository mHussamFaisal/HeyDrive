<?php

return [
    'php' => [
        'method' => 'compare',
        'min' => '7.1.3',
        'max' => '7.3.16',
    ],
    'fileinfo' => [
        'method' => 'extension'
    ],
    'openssl' => [
        'method' => 'extension'
    ],
    'pdo' => [
        'method' => 'extension'
    ],
    'pdo_mysql' => [
        'method' => 'extension'
    ],
    'mbstring' => [
        'method' => 'extension'
    ],
    'tokenizer' => [
        'method' => 'extension'
    ],
    'JSON' => [
        'method' => 'extension'
    ],
    'cURL' => [
        'method' => 'extension'
    ],
    'Zip' => [
        'method' => 'extension'
    ],
    'ZipArchive' => [
        'method' => 'class'
    ],
    'mysqli_connect' => [
        'method' => 'function'
    ],
    'allow_url_fopen' => [
        'method' => 'ini'
    ],
    'file_get_contents' => [
        'method' => 'function'
    ],
    'curl_version' => [
        'method' => 'function'
    ],
    'gethostbyname' => [
        'method' => 'function'
    ],
    'getservbyport' => [
        'method' => 'function'
    ],
    'escapeshellarg' => [
        'method' => 'function'
    ],
    'escapeshellcmd' => [
        'method' => 'function'
    ],
    'mail' => [
        'method' => 'function'
    ],
    // 'proc_open' => [
    //     'method' => 'function'
    // ],
    // 'proc_close' => [
    //     'method' => 'function'
    // ],
];
