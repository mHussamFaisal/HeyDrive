<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use File;

class SettingsHelper
{
    protected static $expireHours = 12;

    protected static $hierarchyRelation = [
        'widget',
        'user',
        'site',
        'subscription',
        'system'
    ];

    protected static $casts = [
        // 'pricing.test' => 'float',
    ];

    public static function get($param = null, $relation = null)
    {
        $default = Cache::store('file')->get('override_default') ?: [];

        if (empty($default)) {
            $expiresAt = \Carbon\Carbon::now()->addHours(self::$expireHours);
            $files = File::files(base_path('config'));

            foreach ($files as $file) {
                $paramFile = preg_replace('#\..*$#', '', basename($file));
                $default[$paramFile] = include($file);
            }

            Cache::store('file')->put('override_default', $default, $expiresAt);
        }

        $configData = (is_null($param) || empty($param)) ? $default : config($param);
        // check for user id, site id, sub_id, make as loop
        // https://laravel.com/docs/5.8/helpers
        // https://github.com/illuminate/support/blob/5.3/helpers.php

        switch ($relation) {
            case 'user':
                $allowed = ['user', 'site', 'subscription', 'system'];
            break;
            case 'site':
                $allowed = ['site', 'subscription', 'system'];
            break;
            case 'subscription':
                $allowed = ['subscription', 'system'];
            break;
            case 'system':
                $allowed = ['system'];
            break;
            case 'default':
                $allowed = ['default'];
            break;
            default:
                $allowed = ['user', 'site', 'subscription', 'system'];
            break;
        }

        foreach($allowed as $rel) {
            $key = !is_null($param) && !empty($param) ? $param : null;
            $relationList = self::relations($rel);
            $data = Cache::store('file')->get('override_'.$rel.'_' . $relationList[$rel]);

            $config = $data && $key ? \Illuminate\Support\Arr::get($data, $key) : null;
            if (!is_null($config)) {
                $configData = is_array($configData)
                    ? array_replace_recursive($configData, $config)
                    : $config;
                break;
            }
        }

        return $configData;
    }

    public static function load($param = null, $relation = null, $forceReload = false)
    {
        $request = request();
        if ($request->system->subscription->id == 0) { return false; }

        $getQuery = false;
        $overrideKeys = [];
        $expiresAt = \Carbon\Carbon::now()->addHours(self::$expireHours);
        $relationList = self::relations($relation);
        $default = Cache::store('file')->get('override_default') ?: [];
        $override = ['default' => $default];

        if (empty($default)) {
            $files = File::files(base_path('config'));

            foreach ($files as $file) {
                $paramFile = preg_replace('#\..*$#', '', basename($file));
                $override['default'][$paramFile] = include($file);
            }

            Cache::store('file')->put('override_default', $override['default'], $expiresAt);
        }

        config($override['default']);

        foreach ($relationList as $r=>$rId) {
            if ($r == 'default') {
                continue;
            }

            $data = $forceReload === false ? Cache::store('file')->get('override_'.$r.'_'.$rId) : null;
            $override[$r] = !is_null($data) && !empty($param) ? array_get($data, $param) : $data;

            if (!$override[$r]) {
                $getQuery = true;
            }
            elseif ($r != 'default') {
                config(array_replace_recursive(\Config::all(), $override[$r]));
            }
        }

        if ($getQuery) {
            $q = Setting::select('*');

            if (!empty($param)) {
                $q->where('param', 'like', $param .'%');
            }

            $settings = $q->where(function($q2) use ($override, $relationList) {
                foreach ($override as $r=>$rData) {
                    if ($r != 'default' && empty($rData)) {
                        $ids = array_replace_recursive([0], [$relationList[$r]]);
                        $q2->orWhere(function($q3) use ($ids, $r) {
                            $q3->where('relation_type', $r);
                            $q3->whereIn('relation_id', $ids);
                        });
                    }
                }
            })
            ->orderBy('relation_type', 'asc')
            ->orderBy('relation_id', 'asc')
            ->orderBy('param', 'asc')
            ->get();

            foreach ($settings as $k => $v) {
                $v = (object)$v;
                $type = !empty(self::$casts[$v->param]) ? self::$casts[$v->param] : value_type_of($v->value);
                $v->value = value_cast_to($v->value, $type);

                data_set($override[$v->relation_type], $v->param, $v->value);
                $overrideKeys[$v->relation_type][$v->param] = $v->value;
            }

            foreach ($relationList as $r=>$rId) {
                if ($r != 'default') {
                    Cache::store('file')->forget('override_' . $r . '_' . $rId);

                    if (!is_null($override[$r])) {
                        Cache::store('file')->put('override_' . $r . '_' . $rId, $override[$r], $expiresAt);
                    }
                }
            }
        }

        $settings = array_merge($request->system->config ?: [], [
            'override.default' => $override['default'],
            'site.site_id' => $request->system->site->id,
            'site.site_key' => $request->system->site->key,
            'session.secure' => eto_config('SESSION_COOKIE_SECURE', false),
            'session.same_site' => eto_config('SESSION_COOKIE_SAME_SITE', null),
            // 'session.cookie' => eto_config('SESSION_COOKIE_NAME', 'eto_session'),
            // 'session.xsrf_token' => eto_config('SESSION_COOKIE_XSRF_TOKEN', 'XSRF-TOKEN'),
        ]);

        for ($i=count(self::$hierarchyRelation)-1; $i >= 0; $i--) {
            $hRel = self::$hierarchyRelation[$i];

            if (!empty($overrideKeys[$hRel])) {
                $settings = array_merge($settings, $overrideKeys[$hRel]);
            }
        }

        unset($overrideKeys);
        unset($relationList);

        // Mobile app expire date
        if (!empty(config('site.expiry_driver_app')) && \Carbon\Carbon::parse(config('site.expiry_driver_app'))->lte(\Carbon\Carbon::now())) {
            $settings['site.allow_driver_app'] = 0;
        }

        if (session('admin_site_id') && !(
           $request->is('/') || $request->is('locale/*') || $request->is('payment-waiting') ||
           $request->is('booking') || $request->is('booking/*') ||
           $request->is('customer') || $request->is('customer/*') ||
           $request->is('feedback') || $request->is('feedback/*') ||
           ($request->is('etov2') && $request->get('apiType') == 'frontend' && $request->get('isAdminRequest') != 1)
        )) {
           $settings['site.site_id'] = session('admin_site_id');
        }

        config($settings);

        return $override;
    }

