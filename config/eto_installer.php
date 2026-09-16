<?php

use Illuminate\Validation\Rule;

return [
    /*
    |--------------------------------------------------------------------------
    | Environment Form Wizard Validation Rules & Messages
    |--------------------------------------------------------------------------
    |
    | This are the default form vield validation rules. Available Rules:
    | https://laravel.com/docs/5.4/validation#available-validation-rules
    |
    */
    'environment' => [
        'form' => [
            'rules' => [
                'app_name' => 'required|string|max:50',
                'environment' => 'required|string|max:50',
                'environment_custom' => 'required_if:environment,other|max:50',
                'app_debug' => [
                    'required',
                    Rule::in(['true', 'false']),
                ],
                'app_log_level' => 'required|string|max:50',
                'app_url' => 'required|url',
                'app_email' => 'required|string|max:255',
                'app_password' => 'required|string|min:4|max:255',
                'app_license' => 'required|string|max:255',
                'database_connection' => 'required|string|max:50',
                'database_hostname' => 'required|string|max:255',
                'database_port' => 'required|numeric',
                'database_name' => 'required|string|max:50',
                'database_username' => 'required|string|max:50',
                'database_password' => 'string|max:50',
                'database_prefix' => 'string|max:50',
                'broadcast_driver' => 'required|string|max:50',
                'cache_driver' => 'required|string|max:50',
                'session_driver' => 'required|string|max:50',
                'queue_driver' => 'required|string|max:50',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Installed Middlware Options
    |--------------------------------------------------------------------------
    | Different available status switch configuration for the
    | canInstall middleware located in `canInstall.php`.
    |
    */
    'installed' => [
        'redirectOptions' => [
            'route' => [
                'name' => 'admin',
                'data' => [],
            ],
            'abort' => [
                'type' => '404',
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Selected Installed Middlware Option
    |--------------------------------------------------------------------------
    | The selected option fo what happens when an installer intance has been
    | Default output is to `/resources/views/error/404.blade.php` if none.
    | The available middleware options include:
    | route, abort, dump, 404, default, ''
    |
    */
    'installedAlreadyAction' => '',

];
