<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        return view('driver.settings.index');
    }

    public function getStatus()
    {
        $user = auth()->user();
        $response = ['success' => false];

        if ($user) {
            $response = ['success' => true, 'statusId' => (int)$user->profile->availability_status];
        }

        return response()->json($response, 200);
    }

    public function setStatus()
    {
        $response = ['success' => false];
        $user = auth()->user();
        $profile = $user->profile()->first();
        $profile->availability_status = (int)request('statusId');

        if ($profile->save()) {
            $response = ['success' => true];
        }

        return response()->json($response, 200);
    }
}
