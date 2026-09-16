<?php
use App\Helpers\SiteHelper;

switch($action)
{
  case 'init':

      // Images
      $imagesList = array();
      $fileList = glob(asset_path('images','vehicles-types/*.{jpg,jpeg,gif,png}'), GLOB_BRACE);
      foreach($fileList as $filename) {
          $image =  basename($filename);
          $text = ucwords(str_replace('_', ' ', $image));
          $text = substr($text, 0, (strrpos($text, '.')));
          $imagesList[] = array(
              'value' => $image,
              'text' => $text
          );
      }

      // Services
      $services = \App\Models\Service::where('relation_type', 'site')
        ->where('relation_id', $siteId)
        ->where('status', 'active')
        ->orderBy('order', 'asc')
        ->orderBy('name', 'asc')
        ->get();

      $servicesList = [];
      foreach ($services as $key => $value) {
          $servicesList[] = [
              'id' => $value->id,
              'name' => $value->name
          ];
      }

      // Users
      $users = \App\Models\User::role('driver.*')
        // ->where('status', '=', 'approved')
        ->orderBy('name', 'ASC')
        ->get();

      $usersList = [];
      $usersList[] = [
        'id' => 0,
        'name' => 'Unassigned',
      ];

      foreach ($users as $key => $value) {
          $usersList[] = [
              'id' => $value->id,
              'name' => $value->getName(true)
          ];
      }

      $data['servicesList'] = $servicesList;
      $data['imagesList'] = $imagesList;
      $data['usersList'] = $usersList;
      $data['success'] = true;

  break;
  case 'list':
            if (!auth()->user()->hasPermission('admin.vehicle_types.index')) {
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
          case 'user_id':
            $property = '`user_id`';
          break;
          case 'service_ids':
            $property = '`service_ids`';
          break;
          case 'hourly_rate':
            $property = '`hourly_rate`';
          break;
          case 'name':
            $property = '`name`';
          break;
          case 'description':
            $property = '`description`';
          break;
          case 'disable_info':
            $property = '`disable_info`';
          break;
          case 'image':
            $property = '`image`';
          break;
          case 'max_amount':
            $property = '`max_amount`';
          break;
          case 'passengers':
            $property = '`passengers`';
          break;
          case 'luggage':
            $property = '`luggage`';
          break;
          case 'hand_luggage':
            $property = '`hand_luggage`';
          break;
          case 'child_seats':
            $property = '`child_seats`';
          break;
          case 'baby_seats':
            $property = '`baby_seats`';
          break;
          case 'infant_seats':
            $property = '`infant_seats`';
          break;
          case 'wheelchair':
            $property = '`wheelchair`';
          break;
          case 'factor_type':
            $property = '`factor_type`';
          break;
          case 'price':
            $property = '`price`';
          break;
          case 'default':
            $property = '`default`';
          break;
          case 'ordering':
            $property = '`ordering`';
          break;
          case 'published':
            $property = '`published`';
          break;
          case 'is_backend':
            $property = '`is_backend`';
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
      $sqlSearch .= " AND (`id` LIKE '%" . $searchText . "%'".
                    " OR `name` LIKE '%" . $searchText . "%'".
                    " OR `description` LIKE '%" . $searchText . "%'".
                    " )";
    }

    // $data['sqlSearchFilter'] = $sqlSearchFilter;


    // Total
    $sql = "SELECT `id`
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='" . $siteId . "' ";

    $resultsVehicleTotal = count($db->select($sql));
    $data['recordsTotal'] = $resultsVehicleTotal;


    // Filtered
    $sql = "SELECT `id`
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch;

    $resultsVehicleTotal = count($db->select($sql));
    $data['recordsFiltered'] = $resultsVehicleTotal;


    // List
    $sql = "SELECT *
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='" . $siteId . "' " . $sqlSearch . "
            ORDER BY " . $sqlSort . " `name` ASC
            LIMIT {$start},{$limit}";

    $resultsVehicle = $db->select($sql);

    if ( !empty($resultsVehicle) )
    {
      // Services
      $services = \App\Models\Service::where('relation_type', 'site')
        ->where('relation_id', $siteId)
        ->orderBy('order', 'asc')
        ->orderBy('name', 'asc')
        ->get();

      $vehicle = array();

      foreach($resultsVehicle as $key => $value)
      {
        if ( $value->factor_type == 1 ) {
          $factor_type = '*';
        } else {
          $factor_type = '+';
        }

        $image = '';
        if ( !empty($value->image) && \Storage::disk('vehicles-types')->exists($value->image) ) {
            $image = '<div style="width:100px;"><img src="'. asset_url('uploads','vehicles-types/'. $value->image) .'" class="vehicleImage" /></div>';
        }
        else {
            $image = '<div style="width:100px;"><img src="'. asset_url('images','placeholders/vehicle_type.png') .'" class="vehicleImage" /></div>';
        }

        $userName = '';
        if ( !empty($value->user_id) ) {
            $u = \App\Models\User::find($value->user_id);
            if ( !empty($u) && !empty($u->getName(true)) ) {
                $userName = $u->getName(true);
            }
        }

        $serviceNames = '';
        if ( !empty($value->service_ids) ) {
          $service_ids = json_decode($value->service_ids);
          $i = 0;
          foreach ($services as $k => $v) {
            if ( in_array($v->id, $service_ids) ) {
              $serviceNames .= '<div>';
              $serviceNames .= $v->name;
              if ( $i < count($service_ids) - 1 ) {
                $serviceNames .= ', ';
                $i++;
              }
              $serviceNames .= '</div>';
            }
          }
        }
        else {
          $serviceNames = 'All';
        }

        // Capacity
        $capacity = '';
        if ( $value->max_amount ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Vehicles:</td><td>'. $value->max_amount .'</td></tr>';
        }
        if ( $value->passengers ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Passengers:</td><td>'. $value->passengers .'</td></tr>';
        }
        if ( $value->luggage ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Luggage:</td><td>'. $value->luggage .'</td></tr>';
        }
        if ( $value->hand_luggage ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Hand luggage:</td><td>'. $value->hand_luggage .'</td></tr>';
        }
        if ( $value->baby_seats ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Booster seats:</td><td>'. $value->baby_seats .'</td></tr>';
        }
        if ( $value->child_seats ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Child seats:</td><td>'. $value->child_seats .'</td></tr>';
        }
        if ( $value->infant_seats ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Infant seats:</td><td>'. $value->infant_seats .'</td></tr>';
        }
        if ( $value->wheelchair ) {
            $capacity .= '<tr><td style="padding-right:10px; color:#888;">Wheelchair:</td><td>'. $value->wheelchair .'</td></tr>';
        }
        if ( $capacity ) {
            $capacity = '<table style="width:auto; font-size:12px;">'. $capacity .'</table>';
        }

        $columns = array();
        $columns['id'] = $value->id;
        $columns['user_id'] = $userName;
        $columns['service_ids'] = $serviceNames;
        $columns['hourly_rate'] = $value->hourly_rate;
        $columns['name'] = $value->name;
        $columns['description'] = $value->description;
        $columns['disable_info'] = $value->disable_info == 'yes' ? 'Yes' : '' ;
        $columns['image'] = $image;
        $columns['capacity'] = $capacity;
        $columns['factor_type'] = $factor_type;
        $columns['price'] = $factor_type .''. $value->price;
        $columns['default'] = !empty($value->default) ? '<span style="color:green;">Yes</span>' : '';
        $columns['ordering'] = $value->ordering;
        $columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';
        $columns['is_backend'] = !empty($value->is_backend) ? '<span>Backend</span>' : '<span>Frontend & Backend</span>';

        $vehicle[] = $columns;
      }

      $data['list'] = $vehicle;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = 'No vehicle were found!';
      $data['success'] = true;
    }

  break;
  case 'read':
            if (!auth()->user()->hasPermission('admin.vehicle_types.show')) {
                return redirect_no_permission();
            }

    $id = (int) $etoPost['id'];

    $sql = "SELECT *
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='" . $siteId . "'
            AND `id`='" . $id . "'
            LIMIT 1";

    $resultsVehicle = $db->select($sql);
    if (!empty($resultsVehicle[0])) {
      $resultsVehicle = $resultsVehicle[0];
    }

    if (!empty($resultsVehicle))
    {
      if ( !empty($resultsVehicle->service_ids) ) {
          $service_ids = json_decode($resultsVehicle->service_ids);
      }
      else {
          $service_ids = [];
      }

      $record = array(
        'id' => $resultsVehicle->id,
        'site_id' => $resultsVehicle->site_id,
        'user_id' => $resultsVehicle->user_id,
        'service_ids' => $service_ids,
        'hourly_rate' => $resultsVehicle->hourly_rate,
        'name' => $resultsVehicle->name,
        'description' => $resultsVehicle->description,
        'disable_info' => $resultsVehicle->disable_info == 'yes' ? 1 : 0,
        'image' => $resultsVehicle->image,
        'max_amount' => $resultsVehicle->max_amount,
        'passengers' => $resultsVehicle->passengers,
        'luggage' => $resultsVehicle->luggage,
        'hand_luggage' => $resultsVehicle->hand_luggage,
        'child_seats' => $resultsVehicle->child_seats,
        'baby_seats' => $resultsVehicle->baby_seats,
        'infant_seats' => $resultsVehicle->infant_seats,
        'wheelchair' => $resultsVehicle->wheelchair,
        'factor_type' => $resultsVehicle->factor_type,
        'price' => $resultsVehicle->price,
        'default' => $resultsVehicle->default,
        'ordering' => $resultsVehicle->ordering,
        'published' => $resultsVehicle->published,
        'is_backend' => $resultsVehicle->is_backend
      );

      $data['record'] = $record;
      $data['success'] = true;
    }
    else
    {
      $data['message'][] = $gLanguage['API']['ERROR_NO_Vehicle'];
    }

  break;
  case 'update':
            if (!auth()->user()->hasPermission('admin.vehicle_types.edit')) {
                return redirect_no_permission();
            }

      $request = request();

      $validator = \Validator::make($request->all(), [
          'image_upload' => 'mimes:jpg,jpeg,gif,png',
      ]);

      $success = false;
      $errors = [];

      if ( $validator->fails() ) {
          if ( $validator->errors() ) {
              $errors[] = $validator->errors()->first();
          }
          $success = false;
      }
      else {
          if ( !empty($etoPost['service_ids']) ) {
              $service_ids = json_encode((array)$etoPost['service_ids']);
          }
          else {
              $service_ids = null;
          }

          $id = (int)$etoPost['id'];
          $site_id = (int)$etoPost['site_id'];
          $default = ( (string)$etoPost['default'] == '1' ) ? 1 : 0;

          $sql = "SELECT `id`, `image`
                  FROM `{$dbPrefix}vehicle`
                  WHERE `site_id`='". $siteId ."'
                  AND `id`='". $id ."'
                  LIMIT 1";

          $resultsVehicle = $db->select($sql);
          if (!empty($resultsVehicle[0])) {
              $resultsVehicle = $resultsVehicle[0];
          }

          $delete = 0;
          $image = '';

          if ( !empty($resultsVehicle->image) ) {
              $image = $resultsVehicle->image;
          }

          $prevImage = $image;

          if ( $request->hasFile('image_upload') ) {
              $file = $request->file('image_upload');
              $filename = \App\Helpers\SiteHelper::generateFilename('vehicle_type') .'.'. $file->getClientOriginalExtension();

              $img = \Image::make($file);

              if ($img->width() > config('site.image_dimensions.vehicle_type.width')) {
                  $img->resize(config('site.image_dimensions.vehicle_type.width'), null, function ($constraint) {
                      $constraint->aspectRatio();
                      $constraint->upsize();
                  });
              }

              if ($img->height() > config('site.image_dimensions.vehicle_type.height')) {
                  $img->resize(null, config('site.image_dimensions.vehicle_type.height'), function ($constraint) {
                      $constraint->aspectRatio();
                      $constraint->upsize();
                  });
              }

              $img->save(asset_path('uploads','vehicles-types/'. $filename));

              if ( \Storage::disk('vehicles-types')->exists($filename) ) {
                  $image = $filename;
                  $delete = 1;
              }
          }
          elseif ( !empty($request->get('image_gallery')) && \Storage::disk('images-vehicles-types')->exists($request->get('image_gallery')) ) {
              $filepath = asset_path('images','vehicles-types/'. $request->get('image_gallery'));
              $filename = \App\Helpers\SiteHelper::generateFilename('vehicle_type') .'.'. \File::extension($filepath);
              \Storage::disk('vehicles-types')->put($filename, \Storage::disk('images-vehicles-types')->get($request->get('image_gallery')));
              if ( \Storage::disk('vehicles-types')->exists($filename) ) {
                  $image = $filename;
                  $delete = 1;
              }
          }

          if ( $delete && !empty($prevImage) && \Storage::disk('vehicles-types')->exists($prevImage) ) {
              \Storage::disk('vehicles-types')->delete($prevImage);
          }

          $row = new \stdClass();
          $row->id = null;
          $row->site_id = ($site_id) ? $site_id : (int)$siteId;
          $row->user_id = (int)$etoPost['user_id'];
          $row->service_ids = $service_ids;
          $row->hourly_rate = (float)$etoPost['hourly_rate'];
          $row->name = trim((string)$etoPost['name']);
          $row->description = (string)$etoPost['description'];
          $row->disable_info = (string)$etoPost['disable_info'] == 'yes' ? 'yes' : '';
          $row->image = $image;
          $row->max_amount = (int)$etoPost['max_amount'];
          $row->passengers = (int)$etoPost['passengers'];
          $row->luggage = (int)$etoPost['luggage'];
          $row->hand_luggage = (int)$etoPost['hand_luggage'];
          $row->child_seats = (int)$etoPost['child_seats'];
          $row->baby_seats = (int)$etoPost['baby_seats'];
          $row->infant_seats = (int)$etoPost['infant_seats'];
          $row->wheelchair = (int)$etoPost['wheelchair'];
          $row->factor_type = (int)$etoPost['factor_type'];
          $row->price = (float)$etoPost['price'];
          $row->default = $default;
          $row->ordering = (int)$etoPost['ordering'];
          $row->published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;
          $row->is_backend = (int)$etoPost['is_backend'];

          if ( $default > 0 ) {
              $sql = "UPDATE `{$dbPrefix}vehicle` SET `default`='0' WHERE `site_id`='" . $siteId . "' AND `default`='1'";
              $db->update($sql);
          }

          if ( !empty($resultsVehicle) ) {
              $row->id = $resultsVehicle->id;
              $results = \DB::table('vehicle')->where('id', $row->id)->update((array)$row);
              $results = $row->id;
          }
          else {
              $results = \DB::table('vehicle')->insertGetId((array)$row);
              $row->id = $results;
          }

          $success = true;
          $data['id'] = $row->id;
      }

      $data['success'] = $success;
      $data['errors'] = $errors;

  break;
  case 'destroy':
      if (!auth()->user()->hasPermission('admin.vehicle_types.destroy')) {
          return redirect_no_permission();
      }

      $id = (int) $etoPost['id'];

      $sql = "SELECT `id`, `image`
              FROM `{$dbPrefix}vehicle`
              WHERE `site_id`='" . $siteId . "'
              AND `id`='" . $id . "'
              LIMIT 1";

      $resultsVehicle = $db->select($sql);
      if (!empty($resultsVehicle[0])) {
          $resultsVehicle = $resultsVehicle[0];
      }

      if (!empty($resultsVehicle)) {
          if ( !empty($resultsVehicle->image) && \Storage::disk('vehicles-types')->exists($resultsVehicle->image) ) {
              \Storage::disk('vehicles-types')->delete($resultsVehicle->image);
          }

          $sql = "DELETE FROM `{$dbPrefix}vehicle` WHERE `id`='" . $resultsVehicle->id . "' LIMIT 1";
          $results = $db->delete($sql);
          $data['success'] = true;
      }
      else {
          $data['message'][] = $gLanguage['API']['ERROR_NO_Vehicle'];
      }

  break;
}