    public static function save($param = null, $value = null, $type = 'system', $id = 0, $reloadCache = false)
    {
        if (is_array($param)) {
            foreach ($param as $k => $v) {
                self::setSettings($v[0], $v[1], $v[2], $v[3]);
            }
        }
        else {
            self::setSettings($param, $value, $type, $id);
        }

        $relation = !is_array($type) ? $type : [$type];

        // if ($reloadCache) {
            self::load(null, null, $reloadCache);
        // }
    }

    private static function setSettings($param = null, $value = null, $type = 'system', $id = 0)
    {
        if (!is_null($param) && !is_null($type) && !is_null($id)) {
            $typeVal = value_type_of($value);
            $castValue = value_cast_to($value, $typeVal);

            $skip = $type == 'user' && in_array($param, ['app.locale', 'app.timezone']);

            if (config('override.default.'. $param) !== $castValue || $skip) {
                if (config($param) !== $castValue || $skip) {
                    $dbPrefix = get_db_prefix();
                    \DB::statement("INSERT INTO `{$dbPrefix}settings` (`relation_type`, `relation_id`, `param`, `value`)
                        VALUES ('{$type}', '{$id}', '{$param}', '{$value}')
                        ON DUPLICATE KEY UPDATE `value` = VALUES (`value`)");
                }
            } else {
                self::delete($param, $type, $id);
            }
        }
    }

    public static function delete($param = null, $type = 'system', $id = 0)
    {
        return \DB::table('settings')
            ->where('relation_type', $type)
            ->where('relation_id', $id)
            ->where('param', 'like', str_replace('*', '%', $param))
            ->delete();
    }

    public static function disk_extends()
    {
        $disks = self::get('filesystems.disks', 'user');

        if (!empty($disks)) {
            foreach ($disks as $name => $disk) {
                $conf = [];
                if (is_string($name) && !empty($disk['driver']) && ($disk['driver'] == 'ftp' || $disk['driver'] == 'sftp')) {
                    $conf['driver'] = $disk['driver'];
                    if (!empty($disk['host'])) {
                        $conf['host'] = $disk['host'];
                    }
                    if (!empty($disk['username'])) {
                        $conf['username'] = $disk['username'];
                    }
                    if (!empty($disk['password'])) { // Settings for SSH key based authentication - encryption-password
                        $conf['password'] = $disk['password'];
                    }

                    // Settings for SSH key based authentication...
                    if (!empty($disk['private_key'])) {
                        $conf['privateKey'] = $disk['private_key'];
                    }

                    // Optional SFTP and FTP Settings...
                    $conf['port'] = !empty($disk['port']) ? (int)$disk['port'] : ($disk['driver'] == 'ftp' ? 21 : 22);
                    $conf['root'] = !empty($disk['root']) ? $disk['root'] : '';
                    $conf['timeout'] = !empty($disk['timeout']) ? (int)$disk['timeout']: 30;

                    // Optional FTP Settings...
                    if ($disk['driver'] == 'ftp') {
                        $conf['passive'] = !empty($disk['passive']) ? (bool)$disk['passive'] : true;
                        $conf['ssl'] = !empty($disk['ssl']) ? (bool)$disk['ssl'] : true;
                    }
                }
                else if (is_string($name) && !empty($disk['driver']) && $disk['driver'] == 'rackspace') {
                    $conf['driver'] = $disk['driver'];
                    if (!empty($disk['endpoint'])) {
                        $conf['endpoint'] = $disk['endpoint'];
                    }
                    if (!empty($disk['username'])) {
                        $conf['username'] = $disk['username'];
                    }
                    if (!empty($disk['key'])) {
                        $conf['key'] = $disk['key'];
                    }

                    $conf['container'] = !empty($disk['container']) ? $disk['container'] : '';
                    $conf['region'] = !empty($disk['region']) ? $disk['region'] : 'LHR';

                    if (!empty($disk['url_type)'])) {
                        $conf['url_type'] = $disk['url_type'];
                    }
                }
                else if (is_string($name) && !empty($disk['driver']) && $disk['driver'] == 's3') {
                    $conf['driver'] = $disk['driver'];
                    if (!empty($disk['key'])) {
                        $conf['key'] = $disk['key'];
                    }
                    if (!empty($disk['secret'])) {
                        $conf['secret'] = $disk['secret'];
                    }
                    if (!empty($disk['region)'])) {
                        $conf['region'] = $disk['region'];
                    }
                    if (!empty($disk['bucket'])) {
                        $conf['bucket'] = $disk['bucket'];
                    }
                }

                if (!empty($conf)) {
                    config(["filesystems.disks.{$name}" => $conf]);
                }
            }
            return true;
        }
        return false;
    }

    protected static function relations($relation = null)
    {
        $request = request();
        $relations = [];

        if (!empty($relation)) {
            if (is_array($relation)) {
                foreach ($relation as $k => $v) {
                    $relations[$k] = $v;
                }
            }
            else {
                $id = 0;
                if ($relation == 'user') {
                    $user = auth()->user();
                    if (!empty($user->id)) {
                        $id = $user->id;
                    }
                }
                elseif ($relation == 'subscription') {
                    if (!empty($request->system->subscription->id)) {
                        $id = $request->system->subscription->id;
                    }
                }
                elseif ($relation == 'site') {
                    if (!empty($request->system->site->id)) {
                        $id = $request->system->site->id;
                    }
                }
                $relations[$relation] = $id;
            }
        }
        else {
            $relations['system'] = 0;

            if (!empty($request->system->subscription->id)) {
                $relations['subscription'] = $request->system->subscription->id;
            }

            if (!empty($request->system->site->id)) {
                $relations['site'] = $request->system->site->id;
            }

            $user = auth()->user();
            if (!empty($user->id)) {
                $relations['user'] = $user->id;
            }
        }
        return $relations;
    }

    // Loading configuration to view JS
    public static function getJsConfig($site_id = false)
    {
        $data = ['config' => [], 'current_user' => self::getUserForJs(), 'lang' => []];
        $data['config'] = [
            'csrfToken' => csrf_token(),
            'appPath' => url('/'),
            'timestamp' => config('app.timestamp'),
            'timezone' => config('app.timezone'),
            'currency_symbol' => config('site.currency_symbol'),
            'currency_code' => config('site.currency_code'),
            'date_format' => config('site.date_format'),
            'time_format' => config('site.time_format'),
            'date_start_of_week' => config('site.start_of_week'),
            'site_id' => $site_id ?: config('site.site_id'),
        ];

        $routeName = \Route::currentRouteName();
        $config = json_decode(file_get_contents(base_path('assets/js/eto/eto.json')), true);

        if (!empty($config[$routeName])) {
            $params = $config[$routeName];

            if (!empty($params['config'])) {
                if (in_array('booking_list', $params['config'])) {
                    $eto_booking = config('eto_booking');
                    unset($eto_booking['admin_bookings_state']);
                    unset($eto_booking['admin_dispatch_state']);
                    $data['config']['eto_booking'] = $eto_booking;
                    $data['config']['page'] = str_slug(request('page', 'all'), '_');
                    $data['config']['booking'] = [
                        'meeting_board_enabled' => config('site.booking_meeting_board_enabled'),
                        'refresh_interval' => config('site.booking_listing_refresh_interval'),
                        'refresh_type' => config('site.booking_listing_refresh_type'),
                        'refresh_counter' => config('site.booking_listing_refresh_counter'),
                        'allow_driver_app' => config('site.allow_driver_app'),
                        'allow_fleet_operator' => config('eto.allow_fleet_operator'),
                        'allow_activitylog' => config('laravel-activitylog.enabled'),
                    ];
                }
                if (in_array('booking_form', $params['config'])) {
                    $eto_booking = config('eto_booking');
                    unset($eto_booking['admin_bookings_state']);
                    unset($eto_booking['admin_dispatch_state']);
                    $data['config']['eto_booking'] = $eto_booking;
                    $data['config']['origin_status_color'] = config('override.default.eto_booking.status_color');
                    $data['config']['locale_list'] = self::getLocaleList();
                    $data['config']['time_every_minute'] = config('eto_dispatch.time_every_minute');
                    $bookingController = new \App\Http\Controllers\BookingController2();
                    $data['config']['formObject'] = $bookingController->generateDefaultObject();
                }

                if (in_array('state_bookings', $params['config'])) {
                    $page = str_slug(request('page', 'all'), '_');
                    $eto_booking = config('eto_booking');
                    $keyParam = 'admin_bookings_state';
                    $data['config']['eto_booking'][$keyParam] = isset($eto_booking[$keyParam][$page]) ? $eto_booking[$keyParam][$page] : null;
                }

                if (in_array('state_dispatch', $params['config'])) {
                    $eto_booking = config('eto_booking');
                    $keyParam = 'admin_dispatch_state';
                    $data['config']['eto_booking'][$keyParam] = isset($eto_booking[$keyParam]) ? $eto_booking[$keyParam] : null;
                }

                if (in_array('map', $params['config'])) {
                    $data['current_user']['settings']['eto_map'] = config('eto_map'); // do usunięcia
                    $data['config']['subscription']['eto_map'] = config('eto_map');
                    $data['config']['driverApp'] = config('site.allow_driver_app');
                    $data['config']['eto']['interval'] = config('eto.interval');
                }
                if (in_array('bookingStatusColor', $params['config'])) {
                    $data['config']['bookingStatusColor'] = (new \App\Models\BookingRoute)->options->status;
                }
                if (in_array('vehicle', $params['config'])) {
                    $data['config']['vehicle'] = self::getVehicles($data['config']['site_id']);
                }
                if (in_array('service', $params['config'])) {
                    $data['config']['service'] = self::getServices($data['config']['site_id']);
                }
                if (in_array('source', $params['config'])) {
                    $data['config']['source'] = self::getSourceList($data['config']['site_id']);
                }
                if (in_array('booking_status', $params['config'])) {
                    $data['config']['bookingStatus'] = self::getBookingStatusList();
                }
                if (in_array('payment_type', $params['config'])) {
                    $data['config']['paymentType'] = self::getPaymentTypeList($data['config']['site_id']);
                }
                if (in_array('driver', $params['config'])) {
                    $data['config']['driver_income'] = config('eto_driver.income');
                    $data['config']['driver_income_total'] =
                        (int)config('eto_driver.income.child_seats') === 1
                        && (int)config('eto_driver.income.additional_items') === 1
                        && (int)config('eto_driver.income.parking_charges') === 1
                        && (int)config('eto_driver.income.payment_charges') === 1
                        && (int)config('eto_driver.income.meet_and_greet') === 1
                        && (int)config('eto_driver.income.discounts') === 1;
                    $data['lang']['driver_income'] = [
                        'child_seats' => trans('driver/jobs.child_seats'),
                        'additional_items' => trans('admin/config.driver_income.additional_items'),
                        'parking_charges' => trans('admin/config.driver_income.parking_charges'),
                        'payment_charges' => trans('admin/config.driver_income.payment_charges'),
                        'meet_and_greet' => trans('driver/jobs.meet_and_greet'),
                        'discounts' => trans('roles.permissions.discounts'),
                    ];
                }
                if (in_array('map_icons', $params['config'])) {
                    if (file_exists(asset_path('uploads', 'map_pin.png'))) {
                        $data['config']['icons']['carBlack'] = asset_url('uploads', 'map_pin.png');
                    } else {
                        $data['config']['icons']['carBlack'] = asset_url('images', 'icons/car-black.png');
                    }
                }
                if (in_array('map_routes', $params['config'])) {
                    $data['config']['routes'] = [
                        'mapDrivers' => route('dispatch.map-drivers'),
                    ];
                }
                if (in_array('report', $params['config'])) {
                    $data['config']['eto_report'] = config('eto_report');
                }
                if (in_array('backup', $params['config'])) {
                    $data['current_user']['settings']['filesystems']['disks']['backup_ftp'] = config('filesystems.disks.backup_ftp');
                }
                if (in_array('notifications', $params['config'])) {
                    $data['config']['notifications'] = config('site.notifications');
                }
                if (in_array('google', $params['config'])) {
                    $data['config']['google'] = [
                        'booking_map_zoom' => config('site.booking_map_zoom'),
                        'booking_map_draggable' => config('site.booking_map_draggable'),
                        'booking_map_zoomcontrol' => config('site.booking_map_zoomcontrol'),
                        'booking_map_scrollwheel' => config('site.booking_map_scrollwheel'),
                        'google_region_code' => config('site.google_region_code'),
                        'quote_avoid_highways' => config('site.quote_avoid_highways'),
                        'quote_avoid_tolls' => config('site.quote_avoid_tolls'),
                        'quote_avoid_ferries' => config('site.quote_avoid_ferries'),
                        'quote_enable_shortest_route' => config('site.quote_enable_shortest_route'),
                    ];
                }
            }
            // Langs
            if (!empty($params['lang'])) {
                if (in_array('booking', $params['lang'])) {
                    $bookingLang = array_merge(trans('frontend.js'), trans('admin/bookings'));
                    $bookingLang = array_merge($bookingLang, trans('backend.old'));
                    $bookingLang = array_merge($bookingLang, trans('frontend.old'));
                    $bookingLang = array_merge($bookingLang, trans('booking'));
                    $savedLocale = app()->getLocale();
                    app()->setLocale(config('app.fallback_locale'));
                    $bookingLangEN = array_merge(trans('frontend.js'), trans('admin/bookings'));
                    $bookingLangEN = array_merge($bookingLangEN, trans('backend.old'));
                    $bookingLangEN = array_merge($bookingLangEN, trans('frontend.old'));
                    $bookingLangEN = array_merge($bookingLangEN, trans('booking'));
                    app()->setLocale($savedLocale);
                    $bookingLang = array_merge($bookingLangEN, $bookingLang);
                    $data['lang']['booking'] = $bookingLang;
                    unset($bookingLang);

                    // if (!empty($data['config']['subscription']['booking']['custom_field_name'])) {
                    //    $data['lang']['booking']['custom_field_name'] = $data['config']['subscription']['booking']['custom_field_name'];
                    // }
                }
                if (in_array('map', $params['lang'])) {
                    $data['lang']['map'] = self::getLangData('admin/map');
                }
                if (in_array('user', $params['lang'])) {
                    $data['lang']['user'] = self::getLangData('admin/users');
                }
                if (in_array('translations', $params['lang'])) {
                    $data['lang']['translations'] = self::getLangData('translations');
                }
                if (in_array('reports', $params['lang'])) {
                    $data['lang']['reports'] = self::getLangData('reports');
                }
                if (in_array('backup', $params['lang'])) {
                    $data['lang']['backup'] = self::getLangData('backup');
                }
                if (in_array('notifications', $params['lang'])) {
                    $data['lang']['notifications'] = self::getLangData('admin/settings.notifications');
                }
                if (in_array('export', $params['lang'])) {
                    $data['lang']['export'] = trans('export');
                    $data['lang']['common']['booking_status_options'] = trans('common.booking_status_options');
                    $data['lang']['common']['user_status_options'] = trans('common.user_status_options');
                    $data['lang']['common']['user_profile_type_options'] = trans('common.user_profile_type_options');
                    $data['lang']['common']['vehicle_status_options'] = trans('common.vehicle_status_options');
                    $data['lang']['admin/feedback']['statuses'] = trans('admin/feedback.statuses');
                    $data['lang']['admin/feedback']['types'] = trans('admin/feedback.types');
                }
                if (in_array('roles', $params['lang'])) {
                    $data['lang']['roles'] = self::getLangData('roles');
                }
                if (in_array('subscription', $params['lang'])) {
                    $data['lang']['subscription'] = self::getLangData('subscription');
                }
            }
            // Fields
            if (!empty($params['fields'])) {
                if (in_array('booking', $params['fields'])) {
                    $data['fields']['booking'] = self::getItems($data['config']['site_id']);
                }
            }
        }

        return $data;
    }

    private static function getLangData($key)
    {
        if (!is_string($key)) { return []; }

        $lang = trans($key);
        $savedLocale = app()->getLocale();
        app()->setLocale(config('app.fallback_locale'));
        $langEN = (array)trans($key);
        app()->setLocale($savedLocale);

        return array_merge($langEN, $lang);
    }

    private static function getUserForJs($id = false)
    {
        if (!empty(eto_config('APP_NAME')) && auth()->check()) {
            $user = auth()->user();
            $data = [];

            if ($user->id) {
                $data = $user->toArray();
                $data['permissions'] =  auth()->user()->getPermissions()->pluck('slug');
                $data['roles'] =  auth()->user()->getRoles()->pluck('slug');
                $data['role'] = $user->role;
                $data['avatar'] = $user->getAvatarPath();

                unset($data['used_role_rel']);
                unset($data['created_at']);
                unset($data['updated_at']);
                unset($data['heading']);
                unset($data['accuracy']);
                unset($data['status']);
                unset($data['used_role']);
                unset($data['last_seen_at']);
            }

            return $data;
        }

        return [];
    }

    private static function getLocaleList()
    {
        $localeList = [['code'=>'', 'name'=>'Default', 'selected'=>true]];

        foreach(config('app.locales') as $id=>$locale) {
            $localeList[$id] = $locale;
        }

        return ['data' => $localeList, 'selected' => 0, 'length' => count($localeList)];
    }

    private static function getVehicles($siteId = false)
    {
        if (!$siteId) { return []; }
        $result = [];
        $selected = 0;
        $result[0]['id'] = 0;
        $result[0]['name'] = trans('booking.booking_unassigned');
        $result[0]['selected'] = 0;
        $data = \App\Models\VehicleType::select(['id', 'name', 'default'])
            ->where('site_id', $siteId)
            ->orderBy('ordering', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        foreach($data as $id=>$vehicle) {
            $result[] = [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'selected' => $vehicle->default
            ];
            if ($vehicle->default == '1') { $selected = $vehicle->id; }
        }
        return ['data' => $result,  'selected' => $selected];
    }

    public static function getServices($siteId = false)
    {
        $servicesList = [];
        $selected = 0;
        $services = \App\Models\Service::where('relation_type', 'site')
            ->where('relation_id', $siteId)
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        foreach($services as $k => $v) {
            $params = $v->getParams('raw');
            $servicesList[] = [
                'id' => (int)$v->id,
                'name' => (string)$v->name,
                'type' => (string)$v->type,
                'availability' => (int)$params->availability,
                'hide_location' => (int)$params->hide_location,
                'duration' => (int)$params->duration,
                'duration_min' => (int)$params->duration_min,
                'duration_max' => (int)$params->duration_max,
                'is_featured' => (int)$v->is_featured,
                'min' => $params->duration_min,
                'max' => $params->duration_max,
            ];

            if ($v->is_featured > 0) {
                $selected = (int)$v->id;
            }
        }

        $servicesList[] = [
            'id' => 0,
            'name' => trans('booking.booking_unassigned'),
            'type' => 'standard',
            'hide_location' => 0,
            'availability' => 0,
            'duration' => 0,
            'duration_min' => 1,
            'duration_max' => 10,
            'is_featured' => 0,
            'min' => 1,
            'max' => 10,
        ];

        return ['data' => $servicesList, 'selected' => $selected, 'length' => count($servicesList)];
    }

    public static function getSourceList($siteId = false)
    {
        $sourceNotIn = [];
        $sourceList = [];

        $configSource = config('eto_booking.sources');
        foreach($configSource as $key => $value) {
            $sourceList[$value] = ['id'=> $value, 'name'=> $value];
            if ($value == 'Admin') { $sourceList[$value]['selected'] = true; }
            $sourceNotIn[] = $value;
        }

        $resultsSites = \App\Models\Site::select('id', 'name')
            ->where('published', '1')
            ->orderBy('ordering', 'asc')
            ->orderBy('name', 'desc')
            ->get();

        foreach($resultsSites as $key => $value) {
            if (empty($value->name)) {
                $sourceList[$value->name] = ['id' => $value->name, 'name' => $value->name];
                $sourceNotIn[] = $value->name;
            }
        }

        // $bookingSources = \App\Models\BookingRoute::select('source')
        //     ->distinct('source')
        //     ->whereNotIn('source', $sourceNotIn)
        //     ->orderBy('source', 'desc')
        //     ->get();
        //
        // foreach($bookingSources as $key => $value) {
        //     if (!empty($value->source)) {
        //         $sourceList[$value->source] = ['id' => $value->source, 'name' => $value->source];
        //     }
        // }

        $sourceList[trans('booking.booking_unassigned')] = ['id' => '', 'name' => trans('booking.booking_unassigned')];

        return ['data'=>$sourceList];
    }

    private static function getBookingStatusList()
    {
        $statusList = [];

        $statuses = (new \App\Models\BookingRoute)->getStatusList();

        foreach($statuses as $id=>$status) {
            $statusList[$id] = [
                'id' => $status->value,
                'name' => '<span style="color:'. $status->color .';">'. $status->text .'</span>',
            ];

            if ($status->value == 'pending') {
                $statusList[$id]['selected'] = true;
            }
        }

        return ['data'=>$statusList];
    }

    public static function getPaymentTypeList($siteId = false)
    {
        $paymentList = [];
        $selected = 0;

        $payments = \App\Models\Payment::where('site_id', $siteId)
            ->where('published', 1)
            ->orderBy('ordering', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        foreach($payments as $id=>$payment) {
            $paymentList[$id] = $payment;
            if ($payment->default == '1') {
                $selected = $payment->id;
                $paymentList[$id]['selected'] = true;
            }
        }

        $paymentList[] = (object)[
            'id' => 0,
            'name' => trans('booking.booking_unassigned'),
            'selected' => $selected === 0 ? true : false
        ];

        return ['data' => $paymentList, 'selected' => $selected, 'length' => count($paymentList)];
    }

    public static function getItems($siteId = false)
    {
        $itemValues = [];
        $fields = \App\Models\Field::toList('booking', $siteId)->isActive()->get();
        $items = \App\Models\Field::translateNames($fields);
        $charges = \App\Models\Charge::where('site_id', $siteId)->get();

        foreach($charges as $charge) {
            $itemValues[$charge->type] = $charge->value;
        }

        foreach($items as $k=>$v) {
            if ($v->field_key == 'stopover') {
                $key = 'waypoint';
            } else {
                $key = isset($itemValues[$v->field_key . 's']) ? $v->field_key . 's' : $v->field_key;
            }

            if (isset($itemValues[$key])) {
                $items[$k]->price = $itemValues[$key];
            }
        }

        $config = \App\Models\Config::where('site_id', $siteId)->where('key', 'booking_items')->first();
        $additionalItems = empty($config->value) ? [] : json_decode($config->value, true);
        end($items);
        $nr = key($items);

        foreach($additionalItems as $id => $item) {
            $nr++;
            $items[$nr] = [
                'id' => $nr,
                'section' => ['item'],
                'field_key' => 'other_'.str_slug($item['name'], '_'),
                'type' => 'number_spin',
                'is_core' => 0,
                'is_edit' => 1,
                'is_required' => 0,
                'params' => [
                    'label' => '',
                    "trans_key" => $item['name'],
                    'placeholder' => '',
                    'help' => '',
                    'name' => '',
                    'is_override' => 1,
                    'is_price' => 1,
                ],
                'label' => $item['name'],
                'amount' => $item['amount'],
                'price' => $item['value'],
            ];
        }

        return ['data'=>$items];
    }
}
