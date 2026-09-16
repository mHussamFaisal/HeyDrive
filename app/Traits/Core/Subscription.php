<?php

namespace App\Traits\Core;

use Exception;
use Illuminate\Support\Facades\Cache;

trait Subscription
{
    public function getSubscription($force = false)
    {
        $request = request();
        $domain = get_domain();

        if ($request->system->subscription->id === 0 || $force) {
            $expiresAt = \Carbon\Carbon::now()->addHours(24);

            if (!$request->is('install/license')) {
                $subscription = \App\Models\Subscription::first();
            }

            if (!empty($subscription)) {
                $request->system->subscription->id = $subscription->id;
                $request->system->subscription->domain = (is_ssl() ? 'https://' : 'http://') . $subscription->domain . '/';
                $request->system->subscription->domain_raw = $subscription->domain;
                $request->system->subscription->license = $subscription->license;
                $request->system->subscription->license_status = !empty($subscription->params->licenseStatus) ? $subscription->params->licenseStatus : 'active';
                $request->system->subscription->license_status_expire_at = !empty($subscription->params->licenseStatusExpireAt) ? $subscription->params->licenseStatusExpireAt : null;
                $request->system->subscription->hash = $subscription->hash;
                $request->system->subscription->expire_at = $subscription->expire_at;
                $request->system->subscription->default_site_id = $subscription->site_id;
                $request->system->subscription->update_at = $subscription->update_at;
                $request->system->subscription->support_at = $subscription->support_at;
                $request->system->subscription->params = $subscription->params;

                $sites = \App\Models\Site::where('subscription_id', $request->system->subscription->id)
                  ->orderBy('default', 'desc')->orderBy('ordering', 'asc')
                  ->get()->toArray();

                $detectedSite = null;
                $request->system->sites = [];

                foreach ($sites as $k => $v) {
                    $v = (object)$v;
                    $request->system->sites[] = $v;

                    if ($v->published == 1) {
                        if ($v->default == 1 || (empty($request->system->site->id) && ($v->domain == $subscription->domain || $k == 0))) {
                            $request->system->site->id = $v->id;
                            $request->system->site->key = $v->key;
                            $request->system->site->domain = $v->domain;
                            $request->system->site->name = $v->name;
                        }

                        if (empty($detectedSite) && $v->domain == get_domain()) {
                            $detectedSite = $v;
                        }
                    }
                }

                if (!empty($detectedSite)) {
                    $request->system->site->id = $detectedSite->id;
                    $request->system->site->key = $detectedSite->key;
                    $request->system->site->domain = $detectedSite->domain;
                    $request->system->site->name = $detectedSite->name;
                }
            }

            $this->getSubscriptionModulesConfig();
            Cache::put(md5($domain), $request->system, $expiresAt);
        }

        if ($request->system->subscription->id !== 0) {
            foreach ($request->system->modules as $module) {
                if (count($module->subscriptions) > 0) {
                    $checkModule = check_expire($module->subscriptions[0]->expire_at);

                    if ($checkModule->isExpire == false
                        && $module->status == '1'
                        && $module->subscriptions[0]->status == '1'
                        && $module->subscriptions[0]->hash == md5($request->system->subscription->license . json_encode($module->subscriptions[0]->params) . $module->subscriptions[0]->expire_at)
                    ) {
                        if ($module->type == 'eto.driverApp') {
                            $request->system->config['site.allow_driver_app'] = 1;
                            $request->system->config['site.expiry_driver_app'] = $module->subscriptions[0]->expire_at;
                        }
                    }
                }
            }

            if ($request->get('site_key') && $request->get('site_key') != $request->system->site->key) {
                foreach ($request->system->sites as $site) {
                    if (!empty($site->key) && $site->key == $request->get('site_key')) {
                        $request->system->site->id = $site->id;
                        $request->system->site->key = $site->key;
                        $request->system->site->domain = $site->domain;
                        $request->system->site->name = $site->name;
                    }
                }
            }

            if ($request->get('site_key') || (empty(session('site_id')) || empty(session('site_key')))) {
                session([
                    'site_id' => $request->system->site->id,
                    'site_key' => $request->system->site->key,
                    'site_domain' => $request->system->site->domain,
                    'site_name' => $request->system->site->name,
                ]);
            }

            if ((!$request->get('site_key') || ($request->get('site_key') && $request->get('site_key') != $request->system->site->key))
                && session('site_id') && session('site_key') && session('site_domain') && session('site_name')
            ) {
                $request->system->site->id = session('site_id');
                $request->system->site->key = session('site_key');
                $request->system->site->domain = session('site_domain');
                $request->system->site->name = session('site_name');
            }
        }
        else {
            $this->isNotInstalled = true;
        }
    }

    public function getSubscriptionModulesConfig() {
        $request = request();
        $modules = \App\Models\Module::with([
            'subscriptions' => function($query) use($request) {
                $query->where('subscription_id', $request->system->subscription->id);
            }
        ])->get();

        $request->system->modules = $modules;
        return $request;
    }

    public function runCron($date = false, $interval = false)
    {
        $request = request();
        $runCron = $subscriptionController = true;

        if ($date) {
            $check = check_expire($date, true);
            $runCron = $check->diff >= $interval ? true : false;
        }

        $isBlock = config('eto_cron.running');

        if ($runCron && (int)$isBlock === 0 && !$request->is('mobile') && !$request->is('mobile/*')) {
            $sendDriverCount = false;
            if (!empty($request->system->subscription->id) && config('eto.last_verify')) {
                $sendDriverCount = get_active_drivers_cron();
            }

            $subscriptionController = (new \App\Http\Controllers\Subscription\SubscriptionController())->verify($request, true, $sendDriverCount, true);
            check_requirements();

            try {
                \DB::table('cache')->where('expiration', '<', time())->delete();
            }
            catch (Exception $e) {
                \Log::error('Cannot clear cache data Traits\Core\Subscription::runCron');
            }
        }

        if (isset($check) && $runCron) {
            return false;
        }
        elseif (is_object($subscriptionController) && $subscriptionController != true && $subscriptionController->status === false) {
            return 'fail_api_connect';
        }

        $this->getSubscription(($date && $runCron));
        return true;
    }

    public function runCronFromSettings()
    {
        $lastVerify = false;
        $interval = false;
        $request = request();
        $domain = get_domain();
        $lastVerifyCache = Cache::get(md5($domain.'_last_verify')) ?: config('eto.last_verify');

        if (null !== config('eto_cron.update')) {
            if (null !== $lastVerifyCache) {
                $lastVerify = \Carbon\Carbon::parse($lastVerifyCache);
                $interval = config('eto_cron.update.interval');
            }
        }

        if (null !== config('eto.available_versions')) {
            $request->system->modules_new = count(config('eto.available_versions'));
        }

        $this->runCron($lastVerify, $interval);
        return $request;
    }

    public function runCronWithRedirect()
    {
        $cron = $this->runCron();

        if ($cron === 'fail_api_connect') {
            $this->isCorrupted = true;
        }
        elseif ($cron === false) {
            return redirect('install/license');
        }

        return true;
    }

    public function runViewOnMilddleware($view)
    {
        if ($view == 'installer.activation' && (int)config('eto.deactivate_system') === 0) {
            return redirect(redirect_to());
        } elseif($view == 'installer.deactivation' && (int)config('eto.deactivate_system') === 1) {
            return redirect('activation');
        }

        $response = new \Illuminate\Http\Response(view($view));
        return $response;
    }
}
