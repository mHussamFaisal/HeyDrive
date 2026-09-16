<?php
use App\Helpers\SiteHelper;

switch($action) {
    case 'init':

        $profileList = array();

        $sql = "SELECT `id`, `name`
                FROM `{$dbPrefix}profile`
                WHERE `published`='1'
                ORDER BY `ordering` ASC";

        $resultsProfile = $db->select($sql);

        if (!empty($resultsProfile)) {
          foreach($resultsProfile as $key => $value) {
            $profileList[] = array(
              'value' => $value->id,
              'text' => $value->name
            );
          }
        }

        $data['profileList'] = $profileList;
        $data['siteId'] = $siteId;
        $data['success'] = true;

    break;
    case 'list':

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

        if (!empty($sort)) {
          foreach($sort as $key => $value) {
            $property = '';
            switch((string) $value->property) {
              case 'id':
                $property = '`id`';
              break;
              case 'name':
                $property = '`name`';
              break;
              case 'description':
                $property = '`description`';
              break;
              case 'domain':
                $property = '`domain`';
              break;
              case 'key':
                $property = '`key`';
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
            }

            if (!empty($property)) {
              $sqlSort .= $property . ' ' . $value->direction . ', ';
            }
          }
        }

        // Search text
        $searchText = (string) $etoPost['searchText'];
        $searchText = SiteHelper::makeStrSafe($searchText);
        $sqlSearch  = '';

        if (!empty($searchText)) {
          $sqlSearch .= " AND (`id` LIKE '%" . $searchText . "%'".
                        " OR `name` LIKE '%" . $searchText . "%'".
                        " OR `description` LIKE '%" . $searchText . "%'".
                        " )";
        }

        // $data['sqlSearchFilter'] = $sqlSearchFilter;


        // Profile total
        $sql = "SELECT `id`
                FROM `{$dbPrefix}profile`";

        $resultsProfileTotal = count($db->select($sql));
        $data['recordsTotal'] = $resultsProfileTotal;


        // Profile filtered
        $sql = "SELECT `id`
                FROM `{$dbPrefix}profile`
                WHERE 1 " . $sqlSearch;

        $resultsProfileTotal = count($db->select($sql));
        $data['recordsFiltered'] = $resultsProfileTotal;


        // List
        $sql = "SELECT *
                FROM `{$dbPrefix}profile`
                WHERE 1 " . $sqlSearch . "
                ORDER BY " . $sqlSort . " `name` ASC
                LIMIT {$start},{$limit}";

        $resultsProfile = $db->select($sql);

        if ( !empty($resultsProfile) ) {
          $profile = array();

          foreach($resultsProfile as $key => $value) {
            $columns = array();
            $columns['id'] = $value->id;
            $columns['name'] = $value->name;
            $columns['description'] = $value->description;
            $columns['domain'] = $value->domain;
            $columns['key'] = $value->key;
            $columns['default'] = !empty($value->default) ? '<span style="color:green;">Yes</span>' : '';
            $columns['ordering'] = $value->ordering;
            $columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';
            $profile[] = $columns;
          }

          $data['list'] = $profile;
          $data['success'] = true;
        }
        else {
          $data['message'][] = 'No profile were found!';
          $data['success'] = true;
        }

    break;
    case 'read':

        $id = (int) $etoPost['id'];

        $sql = "SELECT *
                FROM `{$dbPrefix}profile`
                WHERE 1
                AND `id`='" . $id . "'
                LIMIT 1";

        $resultsProfile = $db->select($sql);
        if (!empty($resultsProfile[0])) {
          $resultsProfile = $resultsProfile[0];
        }

        if (!empty($resultsProfile)) {
          $record = array(
            'id' => $resultsProfile->id,
            'name' => $resultsProfile->name,
            'description' => $resultsProfile->description,
            'domain' => $resultsProfile->domain,
            'key' => $resultsProfile->key,
            'default' => $resultsProfile->default,
            'ordering' => $resultsProfile->ordering,
            'published' => $resultsProfile->published
          );

          $data['record'] = $record;
          $data['success'] = true;
        }
        else {
          $data['message'][] = $gLanguage['API']['ERROR_NO_Profile'];
        }

    break;
    case 'update':

        $request = request();

        $id = (int)$etoPost['id'];
        $name = (string)$etoPost['name'];
        $description = (string)$etoPost['description'];
        $domain = (string)$etoPost['domain'];
        $key = (string)$etoPost['key'];
        $default = ( (string)$etoPost['default'] == '1' ) ? 1 : 0;
        $ordering = (int)$etoPost['ordering'];
        $published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

        $sql = "SELECT `id`
                FROM `{$dbPrefix}profile`
                WHERE 1
                AND `id`='" . $id . "'
                LIMIT 1";

        $resultsProfile = $db->select($sql);
        if (!empty($resultsProfile[0])) {
          $resultsProfile = $resultsProfile[0];
        }

        if ( empty($key) ) {
          $key = md5('ETO-'. rand(10000, 100000000) .'-'. date('Y-m-d-H:i:s'));
        }

        $row = new \stdClass();
        $row->id = null;
        $row->subscription_id = $request->system->subscription->id;
        $row->name = trim($name);
        $row->description = trim($description);
        $row->domain = trim($domain);
        $row->key = trim($key);
        $row->default = $default;
        $row->ordering = $ordering;
        $row->published = $published;

        // Reset default
        if ( $default > 0 ) {
            $sql = "UPDATE `{$dbPrefix}profile` SET `default`='0' WHERE `default`='1'";
            $db->update($sql);
        }

        if ( !empty($resultsProfile) ) {
            $row->id = $resultsProfile->id;

            $results = \DB::table('profile')->where('id', $row->id)->update((array)$row);
            $results = $row->id;
        }
        else {
            if ($request->system->subscription->params->sites->limit > count($request->system->sites)) {
                $results = \DB::table('profile')->insertGetId((array)$row);
                $row->id = $results;
            }
            else {
                $subscriprionSites = false;
            }
        }

        if (isset($subscriprionSites) && $subscriprionSites === false) {
            $data['message'] = 'Site limit exceeded.';
            $data['success'] = false;
        }
        else {
            $data['success'] = true;
        }

    break;
    case 'switch':

        $id = (int) $etoPost['id'];

        $sql = "SELECT `id`, `key`
                FROM `{$dbPrefix}profile`
                WHERE `id`='". $id ."'
                AND `published`='1'
                LIMIT 1";

        $resultsProfile = $db->select($sql);
        if (!empty($resultsProfile[0])) {
          $resultsProfile = $resultsProfile[0];
        }

        if (!empty($resultsProfile)) {
            session([
                'admin_site_id' => $resultsProfile->id,
                'admin_site_key' => $resultsProfile->key,
            ]);

            $data['success'] = true;
        }
      else {
          $data['message'][] = 'No profile was found!';
      }

    break;
    case 'destroy':

        $deleteSiteId = (int)$etoPost['id'];

        if ( !empty($deleteSiteId) ) {
          $sql = "SELECT `id`
              FROM `{$dbPrefix}booking`
              WHERE `site_id`='". $deleteSiteId ."'
              ORDER BY `id` ASC";
          $query = $db->select($sql);

          if ( !empty($query) ) {
            foreach($query as $key => $value) {
              $sql = "DELETE FROM `{$dbPrefix}booking_route` WHERE `booking_id`='". $value->id ."'";
              $db->delete($sql);

              \App\Models\Transaction::where('relation_type', 'booking')->where('relation_id', $value->id)->delete();
            }
          }

          $sql = "DELETE FROM `{$dbPrefix}booking` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          \App\Models\Base::where('relation_type', 'site')->where('relation_id', $deleteSiteId)->delete();

          \App\Models\Service::where('relation_type', 'site')->where('relation_id', $deleteSiteId)->delete();

          $sql = "DELETE FROM `{$dbPrefix}category` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "DELETE FROM `{$dbPrefix}charge` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $q = \App\Models\Config::where('site_id', $deleteSiteId)->where('key', 'logo')->first();
          if ( !empty($q->id) ) {
              if ( \Storage::disk('logo')->exists($q->value) ) {
                  \Storage::disk('logo')->delete($q->value);
              }
          }
          $sql = "DELETE FROM `{$dbPrefix}config` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "DELETE FROM `{$dbPrefix}discount` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "DELETE FROM `{$dbPrefix}excluded_routes` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);


          // Fixed prices
          $prices = \App\Models\FixedPrice::where('site_id', $deleteSiteId)->get();

          if (!empty($prices)) {
              foreach ($prices as $k => $v) {
                  $price = \App\Models\FixedPrice::findOrFail($v->id);
                  $price->zones()->detach();
                  $price->delete();
              }
          }

          // $sql = "DELETE FROM `{$dbPrefix}fixed_prices` WHERE `site_id`='". $deleteSiteId ."'";
          // $db->delete($sql);


          $sql = "DELETE FROM `{$dbPrefix}location` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "DELETE FROM `{$dbPrefix}meeting_point` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $q = \App\Models\Payment::where('site_id', $deleteSiteId)->get();
          foreach ($q as $qK => $qV) {
            if ( \Storage::disk('payments')->exists($qV->image) ) {
                \Storage::disk('payments')->delete($qV->image);
            }
          }
          $sql = "DELETE FROM `{$dbPrefix}payment` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "SELECT `id`
              FROM `{$dbPrefix}user`
              WHERE `site_id`='". $deleteSiteId ."'
              ORDER BY `id` ASC";
          $query = $db->select($sql);

          if ( !empty($query) ) {
            foreach($query as $key => $value) {
              $sql = "DELETE FROM `{$dbPrefix}user_customer` WHERE `user_id`='". $value->id ."'";
              $db->delete($sql);

              // Delete files
              $sql = "SELECT *
                  FROM `{$dbPrefix}file`
                  WHERE `file_relation_type`='user'
                  AND `file_relation_id`='". $value->id ."'";
              $query2 = $db->select($sql);

              if ( !empty($query2) ) {
                foreach($query2 as $key2 => $value2) {
                  if ( \Storage::disk('safe')->exists($value2->file_path) ) {
                      \Storage::disk('safe')->delete($value2->file_path);
                  }
                  $sql = "DELETE FROM `{$dbPrefix}file` WHERE `file_id`='". $value2->file_id ."' LIMIT 1";
                  $db->delete($sql);
                }
              }
            }
          }

          // Double check
          $sql = "DELETE FROM `{$dbPrefix}file` WHERE `file_site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "DELETE FROM `{$dbPrefix}user` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $q = \App\Models\VehicleType::where('site_id', $deleteSiteId)->get();
          foreach ($q as $qK => $qV) {
            if ( \Storage::disk('vehicles-types')->exists($qV->image) ) {
                \Storage::disk('vehicles-types')->delete($qV->image);
            }
          }
          $sql = "DELETE FROM `{$dbPrefix}vehicle` WHERE `site_id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $sql = "DELETE FROM `{$dbPrefix}profile` WHERE `id`='". $deleteSiteId ."'";
          $db->delete($sql);

          $data['success'] = true;
        }

        clear_cache('cache');

    break;
    case 'copy':

          $request = request();
          $copySiteId = (int)$etoPost['id'];

          if ($request->system->subscription->params->sites->limit > count($request->system->sites)) {
              $subscriprionSites = true;
          }
          else {
              $subscriprionSites = false;
          }

          if (isset($subscriprionSites) && $subscriprionSites === false) {
              $data['message'] = 'Site limit exceeded.';
              $data['success'] = false;
          }
          else {
              if ( !empty($copySiteId) ) {
                $sql = "SELECT `id`
                    FROM `{$dbPrefix}profile`
                    WHERE `id`='". $copySiteId ."'
                    LIMIT 1";
                $query = $db->select($sql);
                if (!empty($query[0])) {
                  $query = $query[0];
                }

                if ( !empty($query) ) {

                  // Profile
                  $rowProfile = new \stdClass();
                  $rowProfile->id = null;
                  $rowProfile->subscription_id = $request->system->subscription->id;
                  $rowProfile->name = 'Profile '. date('Ymd-His');
                  $rowProfile->description = '';
                  $rowProfile->domain = '';
                  $rowProfile->key = md5('ETO-'. rand(10000, 100000000) .'-'. date('Y-m-d-H:i:s'));
                  $rowProfile->default = '0';
                  $rowProfile->ordering = '0';
                  $rowProfile->published = '1';

                  $resultsProfile = \DB::table('profile')->insertGetId((array)$rowProfile);
                  $rowProfile->id = $resultsProfile;

                  if ( $rowProfile->id > 0 ) {
                    $data['message'][] = 'Profile - Done';

                    // Category
                    $sql = "SELECT *
                        FROM `{$dbPrefix}category`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        $rowCategory = new \stdClass();
                        $rowCategory->id = null;
                        $rowCategory->site_id = $rowProfile->id;
                        $rowCategory->name = $value->name;
                        $rowCategory->type = $value->type;
                        $rowCategory->icon = $value->icon;
                        $rowCategory->color = $value->color;
                        $rowCategory->featured = $value->featured;
                        $rowCategory->ordering = $value->ordering;
                        $rowCategory->published = $value->published;

                        $resultsCategory = \DB::table('category')->insertGetId((array)$rowCategory);
                        $rowCategory->id = $resultsCategory;

                        if ( $rowCategory->id > 0 ) {

                          // Location
                          $sql = "SELECT *
                              FROM `{$dbPrefix}location`
                              WHERE `site_id`='". $copySiteId ."'
                              AND `category_id`='". $value->id ."'
                              AND `published`='1'
                              ORDER BY `id` ASC";
                          $query2 = $db->select($sql);

                          if ( !empty($query2) ) {
                            foreach($query2 as $key2 => $value2) {
                              $row2 = new \stdClass();
                              $row2->id = null;
                              $row2->site_id = $rowProfile->id;
                              $row2->category_id = $rowCategory->id;
                              $row2->name = $value2->name;
                              $row2->address = $value2->address;
                              $row2->full_address = $value2->full_address;
                              $row2->search_keywords = $value2->search_keywords;
                              $row2->lat = $value2->lat;
                              $row2->lon = $value2->lon;
                              $row2->ordering = $value2->ordering;
                              $row2->published = $value2->published;

                              \DB::table('location')->insert((array)$row2);
                            }
                          }

                        }
                      }

                      $data['message'][] = 'Category - Done';
                    }

                    // Charge
                    $sql = "SELECT *
                        FROM `{$dbPrefix}charge`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->note = $value->note;
                        $row->note_published = $value->note_published;
                        $row->type = $value->type;
                        $row->params = $value->params;
                        $row->value = $value->value;
                        $row->start_date = $value->start_date;
                        $row->end_date = $value->end_date;
                        $row->published = $value->published;

                        \DB::table('charge')->insert((array)$row);
                      }

                      $data['message'][] = 'Charge - Done';
                    }

                    // Restricted Areas
                    $sql = "SELECT *
                        FROM `{$dbPrefix}excluded_routes`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->direction = $value->direction;
                        $row->start_postcode = $value->start_postcode;
                        $row->end_postcode = $value->end_postcode;
                        $row->start_date = $value->start_date;
                        $row->end_date = $value->end_date;
                        $row->allowed = $value->allowed;
                        $row->vehicles = $value->vehicles;
                        $row->description = $value->description;
                        $row->modified_date = $value->modified_date;
                        $row->ordering = $value->ordering;
                        $row->published = $value->published;

                        \DB::table('excluded_routes')->insert((array)$row);
                      }

                      $data['message'][] = 'Restricted Areas - Done';
                    }

                    // Fixed prices
                    $prices = \App\Models\FixedPrice::where('site_id', $copySiteId)->get();

                    if (!empty($prices)) {
                        foreach ($prices as $k => $v) {
                            $new = $v->replicate();
                            $new->site_id = $rowProfile->id;
                            $new->save();

                            if (!empty($new->id)) {
                      					$zones = $v->zones()->get();
                      					$sync = [];
                      					foreach ($zones as $k => $v) {
                      							$sync[] = [
                      								'relation_type' => $v->pivot->relation_type,
                      								'relation_id' => $new->id,
                      								'target_id' => $v->pivot->target_id,
                      								'type' => $v->pivot->type
                      							];
                      					}
                      					$new->zones()->sync($sync);
                      			}
                        }

                        $data['message'][] = 'Fixed prices - Done';
                    }

                    // $sql = "SELECT *
                    //     FROM `{$dbPrefix}fixed_prices`
                    //     WHERE `site_id`='". $copySiteId ."'
                    //     ORDER BY `id` ASC";
                    // $query = $db->select($sql);
                    //
                    // if ( !empty($query) ) {
                    //   foreach($query as $key => $value) {
                    //     $row = new \stdClass();
                    //     $row->id = null;
                    //     $row->site_id = $rowProfile->id;
                    //     $row->type = $value->type;
                    //     $row->start_type = $value->start_type;
                    //     $row->start_postcode = $value->start_postcode;
                    //     $row->start_date = $value->start_date;
                    //     $row->direction = $value->direction;
                    //     $row->end_type = $value->end_type;
                    //     $row->end_postcode = $value->end_postcode;
                    //     $row->end_date = $value->end_date;
                    //     $row->value = $value->value;
                    //     $row->params = $value->params;
                    //     $row->modified_date = $value->modified_date;
                    //     $row->ordering = $value->ordering;
                    //     $row->published = $value->published;
                    //
                    //     \DB::table('fixed_prices')->insert((array)$row);
                    //   }
                    //
                    //   $data['message'][] = 'Fixed prices - Done';
                    // }

                    // Config
                    $sql = "SELECT *
                        FROM `{$dbPrefix}config`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        if ( $value->key == 'logo' ) {
                            if ( \Storage::disk('logo')->exists($value->value) ) {
                                $name = $value->value;
                                $name = \App\Helpers\SiteHelper::generateFilename('logo') .'.'. substr($name, strrpos($name, '.') + 1);
                                \Storage::disk('logo')->copy($value->value, $name);
                                $value->value = $name;
                            }
                        }

                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->key = $value->key;
                        $row->value = $value->value;
                        $row->type = $value->type;
                        $row->browser = $value->browser;

                        \DB::table('config')->insert((array)$row);
                      }

                      $data['message'][] = 'Config - Done';
                    }

                    // Discount
                    $sql = "SELECT *
                        FROM `{$dbPrefix}discount`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->code = $value->code;
                        $row->type = $value->type;
                        $row->value = $value->value;
                        $row->allowed_times = $value->allowed_times;
                        $row->used_times = $value->used_times;
                        $row->start_date = $value->start_date;
                        $row->end_date = $value->end_date;
                        $row->minimum_bookings = $value->minimum_bookings;
                        $row->description = $value->description;
                        $row->created_date = $value->created_date;
                        $row->published = $value->published;

                        \DB::table('discount')->insert((array)$row);
                      }

                      $data['message'][] = 'Config - Done';
                    }

                    // Meeting point
                    $sql = "SELECT *
                        FROM `{$dbPrefix}meeting_point`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->postcode = $value->postcode;
                        $row->meet_and_greet = $value->meet_and_greet;
                        $row->airport = $value->airport;
                        $row->description = $value->description;
                        $row->note = $value->note;
                        $row->modified_date = $value->modified_date;
                        $row->ordering = $value->ordering;
                        $row->published = $value->published;

                        \DB::table('meeting_point')->insert((array)$row);
                      }

                      $data['message'][] = 'Meeting point - Done';
                    }

                    // Payment
                    $sql = "SELECT *
                        FROM `{$dbPrefix}payment`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {

                        if ( $value->image ) {
                            if ( \Storage::disk('payments')->exists($value->image) ) {
                                $name = $value->image;
                                $name = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. substr($name, strrpos($name, '.') + 1);
                                \Storage::disk('payments')->copy($value->image, $name);
                                $value->image = $name;
                            }
                        }

                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->name = $value->name;
                        $row->description = $value->description;
                        $row->payment_page = $value->payment_page;
                        $row->image = $value->image;
                        $row->params = $value->params;
                        $row->method = $value->method;
                        $row->factor_type = $value->factor_type;
                        $row->price = $value->price;
                        $row->default = $value->default;
                        $row->ordering = $value->ordering;
                        $row->published = $value->published;
                        $row->is_backend = $value->is_backend;

                        \DB::table('payment')->insert((array)$row);
                      }

                      $data['message'][] = 'Payment - Done';
                    }

                    // Vehicle
                    $vehicleMap = array();

                    $sql = "SELECT *
                        FROM `{$dbPrefix}vehicle`
                        WHERE `site_id`='". $copySiteId ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {

                        if ( $value->image ) {
                            if ( \Storage::disk('vehicles-types')->exists($value->image) ) {
                                $name = $value->image;
                                $name = \App\Helpers\SiteHelper::generateFilename('vehicle_type') .'.'. substr($name, strrpos($name, '.') + 1);
                                \Storage::disk('vehicles-types')->copy($value->image, $name);
                                $value->image = $name;
                            }
                        }

                        $row = new \stdClass();
                        $row->id = null;
                        $row->site_id = $rowProfile->id;
                        $row->user_id = $value->user_id;
                        $row->service_ids = $value->service_ids;
                        $row->hourly_rate = $value->hourly_rate;
                        $row->name = $value->name;
                        $row->description = $value->description;
                        $row->disable_info = $value->disable_info;
                        $row->image = $value->image;
                        $row->max_amount = $value->max_amount;
                        $row->passengers = $value->passengers;
                        $row->luggage = $value->luggage;
                        $row->hand_luggage = $value->hand_luggage;
                        $row->child_seats = $value->child_seats;
                        $row->baby_seats = $value->baby_seats;
                        $row->infant_seats = $value->infant_seats;
                        $row->wheelchair = $value->wheelchair;
                        $row->factor_type = $value->factor_type;
                        $row->price = $value->price;
                        $row->default = $value->default;
                        $row->ordering = $value->ordering;
                        $row->published = $value->published;
                        $row->is_backend = $value->is_backend;

                        $row->id = \DB::table('vehicle')->insertGetId((array)$row);

                        // Map vehicles
                        $rowMap = new \stdClass();
                        $rowMap->old_id = $value->id;
                        $rowMap->new_id = $row->id;
                        $vehicleMap[] = $rowMap;
                      }

                      $data['message'][] = 'Vehicle - Done';
                    }


                    // Vehicle update - Fixed prices
                    $sql = "SELECT *
                        FROM `{$dbPrefix}fixed_prices`
                        WHERE `site_id`='". $rowProfile->id ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        if ( !empty($value) ) {
                          $params = json_decode($value->params);
                          $vehicle = array();

                          if ( !empty($params->vehicle) ) {
                            foreach($params->vehicle as $key1 => $value1) {
                              foreach($vehicleMap as $key2 => $value2) {
                                if ( $value1->id == $value2->old_id ) {
                                  $value1->id = $value2->new_id;
                                  $vehicle[] = $value1;
                                }
                              }
                            }
                          }

                          $params->vehicle = $vehicle;

                          $row = new \stdClass();
                          $row->id = $value->id;
                          $row->params = json_encode($params);

                          $results = \DB::table('fixed_prices')->where('id', $row->id)->update((array)$row);
                          $results = $row->id;
                        }
                      }

                      $data['message'][] = 'Vehicle update - Fixed prices - Done';
                    }

                    // Vehicle update - Config
                    $sql = "SELECT *
                            FROM `{$dbPrefix}config`
                            WHERE `site_id`='" . $rowProfile->id . "'
                            AND `key`='quote_distance_range'
                            LIMIT 1";
                    $query = $db->select($sql);
                    if (!empty($query[0])) {
                      $query = $query[0];
                    }

                    if ( !empty($query) ) {
                      $params = json_decode($query->value);

                      foreach($params as $key0 => $value0) {
                        $vehicle = array();

                        if ( !empty($value0->vehicle) ) {
                          foreach($value0->vehicle as $key1 => $value1) {
                            foreach($vehicleMap as $key2 => $value2) {
                              if ( $value1->id == $value2->old_id ) {
                                $value1->id = $value2->new_id;
                                $vehicle[] = $value1;
                              }
                            }
                          }
                        }

                        $value0->vehicle = $vehicle;
                        $params[$key0] = $value0;
                      }

                      $row = new \stdClass();
                      $row->id = (int)$query->id;
                      $row->value = json_encode($params);

                      $results = \DB::table('config')->where('id', $row->id)->update((array)$row);
                      $results = $row->id;

                      $data['message'][] = 'Vehicle update - Config - Done';
                    }

                    // Vehicle update - Restricted Areas
                    $sql = "SELECT *
                        FROM `{$dbPrefix}excluded_routes`
                        WHERE `site_id`='". $rowProfile->id ."'
                        ORDER BY `id` ASC";
                    $query = $db->select($sql);

                    if ( !empty($query) ) {
                      foreach($query as $key => $value) {
                        $vehicles = explode(',', $value->vehicles);
                        $vehicle = array();

                        if ( !empty($vehicles) ) {
                          foreach($vehicles as $key1 => $value1) {
                            foreach($vehicleMap as $key2 => $value2) {
                              if ( $value1 == $value2->old_id ) {
                                $vehicle[] = $value2->new_id;
                              }
                            }
                          }
                        }

                        $row = new \stdClass();
                        $row->id = (int)$value->id;
                        $row->vehicles = implode(',', $vehicle);

                        $results = \DB::table('excluded_routes')->where('id', $row->id)->update((array)$row);
                        $results = $row->id;
                      }

                      $data['message'][] = 'Vehicle update - Restricted Areas - Done';
                    }

                  }

                  $data['success'] = true;
                }
                else {
                  $data['message'][] = 'No profile was found!';
                }
              }
          }

          clear_cache('cache');

    break;
}
