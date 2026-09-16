<?php
use Carbon\Carbon;

$booking = (array)json_decode($etoPost['booking'], true);
// dd($_POST);
// dd($booking);

if ( !empty($booking) ) {

    // Payment
    $sql = "SELECT *
            FROM `{$dbPrefix}payment`
            WHERE `site_id`='".$siteId."'
            AND `published`='1'
            AND `is_backend`='0'
            AND `id`='".(int)$booking['payment']."'
            LIMIT 1";

    $qPayment = $db->select($sql);
    if ( !empty($qPayment[0]) ) {
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
        $data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENT'];
    }

    // Vehicles
    $sql = "SELECT *
            FROM `{$dbPrefix}vehicle`
            WHERE `site_id`='".$siteId."'
            AND `published`='1'
            AND `is_backend`='0'
            ORDER BY `ordering` ASC, `name` ASC";

    $qVehicle = $db->select($sql);

    if ( !empty($qVehicle) ) {
        $vehicles = $qVehicle;
    }
    else {
        $vehicles = array();
        $data['message'][] = $gLanguage['API']['ERROR_NO_VEHICLE'];
    }

    // General
    $currentDate = date('Y-m-d H:i:s');
    $routeReturn = (int)$booking['routeReturn'];

    // Discount
    $discount_id = (int)$booking['discountId'];
    $discount_code = (string)$booking['discountCode'];

    if ( $discount_id > 0 ) {
        $sql = "SELECT *
                FROM `{$dbPrefix}discount`
                WHERE `published`='1'
                AND BINARY `id`='". $discount_id . "'
                LIMIT 1";

        $qDiscount = $db->select($sql);
        if (!empty($qDiscount[0])) {
            $qDiscount = $qDiscount[0];
        }

        if ( !empty($qDiscount) ) {
            $rowD = new \stdClass();
            $rowD->id = $qDiscount->id;
            $rowD->used_times = $qDiscount->used_times + 1;
            \DB::table('discount')->where('id', $rowD->id)->update((array)$rowD);
        }
    }

    // Default values
    $bookingParams = new \stdClass();
    $isRequested = 0;

    // Unconfirmed status
    if ( !empty($gConfig['booking_request_enable']) ) {
        if ( !empty($gConfig['booking_request_time']) ) {
            $checkTime = \App\Helpers\SiteHelper::timeToSeconds($gConfig['booking_request_time'] .':00');
            if ( $checkTime > 0 ) {
                $isRequested = 1;
            }
        }

        if ( !empty($gConfig['booking_auto_confirm_time']) ) {
            $checkTime = \App\Helpers\SiteHelper::timeToSeconds($gConfig['booking_auto_confirm_time'] .':00');
            $bDate = (string)$booking['route1']['date'];
            $bDate = !empty($bDate) ? Carbon::parse($bDate) : Carbon::now();
            $aDate = Carbon::now()->addSeconds($checkTime)->addHours((int)$gConfig['min_booking_time_limit']);

            if ( $checkTime > 0 && $bDate->gt($aDate) ) {
                $isRequested = 0;
            }
            // dump($checkTime, $bDate, $aDate, $isRequested);
        }
    }
    // $data['!DEBUG_STATUS'] = [$bDate, Carbon::now(), $aDate, $bookingStatus, $isRequested]; dd($data);

    // Default status
    if ( !empty($payment->method) && in_array($payment->method, ['cash', 'account', 'bacs', 'none']) ) {
        $bookingStatus = 'pending';
        if ($isRequested) {
            $bookingStatus = 'requested';
        }
    }
    else {
        if ($payment->id && (float)$booking['totalPriceWithDiscount'] == 0) {
            $bookingStatus = 'pending';
            if ($isRequested) {
                $bookingStatus = 'requested';
            }
        }
        else {
            $bookingStatus = 'incomplete';
            if ($isRequested) {
                $bookingParams->new_status = 'requested';
            }
        }
    }

    // Lead passenger
    if ( (int)$booking['leadPassenger'] <= 0 ) {
        $lead_passenger_title = (string)$booking['leadPassengerTitle'];
        $lead_passenger_name = (string)$booking['leadPassengerName'];
        $lead_passenger_email = (string)$booking['leadPassengerEmail'];
        $lead_passenger_mobile = (string)$booking['leadPassengerMobile'];
    }
    else {
        $lead_passenger_title = '';
        $lead_passenger_name = '';
        $lead_passenger_email = '';
        $lead_passenger_mobile = '';
    }

    $refNumber = 'REF-'. date('Ymd-His') . rand(1000, 100000);

    $rowB = new \stdClass();
    $rowB->id = null;
    $rowB->site_id = (int)$siteId;
    $rowB->user_id = (int)$userId;
    $rowB->unique_key = md5('booking_'. date('Y-m-d H:i:s') . rand(1000, 100000));
    $rowB->ref_number = $refNumber;

    // Driver
    $vehicleDriverId = 0;
    $vehicleList = '';
    if ( !empty($vehicles) ) {
        foreach($booking['route1']['vehicle'] as $key1 => $value1) {
            foreach($vehicles as $key2 => $value2) {
                if ( $value1['id'] == $value2->id ) {
                    $vehicleDriverId = $value2->user_id;
                    if ( !empty($vehicleList) ) {
                        $vehicleList .= "\n";
                    }
										$vehicleList .= ((int)$value1['amount'] > 1 ? $value1['amount']." x " : "") . $value2->name;
                    break;
                }
            }
        }
    }

    if ( !empty($vehicleDriverId) ) {
        $driver = \App\Models\User::find($vehicleDriverId);
        $driver_id = $driver->id;
        // $bookingStatus = 'assigned'; // It causes problem with booking confirmation email not being sent out
    }
    else {
        $driver_id = 0;
    }

    // Summary types (route 1,2)
    $itemsTypes = [
        'journey',
        'parking',
        'stopover',
        'meet_and_greet',
        'child_seat',
        'baby_seat',
        'infant_seat',
        'wheelchair',
        'waiting_time',
        'luggage',
        'hand_luggage',
        'other',
    ];

    // Items
    $items = [];
    if ( !empty($booking['route1']['extraChargesList']) ) {
        foreach($booking['route1']['extraChargesList'] as $key => $value) {
            if ($value['type'] != 'info') {
                if (in_array($value['type'], $itemsTypes)) {
                    if ($value['type'] != 'parking' && $value['type'] != 'other') {
                        $value['name'] = '';
                    }
                }
                else {
                    $value['type'] = 'other';
                }
                $rowItem = new \stdClass();
                $rowItem->type = $value['type'];
                $rowItem->name = $value['name'];
                $rowItem->value = $value['base'] ? $value['base'] : 0;
                $rowItem->amount = $value['amount'] ? $value['amount'] : 1;
                $items[] = $rowItem;
            }
        }
    }

    // Route 1
    $rowR1 = new \stdClass();
    $rowR1->id = null;
    $rowR1->service_id = (int)$booking['serviceId'];
    $rowR1->service_duration = (int)$booking['serviceDuration'];
    $rowR1->scheduled_route_id = (int)$booking['scheduledRouteId'];
    $rowR1->booking_id = 0;
    $rowR1->driver_id = $driver_id;
    $rowR1->status = $bookingStatus;
    $rowR1->ref_number = $rowB->ref_number .'a';
    $rowR1->route = 1;
    $rowR1->category_start = (string)$booking['route1']['category']['start'];
    $rowR1->category_type_start = (string)$booking['route1']['category']['type']['start'];
    $rowR1->location_start = (string)$booking['route1']['location']['start'];
    $rowR1->address_start = (string)$booking['route1']['address']['start'];
    $rowR1->address_start_complete = (string)$booking['route1']['addressComplete']['start'];
    $rowR1->coordinate_start_lat = (float)$booking['route1']['coordinate']['start']['lat'];
    $rowR1->coordinate_start_lon = (float)$booking['route1']['coordinate']['start']['lon'];
    $rowR1->waypoints = json_encode((array)$booking['route1']['waypoints']);
    $rowR1->waypoints_complete = json_encode((array)$booking['route1']['waypointsComplete']);
    $rowR1->category_end = (string)$booking['route1']['category']['end'];
    $rowR1->category_type_end = (string)$booking['route1']['category']['type']['end'];
    $rowR1->location_end = (string)$booking['route1']['location']['end'];
    $rowR1->address_end = (string)$booking['route1']['address']['end'];
    $rowR1->address_end_complete = (string)$booking['route1']['addressComplete']['end'];
    $rowR1->coordinate_end_lat = (float)$booking['route1']['coordinate']['end']['lat'];
    $rowR1->coordinate_end_lon = (float)$booking['route1']['coordinate']['end']['lon'];
    $rowR1->distance = (float)$booking['route1']['distance'];
    $rowR1->duration = (int)$booking['route1']['duration'];
    $rowR1->distance_base_start = (float)$booking['route1']['distance_base_start'];
    $rowR1->duration_base_start = (int)$booking['route1']['duration_base_start'];
    $rowR1->distance_base_end = (float)$booking['route1']['distance_base_end'];
    $rowR1->duration_base_end = (int)$booking['route1']['duration_base_end'];
    $rowR1->date = (string)$booking['route1']['date'];
    $rowR1->flight_number = (string)$booking['route1']['flightNumber'];
    $rowR1->flight_landing_time = (string)$booking['route1']['flightLandingTime'];
    $rowR1->departure_city = (string)$booking['route1']['departureCity'];
    $rowR1->departure_flight_number = (string) $booking['route1']['departureFlightNumber'];
    $rowR1->departure_flight_time = (string) $booking['route1']['departureFlightTime'];
    $rowR1->departure_flight_city = (string) $booking['route1']['departureFlightCity'];
    $rowR1->waiting_time = (int)$booking['route1']['waitingTime'];
    $rowR1->meet_and_greet = (int)$booking['route1']['meetAndGreet'];
    $rowR1->meeting_point = (string)$booking['route1']['meetingPoint'];
    $rowR1->requirements = (string)$booking['route1']['requirements'];
    $rowR1->items = json_encode($items);
    $rowR1->vehicle = json_encode((array)$booking['route1']['vehicle']);
    $rowR1->vehicle_list = $vehicleList;
    $rowR1->passengers = (int)$booking['route1']['passengers'];
    $rowR1->luggage = (int)$booking['route1']['luggage'];
    $rowR1->hand_luggage = (int)$booking['route1']['handLuggage'];
    $rowR1->child_seats = (int)$booking['route1']['childSeats'];
    $rowR1->baby_seats = (int)$booking['route1']['babySeats'];
    $rowR1->infant_seats = (int)$booking['route1']['infantSeats'];
    $rowR1->wheelchair = (int)$booking['route1']['wheelchair'];
    $rowR1->extra_charges_list = '';
    $rowR1->extra_charges_price = 0;
    $rowR1->total_price = (float)$booking['route1']['totalPrice'];
    $rowR1->discount = (float)$booking['route1']['totalDiscount'];
    $rowR1->discount_code = $discount_code;
    $rowR1->contact_title = ''; //(string)$booking['contactTitle'];
    $rowR1->department = !empty($booking['contactDepartment']) ? $booking['contactDepartment'] : null;
    $rowR1->contact_name = (string)$booking['contactName'];
    $rowR1->contact_email = (string)$booking['contactEmail'];
    $rowR1->contact_mobile = (string)$booking['contactMobile'];
    $rowR1->lead_passenger_title = ''; //$lead_passenger_title;
    $rowR1->lead_passenger_name = $lead_passenger_name;
    $rowR1->lead_passenger_email = $lead_passenger_email;
    $rowR1->lead_passenger_mobile = $lead_passenger_mobile;
    $rowR1->source_details = '';
    $rowR1->source = $siteName;
    $rowR1->ip = (string)$_SERVER['REMOTE_ADDR'];
    $rowR1->locale = !empty(session('locale')) ? session('locale') : app()->getLocale();
    $rowR1->modified_date = null;
    $rowR1->params = !empty($bookingParams) && count((array)$bookingParams) ? \GuzzleHttp\json_encode($bookingParams) : null;
    $rowR1->created_date = $currentDate;

    if ( $routeReturn == 2 ) {

        // Driver
        $vehicleDriverId = 0;
        $vehicleList = '';
        if ( !empty($vehicles) ) {
            foreach($booking['route2']['vehicle'] as $key1 => $value1) {
                foreach($vehicles as $key2 => $value2) {
                    $vehicleDriverId = $value2->user_id;
                    if ( $value1['id'] == $value2->id ) {
                        if ( !empty($vehicleList) ) {
                            $vehicleList .= "\n";
                        }
    										$vehicleList .= ((int)$value1['amount'] > 1 ? $value1['amount']." x " : "") . $value2->name;
                        break;
                    }
                }
            }
        }

        if ( !empty($vehicleDriverId) ) {
            $driver = \App\Models\User::find($vehicleDriverId);
            $driver_id = $driver->id;
            // $bookingStatus = 'assigned'; // It causes problem with booking confirmation email not being sent out
        }
        else {
            $driver_id = 0;
        }

        // Items
        $items = [];
        if ( !empty($booking['route2']['extraChargesList']) ) {
            foreach($booking['route2']['extraChargesList'] as $key => $value) {
                if ($value['type'] != 'info') {
                    if (in_array($value['type'], $itemsTypes)) {
                        if ($value['type'] != 'other') {
                            $value['name'] = '';
                        }
                    }
                    else {
                        $value['type'] = 'other';
                    }
                    $rowItem = new \stdClass();
                    $rowItem->type = $value['type'];
                    $rowItem->name = $value['name'];
                    $rowItem->value = $value['base'] ? $value['base'] : 0;
                    $rowItem->amount = $value['amount'] ? $value['amount'] : 1;
                    $items[] = $rowItem;
                }
            }
        }


        // Route 2
        $rowR2 = new \stdClass();
        $rowR2->id = null;
        $rowR2->service_id = (int)$booking['serviceId'];
        $rowR2->service_duration = (int)$booking['serviceDuration'];
        $rowR2->scheduled_route_id = (int)$booking['scheduledRouteId'];
        $rowR2->booking_id = 0;
        $rowR2->driver_id = $driver_id;
        $rowR2->status = $bookingStatus;
        $rowR2->ref_number = $rowB->ref_number .'b';
        $rowR2->route = 2;
        $rowR2->category_start = (string)$booking['route2']['category']['start'];
        $rowR2->category_type_start = (string)$booking['route2']['category']['type']['start'];
        $rowR2->location_start = (string)$booking['route2']['location']['start'];
        $rowR2->address_start = (string)$booking['route2']['address']['start'];
        $rowR2->address_start_complete = (string)$booking['route2']['addressComplete']['start'];
        $rowR2->coordinate_start_lat = (float)$booking['route2']['coordinate']['start']['lat'];
        $rowR2->coordinate_start_lon = (float)$booking['route2']['coordinate']['start']['lon'];
        $rowR2->waypoints = json_encode((array)$booking['route2']['waypoints']);
        $rowR2->waypoints_complete = json_encode((array)$booking['route2']['waypointsComplete']);
        $rowR2->category_end = (string)$booking['route2']['category']['end'];
        $rowR2->category_type_end = (string)$booking['route2']['category']['type']['end'];
        $rowR2->location_end = (string)$booking['route2']['location']['end'];
        $rowR2->address_end = (string)$booking['route2']['address']['end'];
        $rowR2->address_end_complete = (string)$booking['route2']['addressComplete']['end'];
        $rowR2->coordinate_end_lat = (float)$booking['route2']['coordinate']['end']['lat'];
        $rowR2->coordinate_end_lon = (float)$booking['route2']['coordinate']['end']['lon'];
        $rowR2->distance = (float)$booking['route2']['distance'];
        $rowR2->duration = (int)$booking['route2']['duration'];
        $rowR2->distance_base_start = (float)$booking['route2']['distance_base_start'];
        $rowR2->duration_base_start = (int)$booking['route2']['duration_base_start'];
        $rowR2->distance_base_end = (float)$booking['route2']['distance_base_end'];
        $rowR2->duration_base_end = (int)$booking['route2']['duration_base_end'];
        $rowR2->date = (string)$booking['route2']['date'];
        $rowR2->flight_number = (string)$booking['route2']['flightNumber'];
        $rowR2->flight_landing_time = (string)$booking['route2']['flightLandingTime'];
        $rowR2->departure_city = (string)$booking['route2']['departureCity'];
        $rowR2->departure_flight_number = (string) $booking['route2']['departureFlightNumber'];
        $rowR2->departure_flight_time = (string) $booking['route2']['departureFlightTime'];
        $rowR2->departure_flight_city = (string) $booking['route2']['departureFlightCity'];
        $rowR2->waiting_time = (int)$booking['route2']['waitingTime'];
        $rowR2->meet_and_greet = (int)$booking['route2']['meetAndGreet'];
        $rowR2->meeting_point = (string)$booking['route2']['meetingPoint'];
        $rowR2->requirements = (string)$booking['route2']['requirements'];
        $rowR2->items = json_encode($items);
        $rowR2->vehicle = json_encode((array)$booking['route2']['vehicle']);
        $rowR2->vehicle_list = $vehicleList;
        $rowR2->passengers = (int)$booking['route2']['passengers'];
        $rowR2->luggage = (int)$booking['route2']['luggage'];
        $rowR2->hand_luggage = (int)$booking['route2']['handLuggage'];
        $rowR2->child_seats = (int)$booking['route2']['childSeats'];
        $rowR2->baby_seats = (int)$booking['route2']['babySeats'];
        $rowR2->infant_seats = (int)$booking['route2']['infantSeats'];
        $rowR2->wheelchair = (int)$booking['route2']['wheelchair'];
        $rowR2->extra_charges_list = '';
        $rowR2->extra_charges_price = 0;
        $rowR2->total_price = (float)$booking['route2']['totalPrice'];
        $rowR2->discount = (float)$booking['route2']['totalDiscount'];
        $rowR2->discount_code = $discount_code;
        $rowR2->contact_title = ''; //(string)$booking['contactTitle'];
        $rowR2->department = !empty($booking['contactDepartment']) ? $booking['contactDepartment'] : null;
        $rowR2->contact_name = (string)$booking['contactName'];
        $rowR2->contact_email = (string)$booking['contactEmail'];
        $rowR2->contact_mobile = (string)$booking['contactMobile'];
        $rowR2->lead_passenger_title = ''; //$lead_passenger_title;
        $rowR2->lead_passenger_name = $lead_passenger_name;
        $rowR2->lead_passenger_email = $lead_passenger_email;
        $rowR2->lead_passenger_mobile = $lead_passenger_mobile;
        $rowR2->source_details = '';
        $rowR2->source = $siteName;
        $rowR2->ip = (string)$_SERVER['REMOTE_ADDR'];
        $rowR2->locale = !empty(session('locale')) ? session('locale') : app()->getLocale();
        $rowR2->modified_date = null;
        $rowR2->params = !empty($bookingParams) && count((array)$bookingParams) ? \GuzzleHttp\json_encode($bookingParams) : null;
        $rowR2->created_date = $currentDate;
    }

    // Finish type
    if ( !empty($etoPost['manualQuote']) ) {
        $finishType = 'manualThankYou';

        $rowR1->total_price = 0;
        $rowR1->status = 'quote';

        if ( $routeReturn == 2 ) {
            $rowR2->total_price = 0;
            $rowR2->status = 'quote';
        }
    }
    elseif ( !in_array($payment->method, array('cash', 'account', 'bacs', 'none')) ) {
        $finishType = 'payment';
    }
    else {
        $finishType = 'thankYou';
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

            $bData = array_merge((array) clone $rowB, [
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
                $ref_number = $refGenerator->generateRefNumber([
                    'id' => $newBookingRoute->id,
                    'pickupDateTime' => $newBookingRoute->date
                ]);

                $newBooking->ref_number = $ref_number;
                $newBooking->save();

                $newBookingRoute->ref_number = $ref_number;
                $newBookingRoute->save();

                $parentID = $newBookingRoute->id;
            }
        }

        $rowR1->parent_booking_id = $parentID;
    }
    // Master booking - end


    // Create booking
    $rowB->id = \DB::table('booking')->insertGetId((array)$rowB);

    if ( !empty($rowB->id) ) {
        $refGenerator = new \App\Models\BookingRoute;
        $refNumberBase = $refGenerator->generateRefNumber([
            'exclude' => ['id', 'create', 'pickup']
        ]);

        $refNumber = $refGenerator->generateRefNumber([
            'ref_number' => $refNumberBase,
            'id' => $rowB->id,
            'pickupDateTime' => $rowR1->date
        ]);

        $rowUpdate = new \stdClass();
        $rowUpdate->id = $rowB->id;
        $rowUpdate->ref_number = $refNumber;
        \DB::table('booking')->where('id', $rowUpdate->id)->update((array)$rowUpdate);

        if ( !empty($rowUpdate->id) ) {
            if ( $routeReturn == 2 ) {
                $rowR1->ref_number = $rowUpdate->ref_number .'a';
                $rowR2->ref_number = $rowUpdate->ref_number .'b';
            }
            else {
                $rowR1->ref_number = $rowUpdate->ref_number;
            }
        }

        // Insert booking route
        $rowR1->booking_id = $rowB->id;
        $rowR1->id = \DB::table('booking_route')->insertGetId((array)$rowR1);
        $r1Booking = \App\Models\BookingRoute::find($rowR1->id);

        // Flight details
        if (config('eto.allow_flightstats') && config('services.flightstats.enabled')) {
            \App\Http\Controllers\FlightController::updateFlightDetails($r1Booking->id, 'pickup');
            \App\Http\Controllers\FlightController::updateFlightDetails($r1Booking->id, 'dropoff');
        }

        if ( $routeReturn == 2 ) {
            $rowR2->booking_id = $rowB->id;
            $rowR2->id = \DB::table('booking_route')->insertGetId((array)$rowR2);
            $r2Booking = \App\Models\BookingRoute::find($rowR2->id);

            // Flight details
            if (config('eto.allow_flightstats') && config('services.flightstats.enabled')) {
                \App\Http\Controllers\FlightController::updateFlightDetails($r2Booking->id, 'pickup');
                \App\Http\Controllers\FlightController::updateFlightDetails($r2Booking->id, 'dropoff');
            }
        }

        // Send notification
        if (!empty($rowR1->id)) {
            event(new \App\Events\BookingCreated($r1Booking));
            if (!empty($rowR1->driver_id)) {
                $r1BookingCopy = clone $r1Booking;
                $r1BookingCopy->status = 'assigned';
                event(new \App\Events\BookingStatusChanged($r1BookingCopy));
            }
        }

        if ($routeReturn == 2) {
            if (!empty($rowR2->id)) {
                event(new \App\Events\BookingCreated($r2Booking));
                if (!empty($rowR2->driver_id)) {
                    $r2BookingCopy = clone $r2Booking;
                    $r2BookingCopy->status = 'assigned';
                    event(new \App\Events\BookingStatusChanged($r2BookingCopy));
                }
            }
        }

        /*
        $total = (float)$booking['totalPriceWithDiscount'];
        $deposit = (float)$booking['totalDeposit'];
        $bookingList = [];
        $tIDs = [];

        if ( $rowR1->id ) {
          $bookingList[] = (object)[
            'id' => $rowR1->id,
            'total' => (float)$booking['route1']['totalPriceWithDiscount'],
            'deposit' => ($deposit / $total) * (float)$booking['route1']['totalPriceWithDiscount'],
          ];
        }

        if ( $rowR2->id ) {
          $bookingList[] = (object)[
            'id' => $rowR2->id,
            'total' => (float)$booking['route2']['totalPriceWithDiscount'],
            'deposit' => ($deposit / $total) * (float)$booking['route2']['totalPriceWithDiscount'],
          ];
        }

        foreach($bookingList as $bKey => $bValue) {
          $total = $bValue->total;
          $deposit = $bValue->deposit;

          if ( $payment->id && $total > 0 ) {
                    $balance = $total - $deposit;
                    $total_charge = 0;
                    $deposit_charge = 0;
                    $balance_charge = 0;

                    if ( $payment->price ) {
                        if ( $payment->factor_type == 1 ) {
                            $total_charge = ($total / 100) * $payment->price;
                            $deposit_charge = ($deposit / 100) * $payment->price;
                            $balance_charge = ($balance / 100) * $payment->price;
                        }
                        else {
                            $total_charge = $payment->price;
                            $deposit_charge = $payment->price;
                            $balance_charge = $payment->price;
                        }
                    }

                    if ( $deposit > 0 && $deposit < $total ) {
                        // Deposit
                        $transaction = new \App\Models\Transaction;
                        $transaction->relation_type = 'booking_route';
                        $transaction->relation_id = $bValue->id;
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

              $tIDs[] = $transaction->id;

                        // Balance
                        $transaction = new \App\Models\Transaction;
                        $transaction->relation_type = 'booking_route';
                        $transaction->relation_id = $bValue->id;
                        $transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
                        $transaction->name = trans('booking.transaction.balance');
                        $transaction->description = '';
                        $transaction->payment_id = $payment->id;
                        $transaction->payment_method = $payment->method;
                        $transaction->payment_name = $payment->name;
                        $transaction->payment_charge = $balance_charge;

                        $cashPayment = \App\Models\Payment::where('site_id', '=', $siteId)->where('method', '=', 'cash')->first();
                        if ( $gConfig['booking_deposit_balance'] == 'cash' && !empty($cashPayment) ) {
                $balance_charge = 0;

                            if ( $cashPayment->price ) {
                                if ( $cashPayment->factor_type == 1 ) {
                                    $balance_charge = ($balance / 100) * $cashPayment->price;
                                }
                                else {
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
                    }
                    else {
                        // Full amount
                        $transaction = new \App\Models\Transaction;
                        $transaction->relation_type = 'booking_route';
                        $transaction->relation_id = $bValue->id;
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

              $tIDs[] = $transaction->id;
                    }
                }
        }

        $bID = $rowB->unique_key;
              $tID = '';

        if ( $tIDs ) {
          $tID = urlencode(base64_encode(implode('|', $tIDs)));
        }
        else {
            if ( !$finishType ) {
                $finishType = 'thankYou';
            }
        }
        */

        ////


        $bID = $rowB->unique_key;
        $tID = '';
        $total = (float)$booking['totalPriceWithDiscount'];
        $deposit = (float)$booking['totalDeposit'];

        // Transactions
        if ( $payment->id && $total >= 0 ) {
            $payment_status = $total == 0 ? 'paid' : 'pending';
            $balance = $total - $deposit;
            $total_charge = 0;
            $deposit_charge = 0;
            $balance_charge = 0;

            if ( $payment->price ) {
                if ( $payment->factor_type == 1 ) {
                    $total_charge = ($total / 100) * $payment->price;
                    $deposit_charge = ($deposit / 100) * $payment->price;
                    $balance_charge = ($balance / 100) * $payment->price;
                }
                else {
                    $total_charge = $payment->price;
                    $deposit_charge = $payment->price;
                    $balance_charge = $payment->price;
                }
            }

            if ( $deposit > 0 && $deposit < $total ) {
                // Deposit
                $transaction = new \App\Models\Transaction;
                $transaction->relation_type = 'booking';
                $transaction->relation_id = $rowB->id;
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
                $transaction->status = $payment_status;
                $transaction->save();

                $tID = $transaction->unique_key;

                // Balance
                $transaction = new \App\Models\Transaction;
                $transaction->relation_type = 'booking';
                $transaction->relation_id = $rowB->id;
                $transaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
                $transaction->name = trans('booking.transaction.balance');
                $transaction->description = '';
                $transaction->payment_id = $payment->id;
                $transaction->payment_method = $payment->method;
                $transaction->payment_name = $payment->name;
                $transaction->payment_charge = $balance_charge;

                $cashPayment = \App\Models\Payment::where('site_id', '=', $siteId)->where('method', '=', 'cash')->first();
                if ( $gConfig['booking_deposit_balance'] == 'cash' && !empty($cashPayment) ) {
                    $balance_charge = 0;

                    if ( $cashPayment->price ) {
                        if ( $cashPayment->factor_type == 1 ) {
                            $balance_charge = ($balance / 100) * $cashPayment->price;
                        }
                        else {
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
            }
            else {
                // Full amount
                $transaction = new \App\Models\Transaction;
                $transaction->relation_type = 'booking';
                $transaction->relation_id = $rowB->id;
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
                $transaction->status = $payment_status;
                $transaction->save();

                $tID = $transaction->unique_key;
            }
        }
        else {
            if ( !$finishType ) {
                $finishType = 'thankYou';
            }
        }


        session(['notification_sent' => 0]);

        $data['finishType'] = $finishType;
        $data['bID'] = $bID;
        $data['tID'] = $tID;

        // $data['totalPriceWithDiscount'] = $booking['totalPriceWithDiscount'];
        // $data['totalDeposit'] = $booking['totalDeposit'];
        // dd($data);
    }
    else {
        $data['message'][] = $gLanguage['API']['ERROR_BOOKING_NOT_SAVED'];
    }
}
else {
    $data['message'][] = $gLanguage['API']['ERROR_NO_BOOKING_DATA'];
}
