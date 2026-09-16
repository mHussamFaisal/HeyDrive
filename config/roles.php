<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package Connection
    |--------------------------------------------------------------------------
    |
    | You can set a different database connection for this package. It will set
    | new connection for models Role and Permission. When this option is null,
    | it will connect to the main database, which is set up in database.php
    |
    */

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Slug Separator
    |--------------------------------------------------------------------------
    |
    | Here you can change the slug separator. This is very important in matter
    | of magic method __call() and also a `Slugable` trait. The default value
    | is a dot.
    |
    */

    'separator' => '.',

    /*
    |--------------------------------------------------------------------------
    | Roles, Permissions and Allowed "Pretend"
    |--------------------------------------------------------------------------
    |
    | You can pretend or simulate package behavior no matter what is in your
    | database. It is really useful when you are testing you application.
    | Set up what will methods hasRole(), hasPermission() and allowed() return.
    |
    */

    'pretend' => [
        'enabled' => false,
        'options' => [
            'hasRole' => true,
            'hasPermission' => true,
            'allowed' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel Roles Package Integration Settings
    |--------------------------------------------------------------------------
    */

    'laravelUsersEnabled' => eto_config('ROLES_GUI_LARAVEL_ROLES_ENABLED', false),

    'not_use_roles' => [
        'customer.root',
        'service',
        'admin.manager',
        'admin.operator',
        'admin.fleet_operator',
    ],
    'make_duplicate' => [
        'admin.root',
        'admin.manager',
        'admin.operator',
        'supplier.root',
        'driver.root',
        'customer.root',
    ]
];
