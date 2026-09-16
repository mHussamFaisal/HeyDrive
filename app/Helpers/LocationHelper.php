<?php

namespace App\Helpers;

class LocationHelper
{
    public static function geocode($search = null)
    {
        $params = [
            'key' => config('site.google_maps_geocoding_api_key'),
        ];

        if (!empty(config('site.google_language'))) {
            $params['language'] = strtolower(config('site.google_language'));
        }
        else {
            $language = explode('-', config('site.language'));
            $params['language'] = ($language[0]) ? strtolower($language[0]) : 'en';
        }

        if (!empty($search['place_id'])) {
            $params['place_id'] = trim($search['place_id']);
        }
        elseif (!empty($search['lat']) && !empty($search['lng'])) {
            $params['latlng'] = $search['lat'] .','. $search['lng'];
        }
        else {
            if (!empty(config('site.google_region_code'))) {
                $params['region'] = strtolower(config('site.google_region_code'));
            }

            if (!empty(config('site.google_country_code'))) {
                $list = [];
                $codes = explode(',', config('site.google_country_code'));
                foreach ($codes as $kC => $vC) {
                    $vC = strtolower(trim($vC));
                    if (!empty($vC)) {
                        $list[] = 'country:'. $vC;
                    }
                }
                if (!empty($list)) {
                    $params['components'] = implode('|', $list);
                }
            }

            $address = !empty($search['address']) ? $search['address'] : '';

            if (in_array($address, ['Gatwick North, RH6 0PJ'])) {
                $address = 'RH6 0PJ, UK';
            }
            elseif (in_array($address, ['Heathrow Terminal 1, TW6 1AP', 'Terminal 1, Hounslow, TW6 1AP'])) {
                $address = 'TW6 1AP, UK';
            }
            elseif (in_array($address, ['Heathrow Terminal 2,	TW6 1EW', 'Terminal 2, Hounslow, TW6 1EW'])) {
                $address = 'TW6 1EW, UK';
            }
            elseif (in_array($address, ['Heathrow Terminal 3, TW6 1QG', 'Terminal 3, Hounslow, TW6 1QG'])) {
                $address = 'TW6 1QG, UK';
            }
            elseif (in_array($address, ['Heathrow Terminal 4, TW6 3XA', 'Terminal 4, Hounslow, TW6 3XA'])) {
                $address = 'TW6 3XA, UK';
            }
            elseif (in_array($address, ['Heathrow Terminal 5, TW6 2GA', 'Terminal 5, Hounslow, TW6 2GA'])) {
                $address = 'TW6 2GA, UK';
            }

            $params['address'] = trim($address);
        }

        $hash = 'g_geocode_'. md5(json_encode($params));
        $cache_expiry_time = config('site.google_cache_expiry_time') ? config('site.google_cache_expiry_time') : config('site.google_cache_runtime');
        $response = null;

        if ($cache_expiry_time && cache($hash)) {
            $response = cache($hash);
        }

        if (empty($response) && (!empty($params['place_id']) || !empty($params['latlng']) || !empty($params['address']))) {
            $client = new \GuzzleHttp\Client();
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
}
