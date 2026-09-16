<?php

namespace App\Helpers;

class SiteHelper
{
    public static function nl2br2($string)
    {
        return str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
    }

    public static function formatPrice($value, $precision = 2, $nozeros = 0)
    {
        $value = number_format((float)$value, $precision);

        if ( $nozeros ) {
            $value = (float)$value;
        }

        if ($value < 0) {
            return '-'. config('site.currency_symbol') . abs($value) . config('site.currency_code');
        }
        else {
            return config('site.currency_symbol') . $value . config('site.currency_code');
        }
    }

    public static function formatDateTime($value = '', $type = 'datetime')
    {
        if ( $value ) {
            switch( $type ) {
                case 'date':
                    $format = config('site.date_format');
                break;
                case 'time':
                    $format = config('site.time_format');
                break;
                default:
                    $format = config('site.date_format') .' '. config('site.time_format');
                break;
            }

            $value = \Carbon\Carbon::parse($value)->format($format);
        }

        return $value;
    }

    public static function displayElapsedTime($date)
    {
        return sprintf('%s %s %s %s',
            self::formatDateInterval('%d', 'day', $date),
            self::formatDateInterval('%h', 'hour', $date),
            self::formatDateInterval('%i', 'minute', $date),
            self::formatDateInterval('%s', 'second', $date)
        );
    }

