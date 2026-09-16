<?php

use App\Helpers\SettingsHelper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!function_exists('is_ssl')) {
    function is_ssl() {
        if (isset($_SERVER['HTTPS'])) {
            if (strtolower($_SERVER['HTTPS']) == 'on') {
                return true;
            }
            elseif ($_SERVER['HTTPS'] == '1') {
                return true;
            }
        }
        elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
            return true;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return true;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on') {
            return true;
        }
        return false;
    }
}

if (!function_exists('get_base_url')) {
    function get_base_url() {
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        }
        elseif (isset($_SERVER['REQUEST_URI'])) {
            $baseUrl = $_SERVER['REQUEST_URI'];
        }
        elseif (isset($_SERVER['PHP_SELF'])) {
            $baseUrl = $_SERVER['PHP_SELF'];
        }
        else {
            $baseUrl = '';
        }
        return rtrim(dirname($baseUrl), '/\\');
    }
}

if (!function_exists('value_type_of')) {
    function value_type_of($value = null) {
        if (((is_int($value) || preg_match('/^[-+]?[0-9]*$/', $value)) && ($value != '' || $value === 0))
            && !preg_match("/^(true|false)$/", is_bool($value)?($value?'true':'false'):$value)
        ) {
            return 'int';
        }
        else if ((preg_match('/^[-+]?(\d*\.\d+)$/', $value) || $value == '0.0' || $value == '0.00')
            && !preg_match("/^(true|false)$/", is_bool($value)?($value?'true':'false'):$value)
        ) {
            return 'float';
        }
        else if (preg_match("/^(true|false)$/", is_bool($value)?($value?'true':'false'):$value)) {
            return 'bool';
        }
        // else if (is_object(json_decode($value))) {
        //    return 'object';
        // }
        else if (is_array(json_decode($value, true))) {
            return 'array';
        }
        // else if (is_array(json_decode($value, true))) {
        //    return 'json';
        // }
        // else if (is_array($value)) {
        //    return 'array';
        // }
        else if (is_string($value)) {
            return 'string';
        }
        else {
            return 'undefined';
        }
    }
}

if (!function_exists('value_cast_to')) {
    function value_cast_to($value = null, $type = null, $read = true) {
        switch ($type) {
            case 'int':
                return (int)$value;
            break;
            case 'float':
                return (float)$value;
            break;
            case 'bool':
                if ($value == 'true' || $value === true) {
                    return $read == true ? true : 'true';
                }
                else {
                    return $read == true ? false : 'false';
                }
            break;
            case 'object':
                return $read == true ? json_decode($value) : json_encode($value);
            break;
            case 'array':
                return $read == true ? json_decode($value, true) : json_encode($value, true);
            break;
            // case 'json':
            //    return $read == true ? json_decode($value) : $value;
            // break;
            // case 'array':
            //    return $read == true ? $value : json_encode($value, true);
            // break;
            case 'string':
            default:
                return (string)$value;
            break;
        }
    }
}

if (!function_exists('settings_save')) {
    function settings_save($param = null, $value = null, $type = 'system', $id = 0, $reloadCache = false) {
        SettingsHelper::save($param, $value, $type, $id, $reloadCache);
    }
}

if (!function_exists('settings_delete')) {
    function settings_delete($param = null, $type = 'system', $id = 0) {
        return SettingsHelper::delete($param, $type, $id);
    }
}

if (!function_exists('settings_load')) {
    function settings_load($param = null, $relation = null, $forceReload = false) {
        return SettingsHelper::load($param, $relation, $forceReload);
    }
}

if (!function_exists('settings')) {
    function settings($param = null, $relation = null) {
        return SettingsHelper::get($param, $relation);
    }
}

if (!function_exists('settings_user')) {
    function settings_user($param = null) {
        return settings($param, 'user');
    }
}

if (!function_exists('settings_site')) {
    function settings_site($param = null) {
        return settings($param, 'site');
    }
}

if (!function_exists('settings_subscription')) {
    function settings_subscription($param = null) {
        return settings($param, 'subscription');
    }
}

if (!function_exists('settings_system')) {
    function settings_system($param = null) {
        return settings($param, 'system');
    }
}

if (!function_exists('settings_default')) {
    function settings_default($param = null) {
        return settings($param, 'default');
    }
}

