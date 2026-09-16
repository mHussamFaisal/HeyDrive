<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;

class AppLoader
{
    use \App\Traits\Core\Subscription;
    // use \App\Traits\TestDispatch; // Test

    public $app;
    public $isNotInstalled = false;
    public $isExpired = false;
    public $isCorrupted = false;
    public $system = false;

    public function __construct(Application $app)
    {
        // $this->_driverTracking(); // Test

        $this->app = $app;

        $request = request();
        $request->system = new \stdClass();
        $request->system->subscription = (object)[
            'id' => 0,
            'domain' => $request->getSchemeAndHttpHost(),
            'domain_raw' => get_domain(),
            'license' => '',
            'license_status' => 'active',
            'license_status_expire_at' => null,
            'expire_at' => null,
        ];
        $request->system->sites = [];
        $request->system->site = (object)[
            'id' => 0,
            'key' => '',
            'domain' => '',
            'name' => '',
        ];
        $request->system->modules_new = 0;
        $request->system->config = [
            'site.allow_driver_availability' => 1,
            'site.allow_services' => 1,
            'site.allow_dispatch' => 1,
            'site.expiry_dispatch' => null,
            'site.allow_customer_app' => 1,
            'site.allow_driver_app' => 0,
            'site.expiry_driver_app' => null,
            // 'eto.allow_fleet_operator' => 0,
        ];
    }

    public function handle($request, Closure $next)
    {
        // Skip when in installer
        if ($request->is('install') || $request->is('install/*') ||
            $request->is('migrate') || $request->is('subscription/migrate') ||
            $request->is('modules/migrate') // Required for version <= 3.25.3
        ) {
            return $next($request);
        }

        try {
            $request->system = Cache::get(md5(get_domain())) ?: $request->system;

            // Merge with default to make sure we always have some value in case it was later added before cache
            if (!isset($request->system->sites)) {
                $request->system->sites = [];
            }
        }
        catch (Exception $e) {}

        $request = $this->checkSubscription();

        // load settings from DB|Cache
        settings_load();
        disk_extends();

        // if (config('eto.deactivate_system') === 1 && !$request->is('activation') && !$request->is('deactivation')) {
        //     return $this->runViewOnMilddleware('installer.activation');
        // }
        // elseif ($request->is('activation')) {
        //     return $next($request);
        // }
        // elseif ($this->isNotInstalled && !$request->is('install/license')) {
        //     return redirect('install/license');
        // }

        $request = $this->runCronFromSettings();

        if ($request->system->subscription->license_status == 'suspended' && !empty($request->system->subscription->license_status_expire_at)) {
            $checkStatus = check_expire($request->system->subscription->license_status_expire_at);
            if (!$checkStatus->isExpire) {
                $request->system->subscription->license_status = 'suspension_warning';
            }
        }

        if ($request->system->subscription->license_status != 'suspended') {
            if ($this->isExpired || $this->isCorrupted) {
                if ($request->is('admin') || $request->is('subscription') || $request->is('dispatch') || $request->is('login')) {
                    $this->runCron();
                    $request = request();
                    $checkLicense = check_expire($request->system->subscription->expire_at);
                    if ($checkLicense->isExpire) {
                        $request = $this->checkSubscription(true);

                        if ($this->isCorrupted) {
                            return $this->runViewOnMilddleware('errors.corrupted');
                        }
                        else {
                            return $this->runViewOnMilddleware('errors.expired');
                        }
                    }
                }
                else {
                    if ($this->isCorrupted) {
                        return $this->runViewOnMilddleware('errors.corrupted');
                    }
                    else {
                        return $this->runViewOnMilddleware('errors.expired');
                    }
                }
            }
        }
        else {
            if (defined('ETO_REMINDERS_TABLE_EXISTS')) {
                \App\Http\Controllers\ReminderController::checkSubscription($request->system->subscription);
            }
        }

        if ($this->isCorrupted) {
            return $this->runViewOnMilddleware('errors.corrupted');
        }

        // Set locale
        $locale = $request->get('locale');

        if ($request->get('lang')) {
            $locale = $request->get('lang');
        }

        if (!empty($locale)) {
            \App\Http\Controllers\LocaleController::change($locale, 'status');
        }

        // Default site configuration
        $config = \App\Models\Config::getBySiteId(config('site.site_id'));
        $config->mapData();

        // IMPORTANT! This should be set just after the setting are loaded to avoid issues with timezone
        // Set timezone
        date_default_timezone_set(config('app.timezone'));

        // Force HTTPS
        if (config('site.force_https') && empty($request->get('no_https_redirect'))) {
            \Request::setTrustedProxies([$request->getClientIp()]); // for proxies
            if (!$request->isSecure()) {
                return redirect()->to('https://'. $request->getHttpHost() . $request->getRequestUri());
            }
        }

        // Default locale
        if (!session()->has('locale')) {
            session(['locale' => config('app.locale')]);
        }

        // Set locale
        if (session()->has('locale') && array_key_exists(session('locale'), config('app.locales'))) {
            $locale = session('locale');
        }
        else {
            $locale = config('app.fallback_locale');
        }
        app()->setLocale($locale);

        // Set site defaults
        config(['app.locale' => app()->getLocale()]);
        $config->loadLocale();
        $config->mapData();

        // Force user language
        $user = auth()->user();

        if (isset($user->id) && !(
            $request->is('/') || $request->is('locale/*') || $request->is('payment-waiting') ||
            $request->is('booking') || $request->is('booking/*') ||
            $request->is('customer') || $request->is('customer/*') ||
            $request->is('feedback') || $request->is('feedback/*') ||
            ($request->is('etov2') && $request->get('apiType') == 'frontend' && $request->get('isAdminRequest') != 1)
        )) {
            $locale = $user->getSetting('app.locale') !== null ? $user->getSetting('app.locale') : config('app.locale');
            app()->setLocale($locale);
        }

        // User last activity
        if (\Auth::check() && !$request->is('logout') && !$request->is('mobile/logout')) {
            auth()->user()->setLastActivity();
        }

        return $next($request);
    }

