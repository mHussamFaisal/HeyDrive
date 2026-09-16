<?php

switch($action)
{
  case 'init':

    $typeList = array(
      array(
        'value' => 'custom',
        'text' => 'Custom'
      ),
      array(
        'value' => 'airport',
        'text' => 'Airport'
      ),
      array(
        'value' => 'seaport',
        'text' => 'Seaport'
      ),
      array(
        'value' => 'hotel',
        'text' => 'Hotel'
      ),
      array(
        'value' => 'station',
        'text' => 'Station'
      ),
      array(
        'value' => 'address',
        'text' => 'Address'
      ),
      array(
        'value' => 'postcode',
        'text' => 'Postcode'
      )
    );

    $data['typeList'] = $typeList;
    $data['success'] = true;

  break;
  case 'list':
    if (!auth()->user()->hasPermission('admin.categories.index')) {
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
          case 'name':
            $property = '`name`';
          break;
          case 'type':
            $property = '`type`';
          break;
          case 'color':
            $property = '`color`';
          break;
          case 'icon':
            $property = '`icon`';
          break;
          case 'featured':
            $property = '`featured`';
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
      $sqlSearch .= " AND (`id` LIKE '%" . $searchText . "%'".
                    " OR `name` LIKE '%" . $searchText . "%'".
                    " OR `type` LIKE '%" . $searchText . "%'".
                    " )";
    }

    // $data['sqlSearchFilter'] = $sqlSearchFilter;


    // Total
    $sql = "SELECT `id`
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "' ";

    $resultsCategoryTotal = count($db->select($sql));
    $data['recordsTotal'] = $resultsCategoryTotal;


    // Filtered
    $sql = "SELECT `id`
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch;

    $resultsCategoryTotal = count($db->select($sql));
    $data['recordsFiltered'] = $resultsCategoryTotal;


    // List
    $sql = "SELECT *
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch . "
            ORDER BY " . $sqlSort . " `name` ASC
            LIMIT {$start},{$limit}";

    $resultsCategory = $db->select($sql);

    if ( !empty($resultsCategory) )
    {
      $category = array();

      foreach($resultsCategory as $key => $value)
      {
        $icon = '';
        if ( $value->icon ) {
            $color = $value->color ? $value->color : '#cccccc';
            if ( preg_match('/^fa-/', $value->icon) ) {
                $value->icon = 'fa '. $value->icon;
            }
            else {
                $value->icon = 'glyphicon '. $value->icon;
            }
            $icon = $value->icon ? '<div style="text-align:center; width:30px;"><i class="'. $value->icon .'" style="font-size:22px; color:'. $color.';"></i></div>' : '';
        }

        $columns = array();
        $columns['id'] = $value->id;
        $columns['name'] = $value->name;
        $columns['type'] = $value->type;
        $columns['icon'] = $icon;
        $columns['color'] = '<span style="color:'. $value->color .';">'. $value->color .'</span>';
        $columns['featured'] = !empty($value->featured) ? '<span style="color:green;">Yes</span>' : '';
        $columns['ordering'] = $value->ordering;
        $columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

        $category[] = $columns;
      }

      $data['list'] = $category;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = 'No category were found!';
      $data['success'] = true;
    }

  break;
  case 'read':
            if (!auth()->user()->hasPermission('admin.categories.show')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT *
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsCategory = $db->select($sql);
    if (!empty($resultsCategory[0])) {
      $resultsCategory = $resultsCategory[0];
    }

    if (!empty($resultsCategory))
    {
      $record = array(
        'id' => $resultsCategory->id,
        'site_id' => $resultsCategory->site_id,
        'name' => $resultsCategory->name,
        'type' => $resultsCategory->type,
        'color' => $resultsCategory->color,
        'icon' => $resultsCategory->icon,
        'featured' => $resultsCategory->featured,
        'ordering' => $resultsCategory->ordering,
        'published' => $resultsCategory->published
      );

      $data['record'] = $record;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_Category'];
    }

  break;
  case 'update':
            if (!auth()->user()->hasPermission('admin.categories.edit')) {
                return redirect_no_permission();
            }

    $id = (int)$etoPost['id'];
    $site_id = (int)$etoPost['site_id'];
    $name = (string)$etoPost['name'];
    $type = (string)$etoPost['type'];
    $color = (string)$etoPost['color'];
    $icon = (string)$etoPost['icon'];
    $featured = ( (string)$etoPost['featured'] == '1' ) ? 1 : 0;
    $ordering = (int)$etoPost['ordering'];
    $published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

    $sql = "SELECT `id`
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsCategory = $db->select($sql);
    if (!empty($resultsCategory[0])) {
      $resultsCategory = $resultsCategory[0];
    }

    $row = new \stdClass();
    $row->id = null;
    $row->site_id = ($site_id) ? $site_id : (int)$siteId;
    $row->name = trim($name);
    $row->type = $type;
    $row->color = trim($color);
    $row->icon = $icon;
    $row->featured = $featured;
    $row->ordering = $ordering;
    $row->published = $published;

    if ( !empty($resultsCategory) ) // Update
    {
      $row->id = $resultsCategory->id;

      $results = \DB::table('category')->where('id', $row->id)->update((array)$row);
      $results = $row->id;
    }
    else // Insert
    {
      $results = \DB::table('category')->insertGetId((array)$row);
      $row->id = $results;
    }

    $data['success'] = true;

  break;
  case 'destroy':
            if (!auth()->user()->hasPermission('admin.categories.destroy')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT `id`
            FROM `{$dbPrefix}category`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsCategory = $db->select($sql);
    if (!empty($resultsCategory[0])) {
      $resultsCategory = $resultsCategory[0];
    }

    if (!empty($resultsCategory))
    {
      // Remove locations
      $sql = "DELETE FROM `{$dbPrefix}location` WHERE `category_id`='" . $resultsCategory->id . "'";
      $results = $db->delete($sql);

      // Remove category
      $sql = "DELETE FROM `{$dbPrefix}category` WHERE `id`='" . $resultsCategory->id . "' LIMIT 1";
      $results = $db->delete($sql);
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_Category'];
    }

  break;
}