if (!function_exists('disk_extends')) {
    function disk_extends() {
        return SettingsHelper::disk_extends();
    }
}

if (!function_exists('disk_url')) {
    function disk_url($name, $path = null, $secure = null) {
        $disks = config('filesystems.disks');
        if ($path == null) {
            $path = $name;
        }
        elseif ($name != '' && array_key_exists($name, $disks)) {
            $path = ltrim($disks[$name]['url'] .'/'. $path, '/');
        }
        else {
            $path = $disks['assets']['url'] .'/'. $path;
        }
        return app('url')->asset($path, $secure);
    }
}

if (!function_exists('disk_path')) {
    function disk_path($name, $path = null) {
        $disks = config('filesystems.disks');
        if ($path == null) {
            $path = base_path($name);
        }
        elseif ($name != '' && array_key_exists($name, $disks)) {
            $path = $disks[$name]['root'] . DIRECTORY_SEPARATOR . $path;
        }
        else {
            $path = $disks['assets']['root'] . DIRECTORY_SEPARATOR . $path;
        }
        return parse_path($path, 'real');
    }
}

if (!function_exists('asset_url')) {
    function asset_url($name, $path = null, $secure = null) {
        return disk_url($name, $path, $secure);
    }
}

if (!function_exists('asset_path')) {
    function asset_path($name, $path = null) {
        return disk_path($name, $path);
    }
}

if (!function_exists('parse_path')) {
    function parse_path($path, $base = true) {
        $path = str_replace(['/','\\'], DIRECTORY_SEPARATOR, preg_replace('#/+#','/',$path));
        if ($base === true) {
            return preg_replace('#(\\\\+|\\\/+|\/+)#', DIRECTORY_SEPARATOR, base_path($path));
        }
        elseif ($base == 'real') {
            return preg_replace('#(\\\\+|\\\/+|\/+)#', DIRECTORY_SEPARATOR, $path);
        }
        elseif ($base == 'public') {
            return preg_replace('#(\\\\+|\\\/+|\/+)#', DIRECTORY_SEPARATOR, public_path($path));
        }
        return preg_replace('#(\\\\+|\\\/+|\/+)#', DIRECTORY_SEPARATOR, storage_path($path));
    }
}

if (!function_exists('format_date_time')) {
    function format_date_time($value = '', $type = 'datetime') {
        return \App\Helpers\SiteHelper::formatDateTime($value, $type);
    }
}

if (!function_exists('format_price')) {
    function format_price($value, $precision = 2, $nozeros = 0) {
        return \App\Helpers\SiteHelper::formatPrice($value, $precision, $nozeros);
    }
}

if (!function_exists('get_string_between')) {
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}

if (!function_exists('replace_string_between')) {
    function replace_string_between($string, $start, $end, $replacement){
        $toReplace = get_string_between($string, $start, $end);
        return str_replace($toReplace, $replacement, $string);
    }
}

if (!function_exists('check_expire')) {
    function check_expire($expire_at, $removeHours = false) {
        $now = \Carbon\Carbon::now();

        if ($removeHours) {
            $now->hour('00');
            $now->minute('00');
            $now->second('00');
        }

        $diff = null;
        $isExpire = false;
        if (null !== $expire_at && !empty($expire_at)) {
            if (!is_a($expire_at, 'Carbon\Carbon')) {
                $expire_at = \Carbon\Carbon::parse($expire_at);
            }
            $diff = $expire_at->diffInDays($now);
            $isExpire = $expire_at->gt($now);
        }
        return (object)['diff' => $diff, 'isExpire' => $isExpire === false && $diff !== null ? : false];
    }
}

if (!function_exists('dt_parse_sort')) {
    function dt_parse_sort($post) {
        $sortData = array();
        if (isset($post['order'])) {
            foreach($post['order'] as $key => $value) {
                $index = (int)$value['column'];
                $new = new stdClass();
                $new->property = $post['columns'][$index]['data'];
                $new->direction = ($value['dir'] == 'asc') ? 'ASC' : 'DESC';
                $sortData[] = $new;
            }
        }
        return \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($sortData));
    }
}

if (!function_exists('clear_tmp')) {
    function clear_tmp() {
        \App\Helpers\FilesystemHelper::clearTmp();
    }
}

