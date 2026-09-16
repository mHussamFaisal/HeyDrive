<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ETOv2Controller extends Controller
{
    public static function index($apiType = '')
    {
        error_reporting(E_ALL & ~E_NOTICE);

        $response = [
            'alert' => [],
            'success' => false
        ];

        $apiType = request('apiType', $apiType);
        $etoPost = request()->all();

    		switch( $apiType ) {
      			case 'cron':
                require_once __DIR__ .'/ETO/Cron.php';
      			break;
      			case 'backend':
                require_once __DIR__ .'/ETO/Backend.php';
      			break;
      			case 'frontend':
      			default:
                require_once __DIR__ .'/ETO/Frontend.php';
      			break;
    		}

        if ( config('app.debug') == 1 ) {
            $response['!SENT'] = $etoPost;
        }

        return $response;
    }

    public function cron()
    {
        self::index('cron');
    }
}
