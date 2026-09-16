<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DebugController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.settings.debug.index')) {
            return redirect_no_permission();
        }

        if ($request->get('action')) {
            $response = [];
            $message = '';

            switch ($request->get('action')) {
                case 'clear_cache':
                    // clear_cache('cache');
                    clear_cache();
                    $message = trans('debug.message.cache_cleared');
                break;
                case 'clear_view':
                    clear_cache('view');
                    $message = trans('debug.message.view_cleared');
                break;
                case 'clear_config':
                    clear_cache('config');
                    $message = trans('debug.message.config_cleared');
                break;
                case 'clear_session':
                    session()->flush();
                    $message = trans('debug.message.session_cleared');
                break;
                case 'clear_tmp':
                    clear_tmp();
                    $message = trans('debug.message.tmp_cleared');
                break;
                case 'reset_permissions':
                    \App\Helpers\FilesystemHelper::resetPermissions();
                    $message = trans('debug.message.permissions_reseted');
                break;
            }

            if ($request->ajax()) {
                return $response;
            }
            else {
                $errors = [];

                if (!empty($response['error'])) {
                    $errors[] = $response['error'];
                }
                else {
                    session()->flash('message', $message);
                }

                return redirect()->back()->withErrors($errors);
            }
        }
        else {
            return view('debug.index');
        }
    }
}
