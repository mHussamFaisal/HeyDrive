<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookingDriver;
use Carbon\Carbon;

class DispatchDriverController extends Controller
{
    public static function check()
    {
        if (config('eto_dispatch.enable_autodispatch')) {
            self::expireRequest();
            self::availableBookings();
        }
    }

    public static function availableBookings($id = 0)
    {
        $bookings = \App\Models\BookingRoute::where('driver_id', 0)->where('date', '>=', Carbon::now());

        // Assign booking ahead of specific time
        if (config('eto_dispatch.time_to_assign')) {
            $bookings->where('date', '<=', Carbon::now()->addMinutes(config('eto_dispatch.time_to_assign')));
        }

        // Select only bookings with these status
        if (config('eto_dispatch.only_auto_dispatch_status')) {
            $bookings->whereIn('status', ['auto_dispatch']);
        }
        else {
            $bookings->whereIn('status', ['pending', 'assigned', 'auto_dispatch']);
        }

        // Specific booking
        if ($id) {
            $bookings->where('id', $id);
        }

        // Process bookings with closest journey date as first
        $bookings->orderBy('date', 'asc');

        $bookings = $bookings->get();
        // dd($bookings);

        foreach ($bookings as $booking) {
            $vehicleTypes = $booking->getVehicleTypes('ids');
            $drivers = self::availableDrivers($booking);
            // $transactions = $booking->bookingTransactions()->where('payment_method', 'cash')->get();
            // dump($drivers);
            // dd($drivers);

            foreach ($drivers as $driver) {
                $vehicle = null;
                $vehicles = $driver->vehicles->sortBy('vehicleType.passengers')->sortBy('vehicleType.luggage')->sortBy('vehicleType.hand_luggage');

                foreach ($vehicles as $k => $v) {
                    if (!empty($vehicleTypes)) {
                        if (in_array($v->vehicle_type_id, $vehicleTypes)) {
                            $vehicle = $v;
                            break;
                        }
                    }
                    else {
                        if (!empty($v->vehicleType->id) && (
                            $v->vehicleType->passengers >= $booking->passengers &&
                            $v->vehicleType->luggage >= $booking->luggage &&
                            $v->vehicleType->hand_luggage >= $booking->hand_luggage
                        )) {
                            $vehicle = $v;
                            break;
                        }
                    }
                }

                if (!$vehicle) {
                    $vehicle = $driver->driverDefaultVehicle();
                }

                // dd($vehicle);
                $vehicleId = !empty($vehicle->id) ? $vehicle->id : 0;

                $temp = $booking->getTotal('data', 'raw');
                $total = $temp->total;
                $cash = $temp->cash;
                $percent = !empty($driver->profile->commission) ? $driver->profile->commission : 0;
                $commission = $booking->commission ? $booking->commission : (($total / 100) * $percent);

                $req = new BookingDriver;
                $req->booking_id = $booking->id;
                $req->driver_id = $driver->id;
                $req->vehicle_id = $vehicleId;
                $req->commission = $commission;
                $req->cash = $cash;
                $req->status = 0;
                $req->auto_assigned = 1;
                $req->expired_at = config('eto_dispatch.time_to_confirm') ? Carbon::now()->addMinutes(config('eto_dispatch.time_to_confirm')) : null;
                $req->save();

                // Send notifications
                $notifications = [[
                    'type' => 'auto_dispatch',
                    'role' => [
                        'driver' => [],
                        // 'customer' => [],
                    ],
                ]];

                $nBooking = clone $booking;
                $nBooking->driver_id = $req->driver_id;
                $nBooking->vehicle_id = $req->vehicle_id;
                $nBooking->commission = $req->commission;
                $nBooking->cash = $req->cash;
                $nBooking->status = 'auto_dispatch';

                event(new \App\Events\BookingStatusChanged($nBooking, $notifications, true));
            }

            if (count($drivers) > 0) {
                $booking->status = 'auto_dispatch';
                $booking->save();
            }
        }

        return $bookings;
    }

