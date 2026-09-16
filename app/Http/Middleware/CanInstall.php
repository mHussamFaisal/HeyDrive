<?php

namespace App\Http\Middleware;

use Closure;

class CanInstall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (($request->is('install') || $request->is('install/*'))
            && !$request->is('install/license')
            && !$request->is('install/setLicense')
            && !$request->is('install/setDataToDB')
            && !$request->is('install/final')
            && !$request->is('install/loginAfterInstall')
            && $this->alreadyInstalled()
        ) {
            $installedRedirect = config('eto_installer.installedAlreadyAction');

            switch ($installedRedirect) {
                case 'route':
                    $routeName = config('eto_installer.installed.redirectOptions.route.name');
                    $data = config('eto_installer.installed.redirectOptions.route.message');
                    return redirect()->route($routeName)->with(['data' => $data]);
                break;
                case 'abort':
                    abort(config('eto_installer.installed.redirectOptions.abort.type'));
                break;
                case '404':
                case 'default':
                default:
                    return redirect('/');
                break;
            }
        }
        else if (!$request->is('install') && !$request->is('install/*')
            && !$request->is('install/license')
            && !$request->is('install/setLicense')
            && !$request->is('install/setDataToDB')
            && !$request->is('install/final')
            && !$request->is('install/loginAfterInstall')
            && !$this->alreadyInstalled()
        ) {
            return redirect('install');
        }

        return $next($request);
    }

    /**
     * If application is already installed.
     *
     * @return bool
     */
    public function alreadyInstalled()
    {
        return file_exists(storage_path('installed')) && file_exists(base_path('config.php'));
    }
}
