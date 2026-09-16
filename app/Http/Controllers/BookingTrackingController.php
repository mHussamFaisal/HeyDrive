<?php

namespace App\Http\Controllers;

use App\Models\BookingRoute;
use App\Models\BookingDriverTracking;
use Illuminate\Http\Request;

class BookingTrackingController extends Controller
{
    public function show(Request $request, $id, $timestamp = false)
    {
        $response = (object)['status'=>false];

        if ($request->ajax()) {
            $booking = BookingRoute::with([
                'booking' => function($query) {
                    $query->withTrashed();
                }
            ])->withTrashed()->findOrFail($id);

            $trackedStatuses = array_merge($booking->tracking_statuses, $booking->untracking_statuses);

            if ($booking && ((auth()->check()
                && (auth()->user()->role = 'admin'
                    || (auth()->user()->role = 'driver' && (int)auth()->user()->id === (int)$booking->driver_id)
                ))
                || (int)session('etoUserId') === (int)$booking->booking->user_id)
            ) {
                if ($request->type) {
                    $typeUser = $request->type;
                }
                else {
                    $typeUser = !empty(auth()->user()->role) ? auth()->user()->role : 'customer';
                }

                $userStatusesVar = $typeUser . '_statuses';
                $response->status = true;
                $response->new = false;
                $setTimestamp = $timestamp !== false;

                if (!$timestamp) {
                    $response->origin = $booking->address_start;
                    $response->destination = $booking->address_end;

                    if ((int)$booking->coordinate_start_lat !== 0
                        && (int)$booking->coordinate_start_lon !== 0
                    ) {
                        $response->origin = (object)[
                            'lat' => (float)$booking->coordinate_start_lat,
                            'lng' => (float)$booking->coordinate_start_lon,
                        ];
                    }

                    if ((int)$booking->coordinate_end_lat !== 0
                        && (int)$booking->coordinate_end_lon !== 0
                    ) {
                        $response->destination = (object)[
                            'lat' => (float)$booking->coordinate_end_lat,
                            'lng' => (float)$booking->coordinate_end_lon,
                        ];
                    }

                    $waypoints = json_decode($booking->waypoints);

                    if (!empty($waypoints)) {
                        $response->waypoints = $waypoints;
                    }
                }

                if (!empty(auth()->user()->role) && auth()->user()->role == 'admin') {
                    $response->statuses = $booking->getDriverStatuses(false, $timestamp);
                }
                else {
                    $response->statuses = $booking->getDriverStatuses($booking->driver_id, $timestamp);
                }

                if ((int)session('etoUserId') === (int)$booking->booking->user_id) {
                    $setTimestamp = false;

                    foreach($response->statuses as $time=>$statuses) {
                        if (!empty($statuses['status'])) {
                            if (!in_array($statuses['status'], (array)$booking->$userStatusesVar)) {
                                unset($response->statuses[$time]);
                            }
                            else {
                                if (!$setTimestamp) {
                                    $timestamp = $time;
                                    $setTimestamp = true;
                                }
                            }
                        }
                        else {
                            foreach ($statuses as $time2 => $status) {
                                if (!in_array($status['status'], (array)$booking->$userStatusesVar)) {
                                    unset($response->statuses[$time][$time2]);
                                }
                            }
                        }
                    }
                }

                if (in_array($booking->status, $trackedStatuses)
                    && ((int)session('etoUserId') !== (int)$booking->booking->user_id
                        || $setTimestamp)
                ) {
                    $response->coordinates = $booking->getTracking($booking->driver_id, $timestamp);
                        foreach ($response->coordinates as $k=>$v) {
                            $last = end($response->coordinates[$k]);
                            $response->lastCoordinateTime[$k] = $last->timestamp;

                            foreach ($response->coordinates[$k] as $idc=>$coor) {
                                $coor = (array)$coor;
                                unset($coor['timestamp']);
                                $response->coordinates[$k][$idc] = (object)$coor;
                            }

                            if (!empty(auth()->user()->role)
                                && auth()->user()->role != 'admin'
                                && (int)$k !== (int)auth()->user()->id
                            ) {
                                unset($response->coordinates[$k]);
                            }
                        }

                    if (count((array)$response->coordinates) > 0) {
                        $response->new = true;

                        if (in_array($booking->status, $booking->untracking_statuses)) {
                            $response->new = 'end';
                        }
                    }
                }

                if (!empty(auth()->user()->role) && auth()->user()->role == 'admin') {
                    $driverids = [];

                    foreach ($response->statuses as $status) {
                        $driverids[] = $status['user_id'];
                    }

                    $drivers = \App\Models\User::whereIn('id', $driverids)->get();

                    foreach ($drivers as $driver) {
                        $response->drivers[$driver->id] = $driver->getName(true);
                    }
                }

                return response()->json($response, 200);
            }
        }

        return response()->json($response, 404);
    }
}
