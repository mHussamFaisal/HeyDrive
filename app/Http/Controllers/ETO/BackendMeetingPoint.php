<?php

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
    $sql = "SELECT `postcode`
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='". $siteId ."'";
    $query = $db->select($sql);

    $tempList = array();
    foreach($query as $k => $v) {
      $tempList = array_merge($tempList, explode(',', $v->postcode) );
    }

    foreach($tempList as $k => $v) {
      $postcode = $v;
      foreach($postcodeList as $k2 => $v2) {
        if ( $v == $v2['value'] ) {
          $postcode = '';
        }
      }
      if ( $postcode ) {
        $postcodeList[] = array(
          'value' => $postcode,
          'text' =>  $postcode
        );
      }
    }

    $data['postcodeList'] = $postcodeList;
    $data['success'] = true;

  break;
  case 'list':
            if (!auth()->user()->hasPermission('admin.meeting_points.index')) {
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
          case 'postcode':
            $property = '`postcode`';
            break;
          case 'meet_and_greet':
            $property = '`meet_and_greet`';
          break;
          case 'airport':
            $property = '`airport`';
          break;
          case 'description':
            $property = '`description`';
          break;
          case 'note':
            $property = '`note`';
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
    $searchText = \App\Helpers\SiteHelper::makeStrSafe($searchText);
    $sqlSearch  = '';

    if (!empty($searchText))
    {
      $sqlSearch .= " AND (".
                    " `postcode` LIKE '%" . $searchText . "%'".
                    " OR `description` LIKE '%" . $searchText . "%'".
                    " )";
    }

    // $data['sqlSearchFilter'] = $sqlSearchFilter;

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
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='" . $siteId . "' ";

    $resultsMeetingPointTotal = count($db->select($sql));
    $data['recordsTotal'] = $resultsMeetingPointTotal;


    // Filtered
    $sql = "SELECT `id`
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch;

    $resultsMeetingPointTotal = count($db->select($sql));
    $data['recordsFiltered'] = $resultsMeetingPointTotal;


    // List
    $sql = "SELECT *
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch . "
            ORDER BY " . $sqlSort . " `ordering` ASC
            LIMIT {$start},{$limit}";

    $resultsMeetingPoint = $db->select($sql);

    if ( !empty($resultsMeetingPoint) )
    {
      $meetingPoint = array();

      foreach($resultsMeetingPoint as $key => $value)
      {
        $tempList = explode(',', $value->postcode);
        foreach($tempList as $k => $v) {
          $tempList[$k] = getLocationNameByAddress($queryLocations, $v);
        }
        $postcode = implode(', ', $tempList);

        $modified_date = '';
        if ( $value->modified_date ) {
          $modified_date = \App\Helpers\SiteHelper::formatDateTime($value->modified_date);
        }

        $columns = array();
        $columns['id'] = $value->id;
        $columns['postcode'] = '<div style="width:300px;white-space:normal;">'. $postcode .'</div>';
        $columns['meet_and_greet'] = !empty($value->meet_and_greet) ? '<span style="color:green;">Yes</span>' : '';
        $columns['airport'] = !empty($value->airport) ? '<span style="color:green;">Yes</span>' : '';
        $columns['description'] = '<div style="width:300px;white-space:normal;">'. $value->description .'</div>';
        $columns['note'] = '<div style="width:300px;white-space:normal;">'. $value->note .'</div>';
        $columns['modified_date'] = $value->modified_date;
        $columns['ordering'] = $value->ordering;
        $columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

        $meetingPoint[] = $columns;
      }

      $data['list'] = $meetingPoint;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = 'No meeting points were found!';
      $data['success'] = true;
    }

  break;
  case 'read':
            if (!auth()->user()->hasPermission('admin.meeting_points.show')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT *
            FROM `{$dbPrefix}meeting_point`
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
        'postcode' => explode(',', $query->postcode),
        'meet_and_greet' => $query->meet_and_greet,
        'airport' => $query->airport,
        'description' => $query->description,
        'note' => $query->note,
        'ordering' => $query->ordering,
        'published' => $query->published
      );

      $data['record'] = $record;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_MEETING_POINT'];
    }

  break;
  case 'update':
            if (!auth()->user()->hasPermission('admin.meeting_points.edit')) {
                return redirect_no_permission();
            }

    $id = (int)$etoPost['id'];
    $site_id = (int)$etoPost['site_id'];
    $postcode = (array)$etoPost['postcode'];
    $meet_and_greet = ( (string)$etoPost['meet_and_greet'] == '1' ) ? 1 : 0;
    $airport = ( (string)$etoPost['airport'] == '1' ) ? 1 : 0;
    $description = (string)$etoPost['description'];
    $note = (string)$etoPost['note'];
    $ordering = (int)$etoPost['ordering'];
    $published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

    // $postcodeArray = json_decode($postcode, true);
    $postcodeArray = $postcode;
    $postcodeArray = array_map('trim', $postcodeArray);
    asort($postcodeArray);
    $postcode = implode(',', $postcodeArray);
    if ( in_array('ALL', $postcodeArray) ) {
      $postcode = 'ALL';
    }

    $row = new \stdClass();
    $row->id = null;
    $row->site_id = ($site_id) ? $site_id : (int)$siteId;
    $row->postcode = strtoupper(trim($postcode));
    $row->meet_and_greet = $meet_and_greet;
    $row->airport = $airport;
    $row->description = $description;
    $row->note = $note;
    $row->modified_date = null;
    $row->ordering = $ordering;
    $row->published = $published;

    $sql = "SELECT `id`
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsMeetingPoint = $db->select($sql);
    if (!empty($resultsMeetingPoint[0])) {
      $resultsMeetingPoint = $resultsMeetingPoint[0];
    }

    if ( !empty($resultsMeetingPoint) ) // Update
    {
      $row->id = $resultsMeetingPoint->id;
      $row->modified_date = date('Y-m-d H:i:s');

      $results = \DB::table('meeting_point')->where('id', $row->id)->update((array)$row);
      $results = $row->id;
    }
    else // Insert
    {
      $results = \DB::table('meeting_point')->insertGetId((array)$row);
      $row->id = $results;
    }

    $data['success'] = true;

  break;
  case 'destroy':
            if (!auth()->user()->hasPermission('admin.meeting_points.destroy')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT `id`
            FROM `{$dbPrefix}meeting_point`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $query = $db->select($sql);
    if (!empty($query[0])) {
      $query = $query[0];
    }

    if (!empty($query))
    {
      $sql = "DELETE FROM `{$dbPrefix}meeting_point` WHERE `id`='" . $query->id . "' LIMIT 1";
      $results = $db->delete($sql);
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_MEETING_POINT'];
    }

  break;
}
