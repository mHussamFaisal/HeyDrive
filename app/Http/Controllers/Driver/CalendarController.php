<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\BookingRoute;
use App\Models\Event;
use Form;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\SiteHelper;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('driver.calendar.index')) {
            return redirect_no_permission();
        }

        $user = auth()->user();

        if ( $request->ajax() ) {
            $vsdUTC = Carbon::now('UTC')->timestamp(request()->get('viewStart'));
            $vedUTC = Carbon::now('UTC')->timestamp(request()->get('viewEnd'));

            $vsd = Carbon::parse($vsdUTC->toDateTimeString());
            $ved = Carbon::parse($vedUTC->toDateTimeString());

            $eventsList = [];

            // Events
            $events = \App\Models\Event::where('relation_type', '=', 'user')
                ->where('relation_id', '=', $user->id)
                ->orderBy('ordering', 'asc');

            foreach ($events->get() as $event) {
                $event->repeat_days = !empty($event->repeat_days) ? json_decode($event->repeat_days) : [$event->start_at->dayOfWeek];
                $event->repeat_interval = !empty($event->repeat_interval) ? $event->repeat_interval : 1;

                if ($event->repeat_type == 'none') {
                    $sd = $event->start_at->copy();
                    $ed = $event->end_at->copy();

                    $eventsList[] = [
                        'event_type' => 'event',
                        'id' => $event->id,
                        'title' => $event->name,
                        'description' => $event->description,
                        'ordering' => $event->ordering,
                        'start' => $sd->toDateTimeString(),
                        'end' => $ed->toDateTimeString(),
                        'className' => ($event->status == 'active') ? 'fc-event_driver_active' : 'fc-event_driver_inactive'
                    ];

                    continue;
                }

                if ($event->start_at->lt($vsd)) {
                    $vsd->subSeconds($event->start_at->diffInSeconds($vsd));
                }

                if ($event->end_at->gt($ved)) {
                    $ved->addSeconds($event->end_at->diffInSeconds($ved));
                }

                for ($i = 0; $i <= $vsd->diffInDays($ved); $i++) {
                    $sd = $vsd->copy()->addDays($i)->setTime($event->start_at->hour, $event->start_at->minute, $event->start_at->second);
                    $ed = $sd->copy()->addSeconds($event->start_at->diffInSeconds($event->end_at));

                    $skip = 0;
                    $diff = 0;
                    $diffSpan = 0;
                    $time = 0;

                    switch ($event->repeat_type) {
                        case 'daily':
                            $diff = $sd->diffInDays($event->start_at);
                            $diffSpan = $event->start_at->diffInDays($event->end_at);
                            $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);
                        break;
                        case 'weekly':
                            $diff = $sd->diffInDays($event->start_at);
                            $diffSpan = $event->start_at->diffInDays($event->end_at);
                            $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);

                            $repeat_days = $event->repeat_days;
                            if ($event->start_at->diffInDays($event->end_at) > 0) {
                                for ($j = 0; $j <= $event->start_at->diffInDays($event->end_at); $j++) {
                                    $repeat_days[] = (string)$sd->copy()->addDays($j)->dayOfWeek;
                                }
                            }
                            $event->repeat_days = $repeat_days;

                            if ( !in_array($sd->dayOfWeek, $event->repeat_days) ) {
                                $skip = 1;
                            }
                        break;
                        case 'monthly':
                            $diff = $sd->diffInMonths($event->start_at);
                            $diffSpan = $event->start_at->diffInMonths($event->end_at);
                            $time = $sd->copy()->subMonths($diff)->diffInSeconds($event->start_at);
                        break;
                        case 'yearly':
                            $diff = $sd->diffInYears($event->start_at);
                            $diffSpan = $event->start_at->diffInYears($event->end_at);
                            $time = $sd->copy()->subYears($diff)->diffInSeconds($event->start_at);
                        break;
                        default:
                            $diff = $sd->diffInDays($event->start_at);
                            $diffSpan = $event->start_at->diffInDays($event->end_at);
                            $time = $sd->copy()->subDays($diff)->diffInSeconds($event->start_at);

                            $event->repeat_interval = 1;
                            $event->repeat_limit = 1;
                        break;
                    }

                    // The same day of week
                    if ( $time != 0 ) {
                        $skip = 1;
                    }

                    // Lower then start date
                    if ( $sd->lt($event->start_at) ) {
                        $skip = 1;
                    }

                    // Repeat end date
                    if ( !empty($event->repeat_end) && $ed->gte($event->repeat_end) ) {
                        $skip = 1;
                    }

                    // Repeat interval
                    if ( !empty($event->repeat_interval) && ($diff % $event->repeat_interval) - $diffSpan > 0 ) {
                        $skip = 1;
                    }

                    // Repeat limit
                    if ( !empty($event->repeat_limit) && $diff >= ($event->repeat_limit * $event->repeat_interval) ) {
                        $skip = 1;
                    }

                    if ( $skip ) {
                        continue;
                    }

                    $eventsList[] = [
                        'event_type' => 'event',
                        'id' => $event->id,
                        'title' => $event->name,
                        'description' => $event->description,
                        'ordering' => $event->ordering,
                        'start' => $sd->toDateTimeString(),
                        'end' => $ed->toDateTimeString(),
                        'className' => ($event->status == 'active') ? 'fc-event_driver_active' : 'fc-event_driver_inactive'
                    ];
                }
            }


            // Bookings
            $bookings = \App\Models\BookingRoute::whereRaw("
                DATE_ADD(`date`, INTERVAL (`duration` + (`service_duration` * 60) + `duration_base_start`) MINUTE) >= '". $vsd->toDateTimeString() ."'
                    AND
                DATE_SUB(`date`, INTERVAL `duration_base_end` MINUTE) <= '". $ved->toDateTimeString() ."'
            ")
            ->where('parent_booking_id', 0)
            ->whereDriver($user->id);

            foreach ($bookings->get() as $booking) {
                $duration = $booking->duration + ($booking->service_duration * 60);

                $bd = Carbon::parse($booking->getOriginal('date'));

                $bsd = $bd->copy();
                $bed = $bd->copy()->addMinutes($duration);

                // Total time
                $sd = $bsd->copy()->subMinutes($booking->duration_base_start);
                $ed = $bed->copy()->addMinutes($booking->duration_base_end);

                $elapsed = SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));
                $title = '';

                if (config('site.driver_calendar_show_ref_number')) {
                    $title .= $booking->getRefNumber() ."\n";
                }

                if (config('site.driver_calendar_show_from')) {
                    $title .= trans('booking.from') .": ". $booking->getFrom('no_html') ."\n";
                }

                if (config('site.driver_calendar_show_to')) {
                    $title .= trans('booking.to') .": ". $booking->getTo('no_html') ."\n";
                }

                if (config('site.driver_calendar_show_via') && $booking->getVia('no_html')) {
                    $title .= trans('booking.via') .": ". $booking->getVia('no_html') ."\n";
                }

                if (config('site.driver_calendar_show_passengers') && $booking->passengers) {
                    $title .= trans('booking.passengers') .": ". $booking->passengers ."\n";
                }

                if (config('site.driver_calendar_show_vehicle_type') && $booking->getVehicleList()) {
                    $title .= trans('booking.vehicle') .": ". $booking->getVehicleList() ."\n";
                }

                if (config('site.driver_calendar_show_custom') && $booking->custom) {
                    if (!empty(config('eto_booking.custom_field.name'))) {
                        $title .= config('eto_booking.custom_field.name');
                    }
                    else {
                        $title .= trans('booking.customPlaceholder');
                    }

                    $title .= ": ". $booking->custom ."\n";
                }

                if (config('site.driver_calendar_show_estimated_time') && !empty(trim($elapsed))) {
                    $title .= ' ('. trim($elapsed) .')';
                }

                $eventsList[] = [
                    'event_type' => 'job',
                    'id' => $booking->id,
                    'title' => $title ? $title : trans('driver/calendar.subtitle.job'),
                    'description' => '',
                    'ordering' => 0,
                    'start' => $sd->toDateTimeString(),
                    'end' => config('site.driver_calendar_show_actual_time_slot') ? $ed->toDateTimeString() : null,
                    'color' => $booking->getStatus('color_value'),
                    // 'className' => 'fc-event_driver_booked',
                ];

                /*
                // Actual time
                $sd = $bsd->copy();
                $ed = $bed->copy();

                $elapsed = SiteHelper::displayElapsedTime(Carbon::now()->addSeconds($sd->diffInSeconds($ed)));

                $title = trans('driver/calendar.subtitle.job');
                if ( !empty(trim($elapsed)) ) {
                    $title .= ' ('. trim($elapsed) .')';
                }

                // $title .= ' | bsd: '. $bsd->toDateTimeString();
                // $title .= ' | bed: '. $bed->toDateTimeString();
                // $title .= ' | sd: '. $sd->toDateTimeString();
                // $title .= ' | ed: '. $ed->toDateTimeString();
                // $title .= ' | duration: '. $duration;

                $eventsList[] = [
                    'event_type' => 'job',
                    'id' => $booking->id,
                    'title' => $title,
                    'description' => '',
                    'ordering' => 0,
                    'start' => $sd->toDateTimeString(),
                    'end' => $ed->toDateTimeString(),
                    'className' => 'fc-event_driver_booked',
                ];
                */
            }

            return $eventsList;
        }

        return view('driver.calendar.index');
    }

    // public function show($id)
    // {
    //     $user = auth()->user();
    //     $event = Event::where('relation_type', '=', 'user')->where('relation_id', '=', $user->id)->findOrFail($id);
    //
    //     return view('driver.calendar.show', [
    //         'event' => $event
    //     ]);
    // }

    public function create()
    {
        if (!auth()->user()->hasPermission('driver.calendar.create')) {
            return redirect_no_permission();
        }

        $event = new Event;

        $status = [];
        foreach($event->options->status as $key => $value) {
            $status[$key] = $value['name'];
        }

        if ( request()->get('start') ) {
            $start_at = Carbon::now('UTC')->timestamp(request()->get('start'))->toDateTimeString();
            // $event->start_at = str_replace(' ', 'T', $start_at);
        }

        if ( request()->get('end') ) {
            $end_at = Carbon::now('UTC')->timestamp(request()->get('end'))->toDateTimeString();
            // $event->end_at = str_replace(' ', 'T', $end_at);
        }

        return view('driver.calendar.create', [
            'event' => $event,
            'status' => $status
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('driver.calendar.create')) {
            return redirect_no_permission();
        }

        $user = auth()->user();

        $rules = [
            'name' => 'required|max:255',
            'start_at' => 'date',
            'end_at' => 'date',
            'repeat_end' => 'date',
        ];

        $messages = [];

        $attributeNames = [];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        $event = new Event;
        $event->relation_type = 'user';
        $event->relation_id = $user->id;
        $event->name = $request->get('name');
        $event->description = $request->get('description');
        $event->start_at = $request->get('start_at') ?: Carbon::now();
        $event->end_at = $request->get('end_at') ?: Carbon::now()->addMinutes(15);
        $event->repeat_type = $request->get('repeat_type');

        if ( $request->get('repeat_type') != 'none' ) {
            $event->repeat_interval = $request->get('repeat_interval');
            $event->repeat_days = !empty($request->get('repeat_days')) ? json_encode((array)$request->get('repeat_days')) : null;
            $event->repeat_end = $request->get('repeat_end');
            $event->repeat_limit = $request->get('repeat_limit');
        }
        else {
            $event->repeat_interval = 0;
            $event->repeat_days = null;
            $event->repeat_end = null;
            $event->repeat_limit = 0;
        }

        $event->ordering = $request->get('ordering');
        $event->status = $request->get('status');
        $event->save();

        session()->flash('message', trans('driver/calendar.message.store_success'));

        if ($request->get('tmpl') == 'body') {
            return redirect()->route('driver.calendar.edit', ['id' => $event->id, 'tmpl' => 'body', 'close' => '1']);
        }
        else {
            return redirect()->route('driver.calendar.create');
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('driver.calendar.edit')) {
            return redirect_no_permission();
        }

        $user = auth()->user();
        $event = Event::where('relation_type', '=', 'user')->where('relation_id', '=', $user->id)->findOrFail($id);

        $status = [];
        foreach($event->options->status as $key => $value) {
            $status[$key] = $value['name'];
        }

        if ( !empty($event->repeat_days) ) {
            $event->repeat_days = json_decode($event->repeat_days);
        }

        return view('driver.calendar.edit', [
            'event' => $event,
            'status' => $status
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('driver.calendar.edit')) {
            return redirect_no_permission();
        }

        $user = auth()->user();
        $event = Event::where('relation_type', '=', 'user')->where('relation_id', '=', $user->id)->findOrFail($id);

        $rules = [
            'name' => 'required|max:255',
            'start_at' => 'date',
            'end_at' => 'date',
            'repeat_end' => 'date',
        ];

        $messages = [];

        $attributeNames = [];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        $event->name = $request->get('name');
        $event->description = $request->get('description');
        $event->start_at = $request->get('start_at') ?: Carbon::now();
        $event->end_at = $request->get('end_at') ?: Carbon::now()->addMinutes(15);
        $event->repeat_type = $request->get('repeat_type');

        if ( $request->get('repeat_type') != 'none' ) {
            $event->repeat_interval = $request->get('repeat_interval');
            $event->repeat_days = !empty($request->get('repeat_days')) ? json_encode((array)$request->get('repeat_days')) : null;
            $event->repeat_end = $request->get('repeat_end');
            $event->repeat_limit = $request->get('repeat_limit');
        }
        else {
            $event->repeat_interval = 0;
            $event->repeat_days = null;
            $event->repeat_end = null;
            $event->repeat_limit = 0;
        }

        $event->ordering = $request->get('ordering');
        $event->status = $request->get('status');
        $event->updated_at = Carbon::now();
        $event->save();

        session()->flash('message', trans('driver/calendar.message.update_success'));

        if ($request->get('tmpl') == 'body') {
            return redirect()->route('driver.calendar.edit', ['id' => $id, 'tmpl' => 'body', 'close' => '1']);
        }
        else {
            return redirect()->back();
        }
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('driver.calendar.destroy')) {
            return redirect_no_permission();
        }

        $user = auth()->user();
        $event = Event::where('relation_type', '=', 'user')->where('relation_id', '=', $user->id)->findOrFail($id);
        $event->delete();

        session()->flash('message', trans('driver/calendar.message.destroy_success'));

        if ($request->get('tmpl') == 'body') {
            return redirect()->route('driver.calendar.create', ['tmpl' => 'body', 'close' => '1']);
        }
        else {
            return redirect()->route('driver.calendar.create'); // driver.calendar.index
        }
    }

    public function status($id, $status)
    {
        if (!auth()->user()->hasPermission('driver.calendar.edit')) {
            return redirect_no_permission();
        }

        $user = auth()->user();
        $event = Event::where('relation_type', '=', 'user')->where('relation_id', '=', $user->id)->findOrFail($id);

        if ( in_array($status, $event->options->status) ) {
            $event->status = $status;
            $event->save();
        }

        return redirect()->back();
    }
}
