<?php

namespace App\Http\Controllers\Subscription;

use Closure;
use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Module;
use App\Models\Site;
use App\Models\Subscription;
use App\Models\SubscriptionModule;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Output\BufferedOutput;

class SubscriptionController extends Controller
{
    protected $apiLicenseUrl;
    protected $apiDownloadUrl;
    protected $currentVersion = [0=>''];
    protected $newVersion = false;
    protected $maxVersion = [0=>''];
    protected $lastVersion = [];
    protected $maxCoreVersion = '';
    protected $lastCoreVersion = '';
    protected $instaledTypes = [];
    protected $seconds = 86400;
    protected $availableVersion = [];
    protected $checkUpdates = false;
    protected $subscriptionApiCache = false;
    protected $localModules = false;

    public function __construct()
    {
        $this->apiLicenseUrl = config('app.api_license_url');
        $this->apiDownloadUrl = config('app.api_download_url');

        try {
            $this->subscriptionApiCache = Cache::store('file')->get('subscription_api');
        }
        catch (Exception $e) {}
    }

    public function index(Request $request, $idModule = false)
    {
        if (!auth()->user()->hasPermission('admin.subscription.index')) {
            return redirect_no_permission();
        }

        $coreUpdate = $responseParams = new \stdClass();
        $modules = $this->getAll($request);

        foreach($modules as $module) {
            $this->instaledTypes[] = $module->type;
        }

        if ($this->subscriptionApiCache) {
            $response = $this->subscriptionApiCache;
        }
        else {
            $response = $this->connectApi($request, 'verify', $idModule);
        }

        $licenseUpdate = isset($response->data->license->update_at) ? $response->data->license->update_at : null;

        if (!empty($response->data->license->updates)) {
            $checkCoreUpdates = $this->checkResponseForUpdates($response->data->license->updates, $response->data->license->updates, false, true);
            $coreUpdate = $checkCoreUpdates->viewModule;
            $check = check_expire($licenseUpdate);
            $coreUpdate->maxUpdateVersion = $coreUpdate->maxVersion;
            if ($check->isExpire) {
                $versionExplode = explode('.', config('app.version'));

                foreach ($coreUpdate->versions as $idv => $v) {
                    $newVewsionExplode = explode('.', $v->version);

                    if((int)$versionExplode[0] === (int)$newVewsionExplode[0]
                        && (int)$versionExplode[1] === (int)$newVewsionExplode[1]
                        && (int)$versionExplode[2] < (int)$newVewsionExplode[2]
                    ) {
                        $versionExplode[2] = $newVewsionExplode[2];
                        $coreUpdate->maxUpdateVersion = $v->version;
                    }
                }
            }
        }

        if (!empty($response->data->modules)) {
            $responseModules = $response->data->modules;
            foreach ($responseModules as $id => $module) {
                if (!empty($module->license->mode)) {
                    $type = $module->type;
                    $checkUpdates = $this->checkResponseForUpdates($response->data->modules[$id], $module, $id, true);
                    $response->data->modules[$id] = $checkUpdates->viewModule;
                    $responseParams->$type = $responseModules[$id];
                }
            }

            for ($i = 0; $i <= count($modules) - 1; $i++) {
                $type = $modules[$i]->type;
                $dif = null;
                $params = !empty($responseParams->$type) ? $responseParams->$type : null;
                $modules[$i]->available_version = !empty($params->lastVersion) ? $params->lastVersion : null;
                $modules[$i]->max_version = !empty($params->maxVersion) ? $params->maxVersion : null;
                $modules[$i]->versions = !empty($params->versions) ? $params->versions : null;
                $modules[$i]->free = !empty($params->free) ? $params->free : 0;
                $modules[$i]->pro = !empty($params->pro) ? $params->pro : 0;
                $modules[$i]->trial = !empty($params->trial) ? $params->trial : false;

                if (null !== $params) {
                    $checkLicense = check_expire($params->license->expire_at);
                    if ($checkLicense->isExpire) {
                        $modules[$i]->errors['module_license_expired'] = true;
                    }

                    $checkLicense = check_expire($params->license->support_at);
                    if ($checkLicense->isExpire) {
                        $modules[$i]->errors['module_support_expired'] = true;
                    }

                    $checkLicense = check_expire($params->license->update_at);
                    if ($checkLicense->isExpire) {
                        $modules[$i]->errors['module_update_expired'] = true;
                    }
                }

                if ($modules[$i]->available_version) {
                    $this->availableVersion[$type] = $modules[$i]->available_version;
                }

                if ($params !== null) {
                    $this->updateModuleSubscription($modules[$i], \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($params), true), $params);
                }
            }

            $request->system->modules = $modules;
            $this->setNewVersions();

            foreach ($response->data->modules as $key => $module) {
                if (!in_array($module->type, $this->instaledTypes)) {
                    $module->status = $module->id = 0;
                    $module->version = $module->max_version = $module->available_version = null;
                    $modules[] = $module;
                }
            }
        }

