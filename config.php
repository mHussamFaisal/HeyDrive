<?php

$is_local = (in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || php_sapi_name() === 'cli' || strpos($_SERVER['HTTP_HOST'] ?? '', '192.168.') !== false);

return [
    'APP_NAME' => 'TaxisDispatch',
    'APP_KEY' => 'base64:2DK02cDCBVCkWtiMmGDwILPiQ+20zfTmOXfespeNDNk=',
    'APP_URL' => $is_local ? 'http://localhost/Dispatch%20System/Live%20website%20code' : 'https://taxisdispatch.com',
    'APP_DEBUG' => $is_local,
    'APP_ENV' => $is_local ? 'local' : 'production',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'versjspr_taxisdispatch',
    'DB_USERNAME' => $is_local ? 'root' : 'versjspr_taxisdispatch',
    'DB_PASSWORD' => $is_local ? '' : 'TaxiDispatch2024!',
    'DB_PREFIX' => 'eto_',
];
