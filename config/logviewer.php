<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pattern and storage path settings
    |--------------------------------------------------------------------------
    |
    | The env key for pattern and storage path with a default value
    |
    */
    'max_file_size' => 52428800, // size in Byte
    'pattern' => eto_config('LOGVIEWER_PATTERN', '*.log'),
    'storage_path' => eto_config('APP_LOG_PATH', storage_path('logs')), // LOGVIEWER_STORAGE_PATH
];
