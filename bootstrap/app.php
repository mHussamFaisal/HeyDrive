<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

// Load config data
if (empty($etoConfig)) {
    $etoConfig = [];
}
if (file_exists(base_path('config.php'))) {
    $localEtoConfig = include(base_path('config.php'));
    $etoConfig = array_replace_recursive($localEtoConfig, $etoConfig);
}

// Check what type of cookie to set
if (is_ssl()
    && !isset($etoConfig['SESSION_COOKIE_SECURE'])
    && !isset($etoConfig['SESSION_COOKIE_SAME_SITE'])
    // && !isset($etoConfig['SESSION_COOKIE_NAME'])
) {
    $etoConfig['SESSION_COOKIE_SECURE'] = true;
    $etoConfig['SESSION_COOKIE_SAME_SITE'] = 'none';
    // $etoConfig['SESSION_COOKIE_NAME'] = 'eto_session_secure';
}
// else {
//     $etoConfig['SESSION_COOKIE_NAME'] = 'eto_session_http';
//     $etoConfig['SESSION_COOKIE_XSRF_TOKEN'] = 'XSRF-TOKEN_HTTP';
// }

if (!isset($etoConfig['SESSION_COOKIE_PATH'])) {
    $etoConfig['SESSION_COOKIE_PATH'] = get_base_url() ?: '/';
}

$app->etoConfig = $etoConfig;

// Set public path https://laracasts.com/discuss/channels/general-discussion/where-do-you-set-public-directory-laravel-5
$app->bind('path.public', function() {
    $type = eto_config('APP_PUBLIC_PATH');

    if ($type === 'DOCUMENT_ROOT') {
        $path = $_SERVER['DOCUMENT_ROOT'];
    }
    elseif ($type === 'PUBLIC') {
        $path = base_path().DIRECTORY_SEPARATOR.'public';
    }
    elseif (!empty($type)) {
        $path = $type;
    }
    else {
        $path = base_path();
    }
    return realpath($path);
});

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
