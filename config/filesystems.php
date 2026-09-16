<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'url' => 'storage/app',
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => 'storage/app/public',
            'visibility' => 'public',
        ],
        'storage_framework' => [
            'driver' => 'local',
            'root' => storage_path('framework'),
            'url' => 'storage/framework',
        ],
        'assets' => [
            'driver' => 'local',
            'root' => public_path('assets'),
            'url' => 'assets',
        ],
        'css' => [
            'driver' => 'local',
            'root' => public_path('assets/css'),
            'url' => 'assets/css',
        ],
        'js' => [
            'driver' => 'local',
            'root' => public_path('assets/js'),
            'url' => 'assets/js',
        ],
        'plugins' => [
            'driver' => 'local',
            'root' => public_path('assets/plugins'),
            'url' => 'assets/plugins',
        ],
        'images' => [
            'driver' => 'local',
            'root' => public_path('assets/images'),
            'url' => 'assets/images',
        ],
        'images-vehicles-types' => [
            'driver' => 'local',
            'root' => public_path('assets/images/vehicles-types'),
            'url' => 'assets/images/vehicles-types',
        ],
        'images-payments' => [
            'driver' => 'local',
            'root' => public_path('assets/images/payments'),
            'url' => 'assets/images/payments',
        ],
        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => 'uploads',
        ],
        'archive' => [
            'driver' => 'local',
            'root' => public_path('uploads/archive'),
            'url' => 'uploads/archive',
        ],
        'avatars' => [
            'driver' => 'local',
            'root' => public_path('uploads/avatars'),
            'url' => 'uploads/avatars',
        ],
        'logo' => [
            'driver' => 'local',
            'root' => public_path('uploads/logo'),
            'url' => 'uploads/logo',
        ],
        'payments' => [
            'driver' => 'local',
            'root' => public_path('uploads/payments'),
            'url' => 'uploads/payments',
        ],
        'safe' => [
            'driver' => 'local',
            'root' => public_path('uploads/safe'),
            'url' => 'uploads/safe',
        ],
        'vehicles' => [
            'driver' => 'local',
            'root' => public_path('uploads/vehicles'),
            'url' => 'uploads/vehicles',
        ],
        'vehicles-types' => [
            'driver' => 'local',
            'root' => public_path('uploads/vehicles-types'),
            'url' => 'uploads/vehicles-types',
        ],
        'backup_local' => [
            'driver' => 'local',
            'root' => eto_config('APP_BACKUP_PATH', base_path('backups')),
            'url' => 'backups',
        ],
        'logs' => [
            'driver' => 'local',
            'root' => eto_config('APP_LOG_PATH', storage_path('logs')),
            'url' => 'logs',
            'file_name' => 'eto.log',
        ],
        'tmp' => [
            'driver' => 'local',
            'root' => public_path('tmp'),
            'url' => 'tmp',
        ],
        'root' => [
            'driver' => 'local',
            'root' => base_path(),
            'url' => '',
        ],
        'root_subscription' => [
            'driver' => 'local',
            'root' => public_path(),
            'url' => '',
        ],
        's3' => [
            'driver' => 's3',
            'key' => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],
        'backup_ftp' => [
            'driver' => 'ftp',
            'host' => '',
            'username' => '',
            'password' => '',
            'private_key' => '',
            'port' => 21,
            'root' => '',
            'timeout' => 30,
            'passive' => true,
            'ssl' => true,
        ],
    ],

];