if (!function_exists('clear_cache')) {
    function clear_cache($type = false, $store = false) {
        try {
            if (!$type || $type == 'cache') {
                if (!$store || $store == 'file') {
                   \Cache::store('file')->flush();
                }
                if (!$store || $store == 'database') {
                   \Cache::store('database')->flush();
                }
                // if ($store == 'apc') {
                //     \Cache::store('apc')->flush();
                // }
                // if ($store == 'array') {
                //     \Cache::store('array')->flush();
                // }
                // if ($store == 'memcached') {
                //     \Cache::store('memcached')->flush();
                // }
                // if ($store == 'redis') {
                //     \Cache::store('redis')->flush();
                // }
            }
            if (!$type || $type == 'view') {
                Illuminate\Support\Facades\Artisan::call('view:clear');
            }
            if (!$type || $type == 'config') {
                Illuminate\Support\Facades\Artisan::call('config:clear');
            }

            if (!$type || $type == 'cache' || $type == 'config') {
                settings_load();
            }
        }
        catch (Exception $e) {
            \Log::error('Cannot clear cache data (helper)');
        }
    }
}

if (!function_exists('get_ini_time')) {
    /**
     * @param int $time
     * @param bool $isCron
     * @return int|string
     */
    function get_ini_time($time = 300, $isCron = false) {
        $maxInputTime = @ini_get('max_input_time');
        $maxExecutionTime = @ini_get('max_execution_time');

        if (!empty($maxInputTime) && $maxInputTime != '-1') {
            if ($isCron === false) {
                @set_time_limit($time);
                @ini_set('max_execution_time', $time);
                @ini_set('max_input_time', $time);
                $maxInputTime = @ini_get('max_input_time');
                $maxExecutionTime = @ini_get('max_execution_time');
            }

            if ((int)$maxExecutionTime < $maxInputTime && $maxExecutionTime != '-1') {
                return $maxExecutionTime;
            }
            else {
                return $maxInputTime;
            }
        }
        elseif (!empty($maxExecutionTime) && $maxExecutionTime != '-1' && $maxInputTime == '-1') {
            if ($isCron === false) {
                @set_time_limit($time);
                @ini_set('max_execution_time', $time);
                @ini_set('max_input_time', $time);
                $maxInputTime = @ini_get('max_input_time');
                $maxExecutionTime = @ini_get('max_execution_time');
            }

            if ((int)$maxExecutionTime < $maxInputTime || $maxInputTime == '-1') {
                return $maxExecutionTime;
            }
            else {
                return $maxInputTime;
            }
        }

        return $time;
    }
}

if (!function_exists('custom_log')) {
    /**
     * @param $type
     * @param $message
     * @param array $attributes
     * @throws Exception
     */
    function custom_log($type = '', $message = false, $attributes = []) {
        if ($message == false) {
            $message = $type;
            $type = 'info';
        }
        $orderLog = new Logger($type);
        $orderLog->pushHandler(new StreamHandler(config('filesystems.disks.logs.root').'/'.$type.'-'.date('Y-m-d').'.log'), Logger::INFO);
        $orderLog->alert($message, $attributes);
    }
}

if (!function_exists('redirect_to')) {
    /**
     * @return string
     */
    function redirect_to() {
        $path = '/';

        if (auth()->check()) {
            if (auth()->user()->hasRole('admin.*')) {
                $page = config('site.admin_default_page');

                if ($page == 'bookings-latest') {
                    $path = '/admin/bookings?page=latest';
                }
                elseif ($page == 'bookings-next24') {
                    $path = '/admin/bookings?page=next24';
                }
                elseif ($page == 'bookings-unconfirmed') {
                    $path = '/admin/bookings?page=requested';
                }
                elseif ($page == 'getting-started') {
                    $path = '/admin/getting-started';
                }
                else {
                    $path = config('site.allow_dispatch') ? '/dispatch' : '/admin/bookings?page=latest';
                }
            }
            elseif (auth()->user()->hasRole('driver.*')) {
                if (auth()->user()->hasPermission('admin.bookings.index')) {
                    $path = '/driver';
                }
                else {
                    $path = 'driver/account';
                }
            }
            elseif (auth()->user()->hasRole('customer.*')) {
                $path = '/customer';
            }
        }

        return $path;
    }
}

