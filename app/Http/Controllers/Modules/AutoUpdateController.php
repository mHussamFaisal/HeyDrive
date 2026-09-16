<?php

namespace App\Http\Controllers\Modules;

use Illuminate\Http\Request;

class AutoUpdateController
{
    public function allowAccess()
    {
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

    public function ping(Request $request)
    {
        if (!$this->allowAccess()) { abort(404); }

        (new \App\Http\Controllers\Modules\ModulesController())->verify($request, true);
        return response()->json(['status' => 'OK'] , 200);
    }

    public function index(Request $request)
    {
        if (!$this->allowAccess()) { abort(404); }

        if ((bool)eto_config('AUTO_UPDATE_ENABLE') === true && !empty($request->step)) {
            (new \App\Http\Controllers\Modules\ModulesController())->verify($request, true);

            $settings = \App\Models\Setting::where('param', 'available_versions')->toObject();
            $versions = \GuzzleHttp\json_decode($settings->available_versions);

            if (empty($versions->eto)) {
                abort(404);
            }

            $request->request->add([
                'max_execution_time' => 1000,
                'type' => 'eto',
                'maxVersion' => $versions->eto,
            ]);

            if ((int)$request->step === 1) {
                return (new ModulesController)->update($request);
            }
            elseif ((int)$request->step === 2) {
                // nothing
            }
            elseif ((int)$request->step === 3) {
                return (new ModulesController)->migrateAndClearAfterUpgrade($request);
            }
            else {
                abort(404);
            }
        }
        else {
            abort(404);
        }
    }
}
