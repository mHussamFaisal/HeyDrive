<?php

namespace App\Http\Controllers\Modules;

use Closure;
use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Module;
use App\Models\Site;
use App\Models\Setting;
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
use ZanySoft\Zip\Zip;

class ModulesController extends Controller
{
    private $apiLicenseUrl;
    private $apiDownloadUrl;
    private $currentVersion = [0=>''];
    private $newVersion = false;
    private $maxVersion = [0=>''];
    private $lastVersion = [];
    private $maxCoreVersion = '';
    private $lastCoreVersion = '';
    private $instaledTypes = [];
    private $seconds = 86400;
    private $availableVersion = [];
    private $checkUpdates = false;

    public function __construct() {
        $this->apiLicenseUrl = config('app.api_license_url');
        $this->apiDownloadUrl = config('app.api_download_url');
    }

    public function index(Request $request, $idModule = false)
    {
        $coreUpdate = $responseParams = new \stdClass();
        $modules = $request->system->modules;

        foreach($modules as $module) {
            $this->instaledTypes[] = $module->type;
        }

        if (Cache::has('system_modules_api')) {
            $response = Cache::get('system_modules_api');
        }
        else {
            $response = $this->connectApi($request, 'verify', $idModule);
        }

        if (!empty($response->data->license->updates)) {
            $checkCoreUpdates = $this->checkResponseForUpdates($response->data->license->updates, $response->data->license->updates, false, true);
            $coreUpdate = $checkCoreUpdates->viewModule;
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

        return view('modules.list', [
            'modules' =>$modules,
            'newVersionsIsset' => $this->newVersion,
            'newVersions' => $this->lastVersion,
            'maxCoreVersion' => $this->maxCoreVersion,
            'coreUpdate' => $coreUpdate,
            'client' => !empty($response->data->client) ? $response->data->client : [],
            'licenseKey' => isset($response->data->license->license_key) ? $response->data->license->license_key : '',
            'licenseExpire' => isset($response->data->license->expire_at) ? $response->data->license->expire_at : null,
            'licenseSupport' => isset($response->data->license->support_at) ? $response->data->license->support_at : null,
            'licenseUpdate' => isset($response->data->license->update_at) ? $response->data->license->update_at : null,
            'licenseErrorMsg' => isset($response->status) && $response->status != 'OK' ? $response->message : '',
        ]);
    }

    public function verify(Request $request, $croneCheck = false)
    {
        $checkUpdates = $request->method() == 'POST';
        $this->checkUpdates = $checkUpdates;
        $response = $this->connectApi($request, 'verify', false);
        // dd($response, $request);

        if (!empty($response->status) && $response->status == 'response_fail') {
            $resp = ['status' => false, 'new_versions' => $this->newVersion, 'message' => $response->message, 'code' => $response->code];
            if ($croneCheck) {
                return (object)$resp;
            }
            else {
                return response()->json($resp, 200);
            }
        }

        Cache::forget('system_modules_api');

        if (!empty($response->data->license)) {
            $subscription = Subscription::find($request->system->subscription->id);
            if ($subscription) {
                $subscription->params = $response->data->license->core;
                $subscription->hash = md5($response->data->license->license_key . json_encode($response->data->license->core) . $response->data->license->expire_at);
                $subscription->expire_at = $response->data->license->expire_at;
                $subscription->support_at = $response->data->license->support_at;
                $subscription->update_at = $response->data->license->update_at;
                $subscription->save();
            }
        }
        if (!empty($response->data->license->updates)) {
            $this->checkResponseForUpdates($response->data->license->updates, $response->data->license->updates, false);
        }

        if (!empty($response->data->modules)) {
            $responseModules = $response->data->modules;
            foreach ($responseModules as $id => $module) {
                if (!empty($module->license->mode)) {
                    $localModule = Module::with(['subscriptions'=>function($query) use($request) {
                        $query->where('subscription_id', $request->system->subscription->id);
                    }])->where('type', $module->type)->first();

                    if (!empty($localModule->subscriptions) && count($localModule->subscriptions) > 0) {
                        $this->checkResponseForUpdates($localModule, $module, $id);
                    }
                }
            }
        }

        $this->setNewVersions();

        if ($checkUpdates) {
            return response()->json(['status' => true, 'new_versions' => $this->newVersion], 200);
        }

        if (!$response) {
            return false;
        }
        return true;
    }

    public function install(Request $request)
    {
        $localModule = Module::with(['subscriptions'=>function($query) use($request) {
            $query->where('subscription_id', $request->system->subscription->id);
        }])->where('type', $request->type)->first();

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

                    Cache::forget('system_modules_api');
                    return response()->json(['status' => true, 'message' => trans('modules.message.moduleInstalled')], 200);
                }
                elseif ($module->license->isExpire === true) {
                    return response()->json(['status' => false, 'message' => trans('modules.message.module_license_expired')], 200);
                }
            }
        }

        Cache::forget('system_modules_api');
        return response()->json([
            'status' => 'invalid_data',
            'message' => 'The license code is incorrect',
        ], 200);
    }

    public function uninstall(Request $request)
    {
        Cache::forget('system_modules_api');

        $localModule = Module::with(['subscriptions'=>function($query) use($request) {
            $query->where('subscription_id', $request->system->subscription->id);
        }])->where('type', $request->type)->first();

        if (empty($localModule->type)) {
            return response()->json(['status'=>false, 'message'=>trans('modules.message.not_instaled_local')], 200);
        }

        if (count($localModule->subscriptions) > 0) {
            $subscriptionModule = SubscriptionModule::find($localModule->subscriptions[0]->id)->where('module_id', $localModule->id);
            $subscriptionModule->delete();

            $subscriptionsForModule = SubscriptionModule::where('module_id', $localModule->id)->get();

            if (count($subscriptionsForModule) === 0) {
                $localModule->delete();
            }

            return response()->json(['status' => true, 'message' => trans('modules.message.uninstalled')], 200);
        }
        return response()->json(['status'=>false, 'message'=> trans('modules.message.uninstallFail')], 200);
        // after the notification system has been created, information about the module uninstallation will be sent in the API
        // $response = $this->connectApi($request, 'uninstall', $moduleLocal->id);
        // $message = !empty($response->message) ? implode('<br>', $response->message) : '';
    }

    public function changeStatus(Request $request)
    {
        if ($request->type != '' && $request->status != '') {
            $localModule = Module::with(['subscriptions'=>function($query) use($request) {
                $query->where('subscription_id', $request->system->subscription->id);
            }])->where('type', $request->type)->first();

            if (!empty($localModule->subscriptions) && count($localModule->subscriptions) > 0) {
                $subscriptionModule = SubscriptionModule::find($localModule->subscriptions[0]->id);
                $subscriptionModule->status = $request->status;
                if ($subscriptionModule->save()) {
                    return response()->json(['status' => true, 'message' => ''], 200);
                }
            }
        }
        return response()->json(['status'=>false, 'message'=> trans('modules.message.invalidData')], 200);
    }

    public function update(Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
        }
        catch (Exception $e) {
            \Log::error('Cannot clear cache data.');
        }

        $max_execution_time = (int)$request->max_execution_time ?: 300;
        @ini_set('max_execution_time', $max_execution_time);
        $permissions = 0755;
        $backupDir = config('filesystems.disks.backups.root');

        $fileSystem = new Filesystem();
        $response = $this->connectApi($request, 'update', $request->type);

        if ($response === false) {
            return response()->json(['status'=>false], 500);
        }

        if (empty($response->headers->fileName)) {
            $body = json_decode($response->body);
            if (!empty($body->status) && $body->status == 'update_at_expired') {
                return response()->json(['status'=>false, 'message'=>$body->message], 200);
            }
        }

        $fileSystem->makeDirectory(parse_path('tmp'), $permissions, true, true);
        file_put_contents(parse_path('tmp/' . $response->headers->fileName),$response->body);
        $pathParts = pathinfo(parse_path('tmp/' . $response->headers->fileName));
        $zip = Zip::open(parse_path('tmp/' . $response->headers->fileName));
        $zip->extract(parse_path('tmp'));
        $zip->close();
        $fileSystem->delete(parse_path('tmp/' . $response->headers->fileName));
        $backupName = 'before_' . date('Y_m_d__H_i_s_') . $pathParts['filename'];
        \Storage::disk('backups')->makeDirectory($backupName, $permissions, true, true);

        if (\Storage::disk('backups')->exists($backupName)
            && $fileSystem->exists(parse_path('tmp/' . $pathParts['filename'] . '/dump.json'))
        ) {
            $this->exportDatabase($backupName . DIRECTORY_SEPARATOR . 'dump.sql');
            $json = $fileSystem->get(parse_path('tmp/' . $pathParts['filename'] . '/dump.json'));
            $changes = \GuzzleHttp\json_decode($json, true);
            \Storage::disk('backups')->put(
                $backupName . DIRECTORY_SEPARATOR . 'dump.json',
                $json
            );
            // $fileSystem->copy(
            //    parse_path('tmp/' . $pathParts['filename'] . '/dump.json'),
            //    parse_path('backups/' . $backupName . '/dump.json')
            // );

            if (!empty($changes['delete'])) {
                foreach ($changes['delete'] as $file) {
                    if (preg_match('#(^backups|^uploads|^storage|^tmp|^updater\.php)#', $file)) {
                        continue;
                    }
                    $etoChanges['delete'][] = $file;
                    if (\Storage::disk('backups')->exists($backupName . DIRECTORY_SEPARATOR . $file)
                        && $fileSystem->exists(parse_path($file))
                    ) {
                        $fileSystem->makeDirectory(dirname($backupDir . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . $file), $permissions, true, true);

                        if (\File::isDirectory(parse_path($file))) {
                            \File::copyDirectory(parse_path($file), $backupDir . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . $file);
                        }
                        else {
                            \File::copy(parse_path($file), $backupDir . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                }
            }

            if (!empty($changes['modify'])) {
                foreach ($changes['modify'] as $file) {
                    if (preg_match('#(^backups|^uploads|^storage|^tmp|^updater\.php)#', $file)) {
                        continue;
                    }
                    $etoChanges['modify'][] = $file;
                    if (!\Storage::disk('backups')->exists($backupName . DIRECTORY_SEPARATOR . $file)
                        && $fileSystem->exists(parse_path($file))
                    ) {
                        $fileSystem->makeDirectory(dirname($backupDir . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . $file), $permissions, true, true);

                        if ($fileSystem->isDirectory(parse_path($file))) {
                            $fileSystem->copyDirectory(parse_path($file), $backupDir . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . $file);
                        }
                        else {
                            $fileSystem->copy(parse_path($file), $backupDir . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . $file);
                        }
                    }
                }
            }

            $zip = Zip::create($backupDir . DIRECTORY_SEPARATOR . $backupName . '.zip');
            $zip->add($backupDir . DIRECTORY_SEPARATOR . $backupName);
            $zip->close();

            if (Zip::check($backupDir . DIRECTORY_SEPARATOR . $backupName . '.zip')) {
                @chmod($backupDir . DIRECTORY_SEPARATOR . $backupName . '.zip', 0644);
                $fileSystem->deleteDirectory($backupDir . DIRECTORY_SEPARATOR . $backupName);
            }
            else {
                return response()->json(['status'=>false], 500);
            }

            if ($fileSystem->exists(parse_path('tmp/' . $pathParts['filename'] . '/updater.php'))) {
                $fileSystem->delete(parse_path('updater.php' ));
                $fileSystem->copy(parse_path('tmp/' . $pathParts['filename'] . '/updater.php'), parse_path('updater.php'));
                @chmod(parse_path('updater.php'), 0644);
            }

            $fileSystem->put(parse_path('/framework/down', false), '');

            Cache::forget('system_modules_api');
            return response()->json(['status'=>true, 'folder' => $pathParts['filename']], 200);
        }

        Cache::forget('system_modules_api');
        return response()->json(['status'=>false], 500);
    }

    private function connectApi($request, $uri, $idmodule = false, $data = false) {
        $responseCode = 200;
        $error = '';

        try {
            $headers = $this->getRequestData($request, $uri, $idmodule, $data);
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
                    cache(['system_modules_api' => $data], $this->seconds);
                }
                else {
                    Cache::forget('system_modules_api');
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

    private function getRequestData($request, $uri, $idmodule = false, $data = false)
    {
        $settings = Setting::where('relation_id', 0)->where('relation_type', 'system')->toObject();
        $coreVersion = !empty($settings->core_version) ? $settings->core_version : config('app.version');

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

        if (!$data) {
            if ($uri != 'install') {
                if (!empty($request->system->modules)) {
                    if ($idmodule === false) {
                        $modules = $request->system->modules;
                        if (!$modules) {
                            $modules = Module::with(['subscriptions' => function ($query) use ($request) {
                                $query->where('subscription_id', $request->system->subscription->id);
                            }])->get();
                        }
                    } else {
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

    private function checkRequirements($requirements) {
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

    private function checkResponseForUpdates($localModule, $module, $id, $viewModule = false) {
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

    private function parseVersions($versions, $id) {
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
        $fileSystem = new Filesystem();

        if (!empty($request->type) && $fileSystem->exists(storage_path('framework/down'))) {
            if ($this->runMigrations()) {
                $setting = Setting::where('relation_id', 0)->where('relation_type', 'system')->where('param', 'available_versions')->first();
                if ($setting) {
                    $versions = \GuzzleHttp\json_decode($setting->value, true);

                    if ($request->type == 'eto') {
                        $settingCore = Setting::where('relation_id', 0)->where('relation_type', 'system')->where('param', 'core_version')->first();
                        if (!$settingCore) {
                            $settingCore = new Setting;
                            $settingCore->relation_type = 'system';
                            $settingCore->relation_id = 0;
                            $settingCore->param = 'core_version';
                        }
                        $settingCore->value = $versions[$request->type];
                        $settingCore->save();
                    }
                    else {
                        $module = Module::where('type', $request->type)->first();
                        $module->version = $versions[$request->type];
                        $module->save();
                    }
                    $setting->delete();
                }
                $fileSystem->delete(parse_path('/framework/down', false), '');
                $fileSystem->cleanDirectory(parse_path('/tmp/'), '');
                $this->verify($request);
                return response()->json(['status'=>true], 200);
            }
        }

        return response()->json(['status'=>false], 403);
    }

    public function runMigrations()
    {
        $outputLog = new BufferedOutput;

        try {
            Artisan::call('migrate', ["--force" => true], $outputLog);
        }
        catch (Exception $e) {
            \Log::error('Module update (runMigrations): '. $e->getMessage());
            return false;
        }

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        return true;
    }

    private function exportDatabase($fileSql)
    {
        $fileSql = config('filesystems.disks.backups.root') . DIRECTORY_SEPARATOR . $fileSql;
        include(base_path('vendor/easytaxioffice/dumper.php'));

        $dumper = \Shuttle_Dumper::create(array(
            'host' => config('database.connections.mysql.host'),
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'db_name' => config('database.connections.mysql.database'),
        ));

        return $dumper->dump($fileSql, config('database.connections.mysql.prefix'));
    }

    private function parseData($data)
    {
        $prarams = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($data), true);

        if (!$prarams) { return false; }
        unset($prarams['id']);
        unset($prarams['type']);
        return $prarams;
    }

    private function parseConditions($parseVersions)
    {
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

    private function updateModuleSubscription($localModule, $params, $module) {
        $request = request();
        if (!empty(['params'])) {
            if (empty($localModule->available_version) || !is_a($localModule, 'Illuminate\Database\Eloquent\Collection')) {
                $localModule = Module::with(['subscriptions'=>function($query) use($request) {
                    $query->where('subscription_id', $request->system->subscription->id);
                }])->find($localModule->id);
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

    private function setNewVersions() {
        if (count($this->availableVersion) > 0) {
            Setting::updateOrCreate(['relation_id' => 0, 'relation_type' => 'system', 'param' => 'available_versions'],['value'=>\GuzzleHttp\json_encode($this->availableVersion)]);
        }
        else {
            Setting::where('relation_id', 0)->where('relation_type', 'system')->where('param', 'available_versions')->delete();
        }
    }

    private function addModule($apiModule, $lastVersion = '', $params = [])
    {
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

    private function getParentId($type) {
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
        $domain = preg_replace('/www./', '', $request->getHost());
        $currentSite = Site::where('domain', $domain)->first();
        $isCorrupted = true;

        if (empty(Subscription::all()->count())) {
            $isCorrupted = false;
        }
        elseif ($currentSite) {
            abort(404);
        }

        return view('modules.license', ['corrupted' => $isCorrupted]);
    }

    public function installETO($license = null) {
        $request = request();

        if ((!empty($request->system->core) && $request->system->core->params->domainInstallation == $_SERVER['HTTP_HOST']) && empty(session('installer_app_license'))) {
            abort(404);
        }

        if ($request->method() == 'POST' && $license == null)  {
            $license = $request->license;
        }

        session()->forget('installer_app_license');

        $data = [
            'license_key' => $license,
            'installation_type' => 'core',
            'core_type' => 'eto',
            'root_url' => url('/').'/',
            'php' => PHP_VERSION,
            'company_name' => config('app.name'), // config('site.company_name')
        ];

        $response = $this->connectApi(request(), 'install-core', $license, $data);

        // dd($response, $license, $data);

        if (!empty($response->status)) {
            if ($response->status != 'OK' && !empty($response->data)) {
                return response()->json(['status' => false, 'message' => $response->message], 200);
            }
            if (!empty($response->data->license)) {
                $companyName = !empty($request->company_name) ? $request->company_name : '';

                if ($companyName) {
                    $unique_id = str_replace('-', '', str_slug($companyName));
                }
                else {
                    $unique_id = uniqid();
                }

                $subscription = new Subscription();
                $subscription->unique_id = $unique_id;
                $subscription->domain = preg_replace('/www./', '', $request->getHost());
                $subscription->license = $response->data->license->license_key;
                $subscription->status = 1;
                $subscription->params = $response->data->license->core;
                $subscription->hash = md5($response->data->license->license_key . json_encode($response->data->license->core) . $response->data->license->expire_at);
                $subscription->expire_at = $response->data->license->expire_at;
                $subscription->update_at = $response->data->license->update_at;
                $subscription->support_at = $response->data->license->support_at;
                $subscription->save();

                $request->system->subscription->license = $subscription->license;
                $request->system->subscription->id = $subscription->id;
                $sites = Site::all();

                foreach($sites as $site) {
                    $site->subscription_id = $subscription->id;
                    $site->domain = $subscription->domain;
                    $site->save();
                }

                Setting::updateOrCreate(['relation_id' => 0, 'relation_type' => 'system', 'param' => 'core_version'],['value' => config('app.version')]);
            }
        }

        if (\File::exists(base_path('tmp/eto.json'))) {
            $configInstallData = json_decode(file_get_contents(base_path('tmp/eto.json')));

            foreach($sites as $site) {
                if (!empty($site->id)) {
                    $config = Config::where('site_id', $site->id)->where('key', 'force_https')->first();

                    if (null === $config) {
                        $config = new Config();
                        $config->site_id = $site->id;
                        $config->key = 'force_https';
                        $config->type = 'int';
                        $config->browser = 0;
                    }

                    $config->value = $configInstallData->installer_app_url_schema ?: 0;
                    $config->save();
                }
            }

            if ($configInstallData->autologin === true) {
                $credentials = [
                    'email' => $configInstallData->installer_app_email,
                    'password' => $configInstallData->installer_app_password,
                ];
                if (\Auth::attempt($credentials)) {
                    $this->permmisoinFiles();
                    return redirect('admin');
                }
            }
        }

        $this->permmisoinFiles();
        return redirect('login');
    }

    private function permmisoinFiles() {
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
        return view('modules.corrupted');
    }

    public function expiredETOLicense() {
        return view('modules.expired');
    }

    public function checkForInstallation($license) {
        $request = request();
        $response = false;
        $domain = preg_replace('/www./', '', $request->getHost());
        $data = [
            'stream' => true,
            'connect_timeout' => 10,
            'read_timeout' => 10,
            "decode_content" => true,
            "verify" => false,
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
                'X-Forwarded-For' => $_SERVER['REMOTE_ADDR'],
                'referer' => $domain,
                'etogetall' => 0,
                'globalparent' => 'eto'
            ],
            'form_params' => [
                'license_key' => $license,
                'installation_type' => 'core',
                'core_type' => 'eto'
            ],
        ];

        try {
            $client = new Client();
            $result = $client->post($this->apiLicenseUrl . 'check-install-core', $data);
        }
        catch (Exception $e) {
            $responseCode = $e->getCode();
            $error = $e->getMessage();
            \Log::error([$error,$responseCode]);
        }

        if (isset($result) && !empty($result)) {
            $body = $result->getBody();

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

        if ($response === false) {
            return ['message'=>'Failed connection.'];
        }

        if ($response->status == 'OK') {
            return true;
        }

        return ['message'=>trans('installer.'.$response->status)];
    }
}
