<?php
use App\Helpers\SiteHelper;

if ( !empty($etoPost['siteId']) ) {
		$siteId = (int) $etoPost['siteId'];
}

// Config
$gConfig = array();

$sql = "SELECT `key`, `value`
				FROM `{$dbPrefix}config`
				WHERE `site_id`='". $siteId ."'
				ORDER BY `key` ASC";

$resultsConfig = $db->select($sql);

if ( !empty($resultsConfig) ) {
	foreach($resultsConfig as $key => $value) {
		$gConfig[$value->key] = $value->value;
	}
} else {
	// $data['message'][] = 'No global config was found!';
}


// Source list
$sourceList = config('eto_booking.sources');

$sql = "SELECT `id`, `name`
				FROM `{$dbPrefix}profile`
				WHERE 1 /*`id`='" . $siteId . "'*/
				AND `published`='1'
				ORDER BY `ordering` ASC";

$resultsProfile = $db->select($sql);

if (!empty($resultsProfile)) {
		foreach($resultsProfile as $key => $value) {
				$sourceList[] = $value->name;
		}
}

switch($action)
{
		case 'init':

				$loadFilter = (string)$etoPost['loadFilter'];

				if ( empty($loadFilter) ) {
						$loadFilter = 'all';
				}

				if ( in_array($loadFilter, array('all', 'services')) ) {
						$data['services'] = \App\Helpers\BookingHelper::getServiceList();
				}

				if ( in_array($loadFilter, array('all', 'source')) ) {
						$data['source'] = \App\Helpers\BookingHelper::getSourceList();
				}

				if ( in_array($loadFilter, array('all', 'status')) ) {
						$data['status'] = (new \App\Models\BookingRoute)->getStatusList();
				}

				if ( in_array($loadFilter, array('all', 'payment_method')) ) {
						$data['payment_method'] = \App\Helpers\BookingHelper::getPaymentMethodList();
				}

				if ( in_array($loadFilter, array('all', 'payment_status')) ) {
						$data['payment_status'] = (new \App\Models\Transaction)->getStatusList();
				}

				if ( in_array($loadFilter, array('all', 'driver')) ) {
						$data['driver'] = \App\Helpers\BookingHelper::getDriverList();
				}

				if ( in_array($loadFilter, array('all', 'customer')) ) {
						$data['customer'] = \App\Helpers\BookingHelper::getCustomerList();
				}

				if ( in_array($loadFilter, array('all', 'dateType')) ) {
						$data['dateType'] = \App\Helpers\BookingHelper::getDateTypeList();
				}

				$data['success'] = true;

		break;
		case 'list':

				if (!auth()->user()->hasPermission(['admin.bookings.index', 'admin.dispatch.index'])) {
						return redirect_no_permission();
				}

				// Convert start
				$data['bookings'] = [];

				$page_type = isset($etoPost['page']) ? $etoPost['page'] : '';
				$searchData = $etoPost['search']['value'];
				parse_str($searchData, $output);
				// $data['outputData'] = $output;

				$etoPost['searchFilterStatus'] = json_encode(isset($output['filter-status']) ? $output['filter-status'] : []);
				$etoPost['searchFilterPaymentMethod'] = json_encode(isset($output['filter-payment_method']) ? $output['filter-payment_method'] : []);
				$etoPost['searchFilterPaymentStatus'] = json_encode(isset($output['filter-payment_status']) ? $output['filter-payment_status'] : []);
				$etoPost['searchFilterScheduledRouteId'] = isset($output['filter-scheduled_route_id']) ? (int)$output['filter-scheduled_route_id'] : 0;
				$etoPost['searchFilterParentBookingId'] = isset($output['filter-parent_booking_id']) ? (int)$output['filter-parent_booking_id'] : 0;
				$etoPost['searchFilterServiceId'] = json_encode(isset($output['filter-service_id']) ? $output['filter-service_id'] : []);
				$etoPost['searchFilterSource'] = json_encode(isset($output['filter-source']) ? $output['filter-source'] : []);
				$etoPost['searchFilterDriverName'] = json_encode(isset($output['filter-driver-name']) ? $output['filter-driver-name'] : []);
				$etoPost['searchFilterCustomerName'] = json_encode(isset($output['filter-customer-name']) ? $output['filter-customer-name'] : []);
				$etoPost['searchFilterFleetName'] = json_encode(isset($output['filter-fleet-name']) ? $output['filter-fleet-name'] : []);

				$valueDate = '';
				if ( isset($output['filter-start-date']) && $output['filter-start-date'] != '' ) {
					$valueDate = date('Y-m-d H:i:s', strtotime($output['filter-start-date']));
				}
				$etoPost['searchFilterStartDate'] = $valueDate;

				$valueDate = '';
				if ( isset($output['filter-end-date']) && $output['filter-end-date'] != '' ) {
					$valueDate = date('Y-m-d H:i:s', strtotime($output['filter-end-date']));
				}
				$etoPost['searchFilterEndDate'] = $valueDate;

				$etoPost['searchFilterDateType'] = !empty($output['filter-date-type']) ? $output['filter-date-type'] : 'date';
				$etoPost['summary'] = isset($output['filter-summary']) ? 'true' : 'false';
				// $etoPost['exportType'] = isset($output['filter-export-type']) ? $output['filter-export-type'] : '';
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


				// Delete incomplete bookings - start
				if ($page_type != 'trash' &&
						!empty($gConfig['incomplete_bookings_delete_enable']) &&
						!empty($gConfig['incomplete_bookings_delete_after']) &&
						empty(\Cache::store('file')->get('admin_check_incomplete_bookings'))
				) {
						\Cache::store('file')->put('admin_check_incomplete_bookings', 1, config('eto_booking.admin_check_incomplete_bookings_cache_time'));
						$limitTime = date('Y-m-d H:i:s', time() - ($gConfig['incomplete_bookings_delete_after'] * 60 * 60) ); // hours; 60 mins; 60secs

						$deleteBookings = \App\Models\BookingRoute::with([
								'booking',
								'bookingTransactions'
						])
						->whereHas('booking', function ($query) use ($siteId) {
						    $query->where('site_id', $siteId);
						})
						->where('status', 'incomplete')
						->where('created_date', '<=', $limitTime)
						->get();

						foreach ($deleteBookings as $bookingRoute) {
								if (!empty($bookingRoute->id)) {
										// Remove child bookings
										$childBookings = $bookingRoute->childBookings()->with(['booking', 'bookingTransactions'])->get();

										foreach ($childBookings as $child) {
												if ($child->bookingTransactions->count() <= 1) {
														foreach($child->bookingTransactions as $transaction) {
																$transaction->delete();
														}
														$child->booking->delete();
												}
												$child->delete();
										}

										// Remove booking if there is no routes
										if ($bookingRoute->bookingAllRoutes->count() <= 1) {
												foreach($bookingRoute->bookingTransactions as $transaction) {
														$transaction->delete();
												}
												$bookingRoute->booking->delete();
										}

										$bookingRoute->delete();
								}
						}

						// $data['nowTime'] = date('Y-m-d H:i:s');
						// $data['limitTime'] = $limitTime;
				}
				// Delete incomplete bookings - end


				// Sort and limit
				$sort    = json_decode($etoPost['sort']);
				$start   = (int) $etoPost['start'];
				$limit   = (int) $etoPost['length'];
				// $page    = (int) $etoPost['page'];
				$sqlSort = '';

				if (!empty($sort)) {
					foreach($sort as $key => $value) {
						$property = '';

						switch((string) $value->property)	{
							case 'id':
								$property = '`a`.`id`';
								break;
							case 'ref_number':
								$property = '`a`.`ref_number`';
								break;
							case 'route':
								$property = '`a`.`route`';
								break;
							case 'date':
								$property = '`a`.`date`';
								break;
							case 'from':
								$property = '`a`.`address_start`';
								break;
							case 'to':
								$property = '`a`.`address_end`';
								break;
							case 'vehicle':
								$property = '`a`.`vehicle_list`';
								break;
							case 'meet_and_greet':
								$property = '`a`.`meet_and_greet`';
								break;
							case 'contact_title':
								$property = '`a`.`contact_title`';
								break;
							case 'contact_name':
								$property = '`a`.`contact_name`';
								break;
							case 'contact_email':
								$property = '`a`.`contact_email`';
								break;
							case 'contact_mobile':
								$property = '`a`.`contact_mobile`';
								break;
							case 'lead_passenger_title':
								$property = '`a`.`lead_passenger_title`';
								break;
							case 'lead_passenger_name':
								$property = '`a`.`lead_passenger_name`';
								break;
							case 'lead_passenger_email':
								$property = '`a`.`lead_passenger_email`';
								break;
							case 'lead_passenger_mobile':
								$property = '`a`.`lead_passenger_mobile`';
								break;
							case 'driver_id':
								$property = '`a`.`driver_id`';
							break;
							case 'driver_btn':
								$property = '`a`.`driver_id`';
							break;
							case 'vehicle_id':
								$property = '`a`.`vehicle_id`';
							break;
							case 'vehicle_btn':
								$property = '`a`.`vehicle_id`';
							break;
							case 'fleet_id':
								$property = '`a`.`fleet_id`';
							break;
							case 'fleet_btn':
								$property = '`a`.`fleet_id`';
							break;
							case 'fleet_commission_btn':
								$property = '`a`.`fleet_commission`';
							break;
							case 'commission_btn':
								$property = '`a`.`commission`';
							break;
							case 'cash_btn':
								$property = '`a`.`cash`';
							break;
							case 'flight_number':
								$property = '`a`.`flight_number`';
							break;
							case 'flight_landing_time':
								$property = '`a`.`flight_landing_time`';
							break;
							case 'departure_city':
								$property = '`a`.`departure_city`';
							break;
							case 'departure_flight_number':
								$property = '`a`.`departure_flight_number`';
							break;
							case 'departure_flight_time':
								$property = '`a`.`departure_flight_time`';
							break;
							case 'departure_flight_city':
								$property = '`a`.`departure_flight_city`';
							break;
							case 'waiting_time':
								$property = '`a`.`waiting_time`';
							break;
							//case 'user_name':
							//	$property = '`a`.`user_name`';
							//break;
							case 'price':
								$property = '`a`.`total_price`';
							break;
							case 'discount':
								$property = '`a`.`discount`';
							break;
							case 'discount_code':
								$property = '`a`.`discount_code`';
							break;
							case 'status_btn':
								$property = '`a`.`status`';
							break;
							case 'modified_date':
								$property = '`a`.`modified_date`';
							break;
							case 'created_date':
								$property = '`a`.`created_date`';
							break;
							case 'service_id':
								$property = '`a`.`service_id`';
							break;
							case 'service_duration':
								$property = '`a`.`service_duration`';
							break;
							case 'scheduled_route_id':
								$property = '`a`.`scheduled_route_id`';
							break;
							case 'source':
								$property = '`a`.`source`';
							break;
							case 'is_read_formatted':
								$property = '`a`.`is_read`';
							break;
							case 'custom':
								$property = '`a`.`custom`';
							break;
							case 'department':
								$property = '`a`.`department`';
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
						$sqlSearch .= " AND (`a`.`ref_number` LIKE '%" . $searchText . "%'".
								" OR BINARY `a`.`date` LIKE BINARY '%" . $searchText . "%'".
								" OR `a`.`source_details` LIKE '%" . $searchText . "%'".
								" OR `a`.`address_start` LIKE '%" . $searchText . "%'".
								" OR `a`.`address_end` LIKE '%" . $searchText . "%'".
								" OR `a`.`flight_number` LIKE '%" . $searchText . "%'".
								" OR `a`.`flight_landing_time` LIKE '%" . $searchText . "%'".
								" OR `a`.`departure_city` LIKE '%" . $searchText . "%'".
								" OR `a`.`departure_flight_number` LIKE '%" . $searchText . "%'".
								" OR `a`.`departure_flight_time` LIKE '%" . $searchText . "%'".
								" OR `a`.`departure_flight_city` LIKE '%" . $searchText . "%'".
								" OR `a`.`contact_name` LIKE '%" . $searchText . "%'".
								" OR `a`.`contact_email` LIKE '%" . $searchText . "%'".
								" OR `a`.`contact_mobile` LIKE '%" . $searchText . "%'".
								" OR `a`.`lead_passenger_name` LIKE '%" . $searchText . "%'".
								" OR `a`.`lead_passenger_email` LIKE '%" . $searchText . "%'".
								" OR `a`.`lead_passenger_mobile` LIKE '%" . $searchText . "%'".
								" )";
				}

				// Scheduled Route Id
				$searchFilterScheduledRouteId = $etoPost['searchFilterScheduledRouteId'];
				$sqlSearchFilterScheduledRouteId = '';

				if (!empty($searchFilterScheduledRouteId)) {
						$sqlSearchFilterScheduledRouteId = " AND (`a`.`scheduled_route_id` = '". $searchFilterScheduledRouteId ."') ";
				}

				// Parent Booking Id
				$searchFilterParentBookingId = $etoPost['searchFilterParentBookingId'];
				$sqlSearchFilterParentBookingId = '';

				if (!empty($searchFilterParentBookingId)) {
						$sqlSearchFilterParentBookingId = " AND (`a`.`parent_booking_id` = '". $searchFilterParentBookingId ."') ";
				}
				else {
						$booking_types = isset($output['filter-booking_type']) ? $output['filter-booking_type'] : [];

						if (in_array('parent', $booking_types) && in_array('child', $booking_types)) {
								// All bookings
						}
						elseif (in_array('child', $booking_types)) {
								$sqlSearchFilterParentBookingId = " AND (`a`.`parent_booking_id` > '0') ";
						}
						else {
								$sqlSearchFilterParentBookingId = " AND (`a`.`parent_booking_id` = '0') ";
						}
				}

				// Service Type
				$searchFilterServiceId    = json_decode($etoPost['searchFilterServiceId']);
				$sqlSearchFilterServiceId = '';

				if (!empty($searchFilterServiceId)) {
					foreach($searchFilterServiceId as $key => $value) {
						if (!empty($sqlSearchFilterServiceId)) {
							$sqlSearchFilterServiceId .= ' OR ';
						}
						// if (!empty($value)) {
							$sqlSearchFilterServiceId .= '`a`.`service_id` LIKE \'' . (int) $value . '\'';
						// }
					}
					if (!empty($sqlSearchFilterServiceId)) {
						$sqlSearchFilterServiceId = ' AND (' . $sqlSearchFilterServiceId . ') ';
					}
				}

				// Source
				// $searchFilterSource    = json_decode($etoPost['searchFilterSource']);
				$searchFilterSource    = json_decode($etoPost['searchFilterSource']);
				$sqlSearchFilterSource = '';
				if (!empty($searchFilterSource)){
					foreach($searchFilterSource as $key => $value){
						if (!empty($sqlSearchFilterSource)){
							$sqlSearchFilterSource .= ' OR ';
						}
						if (!empty($value)){
							$sqlSearchFilterSource .= '`a`.`source` LIKE \'' . (string) $value . '\'';
						}
					}
					if (!empty($sqlSearchFilterSource)){
						$sqlSearchFilterSource = ' AND (' . $sqlSearchFilterSource . ') ';
					}
				}

				// Status
				$searchFilterStatus = json_decode($etoPost['searchFilterStatus']);

				if ( empty($sqlSort) ) {
						if ( $page_type == 'next24' ) {
								$sqlSort .= '`a`.`date` ASC, ';
						}
						else if ( $page_type == 'latest' ) {
								// $sqlSort .= '`a`.`date` ASC, ';
						}
						else {
								$sqlSort .= '`a`.`created_date` DESC, ';
						}
				}

				if ( empty($searchFilterStatus) ) {
						switch( $page_type ) {
							case 'next24':
									$searchFilterStatus = [
											'pending',
											'confirmed',
											'assigned',
											'auto_dispatch',
											'accepted',
											'rejected',
											'onroute',
											'arrived',
											'onboard',
											'quote',
											'requested',
									];
							break;
							case 'latest':
									$searchFilterStatus = [
											'pending',
											'confirmed',
											'assigned',
											'auto_dispatch',
											'accepted',
											'rejected',
											'onroute',
											'arrived',
											'onboard',
											'quote',
									];
							break;
							case 'requested':
									$searchFilterStatus = [
											'requested',
									];
							break;
							case 'completed':
									$searchFilterStatus = [
											'completed',
									];
							break;
							case 'canceled':
									$searchFilterStatus = [
											'canceled',
											'unfinished',
									];
							break;
						}
				}

				$sqlSearchFilterStatus = '';
				if (!empty($searchFilterStatus)) {
					foreach($searchFilterStatus as $key => $value) {
						if (!empty($sqlSearchFilterStatus)) {
							$sqlSearchFilterStatus .= ' OR ';
						}
						if (!empty($value)) {
							$sqlSearchFilterStatus .= '`a`.`status` LIKE \'' . (string) $value . '\'';
						}
					}
					if (!empty($sqlSearchFilterStatus)) {
						$sqlSearchFilterStatus = ' AND (' . $sqlSearchFilterStatus . ') ';
					}
				}
				else {
					if ( empty($gConfig['incomplete_bookings_display']) ) {
						$sqlSearchFilterStatus = " AND `a`.`status`!='incomplete'";
					}
				}

				// Payment method
				$searchFilterPaymentMethod = json_decode($etoPost['searchFilterPaymentMethod']);
				$sqlSearchFilterPaymentMethod = '';
				if (!empty($searchFilterPaymentMethod)) {
						foreach($searchFilterPaymentMethod as $key => $value) {
								if (!empty($sqlSearchFilterPaymentMethod)) {
										$sqlSearchFilterPaymentMethod .= ' OR ';
								}
								if (!empty($value)) {
										$sqlSearchFilterPaymentMethod .= '`payment_method` = \'' . (string) $value . '\'';
								}
						}
						if (!empty($sqlSearchFilterPaymentMethod)) {
								$sqlSearchFilterPaymentMethod = " AND `a`.`booking_id` IN (
									SELECT `relation_id`
									FROM `{$dbPrefix}transactions`
									WHERE `relation_type`='booking'
									AND `relation_id`=`a`.`booking_id`
									AND (". $sqlSearchFilterPaymentMethod .")) ";
						}
				}

				// Payment status
				$searchFilterPaymentStatus = json_decode($etoPost['searchFilterPaymentStatus']);
				$sqlSearchFilterPaymentStatus = '';
				if (!empty($searchFilterPaymentStatus)) {
						foreach($searchFilterPaymentStatus as $key => $value) {
								if (!empty($sqlSearchFilterPaymentStatus)) {
										$sqlSearchFilterPaymentStatus .= ' OR ';
								}
								if (!empty($value)) {
										$sqlSearchFilterPaymentStatus .= '`status` LIKE \'' . (string) $value . '\'';
								}
						}
						if (!empty($sqlSearchFilterPaymentStatus)) {
								$sqlSearchFilterPaymentStatus = " AND `a`.`booking_id` IN (
									SELECT `relation_id`
									FROM `{$dbPrefix}transactions`
									WHERE `relation_type`='booking'
									AND `relation_id`=`a`.`booking_id`
									AND (". $sqlSearchFilterPaymentStatus .")) ";
						}
				}

				// Driver
				$searchFilterDriverName    = json_decode($etoPost['searchFilterDriverName']);
				$sqlSearchFilterDriverName = '';
				if (isset($searchFilterDriverName)) {
					foreach($searchFilterDriverName as $key => $value) {
						if (!empty($sqlSearchFilterDriverName)) {
							$sqlSearchFilterDriverName .= ' OR ';
						}
						if (isset($value)) {
							$sqlSearchFilterDriverName .= '`a`.`driver_id`=\'' . (int) $value . '\'';
						}
					}
					if (!empty($sqlSearchFilterDriverName)) {
						$sqlSearchFilterDriverName = ' AND (' . $sqlSearchFilterDriverName . ') ';
					}
				}

				// Customer
				$searchFilterCustomerName    = json_decode($etoPost['searchFilterCustomerName']);
				$sqlSearchFilterCustomerName = '';
				if (isset($searchFilterCustomerName)) {
					foreach($searchFilterCustomerName as $key => $value) {
						if (!empty($sqlSearchFilterCustomerName)) {
							$sqlSearchFilterCustomerName .= ' OR ';
						}
						if (isset($value)) {
							$sqlSearchFilterCustomerName .= '`b`.`user_id`=\'' . (int) $value . '\'';
						}
					}
					if (!empty($sqlSearchFilterCustomerName)) {
						$sqlSearchFilterCustomerName = ' AND (' . $sqlSearchFilterCustomerName . ') ';
					}
				}

				// Fleet Operator
				$sqlSearchFilterFleetName = '';
				if (config('eto.allow_fleet_operator')) {
						$searchFilterFleetName = json_decode($etoPost['searchFilterFleetName']);
						if (isset($searchFilterFleetName)) {
								foreach($searchFilterFleetName as $key => $value) {
										if (!empty($sqlSearchFilterFleetName)) {
												$sqlSearchFilterFleetName .= ' OR ';
										}
										if (isset($value)) {
												$sqlSearchFilterFleetName .= '`a`.`fleet_id`=\'' . (int) $value . '\'';
											}
								}
								if (!empty($sqlSearchFilterFleetName)) {
										$sqlSearchFilterFleetName = ' AND (' . $sqlSearchFilterFleetName . ') ';
								}
						}
				}

				// Date Type
				$searchFilterDateType    = (string)$etoPost['searchFilterDateType'];
				$valueDateType = '`a`.`created_date`';

				if ( !empty($searchFilterDateType) ) {
					if ( $searchFilterDateType == 'created_date' ) {
						$valueDateType = '`a`.`created_date`';
					}
					elseif ( $searchFilterDateType == 'date' ) {
						$valueDateType = '`a`.`date`';
					}
					elseif ( $searchFilterDateType == 'modified_date' ) {
						$valueDateType = '`a`.`modified_date`';
					}
				}

				// Start Date
				$searchFilterStartDate    = (string)$etoPost['searchFilterStartDate'];
				$sqlSearchFilterStartDate = '';

				if ( !empty($searchFilterStartDate) ) {
					$valueDate = date('Y-m-d H:i:s', strtotime($searchFilterStartDate));
					$sqlSearchFilterStartDate = " AND (". $valueDateType ." >= '". $valueDate ."') ";
				}

				// End Date
				$searchFilterEndDate = (string)$etoPost['searchFilterEndDate'];
				$sqlSearchFilterEndDate = '';

				if ( !empty($searchFilterEndDate) ) {
					$valueDate = date('Y-m-d H:i:s', strtotime($searchFilterEndDate));
					$sqlSearchFilterEndDate = " AND (". $valueDateType ." <= '". $valueDate ."') ";
				}

				if ( $page_type == 'next24' ) {
						$sDate = \Carbon\Carbon::now();
						$eDate = $sDate->copy()->addHours(24);
						if ( empty($searchFilterStartDate) ) {
								$sqlSearchFilterStartDate = " AND (`a`.`date` >= '". $sDate->toDateTimeString() ."') ";
						}
						if ( empty($searchFilterEndDate) ) {
								$sqlSearchFilterEndDate = " AND (`a`.`date` <= '". $eDate->toDateTimeString() ."') ";
						}
				}

				$sqlSearchFilter = $sqlSearchFilterServiceId .' '.
						$sqlSearchFilterScheduledRouteId .' '.
						$sqlSearchFilterParentBookingId .' '.
						$sqlSearchFilterSource .' '.
						$sqlSearchFilterStatus .' '.
						$sqlSearchFilterPaymentMethod .' '.
						$sqlSearchFilterPaymentStatus .' '.
						$sqlSearchFilterDriverName .' '.
						$sqlSearchFilterCustomerName .' '.
						$sqlSearchFilterFleetName .' '.
						$sqlSearchFilterStartDate .' '.
						$sqlSearchFilterEndDate;

				// $data['sqlSearchFilter'] = $sqlSearchFilter;

				if ($page_type == 'trash') {
						$sqlTrash = " AND `a`.`deleted_at` IS NOT NULL ";
				}
				else {
						$sqlTrash = " AND `a`.`deleted_at` IS NULL ";
				}
				$sqlSearchFilter .= $sqlTrash;

				// Summary
				$summary = ( (string)$etoPost['summary'] == 'true' ) ? 1 : 0;

				if ( $summary ) {
						include __DIR__ .'/BackendBookingsReport.php';
				}

				$exportType = (string)$etoPost['exportType'];

				if ( !empty($exportType) ) {
						$start = 0;
						$limit = 100000;
				}

				// Check fleet
				$sqlFleet = "";
				if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
						$sqlFleet = " AND `a`.`fleet_id`='". auth()->user()->id ."' ";
				}

				// Bookings total
				$sql = "SELECT COUNT(`a`.`id`) as `count` FROM `{$dbPrefix}booking_route` AS `a` WHERE 1 ";
				$sql .= $sqlTrash;
				$sql .= $sqlFleet;
				$resultsBookingTotal = $db->select($sql);
				$data['recordsTotal'] = $resultsBookingTotal[0]->count;


				// Bookings total filtered
				$sql = "SELECT COUNT(`a`.`id`) as `count`
						FROM `{$dbPrefix}booking_route` AS `a`
						LEFT JOIN `{$dbPrefix}booking` AS `b`
						ON `a`.`booking_id`=`b`.`id`
						WHERE 1 ";
				$sql .= $sqlFleet;
				$sql .= " ". $sqlSearch ." ". $sqlSearchFilter;
				$resultsBookingFiltered = $db->select($sql);
				$data['recordsFiltered'] = $resultsBookingFiltered[0]->count;


				// Booking list
				$sql = "SELECT `a`.*,
								`b`.*,
								`a`.`id` AS `route_id`,
								`a`.`ref_number` AS `route_ref_number`,
								(
									SELECT `name`
									FROM `{$dbPrefix}user`
									WHERE `id`=`b`.`user_id`
									/*AND `site_id`=`b`.`site_id`*/
									LIMIT 1
								) AS `user_name`,
								 (
									 SELECT `d`.`company_name`
									 FROM `{$dbPrefix}user` AS `c`
									 LEFT JOIN `{$dbPrefix}user_customer` AS `d`
									 ON `c`.`id`=`d`.`user_id`
									 WHERE `c`.`id`=`b`.`user_id`
									 LIMIT 1
									) AS `company_name`
						FROM `{$dbPrefix}booking_route` AS `a`
						LEFT JOIN `{$dbPrefix}booking` AS `b`
						ON `a`.`booking_id`=`b`.`id`
						WHERE 1 ";

				$sql .= $sqlFleet;
				$sql .= " ". $sqlSearch . " " . $sqlSearchFilter . " ORDER BY " . $sqlSort . " `a`.`date` ASC LIMIT {$start},{$limit}";
				$resultsBooking = $db->select($sql);

				$rIDs = [];
				$rData = [];
				foreach ($resultsBooking as $k => $v) {
						$rIDs[] = $v->route_id;
						$rData[$v->route_id] = [
								'site_id' => $v->site_id,
								'user_id' => $v->user_id,
								'user_name' => $v->user_name,
								'company_name' => $v->company_name,
						];
				}

				$with = [
						'bookingTransactions' => function($query) {
                $query->withTrashed();
            },
						'bookingFeedback',
						'bookingService',
						'bookingScheduledRoute',
						'bookingDriver',
						'bookingDriver.profile',
						'bookingDriver.vehicles',
						'bookingVehicle',
						'bookingAllRoutes' => function($query) {
                $query->withTrashed();
            },
						// 'bookingStatuses', // Where conditions will not work when this is on
						// 'booking', // Not used in the query yet
				];

				if (config('eto_dispatch.enable_autodispatch')) {
						$with[] = 'bookingDrivers';
						$with[] = 'bookingDrivers.driver';
						$with[] = 'bookingDrivers.vehicle';
				}

				if (config('eto.allow_fleet_operator')) {
						$with[] = 'bookingFleet';
				}

				$query = \App\Models\BookingRoute::with($with)->withTrashed()->whereIn('id', $rIDs);

				$oldSqlSort = $sqlSort . " `a`.`date` ASC";
				$newOrder = explode(',', $oldSqlSort);
				foreach ($newOrder as $k => $v) {
						$v = trim(str_replace('`a`.', '', $v));
						$query->orderByRaw($v);
				}
				// dd($oldSqlSort, $query->toSql());

				$resultsBooking = $query->get();

				if (!empty($resultsBooking)) {
						$booking = array();
						$request = request();

						foreach($resultsBooking as $key => $value) {
								$bookingRoute = $value;

								if (!empty($rData[$bookingRoute->id])) {
										$site_id = $rData[$bookingRoute->id]['site_id'];
										$customer_id = $rData[$bookingRoute->id]['user_id'];
										$customer_name = $rData[$bookingRoute->id]['user_name'];
										$customer_company_name = $rData[$bookingRoute->id]['company_name'];
								}
								else {
										$site_id = 0;
										$customer_id = 0;
										$customer_name = '';
										$customer_company_name = '';
								}

								// Payments
								$temp = $bookingRoute->getTotal('data', 'raw');
								$total = !empty($exportType) ? number_format((float)$temp->total, 2, '.', '') : SiteHelper::formatPrice($temp->total); // Used for listing column
								$isPaid = 0;
								$pInfo = '';
								$pCash = $temp->cash;

								foreach($temp->payment_list as $v) {
										$pInfo .= '<span title="'. $v->status .'">';
										if ( count($temp->payment_list) > 1 ) {
												$pInfo .= $v->title .' ';
										}
										$pInfo .= '<span style="color:#333333;">'. $v->formatted->total .'</span> ( '. $v->name .' )';
										$pInfo .= '</span><br>';
								}

								if ( count($temp->payment_list) <= 0 ) {
										$paymentStatus = 'Unassigned';
										$color = '#d20300';
								}
								else if ( $temp->remaining >= 0 ) {
										$paymentStatus = trans('admin/bookings.payment_status.paid');
										$color = '#333333';
										$isPaid = 1;
								}
								elseif ( $temp->payment_paid > 0 ) {
										$paymentStatus = trans('admin/bookings.payment_status.partially_paid');
										$color = '#d20300';
								}
								else {
										$paymentStatus = trans('admin/bookings.payment_status.unpaid');
										$color = '#d20300';
								}

								// $paymentStatus .= ' ('. config('site.currency_symbol') . $temp->remaining . config('site.currency_code') .')';

								if (auth()->user()->hasPermission('admin.bookings.edit')) {
										$paymentStatus = '<a href="' . route('admin.bookings.transactions', $bookingRoute->id) . '" class="payment-status" onclick="modalIframe(this); return false;" title="Payment history ' . $bookingRoute->getRefNumber() . '">' .
												'<span style="color:' . $color . ';">' . $paymentStatus . '</span> <i class="fa fa-eye"></i>' .
												'</a>';
								} else {
										$paymentStatus = '<span style="color:' . $color . ';">' . $paymentStatus . '</span> ';
								}

								if ( !empty($pInfo) && $isPaid != 1 ) {
										$paymentStatus .= '<div style="font-size:11px; color:#888; margin-top:2px;">'. $pInfo .'</div>';
								}


								// Route
								$routeName = $bookingRoute->getRouteName();

								// Source
								$source = $bookingRoute->source;
								if (!empty($bookingRoute->source_details)) {
										$source .= ' '. $bookingRoute->source_details;
								}
								$source = trim($source);

								// Aditional info
								$additionalInfo = '';

								if ($bookingRoute->route == 2) {
										$additionalInfo .= '<span title="'. $routeName .'" class="label bg-purple" style="opacity:0.4;margin-right:2px;">R</span>';
								}

								// Scheduled
								$bookingChildren = 0;

								if ($bookingRoute->scheduled_route_id) {
										if ($bookingRoute->parent_booking_id) {
												$title = 'Assigned to '. (!empty($bookingRoute->parentBooking->getRefNumber()) ? $bookingRoute->parentBooking->getRefNumber() : $bookingRoute->parent_booking_id);
												$additionalInfo .= '<a href="#" title="'. $title .'" style="margin-right:3px; text-align:center;"
														onclick="$(\'#filter-parent_booking_id\').val(\'\'); $(\'#filter-booking_type\').val(\'\').change(); $(\'#filter-keywords\').val(\''. $bookingRoute->parentBooking->ref_number .'\'); filterTable();">
														<i class="fa fa-user" style="font-size:18px; color:#bfbfbf; width:20px; text-align:center;"></i></a>';
										}
										else {
												$bookingChildren = $bookingRoute->childBookings->count();
												$title = 'Group booking ('. $bookingChildren .')';
												$additionalInfo .= '<a href="#" title="'. $title .'" style="margin-right:3px; text-align:center;"
														onclick="$(\'#filter-parent_booking_id\').val('. $bookingRoute->id .'); $(\'#filter-booking_type\').val(\'\').change(); if ( $(\'#filter-keywords\').val() == \''. $bookingRoute->ref_number .'\') { $(\'#filter-keywords\').val(\'\'); } filterTable();">
														<i class="fa fa-users" style="font-size:18px; color:#bfbfbf; width:20px; text-align:center;"></i></a>';
										}
								}

								// Feedback
								if ($bookingRoute->ref_number && auth()->user()->hasPermission('admin.feedback.show')) {
										$feedback = $bookingRoute->bookingFeedback;

										if ($feedback->count()) {
												$feedbackLostFound = [];
												$feedbackComments = [];
												$feedbackComplaints = [];

												foreach ($feedback as $kF => $vF) {
														if ($vF->type == 'complaint') {
																$feedbackComplaints[] = $vF;
														}
														elseif ($vF->type == 'lost_found') {
																$feedbackLostFound[] = $vF;
														}
														else {
																$feedbackComments[] = $vF;
														}
												}

												if (count($feedbackComments)) {
														if (count($feedbackComments) == 1) {
																$title = 'Comment';
																$link = route('admin.feedback.show', ['id' => $feedbackComments[0]->id]);
														}
														else {
																$title = 'Comments ('. count($feedbackComments) .')';
																$link = route('admin.feedback.index', ['type' => 'comment', 'ref_number' => $bookingRoute->ref_number]);
														}
														$icon = '<i class="fa fa-comment" style="font-size:18px; color:#bfbfbf; width:20px;"></i>';
														$additionalInfo .= '<a href="'. $link .'" title="'. $title .'" style="margin-right:3px; text-align:center;">'. $icon .'</a>';
												}

												if (count($feedbackLostFound)) {
														if (count($feedbackLostFound) == 1) {
																$title = 'Lost & Found';
																$link = route('admin.feedback.show', ['id' => $feedbackLostFound[0]->id]);
														}
														else {
																$title = 'Lost & Found ('. count($feedbackLostFound) .')';
																$link = route('admin.feedback.index', ['type' => 'lost_found', 'ref_number' => $bookingRoute->ref_number]);
														}
														$icon = '<i class="fa fa-briefcase" style="font-size:18px; color:#bfbfbf; width:20px;"></i>';
														$additionalInfo .= '<a href="'. $link .'" title="'. $title .'" style="margin-right:3px; text-align:center;">'. $icon .'</a>';
												}

												if (count($feedbackComplaints)) {
														if (count($feedbackComplaints) == 1) {
																$title = 'Complaint';
																$link = route('admin.feedback.show', ['id' => $feedbackComplaints[0]->id]);
														}
														else {
																$title = 'Complaints ('. count($feedbackComplaints) .')';
																$link = route('admin.feedback.index', ['type' => 'complaint', 'ref_number' => $bookingRoute->ref_number]);
														}
														$icon = '<i class="fa fa-meh-o" style="font-size:18px; color:#bfbfbf; width:20px;"></i>';
														$additionalInfo .= '<a href="'. $link .'" title="'. $title .'" style="margin-right:3px; text-align:center;">'. $icon .'</a>';
												}
										}
								}

								$temp = array();

								if ( !empty($bookingRoute->child_seats) ) {
										$temp[] = 'Child seats: '. $bookingRoute->child_seats .'';
								}
								if ( !empty($bookingRoute->baby_seats) ) {
										$temp[] = 'Booster seats: '. $bookingRoute->baby_seats .'';
								}
								if ( !empty($bookingRoute->infant_seats) ) {
										$temp[] = 'Infant seats: '. $bookingRoute->infant_seats .'';
								}
								if ( !empty($bookingRoute->wheelchair) ) {
										$temp[] = 'Wheelchairs: '. $bookingRoute->wheelchair .'';
								}

								$notes = '';
								if ( $bookingRoute->notes ) {
										$notes .= trans('admin/bookings.admin_notes') .': '. SiteHelper::nl2br2(htmlspecialchars($bookingRoute->notes));
								}
								if ( $bookingRoute->driver_notes ) {
										$notes .= $notes ? '<br>' : '';
										$notes .= trans('admin/bookings.driver_notes') .': '. SiteHelper::nl2br2(htmlspecialchars($bookingRoute->driver_notes));
								}
								if ( $notes ) {
										$temp[] = '<b>'. trans('admin/bookings.notes') .'</b><br>'. $notes;
								}

								if ( !empty($temp) ) {
										$additionalInfo .= '<span title="<div style=\'text-align:left;\'>'. implode('<br>', $temp) .'</div>" class="label label-info" style="margin-right:2px;"><i class="fa fa-info-circle"></i></span>';
								}

								if (!$bookingRoute->is_read) {
										$additionalInfo .= '<span class="label label-info">New</span>';
								}

								$status = '';
								$status_btn = '';
								$fleet = null;
								$fleet_id = 0;
								$fleet_name = '';
								$fleet_btn = '';
								$fleet_link = '';
								$fleet_commission = 0;
								$fleet_commission_btn = '';
								$driver = null;
								$driver_id = 0;
								$driver_name = '';
								$driver_btn = '';
								$driver_link = '';
								$vehicle = null;
								$vehicle_id = 0;
								$vehicle_name = '';
								$vehicle_btn = '';
								$vehicle_link = '';
								$commission = 0;
								$commission_btn = '';
								$cash = 0;
								$cash_btn = '';

								// Status
								if ( $bookingRoute->status ) {
										$status = $bookingRoute->getStatus('label');

										if (auth()->user()->hasPermission('admin.bookings.edit')) {
												$btn = '<a href="#" class="inline-editing inline-editing-status"
													data-type="select"
													data-name="update_status"
													data-pk="' . $bookingRoute->id . '"
													data-value="' . $bookingRoute->status . '"
													data-highlight="#faf6de"
													data-url="' . route('admin.bookings.inline-editing', 'update_status') . '">
													' . $status . ' <i class="fa fa-edit"></i>
												</a>';

												// data-source="' . route('admin.bookings.inline-editing', 'status_list') . '"
										} else {
												$btn =  $status;
										}

										if ($bookingRoute->scheduled_route_id && $bookingRoute->parent_booking_id) {
												$status_btn = '<span class="inline-editing-status">'. $status .'</span>';
										}
										else {
												$status_btn = $btn;
										}
								}

								// Status notes
								if ( $bookingRoute->status_notes && in_array($bookingRoute->status, ['canceled', 'unfinished', 'rejected']) ) {
										$status_btn .= '<a href="#" class="inline-editing inline-editing-notes"
													data-type="textarea"
													data-name="update_status_notes"
													data-pk="'. $bookingRoute->id .'"
													data-value="'. htmlspecialchars($bookingRoute->status_notes) .'"
													data-highlight="#faf6de"
													data-url="'. route('admin.bookings.inline-editing', 'update_status_notes') .'"
													title="'. SiteHelper::nl2br2(htmlspecialchars($bookingRoute->status_notes)) .'">
													<i class="fa fa-info-circle"></i>
												</a>';
								}


								if (config('eto.allow_fleet_operator')) {
										// Fleet operator
										if ($bookingRoute->fleet_id) {
										    $fleet = $bookingRoute->bookingFleet;
										    if ($fleet->id) {
										        $fleet_id = $fleet->id;
										        $fleet_name = $fleet->getName();
										        $fleet_link = '<a href="'. route('admin.users.show', $fleet->id) .'">'. $fleet_name .'</a>';
										    }
										}

										if (empty($fleet_name)) {
										    $title = trans('admin/bookings.assign_fleet');
										    if (auth()->user()->hasPermission('admin.bookings.edit')) {
										        $title .= ' <i class="fa fa-plus"></i>';
										    }
										} else {
										    $title = $fleet_name;
										    if (auth()->user()->hasPermission('admin.bookings.edit')) {
										        $title .= ' <i class="fa fa-edit"></i>';
										    }
										}

                    $routeCharges = \App\Helpers\BookingHelper::getChargesPerRoute($bookingRoute);
                    $paymentCharges = 0;

                    foreach($routeCharges->routes as $route) {
                        if ((int)$route->id === (int)$bookingRoute->id) {
                            $paymentCharges = $route->payment_charges;
                        }
                    }

                    $excluded = $routeCharges ? \App\Helpers\DriverHelper::getIncome($routeCharges, $bookingRoute->id) : 0;
                    $priceToCommission = ($bookingRoute->total_price + $paymentCharges) - $excluded;

										if (auth()->user()->hasPermission('admin.bookings.edit')) {
                        $values = (object)[
                            'fleet' => $bookingRoute->fleet_id,
                            'commission' => number_format($bookingRoute->fleet_commission, 2, '.', ''),
                            'total_price' => number_format($priceToCommission, 2, '.', ''),
                        ];

										    $btn = '<a href="#" class="inline-editing inline-editing-fleet"
										      data-type="assign_fleet"
										      data-name="update_fleet"
										      data-pk="' . $bookingRoute->id . '"
											  	data-value=\'' . json_encode($values) . '\'
										      data-highlight="#faf6de"
										      data-url="' . route('admin.bookings.inline-editing', 'update_fleet') . '">
										      ' . $title . '
										    </a>';
										} else {
										    $btn = $title;
										}

										if ($bookingRoute->scheduled_route_id && $bookingRoute->parent_booking_id
												&& $request->system->subscription->license_status != 'suspended'
										) {
												$fleet_btn = '<span class="inline-editing-fleet">'. $fleet_name .'</span>';
										} else {
												$fleet_btn = $btn;
										}

										// if ( $bookingRoute->fleet_data && empty($fleet) ) {
										//   $temp = json_decode($bookingRoute->fleet_data, true);
										//   if ( $temp ) {
										//     $title = '';
										//     foreach($temp as $kD => $vD) {
										//       if ( $vD ) {
										//         if ( is_array($vD) ) {
										//           foreach($vD as $kD2 => $vD2) {
										//             if ( $vD2 ) {
										//               $vD2 = str_replace('"', "'", $vD2);
										//               $title .= trim(ucfirst(str_replace('_', ' ', $kD2))) .': '. $vD2 .'<br>';
										//             }
										//           }
										//         }
										//         else {
										//           $title .= trim(ucfirst(str_replace('_', ' ', $kD))) .': '. $vD .'<br>';
										//         }
										//       }
										//     }
										//     if ( $title ) {
										//       $fleet_btn .= '<span class="inline-editing-fleet" data-toggle="popover" data-title="Fleet has been removed.<br />Here is some saved data." data-content="'. $title .'"><i class="fa fa-info-circle"></i></span>';
										//     }
										//   }
										// }

										// Fleet Commission
										// if ( $bookingRoute->fleet_id || $bookingRoute->fleet_commission ) {
										    $fleet_commission = !empty($exportType) ? number_format((float)$bookingRoute->fleet_commission, 2, '.', '') : SiteHelper::formatPrice($bookingRoute->fleet_commission);
										    if (auth()->user()->hasPermission('admin.bookings.edit') && !auth()->user()->hasRole('admin.fleet_operator')) {
										        $btn = '<a href="#" class="inline-editing inline-editing-fleet_commission"
										          data-type="text"
										          data-name="update_fleet_commission"
										          data-pk="' . $bookingRoute->id . '"
										          data-value="' . $bookingRoute->fleet_commission . '"
										          data-highlight="#faf6de"
										          data-url="' . route('admin.bookings.inline-editing', 'update_fleet_commission') . '">
										          ' . $fleet_commission . ' <i class="fa fa-edit"></i>
										        </a>';
										    } else {
										        $btn = $fleet_commission;
										    }
										    $fleet_commission_btn = $btn;
										// }
								}


								// Driver
								if ( $bookingRoute->driver_id ) {
										// $driver = \App\Models\User::find($bookingRoute->driver_id);
										$driver = $bookingRoute->bookingDriver;

										if ( $driver->id ) {
												$driver_id = $driver->id;
												$driver_name = $driver->getName(true);
												$driver_link = '<a href="' . route('admin.users.show', $driver->id) . '">' . $driver_name . '</a>';
										}
								}

								if ($request->system->subscription->license_status != 'suspended') {
										if (empty($driver_name)) {
												$title = trans('admin/bookings.assign_driver');

												if (auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator')) {
														$title .= ' <i class="fa fa-plus"></i>';
												}
										} else {
												$title = $driver_name;

												if (auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator')) {
														$title .= ' <i class="fa fa-edit"></i>';
												}
										}
								} else {
										if (empty($driver_name)) {
												$title = supplier;
										} else {
												$title = $driver_name;
										}
								}

								// $transactions = \App\Models\Transaction::where('relation_type', '=', 'booking')
								// 		->where('relation_id', $bookingRoute->booking_id)
								// 		->where('payment_method', 'cash')
								// 		->get();

								// $cash_calculated = 0;
								// foreach ($transactions as $kT => $vT) {
								// 		$cash_calculated += $vT->amount + $vT->payment_charge;
								// }

								$cash_calculated = $pCash;

								$priceToCommission = !empty($bookingRoute->fleet_commission) ? $bookingRoute->fleet_commission : ($bookingRoute->total_price + $paymentCharges) - $excluded;

								$values = (object)[
										'driver' => $bookingRoute->driver_id,
										'vehicle' => $bookingRoute->vehicle_id,
										'commission' => number_format($bookingRoute->commission, 2, '.', ''),
										'cash' => number_format($bookingRoute->cash, 2, '.', ''),
										'cash_calculated' => number_format($cash_calculated, 2, '.', ''),
										'total_price' => number_format($priceToCommission, 2, '.', ''),
										// 'total_price' => number_format(($bookingRoute->total_price + $paymentCharges) - $excluded, 2, '.', ''),
								];

								if ($request->system->subscription->license_status != 'suspended' && (
										auth()->user()->hasPermission('admin.bookings.edit') ||
										auth()->user()->hasRole('admin.fleet_operator')
								)) {
										$btn = '<a href="#" class="inline-editing inline-editing-driver"
												data-type="assign_driver"
												data-name="update_driver"
												data-pk="' . $bookingRoute->id . '"
												data-value=\'' . json_encode($values) . '\'
												data-highlight="#faf6de"
												data-url="' . route('admin.bookings.inline-editing', 'update_driver') . '">
												' . $title . '
											</a>';

										// data-source="' . route('admin.bookings.inline-editing', 'driver_list') . '"
								} else {
										$btn = $title;
								}

								if ($bookingRoute->scheduled_route_id && $bookingRoute->parent_booking_id
										&& $request->system->subscription->license_status != 'suspended'
								) {
										$driver_btn = '<span class="inline-editing-driver">'. $driver_name .'</span>';
								} else {
										$driver_btn = $btn;
								}

								if ( $bookingRoute->driver_data && empty($driver) ) {
									$temp = json_decode($bookingRoute->driver_data, true);
									if ( $temp ) {
										$title = '';
										foreach($temp as $kD => $vD) {
											if ( $vD ) {
												if ( is_array($vD) ) {
													foreach($vD as $kD2 => $vD2) {
														if ( $vD2 ) {
															$vD2 = str_replace('"', "'", $vD2);
															$title .= trim(ucfirst(str_replace('_', ' ', $kD2))) .': '. $vD2 .'<br>';
														}
													}
												}
												else {
													$title .= trim(ucfirst(str_replace('_', ' ', $kD))) .': '. $vD .'<br>';
												}
											}
										}
										if ( $title ) {
											$driver_btn .= '<span class="inline-editing-driver" data-toggle="popover" data-title="Driver account has been removed.<br />Here is some saved data." data-content="'. $title .'"><i class="fa fa-info-circle"></i></span>';
										}
									}
								}

								if (config('eto_dispatch.enable_autodispatch') && $bookingRoute->bookingDrivers->count()) {
										$title = '';
										foreach ($bookingRoute->bookingDrivers as $k => $v) {
												$line = [];

												if (!empty($v->driver->name)) {
														$line[] = $v->driver->getName(true);
												}
												if (!empty($v->vehicle->name)) {
														$line[] = $v->vehicle->getName();
												}
												if (!empty($v->commission)) {
														$line[] = trans('admin/bookings.commission') .': '. SiteHelper::formatPrice($v->commission);
												}
												if (!empty($v->cash)) {
														$line[] = trans('admin/bookings.cash') .': '. SiteHelper::formatPrice($v->cash);
												}
												$line[] = trans('admin/bookings.status') .': '. $v->getStatus();
												if (!empty($v->status_notes)) {
														$line[] = trans('admin/bookings.status_notes') .': '. $v->status_notes;
												}
												if (!empty($v->expired_at)) {
														$line[] = trans('admin/bookings.request_expired_at') .': '. SiteHelper::formatDateTime($v->expired_at);
												}

												if (count($line)) {
														if ($k == 0) {
																$title .= '<div>'. implode(', ', $line) .'</div>';
														}
														else {
																$title .= '<div style=\'border-top:1px #d8d8d8 solid; margin-top:5px; padding-top:5px;\'>'. implode(', ', $line) .'</div>';
														}
												}
										}
										$driver_btn .= '<span data-toggle="popover" data-title="'. trans('admin/bookings.auto_assigned') .'" data-content="'. $title .'" class="label bg-green" style="opacity:0.4;margin-right:2px;">A</span>';
								}

								// Vehicle
								if ( $bookingRoute->vehicle_id ) {
										// $vehicle = \App\Models\Vehicle::find($bookingRoute->vehicle_id);
										$vehicle = $bookingRoute->bookingVehicle;

										if ( $vehicle->id ) {
												$vehicle_id = $vehicle->id;
												$vehicle_name = $vehicle->getName();
												$vehicle_link = '<a href="'. route('admin.vehicles.show', $vehicle->id) .'">'. $vehicle_name .'</a>';
										}
								}

								if ( empty($vehicle_name) ) {
										$title = trans('admin/bookings.assign_vehicle');

										if (auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator')) {
												$title .= ' <i class="fa fa-plus"></i>';
										}
								} else {
										$title = $vehicle_name;

										if (auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator')) {
												$title .= ' <i class="fa fa-edit"></i>';
										}
								}

								if (auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator')) {
										$btn = '<a href="#" class="inline-editing inline-editing-vehicle"
											data-type="select"
											data-name="update_vehicle"
											data-pk="' . $bookingRoute->id . '"
											data-value="' . $bookingRoute->vehicle_id . '"
											data-driver_id="' . $bookingRoute->driver_id . '"
											data-highlight="#faf6de"
											data-url="' . route('admin.bookings.inline-editing', 'update_vehicle') . '">
											' . $title . '
										</a>';

										// data-source="' . route('admin.bookings.inline-editing', 'vehicle_list') . '?id=' . $bookingRoute->driver_id . '"
								} else {
										$btn = $title;
								}

								if ( $bookingRoute->driver_id && $driver && $driver->vehicles->count() > 0 ) {
										if ($bookingRoute->scheduled_route_id && $bookingRoute->parent_booking_id) {
												$vehicle_btn = '<span class="inline-editing-vehicle">'. $vehicle_name .'</span>';
										}
										else {
												$vehicle_btn = $btn;
										}
								}
								else {
										$vehicle_btn = '';
								}

								if ( $bookingRoute->vehicle_data && empty($vehicle) ) {
									$temp = json_decode($bookingRoute->vehicle_data, true);
									if ( $temp ) {
										$title = '';
										foreach($temp as $kD => $vD) {
											if ( $vD ) {
												if ( is_array($vD) ) {
													foreach($vD as $kD2 => $vD2) {
														if ( $vD2 ) {
															$vD2 = str_replace('"', "'", $vD2);
															$title .= trim(ucfirst(str_replace('_', ' ', $kD2))) .': '. $vD2 .'<br>';
														}
													}
												}
												else {
													$title .= trim(ucfirst(str_replace('_', ' ', $kD))) .': '. $vD .'<br>';
												}
											}
										}
										if ( $title ) {
											$vehicle_btn .= '<span class="inline-editing-vehicle" data-toggle="popover" data-title="Vehicle has been removed.<br />Here is some saved data." data-content="'. $title .'"><i class="fa fa-info-circle"></i></span>';
										}
									}
								}

								// Commission
								// if ( $bookingRoute->driver_id || $bookingRoute->commission ) {
										$commission = !empty($exportType) ? number_format((float)$bookingRoute->commission, 2, '.', '') : SiteHelper::formatPrice($bookingRoute->commission);
										if (auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator')) {
												$btn = '<a href="#" class="inline-editing inline-editing-commission"
													data-type="text"
													data-name="update_commission"
													data-pk="' . $bookingRoute->id . '"
													data-value="' . $bookingRoute->commission . '"
													data-highlight="#faf6de"
													data-url="' . route('admin.bookings.inline-editing', 'update_commission') . '">
													' . $commission . ' <i class="fa fa-edit"></i>
												</a>';
										} else {
												$btn = $commission;
										}
										$commission_btn = $btn;
								// }

								// Cash
								// if ( $bookingRoute->driver_id || $bookingRoute->cash ) {
										$cash = !empty($exportType) ? number_format((float)$bookingRoute->cash, 2, '.', '') : SiteHelper::formatPrice($bookingRoute->cash);
										if (auth()->user()->hasPermission('admin.bookings.edit')) {
												$btn = '<a href="#" class="inline-editing inline-editing-cash"
													data-type="text"
													data-name="update_cash"
													data-pk="' . $bookingRoute->id . '"
													data-value="' . $bookingRoute->cash . '"
													data-highlight="#faf6de"
													data-url="' . route('admin.bookings.inline-editing', 'update_cash') . '">
													' . $cash . ' <i class="fa fa-edit"></i>
												</a>';
										} else {
												$btn = $cash;
										}

										$cash_btn = $btn;
								// }

								if ($bookingRoute->scheduled_route_id) {
										if ($bookingRoute->parent_booking_id) {
												$commission_btn = '';
										}
										else {
												$paymentStatus = '';
												$cash_btn = '';
										}
								}

								$columns = array();

								$columns['DT_RowClass'] = 'row-booking-status row-booking-status-'. $bookingRoute->status .' '. (!$bookingRoute->is_read ? 'row-booking-unopened' : '');
								$columns['url_show'] = route('admin.bookings.show', $bookingRoute->id);
								$columns['url_edit'] = route('admin.bookings.edit', $bookingRoute->id);
								$columns['url_transactions'] = route('admin.bookings.transactions', $bookingRoute->id);
								$columns['url_copy'] = route('admin.bookings.copy', $bookingRoute->id);
								$columns['url_invoice'] = route('admin.bookings.invoice', $bookingRoute->id);
								$columns['url_sms'] = route('admin.bookings.sms', $bookingRoute->id);
								$columns['url_feedback'] = route('admin.feedback.create', ['ref_number' => $bookingRoute->ref_number]);
								$columns['url_meeting_board'] = route('admin.bookings.meeting-board', $bookingRoute->id);

								$columns['id'] = (int)$bookingRoute->id;
								$columns['site_id'] = (int)$site_id;
								$columns['additional_info'] = $additionalInfo;
								$columns['ref_number'] = $bookingRoute->ref_number;

								$serviceType = $bookingRoute->getServiceType();
								if (auth()->user()->hasPermission('admin.services.show')) {
										$columns['service_id'] = $serviceType ? '<a href="'. route('admin.services.show', $bookingRoute->service_id) .'" class="text-default">'. $serviceType .'</a>' : '';
								}
								else {
										$columns['service_id'] = $serviceType ? $serviceType : '';
								}

								$columns['service_duration'] = $bookingRoute->getServiceDuration();

								$scheduledRouteName = $bookingRoute->getScheduledRouteName();
								if (auth()->user()->hasPermission('admin.scheduled_routes.show')) {
										$columns['scheduled_route_id'] = $scheduledRouteName ? '<a href="'. route('admin.scheduled-routes.show', $bookingRoute->scheduled_route_id) .'" class="text-default">'. $scheduledRouteName .'</a>' : '';
								}
								else {
										$columns['scheduled_route_id'] = $scheduledRouteName ? $scheduledRouteName : '';
								}

								$columns['booking_children'] = $bookingChildren;
								$columns['source']         = $source;
								$columns['date']           = SiteHelper::formatDateTime($bookingRoute->date);
								$columns['from']           = '<div class="eto-address-more">'. $bookingRoute->getFrom() .'</div>';
								$columns['to']             = '<div class="eto-address-more">'. $bookingRoute->getTo() .'</div>';
								$columns['waypoints']      = '<div class="eto-address-more">'. $bookingRoute->getVia() .'</div>';
								$columns['route']          = $routeName;
								$columns['vehicle']        = $bookingRoute->getVehicleList();
								$columns['meet_and_greet'] = !empty($bookingRoute->meet_and_greet) ? 'Yes' : 'No';
								$columns['contact_name']   = '<div class="eto-address-more">'. $bookingRoute->getContactFullName() .'</div>';
								$columns['contact_email']  = $bookingRoute->getEmailLink('contact_email', ['style' => 'color:#333;']);
								$columns['contact_mobile'] = $bookingRoute->getTelLink('contact_mobile', ['style' => 'color:#333;']);
								$columns['lead_passenger_name']   = !empty($bookingRoute->lead_passenger_name) ? '<div class="eto-address-more">'. $bookingRoute->getLeadPassengerFullName() .'</div>' : '';
								$columns['lead_passenger_email']   = !empty($bookingRoute->lead_passenger_email) ? '<div class="eto-address-more">'. $bookingRoute->getEmailLink('lead_passenger_email', ['style' => 'color:#333;']) .'</div>' : '';
								$columns['lead_passenger_mobile'] = $bookingRoute->getTelLink('lead_passenger_mobile', ['style' => 'color:#333;']);
								$columns['passengers'] = $bookingRoute->passengers;
								$columns['flight_number']    = $bookingRoute->flight_number;
								$columns['flight_landing_time'] = $bookingRoute->flight_landing_time;
								$columns['departure_city']    = $bookingRoute->departure_city;
								$columns['departure_flight_number'] = $bookingRoute->departure_flight_number;
								$columns['departure_flight_time'] = $bookingRoute->departure_flight_time;
								$columns['departure_flight_city'] = $bookingRoute->departure_flight_city;
								$columns['waiting_time']    = !empty($bookingRoute->waiting_time) ? $bookingRoute->waiting_time .' min' : '';
								$columns['user_id'] = $customer_id;
								$columns['user_name'] = $customer_id > 0 ? ('<div class="eto-address-more"><a href="'. route('admin.bookings.index') .'?user='. $customer_id .'" style="color:#333;">'. ($customer_company_name && config('site.user_show_company_name') ? trim($customer_company_name) . ' - ' : ''). $customer_name .'</a></div>') : '';
                $columns['department'] = !empty($bookingRoute->department) ? $bookingRoute->department : null;
								$columns['created_date']   = SiteHelper::formatDateTime($bookingRoute->created_date);
								$columns['modified_date']  = SiteHelper::formatDateTime($bookingRoute->modified_date);
								if ($page_type == 'trash') {
										$columns['deleted_at']  = SiteHelper::formatDateTime($bookingRoute->deleted_at);
								}
								$columns['total_price'] = $bookingRoute->total_price;
								$columns['total_discount'] = $bookingRoute->discount;
								$columns['price'] = $bookingRoute->getTotalPrice();
								$columns['discount'] = ($bookingRoute->discount > 0) ? $bookingRoute->getDiscount() : '';
								$columns['discount_code']  = $bookingRoute->discount_code;
								$columns['payment_status']  = $paymentStatus;
								$columns['total'] = $total;
								$columns['status'] = $status;
								$columns['status_btn'] = $status_btn;
								$columns['commission'] = $commission;
								$columns['commission_btn'] = $commission_btn;
								$columns['cash'] = $cash;
								$columns['cash_btn'] = $cash_btn;

								if (config('eto.allow_fleet_operator')) {
										$columns['fleet_id'] = $fleet_id;
										$columns['fleet_name'] = $fleet_name;
										$columns['fleet_link'] = $fleet_link;
										$columns['fleet_btn'] = $fleet_btn;
										$columns['fleet_commission'] = $fleet_commission;
										$columns['fleet_commission_btn'] = $fleet_commission_btn;
								}

								$columns['driver_id'] = $driver_id;
								$columns['driver_name'] = $driver_name;
								$columns['driver_link'] = $driver_link;
								$columns['driver_btn'] = $driver_btn;
								$columns['vehicle_id'] = $vehicle_id;
								$columns['vehicle_name'] = $vehicle_name;
								$columns['vehicle_link'] = $vehicle_link;
								$columns['vehicle_btn'] = $vehicle_btn;
								$columns['custom'] = $bookingRoute->custom;
								$columns['is_read'] = $bookingRoute->is_read;
								$columns['is_read_formatted'] = $bookingRoute->is_read ? 'Yes' : 'No';
								$columns['tracking_history'] = !empty($exportType) ? $bookingRoute->getDriverStatusesHtml('admin', 'export') : '';
								$columns['currency_symbol'] = config('site.currency_symbol');
								$columns['currency_code'] = config('site.currency_code');

								$booking[] = $columns;
						}

						// Export
						if ( !empty($exportType) ) {
								include __DIR__ .'/BackendBookingsExport.php';
						}

						$data['bookings'] = $booking;
						$data['success'] = true;
				}
				else {
						$data['message'][] = 'No booking was found!';
						$data['success'] = true;
				}

		break;
		case 'create':

					if (!auth()->user()->hasPermission('admin.bookings.create')) {
							return redirect_no_permission();
					}

					// Config
					$allowedConfig = array(
						'currency_symbol',
						'currency_code',
						'min_booking_time_limit',
						'enable_passengers',
						'enable_luggage',
						'enable_hand_luggage',
						'enable_child_seats',
						'enable_baby_seats',
						'enable_infant_seats',
						'enable_wheelchair',
						'booking_allow_one_type_of_child_seat',
						'quote_avoid_highways',
						'quote_avoid_tolls',
						'quote_avoid_ferries',
						'quote_duration_in_traffic',
						'quote_traffic_model',
						'quote_enable_shortest_route',
						'source_list',
						'status_list',
						'date_format',
						'time_format',
						'start_of_week',
					);

					// Charges - start
					$config = [
						'charge_meet_and_greet' => 0,
						'charge_child_seat' => 0,
						'charge_baby_seat' => 0,
						'charge_infant_seats' => 0,
						'charge_wheelchair' => 0,
						'charge_waiting_time' => 0,
					];

					$sql = "SELECT *
									FROM `{$dbPrefix}charge`
									WHERE `site_id`='". $siteId ."'
									ORDER BY `id` ASC";

					$resultsCharge = $db->select($sql);

					if ( !empty($resultsCharge) )
					{
						foreach($resultsCharge as $key => $value)
						{
							switch($value->type)
							{
								case 'meet_and_greet':
									$config['charge_meet_and_greet'] = (float)$value->value;
								break;
								case 'child_seat':
									$config['charge_child_seat'] = (float)$value->value;
								break;
								case 'baby_seat':
									$config['charge_baby_seat'] = (float)$value->value;
								break;
								case 'infant_seats':
									$config['charge_infant_seats'] = (float)$value->value;
								break;
								case 'wheelchair':
									$config['charge_wheelchair'] = (float)$value->value;
								break;
								case 'waiting_time':
									$config['charge_waiting_time'] = (float)$value->value;
								break;
							}
						}
					}
					// Charges - end
					// From new api
					foreach($etoAPI->configBrowser as $key => $value) {
						$config[$key] = $value;
					}

					// Old config
					$sql = "SELECT `key`, `value`
									FROM `{$dbPrefix}config`
									WHERE `site_id`='" . $siteId . "'
									ORDER BY `key` ASC";

					$resultsConfig = $db->select($sql);

					if (!empty($resultsConfig)) {
						$statusList = (new \App\Models\BookingRoute)->options->status;

						foreach ($resultsConfig as $key => $value) {
							if (in_array($value->key, $allowedConfig)) {
								if ($value->key == 'source_list') {
									$config[$value->key] = $sourceList;
								} elseif ($value->key == 'status_list') {
									$config[$value->key] = $statusList;
								} else {
									$config[$value->key] = $value->value;
								}
							}
						}

						$data['services'] = \App\Helpers\BookingHelper::getServiceList();

						// Languages
						$locales = [];

						foreach (config('app.locales') as $k => $v) {
								$locales[$k] = $v['name'];
						}

						$config['locale_list'] = $locales;

						// Driver list - start
						$tnUser = (new \App\Models\User)->getTable();
						$tnUserProfile = (new \App\Models\UserProfile)->getTable();

						$driverQuery = \App\Models\User::join($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id')
							->select($tnUser .'.*', $tnUserProfile .'.unique_id')
							->role('driver.*')
							->where($tnUser .'.status', 'approved')
							->orderBy($tnUserProfile .'.unique_id', 'asc')
							->orderBy($tnUser .'.name', 'asc')
							->get();

						$driverList = array();
						foreach($driverQuery as $driver) {
								$driverList[] = array(
										'id' => $driver->id,
										'name' => $driver->getName(true),
										'email' => $driver->email,
										'commission' => $driver->profile->commission,
										'url' => route('admin.users.show', $driver->id)
								);
						}
						$config['driver_list'] = $driverList;
						// Driver list - end


						// User list - start
						$sql = "SELECT `a`.*, `b`.*, `a`.`id` AS `main_user_id`
								FROM `{$dbPrefix}user` AS `a`
								LEFT JOIN `{$dbPrefix}user_customer` AS `b`
								ON `a`.`id`=`b`.`user_id`
								WHERE `a`.`site_id`='". $siteId ."'
								AND `a`.`type`='1'
								ORDER BY `name` ASC";

						$queryList = $db->select($sql);

						$userList = array();
						if (!empty($queryList)) {
							foreach($queryList as $key => $value) {
								$userList[] = array(
									'id' => $value->main_user_id,
									'name' => trim($value->name),
									'title' => $value->title,
									'first_name' => $value->first_name,
									'last_name' => $value->last_name,
									'email' => $value->email,
									'mobile_number' => $value->mobile_number,
									'company_name' => config('site.user_show_company_name') ? $value->company_name : '',
								);
							}
						}
						$config['user_list'] = $userList;
						// User list - end


						// Driver vehicles start
						$vehicles = \App\Models\Vehicle::with('user')->orderBy('name', 'asc')->get();

						$uPrimary = [];
						$vehiclesList = [];

						foreach ($vehicles as $key => $value) {
							if ( !isset($uPrimary[$value->user_id]) || $value->selected == 1 ) {
								$uPrimary[$value->user_id] = $value->id;
							}
						}

						foreach ($vehicles as $key => $value) {
							if ( isset($uPrimary[$value->user_id]) && $uPrimary[$value->user_id] == $value->id ) {
								$primary = 1;
							}
							else {
								$primary = 0;
							}

							$vehiclesList[] = [
								'id' => $value->id,
								'name' => $value->name,
								'user_id' => $value->user_id,
								'primary' => $primary,
								'url' => route('admin.vehicles.show', $value->id),
							];
						}

						$config['vehicle_list'] = $vehiclesList;
						// Driver vehicles end

						$data['config'] = $config;
					}
					else
					{
						$data['message'][] = $gLanguage['API']['ERROR_NO_CONFIG'];
					}


					// Language
					if (!empty($gLanguage))
					{
						$jsLanguage = $gLanguage;
						unset($jsLanguage['API']);
						$data['language'] = $jsLanguage;
					}
					else
					{
						$data['message'][] = $gLanguage['API']['ERROR_NO_LANGUAGE'];
					}


					// Categories
					$sql = "SELECT *
									FROM `{$dbPrefix}category`
									WHERE `site_id`='" . $siteId . "'
									ORDER BY `ordering` ASC, `name` ASC";

					$resultsCategory = $db->select($sql);

					if (!empty($resultsCategory))
					{
						$data['category'] = $resultsCategory;
					}
					else
					{
						$data['category'] = [];
						// $data['message'][] = $gLanguage['API']['ERROR_NO_CATEGORY'];
					}


					// Locations
					if (!empty($resultsCategory))
					{
						$categoriesFiltered = array();
						foreach($resultsCategory as $key => $value)
						{
							$categoriesFiltered[] = $value->id;
						}

						if (!empty($categoriesFiltered))
						{
							$categoriesListFiltered = implode(",", $categoriesFiltered);

							// Locations
							$sql = "SELECT *
											FROM `{$dbPrefix}location`
											WHERE `site_id`='" . $siteId . "'
											AND `category_id` IN (" . $categoriesListFiltered . ")
											AND `published`='1'
											ORDER BY `category_id` ASC, `ordering` ASC, `name` ASC";

							$resultsLocation = $db->select($sql);

							if (!empty($resultsLocation))
							{
								$data['location'] = $resultsLocation;
							}
							else
							{
								$data['location'] = [];
								// $data['message'][] = $gLanguage['API']['ERROR_NO_LOCATION'];
							}
						}
						else
						{
							$data['message'][] = $gLanguage['API']['ERROR_CATEGORY_FILTERED_EMPTY'];
						}
					}
					else
					{
						// $data['message'][] = $gLanguage['API']['ERROR_NO_CATEGORY'];
					}


					// Vehicles
					$sql = "SELECT *
									FROM `{$dbPrefix}vehicle`
									WHERE `site_id`='" . $siteId . "'
									AND `published`='1'
									ORDER BY `ordering` ASC, `name` ASC";

					$resultsVehicle = $db->select($sql);

					if (!empty($resultsVehicle))
					{
						$data['vehicle'] = $resultsVehicle;
					}
					else
					{
						$data['message'][] = $gLanguage['API']['ERROR_NO_VEHICLE'];
					}


					// Payments
					$sql = "SELECT *
									FROM `{$dbPrefix}payment`
									WHERE `site_id`='" . $siteId . "'
									AND `published`='1'
									ORDER BY `ordering` ASC, `name` ASC";

					$qPayment = $db->select($sql);

					if (!empty($qPayment))
					{
						foreach($qPayment as $key => $value) {
							if ( !empty($value->params) ) {
								$value->params = json_decode($value->params);
							}
							unset($value->params);
							$qPayment[$key] = $value;
						}

						$data['payment'] = $qPayment;
					}
					else
					{
						$data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENT'];
					}

		break;
		case 'read':

					if (!auth()->user()->hasPermission('admin.bookings.show')) {
							return redirect_no_permission();
					}

					$id = (int) $etoPost['id'];

					// Check fleet
					$sqlFleet = "";
					if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
							$sqlFleet = " AND `a`.`fleet_id`='". auth()->user()->id ."' ";
					}

					$sql = "SELECT `a`.*,
									`b`.*,
									`a`.`id` AS `route_id`,
									`a`.`ref_number` AS `route_ref_number`
							FROM `{$dbPrefix}booking_route` AS `a`
							LEFT JOIN `{$dbPrefix}booking` AS `b`
							ON `a`.`booking_id`=`b`.`id`
							WHERE /*`b`.`site_id`='" . $siteId . "'
							AND*/ `a`.`id`='" . $id . "' ". $sqlFleet ."
							LIMIT 1";

					$resultsBooking = $db->select($sql);
					if (!empty($resultsBooking[0])) {
						$resultsBooking = $resultsBooking[0];
					}

					if (!empty($resultsBooking))
					{
						$booking = array(
							'scheduledRouteId' => $resultsBooking->scheduled_route_id,
							'parentBookingId' => $resultsBooking->parent_booking_id,
							'serviceId' => $resultsBooking->service_id,
							'serviceDuration' => $resultsBooking->service_duration,
							'routeReturn' => 1,
							'refNumber' => $resultsBooking->route_ref_number,
							'route1' => array(),
							'route2' => array(),
							'status' => $resultsBooking->status,
							'locale' => !empty($resultsBooking->locale) ? $resultsBooking->locale : null,
							'source' => $resultsBooking->source,
							'sourceDetails' => $resultsBooking->source_details,
							'driverId' => $resultsBooking->driver_id,
							'fleet_id' => $resultsBooking->fleet_id,
							'fleet_commission' => $resultsBooking->fleet_commission,
							'vehicleId' => $resultsBooking->vehicle_id,
							'userId' => $resultsBooking->user_id,
							'contactTitle' => $resultsBooking->contact_title,
							'contactName' => $resultsBooking->contact_name,
							'contactEmail' => $resultsBooking->contact_email,
							'contactMobile' => $resultsBooking->contact_mobile,
							// 'leadPassenger' => $resultsBooking->lead_passenger,
							'leadPassengerTitle' => $resultsBooking->lead_passenger_title,
							'leadPassengerName' => $resultsBooking->lead_passenger_name,
							'leadPassengerEmail' => $resultsBooking->lead_passenger_email,
							'leadPassengerMobile' => $resultsBooking->lead_passenger_mobile,
							'department' => !empty($resultsBooking->department) ? $resultsBooking->department : null,
							'requirements' => $resultsBooking->requirements,
							'notes' => $resultsBooking->notes,
							'driverNotes' => $resultsBooking->driver_notes,
							'statusNotes' => $resultsBooking->status_notes,
							'discountCode' => $resultsBooking->discount_code,
							'totalPrice' => $resultsBooking->total_price,
							'totalPriceWithDiscount' => $resultsBooking->total_price - $resultsBooking->discount,
							'totalDiscount' => $resultsBooking->discount,
							'custom' => $resultsBooking->custom,
						);

						$booking['route1'] = array(
							'category' => array(
								'start' => $resultsBooking->category_start,
								'end' => $resultsBooking->category_end,
								'type' => array(
									'start' => $resultsBooking->category_type_start,
									'end' => $resultsBooking->category_type_end
								)
							),
							'location' => array(
								'start' => $resultsBooking->location_start,
								'end' => $resultsBooking->location_end
							),
							'waypoints' => json_decode($resultsBooking->waypoints),
							'waypointsComplete' => json_decode($resultsBooking->waypoints_complete),
							'address' => array(
								'start' => $resultsBooking->address_start,
								'end' => $resultsBooking->address_end
							),
							'addressComplete' => array(
								'start' => $resultsBooking->address_start_complete,
								'end' => $resultsBooking->address_end_complete
							),
							'coordinate' => array(
								'start' => array(
									'lat' => $resultsBooking->coordinate_start_lat,
									'lon' => $resultsBooking->coordinate_start_lon
								),
								'end' => array(
									'lat' => $resultsBooking->coordinate_end_lat,
									'lon' => $resultsBooking->coordinate_end_lon
								)
							),
							'date' => date('Y-m-d H:i', strtotime($resultsBooking->date)),
							'flightNumber' => $resultsBooking->flight_number,
							'flightLandingTime' => $resultsBooking->flight_landing_time,
							'departureCity' => $resultsBooking->departure_city,
							'departureFlightNumber' => $resultsBooking->departure_flight_number,
							'departureFlightTime' => $resultsBooking->departure_flight_time,
							'departureFlightCity' => $resultsBooking->departure_flight_city,
							'waitingTime' => $resultsBooking->waiting_time,
							'meetAndGreet' => $resultsBooking->meet_and_greet,
							'meetingPoint' => $resultsBooking->meeting_point,
							'vehicle' => json_decode($resultsBooking->vehicle),
							'items' => json_decode($resultsBooking->items),
							'distance' => $resultsBooking->distance,
							'duration' => $resultsBooking->duration,
							'distance_base_start' => $resultsBooking->distance_base_start,
							'duration_base_start' => $resultsBooking->duration_base_start,
							'distance_base_end' => $resultsBooking->distance_base_end,
							'duration_base_end' => $resultsBooking->duration_base_end,
							'passengers' => $resultsBooking->passengers,
							'luggage' => $resultsBooking->luggage,
							'handLuggage' => $resultsBooking->hand_luggage,
							'childSeats' => $resultsBooking->child_seats,
							'babySeats' => $resultsBooking->baby_seats,
							'infantSeats' => $resultsBooking->infant_seats,
							'wheelchair' => $resultsBooking->wheelchair,
							'totalPrice' => $resultsBooking->total_price,
							'totalPriceWithDiscount' => $resultsBooking->total_price - $resultsBooking->discount,
							'totalDiscount' => $resultsBooking->discount,
							'commission' => $resultsBooking->commission,
							'cash' => $resultsBooking->cash,
						);

						$booking['route2'] = array();

						$data['bookingId'] = $resultsBooking->route_id;
						$data['siteId'] = $resultsBooking->site_id;
						$data['booking']   = $booking;
						$data['success'] = true;
					}
					else {
						$data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING'];
					}

		break;
		case 'sendSMS':

					$from = (string)$etoPost['from'];
					$to = trim((string)$etoPost['to']);
					$msg = (string)$etoPost['msg'];

					if (config('services.sms_service_type') == 'smsgateway') {
							$params = [
									'apiKey' => config('services.smsgateway.key'),
									'deviceId' => config('services.smsgateway.device_id'),
									'numbers' => [$to],
									'message' => $msg,
							];

							if ( empty($params['apiKey']) ||
									empty($params['deviceId']) ||
									empty($params['numbers']) ||
									empty($params['message']) ) {
									$data['error_message'] = 'Incorrect SMS Gateway integration. Please check your configuration.';
							}
							else {
									require_once(base_path('vendor/easytaxioffice/smsgatewayme/autoload.php'));

									$config = \SMSGatewayMe\Client\Configuration::getDefaultConfiguration();
									$config->setApiKey('Authorization', config('services.smsgateway.key'));
									$apiClient = new \SMSGatewayMe\Client\ApiClient($config);
									$messageClient = new \SMSGatewayMe\Client\Api\MessageApi($apiClient);

									$messages = [];

									foreach($params['numbers'] as $number) {
											$messages[] = new \SMSGatewayMe\Client\Model\SendMessageRequest([
													'phoneNumber' => trim($number),
													'message' => $params['message'],
													'deviceId' => $params['deviceId']
											]);
									}

									$response = $messageClient->sendMessages($messages);

									// \Log::debug((array)$response);
									// dd($params, $response);
									// echo'<pre>'; print_r($response); echo'</pre>';

									if ($response && $response->status == 'fail') {
											$data['error_message'] = $response->message;
									}

									$data['result'] = $response;
							}
					}
					elseif (config('services.sms_service_type') == 'textlocal') {
							$params = [
									'apiKey' => config('services.textlocal.key'),
									'test' => config('services.textlocal.test') ? true : false,
									'sender' => urlencode(substr($from, 0, 11)),
									'numbers' => $to,
									'message' => rawurlencode($msg),
							];

							if ( empty($params['apiKey']) ||
									empty($params['sender']) ||
									empty($params['numbers']) ||
									empty($params['message']) ) {
									$data['error_message'] = 'Incorrect TextLocal integration. Please check your configuration.';
							}
							else {
									$client = new \GuzzleHttp\Client();

									$response = $client->request('POST', 'https://api.txtlocal.com/send/', [
											'form_params' => $params
									]);

									$response = json_decode($response->getBody());

									// \Log::debug((array)$response);
									// dd($params, $response);
									// echo'<pre>'; print_r($response); echo'</pre>';

									$errors = '';
									if ($response && $response->errors) {
											foreach ($response->errors as $kE => $vE) {
													$errors .= ($errors ? "\n" : "") . $vE->message;
											}
									}
									if ($errors) {
											$data['error_message'] = $errors;
									}

									$data['result'] = $response;
							}
					}
					elseif (config('services.sms_service_type') == 'twilio') {
							$params = [
									'sid' => config('services.twilio.sid'),
									'token' => config('services.twilio.token'),
									'from' => config('services.twilio.phone_number'),
									'sender' => substr($from, 0, 11),
									'numbers' => $to,
									'message' => $msg, // rawurlencode($msg)
							];

							if (empty($params['sid']) ||
									empty($params['token']) ||
									empty($params['sender']) ||
									empty($params['numbers']) ||
									empty($params['message'])
							) {
									$data['error_message'] = 'Incorrect Twilio integration. Please check your configuration.';
							}
							else {
									$result = null;
									$response = null;
									$errors = '';

									try {
											$client = new \GuzzleHttp\Client();

											$response = $client->request('POST', 'https://api.twilio.com/2010-04-01/Accounts/'. $params['sid'] .'/Messages.json', [
													'form_params' => [
														'To' => $params['numbers'],
														'From' => $params['from'],
														'Body' => $params['message']
													],
													'auth' => [$params['sid'], $params['token']]
											]);

											$response = json_decode($response->getBody());

	                    // $twilio = new \Twilio\Rest\Client($params['sid'], $params['token']);
	                    // $response = $twilio->messages->create($params['numbers'], [
	                    //     'from' => $params['from'],
	                    //     'body' => $params['message']
	                    // ]);

											if (!empty($response->sid)) {
													$result = [
															'sid' => $response->sid,
															'status' => $response->status,
															'to' => $response->to,
															'from' => $response->from,
															'body' => $response->body,
													];
											}

	                    if (in_array($response->status, ['failed', 'undelivered']) && $response->error_message) {
													$errors = 'Error (Twilio): '. $response->error_message .' (Code '. $v->error_code .')';
	                        \Log::error($errors);
	                    }
	                }
	                catch (\Exception $e) {
											$errors = 'Error (Twilio): '. $e->getMessage();
	                    \Log::error($errors);
	                }

									// \Log::info($result);
									// \Log::debug((array)$response);
									// dd($params, $response);
									// echo'<pre>'; print_r($response); echo'</pre>';

									if ($errors) {
											$data['error_message'] = $errors;
									}

									$data['result'] = $result;
							}
					}
					else {
							$data['error_message'] = 'To send SMS messages you need to activate one of the SMS services in settings tab first.';
					}

		break;
		case 'update':

				if (!auth()->user()->hasPermission('admin.bookings.edit')) {
						return redirect_no_permission();
				}

				$booking = (array) $etoPost['booking'];
				$id      = (int) $etoPost['id'];

				// \Log::debug((array)$booking);

				// Check fleet
				$sqlFleet = "";
				if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
						$sqlFleet = " AND `a`.`fleet_id`='". auth()->user()->id ."' ";
				}

				$sql = "SELECT `a`.*,
							`b`.*,
							`a`.`id` AS `route_id`,
							`a`.`ref_number` AS `route_ref_number`
					FROM `{$dbPrefix}booking_route` AS `a`
					LEFT JOIN `{$dbPrefix}booking` AS `b`
					ON `a`.`booking_id`=`b`.`id`
					WHERE /*`b`.`site_id`='" . $siteId . "'
					AND*/ `a`.`id`='" . $id . "' ". $sqlFleet ."
					LIMIT 1";

				$resultsBooking = $db->select($sql);
				if (!empty($resultsBooking[0])) {
					$resultsBooking = $resultsBooking[0];
				}

				if (!empty($resultsBooking)) {
					$finishType = 'update';
				}
				else {
					$finishType = 'insert';
				}

				if (!empty($booking)) {
					$previousStatus = (string)$resultsBooking->status;

					// Payments
					$payment = new \stdClass();
					$sql = "SELECT *
								FROM `{$dbPrefix}payment`
								WHERE `site_id`='" . $siteId . "'
								AND `id`='" . (int) $booking['payment'] . "'
								LIMIT 1";

					$qPayment = $db->select($sql);
					if (!empty($qPayment[0])) {
						$qPayment = $qPayment[0];
					}

					if ( !empty($qPayment) ) {
						$payment = $qPayment;
						if ( !empty($payment->params) ) {
							$payment->params = json_decode($payment->params);
						}
					}
					else {
						$payment = new \stdClass();
						// $data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENT'];
					}

					// Vehicles
					$vehicle = array();
					$sql = "SELECT *
								FROM `{$dbPrefix}vehicle`
								WHERE `site_id`='" . $siteId . "'
								ORDER BY `ordering` ASC, `name` ASC"; // AND `published`='1'

					$resultsVehicle = $db->select($sql);

					if (!empty($resultsVehicle))
					{
						$vehicle = $resultsVehicle;
					}
					else
					{
						$data['message'][] = $gLanguage['API']['ERROR_NO_VEHICLE'];
					}

						$refNumber = 'REF-' . date('Ymd-His') . rand(1000, 100000);
						$currentDate = date('Y-m-d H:i:s');
						$routeReturn = (int) $booking['routeReturn'];
						$row = new \stdClass();

						if ($finishType == 'update')
						{
							$oldBookingData = json_decode(json_encode(\App\Models\BookingRoute::with('booking')->find($resultsBooking->route_id)));
							$row->id = $resultsBooking->booking_id;
						}
						else
						{
							$row->id = null;
							$row->site_id   = (int) $siteId;
							//$row->user_id = (int) $userId;
							$row->unique_key = md5('booking_'. date('Y-m-d H:i:s') . rand(1000, 100000));
							$row->ref_number = $refNumber;
						}

					// $driverId = (int)$booking['driverId'];
					// $vehicleId = (int)$booking['vehicleId'];
					$userId = (int)$booking['userId'];
					$row->user_id = $userId;

					// Route 1
					$driverId = isset($booking['route1']['driverId']) ? (int)$booking['route1']['driverId'] : (int)$booking['driverId'];
					$vehicleId = isset($booking['route1']['vehicleId']) ? (int)$booking['route1']['vehicleId'] : (int)$booking['vehicleId'];

					$rowR1 = new \stdClass();

					if ($finishType == 'update') {
							$rowR1->id = $resultsBooking->route_id;
							$rowR1->modified_date = $currentDate;
					}
					else {
							$rowR1->id          = null;
							$rowR1->booking_id  = 0;
							$rowR1->ref_number  = $row->ref_number . 'a';
							$rowR1->route       = 1;
							$rowR1->created_date = $currentDate;
							$rowR1->modified_date = $currentDate;
					}

					$rowR1->scheduled_route_id = (int) $booking['scheduledRouteId'];
					$rowR1->parent_booking_id = (int) $booking['parentBookingId'];

					$rowR1->service_id = (int) $booking['serviceId'];
					$rowR1->service_duration = (int) $booking['serviceDuration'];

					$rowR1->status = (string) $booking['status'] ?: 'pending';
					$rowR1->driver_id = $driverId;

					$driverTemp = \App\Models\User::with('profile')->find( $driverId );
					if ( !empty($driverTemp) ) {
						$rowR1->driver_data = json_encode($driverTemp);
					}
					else {
						// $rowR1->driver_data = null;
					}

					$rowR1->vehicle_id = $vehicleId;

					$vehicleTemp = \App\Models\Vehicle::find( $vehicleId );
					if ( !empty($vehicleTemp->id) ) {
						$rowR1->vehicle_data = json_encode($vehicleTemp);
					}
					else {
						// $rowR1->vehicle_data = null;
					}

					$rowR1->category_start       = (string) $booking['route1']['category']['start'];
					$rowR1->category_type_start  = (string) $booking['route1']['category']['type']['start'];
					$rowR1->location_start       = (string) $booking['route1']['location']['start'];
					$rowR1->address_start        = (string) $booking['route1']['address']['start'];
					$rowR1->coordinate_start_lat = (float) $booking['route1']['coordinate']['start']['lat'];
					$rowR1->coordinate_start_lon = (float) $booking['route1']['coordinate']['start']['lon'];
					$rowR1->waypoints            = json_encode((array)$booking['route1']['waypoints']);
					$rowR1->waypoints_complete   = json_encode((array)$booking['route1']['waypointsComplete']);
					$rowR1->category_end         = (string) $booking['route1']['category']['end'];
					$rowR1->category_type_end    = (string) $booking['route1']['category']['type']['end'];
					$rowR1->location_end         = (string) $booking['route1']['location']['end'];
					$rowR1->address_end          = (string) $booking['route1']['address']['end'];
					$rowR1->coordinate_end_lat   = (float) $booking['route1']['coordinate']['end']['lat'];
					$rowR1->coordinate_end_lon   = (float) $booking['route1']['coordinate']['end']['lon'];
					$rowR1->address_start_complete  = (string) $booking['route1']['addressComplete']['start'];
					$rowR1->address_end_complete    = (string) $booking['route1']['addressComplete']['end'];
					$rowR1->distance             = (float) $booking['route1']['distance'];
					$rowR1->duration             = (int) $booking['route1']['duration'];
					$rowR1->distance_base_start  = (float)$booking['route1']['distance_base_start'];
					$rowR1->duration_base_start  = (int)$booking['route1']['duration_base_start'];
					$rowR1->distance_base_end    = (float)$booking['route1']['distance_base_end'];
					$rowR1->duration_base_end    = (int)$booking['route1']['duration_base_end'];
					$rowR1->date                 = (string) $booking['route1']['date'];
					$rowR1->flight_number        = (string) $booking['route1']['flightNumber'];
					$rowR1->flight_landing_time  = (string) $booking['route1']['flightLandingTime'];
					$rowR1->departure_city       = (string) $booking['route1']['departureCity'];
					$rowR1->departure_flight_number = (string) $booking['route1']['departureFlightNumber'];
					$rowR1->departure_flight_time = (string) $booking['route1']['departureFlightTime'];
					$rowR1->departure_flight_city = (string) $booking['route1']['departureFlightCity'];
					$rowR1->waiting_time         = (int) $booking['route1']['waitingTime'];
					$rowR1->meet_and_greet       = (string) $booking['route1']['meetAndGreet'];
					$rowR1->meeting_point 	     = (string)$booking['route1']['meetingPoint'];
					$rowR1->fleet_id             = isset($booking['route1']['fleet_id']) ? (int)$booking['route1']['fleet_id'] : 0;
					$rowR1->fleet_commission     = (int) $booking['route1']['fleet_commission'];
          $rowR1->department           = !empty($booking['route1']['department']) ? (string) $booking['route1']['department'] : null;

					if ( !empty($booking['route1']['custom']) ) {
							$rowR1->custom = $booking['route1']['custom'];
					}

					if (empty($booking['route1']['vehicle'])) {
							$booking['route1']['vehicle'] = [];
					}

					$vehicleList = '';
					if (!empty($vehicle))
					{
							foreach($booking['route1']['vehicle'] as $key1 => $value1)
							{
									foreach($vehicle as $key2 => $value2)
									{
											if ($value1['id'] == $value2->id)
											{
													if (!empty($vehicleList))
													{
															$vehicleList .= "\n";
													}
													$vehicleList .= ((int)$value1['amount'] > 1 ? $value1['amount']." x " : "") . $value2->name;
													break;
											}
									}
							}
					}

					$rowR1->vehicle      = json_encode((array) $booking['route1']['vehicle']);
					$rowR1->vehicle_list = $vehicleList;
					$rowR1->passengers   = (int) $booking['route1']['passengers'];
					$rowR1->luggage      = (int) $booking['route1']['luggage'];
					$rowR1->hand_luggage = (int) $booking['route1']['handLuggage'];
					$rowR1->child_seats  = (int) $booking['route1']['childSeats'];
					$rowR1->baby_seats  = (int) $booking['route1']['babySeats'];
					$rowR1->infant_seats  = (int) $booking['route1']['infantSeats'];
					$rowR1->wheelchair  = (int) $booking['route1']['wheelchair'];

					// Items
					$itemsListPost = (array)$booking['route1']['items'];
					$items = array();
					foreach($itemsListPost as $keyItem => $valueItem) {
						$rowItem = new \stdClass();
						$rowItem->type = $valueItem['type'];
						$rowItem->name = $valueItem['name'];
						$rowItem->value = $valueItem['value'];
						$rowItem->amount = $valueItem['amount'];
						$rowItem->original_name = (string)$valueItem['original_name'];
						$items[] = $rowItem;
					}
					$rowR1->items = json_encode($items);

					$rowR1->extra_charges_list  = '';
					$rowR1->extra_charges_price = 0;

					$total = (float)$booking['route1']['totalPrice'];
					$discount = (float)$booking['route1']['totalDiscount'];
					if ( $discount > $total ) {
						$discount = $total;
					}

					$rowR1->total_price = $total;
					$rowR1->commission = (float)$booking['route1']['commission'];
					$rowR1->cash = (float)$booking['route1']['cash'];
					$rowR1->discount = $discount;
					$rowR1->discount_code = (string)$booking['discountCode'];
					$rowR1->contact_title        = (string) $booking['contactTitle'];
					$rowR1->contact_name        = (string) $booking['contactName'];
					$rowR1->contact_email       = (string) $booking['contactEmail'];
					$rowR1->contact_mobile      = (string) $booking['contactMobile'];
					//$rowR1->lead_passenger            = (string) $booking['leadPassenger'];
					$rowR1->lead_passenger_title      = (string) $booking['leadPassengerTitle'];
					$rowR1->lead_passenger_name       = (string) $booking['leadPassengerName'];
					$rowR1->lead_passenger_email       = (string) $booking['leadPassengerEmail'];
					$rowR1->lead_passenger_mobile     = (string) $booking['leadPassengerMobile'];
					$rowR1->requirements        = (string) $booking['requirements'];
					$rowR1->notes        = (string) $booking['notes'];
					$rowR1->locale = !empty($booking['locale']) ? (string) $booking['locale'] : null;
					$rowR1->status_notes        = (string) $booking['statusNotes'];
					$rowR1->source         = (string) $booking['source'];
					$rowR1->source_details = (string) $booking['sourceDetails'];
					$rowR1->ip           = (string) $_SERVER['REMOTE_ADDR'];
					$rowR1->is_read = 1;
					$rowR1->driver_notes = !empty($booking['route1']['driverNotes']) ? (string)$booking['route1']['driverNotes'] : '';

					if ($routeReturn == 2) {
						// Route 2
						$driverId = isset($booking['route2']['driverId']) ? (int)$booking['route2']['driverId'] : (int)$booking['driverId'];
						$vehicleId = isset($booking['route2']['vehicleId']) ? (int)$booking['route2']['vehicleId'] : (int)$booking['vehicleId'];

						$rowR2 = new \stdClass();

						if ($finishType == 'update')	{
								$rowR2->id = $resultsBooking->route_id;
								$rowR2->modified_date = $currentDate;
						}
						else {
								$rowR2->id          = null;
								$rowR2->booking_id  = 0;
								$rowR2->ref_number  = $row->ref_number . 'b';
								$rowR2->route       = 2;
								$rowR2->created_date = $currentDate;
								$rowR2->modified_date = $currentDate;
						}

						$rowR2->scheduled_route_id = (int) $booking['scheduledRouteId'];
						$rowR2->parent_booking_id = (int) $booking['parentBookingId'];

						$rowR2->service_id = (int) $booking['serviceId'];
						$rowR2->service_duration = (int) $booking['serviceDuration'];

						$rowR2->status = (string) $booking['status'] ?: 'pending';
						$rowR2->driver_id = $driverId;

						$driverTemp = \App\Models\User::with('profile')->find( $driverId );
						if ( !empty($driverTemp) ) {
								$rowR2->driver_data = json_encode($driverTemp);
						}
						else {
							// $rowR2->driver_data = null;
						}

						$rowR2->vehicle_id = $vehicleId;

						$vehicleTemp = \App\Models\Vehicle::find( $vehicleId );
						if ( !empty($vehicleTemp->id) ) {
							$rowR2->vehicle_data = json_encode($vehicleTemp);
						}
						else {
							// $rowR2->vehicle_data = null;
						}

						$rowR2->category_start       = (string) $booking['route2']['category']['start'];
						$rowR2->category_type_start  = (string) $booking['route2']['category']['type']['start'];
						$rowR2->location_start       = (string) $booking['route2']['location']['start'];
						$rowR2->address_start        = (string) $booking['route2']['address']['start'];
						$rowR2->coordinate_start_lat = (float) $booking['route2']['coordinate']['start']['lat'];
						$rowR2->coordinate_start_lon = (float) $booking['route2']['coordinate']['start']['lon'];
						$rowR2->waypoints = json_encode((array)$booking['route2']['waypoints']);
						$rowR2->waypoints_complete = json_encode((array)$booking['route2']['waypointsComplete']);
						$rowR2->address_end          = (string) $booking['route2']['address']['end'];
						$rowR2->category_end         = (string) $booking['route2']['category']['end'];
						$rowR2->category_type_end    = (string) $booking['route2']['category']['type']['end'];
						$rowR2->location_end         = (string) $booking['route2']['location']['end'];
						$rowR2->coordinate_end_lat   = (float) $booking['route2']['coordinate']['end']['lat'];
						$rowR2->coordinate_end_lon   = (float) $booking['route2']['coordinate']['end']['lon'];
						$rowR2->address_start_complete  = (string) $booking['route2']['addressComplete']['start'];
						$rowR2->address_end_complete    = (string) $booking['route2']['addressComplete']['end'];
						$rowR2->distance             = (float) $booking['route2']['distance'];
						$rowR2->duration             = (int) $booking['route2']['duration'];
						$rowR2->distance_base_start = (float)$booking['route2']['distance_base_start'];
						$rowR2->duration_base_start = (int)$booking['route2']['duration_base_start'];
						$rowR2->distance_base_end = (float)$booking['route2']['distance_base_end'];
						$rowR2->duration_base_end = (int)$booking['route2']['duration_base_end'];
						$rowR2->date                 = (string) $booking['route2']['date'];
						$rowR2->flight_number        = (string) $booking['route2']['flightNumber'];
						$rowR2->flight_landing_time        = (string) $booking['route2']['flightLandingTime'];
						$rowR2->departure_city       = (string) $booking['route2']['departureCity'];
						$rowR2->departure_flight_number = (string) $booking['route2']['departureFlightNumber'];
						$rowR2->departure_flight_time = (string) $booking['route2']['departureFlightTime'];
						$rowR2->departure_flight_city = (string) $booking['route2']['departureFlightCity'];
						$rowR2->waiting_time       = (int) $booking['route2']['waitingTime'];
						$rowR2->meet_and_greet       = (string) $booking['route2']['meetAndGreet'];
						$rowR2->meeting_point				 = (string)$booking['route2']['meetingPoint'];
            $rowR2->fleet_id             = isset($booking['route2']['fleet_id']) ? (int)$booking['route2']['fleet_id'] : 0;
            $rowR2->fleet_commission     = (int) $booking['route2']['fleet_commission'];
            $rowR2->department           = !empty($booking['route2']['department']) ? (string) $booking['route1']['department'] : null;

						if ( !empty($booking['route2']['custom']) ) {
							$rowR2->custom = $booking['route2']['custom'];
						}

						if (empty($booking['route2']['vehicle'])) {
								$booking['route2']['vehicle'] = [];
						}

						$vehicleList = '';
						if (!empty($vehicle))
						{
								foreach($booking['route2']['vehicle'] as $key1 => $value1)
								{
										foreach($vehicle as $key2 => $value2)
										{
												if ($value1['id'] == $value2->id)
												{
														if (!empty($vehicleList))
														{
																$vehicleList .= "\n";
														}
														$vehicleList .= ((int)$value1['amount'] > 1 ? $value1['amount']." x " : "") . $value2->name;
														break;
												}
										}
								}
						}

						$rowR2->vehicle      = json_encode((array) $booking['route2']['vehicle']);
						$rowR2->vehicle_list = $vehicleList;
						$rowR2->passengers   = (int) $booking['route2']['passengers'];
						$rowR2->luggage      = (int) $booking['route2']['luggage'];
						$rowR2->hand_luggage = (int) $booking['route2']['handLuggage'];
						$rowR2->child_seats  = (int) $booking['route2']['childSeats'];
						$rowR2->baby_seats  = (int) $booking['route2']['babySeats'];
						$rowR2->infant_seats  = (int) $booking['route2']['infantSeats'];
						$rowR2->wheelchair  = (int) $booking['route2']['wheelchair'];

						// Items
						$itemsListPost = (array)$booking['route2']['items'];
						$items = array();
						foreach($itemsListPost as $keyItem => $valueItem) {
							$rowItem = new \stdClass();
							$rowItem->type = $valueItem['type'];
							$rowItem->name = $valueItem['name'];
							$rowItem->value = $valueItem['value'];
							$rowItem->amount = $valueItem['amount'];
							$rowItem->original_name = (string)$valueItem['original_name'];
							$items[] = $rowItem;
						}
						$rowR2->items = json_encode($items);

						$rowR2->extra_charges_list  = '';
						$rowR2->extra_charges_price = 0;

						$total = (float)$booking['route2']['totalPrice'];
						$discount = (float)$booking['route2']['totalDiscount'];
						if ( $discount > $total ) {
							$discount = $total;
						}

						$rowR2->total_price = $total;
						$rowR2->commission = (float)$booking['route2']['commission'];
						$rowR2->cash = (float)$booking['route2']['cash'];
						$rowR2->discount = $discount;
						$rowR2->discount_code = (string)$booking['discountCode'];
						$rowR2->contact_title        = (string) $booking['contactTitle'];
						$rowR2->contact_name        = (string) $booking['contactName'];
						$rowR2->contact_email       = (string) $booking['contactEmail'];
						$rowR2->contact_mobile      = (string) $booking['contactMobile'];
						//$rowR2->lead_passenger            = (string) $booking['leadPassenger'];
						$rowR2->lead_passenger_title      = (string) $booking['leadPassengerTitle'];
						$rowR2->lead_passenger_name       = (string) $booking['leadPassengerName'];
						$rowR2->lead_passenger_email       = (string) $booking['leadPassengerEmail'];
						$rowR2->lead_passenger_mobile     = (string) $booking['leadPassengerMobile'];
						$rowR2->requirements        = (string) $booking['requirements'];
						$rowR2->notes        = (string) $booking['notes'];
						$rowR2->locale = !empty($booking['locale']) ? (string) $booking['locale'] : null;
						$rowR2->status_notes        = (string) $booking['statusNotes'];
						$rowR2->source         = (string) $booking['source'];
						$rowR2->source_details = (string) $booking['sourceDetails'];
						$rowR2->ip           = (string) $_SERVER['REMOTE_ADDR'];
						$rowR2->is_read = 1;
						$rowR2->driver_notes = !empty($booking['route2']['driverNotes']) ? (string)$booking['route2']['driverNotes'] : '';
					}


					// Master booking - start
					$scheduled = \App\Models\ScheduledRoute::find($rowR1->scheduled_route_id);

					if ($scheduled->id) {
						$parentID = 0;

						$parentBooking = \App\Models\BookingRoute::where('scheduled_route_id', $scheduled->id)
							->where('date', $rowR1->date)
							->where('parent_booking_id', 0)
							->first();

						if ($parentBooking->id) {
							$parentID = $parentBooking->id;
						}
						else {
							$status = 'pending';
							$driver_id = 0;
							$driver_data = null;
							$vehicle_id = 0;
							$vehicle_data = null;

							if ( !empty($scheduled->driver_id) ) {
								$driver = \App\Models\User::find($scheduled->driver_id);
								$driver_id = $driver->id;
								$driver_data = json_encode($driver);
								$status = 'assigned';
							}

							if ( !empty($scheduled->vehicle_id) ) {
								$vehicle = \App\Models\Vehicle::find($scheduled->vehicle_id);
								$vehicle_id = $vehicle->id;
								$vehicle_data = json_encode($vehicle);
							}

							$bData = array_merge((array) clone $row, [
									'unique_key' => md5('booking_'. date('Y-m-d H:i:s') . rand(1000, 100000)),
									'ref_number' => $refNumber,
									'user_id' => 0,
							]);
							$newBooking = \App\Models\Booking::find(
									\DB::table('booking')->insertGetId($bData)
							);

							if ($newBooking->id) {
									$bData = array_merge((array) clone $rowR1, [
											'booking_id' => $newBooking->id,
											'parent_booking_id' => 0,
											'driver_id' => $driver_id,
											'driver_data' => $driver_data,
											'vehicle_id' => $vehicle_id,
											'vehicle_data' => $vehicle_data,
											'commission' => $scheduled->getParams('raw')->commission,
											'passengers' => 0,
											'items' => null,
											'total_price' => 0,
											'contact_title' => '',
											'contact_name' => '',
											'contact_email' => '',
											'contact_mobile' => '',
											'requirements' => '',
											'locale' => null,
											'ip' => '',
											'status' => $status,
											'ref_number' => $refNumber,
											'modified_date' => date('Y-m-d H:i:s'),
											'created_date' => date('Y-m-d H:i:s'),
									]);
									$newBookingRoute = \App\Models\BookingRoute::find(
											\DB::table('booking_route')->insertGetId($bData)
									);

									$refGenerator = new \App\Models\BookingRoute;
									$refNumber = $refGenerator->generateRefNumber([
											'id' => $newBookingRoute->id,
											'pickupDateTime' => $newBookingRoute->date
									]);

									$newBooking->ref_number = $refNumber;
									$newBooking->save();

									$newBookingRoute->ref_number = $refNumber;
									$newBookingRoute->save();

									$parentID = $newBookingRoute->id;
								}
						}

						$rowR1->parent_booking_id = $parentID;
					}
					// Master booking - end


				if ($finishType == 'update') { // Update
						$results = \DB::table('booking')->where('id', $row->id)->update((array)$row);
						$results = $row->id;

						if (!empty($results)) {
								// $bookingForStatus = App\Models\BookingRoute::find($id);
								$bookingForStatus = \App\Models\BookingRoute::with([
								    'booking' => function($query) {
								        $query->withTrashed();
								    },
								    'bookingTransactions' => function($query) {
								        $query->withTrashed();
								    },
								    'bookingParams'
								])->withTrashed()->find($id);

								$resultsR1 = \DB::table('booking_route')->where('id', $rowR1->id)->update((array)$rowR1);
								$resultsR1 = $rowR1->id;

								if ($bookingForStatus->status != $rowR1->status) {
										// $bookingForStatus = App\Models\BookingRoute::find($id);
										$bookingForStatus = \App\Models\BookingRoute::with([
										    'booking' => function($query) {
										        $query->withTrashed();
										    },
										    'bookingTransactions' => function($query) {
										        $query->withTrashed();
										    },
										    'bookingParams'
										])->withTrashed()->find($id);

										$bookingForStatus->setDriverStatus(auth()->user()->id);
								}
						}
					}
					else { // Insert
						$results = \DB::table('booking')->insertGetId((array)$row);
						$row->id = $results;

						if (!empty($results)) {
								$refGenerator = new \App\Models\BookingRoute;
								$refNumberBase = $refGenerator->generateRefNumber([
										'exclude' => ['id', 'create', 'pickup']
								]);

								$refNumber = $refGenerator->generateRefNumber([
										'ref_number' => $refNumberBase,
										'id' => $row->id,
										'pickupDateTime' => $rowR1->date
								]);

								$rowUpdate = new \stdClass();
								$rowUpdate->id = $row->id;
								$rowUpdate->ref_number = $refNumber;
								$resultsUpdate = \DB::table('booking')->where('id', $rowUpdate->id)->update((array)$rowUpdate);
								$resultsUpdate = $rowUpdate->id;

								if (!empty($resultsUpdate)) { // Updated
										if ($routeReturn == 2) {
												$rowR1->ref_number = $rowUpdate->ref_number .'a';
												$rowR2->ref_number = $rowUpdate->ref_number .'b';
										}
										else {
												$rowR1->ref_number = $rowUpdate->ref_number;
										}
								}

								$rowR1->booking_id = $row->id;

								if ($rowR1->fleet_id == 0 && config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
									 $rowR1->fleet_id = auth()->user()->id;
								}

								$resultsR1 = \DB::table('booking_route')->insertGetId((array)$rowR1);
								$rowR1->id = $resultsR1;

								// $bookingForStatus = App\Models\BookingRoute::find($rowR1->id);
								// $bookingForStatus->setDriverStatus(auth()->user()->id);

								if ($routeReturn == 2) {
										$rowR2->booking_id = $row->id;

										if ($rowR2->fleet_id == 0 && config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
											 $rowR2->fleet_id = auth()->user()->id;
										}

										$resultsR2 = \DB::table('booking_route')->insertGetId((array)$rowR2);
										$rowR2->id = $resultsR2;

										// $bookingForStatus = App\Models\BookingRoute::find($rowR2->id);
										// $bookingForStatus->setDriverStatus(auth()->user()->id);
								}
						}
				}

					// Transactions
					if ($finishType != 'update') { // insert
						$total = ($booking['route1']['totalPrice'] - $booking['route1']['totalDiscount']) + ($booking['route2']['totalPrice'] - $booking['route2']['totalDiscount']);
						$deposit = (float)$booking['totalDeposit'];

						if ($payment->id && $total > 0 ) {
							$balance = $total - $deposit;
							$total_charge = 0;
							$deposit_charge = 0;
							$balance_charge = 0;

							if ($payment->price ) {
								if ($payment->factor_type == 1 ) {
									$total_charge = ($total / 100) * $payment->price;
									$deposit_charge = ($deposit / 100) * $payment->price;
									$balance_charge = ($balance / 100) * $payment->price;
								} else {
									$total_charge = $payment->price;
									$deposit_charge = $payment->price;
									$balance_charge = $payment->price;
								}
							}

							if ($deposit > 0 && $deposit < $total ) {
								// Deposit
								$transaction = new \App\Models\Transaction;
								$transaction->relation_type = 'booking';
								$transaction->relation_id = $row->id;
								$transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
								$transaction->name = trans('booking.transaction.deposit');
								$transaction->description = '';
								$transaction->payment_id = $payment->id;
								$transaction->payment_method = $payment->method;
								$transaction->payment_name = $payment->name;
								$transaction->payment_charge = $deposit_charge;
								$transaction->currency_id = 0;
								$transaction->amount = $deposit;
								$transaction->ip = null;
								$transaction->response = null;
								$transaction->requested_at = null;
								$transaction->status = 'pending';
								$transaction->save();

								// Balance
								$transaction = new \App\Models\Transaction;
								$transaction->relation_type = 'booking';
								$transaction->relation_id = $row->id;
								$transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
								$transaction->name = trans('booking.transaction.balance');
								$transaction->description = '';
								$transaction->payment_id = $payment->id;
								$transaction->payment_method = $payment->method;
								$transaction->payment_name = $payment->name;
								$transaction->payment_charge = $balance_charge;

								$cashPayment = \App\Models\Payment::where('site_id', '=', $siteId)->where('method', '=', 'cash')->first();

								if ($gConfig['booking_deposit_balance'] == 'cash' && !empty($cashPayment) ) {
									$balance_charge = 0;

									if ($cashPayment->price ) {
										if ($cashPayment->factor_type == 1 ) {
											$balance_charge = ($balance / 100) * $cashPayment->price;
										} else {
											$balance_charge = $cashPayment->price;
										}
									}

									$transaction->payment_id = $cashPayment->id;
									$transaction->payment_method = $cashPayment->method;
									$transaction->payment_name = $cashPayment->name;
									$transaction->payment_charge = $balance_charge;
								}

								$transaction->currency_id = 0;
								$transaction->amount = $balance;
								$transaction->ip = null;
								$transaction->response = null;
								$transaction->requested_at = null; // Carbon::now() $booking['route2']['date']
								$transaction->status = 'pending';
								$transaction->save();
							} else {
								// Full amount
								$transaction = new \App\Models\Transaction;
								$transaction->relation_type = 'booking';
								$transaction->relation_id = $row->id;
								$transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
								$transaction->name = trans('booking.transaction.full_amount');
								$transaction->description = '';
								$transaction->payment_id = $payment->id;
								$transaction->payment_method = $payment->method;
								$transaction->payment_name = $payment->name;
								$transaction->payment_charge = $total_charge;
								$transaction->currency_id = 0;
								$transaction->amount = $total;
								$transaction->ip = null;
								$transaction->response = null;
								$transaction->requested_at = null;
								$transaction->status = 'pending';
								$transaction->save();
							}
						}
					}

					// \Log::debug([$booking]);

					// Email
					if (!empty($results)) {
						if ($finishType != 'update') {
								$booking1 = \App\Models\BookingRoute::find($rowR1->id);

								activity()
									->performedOn($booking1)
									->useLog($finishType)
									->log('#:subject.ref_number');

								if ($rowR2->id) {
										$booking2 = \App\Models\BookingRoute::find($rowR2->id);

										activity()
											->performedOn($booking2)
											->useLog($finishType)
											->log('#:subject.ref_number');
								}
						}
						else {
								$updates = [];
								// $bookingData = \App\Models\BookingRoute::with('booking')->find($rowR1->id);
								$bookingData = \App\Models\BookingRoute::with([
								    'booking' => function($query) {
								        $query->withTrashed();
								    },
								    'bookingTransactions' => function($query) {
								        $query->withTrashed();
								    },
								    'bookingParams'
								])->withTrashed()->find($rowR1->id);

								$bookingDataP = json_decode(\GuzzleHttp\json_encode($bookingData));

								if ($oldBookingData) {
										if ($bookingDataP->status != $oldBookingData->status || $bookingDataP->driver_id != $oldBookingData->driver_id) {
												if (!empty($bookingDataP->driver_id)) {
														\App\Helpers\BookingHelper::setActiveDriver($oldBookingData, $bookingDataP->status, $bookingDataP->driver_id);
												}
										}

										if ($bookingDataP->status != $oldBookingData->status) {
												$updates['status'] = ['from'=>$oldBookingData->status, 'to' => $bookingDataP->status];
										}
										if ($bookingDataP->address_start != $oldBookingData->address_start) {
												$updates['address_start'] = ['from'=>$oldBookingData->address_start, 'to' => $bookingDataP->address_start];
										}
										if ($bookingDataP->address_end != $oldBookingData->address_end) {
												$updates['address_end'] = ['from'=>$oldBookingData->address_end, 'to' => $bookingDataP->address_end];
										}
										if ($bookingDataP->total_price != $oldBookingData->total_price) {
												$updates['total_price'] = ['from'=>$oldBookingData->total_price, 'to' => $bookingDataP->total_price];
										}
										if ($bookingDataP->driver_id != $oldBookingData->driver_id) {
												$updates['status'] = ['from'=>$oldBookingData->driver_id, 'to' => $bookingDataP->driver_id];
										}
										if ($bookingDataP->booking->user_id != $userId) {
												$updates['user_id'] = ['from'=>$oldBookingData->user_id, 'to' => $bookingDataP->booking->user_id];
										}
								}

								unset($bookingDataP);
								unset($oldBookingData);

								activity()
									->performedOn($bookingData)
									->withProperties($updates)
									->useLog($finishType)
									->log('#:subject.ref_number');

								unset($updates);
								unset($bookingDataP);
						}

						$notifyMsg = $booking['notifyMsg'] ? SiteHelper::nl2br2($booking['notifyMsg']) : '';
						$notifyEmail = $booking['notifyEmail'] ? $booking['notifyEmail'] : '';
						$notifyStatus = $booking['notifyStatus'] ? true : false;
						$notifyInvoice = $booking['notifyPayment'] ? true : false;

						$sql = "SELECT *
									FROM `{$dbPrefix}booking_route`
									WHERE `booking_id`='" . $row->id . "'
									ORDER BY `ref_number` ASC";

						$resultsBookingRoute = $db->select($sql);

						if (!empty($resultsBookingRoute)) {
								foreach ($resultsBookingRoute as $key => $value) {
										if ($finishType == 'update' && $rowR1->id != $value->id ) {
												continue;
										}

										// $booking = \App\Models\BookingRoute::find($value->id);
										$booking = \App\Models\BookingRoute::with([
										    'booking' => function($query) {
										        $query->withTrashed();
										    },
										    'bookingTransactions' => function($query) {
										        $query->withTrashed();
										    },
										    'bookingParams'
										])->withTrashed()->find($value->id);

										// Flight details
										if (config('eto.allow_flightstats') && config('services.flightstats.enabled')) {
												\App\Http\Controllers\FlightController::updateFlightDetails($booking->id, 'pickup');
												\App\Http\Controllers\FlightController::updateFlightDetails($booking->id, 'dropoff');
										}

										if (config('eto_dispatch.enable_autodispatch') && config('eto_dispatch.assign_driver_on_status_change') &&
												in_array($booking->status, ['auto_dispatch']) && $booking->driver_id == 0) {
												\App\Http\Controllers\DispatchDriverController::availableBookings($booking->id);
										}

										$notifications = [];

										if ( $notifyStatus ) {
											$notifications[] = [
												'type' => 'all',
												'message' => $notifyMsg,
											];
										}

										if ($notifyEmail ) {
											$notifications[] = [
												'type' => 'pending',
												'role' => [
													'other' => [
														'email' => $notifyEmail
													]
												],
												'message' => $notifyMsg,
											];
										}

										if ($notifyInvoice ) {
											$notifications[] = [
												'type' => 'invoice',
												'role' => [
													'customer' => []
												],
												'message' => $notifyMsg,
											];
										}

										if ($notifyStatus && $finishType == 'insert' && in_array($booking->status, ['assigned', 'auto_dispatch'])) {
											$notifications[] = [
												'type' => 'pending',
												'message' => $notifyMsg,
											];
										}

										if (!empty($booking->locale) ) {
											app()->setLocale($booking->locale);
										} elseif ( !empty($gConfig['language']) ) {
											app()->setLocale($gConfig['language']);
										}

										// \Log::debug([config('app.locale'), $booking->locale, $gConfig['language'], $booking->booking->site_id]);

										if ($finishType == 'insert') {
												event(new \App\Events\BookingCreated($booking));
										}

										if (!empty($notifications) ) {
												event(new \App\Events\BookingStatusChanged($booking, $notifications, ($finishType == 'update' ? true : false)));
										}
								}
						}

						$data['finishType'] = $finishType;
						$data['success'] = true;

						if (session('admin_booking_return_url')) {
							$data['url_index'] = session('admin_booking_return_url');
						} else {
							$data['url_index'] = route('admin.bookings.index');
						}
					} else {
						$data['message'][] = $gLanguage['API']['ERROR_BOOKING_NOT_SAVED'];
					}
				} else {
					$data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING_DATA'];
				}

		break;
		case 'destroy':

					if (!auth()->user()->hasPermission('admin.bookings.trash')) {
							return redirect_no_permission();
					}

					$id = (int)$etoPost['id'];
					$bookingRoute = \App\Models\BookingRoute::with(['booking', 'bookingTransactions']);

					// Check fleet
					if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
							$bookingRoute->where('fleet_id', auth()->user()->id);
					}

					$bookingRoute = $bookingRoute->find($id);

					if (!empty($bookingRoute->id)) {
							// Remove child bookings
							$childBookings = $bookingRoute->childBookings()->with(['booking', 'bookingTransactions'])->get();

							foreach ($childBookings as $child) {
									if ($child->bookingTransactions->count() <= 1) {
											foreach($child->bookingTransactions as $transaction) {
													$transaction->delete();
											}
											if (!empty($child->booking->id)) {
													$child->booking->delete();
											}
									}
									$child->delete();
							}

							// Remove booking if there is no routes
							if ($bookingRoute->bookingAllRoutes->count() <= 1) {
									foreach($bookingRoute->bookingTransactions as $transaction) {
											$transaction->delete();
									}
									if (!empty($bookingRoute->booking->id)) {
											$bookingRoute->booking->delete();
									}
							}

							$bookingRoute->delete();

							activity()
								->performedOn($bookingRoute)
								->useLog('move_to_trash')
								->log('#:subject.ref_number');

							$data['success'] = true;
					}
					else {
							$data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING'];
					}

		break;
		case 'removeFromTrash':

					if (!auth()->user()->hasPermission('admin.bookings.destroy')) {
							return redirect_no_permission();
					}

					$id = (int) $etoPost['id'];
					$bookingRoute = \App\Models\BookingRoute::with([
							'booking' => function($query) {
					      	$query->withTrashed();
					  	},
							'bookingAllRoutes' => function($query) {
					      	$query->withTrashed();
					  	},
							'bookingTransactions' => function($query) {
					      	$query->withTrashed();
					  	},
							'bookingParams'
					])->withTrashed();

					// Check fleet
					if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
							$bookingRoute->where('fleet_id', auth()->user()->id);
					}

					$bookingRoute = $bookingRoute->find($id);

					if (!empty($bookingRoute)) {
							// Remove child bookings
							$childBookings = $bookingRoute->childBookings()->with([
									'booking' => function($query) {
							      	$query->withTrashed();
							  	},
									'bookingTransactions' => function($query) {
							      	$query->withTrashed();
							  	},
									'bookingParams'
							])->withTrashed()->get();

							foreach ($childBookings as $child) {
									if ($child->bookingTransactions->count() <= 1) {
											foreach($child->bookingTransactions as $transaction) {
													$transaction->forceDelete();
											}
											if (!empty($child->booking->id)) {
													$child->booking->forceDelete();
											}
									}

									if ($child->bookingParams->count() <= 1) {
											foreach($child->bookingParams as $param) {
													$param->forceDelete();
											}
									}

									$child->deleteFiles();
									$child->forceDelete();
							}

							// Remove assigned drivers
							\App\Models\BookingDriver::where('booking_id', $resultsBooking->id)->forceDelete();
							// \App\Models\BookingDriver::where('booking_id', $resultsBooking->id)->delete();

							// Remove booking if there is no routes
							if ($bookingRoute->bookingAllRoutes->count() <= 1) {
									foreach($bookingRoute->bookingTransactions as $transaction) {
											$transaction->forceDelete();
									}
									if (!empty($bookingRoute->booking->id)) {
											$bookingRoute->booking->forceDelete();
									}
							}

							if ($bookingRoute->bookingParams->count() <= 1) {
									foreach($bookingRoute->bookingParams as $param) {
											$param->forceDelete();
									}
							}

							$bookingRoute->deleteFiles();
							$bookingRoute->forceDelete();

							// Remove archives
							\Storage::disk('archive')->deleteDirectory('bookings/'.$id);

							activity()
								->performedOn($bookingRoute)
								->useLog('delete')
								->log('#:subject.ref_number');

							$data['success'] = true;
					}
					else {
							$data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING'];
					}

		break;
		case 'restoreFromTrash':

					if (!auth()->user()->hasPermission('admin.bookings.restore')) {
							return redirect_no_permission();
					}

					$id = (int) $etoPost['id'];

					$bookingRoute = \App\Models\BookingRoute::with([
							'booking' => function($query) {
					      	$query->withTrashed();
					  	},
							'bookingAllRoutes' => function($query) {
					      	$query->withTrashed();
					  	},
							'bookingTransactions' => function($query) {
					      	$query->withTrashed();
					  	}
					])->withTrashed();

					// Check fleet
					if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
							$bookingRoute->where('fleet_id', auth()->user()->id);
					}

					$bookingRoute = $bookingRoute->find($id);

					if (!empty($bookingRoute)) {
							// Remove child bookings
							$childBookings = $bookingRoute->childBookings()->with([
									'booking' => function($query) {
							      	$query->withTrashed();
							  	},
									'bookingTransactions' => function($query) {
							      	$query->withTrashed();
							  	}
							])->withTrashed()->get();

							foreach ($childBookings as $child) {
									if ($child->bookingTransactions->count() <= 1) {
											foreach($child->bookingTransactions as $transaction) {
													$transaction->restore();
											}
											$child->booking->restore();
									}
									$child->restore();
							}

							// Remove booking if there is no routes
							if ($bookingRoute->bookingAllRoutes->count() <= 1) {
									foreach($bookingRoute->bookingTransactions as $transaction) {
											$transaction->restore();
									}
									$bookingRoute->booking->restore();
							}

							$bookingRoute->restore();

							activity()
								->performedOn($bookingRoute)
								->useLog('restored')
								->log('#:subject.ref_number');

							$data['success'] = true;
					}
					else {
							$data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING'];
					}

		break;
		case 'quote':

				global $quoteType;
				$quoteType = 'backend';
				include __DIR__ .'/FrontendQuote.php';

				if (!empty(request()->get('bookingId')) && (int)request()->get('bookingId') !== 0) {
						$booking = \App\Models\BookingRoute::find(request()->get('bookingId'));
						if ($booking) {
								activity()
									->performedOn($booking)
									->useLog('quote')
									->log('#:subject.ref_number');
						}
				}

		break;
}
