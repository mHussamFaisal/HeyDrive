<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Subscription\SubscriptionController;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\InstallHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Output\BufferedOutput;
use Validator;

class InstallController extends Controller
{
    /**
     * @var isSuperAdmin
     */
    protected $isSuperAdmin = false;

    public function index()
    {
        $license = '';

        if (\Storage::disk('tmp')->exists('.eto')) {
            $license = \Storage::disk('tmp')->get('.eto');
            \Storage::disk('tmp')->delete('.eto');
        }

        return view('installer.index', ['isSuperadmin' => $this->isSuperAdmin, 'license' => $license]);
    }

    public function checkConnection(Request $request)
    {
        $request->database_prefix = empty($request->database_prefix) ? 'eto_' : $request->database_prefix;
        $status = false;
        $tables = [];
        $message = '';

        try {
            $mysqli = new \mysqli($request->database_hostname, $request->database_username, $request->database_password, $request->database_name);
            if (!$mysqli->connect_error) {
                $status = true;
                $name = 'Tables_in_'.$request->database_name;
                $result = $mysqli->query("SHOW TABLES");

                if ($result) {
                    while ($row = $result->fetch_object()) {
                        if (preg_match_all('/(^'.$request->database_prefix.')/m', $row->$name)) {
                            $tables[] = $row->$name;
                        }
                    }
                }
                $result->close();
            }
            $mysqli->close();
        }
        catch (\Exception $e) {}

        if (count($tables) > 0) {
            $status = false;
            $message = trans('installer.datatable_has_tables', ['prefix' => $request->database_prefix]);
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            // 'tables' => $tables,
        ], 200);
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function setConfigFile(Request $request)
    {
        $app_key = null;
        $response = (object)['status' => true];
        // $checkLicense = (new SubscriptionController())->checkForInstallation($request->app_license);

        // if ($checkLicense === true) {
        //     $rules = config('eto_installer.environment.form.rules');
        //     $messages = [
        //         'environment_custom.required_if' => trans('installer.form.name_required'),
        //     ];

        //     $validator = Validator::make($request->all(), $rules, $messages);

        //     if ($validator->fails()) {
        //         $errors = $validator->errors();
        //         $response->errors = $errors;
        //     }
        //     else {
        //         try {
        //             Artisan::call('key:generate', ["--show" => true]); // terminal$: php artisan key:generate --show
        //             $app_key = preg_replace('/(\n)/m', '', Artisan::output());
        //         }
        //         catch (Exception $e) {
        //             $response->errors[] = trans('installer.errors');
        //         }

        //         if ($app_key) {
        //             $isFileConfig = InstallHelper::setConfigFile([
        //                 'APP_NAME' => $request->app_name,
        //                 'APP_KEY' => $app_key,
        //                 'APP_URL' => $request->app_url,
        //                 'DB_CONNECTION' => $request->database_connection,
        //                 'DB_HOST' => $request->database_hostname,
        //                 'DB_DATABASE' => $request->database_name,
        //                 'DB_USERNAME' => $request->database_username,
        //                 'DB_PASSWORD' => $request->database_password,
        //                 'DB_PREFIX' => !empty($request->database_prefix) ? $request->database_prefix : 'eto_',
        //             ]);

        //             if ($isFileConfig) {
        //                 $response->status = true;
        //             }
        //             else {
        //                 $response->errors[] = trans('installer.errors');
        //             }
        //         }
        //     }
        // }
        // else {
        //     Validator::extend('checkLicense', function ($attribute, $value, $parameters, $validator)  {
        //         return false;
        //     }, $checkLicense['message']);

        //     $validator = Validator::make($request->all(), [
        //         'app_license' => 'required|max:24|min:6|checkLicense',
        //     ]);

        //     if ($validator->fails()) {
        //         $errors = $validator->errors();
        //         $response->errors = $errors;
        //     }
        // }

        return response()->json($response, 200);
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function setDataToDB()
    {
        $response = (object)['status' => false];

        if (InstallHelper::migrateAndSeed()) {
            $response->status = true;
        }

        return response()->json($response, 200);
    }

    /**
     * Update installed file and display finished view.
     *
     * @return bool
     */
    public function final(Request $request)
    {
        $license = $request->app_license;

        try {
            \DB::connection()->getPdo();
        }
        catch (\Exception $e) {
            return 'Could not connect to the database. Please check your configuration.';
        }

        $installedLogFile = storage_path('installed');
        $dateStamp = date("Y/m/d h:i:sa");

        if (!file_exists($installedLogFile)) {
            $message = trans('installer.installed.success_log_message') . $dateStamp . "\n";
            file_put_contents($installedLogFile, $message);
        }
        else {
            $message = trans('installer.installed.update_success_log_message') . $dateStamp;
            file_put_contents($installedLogFile, $message.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        $this->checkMigrations(true);
        $response = (new SubscriptionController())->installETO($license);
        settings_save('eto.db_version', config('app.version'));

        return response()->json($response, 200);
    }

    public function checkMigrations($noRedirect = false)
    {
        maintenance_mode('block');

        try {
            $outputLog = new BufferedOutput;
            Artisan::call('migrate', ['--force' => true], $outputLog);
        }
        catch (Exception $e) {
            // return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }

        clear_cache();
        maintenance_mode('unblock');
        return $noRedirect ? true : redirect('login');
    }

    public function loginAfterInstall(Request $request)
    {
        $response = (object)['status' => false];
        $credentials = [
            'email' => $request->app_email,
            'password' => $request->app_password,
        ];

        if (\Auth::attempt($credentials)) {
            $response->status = true;
        }

        return response()->json($response, 200);
    }

    public function deactivationView(Request $request)
    {
        if ((int)config('eto.deactivate_system') === 1) {
            return redirect('activation');
        }
        elseif ((int)$request->system->subscription->id === 0) {
            return redirect(redirect_to());
        }

        return view('installer.deactivation');
    }

    public function deactivation(Request $request) {
        if ((int)config('eto.deactivate_system') === 0) {
            $subscription = Subscription::find($request->system->subscription->id);

            if ($subscription->license != $request->license) {
                return redirect('deactivation')->withErrors([trans('installer.invalid_license_key')]);
            }

            if ((new \App\Http\Controllers\Subscription\SubscriptionController())->activation(0, $request->license) === true) {
                Cache::forget(md5(get_domain()));

                settings_save('eto.deactivate_system', 1, 'system', 0, true);
                clear_cache();

                return redirect('activation');
            }
            else {
                return redirect('deactivation')->withErrors([trans('installer.invalid_license_key')]);
            }
        }

        abort(404);
    }

    public function activationView(Request $request)
    {
        if ((int)config('eto.deactivate_system') === 0) {
            return redirect(redirect_to());
        }

        return view('installer.activation');
    }

    public function activation(Request $request) {
        if ((int)config('eto.deactivate_system') === 1) {
            $subscriptions = Subscription::all();
            foreach ($subscriptions as $subscription) {
                if ($subscription->license == $request->license) {
                    $request->system->subscription->id = $subscription->id;
                    $request->system->subscription->license = $subscription->license;
                    break;
                }
            }

            if ((int)$request->system->subscription->id !== 0
                && $request->system->subscription->license == $request->license
                && (new \App\Http\Controllers\Subscription\SubscriptionController())->activation(1, $request->system->subscription->license) === true
            ) {
                $domain = get_domain();
                $subscription = Subscription::find($request->system->subscription->id);

                $oldDomain = $subscription->domain;
                $subscription->domain = $domain;
                $subscription->hash = md5($subscription->license . json_encode($subscription->params) . $subscription->expire_at);
                $subscription->save();

                $site = \App\Models\Site::where('subscription_id', $request->system->subscription->id)->where('domain', $oldDomain)->first();
                $site->domain = $domain;
                $site->save();

                Cache::forget(md5($domain));

                settings_save('eto.deactivate_system', 0, 'system', 0, true);
                clear_cache();
            }
            else {
                return redirect('activation')->withErrors([trans('installer.invalid_license_key')]);
            }
            return redirect('subscription');
        }
        else {
            abort(404);
        }
    }
}