        return view('subscription.index', [
            'modules' =>$modules,
            'newVersionsIsset' => $this->newVersion,
            'newVersions' => $this->lastVersion,
            'maxCoreVersion' => $this->maxCoreVersion,
            'coreUpdate' => $coreUpdate,
            'client' => !empty($response->data->client) ? $response->data->client : [],
            'licenseKey' => isset($response->data->license->license_key) ? $response->data->license->license_key : '',
            'licenseExpire' => isset($response->data->license->expire_at) ? $response->data->license->expire_at : null,
            'licenseSupport' => isset($response->data->license->support_at) ? $response->data->license->support_at : null,
            'licenseUpdate' => $licenseUpdate,
            'licenseErrorMsg' => isset($response->status) && $response->status != 'OK' ? $response->message : '',
        ]);
    }

    public function verify(Request $request, $cronCheck = false, $data = false, $mergeData = false)
    {
        clear_cache('cache');
        settings_save('eto_cron.running', 1);

        $checkUpdates = $request->method() == 'POST';
        $this->checkUpdates = $checkUpdates;

        $response = $this->connectApi($request, 'verify', false, $data, $mergeData);

        if (!empty($response->status) && $response->status == 'response_fail') {
            $resp = ['status' => false, 'new_versions' => $this->newVersion, 'message' => $response->message, 'code' => $response->code];
            settings_save('eto_cron.running', 0, 'system', 0, true);

            if ($cronCheck) {
                return (object)$resp;
            }
            else {
                return response()->json($resp, 200);
            }
        }
        if (!empty($response->data->license)) {
            if (isset($response->data->license->core->news)) {
                settings_save('eto.news.count', $response->data->license->core->news->count, 'subscription', $request->system->subscription->id);
            }

            if ($subscription = Subscription::find($request->system->subscription->id)) {
                $subscription->params = $response->data->license->core;
                $subscription->hash = md5($response->data->license->license_key . json_encode($response->data->license->core) . $response->data->license->expire_at);
                $subscription->expire_at = $response->data->license->expire_at;
                $subscription->support_at = $response->data->license->support_at;
                $subscription->update_at = $response->data->license->update_at;
                $subscription->save();

                settings_save('eto.last_verify', \Carbon\Carbon::now()->toDateString());
                $expiresAt = \Carbon\Carbon::now()->addDays(config('eto_cron.update.interval'));
                $domain = get_domain();

                Cache::forget(md5($domain));
                Cache::put(md5($domain . '_last_verify'), \Carbon\Carbon::now()->toDateString(), $expiresAt);
            }
        }

        if (!empty($response->data->license->updates)) {
            $this->checkResponseForUpdates($response->data->license->updates, $response->data->license->updates, false);
        }

        if (!empty($response->data->modules)) {
            $responseModules = $response->data->modules;

            $localModules = [];
            $this->getAll($request);
            foreach ($this->localModules as $idlm => $lmodule) {
                $localModules[$lmodule->type] = $lmodule;
            }

            foreach ($responseModules as $id => $module) {
                if (!empty($module->license->mode)) {
                    $localModule = !empty($localModules[$module->type]) ? $localModules[$module->type] : null;

                    if (!empty($localModule->subscriptions) && count($localModule->subscriptions) > 0) {
                        $this->checkResponseForUpdates($localModule, $module, $id);
                    }
                }
            }
        }

        $this->setNewVersions();
        settings_save('eto_cron.running', 0, 'system', 0, true);

        if ($checkUpdates) {
            return response()->json(['status' => true, 'new_versions' => $this->newVersion], 200);
        }

        if (!$response) {
            return false;
        }
        return (object)['status' => true, 'new_versions' => $this->newVersion];
    }

    public function install(Request $request) {
        if (!auth()->user()->hasPermission('admin.subscription.install')) {
            return redirect_no_permission();
        }

        $localModule = Module::with([
            'subscriptions' => function($query) use($request) {
                $query->where('subscription_id', $request->system->subscription->id);
            }
        ])->where('type', $request->type)->first();

        if (!empty($localModule->subscriptions) && count($localModule->subscriptions) > 0) {
            return response()->json(['status'=>false, 'message'=>'The license you have provided is already installed in this application'], 200);
        }

        $response = $this->connectApi($request, 'install', $request->type);

        if (!empty($response->status)) {
            if ($response->status != 'OK' && !empty($response->data)) {
                return response()->json(['status' => false, 'message' => $response->message], 200);
            }

            if (!empty($response->data->modules)) {
                $module = $response->data->modules[0];

                if ($request->type == $module->type && ($module->license->isExpire === false || $module->free == 1) && count($module->versions) > 0) {
                    for ($i = count($module->versions) - 1; $i >= 0; $i--) {
                        $module->versions[$i]->fails = !empty($module->versions[$i]->requirements) ? $this->checkRequirements($module->versions[$i]->requirements) : new \stdClass();
                    }

                    $lastVersion = end($module->versions);
                    $module->lastVersion = $lastVersion->version;
                    $prarams = $this->parseData($module);
                    $this->addModule($module, $module->lastVersion, $prarams);

                    Cache::store('file')->forget('subscription_api');
                    Cache::forget(md5(get_domain()));
                    return response()->json(['status' => true, 'message' => trans('subscription.message.moduleInstalled')], 200);
                }
                elseif ($module->license->isExpire === true) {
                    return response()->json(['status' => false, 'message' => trans('subscription.message.module_license_expired')], 200);
                }
            }
        }

        Cache::store('file')->forget('subscription_api');
        return response()->json([
            'status' => 'invalid_data',
            'message' => 'The license code is incorrect',
        ], 200);
    }

    public function uninstall(Request $request) {
        if (!auth()->user()->hasPermission('admin.subscription.install')) {
            return redirect_no_permission();
        }

        Cache::store('file')->forget('subscription_api');

        $localModule = Module::with([
            'subscriptions'=>function($query) use($request) {
                $query->where('subscription_id', $request->system->subscription->id);
            }
        ])->where('type', $request->type)->first();

        if (empty($localModule->type)) {
            return response()->json(['status'=>false, 'message'=>trans('subscription.message.not_instaled_local')], 200);
        }

        if (count($localModule->subscriptions) > 0) {
            $subscriptionModule = SubscriptionModule::find($localModule->subscriptions[0]->id)->where('module_id', $localModule->id);
            $subscriptionModule->delete();

            $subscriptionsForModule = SubscriptionModule::where('module_id', $localModule->id)->get();

            if (count($subscriptionsForModule) === 0) {
                $localModule->delete();
            }

            Cache::forget(md5(get_domain()));
            return response()->json(['status' => true, 'message' => trans('subscription.message.uninstalled')], 200);
        }
        return response()->json(['status'=>false, 'message'=> trans('subscription.message.uninstallFail')], 200);
        // after the notification system has been created, information about the module uninstallation will be sent in the API
        // $response = $this->connectApi($request, 'uninstall', $moduleLocal->id);
        // $message = !empty($response->message) ? implode('<br>', $response->message) : '';
    }

    public function changeStatus(Request $request) {
        if (!auth()->user()->hasPermission('admin.subscription.install')) {
            return redirect_no_permission();
        }

        if ($request->type != '' && $request->status != '') {
            $localModule = Module::with([
                'subscriptions'=>function($query) use($request) {
                    $query->where('subscription_id', $request->system->subscription->id);
                }
            ])->where('type', $request->type)->first();

            if (!empty($localModule->subscriptions) && count($localModule->subscriptions) > 0) {
                $subscriptionModule = SubscriptionModule::find($localModule->subscriptions[0]->id);
                $subscriptionModule->status = $request->status;
                if ($subscriptionModule->save()) {
                    return response()->json(['status' => true, 'message' => ''], 200);
                }
            }
        }

        Cache::forget(md5(get_domain()));

        return response()->json(['status'=>false, 'message'=> trans('subscription.message.invalidData')], 200);
    }

    public function connectApi($request, $uri, $idmodule = false, $data = false, $mergeData = false)
    {
        $responseCode = 200;
        $error = '';

        try {
            $headers = $this->getRequestData($request, $uri, $idmodule, $data, $mergeData);
        }
        catch (Exception $e) {
            $responseCode = $e->getCode();
            $error = $e->getMessage();
            \Log::error([$error,$responseCode]);
            return false;
        }

        if ($headers) {
            try {
                $url = $uri != 'verify' && $uri != 'install-core' ? $this->apiDownloadUrl : $this->apiLicenseUrl;
                $client = new Client();
                $result = $client->post($url . $uri, $headers);
            }
            catch (Exception $e) {
                $responseCode = $e->getCode();
                $error = $e->getMessage();
                \Log::error([$error,$responseCode]);
            }
        }

        if (isset($result) && !empty($result)) {
            $body = $result->getBody();

            if ($uri != 'update') {
                try {
                    $json = $body->getContents();
                    $data = json_decode($json);
                }
                catch (Exception $e) {
                    $responseCode = $e->getCode();
                    $error = $e->getMessage();
                    \Log::error([$error,$responseCode]);
                }

                if (!empty($data->data->modules)) {
                    $this->subscriptionApiCache = $data;
                    Cache::store('file')->put('subscription_api', $data, $this->seconds);
                }
                else {
                    Cache::store('file')->forget('subscription_api');
                }

                if (isset($data) && !empty($data) && null !== $data) {
                    return $data;
                }
            }
            else {
                $serverResponse = new \stdClass();
                $serverResponse->headers = new \stdClass();
                $serverResponse->__headers = $result->getHeaders();
                $serverResponse->body = $body->getContents();

                foreach($serverResponse->__headers as $key=>$header) {
                    $serverResponse->headers->$key = $header[0];
                    if ($key == 'Content-Disposition') {
                        $data = explode('=',$header[0]);
                        $serverResponse->headers->fileName = !empty($data[1]) ? $data[1] : '';
                    }
                }

                unset($serverResponse->__headers);
                return $serverResponse;
            }
        }
        if ($uri == 'verify') {
            return (object)['status' => 'response_fail', 'message' => $error, 'code' => $responseCode];
        }

        return false;
    }

    protected function getRequestData($request, $uri, $idmodule = false, $data = false, $mergeData = false) {
        $coreVersion = config('eto.db_version') ?: config('app.version');

        if (version_compare($coreVersion, config('app.version'), '>')) {
            $coreVersion = config('app.version');
        }

        $licensesToRequest = [
            'license_key' => $request->system->subscription->license,
            'core_version' => $coreVersion,
            'core_type' => 'eto',
            'root_url' => url('/').'/',
            'php' => PHP_VERSION,
            'company_name' => config('app.name'), // config('site.company_name')
        ];

        if ($request->type == 'eto' && !empty($request->maxVersion)) {
            $licensesToRequest['core_max_version'] = $request->maxVersion;
        }

        if (!empty($request->installation_type)) {
            $licensesToRequest['installation_type'] = $request->installation_type;
        }

        if (!$data || ($data && $mergeData)) {
            if ($uri != 'install') {
                if (!empty($request->system->modules)) {
                    if ($idmodule === false) {
                        $modules = $request->system->modules;
                        if (!$modules) {
                            $modules = Module::with([
                                'subscriptions' => function ($query) use ($request) {
                                    $query->where('subscription_id', $request->system->subscription->id);
                                }
                            ])->get();
                        }
                    }
                    else {
                        $modules = new \stdClass();
                        foreach ($request->system->modules as $sModule) {
                            if ($sModule->type == $idmodule) {
                                $modules = [$sModule];
                            }
                        }
                    }
                    foreach ($modules as $id => $module) {
                        $this->currentVersion[$id] = $module->version;
                        $licensesToRequest['modules'][] = [
                            'version' => $module->version,
                            'max_version' => !empty($this->maxVersion[$id]) ? $this->maxVersion[$id] : '',
                            'type' => $module->type,
                        ];
                    }
                }

                if (($data && $mergeData)) {
                    $licensesToRequest = array_merge($licensesToRequest, $data);
                }
            }
            else {
                $licensesToRequest['modules'][] = [
                    'version' => '',
                    'max_version' => '',
                    'type' => $request->type,
                ];
            }
        }
        else {
            $licensesToRequest = $data;
        }

        $headers = [
            'stream' => true,
            'connect_timeout' => 10,
            'read_timeout' => 10,
            "decode_content" => true,
            "verify" => false,
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'X-Forwarded-For' => $_SERVER['REMOTE_ADDR'],
                'referer' => $request->system->subscription->domain,
                'etogetall' => 0,
                'globalparent' => 'eto'
            ],
            'form_params' => $licensesToRequest,
        ];

        if ($uri == 'verify' && $idmodule === false) {
            $headers['headers']['etogetall'] = 1;
        }

        if ($this->checkUpdates === true ) {
            $headers['headers']['checkupdates'] = 1;
        }

        return $headers;
    }

    protected function checkRequirements($requirements) {
        $validation = [];
        $reqClass = new RequirementsController();
        foreach($reqClass->required as $name=>$params) {
            if (isset($requirements->$name)) { $params = (array)$requirements->$name; }

            if ($params['method'] == 'compare') {
                $phpVersion = count(explode('.', PHP_VERSION)) === 2 ? PHP_VERSION . '.0' : PHP_VERSION;
                $fail = $reqClass->etoCompare($name, $phpVersion, $params['min'], isset($params['max']) ? $params['max'] : null);
                if ($fail !== true) {$validation[] = $fail;}
            }
            if ($params['method'] == 'ini') {
                $fail = $reqClass->etoIni($name);
                if ($fail !== true) {$validation[] = $fail;}
            }
            if ($params['method'] == 'extension') {
                $fail = $reqClass->etoExtension($name);
                if ($fail !== true) {$validation[] = $fail;}
            }
            if ($params['method'] == 'class') {
                $fail = $reqClass->etoClass($name);
                if ($fail !== true) {$validation[] = $fail;}
            }
            if ($params['method'] == 'function') {
                $fail = $reqClass->etoFunction($name);
                if ($fail !== true) {$validation[] = $fail;}
            }
        }
        $notifications = $reqClass->etoDisableFunctions();

        return ['validation' => $validation, 'notifications' => $notifications];
    }

    protected function checkResponseForUpdates($localModule, $module, $id, $viewModule = false) {
        if (count($module->versions) > 0 && (in_array($module->type, $this->instaledTypes) || $module->type == 'eto')) {
            $parseVersions = $this->parseVersions($module->versions, $id);
            $module->versions = $parseVersions['versions'];
            $module->conditions = $this->parseConditions($parseVersions);
            $module->noUpdates = $parseVersions['noUpdates'];

            if ($viewModule === true) {
                $localModule->versions = $module->versions;
                $localModule->maxVersion = $parseVersions['maxVersion'];
                $localModule->lastVersion = $parseVersions['lastVersion'];
                $localModule->params->conditions = $module->conditions;
                $localModule->params->noUpdates = $module->noUpdates;
            }

            if ($parseVersions['noUpdates'] === false && empty($module->errors['module_update_expired'])) {
                $this->newVersion = true;
                $this->availableVersion[$module->type] = $parseVersions['maxVersion'];
            }
        }

        $params = $this->parseData($module);
        $this->updateModuleSubscription($localModule, $params, $module);
        if ($viewModule === true) {
            return (object)['viewModule'=>$localModule, 'module'=>$module];
        }
        return $module;
    }

    protected function parseVersions($versions, $id) {
        $maxVersion = $lastVersion = '';
        $reverseVersions = [];
        $conditions = [];
        $fails = ['validation' => [], 'notifications'=>''];
        $ii = 0;
        for ($i = count($versions) - 1; $i >= 0; $i--) {
            // if (!empty($versions[$i]->changelog)) {
            //     $versions[$i]->changelog = \App\Helpers\SiteHelper::nl2br2($versions[$i]->changelog);
            // }
            $ii++;
            if (!empty($versions[$i]->requirements)) {
                $versions[$i]->fails = $this->checkRequirements($versions[$i]->requirements);
                if (count($versions[$i]->fails['validation']) > 0){
                    $fails['validation'][] = '<b>Fails for version '.$versions[$i]->version.':</b>';
                    foreach($versions[$i]->fails['validation'] as $fail) { $fails['validation'][] = $fail; }
                }

                $fails['notifications'] .= !is_bool($versions[$i]->fails['notifications']) ? 'Notifications for version '.$versions[$i]->version.':' . $versions[$i]->fails['notifications'] : '';
            }
            $reverseVersions[$ii] = $versions[$i];

            if (!empty($reverseVersions[$ii]->conditions) && (empty($conditions) || !in_array($reverseVersions[$ii]->conditions, $conditions))) {
                $conditions[] = $versions[$ii]->conditions;
            }
        }
        $versions = $reverseVersions;

        for ($i = count($versions); $i > 0; $i--) {
            if (empty($versions[$i]->fails) || (!empty($versions[$i]->fails) && count($versions[$i]->fails['validation']) === 0)) {
                $maxVersion = $versions[$i]->version;
            }
            $lastVersion = $versions[$i]->version;
        }

        if ($id === false) {
            $this->maxCoreVersion = $maxVersion;
            $this->lastCoreVersion = $lastVersion;
        }
        else {
            $this->maxVersion[$id] = $maxVersion;
            $this->lastVersion[$id] = $lastVersion;
        }

        return [
            'versions' => $versions,
            'conditions' => $conditions,
            'lastVersion' => $lastVersion,
            'maxVersion' => !empty($maxVersion) ? $maxVersion : '',
            'fails' => $fails,
            'noUpdates' => empty($maxVersion)
        ];
    }

    public function migrateAndClearAfterUpgrade(Request $request) {
        if (!empty($request->type) && maintenance_mode('check')) {
            if ($this->runMigrations()) {
                settings_save('eto.db_version', config('app.version'));

                if (config('eto.available_versions')) {
                    settings_delete('eto.available_versions.*');
                }

                maintenance_mode('unblock');
                clear_tmp();
                $this->verify($request);
                return response()->json(['status' => true], 200);
            }
        }
        return response()->json(['status' => false], 403);
    }

    public function runMigrations() {
        $outputLog = new BufferedOutput;

        try {
            Artisan::call('migrate', ['--force' => true], $outputLog);
        }
        catch (Exception $e) {
            \Log::error('Module update (runMigrations): '. $e->getMessage());
            return false;
        }

        clear_cache();
        return true;
    }

    protected function parseData($data) {
        $prarams = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($data), true);

        if (!$prarams) { return false; }
        unset($prarams['id']);
        unset($prarams['type']);
        return $prarams;
    }

    protected function parseConditions($parseVersions) {
        $conditions =
            count($parseVersions['fails']['validation']) > 0
                ? implode('<br>', $parseVersions['fails']['validation']) . '<br>'
                : '';
        $conditions .=
            !empty($parseVersions['fails']['notifications'])
                ? $parseVersions['fails']['notifications'].'<br>'
                : '';
        $conditions .=
            !empty($parseVersions['conditions'])
                ? '<br>'. implode('<br>', $parseVersions['conditions'])
                : '';
        return $conditions;
    }

    protected function updateModuleSubscription($localModule, $params, $module) {
        $request = request();
        if (!empty(['params'])) {
            if (!is_subclass_of($localModule, '\Illuminate\Database\Eloquent\Model')) {
                $localModule = Module::with([
                    'subscriptions'=>function($query) use($request) {
                        $query->where('subscription_id', $request->system->subscription->id);
                    }
                ])->find($localModule->id);
            }
            if (!$localModule) {
                return false;
            }
            unset($params['params']['conditions']);
            unset($params['params']['noUpdates']);
            $localModule->params = $params['params'];
            try {
                $localModule->save();
            }
            catch (Exception $e) {}
        }

        if (count($localModule->subscriptions) === 0 ) {
            return false;
        }

        if (isset($module->license)) {
            $subscriptionModule = SubscriptionModule::find($localModule->subscriptions[0]->id);
            $subscriptionModule->params = $module->license;
            $subscriptionModule->hash = md5($request->system->subscription->license . json_encode($module->license) . $module->license->expire_at);
            $subscriptionModule->install_at = !empty($module->license->install_at) ? $module->license->install_at : Carbon::now();
            $subscriptionModule->expire_at = $module->license->expire_at;
            $subscriptionModule->support_at = $module->license->support_at;
            $subscriptionModule->update_at = $module->license->update_at;

            try {
                $subscriptionModule->save();
            }
            catch (Exception $e) {}
        }
        return true;
    }

    protected function setNewVersions() {
        if (count($this->availableVersion) > 0) {
            foreach($this->availableVersion as $item=>$count) {
                if (!config('eto.available_versions.'.$item)
                    || (config('eto.available_versions.'.$item)  && (int)config('eto.available_versions.'.$item) !== (int)$count)
                ) {
                    settings_save('eto.available_versions.' . $item, $count);
                }
            }
        } else {
            settings_delete('eto.available_versions.*');
        }
    }

    protected function addModule($apiModule, $lastVersion = '', $params = []) {
        if (empty($apiModule->type)) {return false;}
        $request = request();
        $module = Module::where('type', $apiModule->type)->first();
        if (!$module) {
            $parentId = $this->getParentId($apiModule->type);
            $module = new Module();
            $module->parent_id = $parentId;
            $module->name = $apiModule->name;
            $module->type = $apiModule->type;
            $module->version = $lastVersion;
            $module->status = 1;
            $module->description = $apiModule->description;
            unset($params['params']['conditions']);
            unset($params['params']['noUpdates']);
            $module->params = $params['params'];
            $module->save();

            // here will be the initiation of unpacking the new module files
        }
        $subscriptionModule = new SubscriptionModule();
        $subscriptionModule->subscription_id = $request->system->subscription->id;
        $subscriptionModule->module_id = $module->id;
        $subscriptionModule->status = 1;
        $subscriptionModule->params = $apiModule->license;

        // \Log::info([$request->system->subscription->license . json_encode($apiModule->license) . $apiModule->license->expire_at]);

        $subscriptionModule->hash = md5($request->system->subscription->license . json_encode($apiModule->license) . $apiModule->license->expire_at);
        $subscriptionModule->install_at = !empty($apiModule->license->install_at) ? $apiModule->license->install_at : Carbon::now();
        $subscriptionModule->expire_at = $apiModule->license->expire_at;
        $subscriptionModule->support_at = $apiModule->license->support_at;
        $subscriptionModule->update_at = $apiModule->license->update_at;
        $subscriptionModule->save();
        return $module;
    }

    protected function getParentId($type) {
        $typeArr = explode('.', $type);
        $parentId = 0;
        if (count($typeArr) > 1) {
            $last = end($typeArr);
            $typeParent = preg_replace("/\.". $last . "$/", '',  $type);
            $parent = Module::where('type', $typeParent)->first();
            if (!empty($parent->id)) {
                $parentId = $parent->id;
            }
        }
        return $parentId;
    }

    public function setETOLicense(Request $request) {
        if (!defined('ETO_SESSIONS_TABLE_EXISTS')) {
            return response()->view('errors.db');
            //  return response()->view('errors.db');
        }

        $currentSite = Site::where('domain', get_domain())->first();
        $isCorrupted = false;

        // if (empty(Subscription::all()->count())) {
        //     $isCorrupted = false;
        // }
        // elseif ($currentSite) {
        //     abort(404);
        // }

        // if ($isCorrupted) {
        //     return view('errors.corrupted');
        // }
        return view('subscription.license');
    }

    public function installETO($license = null) {
        $request = request();

        // if ((!empty($request->system->subscription) && !empty($request->system->subscription->params) &&
        //     $request->system->subscription->params->domainInstallation == $_SERVER['HTTP_HOST']) && empty(session('installer_app_license'))) {
        //     abort(404);
        // }

        $this->runMigrations();

        // if ($request->method() == 'POST' && $license == null)  {
        //     $license = $request->license;
        // }

        session()->forget('installer_app_license');

        $data = [
            'license_key' => 'eyJpdiI6IjRPUTBvNzVieDY0YU5iZVlSd3Z5MHc9PSIsInZhbHVlIjoiNVh2YVBVZGlySmVzZGxCSWRBZ1FTZVwvZkwwVUhMSHBtSXBnSTJJYVwvXC81Yz0iLCJtYWMiOiI4NDI2YTczOGYxNmZhMzc0YjAxNDYxOGFlNGZhZjM1NzkwNDJhNGU0NjIwZWZiMzAzZGM3NWZhMGFiYzE5OTgxIn0=',
            'installation_type' => 'core',
            'core_type' => 'eto',
            // 'root_url' => url('/').'/',
            // 'php' => PHP_VERSION,
            // 'company_name' => config('app.name'), // config('site.company_name')
        ];

        // $response = $this->connectApi(request(), 'install-core', $license, $data);

        // if (!empty($response->status)) {
        //     if ($response->status != 'OK' && !empty($response->data)) {
        //         return response()->json(['status' => false, 'message' => $response->message], 200);
        //     }
            // if (!empty($response->data->license)) {
                $companyName = !empty($request->company_name) ? $request->company_name : '';

                if ($companyName) {
                    $unique_id = str_replace('-', '', str_slug($companyName));
                }
                else {
                    $unique_id = uniqid();
                }

                $subscription = new Subscription();
                $subscription->unique_id = $unique_id;
                $subscription->domain = get_domain();
                $subscription->license = "eyJpdiI6IjRPUTBvNzVieDY0YU5iZVlSd3Z5MHc9PSIsInZhbHVlIjoiNVh2YVBVZGlySmVzZGxCSWRBZ1FTZVwvZkwwVUhMSHBtSXBnSTJJYVwvXC81Yz0iLCJtYWMiOiI4NDI2YTczOGYxNmZhMzc0YjAxNDYxOGFlNGZhZjM1NzkwNDJhNGU0NjIwZWZiMzAzZGM3NWZhMGFiYzE5OTgxIn0=";
                $subscription->status = 1;
                $subscription->params = "eyJpdiI6IjFkdXNtM2Q1bHVEN0Naa3ZMYUZDekE9PSIsInZhbHVlIjoiNTRtMXlWamFya0daeU5IaDBYSmVZT1JMSmhYdFVXUFNjakF4ellMVVJ6UWVcL2NsMnBQSFlsanBcL2pmYm1SRSt1bjVtVjB5ZU5XWmRSN0hrZ0xKVWFpUWFSRktROTR5OWFDc0kwMG5hM2lwVzB1RGpMUFE0T0dzem9yblpCQUVpaE5TenVneXBcLzhPVFFWNkxXQlU4Q3NnNmlHejc0bHo1RVJHVkt4MWpIWXJxU1lxZXhcL3ZVWEFtc2JQbGtXek1taE9IVCt3b2FOOUx5bjgxNHRwOGYzN1VYT3VuZDhPRk9nMVwvMTVjWnFwOHBEM3lFaFZrNDRwZjEwOWFGTlg5YVwvUWJiR0lrT3Ira3FcLzhJY0tYWmhRbFNKZEo1MHF0am1Ed0R4U1R0ZFFXRlRIaVhUVTZHd29qYU9JRTZFRUFydGx3eVEweVhEekMxY3hrRUlqV0ZSdlB0Vml4eG5lRWJGNVlIekJvWU1pQTMySXRtZTRGaFpYY21zdWp0eEdYM0NlMis3VmNVbGRsMkJEajlcL0ZlYlFhZmFzU25qM1RMZVdBNXQzdWtVNlhKYnh4QVVIZm1Ha1FZMkJtRVN1bXFoNWlkNnhhdFhFQlVCbzJPM3h0Z1hjZ2pxOXl4UzBkQW5RXC9ZWTBRdlhTRnRYT1hhdUxaMlprYXc1QW04ODBZV05xK09zTnI1U0dxaEpXeDMwK1c0UlJsYTI0UDRIZXNRZ1dLZUltaFBhck16RmlTXC9uU25cL0loUFNvWXg5aVU5RUNSVWFOdUpKRmdLRGwrdGJ4cGtaTUJ0Zmd3YXRuOHQzM0g3UFwvVlRvUTM5dkhnV00yc1lYKzFZWXFQRkdzd2NIM1RSbDFsWlNPN2JnbGwyWngzZm1hdHkya3dIZXJuQkZrMVJmcGt0c3ZUcThEeFU4R3B0QnA5Z01pRjBacHg3T1BmMkpCMVhrd1dTcmh2aE44OU5cL2lcL0tpTTcwUEFWeUxiRDdwNXRNS0RvWnJ5bTNuQ0xScFwvUEpBYWxRYjZwanl6bCtxdlVlOVBJaGsydWU0UnpMY2ZHbnVoeXRRVVwvRlZ2QnFTbkhHTDkyUUhnODYxZjgwMWFlV1FQZStLU0pkcUFWUU9ZcmtoY2VXUU81V1Vha0tIZEpGRGVRVko3Q1FiK0dVdXViVkIzbHQ0ejNhM0g0aUNpTGJJN1BNTWRuQU5RenBJY1VONjl2Y3hVVzJ2OFcrSUJobE5SSTJqMjVKN1Zya2ZRU0NOTUpZazdQaz0iLCJtYWMiOiI2MTZkYjc2MDZhYTIwZmQ4YTY2YThkODc0ZGJjNmNjMmM1NzM0MzU1ZWYzYTMxZTU2NDQ3N2ZlZGJkZmM0MzhkIn0=";
                $subscription->hash = md5("eyJpdiI6IjRPUTBvNzVieDY0YU5iZVlSd3Z5MHc9PSIsInZhbHVlIjoiNVh2YVBVZGlySmVzZGxCSWRBZ1FTZVwvZkwwVUhMSHBtSXBnSTJJYVwvXC81Yz0iLCJtYWMiOiI4NDI2YTczOGYxNmZhMzc0YjAxNDYxOGFlNGZhZjM1NzkwNDJhNGU0NjIwZWZiMzAzZGM3NWZhMGFiYzE5OTgxIn0=" . json_encode($response->data->license->core) . $response->data->license->expire_at);
                // $subscription->expire_at = $response->data->license->expire_at;
                // $subscription->update_at = $response->data->license->update_at;
                // $subscription->support_at = $response->data->license->support_at;
                $subscription->save();

                $request->system->subscription->license = $subscription->license;
                $request->system->subscription->id = $subscription->id;
                $sites = Site::all();

                foreach($sites as $site) {
                    $site->subscription_id = $subscription->id;
                    $site->domain = $subscription->domain;
                    $site->save();
                }

                // Update subscription id for all unassigned settings
                \App\Models\Setting::where('relation_type', 'subscription')->where('relation_id', 0)->update(['relation_id' => $subscription->id]);
            // }
        // }

        // if (!empty($request->app_url_schema)) {
        //     foreach($sites as $site) {
        //         if (!empty($site->id)) {
        //             Config::updateOrCreate(['site_id' => $site->id, 'key' => 'force_https'], [
        //                 'site_id' => $site->id,
        //                 'key' => 'force_https',
        //                 'type' => 'int',
        //                 'browser' => 0,
        //                 'value' => $request->app_url_schema ?: 0,
        //             ]);
        //         }
        //     }
        // }

        // if (!empty($request->send_welcome_mail) && (int)$request->send_welcome_mail === 1 && !empty($request->app_email)) {
        //     try {
        //         $sent = \Mail::send([
        //             'html' => 'emails.install_welcome',
        //         ], [
        //             'licenseKey' => $request->app_license,
        //             'appName' => $request->app_name,
        //             'email' => $request->app_email,
        //             'password' => $request->app_password,
        //             'additionalMessage' => '',
        //         ],
        //         function ($message) use ($request) {
        //             $message->from($request->app_email)
        //                 ->to($request->app_email)
        //                 ->subject(trans('emails.install_welcome.subject', ['name' => $request->app_name ]));
        //         });
        //     }
        //     catch (\Exception $e) {
        //         $error = $e->getMessage();
        //         \Log::info(['installETO - welcome email error:', $error]);
        //     }
        // }

        // if (empty($request->reload_invalid_license)) {
        //     $this->permissionFiles();
        //     if (!empty($request->database_connection)) {
        //         return $response;
        //     } else {
        //         return response()->json($response);
        //     }
        // }
        // elseif (!empty($request->reload_invalid_license) && $request->reload_invalid_license == 2) {
        //     return response()->json($response);
        // }
        // else {
        //     return redirect(redirect_to());
        // }
    }

    protected function permissionFiles() {
        $pathname = base_path();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathname), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $name = basename($item);

            if ($name != '.' && $name != '..' && strpos($item, '.git') === false && strpos($item, '.idea') === false && strpos($item, 'node_modules') === false) {
                if (is_dir($item)) {
                    @chmod($item, 0755);
                }
                else if (is_file($item)) {
                    @chmod($item, 0644);
                }
            }
        }

        @unlink(base_path('tmp/eto.json'));
        @unlink(base_path('installer.php'));
    }

    public function corruptedETOLicense() {
        return view('errors.corrupted');
    }

    public function expiredETOLicense() {
        return view('errors.expired');
    }

    protected function prepareConnectApi($data, $uri) {
        try {
            $client = new Client();
            $result = $client->post($this->apiLicenseUrl . $uri, $data);
        }
        catch (Exception $e) {
            $responseCode = $e->getCode();
            $error = $e->getMessage();
            \Log::error([$error,$responseCode]);
        }

        if (isset($result) && !empty($result)) {
            try {
                $body = $result->getBody();
            }
            catch (Exception $e) {
                $responseCode = $e->getCode();
                $error = $e->getMessage();
                \Log::error([$error, $responseCode]);
            }
            if (isset($body)) {
                try {
                    $json = $body->getContents();
                    $response = json_decode($json);
                }
                catch (Exception $e) {
                    $responseCode = $e->getCode();
                    $error = $e->getMessage();
                    \Log::error([$error, $responseCode]);
                }
            }
        }
        if (!isset($response) || $response === false || null === $response) {
            return ['message'=>'Failed connection.'];
        }

        if (!empty($response->status)) {
            if ($response->status == 'OK') {
                clear_cache('cache');
                return true;
            }

            return ['message' => trans('installer.' . $response->status)];
        }
        else {
            return false;
        }
    }

    public function checkForInstallation($license) {
        $data = [
            'stream' => true,
            'connect_timeout' => 10,
            'read_timeout' => 10,
            'decode_content' => true,
            'verify' => false,
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'X-Forwarded-For' => $_SERVER['REMOTE_ADDR'],
                'referer' => get_domain(),
                'etogetall' => 0,
                'globalparent' => 'eto'
            ],
            'form_params' => [
                'license_key' => $license,
                'installation_type' => 'core',
                'core_type' => 'eto'
            ],
        ];

        return $this->prepareConnectApi($data, 'check-install-core');
    }

    public function activation($activation = 1, $license = false) {
        $request = request();
        $license = $license ?: $request->license;
        $data = [
            'stream' => true,
            'connect_timeout' => 10,
            'read_timeout' => 10,
            'decode_content' => true,
            'verify' => false,
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'X-Forwarded-For' => $_SERVER['REMOTE_ADDR'],
                'referer' => get_domain(),
                'etogetall' => 0,
                'globalparent' => 'eto'
            ],
            'form_params' => [
                'license_key' => $license,
                'core_type' => 'eto',
                'activation' => $activation
            ],
        ];

        if ($activation === 0) {
            $response = $this->prepareConnectApi($data, 'deactivation-license');
        } else {
            $response = $this->prepareConnectApi($data, 'activation-license');
        }
        return is_bool($response) ? $response : false;
    }

    protected function getAll(Request $request) {
        $this->localModules = $this->localModules ?: Module::with([
            'subscriptions'=>function($query) use($request) {
                $query->where('subscription_id', $request->system->subscription->id);
            }
        ])->get();

        return $this->localModules;
    }
}
