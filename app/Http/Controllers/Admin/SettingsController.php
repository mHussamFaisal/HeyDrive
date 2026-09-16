<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SiteHelper;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public static function geocode($address) {
        global $gConfig;

        $language = explode('-', config('site.language'));

        $params = array(
            'key' => config('site.google_maps_geocoding_api_key'),
            'address' => trim($address),
        );

        if ( !empty(config('site.google_language')) ) {
            $params['language'] = strtolower(config('site.google_language'));
        }
        else {
            $params['language'] = ($language[0]) ? strtolower($language[0]) : 'en';
        }

        if ( !empty(config('site.google_region_code')) ) {
            $params['region'] = strtolower(config('site.google_region_code'));
        }

        if ( !empty(config('site.google_country_code')) ) {
            $list = [];
            $codes = explode(',', config('site.google_country_code'));
            foreach ($codes as $kC => $vC) {
                $vC = strtolower(trim($vC));
                if ( !empty($vC) ) {
                    $list[] = 'country:'. $vC;
                }
            }
            if ( !empty($list) ) {
                $params['components'] = implode('|', $list);
            }
        }

        $hash = 'g_geocode_'. md5(json_encode($params));
        $cache_expiry_time = config('site.google_cache_expiry_time') ? config('site.google_cache_expiry_time') : config('site.google_cache_runtime');
        $response = null;

        if ($cache_expiry_time && cache($hash)) {
            $response = cache($hash);
        }

        if (empty($response) && (!empty($params['place_id']) || !empty($params['latlng']) || !empty($params['address']))) {
            $client = new Client();
            $request = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json', [
                'headers' => [
                    'accept' => 'application/json',
                    'accept-encoding' => 'gzip, deflate',
                    'content-type' => 'application/json'
                ],
                'query' => $params
            ]);
            $response = json_decode($request->getBody());

            if (!empty($response) && in_array($response->status, ['OK', 'ZERO_RESULTS']) && $cache_expiry_time && !cache($hash)) {
								cache([$hash => $response], $cache_expiry_time);
            }
        }

        return $response;
    }

    public function charges(Request $request)
    {
        if ( $request->get('action') ) {
            $response = [];

            $allowedTypes = [
                'parking',
                'waiting',
            ];

            switch( $request->get('action') ) {
                case 'list':
                    if (!auth()->user()->hasPermission('admin.settings.charges.index')) {
                        return redirect_no_permission();
                    }
                    $results = [];

                    $charges = \App\Models\Charge::where('site_id', config('site.site_id'))
                        ->whereIn('type', $allowedTypes)
                        ->orderBy('type', 'asc')
                        ->get();

                    foreach( $charges as $charge ) {
                        $row = [];
                        $row['id'] = $charge->id;
                        $row['type'] = $charge->type;
                        $row['name'] = $charge->note;
                        $row['price'] = $charge->value;
                        $row['status'] = $charge->published;

                        $params = json_decode($charge->params);
                        if ( !empty($params) ) {
                            if ( !empty($params->location) ) {
                                if ( !empty($params->location->list) ) {
                                    $list = (array)$params->location->list;
                                    foreach( $list as $k => $v ) {
                                        $params->location->list[$k] = $v->address;
                                    }
                                }
                                $row['location'] = $params->location;
                            }
                            if ( !empty($params->vehicle) ) {
                                $row['vehicle'] = $params->vehicle;
                            }
                            if ( !empty($params->datetime) ) {
                                $row['datetime'] = $params->datetime;
                            }
                        }

                        $results[] = $row;
                    }

                    if ( !empty($results) ) {
                        $response['results'] = $results;
                    }
                    else {
                        $response['error'] = 'Charges could not be listed';
                    }

                break;
                case 'read':
                    if (!auth()->user()->hasPermission('admin.settings.charges.show')) {
                        return redirect_no_permission();
                    }
                    $id = $request->get('id');
                    $charge = \App\Models\Charge::findOrFail($id);

                    $results = [];

                    if ( !empty($charge->id) ) {
                        $row = [];
                        $row['id'] = $charge->id;
                        $row['type'] = $charge->type;
                        $row['name'] = $charge->note;
                        $row['name_enabled'] = $charge->note_published;
                        $row['status'] = $charge->published;
                        $row['price'] = $charge->value;

                        $params = json_decode($charge->params);
                        if ( !empty($params) ) {
                            if ( !empty($params->location) ) {
                                if ( !empty($params->location->list) ) {
                                    $list = (array)$params->location->list;
                                    foreach( $list as $k => $v ) {
                                        $params->location->list[$k] = $v->address;
                                    }
                                }
                                $row['location'] = $params->location;
                            }
                            if ( !empty($params->vehicle) ) {
                                $row['vehicle'] = $params->vehicle;
                            }
                            if ( !empty($params->datetime) ) {
                                $row['datetime'] = $params->datetime;
                            }
                        }

                        $results = $row;
                    }

                    if ( !empty($results) ) {
                        $response['results'] = $results;
                    }
                    else {
                        $response['error'] = 'The charge could not be read';
                    }

                break;
                case 'save':
                    $data = (object)$request->get('params');

                    $id = 0;
                    if ( !empty($data->id) ) {
                        $id = (int)$data->id;
                    }

                    $charge = \App\Models\Charge::find($id);

                    $addressCache = [];

                    if ( !empty($charge->id) ) {
                        if (!auth()->user()->hasPermission('admin.settings.charges.edit')) {
                            return redirect_no_permission();
                        }
                        $params = json_decode($charge->params);
                        if ( !empty($params) ) {
                            if ( !empty($params->location) ) {
                                if ( !empty($params->location->list) ) {
                                    $list = (array)$params->location->list;
                                    foreach( $list as $k => $v ) {
                                        $addressCache[md5($v->address)] = $v;
                                    }
                                }
                            }
                        }
                    }

                    $params = [];
                    $start_date = null;
                    $end_date = null;

                    if ( !empty($data->location) ) {
                        $location = (object)$data->location;
                        if ( !empty($location->enabled) ) {
                            $params['location']['enabled'] = 1;
                            if ( !empty($location->type) ) {
                                $params['location']['type'] = (string)$location->type;
                            }
                            if ( !empty($location->list) ) {
                                $list = (array)$location->list;
                                foreach( $list as $k => $v ) {
                                    $v = trim((string)$v);
                                    $row = [
                                        'address' => $v,
                                        'postcode' => '',
                                        'lat' => 0,
                                        'lng' => 0,
                                    ];

                                    if ( !empty($addressCache[md5($v)]) ) {
                                        $row = $addressCache[md5($v)];
                                    }
                                    else {
                                        $geocode = $this->geocode($v);
                                        if ( $geocode->status == 'OK' ) {
                                            foreach( $geocode->results[0]->address_components as $key1 => $value1 ) {
                                                foreach( $value1->types as $key2 => $value2 ) {
                                                    if ( $value2 == 'postal_code' ) {
                                                        $row['postcode'] = $value1->long_name;
                                                        break;
                                                    }
                                                }
                                            }
                                            $row['lat'] = $geocode->results[0]->geometry->location->lat;
                                            $row['lng'] = $geocode->results[0]->geometry->location->lng;
                                        }
                                    }
                                    $list[$k] = $row;
                                }

                                $params['location']['list'] = $list;
                            }
                        }
                    }

                    if ( !empty($data->vehicle) ) {
                        $vehicle = (object)$data->vehicle;
                        if ( !empty($vehicle->enabled) ) {
                            $params['vehicle']['enabled'] = 1;
                            if ( !empty($vehicle->list) ) {
                                $params['vehicle']['list'] = (array)$vehicle->list;
                            }
                        }
                    }

                    if ( !empty($data->datetime) ) {
                        $datetime = (object)$data->datetime;
                        if ( !empty($datetime->enabled) ) {
                            $params['datetime']['enabled'] = 1;
                            if ( !empty($datetime->start) ) {
                                $start_date = Carbon::parse((string)$datetime->start)->toDateTimeString();
                                $params['datetime']['start'] = $start_date;
                            }
                            if ( !empty($datetime->end) ) {
                                $end_date = Carbon::parse((string)$datetime->end)->toDateTimeString();
                                $params['datetime']['end'] = $end_date;
                            }
                        }
                    }

                    if ( empty($charge->id) ) {
                        if (!auth()->user()->hasPermission('admin.settings.charges.create')) {
                            return redirect_no_permission();
                        }
                        $charge = new \App\Models\Charge;
                        $charge->site_id = config('site.site_id');
                    }

                    $charge->note = isset($data->name) ? (string)$data->name : '';
                    $charge->note_published = isset($data->name_enabled) ? (int)$data->name_enabled : 0;
                    $charge->type = isset($data->type) ? (string)$data->type : '';
                    $charge->params = json_encode($params);
                    $charge->value = isset($data->price) ? (float)$data->price : 0;
                    $charge->start_date = $start_date;
                    $charge->end_date = $end_date;
                    $charge->published = isset($data->status) ? (int)$data->status : 0;
                    $charge->save();

                    if ( !empty($charge->id) ) {
                        $response['id'] = $charge->id;
                    }
                    else {
                        $response['error'] = 'The charge could not be saved';
                    }

                break;
                case 'delete':
                    if (!auth()->user()->hasPermission('admin.settings.charges.destroy')) {
                        return redirect_no_permission();
                    }
                    $id = $request->get('id');
                    $charge = \App\Models\Charge::findOrFail($id);

                    if ( $charge->delete() ) {
                        $response['id'] = $id;
                    }
                    else {
                        $response['error'] = 'The charge could not be deleted';
                    }

                break;
            }

            if ( $request->ajax() ) {
                return $response;
            }
            else {
                $errors = [];
                if ( !empty($response['error']) ) {
                    $errors[] = $response['error'];
                }
                else {
                    session()->flash('message', trans('admin/settings.message.saved'));
                }
                return redirect()->back()->withErrors($errors);
            }
        }
        else {
            return view('admin.settings.charges');
        }
    }

    public function notifications(Request $request)
    {
        if ( $request->get('action') ) {
            $errors = [];
            $response = [];

            switch( $request->get('action') ) {
                case 'preview';

                    $role = request('role', 'admin');
                    $channel = request('channel', '');
                    $status = request('status', 'pending');

                    if (!in_array($status, [
                        'pending','requested','quote','confirmed','assigned','auto_dispatch','accepted','rejected',
                        'onroute','arrived','onboard','completed','canceled','unfinished','incomplete'
                    ])) {
                        $status = 'pending';
                    }

                    $booking = new \App\Helpers\NotificationPreviewHelper($status);
                    $notification = new \App\Notifications\BookingStatus($status, $booking);

                    switch ($role) {
                        case 'admin':
                            $user = $booking->__getUser('admin');
                        break;
                        case 'driver':
                            $user = $booking->__getUser('driver');
                        break;
                        case 'customer':
                        default:
                            $user = $booking->__getUser('customer');
                        break;
                    }

                    switch ($channel) {
                        case 'email':

                            $dataObj = $notification->toMail($user);

                            $from = '';
                            if (!empty($dataObj->from) && is_array($dataObj->from)) {
                                if (!empty($dataObj->from[1])) {
                                    $from .= $dataObj->from[1];
                                }
                                if (!empty($dataObj->from[0])) {
                                    $from .= ' '. $dataObj->from[0];
                                }
                            }
                            else {
                                $from = $dataObj->from;
                            }

                            $to = '';
                            if (!empty($dataObj->to) && is_array($dataObj->to)) {
                                if (!empty($dataObj->to[1])) {
                                    $to .= $dataObj->to[1];
                                }
                                if (!empty($dataObj->to[0])) {
                                    $to .= ' '. $dataObj->to[0];
                                }
                            }
                            else {
                                $to = $dataObj->to;
                            }

                            return view('admin.settings.notifications_preview', [
                                'channel' => $channel,
                                'from' => trim($from),
                                'to' => trim($to),
                                'subject' => $dataObj->subject,
                                'body' => view('notifications::email', [
                                    'level' => $dataObj->level,
                                    'subject' => $dataObj->subject,
                                    'greeting' => $dataObj->greeting,
                                    'introLines' => $dataObj->introLines,
                                    'outroLines' => $dataObj->outroLines,
                                    'actionText' => $dataObj->actionText,
                                    'actionUrl' => $dataObj->actionUrl,
                                ]),
                            ]);

                        break;
                        case 'sms':

                            $dataObj = $notification->toSMS($user);

                            return view('admin.settings.notifications_preview', [
                                'channel' => $channel,
                                'body' => $dataObj['message'],
                            ]);

                        break;
                        case 'push':

                            $dataObj = $notification->toExpoPush($user);

                            return view('admin.settings.notifications_preview', [
                                'channel' => $channel,
                                'title' => $dataObj['title'],
                                'body' => $dataObj['body'],
                            ]);

                        break;
                    }

                    exit;

                break;
                case 'save':
                    if (!auth()->user()->hasPermission('admin.settings.notifications.edit')) {
                        return redirect_no_permission();
                    }

                    // notifications
                    $config = \App\Models\Config::ofSite()->whereKey('notifications')->first();

                    if ( empty($config->id) ) {
                        $config = new \App\Models\Config;
                        $config->site_id = config('site.site_id');
                        $config->key = 'notifications';
                        $config->type = 'object';
                        $config->browser = 0;
                    }

                    $config->value = json_encode($request->get('notifications', []));
                    $config->save();

                    if ( empty($config->id) ) {
                        $errors[] = trans('admin/settings.message.save_error');
                    }

                    // notification_booking_pending_info
                    $config = \App\Models\Config::ofSite()->whereKey('notification_booking_pending_info')->first();

                    if ( empty($config->id) ) {
                        $config = new \App\Models\Config;
                        $config->site_id = config('site.site_id');
                        $config->key = 'notification_booking_pending_info';
                        $config->type = 'string';
                        $config->browser = 0;
                    }

                    $config->value = $request->get('notification_booking_pending_info');
                    $config->save();

                    if ( empty($config->id) ) {
                        $errors[] = trans('admin/settings.message.save_error');
                    }


                    // notification_test_email
                    $config = \App\Models\Config::ofSite()->whereKey('notification_test_email')->first();

                    if ( empty($config->id) ) {
                        $config = new \App\Models\Config;
                        $config->site_id = config('site.site_id');
                        $config->key = 'notification_test_email';
                        $config->type = 'string';
                        $config->browser = 0;
                    }

                    $config->value = $request->get('notification_test_email');
                    $config->save();

                    if ( empty($config->id) ) {
                        $errors[] = trans('admin/settings.message.save_error');
                    }

                    // notification_test_phone
                    $config = \App\Models\Config::ofSite()->whereKey('notification_test_phone')->first();

                    if ( empty($config->id) ) {
                        $config = new \App\Models\Config;
                        $config->site_id = config('site.site_id');
                        $config->key = 'notification_test_phone';
                        $config->type = 'string';
                        $config->browser = 0;
                    }

                    $config->value = $request->get('notification_test_phone');
                    $config->save();

                    if ( empty($config->id) ) {
                        $errors[] = trans('admin/settings.message.save_error');
                    }

                break;
            }

            if ( $errors ) {
                $response['errors'] = $errors;
            }

            if ( $request->ajax() ) {
                return $response;
            }
            else {
                if ( empty($errors) ) {
                    session()->flash('message', trans('admin/settings.message.saved'));
                }
                return redirect()->back()->withErrors($errors);
            }
        }
        else {
            if (!auth()->user()->hasPermission('admin.settings.notifications.index')) {
                return redirect_no_permission();
            }
            $settings = \App\Models\Config::ofSite()->whereKeys(['notifications'])->toObject();

            if (is_null($settings->notifications)) {
                $settings->notifications = config('site.notifications');
            }
            // dd($settings);

            $notifications = [
                'booking_pending' => [
                    'admin' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_requested' => [
                    'admin' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_confirmed' => [
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_quote' => [
                    'admin' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_auto_dispatch' => [
                    'driver' => ['email', 'sms', 'push'],
                    // 'customer' => ['email', 'sms', 'push'],
                ],
                'booking_assigned' => [
                    'driver' => ['email', 'sms', 'push'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_accepted' => [
                    'admin' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_rejected' => [
                    'admin' => ['email', 'sms'],
                ],
                'booking_onroute' => [
                    'admin' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_arrived' => [
                    'admin' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_onboard' => [
                    'admin' => ['email', 'sms'],
                ],
                'booking_completed' => [
                    'admin' => ['email', 'sms'],
                    'driver' => ['email', 'sms'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_canceled' => [
                    'admin' => ['email', 'sms'],
                    'driver' => ['email', 'sms', 'push'],
                    'customer' => ['email', 'sms', 'push'],
                ],
                'booking_unfinished' => [
                    'admin' => ['email'],
                ],
                // 'booking_incomplete' => [
                //     'admin' => ['email', 'sms'],
                //     'customer' => ['email', 'sms', 'push'],
                // ],
            ];

            return view('admin.settings.notifications', compact('settings', 'notifications'));
        }
    }

    public function general(Request $request)
    {
        $allowed = [
            'logo',
        ];

        $settings = \App\Models\Config::ofSite()->whereKeys($allowed)->toObject();

        if ( $request->get('action') ) {
            $errors = [];
            $response = [];

            switch( $request->get('action') ) {
                case 'save':

                    $validator = \Validator::make($request->all(), [
                        'logo' => 'mimes:jpg,jpeg,gif,png',
                    ]);

                    if ( $validator->fails() ) {
                        $errors = array_merge($errors, $validator->errors()->all());
                    }
                    else {
                        foreach( $allowed as $key ) {
                            $config = \App\Models\Config::ofSite()->whereKey($key)->first();

                            if ( empty($config->id) ) {
                                $config = new \App\Models\Config;
                                $config->site_id = config('site.site_id');
                                $config->key = $key;
                                $config->value = '';
                                $config->type = 'string';
                                $config->browser = 0;
                            }

                            switch( $key ) {
                                case 'logo':
                                    $logo = $settings->logo;

                                    if ( \Storage::disk('logo')->exists($settings->logo) && $request->get('logo_delete') ) {
                                        \Storage::disk('logo')->delete($settings->logo);
                                        $logo = '';
                                    }

                      							if ( $request->hasFile('logo') ) {
                      									$file = $request->file('logo');
                      									$filename = \App\Helpers\SiteHelper::generateFilename('logo') .'.'. $file->getClientOriginalExtension();

                                        $img = \Image::make($file);

                                        if ($img->width() > config('site.image_dimensions.logo.width')) {
                                            $img->resize(config('site.image_dimensions.logo.width'), null, function ($constraint) {
                                                $constraint->aspectRatio();
                                                $constraint->upsize();
                                            });
                                        }

                                        if ($img->height() > config('site.image_dimensions.logo.height')) {
                                            $img->resize(null, config('site.image_dimensions.logo.height'), function ($constraint) {
                                                $constraint->aspectRatio();
                                                $constraint->upsize();
                                            });
                                        }

                                        $img->save(asset_path('uploads','logo/'. $filename));

                                        if ( \Storage::disk('logo')->exists($filename) ) {
                                            $logo = $filename;
                                            if ( \Storage::disk('logo')->exists($settings->logo) ) {
                                                \Storage::disk('logo')->delete($settings->logo);
                                            }
                      									}
                      							}

                                    $config->value = $logo;
                                    $response['logo'] = $logo;
                                break;
                                default:
                                    $config->value = $request->get($key);
                                break;
                            }

                            $config->save();

                            if ( empty($config->id) ) {
                                $errors[] = trans('admin/settings.message.save_error');
                            }
                        }
                    }

                break;
            }

            if ( $errors ) {
                $response['errors'] = $errors;
            }

            if ( $request->ajax() ) {
                return $response;
            }
            else {
                if ( empty($errors) ) {
                    session()->flash('message', trans('admin/settings.message.saved'));
                }
                return redirect()->back()->withErrors($errors);
            }
        }
        else {
            return view('admin.settings.general', compact('settings'));
        }
    }

    public function updateSettings(Request $request) {
        $data = $request->all();

        foreach ($data as $relationType => $values) {
            foreach ($values as $key => $value) {
                $relationId = 0;
                if ($relationType == 'user') {
                    $relationId = auth()->user()->id;
                }
                elseif ($relationType == 'subscription') {
                    $relationId = $request->system->subscription->id;
                }

                try {
                    settings_save($key, $value, $relationType, $relationId);
                }
                catch (Exception $e) {
                    \Log::error(['code' => $e->getCode(), 'message' => $e->getMessage(), 'place' => '\App\Http\Controllers\Admin\SettingsController::class->updateSettings()']);

                    return response()->json(['status' => false, 'message' => $e->getMessage()], 200);
                }
            }

            settings_load(null, $relationType, true);
        }

        return response()->json(['status' => true], 200);
    }

    public function resetSettings(Request $request) {
        $data = $request->all();

        foreach ($data as $relationType => $values) {
            foreach ($values as $key => $value) {
                $relationId = 0;
                if ($relationType == 'user') {
                    $relationId = auth()->user()->id;
                }
                elseif ($relationType == 'subscription') {
                    $relationId = $request->system->subscription->id;
                }
                settings_delete($key, $relationType, $relationId);
            }
        }
    }

    public function sendTestEmail(Request $request) {
        $status = 'OK';
        $message = '';
        $data = ['body' => trans('admin/settings.send_test_email_message_body')];

        try {
            $sent = \Mail::send(['text' => 'emails.blank-text'], $data, function ($message) use ($request) {
                $message->to($request->email)->subject(trans('admin/settings.send_test_email_message_subject'));
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });
        }
        catch (\Exception $e) {
            $sent = false;
            $responseCode = $e->getCode();
            $message = $e->getMessage();
            \Log::error([$message, $responseCode]);
        }

        if ($sent === false) {
            $message = trans('admin/settings.send_test_email_failed') . "\n\r" . $message;
            $status = 'FAIL';
        }

        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function saveDtState(Request $request) {
        $status = false;
        $message = '';
        $page = str_slug($request->get('page', 'all'), '_');
        $state = !empty($request->get('state')) ? json_encode($request->get('state'), JSON_NUMERIC_CHECK) : null;

        switch ($request->get('type')) {
            case 'admin_bookings_state':
                settings_save('eto_booking.admin_bookings_state.'. $page, $state, 'user', auth()->user()->id, true);
                $status = true;
            break;
            case 'admin_dispatch_state':
                settings_save('eto_booking.admin_dispatch_state.'. $page, $state, 'user', auth()->user()->id, true);
                $status = true;
            break;
        }

        return response()->json(['status' => $status, 'message' => $message]);
    }

    //// start
    /*
    public function update(Request $request)
    {
        $response = [
            'errors' => [],
            'messages' => [],
        ];
        $options = [];
        $rules = [];

        $sections = [
            'general',
        ];

        if ($request->get('sections')) {
            $sections = (array)$request->get('sections');
        }

        if (in_array('general', $sections)) {
            $options = array_merge([
                'logo',
            ], $options);

            $rules = array_merge([
                'logo' => 'mimes:jpg,jpeg,gif,png',
            ], $rules);
        }

        $settings = \App\Models\Config::ofSite()->whereKeys($options)->toObject();

        foreach ($options as $option) {
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $response['errors'] = array_merge($response['errors'], $validator->errors()->all());
            }
            else {
                $config = \App\Models\Config::ofSite()->whereKey($option)->first();

                if ( empty($config->id) ) {
                    $config = new \App\Models\Config;
                    $config->site_id = config('site.site_id');
                    $config->key = $option;
                    $config->value = '';
                    $config->type = 'string';
                    $config->browser = 0;
                }

                switch( $option ) {
                    case 'logo':

                        $logo = $settings->logo;

                        if ( \Storage::disk('logo')->exists($settings->logo) && $request->get('logo_delete') ) {
                            \Storage::disk('logo')->delete($settings->logo);
                            $logo = '';
                        }

                        if ( $request->hasFile('logo') ) {
                            $file = $request->file('logo');
                            $filename = \App\Helpers\SiteHelper::generateFilename('logo') .'.'. $file->getClientOriginalExtension();

                            $img = \Image::make($file);

                            if ($img->width() > config('site.image_dimensions.logo.width')) {
                                $img->resize(config('site.image_dimensions.logo.width'), null, function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                            }

                            if ($img->height() > config('site.image_dimensions.logo.height')) {
                                $img->resize(null, config('site.image_dimensions.logo.height'), function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                            }

                            $img->save(asset_path('uploads','logo/'. $filename));

                            if ( \Storage::disk('logo')->exists($filename) ) {
                                $logo = $filename;
                                if ( \Storage::disk('logo')->exists($settings->logo) ) {
                                    \Storage::disk('logo')->delete($settings->logo);
                                }
                            }
                        }

                        $config->value = $logo;

                        $response['logo'] = $logo;

                    break;
                    case 'notifications':

                        if ( empty($config->id) ) {
                            $config->type = 'object';
                        }
                        $config->value = json_encode($request->get($option, []));

                    break;
                    default:

                        $config->value = $request->get($option);

                    break;
                }

                $config->save();

                if ( empty($config->id) ) {
                    $response['errors'][] = trans('admin/settings.message.save_error');
                }
                else {
                    $response['messages'][] = trans('admin/settings.message.saved');
                }
            }
        }

        if ( $request->ajax() ) {
            if ( empty($response['errors']) ) {
                unset($response['errors']);
            }
            if ( empty($response['messages']) ) {
                unset($response['messages']);
            }
            return $response;
        }
        else {
            if ( !empty($response['messages']) ) {
                session()->flash('message', implode(', ', $response['messages']));
            }
            return redirect()->back()->withErrors($response['errors']);
        }
    }
    */
    //// end
}
