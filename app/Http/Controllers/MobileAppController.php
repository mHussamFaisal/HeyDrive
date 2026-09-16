<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;

class MobileAppController extends Controller
{
    function __construct()
  	{
        $request = request();

        session([
            'isMobileApp' => true,
            'clientType' => $request->get('clientType', ''),
            'clientVersion' => $request->get('clientVersion', '1.0.0'),
        ]);
    }

    public function pushToken()
    {
        switch (request('clientType')) {
            case 'etoengine-customer':

                $data = [
                    'status' => '',
                ];

                if (!$this->isAllowed()) {
                    $data['error_message'] = 'Please purchase appropriate license to use mobile app!';
                    $data['status'] = 'NO_LICENSE';
                    return response()->json($data);
                }

                if (session('etoUserId')) {
                    $id = request('id');
                    $token = request('token');

                    if (!empty($id) && !empty($token)) {
                        $row = [
                            'id' => $id,
                            'push_token' => $token,
                        ];

                        \DB::table('user')->where('id', $id)->update($row);

                        $data['status'] = 'OK';
                    }
                    else {
                        $data['error_message'] = 'Invalid request.';
                        $data['status'] = 'INVALID_REQUEST';
                    }
                }
                else {
                    $data['error_message'] = 'You are logged out';
                    $data['status'] = 'FAILED';
                }

                return response()->json($data);

            break;
            default:

                $data = [
                    'isLoggedIn' => false,
                    'status' => false,
                    'message' => '',
                ];

                if (!$this->isAllowed()) {
                    $data['message'] = 'Please purchase appropriate license to use mobile app!';
                    return response()->json($data);
                }

                if (Auth::check()) {
                    $id = request('id');
                    $token = request('token');

                    if (!empty($id) && !empty($token)) {
                        $user = \App\Models\User::findOrFail($id);
                        $user->push_token = $token;
                        $user->save();

                        $data['status'] = true;
                    }

                    $data['isLoggedIn'] = true;
                }

                return response()->json($data);

            break;
        }
    }

    public function updateStatus()
    {
        switch (request('clientType')) {
            case 'etoengine-customer':

                //

            break;
            default:

                $data = [
                    'results' => [],
                    'status' => '',
                ];

                if (!$this->isAllowed()) {
                    $data['error_message'] = 'Please purchase appropriate license to use mobile app!';
                    $data['status'] = 'NO_LICENSE';
                    return response()->json($data);
                }

                if (Auth::check()) {
                    $id = request('id');

                    switch (request('status')) {
                        case 'available':
                            $status = 1;
                        break;
                        case 'onbreak':
                            $status = 2;
                        break;
                        case 'unavailable':
                        default:
                            $status = 0;
                        break;
                    }

                    if (!empty($id)) {
                        $user = \App\Models\User::with('profile')->findOrFail($id);
                        $user->last_seen_at = \Carbon\Carbon::now();
                        $user->save();

                        if (!empty($user->profile)) {
                            $user->profile->availability_status = $status;
                            $user->profile->save();
                        }

                        $data['status'] = 'OK';
                    }
              			else {
                        $data['error_message'] = 'Availability status could not be changed.';
                        $data['status'] = 'FAILED';
              			}
                }
                else {
                    $data['error_message'] = trans('frontend.userMsg_LoginFailure');
                    $data['status'] = 'FAILED';
                }

                return response()->json($data);

            break;
        }
    }

    public function updateCoordinates()
    {
        switch (request('clientType')) {
            case 'etoengine-customer':

                //

            break;
            default:

                $data = [
                    'isLoggedIn' => false,
                    'status' => false,
                    'message' => '',
                ];

                if (!$this->isAllowed()) {
                    $data['message'] = 'Please purchase appropriate license to use mobile app!';
                    return response()->json($data);
                }

                if (Auth::check()) {
                    $id = request('id');
                    $authUser = auth()->user();

                    if (!empty($id)
                        && $authUser->lat != request('lat')
                        && $authUser->lng != request('lng')
                    ) {
                        $authUser->setTracking();
                        $data['status'] = true;
                    }

                    $data['isLoggedIn'] = true;
                }

                return response()->json($data);

            break;
        }
    }

