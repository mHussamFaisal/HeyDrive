<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Discount;
use App\Helpers\SiteHelper;
use App\Helpers\SettingsHelper;

//use App\Events\BookingStatusChanged;

class BookingController2 extends Controller
{
    private $objectForm = [
        'service' => [
            [
                'service' => 0,
            ]
        ],
        'location' => [
            [
                'address' => '',
                'complete' => '',
            ]
        ],
        'when' => [
            [
                'date' => '',
                'formatted_date' => '',
            ]
        ],
        'serviceDuration' => [
            [
                'service_duration' => 1,
            ]
        ],
        'vehicle' => [
            [
                'vehicle_type' => 0,
                'vehicle_type_text_selected' => '',
                'vehicle_amount' => 1,
            ]
        ],
        'flightDetails' => [
            [
                'flight_number' => '',
                'flight_landing_time' => '',
                'arriving_from' => '',
                'departure_flight_number' => '',
                'departure_flight_time' => '',
                'departure_flight_city' => '',
                'waiting_time' => '',
                'meet_and_greet' => '',
                'meeting_point' => '',
            ]
        ],
        'driver' => [
            [
                'driver' => '',
                'vehicle' => null,
                'commission' => '',
                'cash' => '',
                'comments' => '',
                'driver_text_selected' => '',
                'vehicle_text_selected' => '',
                'userAvatar' => '',
                'userName' => '',
                'uniqueId' => '',
                'userEmail' => '',
                'userPhone' => '',
                'formated_commission' => '',
                'formated_cash' => '',
            ]
        ],
        'fleet' => [
            [
                'fleet' => '',
                'commission' => '',
                'fleet_text_selected' => '',
                'userAvatar' => '',
                'userName' => '',
                'uniqueId' => '',
                'userEmail' => '',
                'userPhone' => '',
                'formated_commission' => '',
            ]
        ],
        'customer' => [
            [
                'customer' => '',
                'customer_text_selected' => '',
                'department' => '',
                'userAvatar' => '',
                'userName' => '',
                'userEmail' => '',
                'userPhone' => '',
            ]
        ],
        'passenger' => [
            [
                'name' => '',
                'email' => '',
                'phone' => '',
                'comments' => '',
            ]
        ],
        'paymentType' => [
            [
                'payment_method' => 0,
                'payment_type_text' => '',
            ]
        ],
        'passengerAmount' => [
            [
                'amount' => 0,
            ]
        ],
        'luggageAmount' => [
            [
                'amount' => 0,
            ]
        ],
        'handLuggageAmount' => [
            [
                'amount' => 0,
            ]
        ],
        'journeyPrice' => [
            [
                'price' => '0.00',
                'override_name' => '',
                'formated_price' => '',
            ]
        ],
        'item' => [],
        'discount' => [
            [
                'price' => '0.00',
                'formated_price' => '',
            ]
        ],
        'deposit' => [
            [
                'price' => '0.00',
                'formated_price' => '',
            ]
        ],
        'discountCode' => [
            [
                'code' => '',
            ]
        ],
        'bookingStatus' => [
            [
                'status' => 'pending',
                'status_text_selected' => 'Confirmed',
                'comments' => '',
            ]
        ],
        'source' => [
            [
                'source' => 'Admin',
                'comments' => '',
            ]
        ],
        'requirement' => [
            [
                'value' => '',
            ]
        ],
        'notes' => [
            [
                'notes' => '',
            ]
        ],
        'notifications' => [
            [
                'send_notification' => 1,
                'send_invoice' => 0,
                'locale' => '',
                'locale_text_selected' => '',
                'comments' => '',
                'email' => '',
            ]
        ],
        'customField' => [
            [
                'custom' => '',
            ]
        ],
        'baseStart' => [
            [
                'distance' => 0,
                'duration' => '0.00',
            ]
        ],
        'startEnd' => [
            [
                'distance' => 0,
                'duration' => '0.00',
            ]
        ],
        'endBase' => [
            [
                'distance' => 0,
                'duration' => '0.00',
            ]
        ],
    ];

    public function __construct()
    {
        $requestParams = request('requestParams') ? (array)request('requestParams') : [];

        if (!empty($requestParams)) {
            if (!empty($requestParams['customerId'])) {
                $this->objectForm['customer'][0]['customer'] = urldecode($requestParams['customerId']);
            }
            // if (!empty($requestParams['department'])) {
            //    $this->objectForm['customer'][0]['department'] = urldecode($requestParams['department']);
            // }
            if (!empty($requestParams['customerName'])) {
                $this->objectForm['customer'][0]['userName'] = urldecode($requestParams['customerName']);
                $this->objectForm['passenger'][0]['name'] = urldecode($requestParams['customerName']);
            }
            if (!empty($requestParams['customerEmail'])) {
                $this->objectForm['customer'][0]['userEmail'] = urldecode($requestParams['customerEmail']);
                $this->objectForm['passenger'][0]['email'] = urldecode($requestParams['customerEmail']);
            }
            if (!empty($requestParams['customerPhone'])) {
                $this->objectForm['customer'][0]['userPhone'] = urldecode($requestParams['customerPhone']);
                $this->objectForm['passenger'][0]['phone'] = urldecode($requestParams['customerPhone']);
            }
            if (!empty($requestParams['phoneNumber'])) {
                $this->objectForm['passenger'][0]['phone'] = urldecode($requestParams['phoneNumber']);
            }
        }
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if ($request->system->subscription->license_status == 'suspended') {
            return redirect_no_permission();
        }

        $data = $this->convertToOld($request);

        $requestBooking = $request->booking;
        $request->merge(['apiType' => 'backend']);
        $request->merge(json_decode(json_encode($data), true));
        $response = (new \App\Http\Controllers\ETOv2Controller)->index();
        $response = json_decode(json_encode($response));

        $validResponse = isset($response->finishType)
                && ($response->finishType == 'insert' || $response->finishType == 'update')
            ? ['success'=>$response->success, 'message'=>$response->message]
            : $this->convertToNew($response, $requestBooking);

        if (config('app.debug') == true) {
            $validResponse['!OLD'] = $response;
        }

        return response()->json($validResponse);
    }

    public function show($id)
    {
        //
    }