    public static function availableDrivers($booking = null, $userId = 0)
    {
        if (empty($booking->id)) { return []; }

        $tnUser = (new \App\Models\User)->getTable();
        $drivers = \App\Models\User::with([
            'profile',
            'vehicles',
            'vehicles.vehicleType'
        ])->role('driver.*');

        if ($userId > 0) {
            $drivers->where($tnUser .'.id', $userId);
        }

        // Check if driver has been active within specific time frame
        if (config('eto_dispatch.time_last_seen')) {
            $drivers->where('last_seen_at', '>=', Carbon::now()->subMinutes(config('eto_dispatch.time_last_seen')));
        }

        // Check if driver status is set to available or onbreak
        if (config('eto_dispatch.check_availability_status')) {
            $drivers->whereHas('profile', function($query) {
                $query->whereIn('availability_status', [1, 2]);
            });
        }

        // Check vehicle type capacity
        $vehicleTypes = $booking->getVehicleTypes('ids');

        if (!empty($vehicleTypes)) {
            $drivers->whereHas('vehicles', function($query) use($booking, $vehicleTypes) {
                $query->whereIn('vehicle_type_id', $vehicleTypes);
            });
        }
        else {
            $drivers->whereHas('vehicles', function($query) use($booking) {
                $query->whereHas('vehicleType', function($query) use($booking) {
                    $query->where('passengers', '>=', $booking->passengers);
                    $query->where('luggage', '>=', $booking->luggage);
                    $query->where('hand_luggage', '>=', $booking->hand_luggage);
                });
            });
        }
        // dd($vehicleTypes);

        // Skip booking that match one of the checks
        $drivers->whereDoesntHave('driverBookings', function($query) use($booking) {
            // Check if booking has already been assigned to driver to prevent duplicates
            $query->where('booking_id', $booking->id);

            // Check if drivers assigned bookings are overlapping
            // https://helgesverre.com/blog/mysql-overlapping-intersecting-dates/
            // https://stackoverflow.com/questions/19743829/mysql-check-if-two-date-range-overlap-with-input/19743880
            $query->orWhereHas('booking', function($query) use($booking) {
                $ts = config('eto_dispatch.extra_time_slot');
                $date = $booking->date;
                $duration = $booking->duration + $ts;

                $query->whereRaw("((
                    `date` BETWEEN '{$date}' AND DATE_ADD('{$date}', INTERVAL '{$duration}' MINUTE)
                        OR
                    DATE_ADD(`date`, INTERVAL `duration` + {$ts} MINUTE) BETWEEN '{$date}' AND DATE_ADD('{$date}', INTERVAL '{$duration}' MINUTE)
                ) OR (
                    '{$date}' BETWEEN `date` AND DATE_ADD(`date`, INTERVAL `duration` + {$ts} MINUTE)
                        OR
                    DATE_ADD('{$date}', INTERVAL '{$duration}' MINUTE) BETWEEN `date` AND DATE_ADD(`date`, INTERVAL `duration` + {$ts} MINUTE)
                ))");
            });

            if (config('eto_dispatch.check_trashed')) {
                $query->withTrashed();
            }
        });

        // Find drivers which are closest to pick up point within specific radius
        if (config('eto_dispatch.check_within_radius')) {
            $drivers->withDistance($booking->coordinate_start_lat, $booking->coordinate_start_lon);
            $drivers->closeTo($booking->coordinate_start_lat, $booking->coordinate_start_lon, config('eto_dispatch.check_within_radius'));
            $drivers->orderBy('distance', 'asc');
        }

        // Limit amount of assigned drivers per booking
        if (config('eto_dispatch.assign_max_drivers')) {
            $reqs = BookingDriver::selectRaw("COUNT(*)")->where('booking_id', $booking->id)->where('status', '!=', 2);
            if (config('eto_dispatch.check_trashed')) {
                $reqs->withTrashed();
            }
            $drivers->whereRaw("({$reqs->toSql()}) < ". config('eto_dispatch.assign_max_drivers'));
            $drivers->mergeBindings($reqs->getQuery());
            $drivers->take(config('eto_dispatch.assign_max_drivers'));
        }

        // dd($drivers->with(['driverBookings', 'driverBookings.booking'])->get());
        // dd(\App\Helpers\DatabaseHelper::toSqlWithBindings($drivers, 0));

        return $drivers->get();
    }

    public static function expireRequest($id = 0)
    {
        $reqs = BookingDriver::where('status', 0)
          ->whereNotNull('expired_at')
          ->where('expired_at', '<=', Carbon::now());

        if ($id) {
            $reqs->where('booking_id', $id);
        }

        if (config('eto_dispatch.delete_expired')) {
            return $reqs->delete();
        }
        else {
            return $reqs->update(['status' => 2]);
        }
    }

    public static function acceptRequest($id = 0)
    {
        $req = BookingDriver::with(['booking', 'driver', 'driver.profile', 'vehicle'])->findOrFail($id);
        $driverData = !empty($req->driver) ? $req->driver->toArray() : [];
        $driverData = !empty($driverData) ? json_encode($driverData) : null;
        $vehicleData = !empty($req->vehicle) ? $req->vehicle->toArray() : [];
        $vehicleData = !empty($vehicleData) ? json_encode($vehicleData) : null;

        if (empty($req->booking->driver_id)) {
            $req->booking()->update([
                'driver_id' => $req->driver_id,
                'vehicle_id' => $req->vehicle_id,
                'commission' => $req->commission,
                'cash' => $req->cash,
                'driver_data' => $driverData,
                'vehicle_data' => $vehicleData,
                'status' => 'accepted',
            ]);

            // $req->driver_data = $driverData;
            // $req->vehicle_data = $vehicleData;
            $req->status = 1;
            $req->save();

            $reqs = BookingDriver::where('id', '!=', $req->id)
              ->where('booking_id', $req->booking_id)
              ->where('status', 0);

            if (config('eto_dispatch.delete_expired')) {
                $reqs->delete();
            }
            else {
                $reqs->update(['status' => 2]);
            }
        }

        return $req;
    }

    public static function rejectRequest($id = 0, $statusNotes = null)
    {
        $req = BookingDriver::with('booking')->findOrFail($id);

        if (empty($req->booking->driver_id)) {
            $req->status = 2;
            $req->status_notes = $statusNotes;
            $req->save();

            if (config('eto_dispatch.assign_driver_on_reject')) {
                self::availableBookings($req->booking->id);
            }

            // Send notifications
            $notifications = [[
                'type' => 'rejected',
                'role' => [
                    'admin' => [],
                ],
            ]];

            $nBooking = clone $req->booking;
            $nBooking->status = 'rejected';

            event(new \App\Events\BookingStatusChanged($nBooking, $notifications, true));
        }

        return $req;
    }
}
