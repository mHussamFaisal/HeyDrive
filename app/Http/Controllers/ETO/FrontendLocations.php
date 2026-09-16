<?php

$showGoogleLogo = 0;
$locations = array();
$start = 0;
$limit = 100;
$search = (string)$etoPost['search'];
$forceSelectionType = (string)$etoPost['forceSelectionType'];
$search = \App\Helpers\SiteHelper::makeStrSafe($search);

function getIcon($type, $title = '', $color = '', $iconCls = '') {
	$cls = 'fa fa-map-marker';

	switch($type) {
		case 'airport':
			$cls = 'fa fa-plane';
		break;
		case 'seaport':
			$cls = 'fa fa-ship';
		break;
		case 'hotel':
			$cls = 'fa fa-h-square';
		break;
		case 'station':
			$cls = 'fa fa-subway';
		break;
		case 'address':
			$cls = 'fa fa-map-marker';
		break;
		case 'postcode':
			$cls = 'fa fa-map-marker';
		break;
		case 'more':
			$cls = 'fa fa-plus-circle';
			// $cls = 'fa fa-plus-square';
		break;
	}

	if ( $iconCls ) {
			if ( preg_match('/^fa-/', $iconCls) ) {
					$iconCls = 'fa '. $iconCls;
			}
			else {
					$iconCls = 'glyphicon '. $iconCls;
			}
			$cls = $iconCls;
	}

	$icon = '<i class="'. $cls .'" title="'. $title .'"></i>';

	return $icon;
}

if ( !empty($search) ) {
	$sqlSearch = "AND (
		`a`.`name` LIKE '%". $search ."%'
			OR
		`a`.`address` LIKE '%". $search ."%'
			OR
		`a`.`full_address` LIKE '%". $search ."%'
			OR
		`a`.`search_keywords` LIKE '%". $search ."%'
	)";
}
else {
	if ( !empty($forceSelectionType) ) {
		if ( $forceSelectionType == 'airport' ) {
			$sqlSearch = "AND `b`.`type`='airport'";
		}
		else {
			$sqlSearch = "AND 0";
		}
	}
	else {
		$sqlSearch = "AND `b`.`featured`='1'";
	}
}

$sql = "SELECT `a`.`id`, `a`.`name`, `a`.`address`, `a`.`full_address`,
		`b`.`id` AS `cat_id`,
		`b`.`type` AS `cat_type`,
		`b`.`name` AS `cat_name`,
		`b`.`color` AS `cat_color`,
		`b`.`icon` AS `cat_icon`,
		`b`.`featured` AS `cat_featured`
		FROM `{$dbPrefix}location` AS `a`
		LEFT JOIN `{$dbPrefix}category` AS `b`
		ON `a`.`category_id`=`b`.`id`
		WHERE `a`.`site_id`='" . $siteId . "'
		AND `a`.`published`='1'
		AND `b`.`published`='1'
		". $sqlSearch ."
		ORDER BY `b`.`ordering` ASC, `a`.`ordering` ASC, `a`.`name` ASC
		LIMIT {$start},{$limit}";

$query = $db->select($sql);
foreach($query as $key => $value)
{
	if ( !empty($value->full_address) ) {
		$address = $value->full_address;
	}
	elseif ( !empty($value->address) ) {
		$address = $value->address;
	}
	else {
		$address = $value->name; //  .', '. $value->address
	}

	if ( !empty($value->name) ) {
		$name = $value->name;
	}
	else {
		$name = $address;
	}

	$location = array();
	$location['id'] = $value->id;
	$location['name'] = $name;
	$location['address'] = str_replace(', , ', '', $address);
	$location['cat_id'] = $value->cat_id;
	$location['cat_icon'] = getIcon($value->cat_type, $value->cat_name, $value->cat_color, $value->cat_icon);
	$location['cat_type'] = $value->cat_type;
	$location['cat_featured'] = $value->cat_featured;
	// $location['cat_name'] = $value->cat_name;
	// $location['cat_color'] = $value->cat_color;
	$locations[] = $location;
}

// Get address category settings
$sql = "SELECT `id`, `name`, `type`, `color`, `icon`
		FROM `{$dbPrefix}category`
		WHERE `site_id`='". $siteId ."'
		AND `type`='address'
		AND `published`='1'
		LIMIT 1";

$query = $db->select($sql);
if (!empty($query[0])) {
	$query = $query[0];
}