if (!function_exists('redirect_no_permission')) {
    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    function redirect_no_permission() {
        if (auth()->check()) {
            if (auth()->user()->hasRole('admin.*')) {
                return redirect('admin/users/'.auth()->user()->id);
            }
            elseif (auth()->user()->hasRole('driver.*')) {
                return redirect('driver/account');
            }
             elseif (auth()->user()->hasRole('supplier.*')) {
                return redirect('supplier/account');
            }
        }

        abort(404);
    }
}

if (!function_exists('uuid')) {
    /**
     * @param int $lenght
     * @return false|string
     * @throws Exception
     */
    function uuid($lenght = 36) {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        }
        elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        }
        else {
            throw new Exception("no cryptographically secure random function available");
        }

        return substr(bin2hex($bytes), 0, $lenght);
    }
}

if (!function_exists('eto_config')) {
    function eto_config($key, $default = null) {
        $etoConfig = (array)app('etoConfig');

        if (isset($etoConfig[$key])) {
            $value = $etoConfig[$key];
        }
        else {
            $value = $default;
        }

        return $value;
    }
}

if (!function_exists('maintenance_mode')) {
    function maintenance_mode($type = 'check', $msg = '') {
        switch ($type) {
            case 'block':
                if (config('eto.multi_subscription')) {
                    \Storage::disk('root_subscription')->put('down', $msg ? $msg : \Carbon\Carbon::now());
                    return \Storage::disk('root_subscription')->exists('down');
                }
                else {
                    \Storage::disk('storage_framework')->put('down', $msg ? $msg : \Carbon\Carbon::now());
                    return \Storage::disk('storage_framework')->exists('down');
                }
            break;
            case 'unblock':
                if (config('eto.multi_subscription')) {
                    if (\Storage::disk('root_subscription')->exists('down')) {
                        \Storage::disk('root_subscription')->delete('down');
                    }
                }
                else {
                    if (\Storage::disk('storage_framework')->exists('down')) {
                        \Storage::disk('storage_framework')->delete('down');
                    }
                }
            break;
            case 'read':
                if (config('eto.multi_subscription')) {
                    if (\Storage::disk('root_subscription')->exists('down')) {
                        return \Storage::disk('root_subscription')->get('down');
                    }
                }
                else {
                    if (\Storage::disk('storage_framework')->exists('down')) {
                        return \Storage::disk('storage_framework')->get('down');
                    }
                }
                return null;
            break;
            default:
                if (config('eto.multi_subscription')) {
                    return \Storage::disk('root_subscription')->exists('down');
                }
                else {
                    return \Storage::disk('storage_framework')->exists('down');
                }
            break;
        }
    }
}

if (!function_exists('calculate_distance')) {
    function calculate_distance($newLat, $newLng, $lastLat, $lastLng) {
        $unit = (config('site.booking_distance_unit') == 1) ? 'K' : 'M';
        $theta = $newLng - $lastLng;
        $dist = sin(deg2rad($newLat))
            * sin(deg2rad($lastLat))
            + cos(deg2rad($newLat))
            * cos(deg2rad($lastLat))
            * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distance = $dist * 60 * 1.1515;

        if ($unit == 'K') {
            $distance = ($distance * 1.609344);
        }
        elseif ($unit == 'M') {
            $distance = ($distance * 0.8684);
        }

        return $distance;
    }
}

if (!function_exists('array_merge_deep_array')) {
    function array_merge_deep_array($arrays) {
        $result = array();
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                // Renumber integer keys as array_merge_recursive() does. Note that PHP
                // automatically converts array keys that are integer strings (e.g., '1')
                // to integers.
                if (is_integer($key)) {
                    $result[] = $value;
                }
                elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = array_merge_deep_array(array(
                        $result[$key],
                        $value,
                    ));
                }
                else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }
}

if (!function_exists('array_merge_deep')) {
    function array_merge_deep() {
        $args = func_get_args();
        return array_merge_deep_array($args);
    }
}

if (!function_exists('get_domain')) {
    function get_domain() {
        return preg_replace('/www./', '', request()->getHost());
    }
}

if (!function_exists('get_subscription_id')) {
    function get_subscription_id() {
        $id = 0;
        if (!empty(request()->system->subscription->id)) {
            $id = (int)request()->system->subscription->id;
        }
        else {
            $subscription = \App\Models\Subscription::select('id')->first();
            if (!empty($subscription->id)) {
                $id = (int)$subscription->id;
            }
        }
        return $id;
    }
}