    public static function timeToSeconds($time)
    {
        $time = explode(':', $time);
        $hours = (int)$time[0];
        $minutes = (int)$time[1];
        $seconds = (int)$time[2];
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    public static function formatDateInterval($format, $interval, $date)
    {
        $count = $date->diff(new \DateTime)->format($format);
        if ( $count <= 0 ) { return; }
        return sprintf('%s %s', $count, str_plural($interval, $count));
    }

    public static function navigateLink($params = null, $type = '')
    {
        $address = '';
        $name = trans('booking.navigate');
        $icon = 'fa fa-globe';
        $target = '_blank';
        $class = 'eto-navigate-link-button';

        if ( is_array($params) ) {
            if ( isset($params['address']) ) {
                $address = $params['address'];
            }
            if ( isset($params['name']) ) {
                $name = $params['name'];
            }
            if ( isset($params['icon']) ) {
                $icon = $params['icon'];
            }
            if ( isset($params['target']) ) {
                $target = $params['target'];
            }
        }
        else {
            $address = $params;
        }

        // $link = 'https://www.google.co.uk/maps/dir/'. implode('/', [urlencode('My Location'), urlencode($address)]) .'/data=!4m2!4m1!3e0';
        $link = 'https://www.google.com/maps/dir/?api=1&origin='. urlencode('Current Location') .'&destination='. urlencode($address) .'&travelmode=driving';

        if ( $type == 'raw' ) {
            $value = $link;
        }
        else {
            $value = '<a href="'. $link .'" target="'. $target .'" class="'. $class .'">';
            if ( $icon ) {
                $value .= '<i class="'. $icon .'"></i> ';
            }
            if ( $name ) {
                $value .= $name;
            }
            $value .= '</a>';
        }

        return $value;
    }

    public static function telLink($phone, $params = [])
    {
        $class = '';
        $style = '';

        if (!empty($params)) {
            if (!empty($params['class'])) {
                $class = 'class="'. $params['class'] .'"';
            }

            if (!empty($params['style'])) {
                $style = 'style="'. $params['style'] .'"';
            }
        }

        return !empty($phone) ? '<a href="tel:'. $phone .'" '. $class .' '. $style .'>'. $phone .'</a>' : '';
    }

    public static function mailtoLink($email, $params = [])
    {
        $class = '';
        $style = '';

        if (!empty($params)) {
            if (!empty($params['class'])) {
                $class = 'class="'. $params['class'] .'"';
            }

            if (!empty($params['style'])) {
                $style = 'style="'. $params['style'] .'"';
            }
        }

        return !empty($email) ? '<a href="mailto:'. $email .'" '. $class .' '. $style .'>'. $email .'</a>' : '';
    }

    public static function generateRandomString($length = 10, $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        return strtoupper(substr(str_shuffle(str_repeat($x, ceil($length/strlen($x)) )), 1, $length));
    }

    public static function remoteFileExists($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
        $ret = false;
        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $ret = true;
            }
        }
        curl_close($curl);
        return $ret;
    }

    public static function translate($text)
    {
        $trans = [];

        foreach (config('app.locales') as $code => $locale) {
            preg_match_all("/\<$code\>(.*?)\<\/$code\>/s", $text, $matches);

            if (!empty($matches[0][0])) {
                $text = str_replace($matches[0][0], '', $text);
            }

            if (!empty($matches[1][0])) {
                $trans[$code] = $matches[1][0];
            }
        }

        // $locale = app()->getLocale();
        $locale = session('locale');

        if (count($trans) && !empty($trans[$locale])) {
            $text = $trans[$locale];
        }

        // $text = trim($text);

        return $text;
    }

    public static function notifictionTrans($key = '', $role = '', $channel = '')
    {
        if (strpos($channel, '_or_') !== false) {
            $channels = explode('_or_', $channel);
            foreach ($channels as $k => $v) {
                if (\Lang::has($key .'_'. $role .'_'. $v)) {
                    $key = $key .'_'. $role .'_'. $v;
                    return $key;
                }
            }
        }

        if (\Lang::has($key .'_'. $role .'_'. $channel)) {
            $key = $key .'_'. $role .'_'. $channel;
        }
        elseif (\Lang::has($key .'_'. $role)) {
            $key = $key .'_'. $role;
        }
        elseif (\Lang::has($key .'_'. $channel)) {
            $key = $key .'_'. $channel;
        }

        return $key;
    }

    public static function seoFriendlyUrl($string)
    {
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string);
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
        $string = strtolower(trim($string, '-'));
        return $string;
    }

    public static function removeParam($url, $param) {
        $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*$/', '', $url);
        $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*&/', '$1', $url);
        return $url;
    }

    public static function makeStrSafe($string) {
      	$string = strip_tags($string);
      	$bad = array("=", "<", ">", "/", "\"", "`", "~", "'", "$", "%", "#");
      	$string = str_replace($bad, "", $string);
      	return $string;
    }

    public static function colorBlendByOpacity($foreground, $opacity, $background = null)
    {
        static $colors_rgb = array(); // stores colour values already passed through the hexdec() functions below.

        $foreground = str_replace('#','',$foreground);

        if ( is_null($background) ) {
            $background = 'FFFFFF'; // default background.
        }

        $pattern = '~^[a-f0-9]{6,6}$~i'; // accept only valid hexadecimal colour values.
        if ( !@preg_match($pattern, $foreground)  or  !@preg_match($pattern, $background) ) {
            // trigger_error( "Invalid hexadecimal colour value(s) found", E_USER_WARNING );
            return false;
        }

        $opacity = intval( $opacity ); // validate opacity data/number.
        if ( $opacity>100  || $opacity<0 ) {
            // trigger_error( "Opacity percentage error, valid numbers are between 0 - 100", E_USER_WARNING );
            return false;
        }

        if ( $opacity==100 ) {
            return strtoupper( $foreground ); // $transparency == 0
        }

        if ( $opacity==0 ) {
            return strtoupper( $background ); // $transparency == 100
        }
        // calculate $transparency value.
        $transparency = 100-$opacity;

        if ( !isset($colors_rgb[$foreground]) ) {
            // do this only ONCE per script, for each unique colour.
            $f = [
                'r'=>hexdec($foreground[0].$foreground[1]),
                'g'=>hexdec($foreground[2].$foreground[3]),
                'b'=>hexdec($foreground[4].$foreground[5])
            ];
            $colors_rgb[$foreground] = $f;
        }
        else {
            // if this function is used 100 times in a script, this block is run 99 times.  Efficient.
            $f = $colors_rgb[$foreground];
        }

        if ( !isset($colors_rgb[$background]) ) {
            // do this only ONCE per script, for each unique colour.
            $b = [
                'r'=>hexdec($background[0].$background[1]),
                'g'=>hexdec($background[2].$background[3]),
                'b'=>hexdec($background[4].$background[5])
            ];
            $colors_rgb[$background] = $b;
        }
        else {
            // if this FUNCTION is used 100 times in a SCRIPT, this block will run 99 times.  Efficient.
            $b = $colors_rgb[$background];
        }

        $add = [
            'r'=>( $b['r']-$f['r'] ) / 100,
            'g'=>( $b['g']-$f['g'] ) / 100,
            'b'=>( $b['b']-$f['b'] ) / 100
        ];

        $f['r'] += intval( $add['r'] * $transparency );
        $f['g'] += intval( $add['g'] * $transparency );
        $f['b'] += intval( $add['b'] * $transparency );

        return sprintf('%02X%02X%02X', $f['r'], $f['g'], $f['b']);
    }

    public static function generateFilename($type = '') {
        $string = !empty($type) ? $type .'_' : '';
        $string .= md5($string . microtime() . mt_rand(1000, 10000));
        return $string;
    }

    public static function extendValidatorRules() {
        \Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
            if (!$value instanceof \Illuminate\Http\UploadedFile) {
                return false;
            }
            $extension = $value->getClientOriginalExtension();
            return $extension != '' && in_array($extension, $parameters);
        }, trans('validation.mimes'));

        \Validator::replacer('file_extension', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':values', implode(', ', $parameters), $message);
        });
    }

    public static function getTimezoneList($type = 'group') {
        $timezones = [];

        if ($type == 'group') {
            // https://gist.github.com/Xeoncross/1204255
            $regions = [
                'Africa' => \DateTimeZone::AFRICA,
                'America' => \DateTimeZone::AMERICA,
                'Antarctica' => \DateTimeZone::ANTARCTICA,
                'Aisa' => \DateTimeZone::ASIA,
                'Atlantic' => \DateTimeZone::ATLANTIC,
                'Europe' => \DateTimeZone::EUROPE,
                'Indian' => \DateTimeZone::INDIAN,
                'Pacific' => \DateTimeZone::PACIFIC
            ];

            foreach ($regions as $name => $mask) {
                $zones = \DateTimeZone::listIdentifiers($mask);
                foreach($zones as $timezone) {
                    // Lets sample the time there right now
                    $time = new \DateTime(NULL, new \DateTimeZone($timezone));
                    // Us dumb Americans can't handle millitary time
                    $ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';
                    // Remove region name and add a sample time
                    $timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
                }
            }
        }
        else {
            $zones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
            foreach($zones as $timezone) {
                $timezones[$timezone] = $timezone;
            }
        }

        return $timezones;
    }

    // public static function getUrlLocales($param = [], $config = [], $locale = '') {
    //     $params = !empty($param) ? (is_array($param) ? (array)$param : [(string)$param]) : [];
    //     $config = !empty($config) ? $config : [];
    //     $locale = !empty($locale) ? $locale : app()->getLocale();
    //     $locales = !empty($config['url_locales']) ? $config['url_locales'] : [];
    //     $urls = [];
    //
    //     foreach ($params as $k => $v) {
    //         $urls[$v] = '';
    //         if (!empty($config[$v])) {
    //             $urls[$v] = $config[$v];
    //         }
    //
    //         if (!empty($locales) && !empty($locales[$locale]) && !empty($locales[$locale][$v])) {
    //             $urls[$v] = $locales[$locale][$v];
    //         }
    //     }
    //
    //     return $urls;
    // }
}
