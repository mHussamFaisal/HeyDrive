<?php
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Helpers\SiteHelper;
use App\Models\FixedPrice;

$booking = (array)$etoPost['booking'];
$gLanguage = trans('frontend.old');

if (config('site.booking_price_status') == 1) {
    if (config('site.booking_price_status_on') == 1) {
        config(['site.booking_manual_quote_enable' => 1]);
    }
    if (config('site.booking_price_status_on_enquiry') == 1) {
        config(['site.booking_hide_vehicle_without_price' => 1]);
    }
}
else {
    if (config('site.booking_price_status_off') == 1) {
        config(['site.booking_hide_vehicle_without_price' => 2]);
    }
    else {
        config(['site.booking_manual_quote_enable' => 2]);
    }
}


function v2_formatDuration($time_in_seconds) {
    global $gLanguage;

    $time_in_seconds = ceil($time_in_seconds);

    // Check for 0
    if ($time_in_seconds == 0){
        return $gLanguage['quote_Format_LessThanASecond'];
    }

    // Days
    $days = floor($time_in_seconds / (60 * 60 * 24));
    $time_in_seconds -= $days * (60 * 60 * 24);

    // Hours
    $hours = floor($time_in_seconds / (60 * 60));
    $time_in_seconds -= $hours * (60 * 60);

    // Minutes
    $minutes = floor($time_in_seconds / 60);
    $time_in_seconds -= $minutes * 60;

    // Seconds
    $seconds = floor($time_in_seconds);

    // Format for return
    $return = '';
    if ($days > 0){
        $return .= $days . ' '. ($days == 1 ? $gLanguage['quote_Format_Day'] : $gLanguage['quote_Format_Days']). ' ';
    }
    if ($hours > 0){
        $return .= $hours . ' '. ($hours == 1 ? $gLanguage['quote_Format_Hour'] : $gLanguage['quote_Format_Hours']) . ' ';
    }
    if ($minutes > 0){
        $return .= $minutes . ' '. ($minutes == 1 ? $gLanguage['quote_Format_Minute'] : $gLanguage['quote_Format_Minutes']) . ' ';
    }
    if ($seconds > 0){
        $return .= $seconds . ' '. ($seconds == 1 ? $gLanguage['quote_Format_Second'] : $gLanguage['quote_Format_Seconds']) . ' ';
    }
    $return = trim($return);

    return $return;
}

function v2_roundTotal($number, $to = .50) {
    global $gConfig;

    $val = round($number, 2);

    if ( !empty($gConfig['booking_round_total_price']) ) {
        switch ($gConfig['booking_round_total_price']) {
            case '1': // Up or Down to nearest integer
                $val = round($number);
            break;
            case '2': // Up to the nearest integer
                $val = ceil($number);
            break;
            case '3': // Down to the nearest integer
                $val = floor($number);
            break;
            case '4': // Up to nearest integer or half (.50)
                $val = round($number / $to, 0) * $to;
            break;
        }
    }

    return $val;
}

function v2_roundVal($value) {
    $value = round($value, 2);
    return $value;
}

function v2_timeToSec($time) {
    $hours = substr($time, 0, -6);
    $minutes = substr($time, -5, 2);
    $seconds = substr($time, -2);
    return (int)$hours * 3600 + (int)$minutes * 60 + (int)$seconds;
}

function v2_computeDistanceBetween($lat1, $lon1, $lat2, $lon2, $unit) {
    // http://www.geodatasource.com/developers/php

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);
    if ($unit == 'K') {
        return ($miles * 1.609344);
    }
    elseif ($unit == 'N') {
        return ($miles * 0.8684);
    }
    else {
        return $miles;
    }
}