if ( !empty($query) ) {
		$addressCategory = array(
			'cat_id' => $query->id,
			'cat_type' => $query->type,
			'cat_name' => $query->name,
			'cat_color' => $query->color,
			'cat_icon' => $query->icon
		);

		// Customer home address
		//  && !empty($etoPost['searchType']) && $etoPost['searchType'] == 'from'
		if ($etoAPI->userId) {
				$user = $etoAPI->getUser();
				$addressParts = [
						trim($user->address),
						trim($user->city),
						trim($user->postcode),
						trim($user->state),
						trim($user->country),
				];
				foreach ($addressParts as $k => $v) {
						if (empty($v)) { unset($addressParts[$k]); }
				}
				$homeAddrress = trim(implode(', ', $addressParts));
				if ($homeAddrress) {
						array_unshift($locations, [
								'id' => 0,
								'name' => $homeAddrress,
								'address' => $homeAddrress,
								'cat_id' => $addressCategory['cat_id'],
								'cat_icon' => getIcon($addressCategory['cat_type'], $addressCategory['cat_name'], $addressCategory['cat_color'], 'fa-home'),
								'cat_type' => $value->cat_type,
						]);
				}
		}


	// PCA Predict - http://www.pcapredict.com/support/webservice/capture/interactive/find/1/
	if ( strlen(trim($search)) >= config('site.booking_location_search_min') && config('services.pcapredict.enabled') && !empty(config('services.pcapredict.key')) )
	{
		class Capture_Interactive_Find_v1_00
		{
			private $Key; //The key to use to authenticate to the service.
			private $Text; //The search term to find.
			private $Container; //A container for the search. This should be an Id previously returned from this service.
			private $Origin; //A starting location for the search. This can be the name or ISO 2 or 3 character code of a country, or Latitude and Longitude, to start the search.
			private $Countries; //A comma separated list of ISO 2 or 3 character country codes to limit the search within e.g. US,CA,MX
			private $Limit; //The maximum number of results to return.
			private $Language; //The preferred language for results. This should be a 2 or 4 character language code e.g. (en, fr, en-gb, en-us etc).
			private $Data; //Holds the results of the query

			function __construct($Key, $Text, $Container, $Origin, $Countries, $Limit, $Language) {
				$this->Key = $Key;
				$this->Text = $Text;
				$this->Container = $Container;
				$this->Origin = $Origin;
				$this->Countries = $Countries;
				$this->Limit = $Limit;
				$this->Language = $Language;
			}

			function MakeRequest() {
				$url = "https://services.postcodeanywhere.co.uk/Capture/Interactive/Find/v1.00/xmla.ws?";
				$url .= "&Key=". $this->Key;
				$url .= "&Text=". $this->Text;
				$url .= "&Container=". $this->Container;
				$url .= "&Origin=". $this->Origin;
				$url .= "&Countries=". $this->Countries;
				$url .= "&Limit=". $this->Limit;
				$url .= "&Language=". $this->Language;

				$url = urlencode($url);

				//Make the request to Postcode Anywhere and parse the XML returned
				$file = simplexml_load_file($url);

				// Check for an error, if there is one then throw an exception
				// if ($file->Columns->Column->attributes()->Name == "Error") {
				// 	throw new \Exception("[ID] " . $file->Rows->Row->attributes()->Error . " [DESCRIPTION] " . $file->Rows->Row->attributes()->Description . " [CAUSE] " . $file->Rows->Row->attributes()->Cause . " [RESOLUTION] " . $file->Rows->Row->attributes()->Resolution);
				// }

				if ( !empty($file->Rows) ) {
					foreach ($file->Rows->Row as $item) {
						$this->Data[] = array('Id'=>$item->attributes()->Id,'Type'=>$item->attributes()->Type,'Text'=>$item->attributes()->Text,'Highlight'=>$item->attributes()->Highlight,'Description'=>$item->attributes()->Description);
					}
				}
			}

			function HasData() {
				if ( !empty($this->Data) ) {
					return $this->Data;
				}
				return false;
			}
		}

		// Query
		$searchContainer = \App\Helpers\SiteHelper::makeStrSafe((string)$etoPost['pacontainer']);
		$language = explode('-', $gConfig['language']);
		$paKey = config('services.pcapredict.key');
		$paText = trim($search);
		$paContainer = !empty($searchContainer) ? $searchContainer : '';
		$paOrigin = '';
		$paCountries = !empty($language[1]) ? strtoupper($language[1]) : '';
		$paLimit = 100;
		$paLanguage = strtolower($gConfig['language']);

		$pa = new Capture_Interactive_Find_v1_00 ($paKey, $paText, $paContainer, $paOrigin, $paCountries , $paLimit, $paLanguage);
		$pa->MakeRequest();

		if ( $pa->HasData() ) {
			$response = $pa->HasData();
			foreach ($response as $item) {
				$location = array();

				$lname = str_replace(', , ', '', (string)$item["Text"] .' '. (string)$item["Description"]);
				$licon = getIcon($addressCategory['cat_type'], $addressCategory['cat_name'], $addressCategory['cat_color'], $addressCategory['cat_icon']);

				if ( !in_array($item["Type"], array('Address', 'Company')) ) {
					$location['pa_container'] = (string)$item["Id"];
					$location['pa_text'] = (string)$item["Text"];
					$licon = getIcon('more', 'More', '', '');
				}

				// $location['pa_type'] = (string)$item["Type"];

				$location['id'] = 0;
				$location['name'] = $lname;
				$location['cat_id'] = $addressCategory['cat_id'];
				$location['cat_icon'] = $licon;
				$location['cat_type'] = $addressCategory['cat_type'];
				// $location['cat_name'] = $addressCategory['cat_name'] .'';
				// $location['cat_color'] = $addressCategory['cat_color'];
				// $location['lat'] = $response->Latitude;
				// $location['lon'] = $response->Longitude;
				$locations[] = $location;
			}

			// Hide google places if postcode anywhere is on
			$gConfig['autocomplete_google_places'] = 0;
		}
	}

	// Google Places
	// https://developers.google.com/places/webservice/autocomplete
	// https://console.developers.google.com/project
	if ( strlen(trim($search)) >= config('site.booking_location_search_min') && $gConfig['autocomplete_google_places'] == 1 )
	{
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
        $client = new \GuzzleHttp\Client();
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
				$data['CONNECTION_ERROR'][] = $response->error_message ? $response->error_message : $response->status;
				// \Log::debug(['response' => $response, 'params' => $params]);
		}

    if (!empty(config('app.debug'))) {
        $data['!debug_predictions'] = ['response' => $response, 'params' => $params];
        // \Log::debug(['response' => $response, 'params' => $params]);
    }

		if ( !empty($response) ) {
			if ( !empty($response->predictions) && $response->status == 'OK' ) {
				foreach($response->predictions as $key => $value) {

					// Skip places
					$locationsSkipPlaceId = explode("\n", $gConfig['locations_skip_place_id']);
					if ( !empty($locationsSkipPlaceId) ) {
						foreach($locationsSkipPlaceId as $k => $v) {
							if ( trim($value->place_id) == trim($v) ) {
								continue 2;
							}
						}
					}

					if ( in_array('transit_station', (array)$value->types) ) {
							$cat_icon = getIcon('station', $addressCategory['cat_name'], $addressCategory['cat_color'], null);
					}
					else {
							$cat_icon = getIcon($addressCategory['cat_type'], $addressCategory['cat_name'], $addressCategory['cat_color'], $addressCategory['cat_icon']);
					}

					$location = array();
					$location['id'] = 0;
					$location['name'] = $value->description;
					$location['cat_id'] = $addressCategory['cat_id'];
					// $location['cat_icon'] = getIcon($addressCategory['cat_type'], $addressCategory['cat_name'], $addressCategory['cat_color'], $addressCategory['cat_icon']);
					$location['cat_icon'] = $cat_icon;
					$location['cat_type'] = $addressCategory['cat_type'];
					// $location['cat_name'] = $addressCategory['cat_name'] .'';
					// $location['cat_color'] = $addressCategory['cat_color'];
					// $location['lat'] = $value->geometry->location->lat;
					// $location['lon'] = $value->geometry->location->lng;
					$location['place_id'] = $value->place_id;
					$locations[] = $location;
				}

				$showGoogleLogo = 1;
			}
		}
	}

	// getAddress - https://getaddress.io
	if ( strlen(trim($search)) >= config('site.booking_location_search_min') && 0 )
	{
		$postcodeRegex = "/((GIR 0AA)|((([A-PR-UWYZ][0-9][0-9]?)|(([A-PR-UWYZ][A-HK-Y][0-9][0-9]?)|(([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY])))) [0-9][ABD-HJLNP-UW-Z]{2}))/i";

		if (preg_match($postcodeRegex, strtoupper(trim($search)), $matches))
		{
			$postcode = str_replace(' ', '', $matches[0]);

			$params = array(
				'api-key' => 'IKS_FdOymECAU-q0uLdAvw1028'
			);

			$paramsString = http_build_query($params);
			$url = 'https://api.getaddress.io/v2/uk/'. urlencode(trim($postcode)) .'?'. ltrim($paramsString, '&');

			if ( function_exists('curl_init') ) {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
				$response = curl_exec($curl);
				curl_close($curl);
			}
			else {
				$url .= '&'.$paramsString;
				$response = file_get_contents($url);
			}

			if ( !empty($response) ) {
				$response = json_decode($response);
				//$data['get address'] = $response;

				if ( !empty($response->Addresses) ) {
					foreach($response->Addresses as $key => $value) {
						$location = array();
						$location['id'] = 0;
						$location['name'] = str_replace(', , ', '', $value);
						$location['cat_id'] = $addressCategory['cat_id'];
						$location['cat_icon'] = getIcon($addressCategory['cat_type'], $addressCategory['cat_name'], $addressCategory['cat_color'], $addressCategory['cat_icon']);
						$location['cat_type'] = $addressCategory['cat_type'];
						// $location['cat_name'] = $addressCategory['cat_name'] .'';
						// $location['cat_color'] = $addressCategory['cat_color'];
						// $location['lat'] = $response->Latitude;
						// $location['lon'] = $response->Longitude;
						$locations[] = $location;
					}
				}
			}
		}
	}

	// Open Addresses - https://alpha.openaddressesuk.org/
	if ( strlen(trim($search)) >= config('site.booking_location_search_min') && 0 )
	{
		$postcodeRegex = "/((GIR 0AA)|((([A-PR-UWYZ][0-9][0-9]?)|(([A-PR-UWYZ][A-HK-Y][0-9][0-9]?)|(([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY])))) [0-9][ABD-HJLNP-UW-Z]{2}))/i";

		if (preg_match($postcodeRegex, strtoupper(trim($search)), $matches))
		{
			$postcode = str_replace(' ', '', $matches[0]);

			$params = array(
				'postcode' => trim($postcode)
			);
			$paramsString = http_build_query($params);
			$url = 'https://alpha.openaddressesuk.org/addresses.json?'. ltrim($paramsString, '&');

			if ( function_exists('curl_init') ) {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
				$response = curl_exec($curl);
				curl_close($curl);
			}
			else {
				$url .= '&'.$paramsString;
				$response = file_get_contents($url);
			}

			if ( !empty($response) ) {
				$response = json_decode($response);
				//$data['get address'] = $response;

				if ( !empty($response->addresses) ) {
					foreach($response->addresses as $key => $value) {
						$name = '';
						if ( !empty($value->sao) ) {
							$name .= $value->sao;
						}
						if ( !empty($value->pao) ) {
							if ( !empty($name) ) { $name .= ', '; }
							$name .= $value->pao;
						}
						if ( !empty($value->street->name->en) ) {
							if ( !empty($name) ) { $name .= ', '; }
							$name .= $value->street->name->en[0];
						}
						if ( !empty($value->town->name->en) ) {
							if ( !empty($name) ) { $name .= ', '; }
							$name .= $value->town->name->en[0];
						}

						$name = ucwords(strtolower($name));

						if ( !empty($value->postcode->name) ) {
							if ( !empty($name) ) { $name .= ', '; }
							$name .= $value->postcode->name;
						}

						$latitude = 0;
						$longitude = 0;

						if ( !empty($value->postcode->geo) ) {
							$latitude = $value->postcode->geo->latitude;
							$longitude = $value->postcode->geo->longitude;
						}

						$location = array();
						$location['id'] = 0;
						$location['name'] = trim($name);
						$location['cat_id'] = $addressCategory['cat_id'];
						$location['cat_icon'] = getIcon($addressCategory['cat_type'], $addressCategory['cat_name'], $addressCategory['cat_color'], $addressCategory['cat_icon']);
						$location['cat_type'] = $addressCategory['cat_type'];
						// $location['cat_name'] = $addressCategory['cat_name'] .'';
						// $location['cat_color'] = $addressCategory['cat_color'];
						// $location['lat'] = $latitude;
						// $location['lon'] = $longitude;
						$locations[] = $location;
					}
				}
			}
		}
	}

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

$data['showGoogleLogo'] = $showGoogleLogo;
$data['locations'] = $locations;
$data['success'] = true;
