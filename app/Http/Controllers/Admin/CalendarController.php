<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        session(['admin_booking_return_url' => url()->full()]);

        return view('admin.calendar.index');
    }

    public function events(Request $request)
    {
        $vsdUTC = Carbon::now('UTC')->timestamp((int)request()->get('viewStart'));
        $vedUTC = Carbon::now('UTC')->timestamp((int)request()->get('viewEnd'));

        $vsd = Carbon::parse($vsdUTC->toDateTimeString());
        $ved = Carbon::parse($vedUTC->toDateTimeString());

        $eventsList = ['data' => [], 'time' => ['start' => false, 'timeStart' => false, 'end' => false, 'timeEnd' => false]];

        $bookings = \App\Models\BookingRoute::with([
            'booking',
            'bookingService',
            'bookingDriver',
            'bookingDriver.profile',
        ]);

        if ($request->keywords) {
            $table = get_db_prefix() . (new \App\Models\BookingRoute())->getTable();

            $bookings->where('parent_booking_id', 0)->whereRaw("LOWER(REPLACE(
                CONCAT(
                    IFNULL(`{$table}`.`ref_number`, ''),
                    IFNULL(`{$table}`.`source`, ''),
                    IFNULL(`{$table}`.`source_details`, ''),
                    IFNULL(`{$table}`.`address_start`, ''),
                    IFNULL(`{$table}`.`address_end`, ''),
                    IFNULL(`{$table}`.`flight_number`, ''),
                    IFNULL(`{$table}`.`flight_landing_time`, ''),
                    IFNULL(`{$table}`.`departure_city`, ''),
                    IFNULL(`{$table}`.`departure_flight_number`, ''),
                    IFNULL(`{$table}`.`departure_flight_time`, ''),
                    IFNULL(`{$table}`.`departure_flight_city`, ''),
                    IFNULL(`{$table}`.`contact_name`, ''),
                    IFNULL(`{$table}`.`contact_email`, ''),
                    IFNULL(`{$table}`.`contact_mobile`, ''),
                    IFNULL(`{$table}`.`lead_passenger_name`, ''),
                    IFNULL(`{$table}`.`lead_passenger_email`, ''),
                    IFNULL(`{$table}`.`lead_passenger_mobile`, ''),
                    IFNULL(`{$table}`.`custom`, '')
                ), ' ', ''
            )) LIKE LOWER(REPLACE('%" . $request->keywords . "%', ' ', ''))");
        }

        if ($request->viewStart) {
            $bookings->whereRaw("DATE_ADD(`date`, INTERVAL (`duration` + (`service_duration` * 60) + `duration_base_start`) MINUTE) >= '" . $vsd->toDateTimeString() . "'");
        }

        if ($request->viewEnd) {
            $bookings->whereRaw("DATE_SUB(`date`, INTERVAL `duration_base_end` MINUTE) <= '" . $ved->toDateTimeString() . "'");
        }

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $bookings->where('fleet_id', auth()->user()->id);
				}

        foreach ($bookings->get() as $booking) {
            $duration = $booking->duration + ($booking->service_duration * 60);

            $bd = Carbon::parse($booking->getOriginal('date'));

            $bsd = $bd->copy();
            $bed = $bd->copy()->addMinutes($duration);

            // Total time
            $sd = $bsd->copy()->subMinutes($booking->duration_base_start);
            $ed = $bed->copy()->addMinutes($booking->duration_base_end);

            if ($eventsList['time']['timeStart'] === false || $eventsList['time']['timeStart'] > $sd->timestamp) {
                $eventsList['time']['start'] = $sd->format('Y-m-d');
                $eventsList['time']['timeStart'] = $sd->timestamp;
            }

            if ($eventsList['time']['timeStart'] === false || $eventsList['time']['timeStart'] < $ed->timestamp) {
                $eventsList['time']['end'] = $ed->format('Y-m-d');
                $eventsList['time']['timeEnd'] = $ed->timestamp;
            }

            $elapsed = \App\Helpers\SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));
            $title = '';

            if (config('site.admin_calendar_show_ref_number')) {
                $title .= $booking->getRefNumber() ."\n";
            }

            $serviceType = $booking->getServiceType();
            if (config('site.admin_calendar_show_service_type') && $serviceType) {
                $title .= trans('booking.service_type') .": ". $serviceType ."\n";
            }

            if (config('site.admin_calendar_show_name_passenger')) {
                if ($booking->getContactFullName()) {
                    $title .= trans('booking.contact_name') .": ". $booking->getContactFullName() ."\n";
                }
            }

            if (config('site.admin_calendar_show_name_customer')) {
                $customer = $booking->assignedCustomer();
                if (!empty($customer->id)) {
                    $title .= trans('booking.bookingCustommerSummaryLabel') .": ". (!empty($customer->company_name) ? trim($customer->company_name) .' - ' : ''). $customer->name ."\n";
                }
            }

            if (config('site.admin_calendar_show_from')) {
                $title .= trans('booking.from') .": ". $booking->getFrom('no_html') ."\n";
            }

            if (config('site.admin_calendar_show_to')) {
                $title .= trans('booking.to') .": ". $booking->getTo('no_html') ."\n";
            }

            if (config('site.admin_calendar_show_via') && $booking->getVia('no_html')) {
                $title .= trans('booking.via') .": ". $booking->getVia('no_html') ."\n";
            }

            if (config('eto_calendar.show.driver_name')) {
                $driver = $booking->assignedDriver();
                if (!empty($driver->id)) {
                    $title .= trans('booking.heading.driver') .": ". $driver->getName(true) . "\n";
                }
            }

            if (config('eto_calendar.show.passengers') && $booking->passengers) {
                $title .= trans('booking.passengers') .": ". $booking->passengers ."\n";
            }

            if (config('site.admin_calendar_show_vehicle_type') && $booking->getVehicleList()) {
                $title .= trans('booking.vehicle') .": ". $booking->getVehicleList() ."\n";
            }

            if (config('eto_calendar.show.custom') && $booking->custom) {
                if (!empty(config('eto_booking.custom_field.name'))) {
                    $title .= config('eto_booking.custom_field.name');
                }
                else {
                    $title .= trans('booking.customPlaceholder');
                }

                $title .= ": ". $booking->custom ."\n";
            }

            if (config('site.admin_calendar_show_estimated_time') && !empty(trim($elapsed))) {
                $title .= ' ('. trim($elapsed) .')';
            }

            $eventsList['data'][] = [
                'event_type' => 'job',
                'id' => $booking->id,
                'site_id' => $booking->booking->site_id,
                'title' => $title,
                'description' => '',
                'ordering' => 0,
                'start' => $sd->toDateTimeString(),
                'end' => config('site.admin_calendar_show_actual_time_slot') ? $ed->toDateTimeString() : null,
                'color' => $booking->getStatus('color_value'),
                'url_show' => route('admin.bookings.show', $booking->id),
            ];
        }

        unset($eventsList['time']['timeStart']);
        unset($eventsList['time']['timeEnd']);

        $eventsList['time']['end'] = $eventsList['time']['end'] === false ? $eventsList['time']['start'] : $eventsList['time']['end'];

        return $eventsList;
    }
}
