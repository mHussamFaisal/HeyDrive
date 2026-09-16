<?php

namespace App\Http\Controllers\Subscription;

use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;

class AutoUpdateController
{
    public function allowAccess() {
        $request = request();

        if ((
            $request->is('auto-update') ||
            $request->is('auto-update/ping')
        ) && ((
            !empty($request->secret_key) &&
            !empty($request->system) &&
            !empty($request->system->subscription) &&
            !empty($request->system->subscription->license) &&
            $request->secret_key == md5($request->system->subscription->license .'-'. date('Y-m-d'))
        ) || (
            !empty($request->secret_key) &&
            !empty(eto_config('AUTO_UPDATE_SECRET_KEY')) &&
            $request->secret_key == eto_config('AUTO_UPDATE_SECRET_KEY')
        ))) {
            return true;
        }
        else {
            return false;
        }
    }

    public function ping(Request $request) {
        if (!$this->allowAccess()) { abort(404); }
        clear_cache('cache');

        $sendDriverCount = get_active_drivers_cron(true);
        (new \App\Http\Controllers\Subscription\SubscriptionController())->verify($request, true, $sendDriverCount, true);

        return response()->json(['status' => 'OK'] , 200);
    }

    public function index(Request $request) {
        if (!$this->allowAccess()) { abort(404); }

        if ((bool)eto_config('AUTO_UPDATE_ENABLE') === true && !empty($request->step)) {
            clear_cache('cache');

            (new \App\Http\Controllers\Subscription\SubscriptionController())->verify($request, true);

            $versions = (object)config('eto.available_versions', []);

            if (empty($versions->eto) && (int)$request->step !== 3) {
                return response()->json(['status'=>'latest', 'message'=>'Up to date'], 200);
            }

            $request->request->add([
                'max_execution_time' => 1000,
                'type' => 'eto',
                'maxVersion' => !empty($versions->eto) ? $versions->eto : null,
            ]);

            if ((int)$request->step === 1) {
                return $this->autoupdate($request);
            }
            elseif ((int)$request->step === 2) {
                // nothing
            }
            elseif ((int)$request->step === 3) {
                return (new SubscriptionController)->migrateAndClearAfterUpgrade($request);
            }
            else {
                abort(404);
            }
        }
        else {
            abort(404);
        }
    }

    protected function autoupdate($request) {
        clear_tmp();
        $message = '';
        $max_execution_time = (int)$request->max_execution_time ?: 300;
        @ini_set('max_execution_time', $max_execution_time);
        $permissions = 0755;

        $fileSystem = new Filesystem();
        $response = (new SubscriptionController)->connectApi($request, 'update', $request->type);

        if ($response === false) {
            return response()->json(['status'=>false,'message'=>'(new SubscriptionController)->connectApi($request, \'update\', $request->type) no response'], 200);
        }

        if (empty($response->headers->fileName)) {
            $body = json_decode($response->body);
            if (!empty($body->status)) {
                if ($body->status == 'update_at_expired') {
                    \Log::error('AutoUpdateController::autoupdate() - '. $body->message);
                    return response()->json(['status'=>false, 'message'=>$body->message], 200);
                }

                return response()->json(['status'=>'latest','message'=>'Up to date: '. json_encode($response)], 200);
            }
        }

        $fileSystem->makeDirectory(parse_path('tmp'), $permissions, true, true);
        file_put_contents(parse_path('tmp/'. $response->headers->fileName), $response->body);
        $pathParts = pathinfo(parse_path('tmp/'. $response->headers->fileName));

        $zip = \Zipper::make('tmp/'. $response->headers->fileName);
        $zip->extractTo(parse_path('tmp'));
        $zip->close();

        $fileSystem->delete(parse_path('tmp/'. $response->headers->fileName));

        $request->folder = $pathParts['filename'];
        $files = [];
        if (\Storage::disk('tmp')->exists($request->folder . '/dump.json')) {
            $json = \Storage::disk('tmp')->get($request->folder . '/dump.json');
            $changes = \GuzzleHttp\json_decode($json, true);

            if (!empty($changes['modify'])) {
                foreach ($changes['modify'] as $file) {
                    $files[] = $file;
                }
            }
            if (!empty($changes['add'])) {
                foreach ($changes['add'] as $file) {
                    $files[] = $file;
                }
            }
        }
        // \Log::info([$request->folder, $pathParts, $files]);

        $request->request->add(['status' => true]);
        $request->request->add(['systemBackup' => true]);
        $request->request->add(['message' => trans('subscription.update.get_changes')]);
        $request->request->add(['folder' => $request->folder]);
        $request->request->add(['type' => 'system']);
        $request->request->add(['process' => 'backup']);
        $bresp = (new \App\Http\Controllers\BackupController)->backupDB($request);

        if (!empty($bresp['status'])) {
            $request->request->add(['backupName' => $bresp['backupName']]);
            $request->request->add(['backupId' => $bresp['backupId']]);
            $request->request->add(['list' => $files]);
            $bresp = (new \App\Http\Controllers\BackupController)->addFilesToZip($request);

            if (!empty($bresp['status'])) {
                // $request->request->add(['process' => 'backup']);
                // $bresp = (new \App\Http\Controllers\BackupController)->addVendorToZip($request);

                // if (!empty($bresp['status'])) {
                    $request->request->add(['step' => 'move_backup']);
                    $bresp = (new \App\Http\Controllers\BackupController)->moveBackupZip($request);

                    if ($bresp['status'] === true) {
                        \Cache::forget('system_modules_api');
                        maintenance_mode('block');
                        return response()->json(['status' => true, 'folder' => $pathParts['filename']], 200);
                    } else {
                        $message = 'AutoUpdateController::autoupdate() - no move backup: '. json_encode($bresp);
                        \Log::error($message);
                    }
                // } else {
                //     $message = 'AutoUpdateController::autoupdate() - no files backup: '. json_encode($bresp);
                //     \Log::error($message);
                // }
            } else {
                $message = 'AutoUpdateController::autoupdate() - no files backup: '. json_encode($bresp);
                \Log::error($message);
            }
        } else {
            $message = 'AutoUpdateController::autoupdate() - no vendor backup: '. json_encode($bresp);
            \Log::error($message);
        }

        \Cache::forget('system_modules_api');

        return response()->json(['status'=>false,'message'=>'Something went wrong: '. $message], 200);
    }
}
