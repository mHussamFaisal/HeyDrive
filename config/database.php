<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_OBJ,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => eto_config('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => eto_config('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => eto_config('DB_PREFIX', 'eto_'),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => eto_config('DB_HOST', 'localhost'),
            'port' => eto_config('DB_PORT', '3306'),
            'database' => eto_config('DB_DATABASE', ''),
            'username' => eto_config('DB_USERNAME', ''),
            'password' => eto_config('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => eto_config('DB_PREFIX', 'eto_'),
            'strict' => eto_config('DB_STRICT', true),
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => eto_config('DB_HOST', 'localhost'),
            'port' => eto_config('DB_PORT', '5432'),
            'database' => eto_config('DB_DATABASE', 'forge'),
            'username' => eto_config('DB_USERNAME', 'forge'),
            'password' => eto_config('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => eto_config('DB_PREFIX', 'eto_'),
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => eto_config('REDIS_HOST', '127.0.0.1'),
            'password' => eto_config('REDIS_PASSWORD', null),
            'port' => eto_config('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
