<?php

return [

    'version' => '4.0.4',
    'timestamp' => 151507965,

    'backup_system_expiry_days' => eto_config('BACKUP_SYSTEM_EXPIRY_DAYS', 30),
    'backup_system_type' => eto_config('BACKUP_SYSTEM_TYPE', 'system'), // full | system

    'api_license_url' => eto_config('API_LICENSE_URL', 'https://api.easytaxioffice.com/api/v1/license/'),
    'api_download_url' => eto_config('API_DOWNLOAD_URL', 'https://api.easytaxioffice.com/api/v1/download/'),
    'api_news_url' => eto_config('API_NEWS_URL', 'https://api.easytaxioffice.com/api/v1/news/'),
    'docs_url' => eto_config('DOCS_URL', 'https://docs.easytaxioffice.com'),

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    */

    'name' => eto_config('APP_NAME', 'Company Name'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => eto_config('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => eto_config('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => eto_config('APP_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    | http://php.net/manual/en/timezones.america.php
    |
    */

    'timezone' => eto_config('APP_TIMEZONE', 'Europe/London'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale_switcher_display_name_code' => 0,
    'locale_switcher_enabled' => 1,
    'locale_switcher_style' => 'dropdown',
    'locale_switcher_display' => 'names_flags',
    'locale_active' => ['en-GB', 'es-ES', 'pt-PT', 'pt-BR', 'it-IT', 'ru-RU', 'hu-HU', 'fr-FR', 'de-DE', 'pl-PL', 'cs-CZ', 'nl-NL', 'fi-FI'],
    'locales' => [
        'en-GB' => ['code' => 'en-GB', 'name' => 'English', 'native' => 'English'],
        'es-ES' => ['code' => 'es-ES', 'name' => 'Spanish', 'native' => 'Español'],
        'pt-PT' => ['code' => 'pt-PT', 'name' => 'Portuguese', 'native' => 'Português'],
        'pt-BR' => ['code' => 'pt-BR', 'name' => 'Portuguese (Brazil)', 'native' => 'Português (Brasil)'],
        'it-IT' => ['code' => 'it-IT', 'name' => 'Italian', 'native' => 'Italiano'],
        'ru-RU' => ['code' => 'ru-RU', 'name' => 'Russian', 'native' => 'Русский'],
        'hu-HU' => ['code' => 'hu-HU', 'name' => 'Hungarian', 'native' => 'Magyar'],
        'fr-FR' => ['code' => 'fr-FR', 'name' => 'French', 'native' => 'Français'],
        'de-DE' => ['code' => 'de-DE', 'name' => 'German', 'native' => 'Deutsch'],
        'pl-PL' => ['code' => 'pl-PL', 'name' => 'Polish', 'native' => 'Polski'],
        'cs-CZ' => ['code' => 'cs-CZ', 'name' => 'Czech', 'native' => 'Čeština'],
        'nl-NL' => ['code' => 'nl-NL', 'name' => 'Dutch', 'native' => 'Nederlands'],
        'fi-FI' => ['code' => 'fi-FI', 'name' => 'Finnish', 'native' => 'Suomalainen'],
    ],
    'locale' => eto_config('APP_LOCALE', 'en-GB'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => eto_config('APP_FALLBACK_LOCALE', 'en-GB'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => eto_config('APP_KEY', 'base64:vaP02UvRD7Pq/67dmzbcl5V/X7EUBJlxxC0slFxu/cY='),
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => eto_config('APP_LOG', 'daily'),
    'log_max_files' => 30,
    'log_level' => eto_config('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        // Illuminate\Translation\TranslationServiceProvider::class,
        Spatie\TranslationLoader\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Intervention\Image\ImageServiceProvider::class,
        Yajra\Datatables\DatatablesServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        Barryvdh\Debugbar\ServiceProvider::class,
        ZanySoft\Zip\ZipServiceProvider::class,
        Spatie\Activitylog\ActivitylogServiceProvider::class,
        Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,

        Chumper\Zipper\ZipperServiceProvider::class,
        jeremykenedy\LaravelRoles\RolesServiceProvider::class,
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        // Laravel\Cashier\CashierServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

        'Image' => Intervention\Image\Facades\Image::class,
        'Datatables' => Yajra\Datatables\Facades\Datatables::class,
        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
        'Debugbar' => Barryvdh\Debugbar\Facade::class,
        'Zip' => ZanySoft\Zip\ZipFacade::class,
        'Zipper' => Chumper\Zipper\Zipper::class,
    ],

];