if (!function_exists('get_db_prefix')) {
    function get_db_prefix() {
        return \DB::getTablePrefix();
    }
}

if (!function_exists('mysql_escape')) {
    function mysql_escape($inp) {
        if (is_array($inp)) {
            return array_map(__METHOD__, $inp);
        }
        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }
        return $inp;
    }
}

if (!function_exists('get_user')) {
    function get_user($userId = false, $withProfile = false) {
        if ($userId) {
            $user = \App\Models\User::where('id', $userId);
            if ($withProfile) {
                $user->with('profile');
            }
            return $user->first();
        }

        return auth()->user();
    }
}

if (!function_exists('calculate_billing_date')) {
    function calculate_billing_date($dateFrom = false) {
        if ($dateFrom && is_string($dateFrom)) {
            $dateFrom = \Carbon\Carbon::parse($dateFrom);
        }

        $request = request();
        $billingDate = !empty($request->system->subscription->params->billingStartDate) ? $request->system->subscription->params->billingStartDate : null;

        if ($billingDate) {
            $billingDate = \Carbon\Carbon::parse($billingDate);

            if ($dateFrom) {
                $diffDateFrom = $billingDate->diffInMonths($dateFrom);

                if ($billingDate->gte($dateFrom)) {
                    $billingDate = $billingDate->subMonths(abs($diffDateFrom));
                } else {
                    $billingDate = $billingDate->addMonths(abs($diffDateFrom));
                }

                if ($billingDate->day > $dateFrom->day) {
                    $billingDate->subMonth(1);
                }
            }

            $now = \Carbon\Carbon::now();

            if ($now->gte($billingDate)) {
                $dates = [];
                $diff = $billingDate->diffInMonths($now);

                if ((int)$diff > 0) {
                    for ($i=0; $i<=$diff; $i++) {
                        $nowBilling = $billingDate->copy()->addMonths((int)$i+1);
                        $to = $nowBilling->copy()->subDay();
                        $dates[] = (object)[
                            'from' => $nowBilling->copy()->subMonth()->format('Y-m-d 00:00:00'),
                            'to' => $to->format('Y-m-d 23:59:59'),
                            'now_month' => $to->month,
                            'now_year' => $to->year,
                        ];
                    }
                } else {
                    $nowBilling = $billingDate->addMonth();
                    $dates[] = (object)[
                        'from' => $nowBilling->copy()->subMonth()->format('Y-m-d 00:00:00'),
                        'to' => $nowBilling->copy()->subDay()->format('Y-m-d 23:59:59'),
                        'now_month' => $nowBilling->month,
                        'now_year' => $nowBilling->year,
                    ];
                }

                return $dates;
            }
        }

        return null;
    }
}

if (!function_exists('get_active_drivers_cron')) {
    function get_active_drivers_cron($force = false) {
        if (!config('eto.stats_active_drivers_created') ) {
            include(parse_path('app/Helpers/Migrations/stats_active_drivers.php', true));
        }

        $request = request();
        $billingDate = !empty($request->system->subscription->params->billingStartDate) ? $request->system->subscription->params->billingStartDate : null;
        $sendDriverCount = false;

        if ($billingDate) {
            if (config('eto.last_driver_count')) {
                $startDate = config('eto.last_driver_count');
            }
            else {
                $q = \DB::table('stats_active_drivers')->orderBy('created_at', 'asc')->first(['created_at']);
                $startDate = !empty($q->created_at) ? $q->created_at : null;
            }

            $dates = calculate_billing_date($startDate);

            if ($dates !== null) {
                $check = true;

                if (!$force && config('eto.last_driver_count') && $lastCheckDate = \Carbon\Carbon::parse(config('eto.last_driver_count'))->addSecond()) {
                    $check = check_expire($lastCheckDate->addMonth())->isExpire;
                }

                if ($check || $force) {
                    $sendDriverCount = [];

                    foreach($dates as $date) {
                        if ($date->to) {
                            $data = \App\Models\StatsActiveDriver::where('subscription_id', $request->system->subscription->id);

                            if ($date->from) {
                                $data->whereBetween('created_at', [$date->from, $date->to]);
                            } else {
                                $data->where('created_at', '<=', $date->to);
                            }

                            $count = $data->count();
                        }

                        if (isset($count) && $count > 0) {
                            $sendDriverCount['active_drivers'][] = [
                                'count' => $count,
                                'month' => $date->now_month,
                                'year' => $date->now_year,
                                'from' => $date->from,
                                'to' => $date->to,
                            ];
                        }
                    }

                    if (count($sendDriverCount) > 0 && !$force) {
                        settings_save('eto.last_driver_count', \Carbon\Carbon::now()->format('Y-m-d H:i:s'), 'subscription', $request->system->subscription->id, true);
                    }
                }
            }
        }

        return $sendDriverCount;
    }
}

