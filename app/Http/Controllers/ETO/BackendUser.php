<?php
use App\Helpers\SiteHelper;

switch( $action ) {
  case 'init':

    $data['success'] = true;

  break;
  case 'list':

    if (!auth()->user()->hasPermission('admin.users.customer.index')) {
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


    $sqlSort = '';
    $sqlFilter  = '';

    // Sort and limit
    $sort = json_decode($etoPost['sort']);
    $start = (int) $etoPost['start'];
    $limit = (int) $etoPost['length'];
    //$page = (int) $etoPost['page'];

//    if ( !empty($sort) ) {
//      foreach($sort as $key => $value) {
//        $property = '';
//
//        switch( (string)$value->property ) {
//          case 'id':
//            $property = '`a`.`id`';
//          break;
//          case 'name':
//            $property = '`a`.`name`';
//          break;
//          case 'email':
//            $property = '`a`.`email`';
//          break;
//          case 'ip':
//            $property = '`a`.`ip`';
//          break;
//          case 'description':
//            $property = '`a`.`description`';
//          break;
//          case 'last_visit_date':
//            $property = '`a`.`last_visit_date`';
//          break;
//          case 'created_date':
//            $property = '`a`.`created_date`';
//          break;
//          case 'activated':
//            $property = '`a`.`activated`';
//          break;
//          case 'published':
//            $property = '`a`.`published`';
//          break;
//          case 'mobile_number':
//            $property = '`b`.`mobile_number`';
//          break;
//          case 'telephone_number':
//            $property = '`b`.`telephone_number`';
//          break;
//          case 'emergency_number':
//            $property = '`b`.`emergency_number`';
//          break;
//          case 'is_company':
//            $property = '`a`.`is_company`';
//          break;
//          case 'company_name':
//            $property = '`b`.`company_name`';
//          break;
//          case 'company_number':
//            $property = '`b`.`company_number`';
//          break;
//          case 'company_tax_number':
//            $property = '`b`.`company_tax_number`';
//          break;
//          case 'is_account_payment':
//            $property = '`b`.`is_account_payment`';
//          break;
//          case 'address':
//            $property = '`b`.`address`';
//          break;
//          case 'city':
//            $property = '`b`.`city`';
//          break;
//          case 'postcode':
//            $property = '`b`.`postcode`';
//          break;
//          case 'state':
//            $property = '`b`.`state`';
//          break;
//          case 'country':
//            $property = '`b`.`country`';
//          break;
//        }
//
//        if ( !empty($property) ) {
//          $sqlSort .= $property .' '. $value->direction .', ';
//        }
//      }
//    }

    // Filters
    $text = (string)$etoPost['searchText'];
    $text = SiteHelper::makeStrSafe($text);

    // Total
    $model = \App\Models\Customer::select('user.*', 'user_customer.*', 'user.id as main_user_id')
        ->join('user_customer', 'user_customer.user_id', '=', 'user.id')
        ->where('user.type', 1);
    $data['recordsTotal'] = $model->get()->count();

    // Filtered
    $model->where(function ($q) use ($text) {
        $q->where('user.name', 'like', '%' . $text . '%')
            ->orWhere('user.email', 'like', '%' . $text . '%')
            ->orWhere('user.description', 'like', '%' . $text . '%');
    });
    $data['recordsFiltered'] = $model->get()->count();

    // List
      if ( !empty($sort) ) {
          foreach($sort as $key => $value) {
              $property = '';

              switch( (string)$value->property ) {
                  case 'id':
                      $property = 'user.id';
                      break;
                  case 'name':
                      $property = 'user.name';
                      break;
                  case 'email':
                      $property = 'user.email';
                      break;
                  case 'ip':
                      $property = 'user.ip';
                      break;
                  case 'description':
                      $property = 'user.description';
                      break;
                  case 'last_visit_date':
                      $property = 'user.last_visit_date';
                      break;
                  case 'created_date':
                      $property = 'user.created_date';
                      break;
                  case 'activated':
                      $property = 'user.activated';
                      break;
                  case 'published':
                      $property = 'user.published';
                      break;
                  case 'mobile_number':
                      $property = 'user_customer.mobile_number`';
                      break;
                  case 'telephone_number':
                      $property = 'user_customer.telephone_number';
                      break;
                  case 'emergency_number':
                      $property = 'user_customer.emergency_number';
                      break;
                  case 'is_company':
                      $property = 'user.is_company';
                      break;
                  case 'company_name':
                      $property = 'user_customer.company_name';
                      break;
                  case 'company_number':
                      $property = 'user_customer.company_number';
                      break;
                  case 'company_tax_number':
                      $property = 'user_customer.company_tax_number';
                      break;
                  case 'is_account_payment':
                      $property = 'user_customer.is_account_payment';
                      break;
                  case 'address':
                      $property = 'user_customer.address';
                      break;
                  case 'city':
                      $property = 'user_customer.city';
                      break;
                  case 'postcode':
                      $property = 'user_customer.postcode';
                      break;
                  case 'state':
                      $property = 'user_customer.state';
                      break;
                  case 'country':
                      $property = 'user_customer.country';
                      break;
              }

              if ( !empty($property) ) {
                  $model->orderBy($property, $value->direction);
              }
          }
      }

    $model->orderBy('user.name', 'asc');
    $model->offset($start)->limit($limit);
    $queryList = $model->get();

//    if ( !empty($text) ) {
//      $sqlFilter .= " AND (".
//                    " `a`.`name` LIKE '%". $text ."%'".
//                    " OR `a`.`email` LIKE '%". $text ."%'".
//                    " OR `a`.`description` LIKE '%". $text ."%'".
//                    " )";
//    }
//
//
//    // Total
//    $sql = "SELECT `a`.*, `b`.*
//            FROM `{$dbPrefix}user` AS `a`
//            LEFT JOIN `{$dbPrefix}user_customer` AS `b`
//            ON `a`.`id`=`b`.`user_id`
//            WHERE `a`.`site_id`='". $siteId ."'
//            AND `a`.`type`='1' ";
//
//    $queryTotal = count($db->select($sql));
//    $data['recordsTotal'] = $queryTotal;
//
//
//    // Filtered
//    $sql = "SELECT `a`.*, `b`.*
//            FROM `{$dbPrefix}user` AS `a`
//            LEFT JOIN `{$dbPrefix}user_customer` AS `b`
//            ON `a`.`id`=`b`.`user_id`
//            WHERE `a`.`site_id`='". $siteId ."'
//            AND `a`.`type`='1' ". $sqlFilter;
//
//    $queryTotal = count($db->select($sql));
//    $data['recordsFiltered'] = $queryTotal;
//
//
//    // List
//    $sql = "SELECT `a`.*, `b`.*, `a`.`id` AS `main_user_id`
//            FROM `{$dbPrefix}user` AS `a`
//            LEFT JOIN `{$dbPrefix}user_customer` AS `b`
//            ON `a`.`id`=`b`.`user_id`
//            WHERE `a`.`site_id`='". $siteId ."'
//            AND `a`.`type`='1' ". $sqlFilter ."
//            ORDER BY " . $sqlSort . " `name` ASC
//            LIMIT {$start},{$limit}";
//
//    $queryList = $db->select($sql);

    if ( !empty($queryList) ) {
      $rows = array();

      foreach($queryList as $key => $value) {
        $row = array();
        $row['id'] = $value->main_user_id;
        $row['name'] = $value->title .' '. $value->first_name .' '. $value->last_name;
        $row['email'] = $value->email;
        $row['ip'] = $value->ip;
        $row['description'] = $value->description;
        $row['last_visit_date'] = SiteHelper::formatDateTime($value->last_visit_date);
        $row['created_date'] = SiteHelper::formatDateTime($value->created_date);
        $row['activated'] = !empty($value->activated) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';
        $row['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

        $row['title'] = $value->title;
        $row['first_name'] = $value->first_name;
        $row['last_name'] = $value->last_name;
        $row['mobile_number'] = $value->mobile_number;
        $row['telephone_number'] = $value->telephone_number;
        $row['emergency_number'] = $value->emergency_number;

        $row['is_company'] = !empty($value->is_company) ? 'Company' : 'Private';
        $row['company_name'] = $value->company_name ?: '';
        $row['company_number'] = $value->company_number ?: '';
        $row['company_tax_number'] = $value->company_tax_number ?: '';
        $row['is_account_payment'] = !empty($value->is_company) ? ($value->is_account_payment ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>') : '';

        $row['address'] = $value->address;
        $row['city'] = $value->city;
        $row['postcode'] = $value->postcode;
        $row['state'] = $value->state;
        $row['country'] = $value->country;
        $row['avatar'] = '<img src="'.$value->getAvatarPath().'" class="img-circle " alt="" style="max-width:50px; max-height:50px;" title="">';

        $rows[] = $row;
      }

      $data['list'] = $rows;
      $data['success'] = true;
    }
    else {
      $data['message'][] = $gLanguage['API']['ERROR_NO_DRIVERS'];
      $data['success'] = true;
    }

  break;
  case 'read':

    if (!auth()->user()->hasPermission('admin.users.customer.show')) {
        return redirect_no_permission();
    }

    $id = (int)$etoPost['id'];
    $model = \App\Models\Customer::select('user.*', 'user_customer.*', 'user.id as main_user_id')->join('user_customer', 'user_customer.user_id', '=', 'user.id')->find($id);

    if ( !empty($model->id) ) {
      $row = array(
        'id' => $model->main_user_id,
        'site_id' => $model->site_id,
        'name' => $model->name,
        'email' => $model->email,
        //'password' => $query->password,
        'type' => $model->type,
        'ip' => $model->ip,
        'token_password' => $model->token_password,
        'token_activation' => $model->token_activation,
        'description' => $model->description,
        'last_visit_date' => $model->last_visit_date ?: '',
        'created_date' => $model->created_date ?: '',
        'activated' => $model->activated,
        'published' => $model->published,
        'title' => $model->title,
        'first_name' => $model->first_name,
        'last_name' => $model->last_name,
        'mobile_number' => $model->mobile_number,
        'telephone_number' => $model->telephone_number,
        'emergency_number' => $model->emergency_number,
        'is_company' => $model->is_company,
        'company_name' => $model->company_name ?: '',
        'company_number' => $model->company_number ?: '',
        'company_tax_number' => $model->company_tax_number ?: '',
        'is_account_payment' => $model->is_account_payment ? 1 : 0,
        'address' => $model->address,
        'city' => $model->city,
        'postcode' => $model->postcode,
        'state' => $model->state,
        'country' => $model->country,
        'departments' => $model->departments,
        'avatar' => $model->avatar,
        'avatar_path' => $model->getAvatarPath()
      );

      $data['record'] = $row;
      $data['success'] = true;
    }
    else {
      $data['message'][] = $gLanguage['API']['ERROR_NO_DRIVER'];
    }

  break;
  case 'update':

    if (!auth()->user()->hasPermission('admin.users.customer.edit')) {
        return redirect_no_permission();
    }

    $id = (int)$etoPost['id'];
    $site_id = (int)$etoPost['site_id'];
    $email = trim((string)$etoPost['email']);
    $password = (string)$etoPost['password'];
    $ip = (string)$etoPost['ip'];
    $token_password = (string)$etoPost['token_password'];
    $token_activation = (string)$etoPost['token_activation'];
    $description = (string)$etoPost['description'];
    $last_visit_date = (string)$etoPost['last_visit_date'];
    $created_date = (string)$etoPost['created_date'];
    $activated = ( (string)$etoPost['activated'] == '1' ) ? 1 : 0;
    $published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

    $title = (string)$etoPost['title'];
    $first_name = (string)$etoPost['first_name'];
    $last_name = (string)$etoPost['last_name'];
    $mobile_number = (string)$etoPost['mobile_number'];
    $telephone_number = (string)$etoPost['telephone_number'];
    $emergency_number = (string)$etoPost['emergency_number'];

    $is_company = ( (string)$etoPost['is_company'] == '1' ) ? 1 : 0;
    $company_name = (string)$etoPost['company_name'];
    $company_number = (string)$etoPost['company_number'];
    $company_tax_number = (string)$etoPost['company_tax_number'];
    $is_account_payment = ( (string)$etoPost['is_account_payment'] == '1' ) ? 1 : 0;

    $address = (string)$etoPost['address'];
    $city = (string)$etoPost['city'];
    $postcode = (string)$etoPost['postcode'];
    $state = (string)$etoPost['state'];
    $country = (string)$etoPost['country'];

    $uploaded = true;

    $queryCheck = \App\Models\Customer::where('email', $email)->where('id', '!=', $id)->first();

    if ( !empty($queryCheck->id) ) {
        $data['message'] = trans('frontend.js.userMsg_EmailTaken');
        $data['success'] = false;
    }
    else {
        $rowUser = new \stdClass();
        $rowUser->id = null;
        $rowUser->site_id = ($site_id) ? $site_id : (int)$siteId;
        $rowUser->name = trim($first_name .' '. $last_name);
        $rowUser->email = $email;
        if ( !empty($password) ) {
            $rowUser->password = md5($password);
        }
        $rowUser->description = $description;
        $rowUser->is_company = $is_company;
        $rowUser->ip = $ip;
        $rowUser->token_password = $token_password;
        $rowUser->token_activation = $token_activation;
        //$rowUser->last_visit_date = $last_visit_date;
        //$rowUser->created_date = $created_date;
        $rowUser->activated = $activated;
        $rowUser->published = $published;

        $rowCustomer = new \stdClass();
        $rowCustomer->id = null;
        $rowCustomer->title = $title;
        $rowCustomer->first_name = $first_name;
        $rowCustomer->last_name = $last_name;
        $rowCustomer->mobile_number = $mobile_number;
        $rowCustomer->telephone_number = $telephone_number;
        $rowCustomer->emergency_number = $emergency_number;

        if ( $is_company == 1 ) {
            $rowCustomer->company_name = $company_name;
            $rowCustomer->company_number = $company_number;
            $rowCustomer->company_tax_number = $company_tax_number;
        }
        else {
            $rowCustomer->company_name = null;
            $rowCustomer->company_number = null;
            $rowCustomer->company_tax_number = null;
        }

        $rowCustomer->is_account_payment = $is_account_payment;
        $rowCustomer->address = $address;
        $rowCustomer->city = $city;
        $rowCustomer->postcode = $postcode;
        $rowCustomer->state = $state;
        $rowCustomer->country = $country;

        $queryUser = \App\Models\Customer::where('id', $id)->first();

        if ($is_company == 1) {
            $departments = [];
            if (!empty($etoPost['departments'])) {
                foreach ($etoPost['departments'] as $k => $v) {
                    if (!empty($v)) {
                        $departments[] = $v;
                    }
                }
            }
            $rowUser->departments = $departments ? json_encode($departments) : null;
        }
        else {
            $rowUser->departments = null;
        }

        if (!empty($_FILES['avatar']['name']) && request()->hasFile('avatar')) {
            $file = request()->file('avatar');

            if (in_array($file->getClientOriginalExtension(), ['jpg','jpeg','gif','png'])) {
                $filename = \App\Helpers\SiteHelper::generateFilename('customer') . '.' . $file->getClientOriginalExtension();
                $img = \Image::make($file);

                if ($img->width() > config('site.image_dimensions.avatar.width')) {
                    $img->resize(config('site.image_dimensions.avatar.width'), null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                if ($img->height() > config('site.image_dimensions.avatar.height')) {
                    $img->resize(null, config('site.image_dimensions.avatar.height'), function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $img->save(asset_path('uploads', 'avatars/' . $filename));
                $rowUser->avatar = $filename;
                $uploaded = true;
            } else {
                $data['message'] = trans('frontend.js.userMsg_AvatarExtension');
                $data['success'] = false;
                $uploaded = false;
            }
        }

        if ($uploaded === true) {
            if (!empty($queryUser->id)) {
                $rowUser->id = $queryUser->id;
                unset($rowUser->site_id);

                if (!empty($rowUser->avatar) || (int)$etoPost['avatar_delete'] === 1) {
                    if (($queryUser->avatar || (int)$etoPost['avatar_delete'] === 1) && \Storage::disk('avatars')->exists($queryUser->avatar)) {
                        \Storage::disk('avatars')->delete($queryUser->avatar);
                    }
                } else {
                    $rowUser->avatar = $queryUser->avatar;
                }

                if (empty($rowUser->avatar) && (int)$etoPost['avatar_delete'] === 1) {
                    $rowUser->avatar = null;
                }

                $results = \DB::table('user')->where('id', $rowUser->id)->update((array)$rowUser);
                $results = $rowUser->id;
            } else {
                $rowUser->type = 1;
                $rowUser->created_date = date('Y-m-d H:i:s');
                if (empty($rowUser->password)) {
                    $rowUser->password = md5(date('Y-m-d H:i:s'));
                }
                if (empty($rowUser->last_visit_date)) {
                    $rowUser->last_visit_date = date('Y-m-d H:i:s');
                }
                if (empty($rowUser->created_date)) {
                    $rowUser->created_date = date('Y-m-d H:i:s');
                }

                $results = \DB::table('user')->insertGetId((array)$rowUser);
                $rowUser->id = $results;
            }

            if ($results) {
                $sql = "SELECT `id`
                    FROM `{$dbPrefix}user_customer`
                    WHERE `user_id`='" . $rowUser->id . "'
                    LIMIT 1";

                $queryUser = $db->select($sql);
                if (!empty($queryUser[0])) {
                    $queryUser = $queryUser[0];
                }

                if (!empty($queryUser)) {
                    $rowCustomer->id = $queryUser->id;
                    $results = \DB::table('user_customer')->where('id', $rowCustomer->id)->update((array)$rowCustomer);
                } else {
                    $rowCustomer->user_id = $rowUser->id;
                    $results = \DB::table('user_customer')->insertGetId((array)$rowCustomer);
                }
            }

            $data['customer_id'] = $rowUser->id;
            $data['success'] = true;
        }
    }

  break;
  case 'destroy':

    if (!auth()->user()->hasPermission('admin.users.customer.destroy')) {
        return redirect_no_permission();
    }

    $id = (int)$etoPost['id'];

    $sql = "SELECT `id`, `avatar`
            FROM `{$dbPrefix}user`
            WHERE `site_id`='". $siteId ."'
            AND `id`='". $id ."'
            AND `type`='1'
            LIMIT 1";

    $query = $db->select($sql);
    if (!empty($query[0])) {
      $query = $query[0];
    }

    if ( !empty($query) ) {
      // User customer
      $sql = "DELETE FROM `{$dbPrefix}user_customer` WHERE `user_id`='". $query->id ."' LIMIT 1";
      $results1 = $db->delete($sql);

      // User
      $sql = "DELETE FROM `{$dbPrefix}user` WHERE `id`='". $query->id ."' LIMIT 1";
      $results2 = $db->delete($sql);

      if ($query->avatar && \Storage::disk('avatars')->exists($query->avatar)) {
          \Storage::disk('avatars')->delete($query->avatar);
      }

      $data['success'] = true;
    }
    else {
      $data['message'][] = $gLanguage['API']['ERROR_NO_DRIVER'];
    }

  break;
}