function v2_places($search) {
    global $data, $gConfig;

    $language = explode('-', $gConfig['language']);

    $params = array(
        'key' => config('site.google_places_api_key'),
        'input' => trim($search),
    );

    if ( !empty($gConfig['google_language']) ) {
        $params['language'] = strtolower($gConfig['google_language']);
    }
    else {
        $params['language'] = ($language[0]) ? strtolower($language[0]) : 'en';
    }

    if ( !empty($gConfig['google_country_code']) ) {
        $list = [];
        $codes = explode(',', $gConfig['google_country_code']);
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

    $hash = 'g_place_'. md5(json_encode($params));
    $cache_expiry_time = config('site.google_cache_expiry_time') ? config('site.google_cache_expiry_time') : config('site.google_cache_runtime');
    $response = null;

    if ($cache_expiry_time && cache($hash)) {
        $response = cache($hash);
    }

    if (empty($response)) {
        $client = new Client();
        $request = $client->request('GET', 'https://maps.googleapis.com/maps/api/place/autocomplete/json', [
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

    // dd($response);

    if (!empty($response) && $response->status == 'REQUEST_DENIED') {
        $data['CONNECTION_ERROR'][] = 'Places: '. ($response->error_message ? $response->error_message : $response->status);
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

    if (!empty(config('app.debug'))) {
        $data['!debug_place'] = ['response' => $response, 'params' => $params];
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

    $placeId = '';

    if ( !empty($response) ) {
        if ( !empty($response->predictions) && $response->status == 'OK' ) {
            foreach($response->predictions as $key => $value) {
                if ( trim($value->description) == trim($search) && !empty($value->place_id) ) {
                    $placeId = $value->place_id;
                }
            }
        }
    }

    return $placeId;
}

function v2_geocode($type, $value) {
    global $data, $gConfig;
    // https://maps.googleapis.com/maps/api/geocode/json?sensor=false&latlng=51.5119713,-0.1188775

    $suffix = $gConfig['quote_address_suffix'];
    $language = explode('-', $gConfig['language']);

    $params = array(
        'key' => config('site.google_maps_geocoding_api_key'),
    );

    if ( !empty($gConfig['google_language']) ) {
        $params['language'] = strtolower($gConfig['google_language']);
    }
    else {
        $params['language'] = ($language[0]) ? strtolower($language[0]) : 'en';
    }

    if ( $type == 'place_id' ) {
        $params['place_id'] = trim($value);
    }
    elseif ( $type == 'latlng' ) {
        if ( isset($value['lat']) && isset($value['lng']) ) {
            $params['latlng'] = $value['lat'] .','. $value['lng'];
        }
    }
    else {
        if ( !empty($gConfig['google_region_code']) ) {
            $params['region'] = strtolower($gConfig['google_region_code']);
        }

        if ( !empty($gConfig['google_country_code']) ) {
            $list = [];
            $codes = explode(',', $gConfig['google_country_code']);
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

        if ( in_array($value, ['Gatwick North, RH6 0PJ']) ) {
            $value = 'RH6 0PJ, UK';
        }
        elseif ( in_array($value, ['Heathrow Terminal 1, TW6 1AP', 'Terminal 1, Hounslow, TW6 1AP']) ) {
            $value = 'TW6 1AP, UK';
        }
        elseif ( in_array($value, ['Heathrow Terminal 2,	TW6 1EW', 'Terminal 2, Hounslow, TW6 1EW']) ) {
            $value = 'TW6 1EW, UK';
        }
        elseif ( in_array($value, ['Heathrow Terminal 3, TW6 1QG', 'Terminal 3, Hounslow, TW6 1QG']) ) {
            $value = 'TW6 1QG, UK';
        }
        elseif ( in_array($value, ['Heathrow Terminal 4, TW6 3XA', 'Terminal 4, Hounslow, TW6 3XA']) ) {
            $value = 'TW6 3XA, UK';
        }
        elseif ( in_array($value, ['Heathrow Terminal 5, TW6 2GA', 'Terminal 5, Hounslow, TW6 2GA']) ) {
            $value = 'TW6 2GA, UK';
        }

        $params['address'] = trim($value . $suffix);
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

    // dd($response);

    if (!empty($response) && $response->status == 'REQUEST_DENIED') {
        $data['CONNECTION_ERROR'][] = 'Geocode: '. ($response->error_message ? $response->error_message : $response->status);
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

    if (!empty(config('app.debug'))) {
        $data['!debug_geocode'][] = ['response' => $response, 'params' => $params];
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

    // Country restriction - start
    // if ( !empty($gConfig['google_country_code']) && 0 ) {
    //     $codes = explode(',', $gConfig['google_country_code']);
    //     foreach ($codes as $key => $value) {
    //         $codes[$key] = strtolower(trim($value));
    //     }
    //
    //     $list = [];
    //     if ( in_array('auto', $codes) ) {
    //         $list[] = ($language[1]) ? strtolower($language[1]) : 'gb';
    //     }
    //     else {
    //         foreach ($codes as $key => $value) {
    //             $list[] = $value;
    //         }
    //     }
    //
    //     if ( $response->status == 'OK' ) {
    //         foreach($response->results as $k => $v) {
    //             $found = 0;
    //             foreach($v->address_components as $k1 => $v1) {
    //                 if ( in_array(strtolower($v1->short_name), $list) ) {
    //                     $found = 1;
    //                 }
    //             }
    //             if ( $found == 0 ) {
    //                 unset($response->results[$k]);
    //             }
    //         }
    //     }
    // }
    // Country restriction - end

    return $response;
}

function v2_directions( $from = '', $to = '', $waypoints = [], $travel_type = null, $travel_time = null )
{
    global $data, $gConfig;

    // http://www.nmcmahon.co.uk/using-the-google-maps-directions-api-to-calculate-distances/
    // https://developers.google.com/maps/documentation/directions/#JSON
    // https://developers.google.com/maps/documentation/directions/#Waypoints

    $suffix = $gConfig['quote_address_suffix'];

    $waypointsString = '';
    if ( !empty($waypoints) ) {
        $waypointsString = 'optimize:true';
        foreach($waypoints as $k => $v) {
            $waypointsString .= '|via:' . $v . $suffix;
        }
    }

    $language = explode('-', $gConfig['language']);

    $params = array(
        'key' => config('site.google_maps_directions_api_key'),
        'origin' => $from . $suffix,
        'destination' => $to . $suffix,
        'alternatives' => 'true',
        'mode' => 'driving',
        'units' => 'imperial' // imperial or metric
    );

    if ( !empty($gConfig['google_language']) ) {
        $params['language'] = strtolower($gConfig['google_language']);
    }
    else {
        $params['language'] = ($language[0]) ? strtolower($language[0]) : 'en';
    }

    if ( !empty($gConfig['google_region_code']) ) {
        $params['region'] = strtolower($gConfig['google_region_code']);
    }

    $avoid = '';

    if ( $gConfig['quote_avoid_highways'] > 0 ) {
        if ( !empty($avoid) ) { $avoid .= '|'; }
        $avoid .= 'highways';
    }

    if ( $gConfig['quote_avoid_tolls'] > 0 ) {
        if ( !empty($avoid) ) { $avoid .= '|'; }
        $avoid .= 'tolls';
    }

    if ( $gConfig['quote_avoid_ferries'] > 0 ) {
        if ( !empty($avoid) ) { $avoid .= '|'; }
        $avoid .= 'ferries';
    }

    if ( !empty($avoid) ) {
        $params['avoid'] = $avoid;
    }

    if ( !empty($waypointsString) ) {
        $params['waypoints'] = $waypointsString;
    }

    // Fixes bug with heathrow airport
    // $travel_time->timestamp += 86400 * 15; // days

    // Fix for editing older booking - Google returned not found status if the date was in the past.
    $dtT = Carbon::createFromTimestamp($travel_time->timestamp);
    $dtC = Carbon::now();

    if ($dtT->lte($dtC)) {
        $days = $dtT->diffInDays($dtC) + 1; // 1 - to make force future date and time
        $travel_time->timestamp += 86400 * $days;
        // dd(Carbon::createFromTimestamp($travel_time->timestamp)->toDateTimeString());
    }
    // Fix end

    if ( !empty($travel_type) && !empty($travel_time) ) {
        if ( $travel_type == 'arrival' ) {
            $params['arrival_time'] = $travel_time->timestamp;
        }
        else {
            $params['departure_time'] = $travel_time->timestamp;
        }
    }

    // if ( $gConfig['quote_duration_in_traffic'] > 0 ) {
        // https://developers.google.com/maps/documentation/directions/intro#traffic-model

        // $gConfig['quote_traffic_model'] = '';
        //
        // if ( !empty($gConfig['quote_traffic_model']) ) {
        // 	switch( $gConfig['quote_traffic_model'] ) {
        // 		case 'pessimistic':
        // 			$params['traffic_model'] = 'pessimistic';
        // 		break;
        // 		case 'optimistic':
        // 			$params['traffic_model'] = 'optimistic';
        // 		break;
        // 		default:
        // 			$params['traffic_model'] = 'best_guess';
        // 		break;
        // 	}
        // }

        // echo $travel_type .': '. $travel_time  .'<br>';
    // }

    $hash = 'g_directions_'. md5(json_encode($params));
    $cache_expiry_time = config('site.google_cache_expiry_time') ? config('site.google_cache_expiry_time') : config('site.google_cache_runtime');
    $response = null;

    if ($cache_expiry_time && cache($hash)) {
        $response = cache($hash);
    }

    if (empty($response)) {
        $client = new Client();
        $request = $client->request('GET', 'https://maps.googleapis.com/maps/api/directions/json', [
            'headers' => [
                'accept' => 'application/json',
                'accept-encoding' => 'gzip, deflate',
                'content-type' => 'application/json'
            ],
            'query' => $params
        ]);
        $response = json_decode($request->getBody());

        if (!empty($response) && in_array($response->status, ['OK', 'ZERO_RESULTS'])) {
            if (!empty($response->routes)) {
                foreach ($response->routes as $kR => $vR) {
                    foreach ($vR->legs as $kL => $vL) {
                        foreach ($vL->steps as $kS => $vS) {
                            // unset($response->routes[$kR]->legs[$kL]->steps[$kS]->html_instructions);
                            // unset($response->routes[$kR]->legs[$kL]->steps[$kS]->polyline);
                            unset($response->routes[$kR]->legs[$kL]->steps);
                            unset($response->routes[$kR]->overview_polyline);
                        }
                    }
                }
            }

            if ($cache_expiry_time && !cache($hash)) {
                cache([$hash => $response], $cache_expiry_time);
            }
        }
    }

    // dd($response);

    if (!empty($response) && $response->status == 'REQUEST_DENIED') {
        $data['CONNECTION_ERROR'][] = 'Directions: '. ($response->error_message ? $response->error_message : $response->status);
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

    if (!empty(config('app.debug'))) {
        $data['!debug_directions'][] = ['response' => $response, 'params' => $params];
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

    return $response;
}

function v2_createRoute()
{
    $from = new \stdClass();
    $from->address = '';
    $from->postcode = '';
    $from->outcode = '';
    $from->incode = '';
    $from->lat = 0;
    $from->lng = 0;
    $from->place_id = '';

    $to = new \stdClass();
    $to->address = '';
    $to->postcode = '';
    $to->outcode = '';
    $to->incode = '';
    $to->lat = 0;
    $to->lng = 0;
    $to->place_id = '';

    $route = new \stdClass();
    $route->from = $from;
    $route->to = $to;
    $route->distance = 0;
    $route->duration = 0;

    $fixed = new \stdClass();
    $fixed->id = 0;
    $fixed->value = 0;
    $fixed->type = 0;
    $fixed->deposit = 0;
    $fixed->vehicle = array();

    $calculated = new \stdClass();
    $calculated->id = 0;
    $calculated->value = 0;
    $calculated->type = 0;
    $calculated->deposit = 0;
    $calculated->vehicle = array();

    $route->fixed = $fixed;
    $route->calculated = $calculated;

    return $route;
}

function v2_calculateSegment($vRoute, &$quote, &$vehicles, &$cacheGeocode, &$cacheDirections, &$cachePlaces)
{
    global $data, $gConfig, $gLanguage, $siteId, $userId;

    $db = \DB::connection();
    $dbPrefix = get_db_prefix();

    $route = v2_createRoute();
    $date = $quote->date;
    $serviceId = $quote->serviceId;
    $returnTrip = $quote->returnTrip;

    // Selected service
    $service = \App\Models\Service::where('relation_type', 'site')
      ->where('relation_id', $siteId)
      ->where('status', 'active')
      ->where('id', $serviceId)
      ->first();

    $enablePlaceIdCheck = 1;

    // If place id is empty
    if ( empty($vRoute->fromPlaceId) && $enablePlaceIdCheck ) {
        $cacheKey = md5($vRoute->from);
        if ( empty($cachePlaces[$cacheKey]) ) {
            $cachePlaces[$cacheKey] = v2_places($vRoute->from);
        }
        $vRoute->fromPlaceId = $cachePlaces[$cacheKey];
    }

    if ( empty($vRoute->toPlaceId) && $enablePlaceIdCheck ) {
        $cacheKey = md5($vRoute->to);
        if ( empty($cachePlaces[$cacheKey]) ) {
            $cachePlaces[$cacheKey] = v2_places($vRoute->to);
        }
        $vRoute->toPlaceId = $cachePlaces[$cacheKey];
    }


    // Geocode FROM
    $route->from->address = $vRoute->from;
    $route->from->place_id = $vRoute->fromPlaceId;

    if ( empty($route->from->postcode) && !empty($vRoute->fromPlaceId) && $enablePlaceIdCheck ) {
        $cacheKey = md5('place_id:'. $vRoute->fromPlaceId);
        if ( empty($cacheGeocode[$cacheKey]) ) {
            $cacheGeocode[$cacheKey] = v2_geocode('place_id', $vRoute->fromPlaceId);
        }
        $geocode = $cacheGeocode[$cacheKey];
        // $route->from->geocode = $geocode;
        if ( $geocode->status == 'OK' ) {
            foreach($geocode->results as $k => $v) {
                $route->from->lat = $v->geometry->location->lat;
                $route->from->lng = $v->geometry->location->lng;
                foreach($v->address_components as $k1 => $v1) {
                    foreach($v1->types as $k2 => $v2) {
                        if ( $v2 == 'postal_code' ) {
                            $route->from->postcode = trim($v1->long_name);
                            break 3;
                        }
                    }
                }
            }
        }
    }

    // Check for Address
    if ( empty($route->from->postcode) ) {
        $cacheKey = md5('address:'. $vRoute->from);
        if ( empty($cacheGeocode[$cacheKey]) ) {
            $cacheGeocode[$cacheKey] = v2_geocode('address', $vRoute->from);
        }
        $geocode = $cacheGeocode[$cacheKey];
        //$route->from->geocode = $geocode;
        if ( $geocode->status == 'OK' ) {
            foreach($geocode->results as $k => $v) {
                $route->from->lat = $v->geometry->location->lat;
                $route->from->lng = $v->geometry->location->lng;
                foreach($v->address_components as $k1 => $v1) {
                    foreach($v1->types as $k2 => $v2) {
                        if ( $v2 == 'postal_code' ) {
                            $route->from->postcode = trim($v1->long_name);
                            break 3;
                        }
                    }
                }
            }
        }
    }

    // Check for Lat Lng
    if ( empty($route->from->postcode) && config('site.booking_advanced_geocoding') ) {
        $cacheKey = md5('latlng:'. $route->from->lat .','. $route->from->lng);
        if ( empty($cacheGeocode[$cacheKey]) ) {
            $cacheGeocode[$cacheKey] = v2_geocode('latlng', array(
                'lat' => $route->from->lat,
                'lng' => $route->from->lng
            ));
        }
        $geocode = $cacheGeocode[$cacheKey];
        // $route->from->geocode = $geocode;
        if ( $geocode->status == 'OK' ) {
            foreach($geocode->results as $k => $v) {
                $route->from->lat = $v->geometry->location->lat;
                $route->from->lng = $v->geometry->location->lng;
                foreach($v->address_components as $k1 => $v1) {
                    foreach($v1->types as $k2 => $v2) {
                        if ( $v2 == 'postal_code' ) {
                            $route->from->postcode = trim($v1->long_name);
                            break 3;
                        }
                    }
                }
            }
        }
    }

    if ( !empty($route->from->postcode) ) {
        $postcode = explode(' ', $route->from->postcode);
        $route->from->outcode = trim($postcode[0]);
        $route->from->incode = trim($postcode[1]);
    }


    // Geocode TO
    $route->to->address = $vRoute->to;
    $route->to->place_id = $vRoute->toPlaceId;

    // Check for Place id
    if ( empty($route->to->postcode) && !empty($vRoute->toPlaceId) && $enablePlaceIdCheck ) {
      $cacheKey = md5('place_id:'. $vRoute->toPlaceId);
      if ( empty($cacheGeocode[$cacheKey]) ) {
        $cacheGeocode[$cacheKey] = v2_geocode('place_id', $vRoute->toPlaceId);
      }
      $geocode = $cacheGeocode[$cacheKey];
      // $route->to->geocode = $geocode;
      if ( $geocode->status == 'OK' ) {
        foreach($geocode->results as $k => $v) {
          $route->to->lat = $v->geometry->location->lat;
          $route->to->lng = $v->geometry->location->lng;
          foreach($v->address_components as $k1 => $v1) {
            foreach($v1->types as $k2 => $v2) {
              if ( $v2 == 'postal_code' ) {
                $route->to->postcode = trim($v1->long_name);
                break 3;
              }
            }
          }
        }
      }
    }

    // Check for Address
    if ( empty($route->to->postcode) ) {
        $cacheKey = md5('address:'. $vRoute->to);
        if ( empty($cacheGeocode[$cacheKey]) ) {
            $cacheGeocode[$cacheKey] = v2_geocode('address', $vRoute->to);
        }
        $geocode = $cacheGeocode[$cacheKey];
        //$route->to->geocode = $geocode;
        if ( $geocode->status == 'OK' ) {
            foreach($geocode->results as $k => $v) {
                $route->to->lat = $v->geometry->location->lat;
                $route->to->lng = $v->geometry->location->lng;
                foreach($v->address_components as $k1 => $v1) {
                    foreach($v1->types as $k2 => $v2) {
                        if ( $v2 == 'postal_code' ) {
                            $route->to->postcode = trim($v1->long_name);
                            break 3;
                        }
                    }
                }
            }
        }
    }

    // Check for Lat Lng
    if ( empty($route->to->postcode) && config('site.booking_advanced_geocoding') ) {
      $cacheKey = md5('latlng:'. $route->to->lat .','. $route->to->lng);
      if ( empty($cacheGeocode[$cacheKey]) ) {
        $cacheGeocode[$cacheKey] = v2_geocode('latlng', array(
            'lat' => $route->to->lat,
            'lng' => $route->to->lng
          ));
      }
      $geocode = $cacheGeocode[$cacheKey];
      // $route->to->geocode = $geocode;
      if ( $geocode->status == 'OK' ) {
        foreach($geocode->results as $k => $v) {
          $route->to->lat = $v->geometry->location->lat;
          $route->to->lng = $v->geometry->location->lng;
          foreach($v->address_components as $k1 => $v1) {
            foreach($v1->types as $k2 => $v2) {
              if ( $v2 == 'postal_code' ) {
                $route->to->postcode = trim($v1->long_name);
                break 3;
              }
            }
          }
        }
      }
    }

    if ( !empty($route->to->postcode) ) {
        $postcode = explode(' ', $route->to->postcode);
        $route->to->outcode = trim($postcode[0]);
        $route->to->incode = trim($postcode[1]);
    }

    // if ( (empty($route->from->postcode) || empty($route->to->postcode)) && config('site.booking_postcode_match') ) {
    //     $quote->status = 'NOT_FOUND';
    // }

    // Distance & Duration

    // $f = $route->from->address;
    // if ( !empty($route->from->place_id) ) {
    	 // $f = 'place_id:'. $route->from->place_id;
    // }
    // else {
        $f = $route->from->lat .','. $route->from->lng;
    // }

    // $t = $route->to->address;
    // if ( !empty($route->to->place_id) ) {
    	 // $t = 'place_id:'. $route->to->place_id;
    // }
    // else {
        $t = $route->to->lat .','. $route->to->lng;
    // }


    // Return journey will be calculated the same way as one-way
    if ( !empty($returnTrip) && config('site.booking_return_as_oneway') ) {
        $temp_f = $f;
        $temp_t = $t;
        $f = $temp_t;
        $t = $temp_f;
    }


    $cacheKey = md5($f .'_'. $t .'_'. $vRoute->travel_type .'_'. $vRoute->travel_time->toDateTimeString());
    if ( empty($cacheDirections[$cacheKey]) ) {
        $cacheDirections[$cacheKey] = v2_directions($f, $t, [], $vRoute->travel_type, $vRoute->travel_time);
    }
    $directions = $cacheDirections[$cacheKey];

    if ( $directions->status == 'OK' ) {
        $selectedIndex = 0;
        $minDistance = 0;
        $minDuration = 0;

        // Find shortes route
        if ( count($directions->routes) > 0 && $gConfig['quote_enable_shortest_route'] > 0 ) {
            for($i = 0; $i < count($directions->routes); $i++) {
                $distance = 0;
                $duration = 0;

                for($j = 0; $j < count($directions->routes[$i]->legs); $j++) {
                    $distance += $directions->routes[$i]->legs[$j]->distance->value;
                    // $duration += $directions->routes[$i]->legs[$j]->duration->value;

                    if ( isset($directions->routes[$i]->legs[$j]->duration_in_traffic) ){
                        $duration += $directions->routes[$i]->legs[$j]->duration_in_traffic->value;
                    }
                    else {
                        $duration += $directions->routes[$i]->legs[$j]->duration->value;
                    }
                }

                if ( ($minDistance >= $distance || $minDistance == 0) &&
                    $gConfig['quote_enable_shortest_route'] == 1 ) {
                    $minDistance = $distance;
                    $selectedIndex = $i;
                }

                if ( ($minDuration >= $duration || $minDuration == 0) &&
                    $gConfig['quote_enable_shortest_route'] == 2 ) {
                    $minDuration = $duration;
                    $selectedIndex = $i;
                }
            }
        }

        $distance = 0;
        $duration = 0;

        foreach($directions->routes[$selectedIndex]->legs as $k => $v) {
            $distance += $v->distance->value;
            // $duration += $v->duration->value;

            if ( isset($v->duration_in_traffic) ){
                $duration += $v->duration_in_traffic->value;
            }
            else {
                $duration += $v->duration->value;
            }
        }

        if ( $gConfig['booking_distance_unit'] == 1 ) {
            $distance = round($distance * 0.001, 1); // km
        }
        else {
            $distance = round($distance * 0.000621371192, 1); // miles
        }

        $duration = ceil($duration / 60); // minutes

        $route->distance = $distance;
        $route->duration = $duration;
    }
    else {
        $quote->status = 'NOT_FOUND';
    }


    // Straight line
    if ( $gConfig['quote_enable_straight_line'] == 1 ) {
        $distance = v2_computeDistanceBetween(
            $route->from->lat,
            $route->from->lng,
            $route->to->lat,
            $route->to->lng,
            ($gConfig['booking_distance_unit'] == 1) ? 'K' : 'M'
        );

        $route->distance = round($distance, 1);
        //$route->duration = 0;
    }


    // Set postcodes
    $temp = new \stdClass();
    $temp->from = new \stdClass();
    $temp->from->postcode = $route->from->postcode;
    $temp->from->outcode = $route->from->outcode;
    $temp->from->incode = $route->from->incode;
    $temp->from->lat = $route->from->lat;
    $temp->from->lng = $route->from->lng;

    $temp->to = new \stdClass();
    $temp->to->postcode = $route->to->postcode;
    $temp->to->outcode = $route->to->outcode;
    $temp->to->incode = $route->to->incode;
    $temp->to->lat = $route->to->lat;
    $temp->to->lng = $route->to->lng;


    if ( !empty($route->from->postcode) && !empty($route->to->postcode) ) {
        // Excluded location - start
        $excludedTemp = array(
            'returnTrip' => $returnTrip,
            'id' => 0,
            'list' => array()
        );

        $query = null;

        $outcodeExclude = array();
        $wildcard = '.*';
        $fromList = 'ALL';
        $toList = 'ALL';

        // From
        for($x = 0; $x < (strlen($temp->from->incode) + 1); $x++) {
            if ( strlen($temp->from->incode) - $x > 0 ) {
                $from = substr($temp->from->postcode, 0, strlen($temp->from->postcode) - $x);
                if ( $from != $temp->from->postcode ) {
                        $from .= $wildcard;
                }
            }
            else {
                $from = $temp->from->outcode;
                if ( in_array($from, array($outcodeExclude)) ) {
                    $from .= $wildcard;
                }
            }

            if ( !empty($from) ) {
                if ( !empty($fromList) ) {
                    $fromList .= '|';
                }
                $fromList .= trim($from);
            }
        }

        // To
        for($y = 0; $y < (strlen($temp->to->incode) + 1); $y++) {
            if ( strlen($temp->to->incode) - $y > 0 ) {
                $to = substr($temp->to->postcode, 0, strlen($temp->to->postcode) - $y);
                if ( $to != $temp->to->postcode ) {
                        $to .= $wildcard;
                }
            }
            else {
                $to = $temp->to->outcode;
                if ( in_array($to, array($outcodeExclude)) ) {
                    $to .= $wildcard;
                }
            }

            if ( !empty($to) ) {
                if ( !empty($toList) ) {
                    $toList .= '|';
                }
                $toList .= trim($to);
            }
        }

        // Score
        $selectSql = "";
        $startSql1 = "";
        $startSql2 = "";
        $endSql1 = "";
        $endSql2 = "";

        $fromArray = explode("|", $fromList);
        foreach($fromArray as $k => $v) {
          $startSql1 .= "WHEN `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
          $startSql2 .= "WHEN `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
        }

        $toArray = explode("|", $toList);
        foreach($toArray as $k => $v) {
          $endSql1 .= "WHEN `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
          $endSql2 .= "WHEN `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
        }

        if ( !empty($startSql1) ) {
            $selectSql .= ", (CASE ". $startSql1 ." ELSE '1000' END) AS `score1_start`";
        }
        else {
            $selectSql .= ", '1000' AS `score1_start`";
        }
        if ( !empty($endSql1) ) {
            $selectSql .= ", (CASE ". $endSql1 ." ELSE '1000' END) AS `score1_end`";
        }
        else {
            $selectSql .= ", '1000' AS `score1_end`";
        }

        if ( !empty($startSql2) ) {
            $selectSql .= ", (CASE ". $startSql2 ." ELSE '1000' END) AS `score2_start`";
        }
        else {
            $selectSql .= ", '1000' AS `score2_start`";
        }
        if ( !empty($endSql2) ) {
            $selectSql .= ", (CASE ". $endSql2 ." ELSE '1000' END) AS `score2_end`";
        }
        else {
            $selectSql .= ", '1000' AS `score2_end`";
        }

        if ( !empty($selectSql) ) {
            $selectSql .= ", (SELECT CASE
                WHEN (CAST(`score1_start` AS SIGNED INTEGER) >= '1000' OR CAST(`score1_end` AS SIGNED INTEGER) >= '1000') THEN `score2_start`+`score2_end`
                ELSE `score1_start`+`score1_end`
            END) AS `score_total`";
        }

        // Vehicles
        $vehiclesList = '';
        foreach($vehicles as $k => $v) {
            $id = (int)$v['id'];
            if ( !empty($id) ) {
                if ( !empty($vehiclesList) ) {
                    $vehiclesList .= '|';
                }
                $vehiclesList .= $id;
            }
        }

        $sql = "SELECT * ". $selectSql ."
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='". $siteId ."'
            AND `published`='1'
            AND (
                CASE
                    WHEN `direction` = '1' THEN (
                        `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                            AND
                        `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                    )
                    ELSE ((
                        `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                            AND
                        `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                    ) OR (
                        `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                            AND
                        `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                    ))
                END
            )
            AND (
                CASE
                    WHEN (`start_date` > '0000-00-00 00:00:00' AND `end_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' BETWEEN `start_date` AND `end_date`)
                    WHEN (`start_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' >= `start_date`)
                    WHEN (`end_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' <= `end_date`)
                    ELSE '1'
                END
            )
            AND (
                CASE
                    WHEN (`vehicles` != '') THEN (`vehicles` REGEXP '(^\s*|\s*\,\s*)(". $vehiclesList .")(\s*\,\s*|\s*$)')
                    ELSE '1'
                END
            )
            ORDER BY
                `vehicles` DESC,
                CAST(`score_total` AS SIGNED INTEGER) ASC,
                `start_date` DESC,
                `end_date` DESC,
                `direction` DESC,
                `ordering` ASC,
                `allowed` DESC
            LIMIT 1";

        $query = $db->select($sql);
        if (!empty($query[0])) {
            $query = $query[0];
        }

        $excludedTemp['list'] = array(
            'from' => $fromList,
            'to' => $toList,
            // 'sql' => $sql
        );

        if ( !empty($query) ) {
            $excludedTemp['id'] = (int)$query->id;
            $excludedTemp['allowed'] = (int)$query->allowed;

            if ( !empty($query->vehicles) ) { //  && !empty($vehiclesList)
                $found = preg_grep("/(^\s*|\s*\,\s*)(". $vehiclesList .")(\s*\,\s*|\s*$)/", array($query->vehicles));
                if ( !empty($found) ) {
                    if ( (int)$query->allowed == 1 ) {
                        $quote->excludedRouteAllowed = 1;
                    }
                    else {
                        $quote->excludedRouteAllowed = 3;
                    }
                }
                else {
                    if ( (int)$query->allowed == 1 ) {
                        $quote->excludedRouteAllowed = 3;
                    }
                    else {
                        $quote->excludedRouteAllowed = 1;
                    }
                }
            }
            else {
                if ( (int)$query->allowed == 1 ) {
                    $quote->excludedRouteAllowed = 1;
                }
                else {
                    $quote->excludedRouteAllowed = 2;
                }
            }

            if ( !empty($query->description) ) {
                $quote->excludedRouteDescription = $query->description;
            }
        }

        $route->excludedTemp = $excludedTemp;
        // Excluded location - end
    }


    // Return journey will be calculated the same way as one-way
    if ( !empty($returnTrip) && config('site.booking_return_as_oneway') ) {
        $temp->from->postcode = $route->to->postcode;
        $temp->from->outcode = $route->to->outcode;
        $temp->from->incode = $route->to->incode;
        $temp->from->lat = $route->to->lat;
        $temp->from->lng = $route->to->lng;

        $temp->to->postcode = $route->from->postcode;
        $temp->to->outcode = $route->from->outcode;
        $temp->to->incode = $route->from->incode;
        $temp->to->lat = $route->from->lat;
        $temp->to->lng = $route->from->lng;
    }


    $postcodePrices = [];
    $zonePrices = [];

    if ( !empty($route->from->postcode) && !empty($route->to->postcode) ) {
        // Prices - start
        $outcodeExclude = array(); // 'TW6','RH6'
        $wildcard = '.*';
        $fromList = 'ALL';
        $toList = 'ALL';

        // From
        for($x = 0; $x < (strlen($temp->from->incode) + 1); $x++) {
          if ( strlen($temp->from->incode) - $x > 0 ) {
            $from = substr($temp->from->postcode, 0, strlen($temp->from->postcode) - $x);
            if ( $from != $temp->from->postcode ) {
                $from .= $wildcard;
            }
          }
          else {
            $from = $temp->from->outcode;
            if ( in_array($from, array($outcodeExclude)) ) {
              $from .= $wildcard;
            }
          }

          if ( !empty($from) ) {
              if ( !empty($fromList) ) {
                $fromList .= '|';
              }
              $fromList .= trim($from);
          }
        }

        // To
        for($y = 0; $y < (strlen($temp->to->incode) + 1); $y++) {
          if ( strlen($temp->to->incode) - $y > 0 ) {
            $to = substr($temp->to->postcode, 0, strlen($temp->to->postcode) - $y);
            if ( $to != $temp->to->postcode ) {
                $to .= $wildcard;
            }
          }
          else {
            $to = $temp->to->outcode;
            if ( in_array($to, array($outcodeExclude)) ) {
              $to .= $wildcard;
            }
          }

          if ( !empty($to) ) {
              if ( !empty($toList) ) {
                $toList .= '|';
              }
              $toList .= trim($to);
          }
        }

        // Score
        $selectSql = "";
        $startSql1 = "";
        $startSql2 = "";
        $endSql1 = "";
        $endSql2 = "";

        $fromArray = explode("|", $fromList);
        foreach($fromArray as $k => $v) {
            $startSql1 .= "WHEN (
              CASE
                WHEN `start_type` = '1' THEN `start_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                ELSE `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
              END
            )	THEN '". ($k + 1) ."' ";

            $startSql2 .= "WHEN (
              CASE
                WHEN `end_type` = '1' THEN `end_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                ELSE `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
              END
            )	THEN '". ($k + 1) ."' ";
        }

        $toArray = explode("|", $toList);
        foreach($toArray as $k => $v) {
            $endSql1 .= "WHEN (
              CASE
                WHEN `end_type` = '1' THEN `end_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                ELSE `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
              END
            )	THEN '". ($k + 1) ."' ";

            $endSql2 .= "WHEN (
              CASE
                WHEN `start_type` = '1' THEN `start_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                ELSE `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
              END
            )	THEN '". ($k + 1) ."' ";
        }

        if ( !empty($startSql1) ) {
            $selectSql .= ", (CASE ". $startSql1 ." ELSE '1000' END) AS `score1_start`";
        }
        else {
            $selectSql .= ", '1000' AS `score1_start`";
        }
        if ( !empty($endSql1) ) {
            $selectSql .= ", (CASE ". $endSql1 ." ELSE '1000' END) AS `score1_end`";
        }
        else {
            $selectSql .= ", '1000' AS `score1_end`";
        }

        if ( !empty($startSql2) ) {
            $selectSql .= ", (CASE ". $startSql2 ." ELSE '1000' END) AS `score2_start`";
        }
        else {
            $selectSql .= ", '1000' AS `score2_start`";
        }
        if ( !empty($endSql2) ) {
            $selectSql .= ", (CASE ". $endSql2 ." ELSE '1000' END) AS `score2_end`";
        }
        else {
            $selectSql .= ", '1000' AS `score2_end`";
        }

        if ( !empty($selectSql) ) {
            $selectSql .= ", (SELECT CASE
                    WHEN (CAST(`score1_start` AS SIGNED INTEGER) >= '1000' OR
                                CAST(`score1_end` AS SIGNED INTEGER) >= '1000') THEN `score2_start`+`score2_end`
                    ELSE `score1_start`+`score1_end`
                END) AS `score_total`";
        }

        $sql = "SELECT * ". $selectSql ."
                FROM `{$dbPrefix}fixed_prices`
                WHERE `site_id`='". $siteId ."'
                AND `published`='1'
                AND `is_zone`='0'
                AND CASE
                    WHEN `direction` = '1'
                    THEN (
                      CASE
                        WHEN `start_type` = '1' THEN `start_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                        ELSE `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                      END
                        AND
                      CASE
                        WHEN `end_type` = '1' THEN `end_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                        ELSE `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                      END
                    )
                    ELSE (
                      (
                        CASE
                          WHEN `start_type` = '1' THEN `start_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                          ELSE `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                        END
                          AND
                        CASE
                          WHEN `end_type` = '1' THEN `end_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                          ELSE `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                        END
                      ) OR (
                        CASE
                          WHEN `start_type` = '1' THEN `start_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                          ELSE `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                        END
                          AND
                        CASE
                          WHEN `end_type` = '1' THEN `end_postcode` NOT REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                          ELSE `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                        END
                      )
                    )
                    END
                AND CASE
                        WHEN (`start_date` > '0000-00-00 00:00:00' AND `end_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' BETWEEN `start_date` AND `end_date`)
                        WHEN (`start_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' >= `start_date`)
                        WHEN (`end_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' <= `end_date`)
                        ELSE '1'
                    END
                ORDER BY
                  `service_ids` ASC,
                  `type` ASC,
                  CAST(`score_total` AS SIGNED INTEGER) ASC,
                  `ordering` ASC,
                  `start_date` DESC,
                  `end_date` DESC";

        $postcodePrices = $db->select($sql);

        if ( config('app.debug') ) {
            $route->_resultsTempPostcode = [
                'returnTrip' => $returnTrip,
                'from' => $fromList,
                'to' => $toList,
                'postcodePrices' => $postcodePrices,
                'postcodeSQL' => $sql,
            ];
        }
        // Prices - end
    }

    $zonePricesModel = FixedPrice::where('site_id', $siteId)->withZoneDistance($temp, 1);

    if ($date) {
        $zonePricesModel->whereRaw("CASE
            WHEN (`start_date` > '0000-00-00 00:00:00' AND `end_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' BETWEEN `start_date` AND `end_date`)
            WHEN (`start_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' >= `start_date`)
            WHEN (`end_date` > '0000-00-00 00:00:00') THEN ('". $date .":00' <= `end_date`)
            ELSE '1'
        END");
    }

    $zonePrices = $zonePricesModel->get();
    // dd($zonePrices->toArray());
    // dd($zonePricesModel->toSql());

    if ( config('app.debug') ) {
        $route->_resultsTempZones = [
            'returnTrip' => $returnTrip,
            'zonePrices' => $zonePrices->toArray(),
            'zoneSQL' => $zonePricesModel->toSql(),
        ];
    }

    if (config('site.fixed_prices_priority') == 1) {
        if (count($zonePrices)) {
            $prices = $zonePrices;
        }
        else {
            $prices = $postcodePrices;
        }
    }
    else {
        if (count($postcodePrices)) {
            $prices = $postcodePrices;
        }
        else {
            $prices = $zonePrices;
        }
    }

    $fpCount = FixedPrice::where('site_id', $siteId)->where('published', 1)->where('is_zone', 0)->count();

    // if ( (empty($route->from->postcode) || empty($route->to->postcode)) && config('site.booking_postcode_match') && count($zonePrices) <= 0 ) {
    if ( (empty($route->from->postcode) || empty($route->to->postcode)) && config('site.booking_postcode_match') && $fpCount > 0 ) {
        $quote->status = 'NOT_FOUND';
        $quote->status_postcode_match = 1;
    }

    // dump([$temp, $prices->toArray(), $zonePricesModel->toSql()]);die();
    // \Log::info([$temp, $prices->toArray(), $zonePricesModel->toSql()]);

    if ( !empty($prices) ) {
        foreach($prices as $k => $v) {
            if ( !empty($service->id) &&
                !empty($v->service_ids) &&
                !in_array($service->id, json_decode($v->service_ids))
            ) {
                $overrideService = 1;
                continue;
            }
            else {
                $overrideService = 0;
            }

            switch( $v->type ) {
                case '1': // Fixed Price

                    if ( empty($route->fixed->id) || $overrideService ) {
                        $v->params = json_decode($v->params);

                        $route->fixed->id = (int)$v->id;
                        $route->fixed->value = (float)$v->value;

                        if ( !empty($v->params->deposit) ) {
                            $route->fixed->deposit = (float)$v->params->deposit;
                        }

                        if ( !empty($v->params->factor_type) ) {
                            $route->fixed->type = (int)$v->params->factor_type;
                        }

                        if ( !empty($v->params->vehicle) ) {
                            $route->fixed->vehicle = $v->params->vehicle;
                        }
                    }

                break;
                case '2': // Modify Mileage Price

                    if ( empty($route->calculated->id) || $overrideService ) {
                        $v->params = json_decode($v->params);

                        $route->calculated->id = (int)$v->id;
                        $route->calculated->value = (float)$v->value;

                        if ( !empty($v->params->deposit) ) {
                            $route->calculated->deposit = (float)$v->params->deposit;
                        }

                        if ( !empty($v->params->factor_type) ) {
                            $route->calculated->type = (int)$v->params->factor_type;
                        }

                        if ( !empty($v->params->vehicle) ) {
                            $route->calculated->vehicle = $v->params->vehicle;
                        }
                    }

                break;
            }
        }
    }

    return $route;
}

function v2_calculateRoute($postData, $postType) {
    global $data, $gConfig, $gLanguage, $siteId, $userId, $quoteType;

    $db = \DB::connection();
    $dbPrefix = get_db_prefix();

    $post = $postData[$postType];

    // Init
    $returnTrip = (int)$post['returnTrip'];
    $from = (string)$post['address']['start'];
    $to = (string)$post['address']['end'];
    if ( !empty($post['address']['waypoints']) ) {
        $via = (array)$post['address']['waypoints'];
    }
    else {
        $via = array();
    }

    $fromPlaceId = (string)$post['address']['startPlaceId'];
    $toPlaceId = (string)$post['address']['endPlaceId'];
    if ( !empty($post['address']['waypointsPlaceId']) ) {
        $viaPlaceId = (array)$post['address']['waypointsPlaceId'];
    }
    else {
        $viaPlaceId = array();
    }

    $date = trim((string)$post['date']);
    $vehicles = (array)$post['vehicle'];
    $childSeats = (int)$post['childSeats'];
    $babySeats = (int)$post['babySeats'];
    $infantSeats = (int)$post['infantSeats'];
    $wheelchair = (int)$post['wheelchair'];
    $waitingTime = (int)$post['waitingTime'];
    $meetAndGreet = (int)$post['meetAndGreet'];
    $items = (array)$post['items'];
    $serviceId = (int)$postData['serviceId'];
    $serviceDuration = (int)$postData['serviceDuration'];
    $preferredPassengers = (int)$postData['preferred']['passengers'];
    $preferredLuggage = (int)$postData['preferred']['luggage'];
    $preferredHandLuggage = (int)$postData['preferred']['handLuggage'];

    // Charges
    $sql = "SELECT *
            FROM `{$dbPrefix}charge`
            WHERE `site_id`='". $siteId ."'
            AND `published`='1'
            AND CASE WHEN( `start_date` > '0000-00-00 00:00:00'
                                AND
                            `end_date` > '0000-00-00 00:00:00'
                ) THEN
                     '". $date .":00' BETWEEN `start_date` AND `end_date`
                ELSE '1' END
            ORDER BY `type` ASC";

    $queryCharges = $db->select($sql);

    if ( empty($queryCharges) ) {
        $data['message'][] = $gLanguage['API']['ERROR_NO_CHARGE'];
    }

    // Vehicles
    $sql = "SELECT *
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='" . $siteId . "' ". ($quoteType == "frontend" ? "AND `published`='1'" : "AND `published`='1'");

    $qVehicles = $db->select($sql);

    if ( empty($qVehicles) ) {
        $data['message'][] = $gLanguage['API']['ERROR_NO_VEHICLE'];
    }

    $routes = array();

    if ( !empty($via) ) {
        $i = 1;
        $total = count($via);
        $lastKey = 0;
        $lastVal = '';

        foreach($via as $k => $v) {
            if ( $i == 1 ) {
                $route = new \stdClass();
                $route->from = $from;
                $route->to = $v;
                $route->fromPlaceId = $fromPlaceId;
                $route->toPlaceId = $viaPlaceId[$k];
                $routes[] = $route;
            }

            if ( $i > 1 && $i <= $total ) {
                $route = new \stdClass();
                $route->from = $lastVal;
                $route->to = $v;
                $route->fromPlaceId = $viaPlaceId[$lastKey];
                $route->toPlaceId = $viaPlaceId[$k];
                $routes[] = $route;
            }

            if ( $i == $total ) {
                $route = new \stdClass();
                $route->from = $v;
                $route->to = $to;
                $route->fromPlaceId = $viaPlaceId[$k];
                $route->toPlaceId = $toPlaceId;
                $routes[] = $route;
            }

            $lastKey = $k;
            $lastVal = $v;
            $i++;
        }
    }
    else {
        $route = new \stdClass();
        $route->from = $from;
        $route->to = $to;
        $route->fromPlaceId = $fromPlaceId;
        $route->toPlaceId = $toPlaceId;
        $routes[] = $route;
    }

    $quote = new \stdClass();
    $quote->status = 'OK';
    $quote->status_postcode_match = 0;
    $quote->routes = array();
    $quote->date = $date;
    $quote->serviceId = $serviceId;
    $quote->returnTrip = $returnTrip;

    $quote->distance = 0;
    $quote->duration = 0;
    $quote->distance_base_start = 0;
    $quote->duration_base_start = 0;
    $quote->distance_base_end = 0;
    $quote->duration_base_end = 0;

    $quote->steps = array();
    $quote->stepsText = '';
    $quote->summary = array();
    $quote->summaryText = '';
    $quote->infoText = '';
    $quote->vehicles = array();
    $quote->charges = 0;
    $quote->total = 0;
    $quote->isAirport = 0;
    $quote->isAirport2 = 0;
    $quote->inRadius = 0;
    $quote->meetingPoint = '';
    $quote->excludedRouteAllowed = 0;
    $quote->excludedRouteDescription = '';

    $quoteHelper = new \stdClass();
    $quoteHelper->distanceMultiplier = 0;
    $quoteHelper->distanceMultipliers = array();
    // $quoteHelper->nightFactorType = 0;
    // $quoteHelper->nightFactorValue = 0;
    $quoteHelper->holidayMultipliers = array();
    $quoteHelper->minPrice = 0;
    $quoteHelper->chargesBase = 0;
    $quoteHelper->chargesBaseExclude = 0;
    $quoteHelper->chargesGeneral = 0;
    $quoteHelper->chargesGeneralExclude = 0;

    $cacheGeocode = array();
    $cacheDirections = array();
    $cachePlaces = array();

    $last_travel_time = !empty($date) ? Carbon::parse($date .':00') : Carbon::now();

    foreach($routes as $kRoute => $vRoute) {
        $vRoute->travel_type = 'departure';
        $vRoute->travel_time = $last_travel_time->copy();

        $route = v2_calculateSegment($vRoute, $quote, $vehicles, $cacheGeocode, $cacheDirections, $cachePlaces);

        $quote->distance += $route->distance;
        $quote->duration += $route->duration;

        $route->travel_type = $vRoute->travel_type;
        $route->travel_time_start = $vRoute->travel_time;
        $route->travel_time_end = $vRoute->travel_time->copy()->addMinutes($route->duration);

        $last_travel_time = $route->travel_time_end->copy();

        $routes[$kRoute] = $route;
    }

    $quote->routes = $routes;


    // Distance multiplier
    $ranges = array();
    if ( !empty($gConfig['quote_distance_range']) ) {
        $rangesTemp = json_decode($gConfig['quote_distance_range']);
        if ( !empty($rangesTemp) ) {
            foreach($rangesTemp as $k => $v) {
                $index = round((float)$v->distance * 100);
                $ranges[$index] = $v;
            }
        }
    }
    ksort($ranges);


    // Night charge
    // if ( !empty($gConfig['night_charge_enable']) ) {
    //     $checkTime = v2_timeToSec(date('H:i:s', strtotime($date .':00')));
    //     $limitStartTime = v2_timeToSec($gConfig['night_charge_start'] .':00');
    //     $limitEndTime = v2_timeToSec($gConfig['night_charge_end'] .':00');
    //     $nightFactorType = $gConfig['night_charge_factor_type'];
    //     $nightFactorValue = $gConfig['night_charge_factor'];
    //     $allowNightTime = 0;
    //
    //     if ( $limitStartTime == $limitEndTime ) {
    //         $allowNightTime = 0;
    //     }
    //     elseif ( $limitStartTime > $limitEndTime ) {
    //         if ( !($checkTime <= $limitStartTime - 1 && $checkTime >= $limitEndTime) ) {
    //             $allowNightTime = 1;
    //         }
    //     }
    //     else {
    //         if ( $checkTime >= $limitStartTime && $checkTime <= $limitEndTime - 1 ) {
    //             $allowNightTime = 1;
    //         }
    //     }
    //
    //     if ( $allowNightTime > 0 && $nightFactorValue > 0 ) {
    //         $quoteHelper->nightFactorValue = $nightFactorValue;
    //         $quoteHelper->nightFactorType = $nightFactorType;
    //
    //         //$results['extraChargesList'][] = 'Night fare '. $nightFactorValue;
    //         //$results['Night-charge'] = 'Night charge';
    //         //$results['checkTime'] = $checkTime;
    //         //$results['startTime'] = $limitStartTime;
    //         //$results['endTime'] = $limitEndTime;
    //     }
    // }

    // From & To postcode - start
    $firstRouteIndex = 0;
    $lastRouteIndex = 0;

    foreach($quote->routes as $k => $v) {
        if ( $lastRouteIndex <= $k ) {
            $lastRouteIndex = $k;
        }
    }

    $fromPostcode = $quote->routes[$firstRouteIndex]->from->postcode;
    $toPostcode = $quote->routes[$lastRouteIndex]->to->postcode;

    $fromData = $quote->routes[$firstRouteIndex]->from;
    $toData = $quote->routes[$lastRouteIndex]->to;
    // From & To postcode - end

    foreach($queryCharges as $k => $v) {
        // Min price
        if ( $v->type == 'distance_min' ) {
            $quoteHelper->minPrice = (float)$v->value;
        }

        // Holiday multiplier
        if ( $v->type == 'distance_override' ) {
            if ( $v->value > 0 ) {
                $params = isset($v->params) ? (array)json_decode($v->params, true) : [];
                $quoteHelper->holidayMultipliers[] = [
                    'type' => isset($params['factor_type']) ? (int)$params['factor_type'] : 0,
                    'value' => (float)$v->value,
                ];
            }

            $summary = new \stdClass();
            $summary->type = 'info';
            $summary->name = $v->note;
            $summary->amount = 0;
            $summary->total = 0;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Waypoints
        if ( $v->type == 'waypoint' && !empty($via) && $v->value > 0 ) {
            $amount = count($via);
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesBase += $total;

            $summary = new \stdClass();
            $summary->type = 'stopover';
            $summary->name = trans('booking.summary_types.stopover');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Airport detection - start
        if ( $v->type == 'airport_postcodes' && !empty($v->params) && (!empty($fromPostcode) || !empty($toPostcode)) ) {
            $found1 = 0;
            $params = json_decode($v->params);
            foreach($params as $kPostcode => $vPostcode) {
                $temp = preg_grep("/^". $vPostcode ."/", array($fromPostcode));
                if ( !empty($temp) ) {
                    $found1 = 1;
                }
            }
            if ( !empty($found1) ) {
                $quote->isAirport = 1;
            }

            $found2 = 0;
            $params = json_decode($v->params);
            foreach($params as $kPostcode => $vPostcode) {
                $temp = preg_grep("/^". $vPostcode ."/", array($toPostcode));
                if ( !empty($temp) ) {
                    $found2 = 1;
                    break;
                }
            }
            if ( !empty($found2) ) {
                $quote->isAirport2 = 1;
            }
        }
        // Airport detection - end


        // Parking - start
        if ( in_array($v->type, ['parking']) && $v->value > 0 ) {
            $params = json_decode($v->params);
            $ok = 1;

            if ( !empty($params->location) && !empty($params->location->enabled) ) {
                $type = (string)$params->location->type;

                if ( !empty($params->location->list) ) {
                    $list = (array)$params->location->list;
                    $check = 0;

                    foreach( $list as $kL => $vL ) {
                        if ( in_array($type, ['all', 'from']) &&
                            !empty($vL->postcode) &&
                            !empty($fromPostcode) &&
                            preg_grep("/^". $vL->postcode ."/", array($fromPostcode)) ) {
                            $check = 1;
                        }
                        if ( in_array($type, ['all', 'to']) &&
                            !empty($vL->postcode) &&
                            !empty($toPostcode) &&
                            preg_grep("/^". $vL->postcode ."/", array($toPostcode)) ) {
                            $check = 1;
                        }
                    }

                    if ( !$check ) {
                        $ok = 0;
                    }
                }
            }

            $apply_on_vehicles = [];

            if ( !empty($params->vehicle) && !empty($params->vehicle->enabled) ) {
                if ( !empty($params->vehicle->list) ) {
                    $apply_on_vehicles = (array)$params->vehicle->list;

                    // $list = (array)$params->vehicle->list;
                    // $check = 0;
                    // foreach( $vehicles as $kV => $vV ) {
                    //     $vV = (object)$vV;
                    //     if ( in_array($vV->id, $list) && $vV->amount > 0 ) {
                    //         $check = 1;
                    //     }
                    // }
                    // if ( !$check ) {
                    //     $ok = 0;
                    // }
                }
            }

            if ( $ok ) {
                $amount = 1;
                $base = (float)$v->value;
                $total = v2_roundVal($amount * $base);

                // $quoteHelper->chargesBase += $total;
                // if ( empty($gConfig['booking_include_aiport_charges']) ) {
                //     $quoteHelper->chargesBaseExclude += $total;
                // }

                $summary = new \stdClass();
                $summary->type = 'parking';
                if ( $v->note ) {
                    $summary->name = $v->note;
                }
                else {
                    $summary->name = trans('booking.summary_types.parking');
                }
                $summary->amount = $amount;
                $summary->base = $base;
                $summary->total = $total;
                $summary->visible = (int)$v->note_published;
                $summary->apply_on_vehicles = $apply_on_vehicles;
                $quote->summary[] = $summary;
            }
        }
        // Parking - end


        // Compusolary meet and greet
        if ( $gConfig['booking_meet_and_greet_compulsory'] == 1 ) {
            if ( $quote->isAirport == 1 ) {
                $meetAndGreet = 1;
            }
            else {
                $meetAndGreet = 0;
            }
        }


        // Meet and Greet
        if ( $v->type == 'meet_and_greet' && !empty($meetAndGreet) && $v->value > 0 ) {
            $amount = $meetAndGreet;
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesGeneral += $total;
            //$quoteHelper->chargesGeneralExclude += $total;

            $summary = new \stdClass();
            $summary->type = 'meet_and_greet';
            $summary->name = trans('booking.summary_types.meet_and_greet');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Child seats
        if ( $v->type == 'child_seat' && !empty($childSeats) && $v->value >= 0 ) {
            $amount = $childSeats;
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesGeneral += $total;
            //$quoteHelper->chargesGeneralExclude += $total;

            $summary = new \stdClass();
            $summary->type = 'child_seat';
            $summary->name = trans('booking.summary_types.child_seat');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Booster seats
        if ( $v->type == 'baby_seat' && !empty($babySeats) && $v->value >= 0 ) {
            $amount = $babySeats;
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesGeneral += $total;
            //$quoteHelper->chargesGeneralExclude += $total;

            $summary = new \stdClass();
            $summary->type = 'baby_seat';
            $summary->name = trans('booking.summary_types.baby_seat');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Infant seats
        if ( $v->type == 'infant_seats' && !empty($infantSeats) && $v->value >= 0 ) {
            $amount = $infantSeats;
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesGeneral += $total;
            //$quoteHelper->chargesGeneralExclude += $total;

            $summary = new \stdClass();
            $summary->type = 'infant_seat';
            $summary->name = trans('booking.summary_types.infant_seat');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Wheelchair
        if ( $v->type == 'wheelchair' && !empty($wheelchair) && $v->value >= 0 ) {
            $amount = $wheelchair;
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesGeneral += $total;
            //$quoteHelper->chargesGeneralExclude += $total;

            $summary = new \stdClass();
            $summary->type = 'wheelchair';
            $summary->name = trans('booking.summary_types.wheelchair');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }

        // Waiting time
        if ( $v->type == 'waiting_time' && !empty($waitingTime) && $v->value > 0 ) {
            $amount = $waitingTime;
            $base = (float)$v->value;
            $total = v2_roundVal($amount * $base);

            $quoteHelper->chargesGeneral += $total;
            //$quoteHelper->chargesGeneralExclude += $total;

            $summary = new \stdClass();
            $summary->type = 'waiting_time';
            $summary->name = trans('booking.summary_types.waiting_time');
            // $summary->name = $v->note;
            $summary->amount = $amount;
            $summary->base = $base;
            $summary->total = $total;
            $summary->visible = (int)$v->note_published;
            $quote->summary[] = $summary;
        }
    }

    // Items charges
    if ( $quoteType == "frontend" ) {
        if ( isset($gConfig['booking_items']) ) {
            $temp = json_decode($gConfig['booking_items']);
            if ( !empty($temp) ) {
                foreach($temp as $keyItem => $valueItem) {
                    foreach($items as $keyItem2 => $valueItem2) {
                        if ( (string)$valueItem->id == (string)$valueItem2['id'] ) {
                            $amount = !empty($valueItem2['amount']) ? (int)$valueItem2['amount'] : 1;
                            $base = (float)$valueItem->value;
                            $total = v2_roundVal($amount * $base);

                            $quoteHelper->chargesGeneral += $total;
                            //$quoteHelper->chargesGeneralExclude += $total;

                            $summary = new \stdClass();
                            $summary->type = 'other';
                            $summary->name = $valueItem->name . (!empty($valueItem2['custom']) ? ': '. $valueItem2['custom'] : '');
                            $summary->amount = $amount;
                            $summary->base = $base;
                            $summary->total = $total;
                            $summary->visible = 1;
                            $quote->summary[] = $summary;
                        }
                    }
                }
            }
        }
    }


    // Set postcodes
    $temp = new \stdClass();
    $temp->from = new \stdClass();
    $temp->from->postcode = $quote->routes[$firstRouteIndex]->from->postcode;
    $temp->from->outcode = $quote->routes[$firstRouteIndex]->from->outcode;
    $temp->from->incode = $quote->routes[$firstRouteIndex]->from->incode;
    $temp->to = new \stdClass();
    $temp->to->postcode = $quote->routes[$lastRouteIndex]->to->postcode;
    $temp->to->outcode = $quote->routes[$lastRouteIndex]->to->outcode;
    $temp->to->incode = $quote->routes[$lastRouteIndex]->to->incode;

    // Excluded location - start
    if ( count($quote->routes) > 1) {
        $excludedTemp = array(
            'returnTrip' => $returnTrip,
            'id' => 0,
            'list' => array()
        );

        $query = null;

        $outcodeExclude = array();
        $wildcard = '.*';
        $fromList = 'ALL';
        $toList = 'ALL';

        // From
        for($x = 0; $x < (strlen($temp->from->incode) + 1); $x++) {
            if ( strlen($temp->from->incode) - $x > 0 ) {
                $from = substr($temp->from->postcode, 0, strlen($temp->from->postcode) - $x);
                if ( $from != $temp->from->postcode ) {
                        $from .= $wildcard;
                }
            }
            else {
                $from = $temp->from->outcode;
                if ( in_array($from, array($outcodeExclude)) ) {
                    $from .= $wildcard;
                }
            }

            if ( !empty($from) ) {
                if ( !empty($fromList) ) {
                    $fromList .= '|';
                }
                $fromList .= trim($from);
            }
        }

        // To
        for($y = 0; $y < (strlen($temp->to->incode) + 1); $y++) {
            if ( strlen($temp->to->incode) - $y > 0 ) {
                $to = substr($temp->to->postcode, 0, strlen($temp->to->postcode) - $y);
                if ( $to != $temp->to->postcode ) {
                        $to .= $wildcard;
                }
            }
            else {
                $to = $temp->to->outcode;
                if ( in_array($to, array($outcodeExclude)) ) {
                    $to .= $wildcard;
                }
            }

            if ( !empty($to) ) {
                if ( !empty($toList) ) {
                    $toList .= '|';
                }
                $toList .= trim($to);
            }
        }

        // Score
        $selectSql = "";
        $startSql1 = "";
        $startSql2 = "";
        $endSql1 = "";
        $endSql2 = "";

        $fromArray = explode("|", $fromList);
        foreach($fromArray as $k => $v) {
            $startSql1 .= "WHEN `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
            $startSql2 .= "WHEN `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
        }

        $toArray = explode("|", $toList);
        foreach($toArray as $k => $v) {
            $endSql1 .= "WHEN `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
            $endSql2 .= "WHEN `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
        }

        if ( !empty($startSql1) ) {
            $selectSql .= ", (CASE ". $startSql1 ." ELSE '1000' END) AS `score1_start`";
        }
        else {
            $selectSql .= ", '1000' AS `score1_start`";
        }
        if ( !empty($endSql1) ) {
            $selectSql .= ", (CASE ". $endSql1 ." ELSE '1000' END) AS `score1_end`";
        }
        else {
            $selectSql .= ", '1000' AS `score1_end`";
        }

        if ( !empty($startSql2) ) {
            $selectSql .= ", (CASE ". $startSql2 ." ELSE '1000' END) AS `score2_start`";
        }
        else {
            $selectSql .= ", '1000' AS `score2_start`";
        }
        if ( !empty($endSql2) ) {
            $selectSql .= ", (CASE ". $endSql2 ." ELSE '1000' END) AS `score2_end`";
        }
        else {
            $selectSql .= ", '1000' AS `score2_end`";
        }

        if ( !empty($selectSql) ) {
            $selectSql .= ", (SELECT CASE
                    WHEN (CAST(`score1_start` AS SIGNED INTEGER) >= '1000' OR
                                CAST(`score1_end` AS SIGNED INTEGER) >= '1000') THEN `score2_start`+`score2_end`
                    ELSE `score1_start`+`score1_end`
                END) AS `score_total`";
        }

        // Vehicles
        $vehiclesList = '';
        foreach($vehicles as $k => $v) {
        $id = (int)$v['id'];
            if ( !empty($id) ) {
                if ( !empty($vehiclesList) ) {
                    $vehiclesList .= '|';
                }
                $vehiclesList .= $id;
            }
      }

        $sql = "SELECT * ". $selectSql ."
                        FROM `{$dbPrefix}excluded_routes`
                        WHERE `site_id`='". $siteId ."'
                        AND `published`='1'
                        AND (
                            CASE WHEN `direction` = '1'
                            THEN (
                                `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                                    AND
                                `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                            )
                            ELSE (
                                (
                                    `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                                        AND
                                    `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                                )
                                OR
                                (
                                    `start_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $toList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                                        AND
                                    `end_postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
                                )
                            )
                            END
                        )
                        AND (
                            CASE WHEN ( `start_date` > '0000-00-00 00:00:00' AND `end_date` > '0000-00-00 00:00:00' )
                            THEN (
                                '". $date .":00' BETWEEN `start_date` AND `end_date`
                            )
                            ELSE '1' END
                        )
                        ORDER BY
                            `vehicles` DESC,
                            CAST(`score_total` AS SIGNED INTEGER) ASC,
                            `start_date` DESC,
                            `end_date` DESC,
                            `direction` DESC,
                            `ordering` ASC,
                            `allowed` DESC
                        LIMIT 1";

        $query = $db->select($sql);
        if (!empty($query[0])) {
            $query = $query[0];
        }

        $excludedTemp['list'] = array(
            'from' => $fromList,
            'to' => $toList,
            // 'sql' => $sql
        );

        if ( !empty($query) ) {
            $excludedTemp['id'] = (int)$query->id;
            $excludedTemp['allowed'] = (int)$query->allowed;

            if ( !empty($query->vehicles) ) { //  && !empty($vehiclesList)
                $found = preg_grep("/(^\s*|\s*\,\s*)(". $vehiclesList .")(\s*\,\s*|\s*$)/", array($query->vehicles));
                if ( !empty($found) ) {
                    if ( (int)$query->allowed == 1 ) {
                        $quote->excludedRouteAllowed = 1;
                    }
                    else {
                        $quote->excludedRouteAllowed = 3;
                    }
                }
                else {
                    if ( (int)$query->allowed == 1 ) {
                        $quote->excludedRouteAllowed = 3;
                    }
                    else {
                        $quote->excludedRouteAllowed = 1;
                    }
                }
            }
            else {
                if ( (int)$query->allowed == 1 ) {
                    $quote->excludedRouteAllowed = 1;
                }
                else {
                    $quote->excludedRouteAllowed = 2;
                }
            }

            if ( !empty($query->description) ) {
                $quote->excludedRouteDescription = $query->description;
            }
        }

        $quoteHelper->excludedTemp = $excludedTemp;
    }
    // Excluded location - end


    // Meeting point - start
    $meetingPointTemp = array(
        'returnTrip' => $returnTrip,
        'id' => 0,
        'list' => array()
      );

  $query = null;

  $outcodeExclude = array();
  $wildcard = '.*';
  $fromList = 'ALL';

  // From
  for($x = 0; $x < (strlen($temp->from->incode) + 1); $x++) {
    if ( strlen($temp->from->incode) - $x > 0 ) {
      $from = substr($temp->from->postcode, 0, strlen($temp->from->postcode) - $x);
      if ( $from != $temp->from->postcode ) {
          $from .= $wildcard;
      }
    }
    else {
      $from = $temp->from->outcode;
      if ( in_array($from, array($outcodeExclude)) ) {
        $from .= $wildcard;
      }
    }

    if ( !empty($from) ) {
        if ( !empty($fromList) ) {
          $fromList .= '|';
        }
        $fromList .= trim($from);
    }
  }

    $fromList = 'ALL'; // To do
    if ( !empty($temp->from->postcode) ) {
        $fromList .= '|'. trim($temp->from->postcode);
    }

    // Score
    $selectSql = "";
    $startSql1 = "";

    $fromArray = explode("|", $fromList);
    foreach($fromArray as $k => $v) {
        $startSql1 .= "WHEN `postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $v .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)' THEN '". ($k + 1) ."' ";
    }

    if ( !empty($startSql1) ) {
        $selectSql .= ", (CASE ". $startSql1 ." ELSE '1000' END) AS `score_total`";
    }
    else {
        $selectSql .= ", '1000' AS `score_total`";
    }

    $sql = "SELECT * ". $selectSql ."
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='". $siteId ."'
            AND `published`='1'
            AND `postcode` REGEXP '(^[[:blank:]]*|[[:blank:]]*\,[[:blank:]]*)(". $fromList .")([[:blank:]]*\,[[:blank:]]*|[[:blank:]]*$)'
            AND (
              CASE WHEN (`airport` > '0')
              THEN (
                CASE WHEN (`airport` = '". $quote->isAirport ."') THEN ('1') ELSE ('0') END
              )
              ELSE ('1')
              END
            )
            AND (
              CASE WHEN (`meet_and_greet` > '0')
              THEN (
                CASE WHEN (`meet_and_greet` = '". $meetAndGreet ."') THEN ('1') ELSE ('0') END
              )
              ELSE ('1')
              END
            )
            ORDER BY
                    CAST(`score_total` AS SIGNED INTEGER) ASC,
                    `airport` DESC,
                    `meet_and_greet` DESC,
                    `ordering` ASC
            LIMIT 1";

      $query = $db->select($sql);
        if (!empty($query[0])) {
            $query = $query[0];
        }

        $meetingPointTemp['list'] = array(
            'from' => $fromList,
            'sql' => $sql
          );

      if ( !empty($query) ) {
        $meetingPointTemp['id'] = (int)$query->id;

        if ( !empty($query->description) ) {
          $quote->meetingPoint = $query->description;
        }
      }

    $quoteHelper->meetingPointTemp = $meetingPointTemp;
    // Meeting point - end


    $quote->helper = $quoteHelper;
    //$quote->queryVehicles = $qVehicles;


    // Vehicle price
    $vehicleTotalAmount = 0;



    // Selected service
    $service = \App\Models\Service::where('relation_type', 'site')
      ->where('relation_id', $siteId)
      ->where('status', 'active')
      ->where('id', $serviceId)
      ->first();


    // Company base address
    $extraTime = 30;
    $baseCalculateType = '';
    $baseAddress = '';
    $inRadius = 0;
    $closest = 0;
    $address = '';
    $addressType = '';
    $basesList = [];

    $firstKey = 0;
    $fromLat = $quote->routes[$firstKey]->from->lat;
    $fromLng = $quote->routes[$firstKey]->from->lng;

    $lastKey = count($quote->routes) - 1;
    $toLat = $quote->routes[$lastKey]->to->lat;
    $toLng = $quote->routes[$lastKey]->to->lng;

    $units = ($gConfig['booking_distance_unit'] == 1) ? 'K' : 'M';

    $bases = \App\Models\Base::where('relation_type', '=', 'site')
        ->where('relation_id', '=', $siteId)
        ->where('status', 'activated')
        ->orderBy('ordering', 'ASC');

    foreach($bases->get() as $kB => $vB) {
        if ( !empty($vB->address) && !is_null($vB->lat) && !is_null($vB->lng) ) {
            $dFrom = v2_computeDistanceBetween($vB->lat, $vB->lng, $fromLat, $fromLng, $units);
            $dTo = v2_computeDistanceBetween($vB->lat, $vB->lng, $toLat, $toLng, $units);
            // $dMin = 0;
            $dMin = -1;
            $aType = 'from';

            // if ( empty($dMin) || $dMin > $dFrom ) {
            if ( $dMin == -1 || $dMin > $dFrom ) {
                $dMin = $dFrom;
                $aType = 'from';
            }

            // if ( empty($dMin) || $dMin > $dTo ) {
            if ( $dMin == -1 || $dMin > $dTo ) {
                $dMin = $dTo;
                $aType = 'to';
            }

            if ( $dMin <= $vB->radius ) {
                $inRadius = 1;
            }

            if ( empty($closest) || $closest > $dMin ) {
                $closest = $dMin;
                $address = $vB->address;
                $addressType = $aType;
            }

            $basesList[] = (object)[
                'address' => $vB->address,
                'aType' => $aType,
                'dMin' => $dMin,
                'dFrom' => $dFrom,
                'dTo' => $dTo,
                'radius' => $vB->radius,
            ];
        }
    }

    if ( !$inRadius && !empty($address) && config('site.booking_base_action') == 'allow' ) {
        if (config('site.booking_base_calculate_type_enable')) {
            $baseCalculateType = config('site.booking_base_calculate_type');
            if (in_array($baseCalculateType, ['from', 'to'])) {
                $baseCalculateType = $addressType;
            }
        }
        else {
            $baseCalculateType = $addressType;
        }
        $baseAddress = $address;
    }
    // dd([$baseCalculateType, $baseAddress, $basesList]);


    if ( count($basesList) <= 0 ) {
        $inRadius = 1;
    }
    $quote->inRadius = $inRadius;


    // Calculate vehicle price
    if ( !empty($qVehicles) )
    {
        foreach($qVehicles as $kVehicle => $vVehicle)
        {
            $debug = [];
            $base = 0;
            $addMinPrice = 0;
            $isBasePrice = 0;

            $vehicle = new \stdClass();
            $vehicle->max_passengers = (int)$vVehicle->passengers;
            $vehicle->max_luggage = (int)$vVehicle->luggage;
            $vehicle->max_hand_luggage = (int)$vVehicle->hand_luggage;

            $vehicle->id = (int)$vVehicle->id;
            $vehicle->name = $vVehicle->name;
            $vehicle->available = 1;
            $vehicle->routes = $quote->routes;

            $vehicle->distance = $quote->distance;
            $vehicle->duration = $quote->duration;
            $vehicle->distance_routes = $quote->distance;
            $vehicle->duration_routes = $quote->duration;
            $vehicle->distance_base_start = 0;
            $vehicle->duration_base_start = 0;
            $vehicle->distance_base_end = 0;
            $vehicle->duration_base_end = 0;

            $vehicle->price_fixed_total = 0;
            $vehicle->price_fixed_deposit = 0;
            $vehicle->price_calculated_total = 0;
            $vehicle->price_calculated_deposit = 0;

            // Driver base address
            if ( !empty($service) && !empty($service->getParams('raw')->availability) && config('site.allow_driver_availability') ) {
                $baseQuery = \App\Models\Base::where('relation_type', '=', 'user')
                      ->where('relation_id', '=', $vVehicle->user_id)
                      ->where('status', 'activated')
                      ->orderBy('ordering', 'ASC')
                      ->first();

                if ( !empty($baseQuery->address) ) {
                    $baseAddress = $baseQuery->address;
                }
            }

            if ( !empty($baseAddress) ) {
                // Base to Pickup
                if ( in_array($baseCalculateType, ['from', 'both']) ) {
                    $firstKey = 0;

                    $temp = new \stdClass();
                    $temp->from = $baseAddress;
                    $temp->fromPlaceId = '';
                    $temp->to = $vehicle->routes[$firstKey]->from->address;
                    $temp->toPlaceId = $vehicle->routes[$firstKey]->fromPlaceId;

                    $temp->driver_journey = 1;
                    $temp->travel_type = 'arrival';
                    $temp->travel_time = $vehicle->routes[$firstKey]->travel_time_start->copy();

                    $route = v2_calculateSegment($temp, $quote, $vehicles, $cacheGeocode, $cacheDirections, $cachePlaces);

                    $vehicle->distance += $route->distance;
                    $vehicle->duration += $route->duration;

                    $vehicle->distance_base_start += $route->distance;
                    $vehicle->duration_base_start += $route->duration;

                    $route->driver_journey = $temp->driver_journey;
                    $route->travel_type = $temp->travel_type;
                    $route->travel_time_start = $temp->travel_time->copy()->subMinutes($route->duration);
                    $route->travel_time_end = $temp->travel_time;

                    array_unshift($vehicle->routes, $route);
                }

                // Dropoff to Base
                if ( in_array($baseCalculateType, ['to', 'both']) ) {
                    $lastKey = count($vehicle->routes) - 1;

                    $temp = new \stdClass();
                    $temp->from = $vehicle->routes[$lastKey]->to->address;
                    $temp->fromPlaceId = $vehicle->routes[$lastKey]->toPlaceId;
                    $temp->to = $baseAddress;
                    $temp->toPlaceId = '';

                    $temp->driver_journey = 1;
                    $temp->travel_type = 'departure';
                    $temp->travel_time = $vehicle->routes[$lastKey]->travel_time_end->copy();

                    $route = v2_calculateSegment($temp, $quote, $vehicles, $cacheGeocode, $cacheDirections, $cachePlaces);

                    $vehicle->distance += $route->distance;
                    $vehicle->duration += $route->duration;

                    $vehicle->distance_base_end += $route->distance;
                    $vehicle->duration_base_end += $route->duration;

                    $route->driver_journey = $temp->driver_journey;
                    $route->travel_type = $temp->travel_type;
                    $route->travel_time_start = $temp->travel_time;
                    $route->travel_time_end = $temp->travel_time->copy()->addMinutes($route->duration);

                    $vehicle->routes[] = $route;
                }
            }

            // Calculate ranges
            foreach($ranges as $k => $v) {
                if ( (float)$vehicle->distance <= (float)$v->distance ) {
                    $quoteHelper->distanceMultipliers = $v;
                    break;
                }
            }

            // Calculate routes
            if ( !empty($vehicle->routes) ) {
                $isFixedPrice = 0;
                $driverTotal = 0;
                $isVehicleFactorAdded = 0;

                foreach($vehicle->routes as $kRoute => $vRoute) {
                    // Base Price
                    $multiplier = $quoteHelper->distanceMultiplier;
                    $vehicleVal = 0;
                    $vehicleFactor = 0;
                    if ( !empty($quoteHelper->distanceMultipliers) ) {
                        $multiplier = (float)$quoteHelper->distanceMultipliers->value;
                        $vehicleFactor = (int)$quoteHelper->distanceMultipliers->factor_type;
                        if ( !empty($quoteHelper->distanceMultipliers->vehicle) ) {
                            foreach($quoteHelper->distanceMultipliers->vehicle as $k => $v) {
                                if ( $vVehicle->id == $v->id && !empty($v->value) ) {
                                    $vehicleVal = (float)$v->value;
                                    break;
                                }
                            }
                        }
                    }
                    if ( !empty($vehicleVal) && $vehicleFactor == 0 ) {
                        $multiplier = $vehicleVal; // Override
                    }
                    $priceBase = v2_roundVal($vRoute->distance * $multiplier);

                    if ( !empty($vehicleVal) ) {
                        if ( $vehicleFactor == 2 ) {
                            $priceBase *= (float)$vehicleVal; // Multiply
                        }
                        elseif ( $vehicleFactor == 1 && !$isVehicleFactorAdded ) {
                            $priceBase += (float)$vehicleVal; // Add
                            $isVehicleFactorAdded = 1;
                        }
                    }

                    // Fixed Price
                    $price_fixed_deposit = 0;
                    $priceFixed = (float)$vRoute->fixed->value;
                    if ( !empty($vRoute->fixed->vehicle) ) {
                        foreach($vRoute->fixed->vehicle as $k => $v) {
                            if ( $vVehicle->id == $v->id ) {
                                // if ( !empty($v->value) ) {
                                    if ( $vRoute->fixed->type == 2 ) {
                                        $priceFixed = (float)$v->value;
                                    }
                                    elseif ( $vRoute->fixed->type == 1 ) {
                                        $priceFixed = (float)$vRoute->fixed->value + (float)$v->value;
                                    }
                                    else {
                                        $priceFixed = (float)$vRoute->fixed->value * (float)$v->value;
                                    }
                                // }

                                if ( !empty($v->deposit) ) {
                                    $price_fixed_deposit = (float)$v->deposit;
                                }
                                else {
                                    $price_fixed_deposit = !empty($vRoute->fixed->deposit) ? (float)$vRoute->fixed->deposit : 0;
                                }
                                break;
                            }
                        }
                    }

                    // Modify Mileage Price
                    $price_calculated_deposit = 0;
                    $priceCalculated = 0;
                    if ( $vRoute->calculated->type == 2 ) {
                        $priceCalculated = v2_roundVal($vRoute->distance * (float)$vRoute->calculated->value);
                    }
                    if ( !empty($vRoute->calculated->vehicle) ) {
                        foreach($vRoute->calculated->vehicle as $k => $v) {
                            if ( $vVehicle->id == $v->id ) {
                                if ( !empty($v->value) ) {
                                    if ( $vRoute->calculated->type == 2 ) {
                                        $priceCalculated = v2_roundVal($vRoute->distance * (float)$v->value);
                                    }
                                    elseif ( $vRoute->calculated->type == 1 ) {
                                        $priceCalculated = $priceBase + (float)$v->value;
                                    }
                                    else {
                                        $priceCalculated = $priceBase * (float)$v->value;
                                    }
                                }

                                if ( !empty($v->deposit) ) {
                                    $price_calculated_deposit = (float)$v->deposit;
                                }
                                else {
                                    $price_calculated_deposit = !empty($vRoute->calculated->deposit) ? (float)$vRoute->calculated->deposit : 0;
                                }
                                break;
                            }
                        }
                    }


                    if ( config('site.booking_pricing_mode') == 2 ) {
                        $priceFixed = 0;
                    }

                    if ( config('site.booking_pricing_mode') == 1 ) {
                        $priceCalculated = 0;
                        $priceBase = 0;
                    }


                    $baseTotal = 0;

                    if ($vRoute->distance <= 0) {
                        $priceFixed = 0;
                        $priceCalculated = 0;
                        $priceBase = 0;
                    }

                    if ( $priceFixed > 0 ) {
                        $baseTotal += $priceFixed;

                        if ($vRoute->driver_journey == 0) {
                            $isFixedPrice = 1;
                        }

                        if ( config('site.fixed_prices_deposit_enable') == 1 ) {
                            $vehicle->price_fixed_total += $priceFixed;
                            if ( config('site.fixed_prices_deposit_type') == 1 ) {
                                $vehicle->price_fixed_deposit += $price_fixed_deposit;
                            }
                            else {
                                $vehicle->price_fixed_deposit += ($priceFixed / 100) * $price_fixed_deposit;
                            }
                        }
                    }
                    elseif ( $priceCalculated > 0 ) {
                        $baseTotal += $priceCalculated;
                        $addMinPrice = 1;

                        if ( config('site.fixed_prices_deposit_enable') == 1 ) {
                            $vehicle->price_calculated_total += $priceCalculated;
                            if ( config('site.fixed_prices_deposit_type') == 1 ) {
                                $vehicle->price_calculated_deposit += $price_calculated_deposit;
                            }
                            else {
                                $vehicle->price_calculated_deposit += ($priceCalculated / 100) * $price_calculated_deposit;
                            }
                        }
                    }
                    elseif ( $priceBase > 0 ) {
                        $baseTotal += $priceBase;
                        $addMinPrice = 1;
                    }

                    // Duration rate ( Minute )
                    if ( $priceFixed <= 0 ) {
                        $duration_rate = (float)$gConfig['booking_duration_rate'];
                        if ( !empty($vRoute->duration) && !empty($duration_rate) ) {
                            $baseTotal += $vRoute->duration * $duration_rate;
                        }
                    }

                    if ( !empty($vRoute->driver_journey) ) {
                        $driverTotal += $baseTotal;
                    }

                    if ($baseTotal > 0) {
                        $isBasePrice = 1;
                    }

                    $base += $baseTotal;
                }

                if ( $isFixedPrice && $driverTotal && config('site.booking_exclude_driver_journey_from_fixed_price') ) {
                    $base -= $driverTotal;
                }
            }

            // Service base price - https://en.wikipedia.org/wiki/Subtraction
            $isServiceHourlyRate = 0;

            if ( !empty($service) &&
                !empty($service->getParams('raw')->factor_type) &&
                !empty($service->getParams('raw')->factor_value) ) {
                if ( $service->getParams('raw')->factor_type == 'addition' ) {
                    $base += (float)$service->getParams('raw')->factor_value;
                    $isServiceHourlyRate = 1;
                }
                elseif ( $service->getParams('raw')->factor_type == 'multiplication' ) {
                    $base *= (float)$service->getParams('raw')->factor_value;
                    $isServiceHourlyRate = 1;
                }
            }

            // Service hourly rate
            if ( !empty($service) && $service->getParams('raw')->duration ) {
                if ( !empty($serviceDuration) && !empty($vVehicle->hourly_rate) ) {
                    $base += $serviceDuration * (float)$vVehicle->hourly_rate;
                    $isServiceHourlyRate = 1;
                }
            }

            // Min price
            if ( $gConfig['booking_min_price_type'] == 1 ) {
                if ( $addMinPrice ) {
                    $base += $quoteHelper->minPrice;
                }
            }
            else {
                if ( $base < $quoteHelper->minPrice ) {
                    $base = $quoteHelper->minPrice;
                }
            }

            // Vehicle min price
            if (!empty($gConfig['booking_vehicle_min_price'])) {
                $vehicle_min_price = json_decode($gConfig['booking_vehicle_min_price']);
                if (!empty($vehicle_min_price)) {
                    foreach ($vehicle_min_price as $vMpV) {
                        if ($vMpV->id == $vehicle->id) {
                            if ($base < $vMpV->value) {
                                $base = (float)$vMpV->value;
                            }
                            break;
                        }
                    }
                }
            }

            // Night surcharge
            if (!empty($gConfig['night_charge_enable']) && !empty($gConfig['booking_night_surcharge'])) {
              $booking_night_surcharge = json_decode($gConfig['booking_night_surcharge']);

              if (!empty($booking_night_surcharge)) {
                foreach ($booking_night_surcharge as $surcharge) {
                  $currentDate = !empty($date) ? Carbon::parse($date .':00') : Carbon::now();
                  $ok = 1;

                  if (!empty($surcharge->vehicle_id) && $surcharge->vehicle_id != $vehicle->id) {
                    $ok = 0;
                  }

                  if (!empty($surcharge->repeat_days) && !in_array($currentDate->dayOfWeek, $surcharge->repeat_days)) {
                    $ok = 0;
                  }

                  $checkTime = v2_timeToSec(date('H:i:s', strtotime($date .':00')));
                  $limitStartTime = v2_timeToSec($surcharge->time_start .':00');
                  $limitEndTime = v2_timeToSec($surcharge->time_end .':00');
                  $allowNightTime = 0;

                  if ( $limitStartTime == $limitEndTime ) {
                    $allowNightTime = 0;
                  }
                  elseif ( $limitStartTime > $limitEndTime ) {
                    if ( !($checkTime <= $limitStartTime && $checkTime >= $limitEndTime + 1) ) {
                      $allowNightTime = 1;
                    }
                  }
                  else {
                    if ( $checkTime >= $limitStartTime + 1 && $checkTime <= $limitEndTime ) {
                      $allowNightTime = 1;
                    }
                  }

                  if (!$allowNightTime) {
                    $ok = 0;
                  }

                  if ($ok && !empty($surcharge->factor_value) ) {
                    if ($surcharge->factor_type == 'multiplication') {
                      $base *= (float)$surcharge->factor_value;
                    }
                    else {
                      $base += (float)$surcharge->factor_value;
                    }
                  }
                }
              }
            }

            // if ( !empty($quoteHelper->nightFactorValue) ) {
            //     if ( !empty($quoteHelper->nightFactorType) ) {
            //         $base += (float)$quoteHelper->nightFactorValue;
            //     }
            //     else {
            //         $base *= (float)$quoteHelper->nightFactorValue;
            //     }
            // }

            if ( !empty($quoteHelper->holidayMultipliers) ) {
                foreach($quoteHelper->holidayMultipliers as $k => $v) {
                    if ( !empty($v['type']) ) {
                        $base += (float)$v['value'];
                    }
                    else {
                        $base *= (float)$v['value'];
                    }
                }
            }

            if ( !empty($vVehicle->price) ) {
                if ( $vVehicle->factor_type == 1 ) { // Multiply (*)
                    $base *= (float)$vVehicle->price;
                }
                else { // Flat (+)
                    $base += (float)$vVehicle->price;
                }
            }

            // // Service base price - https://en.wikipedia.org/wiki/Subtraction
            // $isServiceHourlyRate = 0;
            //
            // if ( !empty($service) &&
            //     !empty($service->getParams('raw')->factor_type) &&
            //     !empty($service->getParams('raw')->factor_value) ) {
            //     if ( $service->getParams('raw')->factor_type == 'addition' ) {
            //         $base += (float)$service->getParams('raw')->factor_value;
            //         $isServiceHourlyRate = 1;
            //     }
            //     elseif ( $service->getParams('raw')->factor_type == 'multiplication' ) {
            //         $base *= (float)$service->getParams('raw')->factor_value;
            //         $isServiceHourlyRate = 1;
            //     }
            // }
            //
            // // Service hourly rate
            // if ( !empty($service) && $service->getParams('raw')->duration ) {
            //     if ( !empty($serviceDuration) && !empty($vVehicle->hourly_rate) ) {
            //         $base += $serviceDuration * (float)$vVehicle->hourly_rate;
            //         $isServiceHourlyRate = 1;
            //     }
            // }

            $base = v2_roundVal($base);

            $vehicleAmount = 0;
            if ( !empty($vehicles) ) {
                foreach($vehicles as $k => $v) {
                    $id = (int)$v['id'];
                    $amount = (int)$v['amount'];
                    if ( $vVehicle->id == $id && $amount > 0 ) {
                        $vehicleAmount = $amount;
                        break;
                    }
                }
            }

            $vehicleAmountDisplay = ($vehicleAmount > 0) ? $vehicleAmount : 1;

            $chargeInclude = 0;
            $chargeExclude = 0;
            foreach ($quote->summary as $kSummary => $vSummary) {
                if ( $vSummary->type == 'parking' ) {
                    if ( empty($vSummary->apply_on_vehicles) || in_array($vVehicle->id, $vSummary->apply_on_vehicles) ) {
                        $chargeInclude += $vSummary->total;
                        if ( empty($gConfig['booking_include_aiport_charges']) ) {
                            $chargeExclude += $vSummary->total;
                        }
                    }
                }
            }
            $chargesBase = $quoteHelper->chargesBase + $chargeInclude;
            $display = $vehicleAmountDisplay * ($base + $chargesBase - $chargeExclude);

            // $display = $vehicleAmountDisplay * ($base + $quoteHelper->chargesBase - $quoteHelper->chargesBaseExclude);
            $display = v2_roundTotal($display + $quoteHelper->chargesGeneral - $quoteHelper->chargesGeneralExclude);


            // $data['!!isBasePrice'] = $isBasePrice;
            if (!$isBasePrice && !$isServiceHourlyRate) {
                $base = 0;
                $display = 0;

                if (config('site.booking_hide_vehicle_without_price') == 1) {
                    $vVehicle->disable_info = 'yes';
                    // $vehicle->hidden = 1;
                }
            }


            // Availability
            if ( !empty($service) && !empty($service->getParams('raw')->availability) && config('site.allow_driver_availability') ) {
                $travelDuration = $vehicle->duration_routes + ($serviceDuration * 60);

                $bd = !empty($date) ? Carbon::parse($date .':00') : Carbon::now();

                $tsd = $bd->copy();
                $ted = $bd->copy()->addMinutes($travelDuration);

                $bsd = $tsd->copy()->subMinutes($vehicle->duration_base_start);
                $bed = $ted->copy()->addMinutes($vehicle->duration_base_end);

                $rsd = $bsd->copy()->subMinutes($extraTime);
                $red = $bed->copy()->addMinutes($extraTime);

                // Events
                $eventsList = [];

                if ( !empty($vVehicle->user_id) ) {
                    $vsd = $rsd->copy()->startOfDay();
                    $ved = $red->copy()->endOfDay();

                    $events = \App\Models\Event::where('relation_type', '=', 'user')
                        ->where('relation_id', '=', $vVehicle->user_id)
                        ->orderBy('status', 'desc')
                        ->orderBy('ordering', 'asc');

                    foreach ($events->get() as $event) {
                        $event->repeat_days = !empty($event->repeat_days) ? json_decode($event->repeat_days) : [$event->start_at->dayOfWeek];
                        $event->repeat_interval = !empty($event->repeat_interval) ? $event->repeat_interval : 1;

                        if ($event->repeat_type == 'none') {
                            $sd = $event->start_at->copy();
                            $ed = $event->end_at->copy();

                            if ( $event->status == 'active' ) {
                                if ( $rsd->gte($sd) && $red->lte($ed) ) {
                                    $vehicle->available = 1;
                                }
                            }
                            else {
                                if ( $ed->gt($rsd) && $sd->lt($red) ) {
                                    $vehicle->available = 0;
                                }
                            }

                            $eventsList[] = [
                                'id' => $event->id,
                                'title' => $event->name,
                                'start' => $sd->toDateTimeString(),
                                'end' => $ed->toDateTimeString()
                            ];

                            continue;
                        }

                        if ($event->start_at->lt($vsd)) {
                            $vsd->subSeconds($event->start_at->diffInSeconds($vsd));
                        }

                        if ($event->end_at->gt($ved)) {
                            $ved->addSeconds($event->end_at->diffInSeconds($ved));
                        }

                        for ($i = 0; $i <= $vsd->diffInDays($ved); $i++) {
                            $sd = $vsd->copy()->addDays($i)->setTime($event->start_at->hour, $event->start_at->minute, $event->start_at->second);
                            $ed = $sd->copy()->addSeconds($event->start_at->diffInSeconds($event->end_at));

                            $skip = 0;
                            $diff = 0;
                            $diffSpan = 0;
                            $time = 0;

                            switch ($event->repeat_type) {
                                case 'daily':
                                    $diff = $sd->diffInDays($event->start_at);
                                    $diffSpan = $event->start_at->diffInDays($event->end_at);
                                    $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);
                                break;
                                case 'weekly':
                                    $diff = $sd->diffInDays($event->start_at);
                                    $diffSpan = $event->start_at->diffInDays($event->end_at);
                                    $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);

                                    $repeat_days = $event->repeat_days;
                                    if ($event->start_at->diffInDays($event->end_at) > 0) {
                                        for ($j = 0; $j <= $event->start_at->diffInDays($event->end_at); $j++) {
                                            $repeat_days[] = (string)$sd->copy()->addDays($j)->dayOfWeek;
                                        }
                                    }
                                    $event->repeat_days = $repeat_days;

                                    if ( !in_array($sd->dayOfWeek, $event->repeat_days) ) {
                                        $skip = 1;
                                    }
                                break;
                                case 'monthly':
                                    $diff = $sd->diffInMonths($event->start_at);
                                    $diffSpan = $event->start_at->diffInMonths($event->end_at);
                                    $time = $sd->copy()->subMonths($diff)->diffInSeconds($event->start_at);
                                break;
                                case 'yearly':
                                    $diff = $sd->diffInYears($event->start_at);
                                    $diffSpan = $event->start_at->diffInYears($event->end_at);
                                    $time = $sd->copy()->subYears($diff)->diffInSeconds($event->start_at);
                                break;
                                default:
                                    $diff = $sd->diffInDays($event->start_at);
                                    $diffSpan = $event->start_at->diffInDays($event->end_at);
                                    $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);

                                    $event->repeat_interval = 1;
                                    $event->repeat_limit = 1;
                                break;
                            }

                            // The same day of week
                            if ( $time != 0 ) {
                                $skip = 1;
                            }

                            // Lower then start date
                            if ( $sd->lt($event->start_at) ) {
                                $skip = 1;
                            }

                            // Repeat end date
                            if ( !empty($event->repeat_end) && $ed->gte($event->repeat_end) ) {
                                $skip = 1;
                            }

                            // Repeat interval
                            if ( !empty($event->repeat_interval) && ($diff % $event->repeat_interval) - $diffSpan > 0 ) {
                                $skip = 1;
                            }

                            // Repeat limit
                            if ( !empty($event->repeat_limit) && $diff >= ($event->repeat_limit * $event->repeat_interval) ) {
                                $skip = 1;
                            }

                            if ( $skip ) {
                                continue;
                            }

                            if ( $event->status == 'active' ) {
                                if ( $rsd->gte($sd) && $red->lte($ed) ) {
                                    $vehicle->available = 1;
                                }
                            }
                            else {
                                if ( $ed->gt($rsd) && $sd->lt($red) ) {
                                    $vehicle->available = 0;
                                }
                            }

                            $eventsList[] = [
                                'id' => $event->id,
                                'title' => $event->name,
                                'start' => $sd->toDateTimeString(),
                                'end' => $ed->toDateTimeString()
                            ];
                        }
                    }
                }

                // Bookings
                $bookings = \App\Models\BookingRoute::whereRaw("
                                DATE_ADD(`date`, INTERVAL (`duration` + (`service_duration` * 60) + `duration_base_end`) MINUTE) >= '". $rsd->toDateTimeString() ."'
                                    AND
                                DATE_SUB(`date`, INTERVAL `duration_base_start` MINUTE) <= '". $red->toDateTimeString() ."'
                            ")
                            ->whereNotIn('status', ['quote', 'canceled'])
                            ->where('vehicle', 'regexp', '\"id\":\"'. $vVehicle->id .'\",\"amount\":\"[1-9]\"');

                if ( !empty($bookings->count()) ) {
                    $vehicle->available = 0;
                }

                // Debug
                if ( config('app.debug') ) {
                    $debug['quoteDistance'] = $quote->distance;
                    $debug['quoteDuration'] = $quote->duration;
                    $debug['extraTime'] = $extraTime;
                    $debug['serviceDuration'] = $serviceDuration;
                    $debug['bd'] = $bd;
                    $debug['bsd'] = $bsd;
                    $debug['bed'] = $bed;
                    $debug['vsd'] = $vsd;
                    $debug['ved'] = $ved;
                    $debug['rsd'] = $rsd;
                    $debug['red'] = $red;
                    $debug['availabilityurl'] = route('booking.availability', $vehicle->id) .'?rstart='. $rsd->timestamp .'&rend='. $red->timestamp .'&tstart='. $tsd->timestamp .'&tend='. $ted->timestamp;
                    $debug['bookings_sql'] = $bookings->toSql();
                    $debug['bookings'] = $bookings->get();
                    $debug['eventsList'] = $eventsList;
                }
            }

            // Link
            if ( !empty($vVehicle->disable_info) && $vVehicle->disable_info == 'yes') {
                $vehicle->linkType = 'enquire';
                $vehicle->linkUrl = $gConfig['url_contact'];
            }
            elseif ( empty($vehicle->available) ) {
                $vehicle->linkType = 'availability';
                $vehicle->linkUrl = route('booking.availability', $vehicle->id) .'?rstart='. $rsd->timestamp .'&rend='. $red->timestamp .'&tstart='. $tsd->timestamp .'&tend='. $ted->timestamp;
            }
            else {
                $vehicle->linkType = '';
                $vehicle->linkUrl = '';
            }

            // Hide
            if ( !empty($service->id) &&
                !empty($vVehicle->service_ids) &&
                !in_array($service->id, json_decode($vVehicle->service_ids)) ) {
                $vehicle->hidden = 1;
            }
            else {
                $vehicle->hidden = 0;
            }

            // Update vehicle
            $vehicle->amount = $vehicleAmount;
            $vehicle->vehicle = $base;
            // $vehicle->charges = $vehicleAmount * $quoteHelper->chargesBase;
            // $vehicle->total = $vehicleAmount * ($base + $quoteHelper->chargesBase);
            $vehicle->charges = $vehicleAmount * $chargesBase;
            $vehicle->total = $vehicleAmount * ($base + $chargesBase);
            $vehicle->display = $display;

            if ( config('app.debug') ) {
                $vehicle->debug = $debug;
            }
            else {
                unset($vehicle->routes);
            }


            // Account discount
            if ( $gConfig['booking_account_discount'] > 0 && $userId > 0 ) {
                $discount = v2_roundTotal(($vehicle->display / 100) * $gConfig['booking_account_discount'], .50);
                if ( $discount > 0 ) {
                    $vehicle->display -= $discount;
                }
            }

            // Return discount
            if ( $gConfig['booking_return_discount'] > 0 && $postData['routeReturn'] == 2 ) {
                $discount = v2_roundTotal(($vehicle->display / 100) * $gConfig['booking_return_discount'], .50);
                if ( $discount > 0 ) {
                    $vehicle->display -= $discount;
                }
            }

            $vehicle->display = v2_roundVal($vehicle->display);


            // Update quote
            $quote->vehicles[] = $vehicle;
            $quote->total += $vehicle->total;
            $quote->charges += $vehicle->charges;

            $vehicleTotalAmount += $vehicleAmount;
        }
    }

    // Update duration time
    if ( !empty($vehicles) ) {
        foreach($vehicles as $k1 => $v1) {
            $id = (int)$v1['id'];
            $amount = (int)$v1['amount'];

            foreach($quote->vehicles as $k2 => $v2) {
                if ( $id == $v2->id && $amount > 0 ) {
                    if ( $quote->distance < $v2->distance ) {
                        $quote->distance = $v2->distance_routes;
                        $quote->distance_base_start = $v2->distance_base_start;
                        $quote->distance_base_end = $v2->distance_base_end;
                    }

                    if ( $quote->duration < $v2->duration ) {
                        $quote->duration = $v2->duration_routes;
                        $quote->duration_base_start = $v2->duration_base_start;
                        $quote->duration_base_end = $v2->duration_base_end;
                    }
                }
            }
        }
    }

    $quote->total = v2_roundTotal($quote->total + $quoteHelper->chargesGeneral);
    $quote->charges = $quote->charges + $quoteHelper->chargesGeneral;


    // Discounts
    $quote->accountDiscount = 0;

    // Account discount
    if ( $gConfig['booking_account_discount'] > 0 && $userId > 0 ) {
        $discount = ($quote->total / 100) * $gConfig['booking_account_discount'];
        $discount = v2_roundTotal($discount, .50);
        if ( $discount > 0 ) {
            $quote->total -= $discount;
            $quote->accountDiscount = $discount;
        }
    }

    // Return discount
    if ( $gConfig['booking_return_discount'] > 0 && $postData['routeReturn'] == 2 ) {
        $discount = ($quote->total / 100) * $gConfig['booking_return_discount'];
        $discount = v2_roundTotal($discount, .50);
        if ( $discount > 0 ) {
            $quote->total -= $discount;
            $quote->accountDiscount = $discount;
        }
    }


    // Summary
    $total = v2_roundVal($quote->total);

    foreach($quote->summary as $kSummary => $vSummary) {
        $remove = 0;
        if ( $vSummary->type == 'parking' ) {
            foreach($quote->vehicles as $kT => $vT) {
                if ($vT->amount > 0) {
                    if ( !empty($vSummary->apply_on_vehicles) && !in_array($vT->id, $vSummary->apply_on_vehicles) ) {
                        $remove = 1;
                    }
                }
            }
        }

        if (!$vSummary->visible || $remove) {
            unset($quote->summary[$kSummary]);
            continue;
        }

        switch( $vSummary->type ) {
            case 'airportStart':
            case 'airportEnd':
            case 'airportBoth':
                $vSummary->amount = $vehicleTotalAmount;
                $vSummary->total = $vSummary->amount * $vSummary->base;
                $quote->summary[$kSummary] = $vSummary;
            break;
            case 'stopover':
                $vSummary->amount = $vehicleTotalAmount * $vSummary->amount;
                $vSummary->total = $vSummary->amount * $vSummary->base;
                $quote->summary[$kSummary] = $vSummary;
            break;
        }

        if ( !empty($vSummary->name) && $vSummary->visible > 0 ) {
            if ( $vSummary->type == 'info' ) {
                if ( !empty($quote->infoText) ) {
                    $quote->infoText .= "\n";
                }

                $quote->infoText .= $vSummary->name;
                $quote->summaryText .= $vSummary->name;
            }
            else {
                if ( !empty($quote->summaryText) ) {
                    $quote->summaryText .= "\n";
                }

                $quote->summaryText .= $vSummary->name;

                if ( $vSummary->amount > 1 && $vSummary->type == 'waiting_time' ) {
                    $quote->summaryText .= " (". $vSummary->amount ." min)";
                }
                elseif ( $vSummary->amount > 1 ) {
                    $quote->summaryText .= " (x". $vSummary->amount .")";
                }

                if ( $vSummary->total > 0 ) {
                    $quote->summaryText .= " ". config('site.currency_symbol') . $vSummary->total . config('site.currency_code');
                    $total -= $vSummary->total;
                }
            }
        }
    }

    $total = v2_roundVal($total);

    if ( $total > 0 ) {
        $summary = new \stdClass();
        $summary->type = 'journey';
        $summary->name = trans('booking.summary_types.journey');
        // $summary->name = $gLanguage['quote_Fare'];
        $summary->amount = 1;
        $summary->base = $total;
        $summary->total = $total;
        $summary->visible = 1;
        array_unshift($quote->summary, $summary);

        $text = $summary->name ." ". config('site.currency_symbol') . $total . config('site.currency_code');
        if ( !empty($quote->summaryText) ) {
            $text .= "\n";
        }
        $quote->summaryText = $text . $quote->summaryText;
    }


    // Remove
    //foreach($quote->routes as $kRoute => $vRoute) {
    //	unset($vRoute->fixedPrice);
    //	unset($vRoute->fixedTemp);
    //	unset($vRoute->fixedPriceMultipliers);
    //	unset($vRoute->excludedTemp);
    //}

    //unset($quote->summary);
    //unset($quote->steps);

    // Converted vehicle total
    $maxVehicleCapacity = [
        'passengers' => 0,
        'luggage' => 0,
        'hand_luggage' => 0
    ];

    foreach($quote->vehicles as $k => $v) {
        $v->totalOriginal = $v->total;
        $v->total = $v->display;

        if ($v->total <= 0 && $v->linkType != 'enquire' && config('site.booking_hide_vehicle_without_price') == 0) {
            $v->hidden = 1;
        }

        if (config('site.booking_show_preferred') == 1) {
            if ($maxVehicleCapacity['passengers'] < $v->max_passengers) {
                $maxVehicleCapacity['passengers'] = $v->max_passengers;
            }
            if ($maxVehicleCapacity['luggage'] < $v->max_luggage) {
                $maxVehicleCapacity['luggage'] = $v->max_luggage;
            }
            if ($maxVehicleCapacity['hand_luggage'] < $v->max_hand_luggage) {
                $maxVehicleCapacity['hand_luggage'] = $v->max_hand_luggage;
            }
        }
    }

    if (config('site.booking_show_preferred') == 1) {
        $selectedVehicle = 0;

        foreach($quote->vehicles as $k => $v) {
            if (
                $v->max_passengers >= $preferredPassengers &&
                $v->max_luggage >= $preferredLuggage &&
                $v->max_hand_luggage >= $preferredHandLuggage &&
                $v->max_passengers <= $maxVehicleCapacity['passengers'] &&
                $v->max_luggage <= $maxVehicleCapacity['luggage'] &&
                $v->max_hand_luggage <= $maxVehicleCapacity['hand_luggage'] &&
                $v->linkType != 'enquire' &&
                $v->hidden == 0
            ) {
                $maxVehicleCapacity['passengers'] = $v->max_passengers;
                $maxVehicleCapacity['luggage'] = $v->max_luggage;
                $maxVehicleCapacity['hand_luggage'] = $v->max_hand_luggage;
                $selectedVehicle = $v->id;
            }
        }

        foreach($quote->vehicles as $k => $v) {
            if ($selectedVehicle == $v->id) {
                $v->amount = 1;
            }
            else {
                $v->amount = 0;
                $v->hidden = 1;
            }
        }
    }

    // Old display
    $firstRouteIndex = 0;
    $lastRouteIndex = 0;

    foreach($quote->routes as $k => $v) {
        if ( $lastRouteIndex <= $k ) {
            $lastRouteIndex = $k;
        }
    }

    $fromRoute = $quote->routes[$firstRouteIndex]->from;
    $toRoute = $quote->routes[$lastRouteIndex]->to;


    // Steps
    // $quote->stepsText .= $gLanguage['quote_EstimatedDistance'] .": <b>". $quote->distance ." ". (($gConfig['booking_distance_unit'] == 1) ? $gLanguage['quote_Kilometers'] : $gLanguage['quote_Miles']) ."</b>\n";
    // $quote->stepsText .= $gLanguage['quote_EstimatedTime'] .": <b>". v2_formatDuration($quote->duration * 60) ."</b>";
    //
    // $i = 1;
    // foreach($quote->routes as $kStep => $vStep)
    // {
    //     $quote->stepsText .= "\n\n<b>". $gLanguage['quote_Route'] ." ". $i ."</b>\n";
    //     $quote->stepsText .= $gLanguage['quote_From'] .": ". $vStep->from->address ."\n";
    //     $quote->stepsText .= $gLanguage['quote_To'] .": ". $vStep->to->address ."\n";
    //     $quote->stepsText .= $gLanguage['quote_Distance'] .": ". $vStep->distance ." ". (($gConfig['booking_distance_unit'] == 1) ? $gLanguage['quote_Kilometers'] : $gLanguage['quote_Miles']) ."\n";
    //     $quote->stepsText .= $gLanguage['quote_Time'] .": ". v2_formatDuration($vStep->duration * 60) ."";
    //     $i++;
    // }

    $stepsText = '';
    $i = 1;

    if ($quote->returnTrip == 0) {
        $stepsText .= '<div class="eto-v2-summary-route-name">'. trans('frontend.bookingSummaryRoute1') .'</div>';
    }
    else {
        $stepsText .= '<div class="eto-v2-summary-route-name">'. trans('frontend.bookingSummaryRoute2') .'</div>';
    }

    foreach($quote->routes as $kStep => $vStep) {
        if (count($quote->routes) > 1) {
            $stepsText .= '<div class="eto-v2-summary-route-name-number clearfix">
              <span class="name">'. $gLanguage['quote_Route'] .' '. $i .'</span>
              <span class="line"></span>
            </div>';
        }

        $stepsText .= '<div class="eto-v2-summary-details">
          <div class="eto-v2-summary-details-row-master clearfix">
            <div class="eto-v2-summary-details-row eto-v2-summary-details-from clearfix">
                <div class="eto-v2-summary-details-title clearfix">
                    <span>'. $gLanguage['quote_From'] .':</span>
                    <i class="ion-arrow-right-c"></i>
                </div>
                <div class="eto-v2-summary-details-address">'. $vStep->from->address .'</div>
            </div>
            <div class="eto-v2-summary-details-row eto-v2-summary-details-to clearfix">
              <div class="eto-v2-summary-details-title clearfix">
                  <span>'. $gLanguage['quote_To'] .':</span>
              </div>
              <div class="eto-v2-summary-details-address">'. $vStep->to->address .'</div>
            </div>
          </div>
          <div class="eto-v2-summary-details-info clearfix">';
          if ($kStep == 0) {
            $stepsText .= '<div class="eto-v2-summary-details-time">
              <span class="eto-v2-summary-details-info-name">'. trans('frontend.bookingSummaryDateTime') .':</span>
              <span class="eto-v2-summary-details-info-value">'. SiteHelper::formatDateTime($quote->date) .'</span>
            </div>';
          }
          $stepsText .= '<div class="eto-v2-summary-details-distance">
              <span class="eto-v2-summary-details-info-name">'. $gLanguage['quote_Distance'] .':</span>
              <span class="eto-v2-summary-details-info-value">
                '. $vStep->distance .' '. (($gConfig['booking_distance_unit'] == 1) ? $gLanguage['quote_Kilometers'] : $gLanguage['quote_Miles']) .'
              </span>
            </div>
            <div class="eto-v2-summary-details-duration">
              <span class="eto-v2-summary-details-info-name">'. trans('frontend.bookingSummaryDuration') .':</span>
              <span class="eto-v2-summary-details-info-value">
                '. v2_formatDuration($vStep->duration * 60) .'
              </span>
            </div>
          </div>
        </div>';

        $i++;
    }

    $vehList = '';
    foreach($quote->vehicles as $kVeh => $vVeh) {
        if ($vVeh->amount > 0) {
            $vehList .= '<div class="eto-v2-summary-vehicle">'. $vVeh->name .' - '. SiteHelper::formatPrice($vVeh->total) .'</div>';
        }
    }

    if ($vehList) {
      $stepsText .= '<div class="eto-v2-summary-vehicles">'. $vehList .'</div>';
    }

    $stepsText = '<div class="eto-v2-summary">
        <div class="eto-v2-summary-header clearfix">
          <div class="eto-v2-summary-header-title">'. trans('frontend.bookingSummaryHeaderTitle') .'</div>
          <div class="eto-v2-summary-header-edit">
            <i class="ion-edit"></i>
            <span>'. trans('frontend.js.bookingButton_Edit') .'</span>
          </div>
        </div>
        '. $stepsText .'
        <div class="eto-v2-summary-note">
          <i class="ion-locked"></i>
          <span>'. trans('frontend.bookingSummaryNote') .'</span>
        </div>
    </div>';

    $quote->stepsText = $stepsText;


    $results = array(
        'status' => $quote->status,
        'status_postcode_match' => $quote->status_postcode_match,
        'distance' => $quote->distance,
        'duration' => $quote->duration,
        'distance_base_start' => $quote->distance_base_start,
        'duration_base_start' => $quote->duration_base_start,
        'distance_base_end' => $quote->distance_base_end,
        'duration_base_end' => $quote->duration_base_end,
        'address' => array(
            'start' => $fromRoute->address,
            'start_postcode' => $fromRoute->postcode,
            'start_outcode' => $fromRoute->outcode,
            'start_incode' => $fromRoute->incode,
            'end' => $toRoute->address,
            'end_postcode' => $toRoute->postcode,
            'end_outcode' => $toRoute->outcode,
            'end_incode' => $toRoute->incode
        ),
        'coordinate' => array(
            'start' => array(
                'lat' => $fromRoute->lat,
                'lon' => $fromRoute->lng
            ),
            'end' => array(
                'lat' => $toRoute->lat,
                'lon' => $toRoute->lng
            )
        ),
        'extraChargesList' => $quote->summary,
        'extraChargesPrice' => $quote->charges,
        'totalPrice' => $quote->total,
        'totalPriceWithDiscount' => $quote->total,
        'totalDiscount' => 0,
        'accountDiscount' => $quote->accountDiscount,
        'vehicleButtons' => $quote->vehicles,
        // 'stepsText' => SiteHelper::nl2br2($quote->stepsText),
        'stepsText' => $quote->stepsText,
        'summaryText' => SiteHelper::nl2br2($quote->summaryText),
        'infoText' => SiteHelper::nl2br2($quote->infoText),
        'isAirport' => $quote->isAirport,
        'isAirport2' => $quote->isAirport2,
        'inRadius' => $quote->inRadius,
        'meetingPoint' => $quote->meetingPoint,
        'excludedRouteAllowed' => $quote->excludedRouteAllowed,
        'excludedRouteDescription' => $quote->excludedRouteDescription
    );

    if ( config('app.debug') ) {
        $results['!quote'] = $quote;
    }

    return $results;
}


function v2_calculateScheduled($postData, $postType) {
    global $gConfig, $quoteType;

    $results = [
        'status' => 'OK',
        'distance' => 0,
        'duration' => 0,
        'distance_base_start' => 0,
        'duration_base_start' => 0,
        'distance_base_end' => 0,
        'duration_base_end' => 0,
        'address' => array(
            'start' => '',
            'end' => ''
        ),
        'coordinate' => array(
            'start' => array(
                'lat' => 0,
                'lon' => 0
            ),
            'end' => array(
                'lat' => 0,
                'lon' => 0
            )
        ),
        'totalPrice' => 0,
        'totalPriceWithDiscount' => 0,
        'totalDiscount' => 0,
        'vehicleButtons' => array(),
        'extraChargesList' => array(),
        'extraChargesPrice' => 0,
        'accountDiscount' => 0,
        'scheduledRouteId' => 0,
        'stepsText' => '',
        'summaryText' => '',
        'infoText' => '',
        'isAirport' => 0,
        'isAirport2' => 0,
        'inRadius' => 1,
        'areadNotInRadius' => [],
        'meetingPoint' => '',
        'excludedRouteAllowed' => 0,
        'excludedRouteDescription' => '',
        'errors' => [],
    ];

    $post = $postData[$postType];
    // dd($post);
    $from = (string)$post['address']['start'];
    $to = (string)$post['address']['end'];
    $items = (array)$post['items'];
    $vehicleAmounts = (array)$post['vehicle'];
    $date = trim((string)$post['date']);
    $date = !empty($date) ? Carbon::parse($date .':00') : Carbon::now();
    $passengers = (int)$post['passengers'];
    $passengers = $passengers > 0 ? $passengers : 1;
    $vehicles = [];

    $qVehicles = \App\Models\VehicleType::where('site_id', config('site.site_id'))
      ->where('published', '1')
      ->get();

    foreach ($qVehicles as $k => $v) {
        $vehicles[] = (object)[
            'amount' => 0,
            'available' => 1,
            'charges' => 0,
            'debug' => [],
            'distance' => 0,
            'distance_base_end' => 0,
            'distance_base_start' => 0,
            'distance_routes' => 0,
            'duration' => 0,
            'duration_base_end' => 0,
            'duration_base_start' => 0,
            'duration_routes' => 0,
            'hidden' => 1,
            'id' => $v->id,
            'name' => $v->name,
            'linkType' => "",
            'linkUrl' => "",
            'price_calculated_deposit' => 0,
            'price_calculated_total' => 0,
            'price_fixed_deposit' => 0,
            'price_fixed_total' => 0,
            'routes' => [],
            'display' => 0,
            'total' => 0,
            'totalOriginal' => 0,
            'vehicle' => 0,
            'ticketPrice' => 0,
        ];
    }

    $scheduled = \App\Models\ScheduledRoute::where('relation_type', 'site')
      ->where('relation_id', config('site.site_id'))
      ->where('status', 'active')
      ->whereHas('from', function($query) use ($from) {
          $query->where('address', $from);
      })
      ->whereHas('to', function($query) use ($to) {
          $query->where('address', $to);
      })
      ->whereHas('event', function($query) use ($date) {
          $query->where('start_at', 'LIKE', '%'. $date->toTimeString() .'%');
      })
      ->orderBy('order', 'asc')
      ->first();

    if (!empty($scheduled->id)) {
        if (!$scheduled->event->availability('check', $date->toDateTimeString())) {
            $results['errors'] = ['step1', 'This service is not available. Please adjust date and time.'];
        }

        $params = $scheduled->getParams('raw');
        $price = $params->factor_value;
        $total = 0;
        $sold = 0;
        $excluded = 0;

        $bookings = \App\Models\BookingRoute::where('scheduled_route_id', $scheduled->id)
        ->where('parent_booking_id', '!=', 0)
        ->where('date', $date->toDateTimeString())
        ->scheduledConfirmed()
        ->get();

        foreach($bookings as $k => $v) {
            $sold += $v->passengers;
        }

        $summaryList = [];
        $summaryText = '';

        // Items charges
        if ( $quoteType == "frontend" ) {
            if ( isset($gConfig['booking_items']) ) {

                $oBases = \App\Models\Base::where('relation_type', '=', 'site')
                    ->where('relation_id', '=', config('site.site_id'))
                    ->where('status', 'activated')
                    ->orderBy('ordering', 'ASC');

                $units = ($gConfig['booking_distance_unit'] == 1) ? 'K' : 'M';

                $temp = json_decode($gConfig['booking_items']);

                if ( !empty($temp) ) {
                    foreach($temp as $keyItem => $valueItem) {
                        foreach($items as $keyItem2 => $valueItem2) {
                            if ( (string)$valueItem->id == (string)$valueItem2['id'] ) {

                                // Operating area - start
                                if ($valueItem2['type'] == 'address') {
                                    $oArea = new \stdClass();
                                    $oArea->lat = 0;
                                    $oArea->lng = 0;

                                    $oAddress = $valueItem2['custom'];

                                    if (!empty($oAddress)) {
                                        $geocode = v2_geocode('address', $oAddress);

                                        if ( $geocode->status == 'OK' ) {
                                            foreach($geocode->results as $k => $v) {
                                                $oArea->lat = $v->geometry->location->lat;
                                                $oArea->lng = $v->geometry->location->lng;
                                            }
                                        }
                                    }

                                    $inRadius = 0;
                                    $basesList = [];

                                    foreach($oBases->get() as $kB => $vB) {
                                        if ( !empty($vB->address) && !is_null($vB->lat) && !is_null($vB->lng) ) {
                                            $dFrom = v2_computeDistanceBetween($vB->lat, $vB->lng, $oArea->lat, $oArea->lng, $units);
                                            $dMin = 0;

                                            if ( empty($dMin) || $dMin > $dFrom ) {
                                                $dMin = $dFrom;
                                            }

                                            if ( $dMin <= $vB->radius ) {
                                                $inRadius = 1;
                                            }

                                            $basesList[] = (object)[
                                                'address' => $vB->address,
                                                'dMin' => $dMin,
                                                'radius' => $vB->radius,
                                            ];
                                        }
                                    }

                                    if ( count($basesList) <= 0 ) {
                                        $inRadius = 1;
                                    }

                                    if ( !$inRadius ) {
                                        $results['inRadius'] = 0;
                                        $results['areadNotInRadius'][] = $oAddress;
                                    }

                                    // dd($geocode);
                                    // dd($oArea, $inRadius, $basesList);
                                }
                                // Operating area - end

                                $amount = !empty($valueItem2['amount']) ? (int)$valueItem2['amount'] : 1;
                                $base = (float)$valueItem->value;
                                $totalInner = v2_roundVal($amount * $base);

                                $excluded += $totalInner;
                                $total += $totalInner;

                                $summary = new \stdClass();
                                $summary->type = 'other';
                                $summary->name = $valueItem->name . (!empty($valueItem2['custom']) ? ': '. $valueItem2['custom'] : '');
                                $summary->amount = $amount;
                                $summary->base = $base;
                                $summary->total = $totalInner;
                                $summary->visible = 1;

                                array_unshift($summaryList, $summary);

                                if ( $summary->amount > 1 ) {
                                    $summaryText .= " (x". $summary->amount .")";
                                }
                                $text = $summary->name ." ". config('site.currency_symbol') . $summary->total . config('site.currency_code');
                                if ( !empty($summaryText) ) {
                                    $text .= "\n";
                                }
                                $summaryText = $text . $summaryText;
                            }
                        }
                    }
                }
            }
        }


        if ( $price > 0 ) {
            $total += $price * $passengers;

            $summary = new \stdClass();
            $summary->type = 'journey';
            $summary->name = 'Ticket';
            // $summary->name = trans('booking.summary_types.journey');
            $summary->amount = $passengers;
            $summary->base = $price;
            $summary->total = $price;
            $summary->visible = 1;
            array_unshift($summaryList, $summary);

            if ( $summary->amount > 1 ) {
                $summaryText .= " (x". $summary->amount .")";
            }
            $text = $summary->name ." ". config('site.currency_symbol') . $price . config('site.currency_code');
            if ( !empty($summaryText) ) {
                $text .= "\n";
            }
            $summaryText = $text . $summaryText;
        }

        foreach ($vehicles as $k => $v) {
            $v->display = $total - $excluded;
            $v->total = $total - $excluded;
            $v->totalOriginal = $total;
            $v->vehicle = $total;
            $v->ticketPrice = $price;

            if ($scheduled->vehicle_type_id == $v->id) {
                $v->amount = 1;
                $v->hidden = 0;
            }

            if ($sold >= $scheduled->vehicleType->passengers) {
                $v->linkType = 'scheduled_sold';
                $v->linkUrl = 'Sold out';

                $results['errors'] = ['step2', $v->linkUrl];
            }
            else if ($sold + $passengers - $scheduled->vehicleType->passengers > 0) {
                $v->linkType = 'scheduled_available';
                $v->linkUrl = 'Available '. ($scheduled->vehicleType->passengers - $sold);

                $results['errors'] = ['step2', $v->linkUrl];
            }

            if ($v->ticketPrice <= 0 && $v->linkType != 'scheduled_sold' && config('site.booking_hide_vehicle_without_price') == 0) {
                $v->hidden = 1;
            }

            $vehicles[$k] = $v;
        }

        $results['address']['start'] = $scheduled->from->address;
        $results['coordinate']['start']['lat'] = $scheduled->from->lat;
        $results['coordinate']['start']['lon'] = $scheduled->from->lng;
        $results['address']['end'] = $scheduled->to->address;
        $results['coordinate']['end']['lat'] = $scheduled->to->lat;
        $results['coordinate']['end']['lon'] = $scheduled->to->lng;
        $results['totalPrice'] = $total;
        $results['totalPriceWithDiscount'] = $total;
        $results['scheduledRouteId'] = $scheduled->id;
        $results['vehicleButtons'] = $vehicles;
        $results['extraChargesList'] = $summaryList;
        $results['summaryText'] = SiteHelper::nl2br2($summaryText);
    }
    else {
        // $results['status'] = 'NOT_FOUND';
        $results['errors'] = ['step1', 'This service is not available. Please choose date and time.'];
    }

    return $results;
}


// Default settings
$rBookingRoute = array(
    'status' => '',
    'distance' => 0,
    'duration' => 0,
    'distance_base_start' => 0,
    'duration_base_start' => 0,
    'distance_base_end' => 0,
    'duration_base_end' => 0,
    'address' => array(
        'start' => '',
        'end' => ''
    ),
    'coordinate' => array(
        'start' => array(
            'lat' => 0,
            'lon' => 0
        ),
        'end' => array(
            'lat' => 0,
            'lon' => 0
        )
    ),
    'totalPrice' => 0,
    'totalPriceWithDiscount' => 0,
    'totalDiscount' => 0,
    'vehicleButtons' => array(),
    'extraChargesList' => array(),
    'extraChargesPrice' => 0,
    'accountDiscount' => 0,
    'scheduledRouteId' => 0,
);

$rBooking = array(
    'route1' => $rBookingRoute,
    'route2' => $rBookingRoute,
    'paymentButtons' => array(),
    'totalPrice' => 0,
    'totalPriceWithDiscount' => 0,
    'totalDiscount' => 0,
    'discountId' => 0,
    'discountCode' => '',
    'discountMessage' => '',
    'discountAccountMessage' => '',
    'discountReturnMessage' => '',
    'discountStatus' => 0,
    'manualQuote' => 0,
);


// Scheduled
$serviceId = (int)$booking['serviceId'];

$service = \App\Models\Service::where('relation_type', 'site')
  ->where('relation_id', $siteId)
  ->where('status', 'active')
  ->where('id', $serviceId)
  ->first();

if (!empty($service->id) && $service->type == 'scheduled') {
    $scheduled = 1;
}
else {
    $scheduled = 0;
}

// R1
$booking['route1']['returnTrip'] = 0;
if ($scheduled) {
    $rBooking['route1'] = v2_calculateScheduled($booking, 'route1');

    if (!empty($rBooking['route1']['errors'])) {
        if ($rBooking['route1']['errors'][0] == 'step1') {
            $data['message_r1'][] = $rBooking['route1']['errors'][1];
        }
        else {
            $data['message_r1s2'][] = $rBooking['route1']['errors'][1];
        }
    }
}
else {
    $rBooking['route1'] = v2_calculateRoute($booking, 'route1');
}

if ( empty($rBooking['route1']) || $rBooking['route1']['status'] != 'OK' ) {
    if (!empty($rBooking['route1']['status_postcode_match'])) {
        $gLanguage['API']['ERROR_NO_ROUTE1'] .= '<br>'. trans('frontend.old.API.ERROR_POSTCODE_MATCH');
    }

    $data['message_r1'][] = $gLanguage['API']['ERROR_NO_ROUTE1'];

    if (config('site.booking_manual_quote_enable') == 1 && !$scheduled) {
        $data['manualQuote'] = 1;
    }
}
else {
    if ( in_array($rBooking['route1']['excludedRouteAllowed'], array(2,3)) && !$scheduled ) {
        if ( !empty($rBooking['route1']['excludedRouteDescription']) ) {
            $description = $rBooking['route1']['excludedRouteDescription'];
        }
        else {
            $description = $gLanguage['API']['ERROR_NO_ROUTE1_EXCLUDED_ROUTE'];
        }

        if ( $rBooking['route1']['excludedRouteAllowed'] == 3 ) {
            $data['message_r1s2'][] = $description;
        }
        else {
            $data['message_r1'][] = $description;
        }

        if (config('site.booking_manual_quote_enable') == 1) {
            $data['manualQuote'] = 1;
        }
    }
}

// Operating area
if ( empty($rBooking['route1']['inRadius']) ) {
    switch( config('site.booking_base_action') ) {
        case 'disallow':
            if ($scheduled) {
                $oAreas = '';
                if (!empty($rBooking['route1']['areadNotInRadius'])) {
                    foreach ($rBooking['route1']['areadNotInRadius'] as $oKey => $oValue) {
                        if ($oValue) {
                            $oAreas .= ($oAreas ? ', ' : '') . '"'. $oValue .'"';
                        }
                    }
                }
                if (!empty($oAreas)) { $oAreas = ' '. $oAreas .''; }

                $data['message_r1s3'][] = trans('booking.errors.booking_disallow') . $oAreas;
            }
            else {
                $data['message_r1'][] = trans('booking.errors.booking_disallow');
            }
            $data['manualQuote'] = 0;
        break;
        case 'quote':
            if ($scheduled) {
                $data['message_r1s3'][] = trans('booking.errors.booking_quote');
            }
            else {
                $data['message_r1s2'][] = trans('booking.errors.booking_quote');
            }
            $data['manualQuote'] = 1;
        break;
    }
}

// If the price is set to zero then show manual quote option.\
// Move translation to files


// R2
if ( !empty($booking['routeReturn']) && $booking['routeReturn'] == 2 ) {
    $booking['route2']['returnTrip'] = 1;
    $rBooking['route2'] = v2_calculateRoute($booking, 'route2');

    if ( empty($rBooking['route2']) || $rBooking['route2']['status'] != 'OK' ) {
        if (!empty($rBooking['route2']['status_postcode_match'])) {
            $gLanguage['API']['ERROR_NO_ROUTE2'] .= '<br>'. trans('frontend.old.API.ERROR_POSTCODE_MATCH');
        }

        $data['message_r2'][] = $gLanguage['API']['ERROR_NO_ROUTE2'];

        if (config('site.booking_manual_quote_enable') == 1) {
            $data['manualQuote'] = 1;
        }
    }
    else {
        if ( in_array($rBooking['route2']['excludedRouteAllowed'], array(2,3)) ) {
            if ( !empty($rBooking['route2']['excludedRouteDescription']) ) {
                $description = $rBooking['route2']['excludedRouteDescription'];
            }
            else {
                $description = $gLanguage['API']['ERROR_NO_ROUTE2_EXCLUDED_ROUTE'];
            }

            if ( $rBooking['route2']['excludedRouteAllowed'] == 3 ) {
                $data['message_r2s2'][] = $description;
            }
            else {
                $data['message_r2'][] = $description;
            }

            if (config('site.booking_manual_quote_enable') == 1) {
                $data['manualQuote'] = 1;
            }
        }
    }

    // Operating area
    if ( empty($rBooking['route2']['inRadius']) ) {
        switch( config('site.booking_base_action') ) {
            case 'disallow':
                $data['message_r2'][] = trans('booking.errors.booking_disallow');
                $data['manualQuote'] = 0;
            break;
            case 'quote':
                $data['message_r2s2'][] = trans('booking.errors.booking_quote');
                $data['manualQuote'] = 1;
            break;
        }
    }
}

if (config('site.booking_manual_quote_enable') == 2) {
    $data['manualQuote'] = 1;
}

if (!empty($data['manualQuote'])) {
    $data['manualQuoteMessage'] = trans('booking.errors.booking_quote');
}

if (config('site.booking_hide_vehicle_without_price') == 2) {
    $data['manualQuote'] = 1;
    $data['manualQuoteMessage'] = '';
}

if ($data['manualQuote'] == 1) {
    $data['message_r1'] = [];

    if (!empty($rBooking['route1']['vehicleButtons'])) {
        foreach($rBooking['route1']['vehicleButtons'] as $k => $v) {
            $v->hidden = 0;
            $v->linkType = '';
            $v->linkUrl = '';
            if (config('site.booking_hide_vehicle_without_price') == 2) {
                $v->linkType = 'enquire';
                $v->linkUrl = $gConfig['url_contact'];
            }
            $rBooking['route1']['vehicleButtons'][$k] = $v;
        }
    }

    if (!empty($rBooking['route2']['vehicleButtons'])) {
        foreach($rBooking['route2']['vehicleButtons'] as $k => $v) {
            $v->hidden = 0;
            $v->linkType = '';
            $v->linkUrl = '';
            if (config('site.booking_hide_vehicle_without_price') == 2) {
                $v->linkType = 'enquire';
                $v->linkUrl = $gConfig['url_contact'];
            }
            $rBooking['route2']['vehicleButtons'][$k] = $v;
        }
    }
}


// Total price
$rBooking['route1']['totalPrice'] = v2_roundVal($rBooking['route1']['totalPrice']);
$rBooking['route1']['totalPrice'] = v2_roundTotal($rBooking['route1']['totalPrice'], .50);

$rBooking['route2']['totalPrice'] = v2_roundVal($rBooking['route2']['totalPrice']);
$rBooking['route2']['totalPrice'] = v2_roundTotal($rBooking['route2']['totalPrice'], .50);

$rBooking['totalPrice'] = v2_roundVal($rBooking['route1']['totalPrice'] + $rBooking['route2']['totalPrice']);

// Discount account
if ( $gConfig['booking_account_discount'] > 0 && $userId > 0 ) {
    $rBooking['discountAccountMessage'] = str_replace('{amount}', $gConfig['booking_account_discount'] .'%', $gLanguage['quote_AccountDiscountApplied']);
}

// Discount return
if ( $gConfig['booking_return_discount'] > 0 && $booking['routeReturn'] == 2 ) {
    $rBooking['discountReturnMessage'] = str_replace('{amount}', $gConfig['booking_return_discount'] .'%', $gLanguage['quote_ReturnDiscountApplied']);
}

// Discount code
if ( !empty($booking['discountCode']) ) {
    $sql = "SELECT *
            FROM `{$dbPrefix}discount`
            WHERE `site_id`='". $siteId ."'
            ". ($quoteType == "frontend" ? "AND `published`='1'" : "") ."
            AND `code` LIKE '". (string)trim($booking['discountCode']) . "'
            LIMIT 1";

    $qDiscount = $db->select($sql);
    if ( !empty($qDiscount[0]) ) {
        $qDiscount = $qDiscount[0];
    }

    if ( !empty($qDiscount) ) {
        $ok = 0;

        if ( $qDiscount->used_times >= $qDiscount->allowed_times ) {
            $rBooking['discountMessage'] = $gLanguage['quote_DiscountExpired'];
        }
        elseif ( !(time() >= strtotime($qDiscount->start_date)) && $qDiscount->start_date != null ) {
            $rBooking['discountMessage'] = $gLanguage['quote_DiscountExpired'];
        }
        elseif ( !(time() <= strtotime($qDiscount->end_date)) && $qDiscount->end_date != null ) {
            $rBooking['discountMessage'] = $gLanguage['quote_DiscountExpired'];
        }
        else {
            $ok = 1;
        }

        // if ( $quoteType == "backend" ) {
            // $ok = 1;
        // }

        if ( $ok ) {
            // Get discount balance
            // if ( $qDiscount->type == 1 ) {
            //     $discount_balance = ($rBooking['totalPrice'] / 100) * $qDiscount->value;
            // }
            // else {
            //     $discount_balance = $qDiscount->value;
            // }

            // Calculate R1 discount
//            $rBooking['route1']['totalPriceWithDiscount'] = $rBooking['route1']['totalPrice'] - $discount_balance;
//            if ( $rBooking['route1']['totalPriceWithDiscount'] < 0 ) {
//                $rBooking['route1']['totalPriceWithDiscount'] = 0;
//            }
//            $rBooking['route1']['totalDiscount'] = $rBooking['route1']['totalPrice'] - $rBooking['route1']['totalPriceWithDiscount'];
//            if ( $discount_balance - $rBooking['route1']['totalPrice'] > 0 ) {
//                $discount_balance -= $rBooking['route1']['totalPrice'];
//            }
//            else {
//                $discount_balance = 0;
//            }
//
//            // Calculate R2 discount
//            $rBooking['route2']['totalPriceWithDiscount'] = $rBooking['route2']['totalPrice'] - $discount_balance;
//            if ( $rBooking['route2']['totalPriceWithDiscount'] < 0 ) {
//                $rBooking['route2']['totalPriceWithDiscount'] = 0;
//            }
//            $rBooking['route2']['totalDiscount'] = $rBooking['route2']['totalPrice'] - $rBooking['route2']['totalPriceWithDiscount'];
//            if ( $discount_balance - $rBooking['route2']['totalPrice'] > 0 ) {
//                $discount_balance -= $rBooking['route2']['totalPrice'];
//            }
//            else {
//                $discount_balance = 0;
//            }

            // Total discount
//            $rBooking['route1']['totalDiscount'] = v2_roundVal($rBooking['route1']['totalDiscount']);
//            $rBooking['route1']['totalDiscount'] = v2_roundTotal($rBooking['route1']['totalDiscount'], .50);
//
//            $rBooking['route2']['totalDiscount'] = v2_roundVal($rBooking['route2']['totalDiscount']);
//            $rBooking['route2']['totalDiscount'] = v2_roundTotal($rBooking['route2']['totalDiscount'], .50);
//
//            $rBooking['totalDiscount'] = $rBooking['route1']['totalDiscount'] + $rBooking['route2']['totalDiscount'];

            ///////// start
            $bookingTmp = [];

            foreach ($rBooking as $route=>$item) {
                if (!empty($item) && in_array($route, ['route1','route2'])) {
                    $bookingTmp[$route] = (object)[
                        'items' => json_encode(!empty($item['extraChargesList']) ? $item['extraChargesList'] : []),
                        'total_price' => $item['totalPrice'],
                        'route' => $route == 'route1' ? 1 : 2,
                        'discount' => 0,
                    ];

                    if ($quoteType == 'backend') {
                        $bookingTmp[$route]->items = json_encode($etoPost['booking'][$route]['items'] ?: []);
                        if (!empty($etoPost['booking'][$route]['items'])) {
                            foreach ($etoPost['booking'][$route]['items'] as $item) {
                                $bookingTmp[$route]->total_price += ($item['value'] * $item['amount']);
                            }
                        }
                    }
                }
            }
            $bookingTmp = \App\Helpers\BookingHelper::getDiscountPerRoute($bookingTmp, $qDiscount);

            $rBooking['discountExcludedInfo'] = $bookingTmp['discountExcludedInfo'];
            $rBooking['route1']['totalPriceWithDiscount'] = $bookingTmp['route1']->totalWithoutDiscount;
            $rBooking['route2']['totalPriceWithDiscount'] = $bookingTmp['route2']->totalWithoutDiscount;
            $rBooking['route1']['totalDiscount'] = v2_roundTotal($bookingTmp['route1']->discount, .50);
            $rBooking['route2']['totalDiscount'] = v2_roundTotal($bookingTmp['route2']->discount, .50);
            $rBooking['totalDiscount'] = v2_roundVal($rBooking['route1']['totalDiscount'] + $rBooking['route2']['totalDiscount']);
            // $rBooking['route1']['bookingTmp'] = $bookingTmp['route1'];
            // $rBooking['route2']['bookingTmp'] = $bookingTmp['route2'];
            /////// end

            // Other
            $rBooking['discountId'] = $qDiscount->id;
            $rBooking['discountCode'] = $qDiscount->code;
            $rBooking['discountMessage'] = str_replace('{amount}', config('site.currency_symbol') . $rBooking['totalDiscount'] . config('site.currency_code'), $gLanguage['quote_DiscountApplied']);
            $rBooking['discountStatus'] = 1;
        }
    }
    else {
        $rBooking['discountMessage'] = $gLanguage['quote_DiscountInvalid'];
    }
}

// Total price with discount
$rBooking['route1']['totalPriceWithDiscount'] = v2_roundVal($rBooking['route1']['totalPriceWithDiscount']);
$rBooking['route1']['totalPriceWithDiscount'] = v2_roundTotal($rBooking['route1']['totalPriceWithDiscount'], .50);

$rBooking['route2']['totalPriceWithDiscount'] = v2_roundVal($rBooking['route2']['totalPriceWithDiscount']);
$rBooking['route2']['totalPriceWithDiscount'] = v2_roundTotal($rBooking['route2']['totalPriceWithDiscount'], .50);

$rBooking['totalPriceWithDiscount'] = v2_roundVal($rBooking['route1']['totalPriceWithDiscount'] + $rBooking['route2']['totalPriceWithDiscount']);

// Payment buttons
$paymentButtons = [];

$sql = "SELECT *
        FROM `{$dbPrefix}payment`
        WHERE `site_id`='". $siteId ."' ". ($quoteType == "frontend" ? "AND `published`='1'" : "AND `published`='1'");
$qPayment = $db->select($sql);

if ( !empty($qPayment) ) {
    foreach($qPayment as $k => $v) {

        // Decode params
        if ( !empty($v->params) ) {
            $v->params = json_decode($v->params);
        }

        $deposits = !empty($gConfig['booking_deposit']) ? json_decode($gConfig['booking_deposit']) : [];

        // Calculate R1 deposit
        $depositRoute1 = 0;

        if ( !empty($rBooking['route1']['vehicleButtons']) ) {
            foreach($rBooking['route1']['vehicleButtons'] as $key3 => $value3) {
                if ( $value3->amount > 0 ) {
                    $dV = 0;

                    if ( $value3->price_fixed_deposit ) {
                        $total = $value3->totalOriginal - $value3->price_fixed_total;
                    }
                    else {
                        $total = $value3->totalOriginal;
                    }

                    foreach($deposits as $kD => $vD) {
                        if ( $vD->id == $value3->id ) {
                            if ( $vD->type == 'addition' ) {
                                $dV = $vD->value;
                            }
                            elseif ( $vD->type == 'multiplication' ) {
                                $dV = ($total / 100) * $vD->value;
                            }
                            break;
                        }
                    }

                    if ( $dV <= 0 ) {
                        foreach($deposits as $kD => $vD) {
                            if ( $vD->id == 0 ) {
                                if ( $vD->type == 'addition' ) {
                                    $dV = $vD->value;
                                }
                                elseif ( $vD->type == 'multiplication' ) {
                                    $dV = ($total / 100) * $vD->value;
                                }
                                break;
                            }
                        }
                    }

                    $depositRoute1 += $value3->amount * ($dV + $value3->price_fixed_deposit);
                }
            }
        }
        if ( $depositRoute1 > $rBooking['route1']['totalPriceWithDiscount'] ) {
            $depositRoute1 = $rBooking['route1']['totalPriceWithDiscount'];
        }
        $depositRoute1 = v2_roundVal($depositRoute1);

        // Calculate R2 deposit
        $depositRoute2 = 0;

        if ( !empty($rBooking['route2']['vehicleButtons']) ) {
            foreach($rBooking['route2']['vehicleButtons'] as $key3 => $value3) {
                if ( $value3->amount > 0 ) {
                    $dV = 0;

                    if ( $value3->price_fixed_deposit ) {
                        $total = $value3->totalOriginal - $value3->price_fixed_total;
                    }
                    else {
                        $total = $value3->totalOriginal;
                    }

                    foreach($deposits as $kD => $vD) {
                        if ( $vD->id == $value3->id ) {
                            if ( $vD->type == 'addition' ) {
                                $dV = $vD->value;
                            }
                            elseif ( $vD->type == 'multiplication' ) {
                                $dV = ($total / 100) * $vD->value;
                            }
                            break;
                        }
                    }

                    if ( $dV <= 0 ) {
                        foreach($deposits as $kD => $vD) {
                            if ( $vD->id == 0 ) {
                                if ( $vD->type == 'addition' ) {
                                    $dV = $vD->value;
                                }
                                elseif ( $vD->type == 'multiplication' ) {
                                    $dV = ($total / 100) * $vD->value;
                                }
                                break;
                            }
                        }
                    }

                    $depositRoute2 += $value3->amount * ($dV + $value3->price_fixed_deposit);
                }
            }
        }
        if ( $depositRoute2 > $rBooking['route2']['totalPriceWithDiscount'] ) {
            $depositRoute2 = $rBooking['route2']['totalPriceWithDiscount'];
        }
        $depositRoute2 = v2_roundVal($depositRoute2);

        // Deposit
        $total = $rBooking['totalPriceWithDiscount'];
        $deposit = $depositRoute1 + $depositRoute2;
        $deposit = v2_roundTotal($deposit, .50);

        if ( $scheduled || $deposit >= $total || in_array($v->method, array('cash', 'account', 'bacs', 'none')) || empty($v->params->deposit) ) {
            $deposit = 0;
        }

        // Payment charge
        if ( $v->factor_type == 1 ) {
            $totalCharge = ($total / 100) * $v->price;
            $depositCharge = ($deposit / 100) * $v->price;
        }
        else {
            $totalCharge = $v->price;
            $depositCharge = $v->price;
        }

        $totalCharge = v2_roundVal($totalCharge);
        // $totalCharge = v2_roundTotal($totalCharge, .50);

        $depositCharge = v2_roundVal($depositCharge);
        // $depositCharge = v2_roundTotal($depositCharge, .50);

        // Hide
        if ( !empty($service->id) && !empty($v->service_ids) &&
            !in_array($service->id, json_decode($v->service_ids)) ) {
            $paymentHidden = 1;
        }
        else {
            $paymentHidden = 0;
        }

        // Add button
        $paymentButtons[] = [
            'id' => (int)$v->id,
            'name' => (string)$v->name,
            'totalOriginal' => $rBooking['totalPrice'],
            'total' => v2_roundVal($total),
            'totalWithCharge' => v2_roundVal($total + $totalCharge),
            'totalCharge' => $totalCharge,
            'depositSelected' => $gConfig['booking_deposit_selected'],
            'deposit' => v2_roundVal($deposit),
            'depositWithCharge' => v2_roundVal($deposit + $depositCharge),
            'depositCharge' => $depositCharge,
            'depositRoute1' => v2_roundVal($depositRoute1),
            'depositRoute2' => v2_roundVal($depositRoute2),
            'hidden' => $paymentHidden,
        ];
    }
}

$rBooking['paymentButtons'] = $paymentButtons;

$data['booking'] = $rBooking;


if ( $quoteType == "backend" ) {
  $messages = [];
  if (!empty($data['message_r1'])) {
    $messages = array_merge($messages, $data['message_r1']);
  }

  if (!empty($data['message_r2'])) {
    $messages = array_merge($messages, $data['message_r2']);
  }

  if (!empty($data['message_r3'])) {
    $messages = array_merge($messages, $data['message_r3']);
  }
  $data['message'] = $messages;
}

if (!empty($data['CONNECTION_ERROR'])) {
    $duplicates = [];
    foreach($data['CONNECTION_ERROR'] as $kE => $vE) {
        $hash = md5($vE);
        if (!in_array($hash, $duplicates)) {
            $data['message'][] = $vE;
        }
        $duplicates[] = $hash;
    }
		unset($data['CONNECTION_ERROR']);
}

$data['success'] = true;
//dd($data);
// \Log::debug([$data]);