if (!function_exists('check_requirements')) {
    function check_requirements() {
        if (!config('eto.multi_subscription') && defined('ETO_REMINDERS_TABLE_EXISTS')) {
            $date = \Carbon\Carbon::parse(config('eto.last_check_requirements'))->addDays(3);
            $lastCheckRequirementsExpire = check_expire($date);

            if (!config('eto.last_check_requirements') || $lastCheckRequirementsExpire->isExpire) {
                $reqClass = new \App\Http\Controllers\Subscription\RequirementsController();
                $validate = [];
                $desc = '';

                foreach ($reqClass->required as $name => $params) {
                    if ($params['method'] == 'compare') {
                        $phpVersion = count(explode('.', PHP_VERSION)) === 2 ? PHP_VERSION . '.0' : PHP_VERSION;
                        $fail = $reqClass->etoCompare($name, $phpVersion, $params['min'], isset($params['max']) ? $params['max'] : null);
                        if ($fail !== true) {
                            $validate[] = $fail;
                        }
                    }
                    if ($params['method'] == 'ini') {
                        $fail = $reqClass->etoIni($name);
                        if ($fail !== true) {
                            $validate[] = $fail;
                        }
                    }
                    if ($params['method'] == 'extension') {
                        $fail = $reqClass->etoExtension($name);
                        if ($fail !== true) {
                            $validate[] = $fail;
                        }
                    }
                    if ($params['method'] == 'class') {
                        $fail = $reqClass->etoClass($name);
                        if ($fail !== true) {
                            $validate[] = $fail;
                        }
                    }
                    if ($params['method'] == 'function') {
                        $fail = $reqClass->etoFunction($name);
                        if ($fail !== true) {
                            $validate[] = $fail;
                        }
                    }
                }

                if ($notifications = $reqClass->etoDisableFunctions()) {
                    if ((int)$notifications !== 1 && (int)$notifications !== 0) {
                        $validate[] = $notifications;
                    }
                }

                if (!empty($validate)) {
                    $desc = implode('<br>', $validate);
                    $item = \App\Models\Reminder::where('type', 'requirements')->where('disable', 0)->first();

                    if (!$item) {
                        $item = new \App\Models\Reminder();
                    }

                    $item->type = 'requirements';
                    $item->description = $desc;
                    $item->save();
                    \Cache::store('file')->forget('reminder_data');
                }

                settings_save('eto.last_check_requirements', \Carbon\Carbon::now(), 'system', 0, true);
            }
        }
    }
}

if (!function_exists('can_update')) {
    function can_update($date, $version, $newVewsion) {
        $check = check_expire($date);

        if ($check->isExpire) {
            $versionExplode = explode('.', $version);
            $newVewsionExplode = explode('.', $newVewsion);

            if ((int)$versionExplode[0] !== (int)$newVewsionExplode[0] || (int)$versionExplode[1] !== (int)$newVewsionExplode[1]) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('get_version_number')) {
    function get_version_number($version) {
        if (is_string($version)) {
            $version = !empty($version) ? explode('.', $version) : null;
        }

        if (!empty($version)) {
            if (empty($version[0])) {
                $version[0] = '';
            }
            if (empty($version[1])) {
                $version[1] = '';
            }
            if (empty($version[2])) {
                $version[2] = '';
            }
            $version[0] = str_repeat('0', (5 - strlen($version[0]))) . $version[0];
            $version[1] = str_repeat('0', (5 - strlen($version[1]))) . $version[1];
            $version[2] = str_repeat('0', (5 - strlen($version[2]))) . $version[2];
            return $version[0] . $version[1] . $version[2];
        } else {
            return '000000000000000';
        }
    }
}

if (!function_exists('split_on')) {
    function split_on($string, $num) {
        $length = strlen($string);
        $output[0] = substr($string, 0, $num);
        $output[1] = substr($string, $num, $length);
        return $output;
    }
}
