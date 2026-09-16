<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlightController extends Controller
{
    public static function getSearchParams($fD, $type, $bFlightNumber = '', $bFlightDateTime = '')
    {
        $departureAirportCode = '';
        $arrivalAirportCode = '';
        $year = '';
        $month = '';
        $day = '';
        $hourOfDay = '';
        $minuteOfDay = '';
        $carrierFsCode = '';
        $flightNumber = '';
        $flightId = '';

        if ($bFlightNumber) {
            $temp = split_on(str_replace(' ', '', trim($bFlightNumber)), 2);
            $carrierFsCode = isset($temp[0]) ? $temp[0] : '';
            $flightNumber = isset($temp[1]) ? $temp[1] : '';
        }

        if (isset($fD->departureAirport->fs)) {
            $departureAirportCode = $fD->departureAirport->fs;
        }
        if (isset($fD->arrivalAirport->fs)) {
            $arrivalAirportCode = $fD->arrivalAirport->fs;
        }
        if (isset($fD->carrier->fs)) {
            $carrierFsCode = $fD->carrier->fs;
        }
        if (isset($fD->flightNumber)) {
            $flightNumber = $fD->flightNumber;
        }
        if (isset($fD->flightId)) {
            $flightId = $fD->flightId;
        }

        $flightDateTime = '';

        if ($bFlightDateTime) {
            $flightDateTime = $bFlightDateTime;
        }

        if ($type == 'arriving') {
            if (isset($fD->arrivalTime)) {
                $flightDateTime = $fD->arrivalTime;
            }
        }
        else {
            if (isset($fD->departureTime)) {
                $flightDateTime = $fD->departureTime;
            }
        }

        if ($flightDateTime) {
            $temp = \Carbon\Carbon::parse($flightDateTime);
            $year = (int)$temp->format('Y');
            $month = (int)$temp->format('m');
            $day = (int)$temp->format('d');
            $hourOfDay = (int)$temp->format('H');
            $minuteOfDay = (int)$temp->format('i');
        }

        return [
            'type' => $type,
            'departureAirportCode' => $departureAirportCode,
            'arrivalAirportCode' => $arrivalAirportCode,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hourOfDay' => $hourOfDay,
            'minuteOfDay' => $minuteOfDay,
            'carrierFsCode' => $carrierFsCode,
            'flightNumber' => $flightNumber,
            'flightId' => $flightId
        ];
    }

    public static function refreshFlightDetails()
    {
        $request = request();
        $type = $request->get('type');
        $id = $request->get('id');

        return self::updateFlightDetails($id, $type, true, true);
    }

    public static function updateFlightDetails($id = 0, $type = '', $html = false, $force = false)
    {
        $type = $type == 'pickup' ? 'arriving' : 'departing';
        $status = false;
        $error = '';
        $results = [];

        if (!config('eto.allow_flightstats') || !config('services.flightstats.enabled')) {
            return ['results' => $results, 'status' => $status, 'error' => 'Enable FlightStats service to use this option.'];
        }

        if ($id && $type) {
            $booking = \App\Models\BookingRoute::with([
                'booking' => function($query) {
                    $query->withTrashed();
                },
                'bookingParams'
            ])->withTrashed()->find($id);

            if (!empty($booking->id)) {
                $key = 'flight_details_'. ($type == 'arriving' ? 'pickup' : 'dropoff');

                if ($type == 'arriving') {
                    $bFlightNumber = trim($booking->flight_number);
                    $bFlightDateTime = trim($booking->date->format('Y-m-d') .' '. $booking->flight_landing_time);
                }
                else {
                    $bFlightNumber = trim($booking->departure_flight_number);
                    $bFlightDateTime = trim($booking->date->format('Y-m-d') .' '. $booking->departure_flight_time);
                }

                $param = $booking->bookingParams()->where('key', $key)->first();

                $fD = (object)[];
                $allow = true;
                $waitTime = $force ? 15 : 60 * 15; // seconds
                // $waitTime = 0; // seconds

                if ($param->value) {
                    $fD = is_object($param->value) ? $param->value : json_decode($param->value);
                }

                if (in_array($booking->status, ['completed', 'canceled'])) {
                    $allow = false;
                }

                if (
                    empty($bFlightNumber) || empty($bFlightDateTime) ||
                    (!empty($bFlightNumber) && !empty($fD->lastFlightNumber) && $fD->lastFlightNumber != $bFlightNumber) ||
                    (!empty($bFlightDateTime) && !empty($fD->lastFlightDateTime) && $fD->lastFlightDateTime != $bFlightDateTime)
                ) {
                    $fD = (object)[];
                    // \Log::debug('Reset');
                }

                // Schedule
                $searchParams = self::getSearchParams($fD, $type, $bFlightNumber, $bFlightDateTime);
                if ($allow && $searchParams['flightNumber'] && $searchParams['carrierFsCode'] && empty($fD->lastUpdateSchedule)) {
                    $response = \App\Http\Controllers\FlightController::searchSchedules($searchParams);

                    if (!empty($response['results']) && !empty($response['results']->flightNumber)) {
                        $r = $response['results'];
                        $fD->carrier = $r->carrier;
                        $fD->flightNumber = $r->flightNumber;
                        $fD->departureAirport = $r->departureAirport;
                        $fD->arrivalAirport = $r->arrivalAirport;
                        $fD->departureTime = $r->departureTime;
                        $fD->arrivalTime = $r->arrivalTime;
                        $fD->departureTerminal = isset($r->departureTerminal) ? $r->departureTerminal : '';
                        $fD->arrivalTerminal = isset($r->arrivalTerminal) ? $r->arrivalTerminal : '';
                    }

                    $fD->lastUpdateSchedule = time();
                    // \Log::debug('Schedule');
                }

                // Status
                $searchParams = self::getSearchParams($fD, $type, $bFlightNumber, $bFlightDateTime);

                if ($allow && $searchParams['flightNumber'] && $searchParams['carrierFsCode'] && (empty($fD->lastUpdate) || (int)$fD->lastUpdate + $waitTime <= time())) {
                    $response = \App\Http\Controllers\FlightController::searchFlightStatus($searchParams);

                    if (!empty($response['results']) && !empty($response['results']->flightNumber)) {
                        $r = $response['results'];
                        $fD->flightId = $r->flightId;
                        $fD->carrier = $r->carrier;
                        $fD->flightNumber = $r->flightNumber;
                        $fD->departureAirport = $r->departureAirport;
                        $fD->arrivalAirport = $r->arrivalAirport;
                        $fD->departureDate = $r->departureDate;
                        $fD->arrivalDate = $r->arrivalDate;
                        $fD->status = $r->status;
                        $fD->operationalTimes = $r->operationalTimes;
                        $fD->delays = $r->delays;
                        $fD->flightDurations = $r->flightDurations;
                        $fD->airportResources = $r->airportResources;
                    }

                    $fD->lastUpdateStatus = time();
                    // \Log::debug('Status');
                }
                else {
                    $error = 'No updates are available';
                }

                $fD->lastUpdate = time();
                $fD->lastFlightNumber = $bFlightNumber;
                $fD->lastFlightDateTime = $bFlightDateTime;

                if (empty($param->id)) {
                    $param = new \App\Models\BookingParam();
                    $param->booking_id = $booking->id;
                    $param->key = $key;
                }
                $param->value = json_encode($fD);
                $param->save();

                $status = true;

                if ($html == true && !empty($fD->flightNumber)) {
                    $results = self::getFlightTable($fD, $id, ($type == 'arriving' ? 'pickup' : 'dropoff'));
                }
            }
        }

        return ['results' => $results, 'status' => $status, 'error' => $error];
    }

    public static function searchFlightStatus($search = [])
    {
        $request = request();
        $status = false;
        $error = '';
        $results = [];

        if (!config('eto.allow_flightstats') || !config('services.flightstats.enabled')) {
            return ['results' => $results, 'status' => $status, 'error' => 'Enable FlightStats service to use this option.'];
        }

        $params = [
            'appId' => config('services.flightstats.app_id'),
            'appKey' => config('services.flightstats.app_key'),
            'extendedOptions' => 'useInlinedReferences',
        ];

        $search = (object)$search;
        $type = $search->type != '' ? $search->type : $request->get('type', '');
        $departureAirportCode = $search->departureAirportCode != '' ? $search->departureAirportCode : $request->get('departureAirportCode', '');
        $arrivalAirportCode = $search->arrivalAirportCode != '' ? $search->arrivalAirportCode : $request->get('arrivalAirportCode', '');
        $year = $search->year != '' ? $search->year : $request->get('year', '');
        $month = $search->month != '' ? $search->month : $request->get('month', '');
        $day = $search->day != '' ? $search->day : $request->get('day', '');
        $hourOfDay = $search->hourOfDay != '' ? $search->hourOfDay : $request->get('hourOfDay', '');
        $minuteOfDay = $search->minuteOfDay != '' ? $search->minuteOfDay : $request->get('minuteOfDay', '');
        $carrierFsCode = $search->carrierFsCode != '' ? $search->carrierFsCode : $request->get('carrierFsCode', '');
        $flightNumber = $search->flightNumber != '' ? $search->flightNumber : $request->get('flightNumber', '');
        $flightId = $search->flightId != '' ? $search->flightId : $request->get('flightId', '');

        if ($flightId) {
            $url = 'flight/status/'. $flightId;
        }
        elseif ($carrierFsCode && $flightNumber) {
            if ($type == 'arriving') {
                $url = 'flight/status/'. $carrierFsCode .'/'. $flightNumber .'/arr/'. $year .'/'. $month .'/'. $day;
            }
            else {
                $url = 'flight/status/'. $carrierFsCode .'/'. $flightNumber .'/dep/'. $year .'/'. $month .'/'. $day;
            }
        }

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://api.flightstats.com/flex/flightstatus/rest/v2/json/'. $url .'?'. http_build_query($params), [
                'headers' => [
                    'accept' => 'application/json',
                    'accept-encoding' => 'gzip, deflate',
                    'content-type' => 'application/json'
                ]
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response)) {
                if (!empty($response->error)) {
                    $error = $response->error->errorMessage .' ('. $response->error->httpStatusCode .')';
                }

                $selected = [];

                if ($flightId && !empty($response->flightStatus)) {
                    $list = [$response->flightStatus];
                }
                elseif (!empty($response->flightStatuses)) {
                    $list = $response->flightStatuses;
                }
                else {
                    $list = [];
                }

                if (!empty($list)) {
                    foreach ($list as $k => $v) {
                        $allow = false;

                        if ($v->departureAirport->fs == $departureAirportCode && $v->arrivalAirport->fs == $arrivalAirportCode) {
                            $allow = true;
                        }
                        elseif ($hourOfDay != '' && !$flightId) {
                            $fDateTime = \Carbon\Carbon::parse($type == 'arriving' ? $v->arrivalDate->dateLocal : $v->departureDate->dateLocal)->format('Y-m-d H');
                            $sDateTime = \Carbon\Carbon::parse($year .'-'. $month .'-'. $day .' '. $hourOfDay .':00')->format('Y-m-d H');

                            if ($fDateTime == $sDateTime) {
                                $allow = true;
                            }
                        }

                        // if ($minuteOfDay != '' && !$carrierFsCode && !$flightNumber && !$flightId) {
                        //     $fDateTime = \Carbon\Carbon::parse($type == 'arriving' ? $v->arrivalTime : $v->departureTime)->format('Y-m-d H:i');
                        //     $sDateTime = \Carbon\Carbon::parse($year .'-'. $month .'-'. $day .' '. $hourOfDay .':'. $minuteOfDay)->format('Y-m-d H:i');
                        //
                        //     if ($fDateTime == $sDateTime) {
                        //         $allow = true;
                        //     }
                        // }

                        if ($allow) {
                            $selected[] = $v;
                        }
                    }

                    $status = true;
                }

                $results = !empty($selected[0]) ? (object)$selected[0] : (object)[];
                // dd($results, $selected, $response);
                // \Log::debug(json_encode($results));
            }
        }
        catch (\Exception $e) {
            $error = 'FlightController::searchFlightStatus: '. $e->getMessage();
            \Log::error($error);
        }

        return ['results' => $results, 'status' => $status, 'error' => $error];
    }

    public static function searchSchedules($search = [])
    {
        $request = request();
        $status = false;
        $error = '';
        $results = [];

        if (!config('eto.allow_flightstats') || !config('services.flightstats.enabled')) {
            return ['results' => $results, 'status' => $status, 'error' => 'Enable FlightStats service to use this option.'];
        }

        $params = [
            'appId' => config('services.flightstats.app_id'),
            'appKey' => config('services.flightstats.app_key'),
            'extendedOptions' => 'useInlinedReferences',
        ];

        $search = (object)$search;
        $type = $search->type != '' ? $search->type : $request->get('type', '');
        $departureAirportCode = $search->departureAirportCode != '' ? $search->departureAirportCode : $request->get('departureAirportCode', '');
        $arrivalAirportCode = $search->arrivalAirportCode != '' ? $search->arrivalAirportCode : $request->get('arrivalAirportCode', '');
        $year = $search->year != '' ? $search->year : $request->get('year', '');
        $month = $search->month != '' ? $search->month : $request->get('month', '');
        $day = $search->day != '' ? $search->day : $request->get('day', '');
        $hourOfDay = $search->hourOfDay != '' ? $search->hourOfDay : $request->get('hourOfDay', '');
        $minuteOfDay = $search->minuteOfDay != '' ? $search->minuteOfDay : $request->get('minuteOfDay', '');
        $carrierFsCode = $search->carrierFsCode != '' ? $search->carrierFsCode : $request->get('carrierFsCode', '');
        $flightNumber = $search->flightNumber != '' ? $search->flightNumber : $request->get('flightNumber', '');

        if ($carrierFsCode && $flightNumber) {
            if ($type == 'arriving') {
                $url = 'flight/'. $carrierFsCode .'/'. $flightNumber .'/arriving/'. $year .'/'. $month .'/'. $day;
            }
            else {
                $url = 'flight/'. $carrierFsCode .'/'. $flightNumber .'/departing/'. $year .'/'. $month .'/'. $day;
            }
        }
        elseif ($departureAirportCode && $arrivalAirportCode) {
            if ($type == 'arriving') {
                $url = 'from/'. $departureAirportCode .'/to/'. $arrivalAirportCode .'/arriving/'. $year .'/'. $month .'/'. $day;
            }
            else {
                $url = 'from/'. $departureAirportCode .'/to/'. $arrivalAirportCode .'/departing/'. $year .'/'. $month .'/'. $day;
            }
        }
        else {
            if ($type == 'arriving') {
                $url = 'from/'. $departureAirportCode .'/departing/'. $year .'/'. $month .'/'. $day .'/'. $hourOfDay;
            }
            else {
                $url = 'to/'. $arrivalAirportCode .'/arriving/'. $year .'/'. $month .'/'. $day .'/'. $hourOfDay;
            }
        }

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://api.flightstats.com/flex/schedules/rest/v1/json/'. $url .'?'. http_build_query($params), [
                'headers' => [
                    'accept' => 'application/json',
                    'accept-encoding' => 'gzip, deflate',
                    'content-type' => 'application/json'
                ]
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response)) {
                if (!empty($response->error)) {
                    $error = $response->error->errorMessage .' ('. $response->error->httpStatusCode .')';
                }

                $selected = [];

                if (!empty($response->scheduledFlights)) {
                    foreach ($response->scheduledFlights as $k => $v) {
                        $allow = false;

                        if ($v->departureAirport->fs == $departureAirportCode && $v->arrivalAirport->fs == $arrivalAirportCode) {
                            $allow = true;
                        }
                        elseif ($hourOfDay != '' && ( ($carrierFsCode && $flightNumber) || ($departureAirportCode && $arrivalAirportCode) )) {
                            $fDateTime = \Carbon\Carbon::parse($type == 'arriving' ? $v->arrivalTime : $v->departureTime)->format('Y-m-d H');
                            $sDateTime = \Carbon\Carbon::parse($year .'-'. $month .'-'. $day .' '. $hourOfDay .':00')->format('Y-m-d H');
                            if ($fDateTime == $sDateTime) {
                                $allow = true;
                            }
                        }
                        else {
                            $allow = true;
                        }

                        // if ($minuteOfDay != '' && !$carrierFsCode && !$flightNumber) {
                        //     $fDateTime = \Carbon\Carbon::parse($type == 'arriving' ? $v->arrivalTime : $v->departureTime)->format('Y-m-d H:i');
                        //     $sDateTime = \Carbon\Carbon::parse($year .'-'. $month .'-'. $day .' '. $hourOfDay .':'. $minuteOfDay)->format('Y-m-d H:i');
                        //
                        //     if ($fDateTime == $sDateTime) {
                        //         $allow = true;
                        //     }
                        // }

                        if ($allow) {
                            $selected[] = $v;
                        }
                    }

                    $status = true;
                }

                $results = !empty($selected[0]) ? (object)$selected[0] : (object)[];
                // dd($selected);
                // dd($results, $selected, $response);
            }
        }
        catch (\Exception $e) {
            $error = 'FlightController::searchSchedules: '. $e->getMessage();
            \Log::error($error);
        }

        return ['results' => $results, 'status' => $status, 'error' => $error];
    }

    public static function getFlightTable($data, $id = 0, $type = '')
    {
        $html = '';

        if (empty($data) || empty($data->flightNumber)) {
            return $html;
        }

        $statusCls = 'etoFSTicketContainerSuccess';
        $statusCode = !empty($data->status) ? $data->status : 'S';
        $statusName = 'Scheduled';
        $statusTime = 'On time';

        switch ($statusCode) {
            case 'A':
                $statusName = 'Departed'; // Active
            break;
            case 'C':
                $statusName = 'Canceled';
                $statusCls = 'etoFSTicketContainerDanger';
            break;
            case 'D':
                $statusName = 'Diverted';
                $statusCls = 'etoFSTicketContainerWarning';
            break;
            case 'DN':
                $statusName = 'Data source needed';
            break;
            case 'L':
                $statusName = 'Arrived'; // Landed
            break;
            case 'NO':
                $statusName = 'Not Operational';
                $statusCls = 'etoFSTicketContainerDanger';
            break;
            case 'R':
                $statusName = 'Redirected';
                $statusCls = 'etoFSTicketContainerWarning';
            break;
            case 'S':
                $statusName = 'Scheduled';
            break;
            case 'U':
                $statusName = 'Unknown';
                $statusCls = 'etoFSTicketContainerWarning';
            break;
            default:
                $statusName = 'Unknown';
                $statusCls = 'etoFSTicketContainerWarning';
            break;
        }

        if (!empty($data->delays) && $statusCode != 'L') {
            $dl = $data->delays;
            $delay = 0;

            if (!empty($dl->arrivalGateDelayMinutes)) {
                $delay = $dl->arrivalGateDelayMinutes;
            }
            elseif (!empty($dl->arrivalRunwayDelayMinutes)) {
                $delay = $dl->arrivalRunwayDelayMinutes;
            }
            elseif (!empty($dl->departureRunwayDelayMinutes)) {
                $delay = $dl->departureRunwayDelayMinutes;
            }
            elseif (!empty($dl->departureGateDelayMinutes)) {
                $delay = $dl->departureGateDelayMinutes;
            }

            if ($delay) {
                $statusTime = 'Delayed by '. $delay .' min';
                $statusCls = 'etoFSTicketContainerWarning';
            }
        }

        $departureDateTime = !empty($data->departureDate) && !empty($data->departureDate->dateLocal) ? $data->departureDate->dateLocal : $data->departureTime;
        $departureDateTime = \Carbon\Carbon::parse($departureDateTime, $data->departureAirport->timeZoneRegionName);
        $depDate = \App\Helpers\SiteHelper::formatDateTime($departureDateTime, 'date');
        $depTime = \App\Helpers\SiteHelper::formatDateTime($departureDateTime, 'time');
        $depZone = $departureDateTime->format('T');
        $depTitle = 'Scheduled';
        $depTime2 = '--';
        $depZone2 = '';
        $depTitle2 = 'Scheduled';
        $depTerminal = !empty($data->departureTerminal) ? $data->departureTerminal : 'N/A';
        $depGate = !empty($data->departureGate) ? $data->departureGate : 'N/A';

        if (!empty($data->airportResources)) {
            $aR = $data->airportResources;

            if (!empty($aR->departureTerminal)) {
                $depTerminal = $aR->departureTerminal;
            }

            if (!empty($aR->departureGate)) {
                $depGate = $aR->departureGate;
            }
        }

        if (!empty($data->operationalTimes)) {
            $oT = $data->operationalTimes;
            $dateTime = null;

            if (!empty($oT->actualGateDeparture)) {
                $depTitle2 = 'Actual';
                $dateTime = $oT->actualGateDeparture->dateLocal;
            }
            elseif (!empty($oT->estimatedGateDeparture)) {
                $depTitle2 = 'Estimated';
                $dateTime = $oT->estimatedGateDeparture->dateLocal;
            }
            elseif (!empty($oT->scheduledGateDeparture)) {
                $depTitle2 = 'Scheduled';
                $dateTime = $oT->scheduledGateDeparture->dateLocal;
            }
            elseif (!empty($oT->publishedDeparture)) {
                $depTitle2 = 'Published';
                $dateTime = $oT->publishedDeparture->dateLocal;
            }

            if ($dateTime) {
                $dateTime = \Carbon\Carbon::parse($dateTime, $data->departureAirport->timeZoneRegionName);
                $depTime2 = \App\Helpers\SiteHelper::formatDateTime($dateTime, 'time');
                $depZone2 = $dateTime->format('T');
            }
        }

        $arrivalDateTime = !empty($data->arrivalDate) && !empty($data->arrivalDate->dateLocal) ? $data->arrivalDate->dateLocal : $data->arrivalTime;
        $arrivalDateTime = \Carbon\Carbon::parse($arrivalDateTime, $data->arrivalAirport->timeZoneRegionName);
        $arrDate = \App\Helpers\SiteHelper::formatDateTime($arrivalDateTime, 'date');
        $arrTime = \App\Helpers\SiteHelper::formatDateTime($arrivalDateTime, 'time');
        $arrZone = $arrivalDateTime->format('T');
        $arrTitle = 'Scheduled';
        $arrTime2 = '--';
        $arrZone2 = '';
        $arrTitle2 = 'Scheduled';
        $arrTerminal = !empty($data->arrivalTerminal) ? $data->arrivalTerminal : 'N/A';
        $arrGate = !empty($data->arrivalGate) ? $data->arrivalGate : 'N/A';
        $arrBaggage = !empty($data->baggage) ? $data->baggage : 'N/A';

        if (!empty($data->airportResources)) {
            $aR = $data->airportResources;

            if (!empty($aR->arrivalTerminal)) {
                $arrTerminal = $aR->arrivalTerminal;
            }
            if (!empty($aR->arrivalGate)) {
                $arrGate = $aR->arrivalGate;
            }
        }

        if (!empty($data->operationalTimes)) {
            $oT = $data->operationalTimes;
            $dateTime = null;

            if (!empty($oT->actualGateArrival)) {
                $arrTitle2 = 'Actual';
                $dateTime = $oT->actualGateArrival->dateLocal;
            }
            elseif (!empty($oT->estimatedGateArrival)) {
                $arrTitle2 = 'Estimated';
                $dateTime = $oT->estimatedGateArrival->dateLocal;
            }
            elseif (!empty($oT->scheduledGateArrival)) {
                $arrTitle2 = 'Scheduled';
                $dateTime = $oT->scheduledGateArrival->dateLocal;
            }
            elseif (!empty($oT->publishedArrival)) {
                $arrTitle2 = 'Published';
                $dateTime = $oT->publishedArrival->dateLocal;
            }

            if ($dateTime) {
                $dateTime = \Carbon\Carbon::parse($dateTime, $data->arrivalAirport->timeZoneRegionName);
                $arrTime2 = \App\Helpers\SiteHelper::formatDateTime($dateTime, 'time');
                $arrZone2 = $dateTime->format('T');
            }
        }

        $lastUpdateID = md5(microtime() . rand(1000, 10000));
        $lastUpdate = !empty($data->lastUpdate) ? \App\Helpers\SiteHelper::formatDateTime(\Carbon\Carbon::createFromTimestamp((int)$data->lastUpdate)->toDateTimeString()) : 'N/A';

        if (!in_array($statusCode, ['L', 'C'])) {
            $refreshCode = '
              <div class="etoFSUpdateLoader hidden"><i class="fa fa-refresh fa-spin"></i></div>
              <a href="#" onclick="return false;" title="Click here to update flight details" class="etoFSUpdateBtn etoFSUpdateBtn-'. $lastUpdateID .'"><i class="fa fa-refresh"></i> Last updated on '. $lastUpdate .'</a>
              <script>
              function refreshFlightDetails(el) {
                var btn = $(el);
                var container = btn.closest(\'.etoFSTicketContainer\');
                // btn.find(\'i.fa-refresh\').addClass(\'fa-spin\');
                container.find(\'.etoFSUpdateLoader\').removeClass(\'hidden\');

                $.ajax({
                  headers : {
                    \'X-CSRF-TOKEN\': EasyTaxiOffice.csrfToken
                  },
                  url: EasyTaxiOffice.appPath +\'/refreshFlightDetails\',
                  type: \'GET\',
                  dataType: \'json\',
                  data: {
                    type: \''. $type .'\',
                    id: \''. $id .'\'
                  },
                  cache: false,
                  success: function(response) {
                    // btn.find(\'i.fa-refresh\').removeClass(\'fa-spin\');
                    container.find(\'.etoFSUpdateLoader\').addClass(\'hidden\');

                    if (response.status == true) {
                      $(\'.etoFSUpdateBtn-'. $lastUpdateID .'\').off();
                      clearInterval(interVal'. $lastUpdateID .');

                      container.hide();
                      container.after(response.results);
                      container.remove();
                      // alert(\'Flight details successfully updated.\');
                    } else {
                      if (response.error) {
                        alert(response.error);
                      } else {
                        // alert(\'Flight details update failed.\');
                      }
                    }
                  },
                  error: function(response) {
                    alert(\'Flight details update error.\');
                  },
                });
              }

              var interVal'. $lastUpdateID .' = setInterval(function() {
                refreshFlightDetails($(\'.etoFSUpdateBtn-'. $lastUpdateID .'\'));
              }, 1000 * 60 * 15);

              $(document).ready(function(){
                $(\'.etoFSUpdateBtn-'. $lastUpdateID .'\').on(\'click\', function (e) {
                    e.preventDefault();
                    refreshFlightDetails(this);
                });
              });
              </script>';
        }
        else {
            $refreshCode = '<a href="#" onclick="return false;" class="etoFSUpdateBtn etoFSUpdateBtn-'. $lastUpdateID .'"><i class="fa fa-refresh"></i> Last updated on '. $lastUpdate .'</a>';
        }

        $html = '<div class="etoFSTicketContainer '. $statusCls .'">
          <div class="etoFSHeader clearfix">
            <div class="etoFSFlightNumberContainer">
              <div class="etoFSTextBig">'. $data->carrier->fs .' '. $data->flightNumber .'</div>
              <div>'. $data->carrier->name .'</div>
            </div>
            <div class="etoFSStatusContainer clearfix">
              <div class="etoFSTextBig">'. $statusName .'</div>
              <div>'. $statusTime .'</div>
            </div>
            <div class="etoFSRouteGroup clearfix">
              <div class="etoFSRoute">
                <div class="etoFSTextBig">'. $data->departureAirport->fs .'</div>
                <div>'. $data->departureAirport->city .'</div>
              </div>
              <div class="etoFSRoute etoFSRouteImg">
                <svg width="35px" height="32px" viewBox="0 0 230 207.2" xmlns="http://www.w3.org/2000/svg">
                  <path d="M69.9 202.8s-1.2.3-2.4.3-3.9-.4-3.2-.4c.6 0 1.4-1.2 1.4-1.2l-1-.1v-.9h1.7L78 172.8s1.5 0 1.5-.1c-1.5 0-2.4.1-2.5-.1.1-.3 12.6-30.6 12.9-31.3.1-.3 1.4-.1 1.4-.1l1.1-7.8h-1s2.1-16.4 1.9-16.9c-.3-.4-16.7.4-27.8-.4s-17.1-2.1-19.2-2.7c-2-.5-2.4-.1-3.3 1-.8 1-15.2 22-16.4 23.7s-1.8 1.8-2.5 1.8h-7.3l6.5-29.2s-2.7-.1-3.1-.6c-.4-.4-.2-1.3-.2-1.3s-5.6-.6-5.9-.9c-.4-.3-.5-.9-.5-.9s-5.7-1.3-5.9-1.9c.1-.6 5.9-1.9 5.9-1.9s.2-.6.5-.9c.4-.3 5.9-.9 5.9-.9s-.1-.9.2-1.3 3.1-.6 3.1-.6l-6.6-29.2H24c.7 0 1.3 0 2.5 1.8 1.3 1.8 15.6 22.7 16.4 23.7s1.3 1.5 3.3 1c2.1-.6 8.1-1.9 19.2-2.7s27.3-.1 27.6-.6c.3-.4-1.9-16.9-1.9-16.9h1L91 68.8s-1.3.1-1.4-.1c-.3-.7-12.8-31-12.9-31.3.1-.2 1.1-.1 2.5-.1 0-.1-1.5-.1-1.5-.1L66.2 9.4h-1.7v-.9l1-.1s-.7-1.2-1.4-1.2c-.6 0 2.1-.4 3.2-.4 1.3 0 2.4.3 2.4.3s-.3-1 .9-.8c.7.1 4.1 2.5 4.1 2.5s.5.2.9.1c.4 0 .7.5.9.8.1.2.9 0 .9 0s7.3 9.2 18.7 25.1c11.4 15.8 25.3 34.6 25.3 34.6s.8 0 1.9-.2 1.6-.3 1.6-.3.3-1 1-1.2c.5-.2 4.5-.7 8-.6s5.4.3 6 .6.8 1.1 1 2.3c.1 1.2.1 5.7-.1 6.8-.1 1-.3 1.7-1.6 1.9-1.4.2-6.1.2-8 .1-1.4-.1-2.9-.5-3-.3s3.3 4 4.7 6.1 4.2 5.7 5.1 6.1 1 .5.2.7c-.8.3-1.6.2-2.2-.1-.6-.4-1.3-.5-1.6-.2-.1.1.7 1.2 1.2 1.4.5.3 1.4 0 1.8.2s1.6.9 2.5.8c5.7-.2 14.4-.4 33.2-.4 20.1-.1 26.2.1 36.9 2.9 9.8 2.5 11.7 4.4 13.1 5.3 1.3.9 2.3 2.9 2.3 3.7 0 .8-1 2.8-2.3 3.7-1.4 1-3.3 2.8-13.1 5.3-10.8 2.7-16.8 2.9-36.9 2.9-18.8-.1-27.4-.3-33.2-.4-1 0-2.1.7-2.5.8-.4.2-1.3-.1-1.8.2s-1.3 1.3-1.2 1.4c.3.4 1 .2 1.6-.2s1.4-.4 2.2-.1.7.3-.2.7-3.8 4-5.1 6.1c-1.4 2.1-4.8 5.9-4.7 6.1s1.6-.2 3-.3c1.9-.1 6.6-.1 8 .1s1.5.8 1.6 1.9.2 5.7.1 6.8-.4 2-1 2.3-2.4.5-6 .6c-3.5.1-7.5-.4-8-.6-.7-.3-1-1.2-1-1.2s-.5-.1-1.6-.3-1.9-.2-1.9-.2-14 18.8-25.3 34.6c-11.4 15.9-18.7 25.1-18.7 25.1s-.7-.2-.9 0c-.2.3-.5.8-.9.8s-.9.1-.9.1-3.3 2.4-4.1 2.5c-1 .1-.7-.9-.7-.9zm8.5-190L77 10.9l-.2.1 1.3 1.8zm2.6 3.7L78.5 13l-.2.2 2.4 3.5zm2.6 3.7l-2.5-3.5-.2.1 2.4 3.5zm3.3 4.6l-3.2-4.4-.2.1 3.2 4.5zm3 4.3L87.1 25l-.2.1 2.9 4.1zm3.1 4.4l-2.9-4.1-.2.1 2.9 4.1zm3.2 4.4l-3-4.3-.2.2 3 4.3zm3.2 4.6l-3.1-4.3-.2.2 3 4.3zm3.2 4.5l-3-4.3-.2.1 3 4.3zm3.1 4.4l-3-4.3-.2.1 3 4.3zm3.1 4.3l-2.9-4.1-.2.1 2.9 4.1zm3 4.2l-2.9-4.1-.2.1 2.9 4.1zm3.6 5.1l-3.4-4.8-.2.1 3.4 4.8zm.9 1.3l-.8-1.1-.2.1.8 1.1zm3 4.1l-2.8-3.9-.2.1 2.8 4zm0 69.1l-.2-.1-2.8 4 .2.1zm-3 4.1l-.2-.1-.8 1.1.2.1zm-.9 1.4l-.2-.1-3.4 4.8.2.1zm-3.6 5l-.2-.1-2.9 4.1.2.1zm-3 4.3l-.2-.1-2.9 4.1.2.1zm-3.1 4.2l-.2-.1-3 4.3.2.1zm-3.1 4.5l-.2-.1-3 4.2.2.1zm-3.2 4.5l-.2-.1-3 4.3.2.2zm-3.2 4.5l-.2-.2-3 4.3.2.1zm-3.2 4.5l-.2-.1-2.9 4.1.2.1zm-3.1 4.3l-.2-.1-2.9 4.1.2.1zm-3 4.3l-.2-.1-3.2 4.5.2.1zm-3.3 4.7l-.2-.1-2.4 3.5.2.1zm-2.6 3.7l-.2-.1-2.4 3.5.2.2zm-2.6 3.6l-.2-.2-1.3 1.8.1.3z"
                    fill="rgba(0, 0, 0, 0.2)"></path>
                </svg>
              </div>
              <div class="etoFSRoute">
                <div class="etoFSTextBig">'. $data->arrivalAirport->fs .'</div>
                <div>'. $data->arrivalAirport->city .'</div>
              </div>
            </div>
          </div>
          <div class="etoFSTicketContent clearfix">
            <div class="etoFSTicketCard">
              <div class="etoFSSection">
                <div>'. $data->departureAirport->city .', '. $data->departureAirport->countryCode .'</div>
                <div>'. $data->departureAirport->name .'</div>
              </div>
              <div class="etoFSSection">
                <div>Flight Departure Times</div>
                <div class="etoFSTextBig">'. $depDate .'</div>
              </div>
              <div class="etoFSTimeGroupContainer clearfix">
                <div class="etoFSSection">
                  <div>'. $depTitle .'</div>
                  <div>
                    <span class="etoFSTextBig">'. $depTime .'</span>
                    <span class="etoFSTimeSuffix"> '. $depZone .'</span>
                  </div>
                </div>
                <div class="etoFSSection">
                  <div>'. $depTitle2 .'</div>
                  <div>
                    <span class="etoFSTextBig">'. $depTime2 .'</span>
                    <span class="etoFSTimeSuffix"> '. $depZone2 .'</span>
                  </div>
                </div>
              </div>
              <div class="etoFSTerminalGateContainer clearfix">
                <div class="etoFSSection">
                  <div>Terminal</div>
                  <div class="etoFSTextBig">'. $depTerminal .'</div>
                </div>
                <div class="etoFSSection">
                  <div>Gate</div>
                  <div class="etoFSTextBig">'. $depGate .'</div>
                </div>
              </div>
            </div>
            <div class="etoFSTicketCard">
              <div class="etoFSSection">
                <div>'. $data->arrivalAirport->city .', '. $data->arrivalAirport->countryCode .'</div>
                <div>'. $data->arrivalAirport->name .'</div>
              </div>
              <div class="etoFSSection">
                <div>Flight Arrival Times</div>
                <div class="etoFSTextBig">'. $arrDate .'</div>
              </div>
              <div class="etoFSTimeGroupContainer clearfix">
                <div class="etoFSSection">
                  <div>'. $arrTitle .'</div>
                  <div>
                    <span class="etoFSTextBig">'. $arrTime .'</span>
                    <span class="etoFSTimeSuffix"> '. $arrZone .'</span>
                  </div>
                </div>
                <div class="etoFSSection">
                  <div>'. $arrTitle2 .'</div>
                  <div>
                    <span class="etoFSTextBig">'. $arrTime2 .'</span>
                    <span class="etoFSTimeSuffix"> '. $arrZone2 .'</span>
                  </div>
                </div>
              </div>
              <div class="etoFSTerminalGateContainer clearfix">
                <div class="etoFSSection">
                  <div>Terminal</div>
                  <div class="etoFSTextBig">'. $arrTerminal .'</div>
                </div>
                <div class="etoFSSection">
                  <div>Gate</div>
                  <div class="etoFSTextBig">'. $arrGate .'</div>
                </div>
              </div>
            </div>
          </div>
          '. $refreshCode .'
        </div>';

        $html .= "";

        return $html;
    }

    public function searchAirports(Request $request)
    {
        $keyword = trim($request->get('keyword', ''));
        $results = [];
        if (config('eto.allow_flightstats') && config('services.flightstats.enabled') && strlen($keyword) >= 3) {
            $results = \App\Models\FlightAirport::where('name', 'like', '%'. $keyword .'%')
              ->orWhere('city', 'like', '%'. $keyword .'%')
              ->orWhere('country_name', 'like', '%'. $keyword .'%')
              ->orWhere('region_name', 'like', '%'. $keyword .'%')
              ->get();
              dd($results);
        }

        return $results;
    }

    public function searchAirlines(Request $request)
    {
        $keyword = trim($request->get('keyword', ''));
        $results = [];

        if (config('eto.allow_flightstats') && config('services.flightstats.enabled') && strlen($keyword) >= 3) {
            $results = \App\Models\FlightAirline::where('name', 'like', '%'. $keyword .'%')->get();
        }

        return $results;
    }

    public function updateAirports(Request $request)
    {
        $status = false;
        $error = '';

        if (!config('eto.allow_flightstats') || !config('services.flightstats.enabled')) {
            return ['status' => $status, 'error' => 'Enable FlightStats service to use this option.'];
        }

        $params = [
            'appId' => config('services.flightstats.app_id'),
            'appKey' => config('services.flightstats.app_key'),
        ];

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://api.flightstats.com/flex/airports/rest/v1/json/active?'. http_build_query($params), [
                'headers' => [
                    'accept' => 'application/json',
                    'accept-encoding' => 'gzip, deflate',
                    'content-type' => 'application/json'
                ],
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response)) {
                if (!empty($response->error)) {
                    $error = $response->error->errorMessage .' ('. $response->error->httpStatusCode .')';
                }

                if (!empty($response->airports)) {
                    $inserts = [];
                    $group = 0;
                    $index = 0;
                    $max = 500;

                    foreach ($response->airports as $k => $v) {
                        if ($index == $max) {
                            $group++;
                            $index = 0;
                        }
                        $index++;

                        $inserts[$group][] = [
                            'fs' => !empty($v->fs) ? $v->fs : null,
                            'iata' => !empty($v->iata) ? $v->iata : null,
                            'icao' => !empty($v->icao) ? $v->icao : null,
                            'faa' => !empty($v->faa) ? $v->faa : null,
                            'name' => !empty($v->name) ? $v->name : null,
                            'city' => !empty($v->city) ? $v->city : null,
                            'state_code' => !empty($v->stateCode) ? $v->stateCode : null,
                            'country_code' => !empty($v->countryCode) ? $v->countryCode : null,
                            'country_name' => !empty($v->countryName) ? $v->countryName : null,
                            'region_name' => !empty($v->regionName) ? $v->regionName : null,
                            'lat' => !empty($v->latitude) ? $v->latitude : null,
                            'lng' => !empty($v->longitude) ? $v->longitude : null,
                            'active' => !empty($v->active) ? 1 : 0,
                        ];
                    }

                    \DB::table('flight_airports')->delete();

                    foreach ($inserts as $k => $v) {
                        \DB::table('flight_airports')->insert($v);
                    }

                    $status = true;
                }
            }
        }
        catch (\Exception $e) {
            $error = 'FlightController::updateAirports: '. $e->getMessage();
            \Log::error($error);
        }

        return ['status' => $status, 'error' => $error];
    }

    public function updateAirlines(Request $request)
    {
        $status = false;
        $error = '';

        if (!config('eto.allow_flightstats') || !config('services.flightstats.enabled')) {
            return ['status' => $status, 'error' => 'Enable FlightStats service to use this option.'];
        }

        $params = [
            'appId' => config('services.flightstats.app_id'),
            'appKey' => config('services.flightstats.app_key'),
        ];

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://api.flightstats.com/flex/airlines/rest/v1/json/active?'. http_build_query($params), [
                'headers' => [
                    'accept' => 'application/json',
                    'accept-encoding' => 'gzip, deflate',
                    'content-type' => 'application/json'
                ],
            ]);

            $response = json_decode($response->getBody());

            if (!empty($response)) {
                if (!empty($response->error)) {
                    $error = $response->error->errorMessage .' ('. $response->error->httpStatusCode .')';
                }

                if (!empty($response->airlines)) {
                    $inserts = [];
                    $group = 0;
                    $index = 0;
                    $max = 500;

                    foreach ($response->airlines as $k => $v) {
                        if ($index == $max) {
                            $group++;
                            $index = 0;
                        }
                        $index++;

                        $inserts[$group][] = [
                            'fs' => !empty($v->fs) ? $v->fs : null,
                            'iata' => !empty($v->iata) ? $v->iata : null,
                            'icao' => !empty($v->icao) ? $v->icao : null,
                            'name' => !empty($v->name) ? $v->name : null,
                            'active' => !empty($v->active) ? 1 : 0,
                        ];
                    }

                    \DB::table('flight_airlines')->delete();

                    foreach ($inserts as $k => $v) {
                        \DB::table('flight_airlines')->insert($v);
                    }

                    $status = true;
                }
            }
        }
        catch (\Exception $e) {
            $error = 'FlightController::updateAirlines: '. $e->getMessage();
            \Log::error($error);
        }

        return ['status' => $status, 'error' => $error];
    }
}