    function checkSubscription($checkExpire = false) {
        $request = request();
        $domain = get_domain();
        $domainAlias = 'www.'.$domain;
        $coreDomain = [];
        $this->getSubscription();
        $subscription = request()->system->subscription;
        $sitesLimit = !empty($subscription->params->sites->limit) ? (int)$subscription->params->sites->limit : 1;

        if (!empty($subscription->domain_raw)) {
            $coreDomain[] = $subscription->domain_raw;
        }

        if (isset($subscription->license)) {
            if (null === $subscription->license) {
                $this->isCorrupted = true;
            }
            elseif (empty($subscription->params->domainInstallation)) {
                $check = $this->runCronWithRedirect();
                if ($check !== true) {
                    return $check;
                }
            }

            if (!empty($subscription->params->allowDriverAvailability)) {
                $request->system->config['site.allow_driver_availability'] = 1;
            }

            if (!empty($subscription->params->allowServices)) {
                $request->system->config['site.allow_services'] = 1;
            }

            if (config('eto.allow_fleet_operator')) {
            // if (!empty($subscription->params->allowFleetOperator)) {
                // $request->system->config['eto.allow_fleet_operator'] = 1;

                $notUseRoles = (array)config('roles.not_use_roles');
                if ($notUseRoles) {
                    foreach ($notUseRoles as $k => $v) {
                        if ($v == 'admin.fleet_operator') {
                            unset($notUseRoles[$k]);
                        }
                    }
                    $request->system->config['roles.not_use_roles'] = $notUseRoles;
                    // config(['roles.not_use_roles' => $notUseRoles]);
                }
            // }
            }

            if ((empty($subscription->params->domainInstallation)
                    || $subscription->hash != md5($subscription->license.json_encode($subscription->params).$subscription->expire_at))
                && (!$request->is('subscription') || !$request->is('subscription/*'))
            ) {
                $check = $this->runCronWithRedirect();
                if ($check !== true) {
                    return $check;
                }
            }

            $checkLicense = check_expire($subscription->expire_at);
            if ($checkLicense->isExpire) {
                $this->isExpired = true;
            }

            foreach ($request->system->sites as $site) {
                if (!empty($site->domain)) {
                    $coreDomain[] = $site->domain;
                }
            }

            if ($checkExpire) {
                $request = $this->runCronFromSettings();
            }

            config($request->system->config);
        }

        // if (((!empty($coreDomain) && !in_array($domain, $coreDomain) && !in_array($domainAlias, $coreDomain)) ||
        //       empty($subscription)) && !$request->is('install/license')) {
        //     $this->isNotInstalled = true;
        // }
        // elseif (!empty($coreDomain) && count($coreDomain) > (int)$sitesLimit + 1 && !$request->is('license-corrupted')) {
        //     $this->isCorrupted = true;
        // }

        return $request;
    }
}