    public function login()
    {
        $request = request();

        session([
            'isMobileApp' => true,
            'clientType' => $request->get('clientType', ''),
            'clientVersion' => $request->get('clientVersion', '1.0.0'),
        ]);

        switch (request('clientType')) {
            case 'etoengine-customer':

                $data = [
                    'results' => [],
                    'status' => '',
                ];

                if (!$this->isAllowed()) {
                    $data['error_message'] = 'Please purchase appropriate license to use mobile app!';
                    $data['status'] = 'NO_LICENSE';
                    return response()->json($data);
                }

                $db = \DB::connection();
                $dbPrefix = get_db_prefix();

                $sql = "SELECT *
          					FROM `{$dbPrefix}user`
          					WHERE `site_id`='". config('site.site_id') ."'
          					AND `email`='". request('username') ."'
          					AND `password`='". md5(request('password')) ."'
          					AND `type`='1'
          					LIMIT 1";

          			$query = $db->select($sql);
          			if (!empty($query[0])) {
        				    $query = $query[0];
          			}

                if ( !empty($query) ) {
            				if (!$query->activated) {
                        $data['error_message'] = trans('frontend.userMsg_ActivationUnfinished');
                        $data['status'] = 'NOT_VERIFIED';
            				}
            				else if (!$query->published) {
                        $data['error_message'] = trans('frontend.userMsg_Blocked');
                        $data['status'] = 'INACTIVE';
            				}
                    else {
                        $userId = (int)$query->id;

                        session(['etoUserId' => $userId]);

                        $row = new \stdClass();
                        $row->id = $userId;
                        $row->site_id = $query->site_id;
                        $row->last_visit_date = date('Y-m-d H:i:s');

                        \DB::table('user')->where('id', $row->id)->update((array)$row);

                        $data['results'] = [
                            'id' => $userId,
                            'name' => $query->name,
                        ];

                        $data['status'] = 'OK';
                    }
          			}
          			else {
                    $data['error_message'] = trans('frontend.userMsg_LoginFailure');
                    $data['status'] = 'FAILED';
          			}

                return response()->json($data);

            break;
            default:

                $data = [
                    'user' => null,
                    'status' => false,
                    'message' => '',
                ];

                if (!$this->isAllowed()) {
                    $data['message'] = 'Please purchase appropriate license to use mobile app!';
                    return response()->json($data);
                }

                $credentials = [
                    'email' => request('email'),
                    'password' => request('password'),
                    'status' => 'approved',
                ];

                if (Auth::attempt($credentials, request()->has('remember'))) {
                    $data['user'] = auth()->user();
                    $data['status'] = true;
                }
                else {
                    $data['message'] = trans('auth.failed');
                }

                return response()->json($data);

            break;
        }
    }

    public function logout()
    {
        switch (request('clientType')) {
            case 'etoengine-customer':

                $data = [
                    'status' => '',
                ];

                session(['etoUserId' => 0]);

            		if (!session('etoUserId')) {
                    $data['status'] = 'OK';
            		}
            		else {
              			$data['error_message'] = trans('frontend.userMsg_LogoutFailure');
                    $data['status'] = 'FAILED';
            		}

                return response()->json($data);

            break;
            default:

                $data = [
                    'status' => false,
                    'message' => '',
                ];

                if (Auth::check()) {
                    auth()->user()->setLastActivity('logout');
                }

                Auth::logout();

                if (Auth::check()) {
                    $data['status'] = false;
                    $data['message'] = 'You are logged out';
                }
                else {
                    $data['status'] = true;
                    $data['message'] = 'You are logged in';
                }

                return response()->json($data);

            break;
        }
    }

    public function host()
    {
        $isLoggedIn = false;

        switch (request('clientType')) {
            case 'etoengine-customer':
                if (session('etoUserId')) {
                    $isLoggedIn = true;
                }
            break;
            default:
                if (Auth::check()) {
                    $isLoggedIn = true;
                }
            break;
        }

        $data = [
            'results' => [
                'name' => config('site.company_name'),
                'version' => config('app.version'),
                'url' => url('/'),
                'isLoggedIn' => $isLoggedIn,
            ],
            'status' => 'OK',
        ];

        return response()->json($data);
    }

    private function isAllowed()
    {
        $status = false;

        switch (request('clientType')) {
            case 'etoengine-customer':

                if (config('site.allow_customer_app')) {
                    $status = true;
                }

            break;
            default:

                if (config('site.allow_driver_app')) {
                    $status = true;
                }

            break;
        }

        return $status;
    }
}
