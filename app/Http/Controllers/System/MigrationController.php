<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class MigrationController extends Controller
{
    public function index() {
        $response = ['status' => false];

        if (maintenance_mode('block')) {
            try {
                $outputLog = new BufferedOutput;
                Artisan::call('migrate', ['--force' => true], $outputLog);
                $response['status'] = true;
                $response['message'] = trans('system.migrate_success');
                $response['output'] = $outputLog;
            }
            catch (Exception $e) {
                $response['message'] = $e->getMessage();
            }

            maintenance_mode('unblock');
        }
        else {
            $response['message'] = trans('system.no_down_file');
        }

        return response()->json($response, 200);
    }
}
