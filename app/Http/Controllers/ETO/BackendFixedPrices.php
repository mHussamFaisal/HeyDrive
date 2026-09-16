<?php
use App\Helpers\SiteHelper;

$fixedPricesHelper = new App\Helpers\FixedPricesHelper($siteId);
$typeList = $fixedPricesHelper->typeList;
$vehicleList = $fixedPricesHelper->getVehicleList();
// Services List
$services = $fixedPricesHelper->getServices();

switch($action) {
	case 'init':
			$postcodeList = array();
			$categoryIds = array();
			// Categories
			$categories = App\Models\Category::where('site_id', $siteId)->get();

			if ( !empty($categories) ) {
				// Locations
				foreach($categories as $k => $v) {
					$categoryIds[] = $v->id;
				}

				if ( !empty($categoryIds) ) {
					// Locations
					$postcodeList = $fixedPricesHelper->getLocationPostcodeList($categoryIds, false, "location.address", "location.name");
				}
			}

			// Add custom tags to list
			$postcodeList = $fixedPricesHelper->getCustomLocationList($postcodeList);

			$data['servicesList'] = $fixedPricesHelper->getServiceNames($services);
			$data['typeList'] = $typeList;
			$data['vehicleList'] = $vehicleList;
			$data['postcodeList'] = $postcodeList;
			$data['zoneList'] = $fixedPricesHelper->getLocationList();
			$data['success'] = true;
	break;
	case 'list':
    if (!auth()->user()->hasPermission('admin.fixed_prices.index')) {
        return redirect_no_permission();
    }

		// Convert start
		$data['list'] = [];
		$searchData = $etoPost['search']['value'];
		parse_str($searchData, $output);
		// $data['outputData'] = $output;

		if (!empty($etoPost['columns'])) {
				foreach ($etoPost['columns'] as $k => $v) {
						if ($etoPost['columns'][$k]['data'] == 'is_zone_text') {
								$etoPost['columns'][$k]['data'] = 'is_zone';
						}
				}
		}

		$sort = dt_parse_sort($etoPost);
		// dd($etoPost, $sort);

		$start = (int)$etoPost['start'];
		$limit = (int)$etoPost['length'];
		//$page = (int) $etoPost['page'];
		// Location name
		$locations = $fixedPricesHelper->getLocationPostcodeList([], true, "location.address", "location.name");
		// Total
		$data['recordsTotal'] = $fixedPricesHelper->getFixedPrices($etoPost, $start, $limit);
		// Filtered
		$data['recordsFiltered'] = $fixedPricesHelper->getFixedPrices($etoPost, $start, $limit, 'filteredCount');
		// List
		$fixPrices = $fixedPricesHelper->getFixedPrices($etoPost, $start, $limit, 'list', true);

		if ( !empty($fixPrices) ) {
			$list = array();

			foreach($fixPrices as $k => $v) {
				$serviceNames = '';
				if ( !empty($v->service_ids) ) {
					$service_ids = json_decode($v->service_ids);
					$i = 0;
					foreach ($services as $k2 => $v2) {
						if ( in_array($v2->id, $service_ids) ) {
							$serviceNames .= '<div>';
							$serviceNames .= $v2->name;
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

				$type = 'Unknown';
				if ( !empty($typeList) ) {
					foreach($typeList as $k2 => $v2) {
						if ( $v->type == $v2['value'] ) {
							$type = $v2['text'];
							break;
						}
					}
				}

				if ($v->is_zone == 0) {
//							if ($v->start_type == 1) {
//								$start_type = '<span style="color:red;">Exclude:</span>';
//							} else {
//								$start_type = '<span style="color:green;">Include:</span>';
//							}
					$start_type = '';

					$temp = explode(',', $v->start_postcode);
					foreach ($temp as $k1 => $v1) {
						$temp[$k1] = $fixedPricesHelper->getLocationNameByAddress($locations, $v1, 'text', 'value');
					}
					$start_postcode = '' . $start_type . ' ' . implode(', ', $temp);
				}
				else {
					$start_postcode = [];
					$zones = $v->zones('from')->get();
					foreach( $zones as $zone ) {
						$start_postcode[] = $zone->name;
					}
					$start_postcode = !empty($start_postcode) ?implode('<br>', $start_postcode) : 'All';
				}

				$start_date = '';
				if ( $v->start_date ) {
					$start_date = SiteHelper::formatDateTime($v->start_date);
				}

				$direction = $v->direction == 1 ? 'From -> To' : 'Both Ways';

				if ($v->is_zone == 0) {
//							if ( $v->end_type == 1 ) {
//								$end_type = '<span style="color:red;">Exclude:</span>';
//							}
//							else {
//								$end_type = '<span style="color:green;">Include:</span>';
//							}
					$end_type = '';

					$temp = explode(',', $v->end_postcode);
					foreach($temp as $k1 => $v1) {
						$temp[$k1] = $fixedPricesHelper->getLocationNameByAddress($locations, $v1, 'text', 'value');
					}
					$end_postcode = ''. $end_type .' '. implode(', ', $temp);
				}
				else {
					$end_postcode = [];
					$zones = $v->zones('to')->get();
					foreach( $zones as $zone ) {
						$end_postcode[] = $zone->name;
					}
					$end_postcode = !empty($end_postcode) ?implode('<br>', $end_postcode) : 'All';
				}

				$end_date = '';
				if ( $v->end_date ) {
					$end_date = SiteHelper::formatDateTime($v->end_date);
				}

				$display_type = $v->type == 1 ? 0 : 1;
				$priceList = '';
				$depositList = '';

				$params = json_decode($v->params);

				if ( !empty($params) ) {
					$price = (float)$v->value;
					$price_type = 0;
					$price_type_name = 'Multiply';
					$price_type_prefix = '*';

					if ( isset($params->factor_type) ) {
						if ( $params->factor_type == 2 ) {
							$price_type = 2;
							$price_type_name = 'Override';
							$price_type_prefix = '=';
						}
						elseif ( $params->factor_type == 1 ) {
							$price_type = 1;
							$price_type_name = 'Add';
							$price_type_prefix = '+';
						}
					}

					$deposit = !empty($params->deposit) ? (float)$params->deposit : 0;
					$deposit_type = 0;
					$deposit_type_name = 'Percent';
					$deposit_type_prefix = '';
					$deposit_type_suffix = '%';

					if ( config('site.fixed_prices_deposit_type') == 1 ) {
						$deposit_type = 1;
						$deposit_type_name = 'Flat';
						$deposit_type_prefix = '=';
						$deposit_type_suffix = '';
					}

					$priceList .= '<table style="border:0; font-size:12px;">';
					$depositList .= '<table style="border:0; font-size:12px;">';

					if ( $display_type ) {
						$priceList .= '<tr><td style="color:#888; padding-right:10px;">Base price:</td><td>'. $price .'</td></tr>';
						$priceList .= '<tr><td style="color:#888; padding-right:10px;">Action:</td><td>'. $price_type_name .'</td></tr>';

						$depositList .= '<tr><td style="color:#888; padding-right:10px;">Base deposit:</td><td>'. $deposit .'</td></tr>';
						$depositList .= '<tr><td style="color:#888; padding-right:10px;">Action:</td><td>'. $deposit_type_name .'</td></tr>';
					}

					if ( isset($params->vehicle) ) {
						foreach($params->vehicle as $k1 => $v1) {
							// Vehicle name
							$name = 'Unknown';
							foreach($vehicleList as $kV => $vV) {
								if ( $v1->id == $vV['id'] ) {
									$name = $vV['name'];
									break;
								}
							}

							// Price
							if ( $price_type == 2 ) {
								$vP = $v1->value;
							}
							elseif ( $price_type == 1 ) {
								$vP = $price + $v1->value;
							}
							else {
								$vP = $price * $v1->value;
							}

							if ( $display_type ) {
								$text = $price_type_prefix . (!empty($v1->value) ? $v1->value : 0);
							}
							else {
								$text = SiteHelper::formatPrice($vP, 2, 1);
							}

							$priceList .= '<tr><td style="color:#888; padding-right:10px;">'. $name .':</td><td>'. $text .'</td></tr>';

							// Deposit
							if ( !empty($v1->deposit) ) {
								$deposit = (float)$v1->deposit;
							}

							if ( $deposit_type == 1 ) {
								$vD = $deposit;
							}
							else {
								$vD = ($vP / 100) * $deposit;
							}

							if ( $display_type ) {
								$text = $deposit_type_prefix . (!empty($v1->value) ? $v1->deposit : 0) . $deposit_type_suffix;
							}
							else {
								$text = SiteHelper::formatPrice($vD, 2, 1);
							}

							$depositList .= '<tr><td style="color:#888; padding-right:10px;">'. $name .':</td><td>'. $text .'</td></tr>';
						}
					}

					$priceList .= '</table>';
					$depositList .= '</table>';
				}

				$modified_date = '';
				if ( $v->modified_date ) {
					$modified_date = SiteHelper::formatDateTime($v->modified_date);
				}

				$row = array();
				$row['id'] = $v->id;
				$row['site_id'] = $v->site_id;
				$row['is_zone'] = $v->is_zone;
				$row['is_zone_text'] = $v->is_zone ? 'Zone' : 'Postcode';
				$row['service_ids'] = $serviceNames;
				$row['type'] = $type;
				$row['start_type'] = $start_type;
				$row['start_postcode'] = '<div style="white-space:pre-line; min-width:150px;">'. $start_postcode .'</div>';
				$row['start_date'] = $start_date;
				$row['direction'] = $direction;
				$row['end_type'] = $end_type;
				$row['end_postcode'] = '<div style="white-space:pre-line; min-width:150px;">'. $end_postcode .'</div>';
				$row['end_date'] = $end_date;
				$row['price'] = $priceList;
				$row['deposit'] = $depositList;
				$row['modified_date'] = $modified_date;
				$row['ordering'] = $v->ordering;
				$row['published'] = !empty($v->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

				$list[] = $row;
			}

			$data['list'] = $list;
			$data['success'] = true;
		}
		else {
			$data['message'][] = 'No fixed prices were found!';
			$data['success'] = true;
		}

	break;
	case 'read':

    if (!auth()->user()->hasPermission('admin.fixed_prices.show')) {
        return redirect_no_permission();
    }

    $id = (int)$etoPost['id'];
		$query = \App\Models\FixedPrice::findOrFail($id);

		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ($query->is_zone == 0) {
			if ($query->start_postcode == 'ALL') {
				$query->start_postcode = '';
			}
			if ($query->end_postcode == 'ALL') {
				$query->end_postcode = '';
			}
		}
		elseif ($query->is_zone == 1) {
			$startZones = [];
			$endZones = [];
			$start = $query->zones('from')->get();
			foreach ($start as $zone) {
				$startZones[] = $zone->id;
			}
			$end = $query->zones('to')->get();
			foreach ($end as $zone) {
				$endZones[] = $zone->id;
			}
		}

		if ( !empty($query) )
		{
			if ( !empty($query->service_ids) ) {
				$service_ids = json_decode($query->service_ids);
			}
			else {
				$service_ids = [];
			}

			$row = array(
				'id' => $query->id,
				'site_id' => $query->site_id,
				'is_zone' => $query->is_zone,
				'service_ids' => $service_ids,
				'type' => $query->type,
				'start_type' => $query->start_type,
				'start_postcode' => explode(',', $query->start_postcode),
				'start_zone' => $startZones,
				'start_date' => $query->start_date ? date('Y-m-d H:i', strtotime($query->start_date)) : '',
				'direction' => $query->direction,
				'end_type' => $query->end_type,
				'end_postcode' => explode(',', $query->end_postcode),
				'end_zone' => $endZones,
				'end_date' => $query->end_date ? date('Y-m-d H:i', strtotime($query->end_date)) : '',
				'value' => $query->value,
				'params' => json_decode($query->params),
				'ordering' => $query->ordering,
				'published' => $query->published
			);

			$data['record'] = $row;
			$data['success'] = true;
		}
		else {
			$data['message'][] = $gLanguage['API']['ERROR_NO_FixedPrices'];
		}

	break;
	case 'update':

    if (!auth()->user()->hasPermission('admin.fixed_prices.edit')) {
        return redirect_no_permission();
    }

    if ( !empty($etoPost['service_ids']) ) {
			$service_ids = json_encode((array)$etoPost['service_ids']);
		}
		else {
			$service_ids = null;
		}

		$id = (int)$etoPost['id'];
		$site_id = (int)$etoPost['site_id'];
		$isZone = (int)$etoPost['is_zone'];
		$type = (int)$etoPost['type'];
		$start_type = (int)$etoPost['start_type'];
		$start_postcode = (array)$etoPost['start_postcode'];
		$start_date = (string)$etoPost['start_date'];
		$direction = (int)$etoPost['direction'];
		$end_type = (int)$etoPost['end_type'];
		$end_postcode = (array)$etoPost['end_postcode'];
		$end_date = (string)$etoPost['end_date'];
		$value = (float)$etoPost['value'];

		$params = array();

		if ( in_array($type, array(1,2)) ) {
			$factor_type = (int)$etoPost['factor_type'];
			if ( isset($factor_type) ) {
				$params['factor_type'] = $factor_type;
			}

			$deposit = (float)$etoPost['deposit'];
			if ( isset($deposit) ) {
				$params['deposit'] = $deposit;
			}
		}

		if ( in_array($type, array(1,2)) ) {
			$sql = "SELECT `id`, `name`
							FROM `{$dbPrefix}vehicle`
							WHERE `site_id`='". $siteId ."'
							ORDER BY `ordering` ASC";
			$query = $db->select($sql);

			$temp = array();
			if ( !empty($query) ) {
				foreach($query as $k => $v) {
					$temp[] = array(
						'id' => (int)$v->id,
						'value' => (float)$etoPost['vehicle_'. $v->id],
						'deposit' => (float)$etoPost['vehicle_deposit_'. $v->id]
					);
				}
			}

			if ( !empty($temp) ) {
				$params['vehicle'] = $temp;
			}
		}

		$params = json_encode($params);
		$ordering = (int)$etoPost['ordering'];
		$published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

		// $temp = json_decode($start_postcode, true);
		$temp = $start_postcode;
		$temp = array_map('trim', $temp);
		asort($temp);
		$start_postcode = implode(',', $temp);
		if ( in_array('ALL', $temp) || empty($start_postcode) ) {
			$start_postcode = 'ALL';
		}

		// $temp = json_decode($end_postcode, true);
		$temp = $end_postcode;
		$temp = array_map('trim', $temp);
		asort($temp);
		$end_postcode = implode(',', $temp);
		if ( in_array('ALL', $temp) || empty($end_postcode) ) {
			$end_postcode = 'ALL';
		}

		$sql = "SELECT `id`
						FROM `{$dbPrefix}fixed_prices`
						WHERE `site_id`='". $siteId ."'
						AND (
							(
								`start_postcode`='". $start_postcode ."'
									AND
								`end_postcode`='". $end_postcode ."'
							)
							OR
							(
								`start_postcode`='". $end_postcode ."'
									AND
								`end_postcode`='". $start_postcode ."'
							)
						) ";

		if ( $id > 0 ) {
			$sql .= "AND `id` != '". $id ."' ";
		}

		$sql .= "LIMIT 1";

		$results = $db->select($sql);
		if (!empty($results[0])) {
				$results = $results[0];
		}

		if ($isZone == 1 && empty((array)$etoPost['start_zone']) && empty((array)$etoPost['end_zone'])) {
				$data['message'][] = 'Zones ALL to ALL are not allowed.';
				$data['success'] = false;
		}
		elseif ($isZone == 0 && $start_postcode == 'ALL' && $end_postcode == 'ALL') {
				$data['message'][] = 'Postcodes ALL to ALL are not allowed.';
				$data['success'] = false;
		}
		else if ( $results && false ) {
				$data['message'][] = 'This price already exists!';
				$data['success'] = false;
		}
		else {
			$row = new \stdClass();
			$row->id = null;
			$row->site_id = ($site_id) ? $site_id : (int)$siteId;
			$row->service_ids = $service_ids;
			$row->is_zone = $isZone;
			$row->type = $type;
			$row->start_type = $isZone == 0 ? $start_type : null;
			$row->start_postcode = $isZone == 0 ? strtoupper(trim($start_postcode)) : null;
			$row->start_date = $start_date ?: null;
			$row->direction = $direction;
			$row->end_type = $isZone == 0 ? $end_type : null;
			$row->end_postcode = $isZone == 0 ? strtoupper(trim($end_postcode)) : null;
			$row->end_date = $end_date ?: null;
			$row->value = $value;
			$row->params = $params;
			$row->modified_date = null;
			$row->ordering = $ordering;
			$row->published = $published;

			$sql = "SELECT `id`
							FROM `{$dbPrefix}fixed_prices`
							WHERE `site_id`='". $siteId ."'
							AND `id`='". $id ."'
							LIMIT 1";
			$query = $db->select($sql);
			if (!empty($query[0])) {
				$query = $query[0];
			}

			if ( !empty($query) ) {
				$row->id = $query->id;
				$row->modified_date = date('Y-m-d H:i:s');

				$results = \DB::table('fixed_prices')->where('id', $row->id)->update((array)$row);
				$results = $row->id;
			}
			else {
				$results = \DB::table('fixed_prices')->insertGetId((array)$row);
				$row->id = $results;
			}

			// Zones
			$fixedPrice = \App\Models\FixedPrice::findOrFail($row->id);

			$zones = $isZone == 1 ? (array)$etoPost['start_zone'] : [];
			$sync = [];
			foreach ($zones as $key => $value) {
				$sync[$value] = [
					'relation_type' => 'fixed_price_location',
					'relation_id' => $row->id,
					'target_id' => $value,
					'type' => 'from'
				];
			}
			$fixedPrice->zones('from')->sync($sync);

			$zones = $isZone == 1 ? (array)$etoPost['end_zone'] : [];
			$sync = [];
			foreach ($zones as $key => $value) {
				$sync[$value] = [
					'relation_type' => 'fixed_price_location',
					'relation_id' => $row->id,
					'target_id' => $value,
					'type' => 'to'
				];
			}
			$fixedPrice->zones('to')->sync($sync);
			// !Zones

			$data['success'] = true;
		}

	break;
	case 'destroy':

	    if (!auth()->user()->hasPermission('admin.fixed_prices.destroy')) {
	        return redirect_no_permission();
	    }

	    $id = (int)$etoPost['id'];
			$fixedPrice = \App\Models\FixedPrice::findOrFail($id);
			$fixedPrice->zones()->detach();
			$result = $fixedPrice->delete();

			if ($result) {
					$data['success'] = true;
			}
			else {
					$data['message'][] = $gLanguage['API']['ERROR_NO_Fixed_Prices'];
			}

	break;
	case 'copy':

			if (!auth()->user()->hasPermission('admin.fixed_prices.create')) {
					return redirect_no_permission();
			}

			$id = (int)$etoPost['id'];
			$fixedPrice = \App\Models\FixedPrice::findOrFail($id);
			$new = $fixedPrice->replicate();
			$result = $new->save();

			if (!empty($new->id)) {
					$zones = $fixedPrice->zones()->get();
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

			if ($result) {
					$data['success'] = true;
			}
			else {
					$data['message'][] = $gLanguage['API']['ERROR_NO_Fixed_Prices'];
			}

	break;
}