    public function getBooking($bookingId)
    {
        $totalDetails = false;
        $incomeCharges = false;
        $siteId = 0;
        $booking = \App\Models\BookingRoute::withTrashed()->findOrFail($bookingId);

        if (!empty($booking->booking->site_id)) {
            $totalDetails = $booking->getTotal('data', 'raw');
            $siteId = $booking->booking->site_id;
            $routesCharges = $booking->getCharges();
            foreach($routesCharges->routes as $route) {
                if ((int)$route->id === (int)$booking->id) {
                    $incomeCharges = $route;
                }
            }
        }

        $request = request();
        $request->merge([
            'apiType' => 'backend',
            'task' => 'bookings',
            'action' => 'read',
            'id' => $bookingId,
            'siteId' => $siteId,
        ]);
        $response = (new \App\Http\Controllers\ETOv2Controller)->index();
        $response = json_decode(json_encode($response));

        $validResponse = $response->success == false
            ? ['success'=>$response->success, 'message'=>$response->message]
            : array_merge($this->convertToNew($response, []), ['total_details'=>$totalDetails, 'income_charges'=>$incomeCharges]);

        if (config('app.debug') == true) {
            $validResponse['!OLD'] = $response;
        }

        return $validResponse;
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    private function convertToNew($data, $sent, $siteId = false)
    {
        $siteId = $siteId != false ? $siteId : config('site.site_id');
        $new = [];
        $new['booking'] = $sent;
        $items = SettingsHelper::getItems($siteId);
        $booking = $data->booking;
        $new['success'] = $data->success;
        $new['message'] = $data->message;
        $new['totalPrice'] = !empty($booking->totalPrice) ? $booking->totalPrice : 0;
        $new['totalPriceWithDiscount'] = !empty($booking->totalPriceWithDiscount) ? $booking->totalPriceWithDiscount : 0;
        $new['totalDiscount'] = !empty($booking->totalDiscount) ? $booking->totalDiscount : 0;
        $new['discountId'] = !empty($booking->discountId) ?  $booking->discountId : 0;
        $new['discountCode'] = !empty($booking->discountCode) ? $booking->discountCode : '';
        $new['discountMessage'] = !empty($booking->discountMessage) ? $booking->discountMessage : '';
        $new['discountExcludedInfo'] = !empty($booking->discountExcludedInfo) ? $booking->discountExcludedInfo : false;
        $new['discountAccountMessage'] = !empty($booking->discountAccountMessage) ? $booking->discountAccountMessage : '';
        $new['discountReturnMessage'] = !empty($booking->discountReturnMessage) ? $booking->discountReturnMessage : '';
        $new['discountStatus'] = !empty($booking->discountStatus) ? $booking->discountStatus : '';
        $new['siteId'] = !empty($data->siteId) ? $data->siteId : config('site.site_id');

        if (!empty($data->bookingId)) {
            $new['bookingId'] = $data->bookingId;
            $route = $booking->route1;
            $from = [
                'address' => $route->address->start,
                'complete' => $route->addressComplete->start,
                'lat' => $route->coordinate->start->lat,
                'lng' => $route->coordinate->start->lon,
                'place_id' => $route->coordinate->start->place_id ?: '',
            ];
            $to = [
                'address' => $route->address->end,
                'complete' => $route->addressComplete->end,
                'lat' => $route->coordinate->end->lat,
                'lng' => $route->coordinate->end->lon,
                'place_id' => $route->coordinate->end->place_id ?: '',
            ];
            $locations = [];
            $locations[] = $from;
            $waypoints = (array)$route->waypoints;
            $waypointsComplete = (array)$route->waypointsComplete;

            foreach($waypoints as $kA => $address) {
                $complete = '';
                if (!empty($waypointsComplete)) {
                    foreach($waypointsComplete as $kC => $vC) {
                        if ($kC == $kA) {
                            $complete = $vC;
                            break;
                        }
                    }
                }

                $locations[] = [
                    'address' => !empty($address) ? (string)$address : '',
                    'complete' => !empty($complete) ? (string)$complete : '',
                ];
            }

            $locations[] = $to;
            $new['refNumber'] = !empty($booking->refNumber) ? $booking->refNumber : '';
            $new['booking']['location'] = $locations;
            $new['booking']['service'][0]['service'] = $booking->serviceId;
            $new['booking']['serviceDuration'][0]['service_duration'] = $booking->serviceDuration;

            $new['booking']['when'][0]['date'] = $route->date;
            $new['booking']['when'][0]['formatted_date'] = SiteHelper::formatDateTime($route->date);
            $new['booking']['flightDetails'][0]['flight_number'] = !empty($route->flightNumber) ? $route->flightNumber : '';
            $new['booking']['flightDetails'][0]['flight_landing_time'] = !empty($route->flightLandingTime) ? $route->flightLandingTime : '';
            $new['booking']['flightDetails'][0]['arriving_from'] = !empty($route->departureCity) ? $route->departureCity : '';
            $new['booking']['flightDetails'][0]['departure_flight_number'] = !empty($route->departureFlightNumber) ? $route->departureFlightNumber : '';
            $new['booking']['flightDetails'][0]['departure_flight_time'] = !empty($route->departureFlightTime) ? $route->departureFlightTime : '';
            $new['booking']['flightDetails'][0]['departure_flight_city'] = !empty($route->departureFlightCity) ? $route->departureFlightCity : '';
            $new['booking']['flightDetails'][0]['waiting_time'] = !empty($route->waitingTime) ? $route->waitingTime : '';
            $new['booking']['flightDetails'][0]['meet_and_greet'] = !empty($route->meetAndGreet) ? (int)$route->meetAndGreet : 0;
            $new['booking']['flightDetails'][0]['meeting_point'] = !empty($route->meetingPoint) ? $route->meetingPoint : '';

            foreach($route->vehicle as $vid=>$vehicle) {
                $vehicleData = $this->getVehicle($new['siteId'], $vehicle->id);

                if (empty($new['booking']['vehicle'][$vehicle->id]['vehicle_type'])) {
                    $new['booking']['vehicle'][$vehicle->id]['vehicle_type'] = $vehicle->id;
                    $new['booking']['vehicle'][$vehicle->id]['vehicle_type_text_selected'] = !empty($vehicleData->name) ? $vehicleData->name : 'Unassigned';
                    $new['booking']['vehicle'][$vehicle->id]['vehicle_amount'] = $vehicle->amount;
                }
                else {
                    $new['booking']['vehicle'][$vehicle->id]['vehicle_amount'] += $vehicle->amount;
                }
            }

            $vehicleList = !empty($new['booking']['vehicle']) ? $new['booking']['vehicle'] : [0 => [
                'vehicle_type' => 0,
                'vehicle_type_text_selected' => 'Unassigned',
                'vehicle_amount' => 1
            ]];
            unset($new['booking']['vehicle']);
            foreach($vehicleList as $vehicleItem) {
                $new['booking']['vehicle'][] = $vehicleItem;
            }

            $new['booking']['item'] = [];
            $new['booking']['journeyPrice'][0]['price'] = 0;
            $new['booking']['journeyPrice'][0]['override_name'] = '';
            foreach ($route->items as $item) {
                if ($item->type == 'journey') {
                    $new['booking']['journeyPrice'][0]['price'] = $item->value;
                    $new['booking']['journeyPrice'][0]['override_name'] = $item->name;
                }
                else {
                    $itemId = 0;
                    $item->label = '';
                    foreach ($items['data'] as $fid => $val) {
                        if ($item->type == $val['field_key']) {
                            $itemId = $val['id'];
                            $item->label = $val['label'];
                        }
                    }

                    if ($item->label == '') { $item->label = $item->name; }
                    else if ($item->label == $item->name) { $item->name = ''; }

                    $new['booking']['item'][] = [
                        'item' => $itemId,
                        'prev_item' => $itemId,
                        'price' => $item->value,
                        'amount' => $item->amount,
                        'item_key' => $item->type,
                        'item_trans_key' => $item->type,
                        'item_type' => $item->label,
                        'override_name' => $item->name,
                    ];
                }
            }

            $new['booking']['startEnd'][0]['distance'] = $route->distance;
            $new['booking']['startEnd'][0]['duration'] = $route->duration;
            $new['booking']['baseStart'][0]['distance'] = $route->distance_base_start;
            $new['booking']['baseStart'][0]['duration'] = $route->duration_base_start;
            $new['booking']['endBase'][0]['distance'] = $route->distance_base_end;
            $new['booking']['endBase'][0]['duration'] = $route->duration_base_end;
            $new['booking']['passengerAmount'][0]['amount'] = $route->passengers;
            $new['booking']['luggageAmount'][0]['amount'] = $route->luggage;
            $new['booking']['handLuggageAmount'][0]['amount'] = $route->handLuggage;

            $statuses = (new \App\Models\BookingRoute)->getStatusList();
            foreach($statuses as $id=>$status) {
                if ($status->value == $booking->status) {
                    break;
                }
            }
            $new['booking']['bookingStatus'][0]['status'] = $booking->status;
            $new['booking']['bookingStatus'][0]['status_text_selected'] = !empty($status->text) ? $status->text : ucfirst($booking->status);
            $new['booking']['bookingStatus'][0]['comments'] = $booking->statusNotes;
            $new['booking']['passenger'][0]['name'] = trim($booking->contactTitle .' '. $booking->contactName);
            $new['booking']['passenger'][0]['email'] = $booking->contactEmail;
            $new['booking']['passenger'][0]['phone'] = $booking->contactMobile;
            if (!empty($booking->leadPassengerName)) {
                $new['booking']['passenger'][1]['name'] = trim($booking->leadPassengerTitle .' '. $booking->leadPassengerName);
                $new['booking']['passenger'][1]['email'] = $booking->leadPassengerEmail ?: '';
                $new['booking']['passenger'][1]['phone'] = $booking->leadPassengerMobile;
            }

            $new['booking']['requirement'][0]['value'] = $booking->requirements;
            $new['booking']['source'][0]['source'] = $booking->source;
            $new['booking']['source'][0]['comments'] = $booking->sourceDetails;
            $new['booking']['notifications'] = $this->objectForm['notifications'];
            $new['booking']['notifications'][0]['send_notification'] = 1;
            $new['booking']['customField'][0]['custom'] = $booking->custom ?: '';

            if ($booking->locale !== null && $booking->locale != 'null' && $booking->locale != '') {
                $new['booking']['notifications'][0]['locale'] = $booking->locale;
                $new['booking']['notifications'][0]['locale_text_selected'] = config('app.locales.'.$booking->locale.'.name');
            }
            else {
                $new['booking']['notifications'][0]['locale_text_selected'] = trans('booking.default');
            }

            $new['booking']['discount'][0]['price'] = $booking->totalDiscount;
            $new['booking']['discountCode'][0]['code'] = $booking->discountCode;
            $new['booking']['notes'][0]['notes'] = $booking->notes;

            if ($booking->userId > 0) {
                $customerController = new \App\Http\Controllers\User\CustomerController();
                $customer = $customerController->get($booking->userId);

                if ($customer) {
                    $new['booking']['customer'][0]['customer'] = $booking->userId;
                    $new['booking']['customer'][0]['userAvatar'] = $customer->avatar_path;
                    $new['booking']['customer'][0]['userName'] = $customer->name;
                    $new['booking']['customer'][0]['userEmail'] = $customer->email;
                    $new['booking']['customer'][0]['userPhone'] = $customer->profile->mobile_no;
                    $new['booking']['customer'][0]['customer_text_selected'] = '<div class="eto-select clearfix"><span class="eto-select-group eto-select-group-name">'.$customer->name.'</span></div>';
                    $new['booking']['customer'][0]['department'] = $booking->department ?: '';
                } else {
                    $new['booking']['customer'][0]['customer'] = $booking->userId;
                    $new['booking']['customer'][0]['userAvatar'] = '';
                    $new['booking']['customer'][0]['userName'] = trans('booking.customer_deleted');
                    $new['booking']['customer'][0]['userEmail'] = '';
                    $new['booking']['customer'][0]['userPhone'] = '';
                    $new['booking']['customer'][0]['customer_text_selected'] = '<div class="eto-select clearfix"><span class="eto-select-group eto-select-group-name">'.trans('booking.customer_deleted').'</span></div>';
                    $new['booking']['customer'][0]['department'] = $booking->department ?: '';
                }
            }
            else {
                $new['booking']['customer'] = $this->objectForm['customer'];
            }

            if ($booking->driverId > 0) {
                $driverController = new \App\Http\Controllers\User\DriverController();
                $driver = $driverController->get($booking->driverId);

                if (!empty($driver->id)) {
                    $vehicle = $driverController->getVehicle($booking->vehicleId);
                    $vehicleName = !empty($vehicle) ? $vehicle->displayName : '';

                    $new['booking']['driver'][0]['driver'] = $booking->driverId;
                    $new['booking']['driver'][0]['vehicle'] = $booking->vehicleId;
                    $new['booking']['driver'][0]['comments'] = $booking->driverNotes;
                    $new['booking']['driver'][0]['commission'] = $route->commission;
                    $new['booking']['driver'][0]['formated_commission'] = SiteHelper::formatPrice($route->commission);
                    $new['booking']['driver'][0]['cash'] = $route->cash;
                    $new['booking']['driver'][0]['formated_cash'] = SiteHelper::formatPrice($route->cash);
                    $new['booking']['driver'][0]['userAvatar'] = $driver->avatar_path;
                    $new['booking']['driver'][0]['userName'] = $driver->displayName;
                    $new['booking']['driver'][0]['uniqueId'] = $driver->profile->unique_id;
                    $new['booking']['driver'][0]['userEmail'] = $driver->email;
                    $new['booking']['driver'][0]['userPhone'] = $driver->profile->mobile_no;
                    $new['booking']['driver'][0]['driver_text_selected'] = '<div class="eto-select clearfix"><span class="eto-select-group eto-select-group-name">'.$driver->displayName.'</span></div>';
                    $new['booking']['driver'][0]['vehicle_text_selected'] = '<div class="eto-select clearfix"><span class="eto-select-group eto-select-group-name">'.$vehicleName.'</span></div>';
                }
            }
            else {
                $new['booking']['driver'] = $this->objectForm['driver'];

                if (!empty($route->commission)) {
                    $new['booking']['driver'][0]['commission'] = $route->commission;
                    $new['booking']['driver'][0]['formated_commission'] = SiteHelper::formatPrice($route->commission);
                }

                if (!empty($route->cash)) {
                    $new['booking']['driver'][0]['cash'] = $route->cash;
                    $new['booking']['driver'][0]['formated_cash'] = SiteHelper::formatPrice($route->cash);
                }
            }

            if ($booking->fleet_id > 0) {
                $userController = new \App\Http\Controllers\User\UserController();
                $fleet = $userController->getFleet($booking->fleet_id);

                if (!empty($fleet->id)) {
                    $new['booking']['fleet'][0]['fleet'] = $booking->fleet_id;
                    $new['booking']['fleet'][0]['commission'] = $booking->fleet_commission;
                    $new['booking']['fleet'][0]['formated_commission'] = SiteHelper::formatPrice($booking->fleet_commission);
                    $new['booking']['fleet'][0]['userAvatar'] = $fleet->avatar_path;
                    $new['booking']['fleet'][0]['userName'] = $fleet->displayName;
                    $new['booking']['fleet'][0]['userEmail'] = $fleet->email;
                    $new['booking']['fleet'][0]['userPhone'] = $fleet->profile->mobile_no;
                    $new['booking']['fleet'][0]['fleet_text_selected'] = '<div class="eto-select clearfix"><span class="eto-select-group eto-select-group-name">'.$driver->displayName.'</span></div>';
                }
            }
            else {
                $new['booking']['fleet'] = $this->objectForm['fleet'];

                if (!empty($booking->fleet_commission)) {
                    $new['booking']['fleet'][0]['commission'] = $booking->fleet_commission;
                    $new['booking']['fleet'][0]['formated_commission'] = SiteHelper::formatPrice($booking->fleet_commission);
                }
            }
        }
        else {
            for ($i = 1; $i <= 2; $i++) {
                if (count($new['booking']) === 1 && $i === 2) {
                    break;
                }
                if ($i === 2) {
                    foreach ($new['booking'][1] as $key => $val) {
                        if (empty($new['booking'][2][$key])) {
                            if ($key == 'item') { $val = []; }
                            $new['booking'][2][$key] = $val;
                        }
                    }
                }
                $routeId = 'route' . $i;
                reset($new['booking'][$i]['location']);
                $start = key($new['booking'][$i]['location']);
                if (count($new['booking'][$i]['location']) > 1) {
                    end($new['booking'][$i]['location']);
                    $end = key($new['booking'][$i]['location']);
                }

                $new['booking'][$i]['location'][$start]['lat'] = $booking->$routeId->coordinate->start->lat;
                $new['booking'][$i]['location'][$start]['lng'] = $booking->$routeId->coordinate->start->lon;
                $new['booking'][$i]['location'][$start]['place_id'] = $booking->$routeId->coordinate->start->place_id;

                if (!empty($end)) {
                    $new['booking'][$i]['location'][$end]['lat'] = $booking->$routeId->coordinate->end->lat;
                    $new['booking'][$i]['location'][$end]['lng'] = $booking->$routeId->coordinate->end->lon;
                    $new['booking'][$i]['location'][$end]['place_id'] = $booking->$routeId->coordinate->end->place_id;
                }

                $new['booking'][$i]['startEnd'][0]['distance'] = $booking->$routeId->distance;
                $new['booking'][$i]['startEnd'][0]['duration'] = $booking->$routeId->duration;
                $new['booking'][$i]['baseStart'][0]['distance'] = $booking->$routeId->distance_base_start;
                $new['booking'][$i]['baseStart'][0]['duration'] = $booking->$routeId->duration_base_start;
                $new['booking'][$i]['endBase'][0]['distance'] = $booking->$routeId->distance_base_end;
                $new['booking'][$i]['endBase'][0]['duration'] = $booking->$routeId->duration_base_end;

                $new['booking'][$i]['quote'] = [
                    'stepsText' => !empty($booking->$routeId->stepsText) ? $booking->$routeId->stepsText : '',
                    'summaryText' => !empty($booking->$routeId->summaryText) ? $booking->$routeId->summaryText : '',
                    'status' => !empty($booking->$routeId->status) ? $booking->$routeId->status : '',
                    'totalPrice' => !empty($booking->$routeId->totalPrice) ? $booking->$routeId->totalPrice : 0,
                    'totalPriceWithDiscount' => !empty($booking->$routeId->totalPriceWithDiscount) ? $booking->$routeId->totalPriceWithDiscount : 0,
                    'totalDiscount' => !empty($booking->$routeId->totalDiscount) ? $booking->$routeId->totalDiscount : 0,
                    'accountDiscount' => !empty($booking->$routeId->accountDiscount) ? $booking->$routeId->accountDiscount : 0,
                ];
                $new['booking'][$i]['discount'][0]['price'] = $new['booking'][$i]['quote']['totalDiscount'];
                $new['booking'][$i]['flightDetails'][0]['meeting_point'] = !empty($booking->$routeId->meetingPoint) ? $booking->$routeId->meetingPoint : '';

                $new['booking'][$i]['item'] = !empty($new['booking'][$i]['item']) ? $new['booking'][$i]['item'] : [];
                $new['booking'][$i]['journeyPrice'][0]['price'] = 0;
                $new['booking'][$i]['journeyPrice'][0]['override_name'] = '';

                $items = $new['booking'][$i]['item'];

                if (!empty($booking->$routeId->extraChargesList)) {
                    foreach ($booking->$routeId->extraChargesList as $item) {
                        $item->base = round($item->base, 2);

                        if ($item->type == 'journey') {
                            $new['booking'][$i]['journeyPrice'][0]['price'] = $item->base;
                        }
                        else {
                            $fields = Field::toList('booking', $siteId)->where('field_key', $item->type)->isActive()->get();
                            $fields = Field::translateNames($fields);

                            $itemId = 0;
                            foreach ($fields as $fid => $val) {
                                $itemId = $fid;
                            }

                            $exist = 0;
                            foreach ($items as $kI => $vI) {
                                if ((isset($vI['item_key']) && isset($item->type) && $vI['item_key'] == $item->type) &&
                                    (isset($vI['amount']) && isset($item->amount) && $vI['amount'] == $item->amount) &&
                                    (isset($vI['price']) && isset($item->base) && $vI['price'] == $item->base)) {
                                    $exist = 1;
                                    break;
                                }
                            }

                            $new['booking'][$i]['item'][] = [
                                'item' => (string)$itemId,
                                'prev_item' => (string)$itemId,
                                'price' => (string)$item->base,
                                'amount' => (string)$item->amount,
                                'item_key' => $item->type,
                                'item_trans_key' => $item->type,
                                'item_type' => $item->name,
                                'override_name' => $item->name,
                                'added' => 1,
                                'exist' => $exist ? 'true' : 'false',
                            ];
                        }

                        if (!empty($new['booking'][$i]['item'])) {
                            foreach ($new['booking'][$i]['item'] as $item) {
                                if (empty($item)) {
                                    unset($item);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $new;
    }

    /**
     * @param $data
     * @param string $task
     * @param bool $siteId
     * @param string $apiType
     * @return \stdClass
     */
    private function convertToOld($data, $task = 'bookings', $siteId = false, $apiType = 'backend')
    {
        $action = !empty($data->action) ? $data->action : 'update';
        $booking = $data->booking;
        $bookingId = !empty($data->bookingId) ? $data->bookingId : 0;
        $waypointsTemp = [];
        $waypointsNew = false;
        $vehicleIds = [];
        $vehicleNew = false;
        $itemsEdited = false;

        if (!empty($bookingId)) {
            $bookingTemp = \App\Models\BookingRoute::withTrashed()->find($bookingId);
            $vehicleIds[] = $bookingTemp->vehicle_id;
            if (!is_array($bookingTemp->items) || !is_object($bookingTemp->items)) {
                $bookingTemp->items = json_decode($bookingTemp->items);
            }
            if (!empty($bookingTemp->booking->site_id)) {
                $waypointsTemp = \GuzzleHttp\json_decode($bookingTemp->waypoints, true);
                $siteId = $bookingTemp->booking->site_id;
            }
        }

        $siteId = $siteId != false ? $siteId : config('site.site_id');
        $services = SettingsHelper::getServices(config('site.site_id'))['data'];
        $service = $services[0];
        if (!empty($booking[1]['service'][0])) {
            foreach ($services as $kS => $vS) {
                if ($booking[1]['service'][0]['service'] == $vS['id']) {
                    $service = $vS;
                }
            }
        }

        $passenger = !empty($booking[1]['passenger'][0]) ? $booking[1]['passenger'][0] : false;
        $leadPassenger = !empty($booking[1]['passenger'][1]) ? $booking[1]['passenger'][1] : false;

        $parseData = new \stdClass();
        $bookingData = new \stdClass();

        $bookingData->scheduledRouteId = 0;
        $bookingData->parentBookingId = 0;
        $bookingData->serviceId = !empty($booking[1]['service'][0]['service']) ? $booking[1]['service'][0]['service'] : 0;
        $bookingData->serviceDuration = !empty($service['duration']) && $service['duration'] == '1' ? $booking[1]['serviceDuration'][0]['service_duration'] : 0;
        $bookingData->routeReturn = !empty($booking[2]) ? 2 : 1;
        $bookingData->payment = !empty($booking[1]['paymentType'][0]['payment_method']) ? $booking[1]['paymentType'][0]['payment_method'] : 0;
        $bookingData->discountCode = !empty($booking[1]['discountCode'][0]['code']) ? $booking[1]['discountCode'][0]['code'] : '';
        $bookingData->bookingId = $bookingId;

        if ($action == 'update') {
            if (!empty($bookingData->discountCode) && empty($booking[1]['discountCode'][0]['discount_id'])) {
                $discount = Discount::where('site_id', config('site.site_id'))->where('code', $bookingData->discountCode)->first();
                $booking[1]['discountCode'][0]['discount_id'] = !empty($discount->id) ? $discount->id : 0;
            }

            // passenger
            $bookingData->contactTitle = '';
            $bookingData->contactName = !empty($passenger['name']) ? $passenger['name'] : '';
            $bookingData->contactEmail = !empty($passenger['email']) ? $passenger['email'] : '';
            $bookingData->contactMobile = !empty($passenger['phone']) ? $passenger['phone'] : '';
            $bookingData->requirements = !empty($booking[1]['requirement'][0]['value']) ? $booking[1]['requirement'][0]['value'] : '';

            // leadPassenger
            $bookingData->leadPassenger = $leadPassenger !== false ? 1 : 0;
            $bookingData->leadPassengerTitle = '';
            $bookingData->leadPassengerName = !empty($leadPassenger['name']) ? $leadPassenger['name'] : '';
            $bookingData->leadPassengerEmail = !empty($leadPassenger['email']) ? $leadPassenger['email'] : '';
            $bookingData->leadPassengerMobile = !empty($leadPassenger['phone']) ? $leadPassenger['phone'] : '';

            $bookingData->discountId = !empty($booking[1]['discountCode'][0]['discount_id']) ? $booking[1]['discountCode'][0]['discount_id'] : 0;
            $bookingData->notes = !empty($booking[1]['notes'][0]['notes']) ? $booking[1]['notes'][0]['notes'] : '';
            $bookingData->statusNotes = !empty($booking[1]['bookingStatus'][0]['comments']) ? $booking[1]['bookingStatus'][0]['comments'] : '';
            $bookingData->status = !empty($booking[1]['bookingStatus'][0]['status']) ? $booking[1]['bookingStatus'][0]['status'] : 'pending';
            $bookingData->source = !empty($booking[1]['source'][0]['source']) ? $booking[1]['source'][0]['source'] : '';

            // $this->setSourceIfNotExist($bookingData->source, $siteId);

            $bookingData->sourceDetails = !empty($booking[1]['source'][0]['comments']) ? $booking[1]['source'][0]['comments'] : '';
            $bookingData->driverId = !empty($booking[1]['driver'][0]['driver']) ? $booking[1]['driver'][0]['driver'] : 0;
            $bookingData->vehicleId = !empty($booking[1]['driver'][0]['vehicle']) ? $booking[1]['driver'][0]['vehicle'] : 0;
            $bookingData->driverNotes = !empty($booking[1]['driver'][0]['comments']) ? $booking[1]['driver'][0]['comments'] : '';
            $bookingData->userId = !empty($booking[1]['customer'][0]['customer']) ? $booking[1]['customer'][0]['customer'] : 0;

            $bookingData->locale = !empty($booking[1]['notifications'][0]['locale']) ? $booking[1]['notifications'][0]['locale'] : '';
            $bookingData->notifyPayment = !empty($booking[1]['notifications'][0]['send_invoice']) ? $booking[1]['notifications'][0]['send_invoice'] : 0;
            $bookingData->notifyEmail = !empty($booking[1]['notifications'][0]['email']) ? $booking[1]['notifications'][0]['email'] : '';
            $bookingData->notifyMsg = !empty($booking[1]['notifications'][0]['comments']) ? $booking[1]['notifications'][0]['comments'] : '';
            $bookingData->totalPrice = 0;
            $bookingData->totalPriceWithDiscount = 0;
            $bookingData->totalDiscount = 0;
            $bookingData->totalDeposit = !empty($booking[1]['deposit'][0]['price']) ? $booking[1]['deposit'][0]['price'] : 0;
            $bookingData->quote = 0;
        }

        $globalRouteItems = ['childSeats','babySeats','infantSeats','wheelchair'];
        foreach($booking as $id=>$route) {
            $routeId = 'route'.$id;
            $totalPrice = 0;

            if ($id === 2) {
                foreach($booking[1] as $key=>$val) {
                    if (empty($booking[2][$key]) && $key != 'item') {
                        $route[$key] = $val;
                    }
                }
            }

            $bookingData->$routeId = new \stdClass();
            $locations = $route['location'];
            $start = reset($route['location']);
            unset($locations[0]);

            if (count($locations) > 0 || $service['hide_location'] == 1) {
                $end = end($route['location']);
                $lastKey = key($route['location']);
                unset($locations[$lastKey]);
            }

            $bookingData->$routeId->category = ['start'=>0,'end'=>0,'type'=>['start'=>'address','end'=>'address']];
            $bookingData->$routeId->coordinate = ['start'=>['lat'=>0,'lon'=>0,'place_id'=>''], 'end'=>['lat'=>0,'lon'=>0,'place_id'=>'']];

            if (empty($end['address']) || $service['hide_location'] == '1') {
                $end = $start;
            }

            $bookingData->$routeId->location['start'] = $start['address'];
            $bookingData->$routeId->location['end'] = $end['address'];


            // Geocode
            $updateStart = false;
            $updateEnd = false;

            if (!empty($bookingId) && !empty($bookingTemp->id)) {
                if ($bookingTemp->location_start != $start['address']) {
                    $updateStart = true;
                }
                if ($bookingTemp->location_end != $end['address']) {
                    $updateEnd = true;
                }
                // dd($bookingTemp->location_start, $start, $updateStart, $bookingTemp->location_end, $end, $updateEnd, $bookingTemp);
            }

            if ($updateStart || (empty($start['lat']) || empty($start['lng']))) {
                $geocode = \App\Helpers\LocationHelper::geocode([
                    'address' => !empty($start['address']) ? $start['address'] : '',
                    'place_id' => !empty($start['place_id']) ? $start['place_id'] : '',
                    // 'lat' => !empty($start['lat']) ? $start['lat'] : null,
                    // 'lng' => !empty($start['lng']) ? $start['lng'] : null,
                ]);
                if (!empty($geocode->status) && $geocode->status == 'OK') {
                    $start['lat'] = $geocode->results[0]->geometry->location->lat;
                    $start['lng'] = $geocode->results[0]->geometry->location->lng;
                }
            }

            if ($updateEnd || (empty($end['lat']) || empty($end['lng']))) {
                $geocode = \App\Helpers\LocationHelper::geocode([
                    'address' => !empty($end['address']) ? $end['address'] : '',
                    'place_id' => !empty($end['place_id']) ? $end['place_id'] : '',
                    // 'lat' => !empty($end['lat']) ? $end['lat'] : null,
                    // 'lng' => !empty($end['lng']) ? $end['lng'] : null,
                ]);
                if (!empty($geocode->status) && $geocode->status == 'OK') {
                    $end['lat'] = $geocode->results[0]->geometry->location->lat;
                    $end['lng'] = $geocode->results[0]->geometry->location->lng;
                }
            }


            if (!empty($start['lat'])) { $bookingData->$routeId->coordinate['start']['lat'] = $start['lat']; }
            if (!empty($start['lng'])) { $bookingData->$routeId->coordinate['start']['lon'] = $start['lng']; }
            if (!empty($start['place_id'])) { $bookingData->$routeId->coordinate['start']['place_id'] = $start['place_id']; }

            if (!empty($end['lat'])) { $bookingData->$routeId->coordinate['end']['lat'] = $end['lat']; }
            if (!empty($end['lng'])) { $bookingData->$routeId->coordinate['end']['lon'] = $end['lng']; }
            if (!empty($end['place_id'])) { $bookingData->$routeId->coordinate['end']['place_id'] = $end['place_id']; }

            $bookingData->$routeId->address = $bookingData->$routeId->location;
            $bookingData->$routeId->addressComplete = ['start' => !empty($start['complete']) ? $start['complete'] : '', 'end' => !empty($end['complete']) ? $end['complete'] : ''];
            $bookingData->$routeId->waypoints = [];
            $bookingData->$routeId->waypointsComplete = [];

            if (count($locations) > 0 && $service['hide_location'] == '0') {
                foreach($locations as $idl=>$location) {
                    if (!empty($location['address'])) {
                        $bookingData->$routeId->waypoints[$idl] = !empty($location['address']) ? $location['address'] : '';
                        $bookingData->$routeId->waypointsComplete[$idl] = !empty($location['complete']) ? $location['complete'] : '';
                        if (!$waypointsNew && !in_array($location['address'], $waypointsTemp)) {
                            $waypointsNew = true;
                        }
                    }
                }
            }

            if ($action != 'update') {
                $bookingData->$routeId->address['waypoints'] = $bookingData->$routeId->waypoints;
            }

            $bookingData->$routeId->department = !empty($booking[1]['customer'][0]['department']) ? $booking[1]['customer'][0]['department'] : '';
            $bookingData->$routeId->distance = !empty($route['startEnd'][0]['distance']) ? $route['startEnd'][0]['distance'] : 0;
            $bookingData->$routeId->duration = !empty($route['startEnd'][0]['duration']) ? $route['startEnd'][0]['duration'] : 0;
            $bookingData->$routeId->distance_base_start = !empty($route['baseStart'][0]['distance']) ? $route['baseStart'][0]['distance'] : 0;
            $bookingData->$routeId->duration_base_start = !empty($route['baseStart'][0]['duration']) ? $route['baseStart'][0]['duration'] : 0;
            $bookingData->$routeId->distance_base_end = !empty($route['endBase'][0]['distance']) ? $route['endBase'][0]['distance'] : 0;
            $bookingData->$routeId->duration_base_end = !empty($route['endBase'][0]['duration']) ? $route['endBase'][0]['duration'] : 0;
            $bookingData->$routeId->date = !empty($route['when'][0]['date']) ? $route['when'][0]['date'] : date('Y-m-d H:i');
            $bookingData->$routeId->flightNumber = !empty($route['flightDetails'][0]['flight_number']) ? $route['flightDetails'][0]['flight_number'] : '';
            $bookingData->$routeId->flightLandingTime = !empty($route['flightDetails'][0]['flight_landing_time']) ? $route['flightDetails'][0]['flight_landing_time'] : '';
            $bookingData->$routeId->departureCity = !empty($route['flightDetails'][0]['arriving_from']) ? $route['flightDetails'][0]['arriving_from'] : '';
            $bookingData->$routeId->departureFlightNumber = !empty($route['flightDetails'][0]['departure_flight_number']) ? $route['flightDetails'][0]['departure_flight_number'] : '';
            $bookingData->$routeId->departureFlightTime = !empty($route['flightDetails'][0]['departure_flight_time']) ? $route['flightDetails'][0]['departure_flight_time'] : '';
            $bookingData->$routeId->departureFlightCity = !empty($route['flightDetails'][0]['departure_flight_city']) ? $route['flightDetails'][0]['departure_flight_city'] : '';
            $bookingData->$routeId->meetAndGreet = !empty($route['flightDetails'][0]['meet_and_greet']) ? (int)$route['flightDetails'][0]['meet_and_greet'] : 0;
            $bookingData->$routeId->meetingPoint = !empty($route['flightDetails'][0]['meeting_point']) ? $route['flightDetails'][0]['meeting_point'] : '';
            $bookingData->$routeId->waitingTime = !empty($route['flightDetails'][0]['waiting_time']) ? $route['flightDetails'][0]['waiting_time'] : 0;
            $bookingData->$routeId->requirements = !empty($route['requirement'][0]['value']) ? $route['requirement'][0]['value'] : '';
            $bookingData->$routeId->notes = !empty($route['notes'][0]['notes']) ? $route['notes'][0]['notes'] : '';
            $bookingData->$routeId->driverId = !empty($route['driver'][0]['driver']) ? $route['driver'][0]['driver'] : 0;
            $bookingData->$routeId->vehicleId = !empty($route['driver'][0]['vehicle']) ? $route['driver'][0]['vehicle'] : 0;
            $bookingData->$routeId->driverNotes = !empty($route['driver'][0]['comments']) ? $route['driver'][0]['comments'] : '';
            $bookingData->$routeId->commission = !empty($route['driver'][0]['commission']) ? $route['driver'][0]['commission'] : 0;
            $bookingData->$routeId->cash = !empty($route['driver'][0]['cash']) ? $route['driver'][0]['cash'] : 0;
            $bookingData->$routeId->fleet_id = !empty($booking[1]['fleet'][0]['fleet']) ? $booking[1]['fleet'][0]['fleet'] : 0;
            $bookingData->$routeId->fleet_commission = !empty($booking[1]['fleet'][0]['commission']) ? $booking[1]['fleet'][0]['commission'] : 0;
            $bookingData->$routeId->statusNotes = !empty($route['bookingStatus'][0]['comments']) ? $route['bookingStatus'][0]['comments'] : '';
            $bookingData->$routeId->items = [];
            $bookingData->$routeId->passengers = !empty($route['passengerAmount'][0]['amount']) ? $route['passengerAmount'][0]['amount'] : 0;
            $bookingData->$routeId->luggage = !empty($route['luggageAmount'][0]['amount']) ? $route['luggageAmount'][0]['amount'] : 0;
            $bookingData->$routeId->handLuggage = !empty($route['handLuggageAmount'][0]['amount']) ? $route['handLuggageAmount'][0]['amount'] : 0;
            $bookingData->$routeId->custom = !empty($route['customField'][0]['custom']) ? $route['customField'][0]['custom'] : null;
            $bookingData->$routeId->childSeats = 0;
            $bookingData->$routeId->babySeats = 0;
            $bookingData->$routeId->infantSeats = 0;
            $bookingData->$routeId->wheelchair = 0;
            $bookingData->$routeId->items[] = [
                'type' => 'journey',
                'name' => !empty($route['journeyPrice'][0]['override_name']) ? $route['journeyPrice'][0]['override_name'] : '',
                'value' => !empty($route['journeyPrice'][0]['price']) ? $route['journeyPrice'][0]['price'] : 0,
                'amount' => 1
            ];
            $totalPrice += !empty($route['journeyPrice'][0]['price']) ? $route['journeyPrice'][0]['price'] : 0;
            if (!empty($route['item'])) {
                if (count($route['item']) > 0) {
                    foreach ($route['item'] as $item) {
                        if (!empty($item['item_key'])) {
                            $item['price'] = !empty($item['price']) ? $item['price'] : 0;
                            $item['amount'] = !empty($item['amount']) ? $item['amount'] : 0;

                            if ($action == 'update') {
                                $key = !empty($item['item_key']) ? $item['item_key'] : '';
                                $key = isset($bookingData->$routeId->$key) ? $key : $key.'s';
                                $key = camel_case($key);

                                $key2 = !empty($item['item_trans_key']) ? $item['item_trans_key'] : '';
                                $key2 = isset($bookingData->$routeId->$key) ? $key2 : $key2.'s';
                                $key2 = camel_case($key2);

                                if (in_array($key, $globalRouteItems)) {
                                    $bookingData->$routeId->{$key} += $item['amount'];
                                }
                                elseif (in_array($key2, $globalRouteItems)) {
                                    $bookingData->$routeId->{$key2} += $item['amount'];
                                }
                            }

                            if (!empty($item['item_key'])) {
                                $addItem = $bookingData->$routeId->items[] = [
                                    'type' => $item['item_key'],
                                    'name' => !empty($item['override_name']) ? $item['override_name'] : '',
                                    'value' => !empty($item['price']) ? $item['price'] : 0,
                                    'amount' => !empty($item['amount']) ? $item['amount'] : 0,
                                    'original_name' => !empty($item['item_type']) ? $item['item_type'] : '',
                                ];
                                $totalPrice += $item['price'] * $item['amount'];

                                if (isset($bookingTemp) && (is_array($bookingTemp->items) || is_object($bookingTemp->items)) && count($bookingTemp->items) > 0) {
                                    foreach ($bookingTemp->items as $itemTemp) {
                                        if ($itemTemp->type == $addItem['type']
                                            && ($itemTemp->name != $addItem['name']
                                                || $itemTemp->name != $addItem['value']
                                                || $itemTemp->amount != $addItem['amount'])
                                        ) {
                                            $itemsEdited = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $bookingData->$routeId->vehicle = [];

            if ($id === 2 && !empty($booking[2]['vehicle'])) {
                $vehicles = $booking[2]['vehicle'];
            }
            else {
                $vehicles = $booking[1]['vehicle'];
            }

            foreach($vehicles as $vid=>$vehicle) {
                if (!empty($vehicle['vehicle_type']) || (!empty($vehicle['vehicle_type']) && count($vehicles) > 0 && $vehicle['vehicle_type'] != '0')) {
                    if (empty($bookingData->$routeId->vehicle[$vehicle['vehicle_type']])) {
                        $bookingData->$routeId->vehicle[$vehicle['vehicle_type']] = [
                            'id' => !empty($vehicle['vehicle_type']) ? $vehicle['vehicle_type'] : 0,
                            'amount' => !empty($vehicle['vehicle_amount']) ? $vehicle['vehicle_amount'] : 0,
                        ];
                    }
                    else {
                        $bookingData->$routeId->vehicle[$vehicle['vehicle_type']]['amount'] += !empty($vehicle['vehicle_amount']) ? $vehicle['vehicle_amount'] : 0;
                    }

                    if (!in_array($bookingData->$routeId->vehicle[$vehicle['vehicle_type']]['id'], $vehicleIds)) {
                        $vehicleNew = true;
                    }
                }
            }

            $vehicleList = $bookingData->$routeId->vehicle;
            unset($bookingData->$routeId->vehicle);
            foreach($vehicleList as $vehicleItem) {
                $bookingData->$routeId->vehicle[] = $vehicleItem;
            }

            $bookingData->$routeId->totalPrice = $totalPrice < 0 ? 0 : $totalPrice;

            if ($action != 'update') {
                if ((empty($route['discount'][0]['price']) || $route['discount'][0]['price'] == 0) && $bookingData->discountCode != '') {
                    $discouttData = $this->getDiscountFromCode($bookingData->discountCode, $totalPrice);
                    $route['discount'][0]['price'] = $discouttData['value'];
                }
            }
            else {
                if (!empty($booking[1]['notifications'][0]['send_notification']) && (int)$booking[1]['notifications'][0]['send_notification'] === 1) {
                    if (isset($bookingTemp)) {
                        $bookingTemp->items = is_array($bookingTemp->items) || is_object($bookingTemp->items) ? $bookingTemp->items : [];
                    }

                    if ((!empty($bookingTemp->booking->site_id)
                            && (
                                $bookingId === 0
                                || $bookingData->$routeId->date != $bookingTemp->date->format('Y-m-d H:i')
                                || (int)$bookingData->userId != (int)$bookingTemp->booking->user_id
                                || $bookingData->contactName != $bookingTemp->contact_name
                                || $bookingData->contactEmail != $bookingTemp->contact_email
                                || $bookingData->contactMobile != $bookingTemp->contact_mobile
                                || $bookingData->leadPassengerName != $bookingTemp->lead_passenger_name
                                || $bookingData->leadPassengerEmail != $bookingTemp->lead_passenger_email
                                || $bookingData->leadPassengerMobile != $bookingTemp->lead_passenger_mobile
                                || $bookingData->status != $bookingTemp->status
                                || $bookingData->requirements != $bookingTemp->requirements
                                || (float)$bookingData->$routeId->totalPrice != (float)$bookingTemp->total_price
                                || $bookingData->$routeId->location['start'] != $bookingTemp->address_start
                                || $bookingData->$routeId->location['end'] != $bookingTemp->address_end
                                || (int)$bookingData->$routeId->passengers != (int)$bookingTemp->passengers
                                || (int)$bookingData->$routeId->driverId !== (int)$bookingTemp->driver_id
                                || count($bookingData->$routeId->items) !== count($bookingTemp->items)
                                || $waypointsNew === true
                                || $vehicleNew === true
                                || $itemsEdited === true
                                || (float)$route['discount'][0]['price'] !== (float)$bookingTemp->discount
                            )
                        )
                        || empty($bookingTemp->booking->site_id)) {
                        $bookingData->notifyStatus = 1;
                    }
                }
                else {
                    $bookingData->notifyStatus = 0;
                }
            }

            $bookingData->$routeId->totalDiscount = !empty($route['discount'][0]['price']) ? $route['discount'][0]['price'] : 0;
            $bookingData->$routeId->totalPriceWithDiscount = $bookingData->$routeId->totalPrice - $bookingData->$routeId->totalDiscount;
        }

        $parseData->task = $task;
        $parseData->action = $action;
        $parseData->booking = $bookingData;
        $parseData->id = $bookingId;
        $parseData->siteId = $siteId;
        $parseData->apiType = $apiType;

        // dd($bookingData);

        return $parseData;
    }

    public function recursive_array_search($needle, $haystack, $currentKey = '')
    {
        foreach($haystack as $key=>$value) {
            if (is_array($value)) {
                $nextKey = $this->recursive_array_search($needle,$value, $currentKey . '[' . $key . ']');
                if ($nextKey) {
                    return $nextKey;
                }
            }
            else if ($value==$needle) {
                return is_numeric($key) ? $currentKey . '[' .$key . ']' : $currentKey . '["' .$key . '"]';
            }
        }
        return false;
    }

    public function getDiscountFromCode($code, $totalPrice)
    {
        $discoun = Discount::where('site_id', config('site.site_id'))->where('code', $code)->first();
        $discountMessage= '';

        if ( !empty($discoun) ) {
            $ok = 0;

            if ( $discoun->used_times >= $discoun->allowed_times ) {
                $discountMessage = trans('frontend.js.quote_DiscountExpired');
            }
            elseif ( !(time() >= strtotime($discoun->start_date)) && $discoun->start_date != null ) {
                $discountMessage = trans('frontend.js.quote_DiscountExpired');
            }
            elseif ( !(time() <= strtotime($discoun->end_date)) && $discoun->end_date != null ) {
                $discountMessage = trans('frontend.js.quote_DiscountExpired');
            }
            else {
                $ok = 1;
            }
            if ( $ok ) {
                if ( $discoun->type == 1 ) {
                    $discountValue = ($totalPrice / 100) * $discoun->value;
                }
                else {
                    $discountValue = $discoun->value;
                }
            }
        }
        else {
            $discountMessage = trans('frontend.js.quote_DiscountInvalid');
            $discountValue = 0;
        }

        return ['value'=>$discountValue, 'message'=>$discountMessage];
    }

    public function generateDefaultObject($bookingId = false)
    {
        $service = $this->getService();
        $vehicle = $this->getVehicle();
        $payment = $this->getPaymentMethod();
        $objectForm = $this->objectForm;
        $objectForm['service'][0]['service'] = !empty($service->id) ? $service->id : 0;
        $objectForm['location'][1] = $objectForm['location'][0];
        $objectForm['serviceDuration'][0]['service_duration'] = !empty($service->duration_min) ? $service->duration_min : 1;
        $objectForm['vehicle'][0]['vehicle_type'] = !empty($vehicle->id) ? $vehicle->id : 0;
        $objectForm['vehicle'][0]['vehicle_type_text_selected'] = !empty($vehicle->name) ? $vehicle->name : trans('booking.booking_unassigned');
        $objectForm['paymentType'][0]['payment_method'] = !empty($payment->id) ? $payment->id : 0;
        $objectForm['paymentType'][0]['payment_type_text'] = !empty($payment->name) ? $payment->name : trans('booking.booking_unassigned');
        $objectForm['notifications'][0]['locale_text_selected'] = trans('booking.default');

        if (is_numeric($bookingId)) {
            $booking = $this->getBooking($bookingId);

            foreach($booking['booking'] as $section=>$data) {
                $objectForm[$section] = $data;
            }
        }

        $return = ['values'=>$objectForm, 'bookingId'=>$bookingId];
        if (!empty($booking)){
            $return['refNumber'] = $booking['refNumber'];
            if (!empty($booking['total_details'])){
                $return['total_details'] = $booking['total_details'];
            }
            if (!empty($booking['income_charges'])){
                $return['income_charges'] = $booking['income_charges'];
            }
        }

        if (config('app.debug') == true) {
            $return['!DATA'] = [
                $service,
                $vehicle,
                $payment,
            ];
        }

        return $return;
    }

    private function getService($siteId = false, $serviceId = false)
    {
        if ($siteId === false) {
            $siteId = config('site.site_id');
        }

        $service = \App\Models\Service::where('relation_type', 'site')
            ->where('relation_id', $siteId)
            ->where('status', 'active');

        if (is_numeric($serviceId)){
            $service->where('id', $serviceId);
        }
        else {
            $service->where('is_featured', 1);
        }

        $data = $service->first();
        return $data;
    }

    private function getVehicle($siteId = false, $vehicleId = false)
    {
        if ($siteId === false) {
            $siteId = config('site.site_id');
        }

        $vehicle = \App\Models\VehicleType::select(['id', 'name'])
            ->where('site_id', $siteId);

        if (is_numeric($vehicleId)){
            $vehicle->where('id', $vehicleId);
        }
        else {
            $vehicle->where('default', 1);
        }

        return $vehicle->first();
    }

    private function getPaymentMethod($siteId = false, $paymentId = false)
    {
        if ($siteId === false) {
            $siteId = config('site.site_id');
        }

        $payments = \App\Models\Payment::where('site_id', $siteId)
            ->where('published', 1);

        if (is_numeric($paymentId)){
            $payments->where('id', $paymentId);
        }
        else {
            $payments->where('default', 1);
        }

        return $payments->first();
    }

    private function setSourceIfNotExist($source, $siteId = false)
    {
        if (trim($source) == '') {
            return false;
        }

        if ($siteId === false) {
            $siteId = config('site.site_id');
        }

        $sourceList = config('eto_booking.sources');

        if (in_array($source, $sourceList) === false) {
            $sourceList[] = $source;

            settings_save('eto_booking.sources', json_encode($sourceList), 'subscription', get_subscription_id(), true);
        }
    }

    public function manageSources(Request $request)
    {
        $siteId = !empty($request->get('siteId')) ? (int)$request->get('siteId') : config('site.site_id');
        // dd($siteId);

        switch ($request->get('action')) {
            case 'save':

                $requestList = (array)$request->get('sourceList') ?: [];
                $sourceList = [];
                foreach ($requestList as $k => $v) {
                    $v = trim($v);
                    if ($v != '') {
                        $sourceList[] = $v;
                    }
                }
                sort($sourceList);
                $sourceList = json_encode($sourceList);

                settings_save('eto_booking.sources', $sourceList, 'subscription', get_subscription_id(), true);

                return [
                    'success' => true,
                    'message' => 'Saved'
                ];

            break;
            default:

                return [
                    'sourceList' => config('eto_booking.sources'),
                    'success' => true,
                    'message' => ''
                ];

            break;
        }
    }

    public function sendNotitications() {
        $notifications = [];
        $post = request()->all();

        $booking = \App\Models\BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            },
            'bookingTransactions' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->find($post['bookingId']);

        $i = 0;
        foreach($post['notifications'] as $status=>$rolesData) {
            $notifications[$i]['type'] = str_replace('booking_', '', $status);
            foreach($rolesData as $role=>$data) {
                $notifications[$i]['role'][$role] = $data;
            }
            $i++;
        }

        if (!empty($notifications)) {
            event(new \App\Events\BookingStatusChanged($booking, $notifications, false));
        }
    }

    public function markBookingRead($id) {
        $booking = \App\Models\BookingRoute::withTrashed()->findOrFail($id);
        $booking->is_read = request('is_read') ? 1 : 0;
        $booking->save();
    }
}
