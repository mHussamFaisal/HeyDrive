<?php

switch($action)
{
  case 'init':

    // Category list for filter
    $categoryFilterList = array();

    $sql = "SELECT `id`, `name`
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "'
            ORDER BY `name` ASC";

    $resultsCategory = $db->select($sql);

    if (!empty($resultsCategory))
    {
      foreach($resultsCategory as $key => $value)
      {
        $categoryFilterList[] = array(
          'value' => $value->id,
          'text' => $value->name
        );
      }
    }

    $data['categoryList'] = $categoryFilterList;
    $data['success'] = true;

  break;
  case 'list':
            if (!auth()->user()->hasPermission('admin.locations.index')) {
                return redirect_no_permission();
            }

    // Convert start
    $data['list'] = [];

    $searchData = $etoPost['search']['value'];
    parse_str($searchData, $output);
    // $data['outputData'] = $output;

    $etoPost['searchFilterCategory'] = json_encode(isset($output['filter-category']) ? $output['filter-category'] : []);
    $etoPost['searchText'] = isset($output['filter-keywords']) ? $output['filter-keywords'] : '';

    $sortData = array();
    if ( isset($etoPost['order']) ) {
      foreach($etoPost['order'] as $key => $value) {
        $index = (int)$value['column'];
        $new = new \stdClass();
        $new->property = $etoPost['columns'][$index]['data'];
        $new->direction = ($value['dir'] == 'asc') ? 'ASC' : 'DESC';
        $sortData[] = $new;
      }
    }
    $etoPost['sort'] = json_encode($sortData);
    // Convert end


    // Sort and limit
    $sort    = json_decode($etoPost['sort']);
    $start   = (int) $etoPost['start'];
    $limit   = (int) $etoPost['length'];
    // $page    = (int) $etoPost['page'];
    $sqlSort = '';

    if (!empty($sort))
    {
      foreach($sort as $key => $value)
      {
        $property = '';

        switch((string) $value->property)
        {
          case 'id':
            $property = '`id`';
          break;
          case 'category_id':
            $property = '`a`.`category_id`';
          break;
          case 'name':
            $property = '`a`.`name`';
          break;
          case 'address':
            $property = '`a`.`address`';
          break;
          case 'full_address':
            $property = '`a`.`full_address`';
          break;
          case 'search_keywords':
            $property = '`a`.`search_keywords`';
          break;
          case 'lat':
            $property = '`a`.`lat`';
          break;
          case 'lon':
            $property = '`a`.`lon`';
          break;
          case 'ordering':
            $property = '`a`.`ordering`';
          break;
          case 'published':
            $property = '`a`.`published`';
          break;
        }

        if (!empty($property))
        {
          $sqlSort .= $property . ' ' . $value->direction . ', ';
        }
      }
    }

    // Search text
    $searchText = (string) $etoPost['searchText'];
    $searchText = \App\Helpers\SiteHelper::makeStrSafe($searchText);
    $sqlSearch  = '';

    if (!empty($searchText))
    {
      $sqlSearch .= " AND (`a`.`id` LIKE '%" . $searchText . "%'".
                    " OR `a`.`name` LIKE '%" . $searchText . "%'".
                    " OR `a`.`address` LIKE '%" . $searchText . "%'".
                    " OR `a`.`full_address` LIKE '%" . $searchText . "%'".
                    " OR `a`.`search_keywords` LIKE '%" . $searchText . "%'".
                    " )";
    }


    // Search filter category
    $searchFilterCategory    = json_decode($etoPost['searchFilterCategory']);
    $sqlSearchFilterCategory = '';

    if (!empty($searchFilterCategory))
    {
      foreach($searchFilterCategory as $key => $value)
      {
        if (!empty($sqlSearchFilterCategory))
        {
          $sqlSearchFilterCategory .= ' OR ';
        }

        if (!empty($value))
        {
          $sqlSearchFilterCategory .= '`a`.`category_id` LIKE \'' . (string) $value . '\'';
        }
      }

      if (!empty($sqlSearchFilterCategory))
      {
        $sqlSearchFilterCategory = ' AND (' . $sqlSearchFilterCategory . ') ';
      }
    }

    $sqlSearch .= " " . $sqlSearchFilterCategory;

    // $data['sqlSearchFilter'] = $sqlSearchFilter;


    // Total
    $sql = "SELECT `a`.`id`
            FROM `{$dbPrefix}location` AS `a`
            LEFT JOIN `{$dbPrefix}category` AS `b`
            ON `a`.`category_id`=`b`.`id`
            WHERE `a`.`site_id`='" . $siteId . "' ";

    $resultsLocationTotal = count($db->select($sql));
    $data['recordsTotal'] = $resultsLocationTotal;


    // Filtered
    $sql = "SELECT `a`.`id`
            FROM `{$dbPrefix}location` AS `a`
            LEFT JOIN `{$dbPrefix}category` AS `b`
            ON `a`.`category_id`=`b`.`id`
            WHERE `a`.`site_id`='" . $siteId . "' " . $sqlSearch;

    $resultsLocationTotal = count($db->select($sql));
    $data['recordsFiltered'] = $resultsLocationTotal;


    // List
    $sql = "SELECT `a`.*, `b`.`name` AS `category_name`
            FROM `{$dbPrefix}location` AS `a`
            LEFT JOIN `{$dbPrefix}category` AS `b`
            ON `a`.`category_id`=`b`.`id`
            WHERE `a`.`site_id`='" . $siteId . "' " . $sqlSearch . "
            ORDER BY " . $sqlSort . " `a`.`category_id` ASC
            LIMIT {$start},{$limit}";

    $resultsLocation = $db->select($sql);

    if ( !empty($resultsLocation) )
    {
      $location = array();

      foreach($resultsLocation as $key => $value)
      {
        $columns = array();
        $columns['id'] = $value->id;
        $columns['category_id'] = $value->category_name;
        $columns['name'] = $value->name;
        $columns['address'] = $value->address;
        $columns['full_address'] = $value->full_address;
        $columns['search_keywords'] = $value->search_keywords;
        $columns['lat'] = $value->lat;
        $columns['lon'] = $value->lon;
        $columns['ordering'] = $value->ordering;
        $columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

        $location[] = $columns;
      }

      $data['list'] = $location;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = 'No location were found!';
      $data['success'] = true;
    }

  break;
  case 'read':
            if (!auth()->user()->hasPermission('admin.locations.show')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT *
            FROM `{$dbPrefix}location`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsLocation = $db->select($sql);
    if (!empty($resultsLocation[0])) {
      $resultsLocation = $resultsLocation[0];
    }

    if (!empty($resultsLocation))
    {
      $record = array(
        'id' => $resultsLocation->id,
        'site_id' => $resultsLocation->site_id,
        'category_id' => $resultsLocation->category_id,
        'name' => $resultsLocation->name,
        'address' => $resultsLocation->address,
        'full_address' => $resultsLocation->full_address,
        'search_keywords' => $resultsLocation->search_keywords,
        'lat' => $resultsLocation->lat,
        'lon' => $resultsLocation->lon,
        'ordering' => $resultsLocation->ordering,
        'published' => $resultsLocation->published
      );

      $data['record'] = $record;
      $data['success'] = true;
    }
    else
    {
      $data['success'] = false;
      $data['message'][] = $gLanguage['API']['ERROR_NO_Location'];
    }

  break;
  case 'update':
            if (!auth()->user()->hasPermission('admin.locations.edit')) {
                return redirect_no_permission();
            }

    function getGeocode($address) {
      global $gConfig;

      $language = explode('-', $gConfig['language']);

      $params = array(
        'key' => config('site.google_maps_geocoding_api_key'),
        'address' => trim($address),
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

      // dd($response);

      return $response;
    }

    $id = (int)$etoPost['id'];
    $site_id = (int)$etoPost['site_id'];
    $category_id = (string)$etoPost['category_id'];
    $name = (string)$etoPost['name'];
    $address = (string)$etoPost['address'];
    $full_address = (string)$etoPost['full_address'];
    $search_keywords = (string)$etoPost['search_keywords'];
    $lat = (float)$etoPost['lat'];
    $lon = (float)$etoPost['lon'];
    $ordering = (int)$etoPost['ordering'];
    $published = ( (string)$etoPost['published'] == 1 ) ? 1 : 0;

    $sql = "SELECT `id`
            FROM `{$dbPrefix}location`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsLocation = $db->select($sql);
    if (!empty($resultsLocation[0])) {
      $resultsLocation = $resultsLocation[0];
    }

    $lat = 0;
    $lon = 0;

    $geocode = getGeocode($address);
    $geocodePostcodeStart = '';

    if ( $geocode->status == 'OK' )
    {
      foreach($geocode->results[0]->address_components as $key1 => $value1){
        foreach($value1->types as $key2 => $value2){
          if ($value2 == 'postal_code') {
            $geocodePostcodeStart = $value1->long_name;
            break;
          }
        }
      }

      $location = $geocode->results[0]->geometry->location;
      $lat = $location->lat;
      $lon = $location->lng;
    }

    $row = new \stdClass();
    $row->id = null;
    $row->site_id = ($site_id) ? $site_id : (int)$siteId;
    $row->category_id = $category_id;
    $row->name = trim($name);
    $row->address = trim($address);
    $row->full_address = trim($full_address);
    $row->search_keywords = trim($search_keywords);
    $row->lat = $lat;
    $row->lon = $lon;
    $row->ordering = $ordering;
    $row->published = $published;

    if ( !empty($resultsLocation) ) // Update
    {
      $row->id = $resultsLocation->id;

      $results = \DB::table('location')->where('id', $row->id)->update((array)$row);
      $results = $row->id;
    }
    else // Insert
    {
      $results = \DB::table('location')->insertGetId((array)$row);
      $row->id = $results;
    }

    $data['success'] = true;

  break;
  case 'destroy':
            if (!auth()->user()->hasPermission('admin.locations.destroy')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT `id`
            FROM `{$dbPrefix}location`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsLocation = $db->select($sql);
    if (!empty($resultsLocation[0])) {
      $resultsLocation = $resultsLocation[0];
    }

    if (!empty($resultsLocation))
    {
      // Remove
      $sql = "DELETE FROM `{$dbPrefix}location` WHERE `id`='" . $resultsLocation->id . "' LIMIT 1";
      $results = $db->delete($sql);
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_Location'];
    }

  break;
}
