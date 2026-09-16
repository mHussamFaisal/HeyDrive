<?php
use App\Helpers\SiteHelper;

switch($action)
{
  case 'init':

    $postcodeList = array(
      array(
        'value' => 'ALL',
        'text' =>  'ALL'
      )
    );

    // Categories
    $sql = "SELECT `id`
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "'";

    $resultsCategory = $db->select($sql);

    if (!empty($resultsCategory))
    {
      // Locations
      $categoriesFiltered = array();
      foreach($resultsCategory as $key => $value)
      {
        $categoriesFiltered[] = $value->id;
      }

      if ( !empty($categoriesFiltered) )
      {
        $categoriesListFiltered = implode(",", $categoriesFiltered);

        // Locations
        $sql = "SELECT `id`, `name`, `address`
                FROM `{$dbPrefix}location`
                WHERE `site_id`='" . $siteId . "'
                AND `category_id` IN (" . $categoriesListFiltered . ")
                ORDER BY `category_id` ASC, `ordering` ASC, `name` ASC";

        $resultsLocation = $db->select($sql);

        if (!empty($resultsLocation))
        {
          foreach($resultsLocation as $key => $value)
          {
            if ( empty($value->name) && empty($value->address) ) {
              $text = 'Unknown';
            }
            else if ( $value->name == $value->address ) {
              $text = $value->name;
            } else {
              $text = empty($value->name) ? $value->address : $value->name .' ('. $value->address .')';
            }

            $postcodeList[] = array(
              'value' => $value->address,
              'text' =>  $text
            );
          }
        }
      }
    }

    // Add custom tags to list
    $sql = "SELECT `start_postcode`, `end_postcode`
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='". $siteId ."'";
    $query = $db->select($sql);

    $tempList = array();
    foreach($query as $k => $v) {
      $tempList = array_merge($tempList, explode(',', $v->start_postcode) );
      $tempList = array_merge($tempList, explode(',', $v->end_postcode) );
    }

    foreach($tempList as $k => $v) {
      $postcode = $v;
      foreach($postcodeList as $k2 => $v2) {
        if ( $v == $v2['value'] ) {
          $postcode = '';
        }
      }
      if ( $postcode ) { // && $postcode != 'ALL'
        $postcodeList[] = array(
          'value' => $postcode,
          'text' =>  $postcode
        );
      }
    }


    // Vehicles
    $vehicleList = array();

    $sql = "SELECT `id`, `name`
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='". $siteId ."'
            ORDER BY `ordering` ASC";
    $query = $db->select($sql);

    if ( !empty($query) ) {
      foreach($query as $k => $v) {
        $vehicleList[] = array(
          'id' => (int)$v->id,
          'name' => (string)$v->name,
        );
      }
    }

    $data['vehicleList'] = $vehicleList;
    $data['postcodeList'] = $postcodeList;
    $data['success'] = true;

  break;
  case 'list':
            if (!auth()->user()->hasPermission('admin.excluded_routes.index')) {
                return redirect_no_permission();
            }

    // Convert start
    $data['list'] = [];

    $searchData = $etoPost['search']['value'];
    parse_str($searchData, $output);
    // $data['outputData'] = $output;

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
          case 'direction':
            $property = '`direction`';
            break;
          case 'start_postcode':
            $property = '`start_postcode`';
            break;
          case 'end_postcode':
            $property = '`end_postcode`';
          break;
          case 'start_date':
            $property = '`start_date`';
          break;
          case 'end_date':
            $property = '`end_date`';
          break;
          case 'allowed':
            $property = '`allowed`';
          break;
          case 'vehicles':
            $property = '`vehicles`';
          break;
          case 'description':
            $property = '`description`';
          break;
          case 'modified_date':
            $property = '`modified_date`';
          break;
          case 'ordering':
            $property = '`ordering`';
          break;
          case 'published':
            $property = '`published`';
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
    $searchText = SiteHelper::makeStrSafe($searchText);
    $sqlSearch  = '';

    if (!empty($searchText))
    {
      $sqlSearch .= " AND (".
                    " `start_postcode` LIKE '%" . $searchText . "%'".
                    " OR `end_postcode` LIKE '%" . $searchText . "%'".
                    " OR `description` LIKE '%" . $searchText . "%'".
                    " )";
    }

    // $data['sqlSearchFilter'] = $sqlSearchFilter;

    // Vehicle list
    $sql = "SELECT `id`, `name`
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='". $siteId ."'
            ORDER BY `ordering` ASC";
    $query = $db->select($sql);

    $vehicleList = array();
    if ( !empty($query) ) {
      foreach($query as $k => $v) {
        $vehicleList[] = array(
          'id' => (int)$v->id,
          'name' => (string)$v->name,
        );
      }
    }

    // Location name
    $sql = "SELECT `a`.`address`, `a`.`name`
            FROM `{$dbPrefix}location` AS `a`
            LEFT JOIN `{$dbPrefix}category` AS `b`
            ON `a`.`category_id`=`b`.`id`
            WHERE `a`.`site_id`='" . $siteId . "'";
    $queryLocations = $db->select($sql);

    function getLocationNameByAddress($locations, $address) {
      if ( !empty($locations) ) {
        foreach($locations as $key => $value) {
          if ( $address == $value->address && !empty($value->name) ) {
            if ( $value->name == $value->address ) {
              $address = $value->address;
            }
            else {
              $address = $value->name .' ('. $value->address .')';
            }
            break;
          }
        }
      }
      return $address;
    }


    // Total
    $sql = "SELECT `id`
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='" . $siteId . "' ";

    $resultsExcludedRoutesTotal = count($db->select($sql));
    $data['recordsTotal'] = $resultsExcludedRoutesTotal;


    // Filtered
    $sql = "SELECT `id`
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch;

    $resultsExcludedRoutesTotal = count($db->select($sql));
    $data['recordsFiltered'] = $resultsExcludedRoutesTotal;


    // List
    $sql = "SELECT *
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch . "
            ORDER BY " . $sqlSort . " `id` DESC
            LIMIT {$start},{$limit}";

    $resultsExcludedRoutes = $db->select($sql);

    if ( !empty($resultsExcludedRoutes) )
    {
      $excludedRoute = array();

      foreach($resultsExcludedRoutes as $key => $value)
      {
        if ( $value->direction == 1 ) {
          $direction = 'From -> To';
        }
        else {
          $direction = 'Both Ways';
        }

        $tempList = explode(',', $value->start_postcode);
        foreach($tempList as $k => $v) {
          $tempList[$k] = getLocationNameByAddress($queryLocations, $v);
        }
        $start_name = implode(', ', $tempList);

        $tempList = explode(',', $value->end_postcode);
        foreach($tempList as $k => $v) {
          $tempList[$k] = getLocationNameByAddress($queryLocations, $v);
        }
        $end_name = implode(', ', $tempList);

        $start_date = '';
        if ( $value->start_date ) {
          $start_date = SiteHelper::formatDateTime($value->start_date);
        }

        $end_date = '';
        if ( $value->end_date ) {
          $end_date = SiteHelper::formatDateTime($value->end_date);
        }

        $modified_date = '';
        if ( $value->modified_date ) {
          $modified_date = SiteHelper::formatDateTime($value->modified_date);
        }

        $vehicles = explode(',', $value->vehicles);
        $vehiclesList = '';
        if ( !empty($vehicles) ) {
          foreach($vehicles as $k1 => $v1) {
            if ( !empty($v1) ) {
              $name = 'Unknown';
              foreach($vehicleList as $kV => $vV) {
                if ( $v1 == $vV['id'] ) {
                  $name = $vV['name'];
                  break;
                }
              }
              $vehiclesList .= '<div style="color:gray;min-width:120px;">'. $name .'</div>';
            }
          }
        }

        $columns = array();
        $columns['id'] = $value->id;
        $columns['direction'] = $direction;
        $columns['start_postcode'] = '<div style="white-space:pre-line;">'. $start_name .'</div>';
        $columns['end_postcode'] = '<div style="white-space:pre-line;">'. $end_name .'</div>';
        $columns['start_date'] = $start_date;
        $columns['end_date'] = $end_date;
        $columns['description'] = $value->description;
        $columns['allowed'] = !empty($value->allowed) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';
        $columns['vehicles'] = $vehiclesList;
        $columns['modified_date'] = $modified_date;
        $columns['ordering'] = $value->ordering;
        $columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

        $excludedRoute[] = $columns;
      }

      $data['list'] = $excludedRoute;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = 'No routes were found!';
      $data['success'] = true;
    }

  break;
  case 'read':
            if (!auth()->user()->hasPermission('admin.excluded_routes.show')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT *
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $query = $db->select($sql);
    if (!empty($query[0])) {
      $query = $query[0];
    }

    if (!empty($query))
    {
      $record = array(
        'id' => $query->id,
        'site_id' => $query->site_id,
        'direction' => $query->direction,
        'start_postcode' => explode(',', $query->start_postcode),
        'end_postcode' => explode(',', $query->end_postcode),
        'start_date' => $query->start_date ? date('Y-m-d H:i', strtotime($query->start_date)) : '',
        'end_date' => $query->end_date ? date('Y-m-d H:i', strtotime($query->end_date)) : '',
        'description' => $query->description,
        'allowed' => $query->allowed,
        'vehicles' => explode(',', $query->vehicles),
        'modified_date' => $query->modified_date ? date('Y-m-d H:i:s', strtotime($query->modified_date)) : '',
        'ordering' => $query->ordering,
        'published' => $query->published
      );

      $data['record'] = $record;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_EXCLUDED_ROUTES'];
    }

  break;
  case 'update':
            if (!auth()->user()->hasPermission('admin.excluded_routes.edit')) {
                return redirect_no_permission();
            }

    $id = (int)$etoPost['id'];
    $site_id = (int)$etoPost['site_id'];
    $direction = (int)$etoPost['direction'];
    $start_postcode = (array)$etoPost['start_postcode'];
    $end_postcode = (array)$etoPost['end_postcode'];
    $start_date = (string)$etoPost['start_date'];
    $end_date = (string)$etoPost['end_date'];
    $description = (string)$etoPost['description'];
    $allowed = ( (string)$etoPost['allowed'] == '1' ) ? 1 : 0;
    $ordering = (int)$etoPost['ordering'];
    $published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

    $vehicles = array();
    $sql = "SELECT `id`, `name`
        FROM `{$dbPrefix}vehicle`
        WHERE `site_id`='". $siteId ."'
        ORDER BY `ordering` ASC";
    $query = $db->select($sql);
    if ( !empty($query) ) {
      foreach($query as $k => $v) {
        if ( !empty($etoPost['vehicle_'. $v->id]) ) {
          $vehicles[] = (int)$v->id;
        }
      }
    }
    $vehicles = implode(',', $vehicles);

    // $startArray = json_decode($start_postcode, true);
    $startArray = $start_postcode;
    $startArray = array_map('trim', $startArray);
    asort($startArray);
    $start_postcode = implode(',', $startArray);
    if ( in_array('ALL', $startArray) ) {
      $start_postcode = 'ALL';
    }

    // $endArray = json_decode($end_postcode, true);
    $endArray = $end_postcode;
    $endArray = array_map('trim', $endArray);
    asort($endArray);
    $end_postcode = implode(',', $endArray);
    if ( in_array('ALL', $endArray) ) {
      $end_postcode = 'ALL';
    }

    $sql = "";

    if ( $id > 0 ) {
      $sql = "AND `id` != '". $id ."'";
    }

    $sql = "SELECT `id`
        FROM `{$dbPrefix}excluded_routes`
        WHERE `site_id`='" . $siteId . "'
        AND (
          (
            `start_postcode`='". $start_postcode ."'
              AND
            `end_postcode`='". $end_postcode ."'
          )	OR (
            `start_postcode`='". $end_postcode ."'
              AND
            `end_postcode`='". $start_postcode ."'
          )
        )
        ". $sql ."
        ORDER BY `id` DESC LIMIT 1";

    $reasults = $db->select($sql);
    if (!empty($reasults[0])) {
      $reasults = $reasults[0];
    }

    // if ( $start_postcode == 'ALL' && $end_postcode == 'ALL' ) {
    // 	$data['message'][] = 'You can not use ALL for both locations!';
    // 	$data['success']   = false;
    // }
    // if ( $reasults )
    // {
    // 	$data['message'][] = 'This route already exists in the database!';
    // 	$data['success']   = false;
    // }
    // else
    // {
      $sql = "SELECT `id`
          FROM `{$dbPrefix}excluded_routes`
          WHERE `site_id`='" . $siteId . "'
          AND `id`='" . $id . "'
          LIMIT 1";

      $resultsExcludedRoutes = $db->select($sql);
      if (!empty($resultsExcludedRoutes[0])) {
        $resultsExcludedRoutes = $resultsExcludedRoutes[0];
      }

      $row = new \stdClass();
      $row->id = null;
      $row->site_id = ($site_id) ? $site_id : (int)$siteId;
      $row->direction = $direction;
      $row->start_postcode = strtoupper(trim($start_postcode));
      $row->end_postcode = strtoupper(trim($end_postcode));
      $row->start_date = $start_date ?: null;
      $row->end_date = $end_date ?: null;
      $row->description = $description;
      $row->allowed = $allowed;
      $row->vehicles = $vehicles;
      $row->modified_date = null;
      $row->ordering = $ordering;
      $row->published = $published;

      if ( !empty($resultsExcludedRoutes) ) // Update
      {
        $row->id = $resultsExcludedRoutes->id;
        $row->modified_date = date('Y-m-d H:i:s');

        $results = \DB::table('excluded_routes')->where('id', $row->id)->update((array)$row);
        $results = $row->id;
      }
      else // Insert
      {
        $results = \DB::table('excluded_routes')->insertGetId((array)$row);
        $row->id = $results;
      }

      $data['success'] = true;
    // }

  break;
  case 'destroy':
            if (!auth()->user()->hasPermission('admin.excluded_routes.distroy')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT `id`
            FROM `{$dbPrefix}excluded_routes`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $query = $db->select($sql);
    if (!empty($query[0])) {
      $query = $query[0];
    }

    if (!empty($query))
    {
      $sql = "DELETE FROM `{$dbPrefix}excluded_routes` WHERE `id`='" . $query->id . "' LIMIT 1";
      $results = $db->delete($sql);

      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_EXCLUDED_ROUTES'];
    }

  break;
}
