<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\BookingRoute;
use App\Helpers\SiteHelper;
use Carbon\Carbon;
use Datatables;
use Yajra\Datatables\Html\Builder;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    public function datatables(Request $request)
    {
        if (!auth()->user()->hasPermission('driver.jobs.index')) {
            return redirect_no_permission();
        }

        if ( $request->ajax() ) {
            $userId = auth()->user()->id;
            $status = $request->get('status');

            $model = BookingRoute::with([
                'booking',
                'bookingTransactions',
                'bookingAllRoutes',
            ]);
            $model->withBookingDriver($userId);
            $model->where('parent_booking_id', 0);
            $model->whereDriver($userId);

            if ($status) {
                if ($status == 'current') {
                    $statusList = ['onroute', 'arrived', 'onboard'];
                }
                elseif (in_array($status, ['accepted', 'onroute', 'arrived', 'onboard'])) {
                    $statusList = ['accepted', 'onroute', 'arrived', 'onboard'];
                }
                elseif (in_array($status, ['canceled', 'unfinished'])) {
                    $statusList = ['canceled', 'unfinished'];
                }
                elseif (in_array($status, ['assigned', 'auto_dispatch'])) {
                    $statusList = ['assigned', 'auto_dispatch'];
                }
                else {
                    $statusList = [$status];
                }

                $model->whereInDriverStatus($statusList);
            }

            $dt = Datatables::eloquent($model)
                ->addColumn('actions', function(BookingRoute $booking) {
                    $buttons = '<div class="actions">';
                        if (auth()->user()->hasPermission('driver.jobs.show')) {
                            $buttons .= '<a href="'. route('driver.jobs.show', $booking->id) .'" class="btn btn-sm btn-default" title="'. trans('driver/jobs.button.show') .'"><i class="fa fa-eye"></i></a>';
                        }

                        // if (auth()->user()->hasPermission('driver.jobs.edit')) {
                          // $buttons .= '<a href="'. route('driver.jobs.edit', $booking->id) .'" class="btn btn-sm btn-primary" title="'. trans('driver/jobs.button.edit') .'"><i class="fa fa-edit"></i></a>';
                        // }

                        $notes = [];
                        if ( $booking->notes ) {
                            $notes[] = trans('driver/jobs.admin_notes') .': '. SiteHelper::nl2br2(htmlspecialchars($booking->notes));
                        }
                        if ( $booking->driver_notes ) {
                            $notes[] = trans('driver/jobs.driver_notes') .': '. SiteHelper::nl2br2(htmlspecialchars($booking->driver_notes));
                        }
                        if ( $notes ) {
                            $buttons .= '<i title="<div style=\'text-align:left;\'><b>'. trans('driver/jobs.notes') .'</b><br>'. implode('<br>', $notes) .'</div>" class="ion-ios-information-outline notes-info"></i>';
                        }

                        // $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-default eto-btn-booking-tracking" data-eto-id="'.$booking->id.'" title="'.trans('admin/bookings.button.tracking').' #'.$booking->ref_number.'">
                        //     <i class="fa fa-map-marker"></i>
                        // </a>';

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->setRowId(function(BookingRoute $booking) {
                    return 'job_row_'. $booking->id;
                })
                ->editColumn('status', function(BookingRoute $booking) {
                    $status = '<span style="opacity:0.6;">'. $booking->getStatus('label') .'</span>';
                    if ( $booking->status_notes && in_array($booking->status, ['canceled', 'unfinished', 'rejected']) ) {
                        $status .= '<i title="'. SiteHelper::nl2br2(htmlspecialchars($booking->status_notes)) .'" class="ion-ios-information-outline notes-info"></i>';
                    }

                    if (!empty($booking->expired_at) && in_array($booking->status, ['assigned', 'auto_dispatch'])) {
                        $date = \Carbon\Carbon::parse($booking->expired_at);
                        $now = \Carbon\Carbon::now();
                        $diff = $date->diffInSeconds($now);

                        if ($date > $now) {
                            $countdown = '<div class="timer-countdown-listing timer-countdown" data-seconds-left="'. $diff .'"></div>';
                        }
                        else {
                            $countdown = '<div class="timer-countdown-listing">'. trans('driver/jobs.auto_dispatch_time_up') .'</div>';
                        }
                    }
                    else {
                        $countdown = '';
                    }

                    $status .= $countdown;

                    return $status;
                })
                ->editColumn('date', function(BookingRoute $booking) {
                    return SiteHelper::formatDateTime($booking->date);
                })
                ->editColumn('total', function(BookingRoute $booking) {
                    return $booking->getTotal();
                })
                ->editColumn('commission', function(BookingRoute $booking) {
                    return '<span class="text-green">'. $booking->getCommission() .'</span>';
                })
                ->editColumn('cash', function(BookingRoute $booking) {
                    if (!$booking->scheduled_route_id || $booking->parent_booking_id) {
                        return '<span class="text-danger">'. ($booking->cash ? $booking->getCash() : trans('driver/jobs.already_paid')) .'</span>';
                    }
                })
                ->editColumn('address_start', function(BookingRoute $booking) {
                    return '<div class="eto-address-more">'. $booking->getFrom() .'</div>';
                })
                ->editColumn('address_end', function(BookingRoute $booking) {
                    return '<div class="eto-address-more">'. $booking->getTo() .'</div>';
                })
                ->editColumn('waypoints', function(BookingRoute $booking) {
                    return '<div class="eto-address-more">'. $booking->getVia() .'</div>';
                })
                ->editColumn('vehicle_list', function(BookingRoute $booking) {
                    return $booking->getVehicleList();
                })
                ->editColumn('ref_number', function(BookingRoute $booking) {
                    if (auth()->user()->hasPermission('driver.jobs.show')) {
                        return '<a href="' . route('driver.jobs.show', $booking->id) . '" class="text-default">' . $booking->ref_number . '</a>';
                    }
                    return $booking->ref_number;
                })
                ->editColumn('created_at', function(BookingRoute $booking) {
                    return SiteHelper::formatDateTime($booking->created_at);
                })
                ->editColumn('updated_at', function(BookingRoute $booking) {
                    return SiteHelper::formatDateTime($booking->updated_at);
                });

            return $dt->make(true);
        }
    }

    public function index(Builder $builder, Request $request)
    {
        if (!auth()->user()->hasPermission('driver.jobs.index')) {
            return redirect_no_permission();
        }

        $columns = [
            ['data' => 'actions', 'name' => 'actions', 'title' => trans('driver/jobs.actions'), 'defaultContent' => '', 'render' => null, 'orderable' => false, 'searchable' => false, 'exportable' => false, 'printable' => true],
            ['data' => 'ref_number', 'name' => 'ref_number', 'title' => trans('driver/jobs.ref_number')],
            ['data' => 'status', 'name' => 'status', 'title' => trans('driver/jobs.status')],
        ];

        if ( config('site.driver_show_total') ) {
            $columns = array_merge($columns, [
                ['data' => 'total', 'name' => 'total', 'title' => trans('driver/jobs.total'), 'orderable' => false, 'searchable' => false]
            ]);
        }

        $columns = array_merge($columns, [
            ['data' => 'commission', 'name' => 'commission', 'title' => trans('driver/jobs.commission') .' <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px;" title="'. trans('driver/jobs.commission_info') .'"></i>'],
            ['data' => 'cash', 'name' => 'cash', 'title' => trans('driver/jobs.cash') .' <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px;" title="'. trans('driver/jobs.cash_info') .'"></i>'],
            ['data' => 'date', 'name' => 'date', 'title' => trans('driver/jobs.date')],
            ['data' => 'address_start', 'name' => 'address_start', 'title' => trans('driver/jobs.from')],
            ['data' => 'address_end', 'name' => 'address_end', 'title' => trans('driver/jobs.to')],
            ['data' => 'waypoints', 'name' => 'waypoints', 'title' => trans('driver/jobs.via')],
            ['data' => 'vehicle_list', 'name' => 'vehicle_list', 'title' => trans('driver/jobs.vehicle')],
            // ['data' => 'updated_at', 'name' => 'updated_at', 'title' => trans('driver/jobs.updated_at'), 'searchable' => false],
            // ['data' => 'created_at', 'name' => 'created_at', 'title' => trans('driver/jobs.created_at'), 'searchable' => false]
        ]);

        if ( in_array($request->get('status'), ['accepted', 'onroute', 'arrived', 'onboard', 'completed', 'current']) ) {
            $columns = array_merge($columns, [
                ['data' => 'flight_number', 'name' => 'flight_number', 'title' => trans('driver/jobs.flight_number'), 'visible' => false],
                ['data' => 'flight_landing_time', 'name' => 'flight_landing_time', 'title' => trans('driver/jobs.flight_landing_time'), 'visible' => false],
                ['data' => 'departure_city', 'name' => 'departure_city', 'title' => trans('driver/jobs.departure_city'), 'visible' => false],
                ['data' => 'departure_flight_number', 'name' => 'departure_flight_number', 'title' => trans('driver/jobs.departure_flight_number'), 'visible' => false],
                ['data' => 'departure_flight_time', 'name' => 'departure_flight_time', 'title' => trans('driver/jobs.departure_flight_time'), 'visible' => false],
                ['data' => 'departure_flight_city', 'name' => 'departure_flight_city', 'title' => trans('driver/jobs.departure_flight_city'), 'visible' => false],
                ['data' => 'meeting_point', 'name' => 'meeting_point', 'title' => trans('driver/jobs.meeting_point'), 'visible' => false],
                ['data' => 'meet_and_greet', 'name' => 'meet_and_greet', 'title' => trans('driver/jobs.meet_and_greet'), 'visible' => false],
                ['data' => 'waiting_time', 'name' => 'waiting_time', 'title' => trans('driver/jobs.waiting_time'), 'visible' => false],
                ['data' => 'contact_name', 'name' => 'contact_name', 'title' => trans('driver/jobs.contact_name'), 'visible' => false],
                ['data' => 'contact_mobile', 'name' => 'contact_mobile', 'title' => trans('driver/jobs.contact_mobile'), 'visible' => false],
                ['data' => 'contact_email', 'name' => 'contact_email', 'title' => trans('driver/jobs.contact_email'), 'visible' => false],
                ['data' => 'lead_passenger_name', 'name' => 'lead_passenger_name', 'title' => trans('driver/jobs.lead_passenger_name'), 'visible' => false],
                ['data' => 'lead_passenger_email', 'name' => 'lead_passenger_email', 'title' => trans('driver/jobs.lead_passenger_email'), 'visible' => false],
                ['data' => 'lead_passenger_mobile', 'name' => 'lead_passenger_mobile', 'title' => trans('driver/jobs.lead_passenger_mobile'), 'visible' => false],
                ['data' => 'passengers', 'name' => 'passengers', 'title' => trans('driver/jobs.passengers'), 'visible' => false],
                ['data' => 'luggage', 'name' => 'luggage', 'title' => trans('driver/jobs.luggage'), 'visible' => false],
                ['data' => 'hand_luggage', 'name' => 'hand_luggage', 'title' => trans('driver/jobs.hand_luggage'), 'visible' => false],
                ['data' => 'child_seats', 'name' => 'child_seats', 'title' => trans('driver/jobs.child_seats'), 'visible' => false],
                ['data' => 'baby_seats', 'name' => 'baby_seats', 'title' => trans('driver/jobs.baby_seats'), 'visible' => false],
                ['data' => 'infant_seats', 'name' => 'infant_seats', 'title' => trans('driver/jobs.infant_seats'), 'visible' => false],
                ['data' => 'wheelchair', 'name' => 'wheelchair', 'title' => trans('driver/jobs.wheelchair'), 'visible' => false],
                ['data' => 'requirements', 'name' => 'requirements', 'title' => trans('driver/jobs.requirements'), 'visible' => false],
            ]);
        }

        // $columns = array_merge($columns, [
        //     ['data' => 'id', 'name' => 'id', 'title' => trans('driver/jobs.id'), 'visible' => false],
        // ]);

        $pageName = 'all';
        $defaultOrder = [6, 'desc'];

        switch(request('status')) {
            case 'current':
                $pageName = 'current';
                $defaultOrder = [6, 'asc'];
            break;
            case 'accepted':
            case 'onroute':
            case 'arrived':
            case 'onboard':
                $pageName = 'accepted';
                $defaultOrder = [6, 'asc'];
            break;
            case 'canceled':
            case 'unfinished':
                $pageName = 'canceled';
            break;
            case 'assigned':
            case 'auto_dispatch':
                $pageName = 'assigned';
                $defaultOrder = [6, 'asc'];
            break;
            case 'completed':
                $pageName = 'completed';
            break;
        }

        $storageKey = 'ETO_driver_jobs_'. $pageName .'_'. config('app.timestamp');

        $parameters = [
            'colReorder' => true,
            'paging' => true,
            'pagingType' => 'full_numbers',
            'scrollX' => true,
            'searching' => true,
            'ordering' => true,
            'lengthChange' => true,
            'info' => true,
            'autoWidth' => true,
            'stateSave' => true,
            'stateSaveCallback' => 'function(settings, data) { window.localStorage.setItem(\''. $storageKey .'\', JSON.stringify(data)) }',
            'stateLoadCallback' => 'function(settings) { return JSON.parse(window.localStorage.getItem(\''. $storageKey .'\')) }',
            'stateDuration' => 0,
            'order' => [$defaultOrder],
            'pageLength' => 10,
            'lengthMenu' => [5, 10, 25, 50],
            'language' => [
                'search' => '_INPUT_',
                'searchPlaceholder' => trans('driver/jobs.search_placeholder'),
                'lengthMenu' => '_MENU_',
                'paginate' => [
                    'first' => '<i class="fa fa-angle-double-left"></i>',
                    'previous' => '<i class="fa fa-angle-left"></i>',
                    'next' => '<i class="fa fa-angle-right"></i>',
                    'last' => '<i class="fa fa-angle-double-right"></i>'
                ]
            ],
            'dom' => '<"row topContainer"<"col-xs-6 col-sm-6 col-md-7 dataTablesHeaderLeft"B><"col-xs-6 col-sm-6 col-md-5 dataTablesHeaderRight"f>><"dataTablesBody"rt><"row bottomContainer"<"col-xs-6 col-sm-6 col-md-7 pull-right dataTablesFooterRight"p><"col-xs-6 col-sm-6 col-md-5 dataTablesFooterLeft"li>>',
            'initComplete' => 'function() { dtCallback(\'init\'); }',
            'drawCallback' => 'function() { dtCallback(\'draw\'); }',
            'infoCallback' => 'function( settings, start, end, max, total, pre ) {return \'<i class="ion-ios-information-outline" title="\'+ pre +\'"></i>\';}',
            'searchDelay' => 350,
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => '<i class="fa fa-eye"></i>',
                    'titleAttr' => trans('driver/jobs.button.column_visibility'),
                    'postfixButtons' => ['colvisRestore'],
                    'className' => 'btn-default btn-sm'
                ], [
                    'text' => '<div onclick="$(\'#dataTableBuilder\').DataTable().state.clear(); window.location.reload();"><i class="fa fa-undo"></i></div>',
                    'titleAttr' => trans('driver/jobs.button.reset'),
                    'className' => 'btn-default btn-sm',
                ], [
                    'extend' => 'reload',
                    'text' => '<i class="fa fa-refresh"></i>',
                    'titleAttr' => trans('driver/jobs.button.reload'),
                    'className' => 'btn-default btn-sm'
                ]
            ]
        ];

        $ajax = [
            'url' => route('driver.jobs.datatables'),
            'type' => 'POST',
            'headers' => [
                'X-CSRF-TOKEN' => csrf_token()
            ],
            'data' => json_encode([
                'status' => $request->get('status')
            ])
        ];

        $builder->columns($columns)->parameters($parameters)->ajax($ajax);

        return view('driver.jobs.index', ['builder' => $builder]);
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('driver.jobs.show')) {
            return redirect_no_permission();
        }

        // \App\Traits\TestDispatch::_driverTracking();
        $userId = auth()->user()->id;
        $job = BookingRoute::withBookingDriver($userId)->whereDriver($userId)->findOrFail($id);
        $runingJob = BookingRoute::whereDriver($userId)
            ->whereIn('status', ['onroute', 'arrived', 'onboard'])
            ->get()->pluck('id')->toArray();

        if (count((array)$runingJob) === 0) {
            $runingJob = [$job->id];
        }

        if (!empty($job->expired_at) && in_array($job->status, ['assigned', 'auto_dispatch'])) {
            $req = \App\Http\Controllers\DispatchDriverController::expireRequest($job->id);

            if ($req > 0) {
                $job = BookingRoute::withBookingDriver($userId)->whereDriver($userId)->findOrFail($id);
            }
        }

        return view('driver.jobs.show', compact('job', 'runingJob'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('driver.jobs.create')) {
            return redirect_no_permission();
        }

        return view('driver.jobs.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('driver.jobs.create')) {
            return redirect_no_permission();
        }

        return redirect()->route('driver.jobs.index');
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('driver.jobs.edit')) {
            return redirect_no_permission();
        }

        $userId = auth()->user()->id;
        $booking = BookingRoute::whereDriver($userId)->findOrFail($id);

        return view('driver.jobs.edit', ['job' => $booking]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('driver.jobs.edit')) {
            return redirect_no_permission();
        }

        $userId = auth()->user()->id;
        $booking = BookingRoute::whereDriver($userId)->findOrFail($id);

        $errors = [];
        $response = [];

        if (!is_null($request->get('driver_notes'))) {
            $booking->driver_notes = $request->get('driver_notes');
            $booking->save();
        }

        // Files
        if (config('eto_driver.booking_file_upload') && !is_null($request->get('files')) && $booking->id && !(
            $booking->status == 'completed' &&
            config('eto_driver.booking_file_upload_auto_lock') &&
            \Carbon\Carbon::parse($booking->date)->addHours(config('eto_driver.booking_file_upload_auto_lock'))->lt(\Carbon\Carbon::now())
        )) {
            \App\Helpers\SiteHelper::extendValidatorRules();

            $filesList = (object)$request->get('files');

            if ( !empty($filesList) ) {
                foreach($filesList as $key => $value) {
                    $value = (object)$value;
                    $value->name = trim($value->name);

                    if ( $value->id > 0 ) {
                        // $query = \DB::table('file')
                        //     ->where('file_relation_type', 'booking')
                        //     ->where('file_relation_id', $booking->id)
                        //     ->where('file_id', $value->id)
                        //     ->first();
                        //
                        // if ( !empty($query) ) {
                        //     if ( $value->delete > 0 ) {
                        //         if ( \Storage::disk('safe')->exists($query->file_path) ) {
                        //             \Storage::disk('safe')->delete($query->file_path);
                        //         }
                        //         \DB::table('file')->where('file_id', $query->file_id)->delete();
                        //     }
                        //     else {
                        //         \DB::table('file')->where('file_id', $query->file_id)->update(['file_name' => $value->name]);
                        //     }
                        // }
                    }
                    else {
                        if ( isset($request->file('files')[$key]['file']) ) {
                            $file = $request->file('files')[$key]['file'];

                            $files = [
                                'file' => $file
                            ];

                            $rules = [
                                'file' => 'required|file_extension:'. config('eto.allowed_file_extensions')
                            ];

                            $validator = \Validator::make($files, $rules);

                            if ( $validator->fails() ) {
                                $errors = array_merge($errors, $validator->errors()->all());
                            }
                            else {
                                $originalName = $file->getClientOriginalName();
                                $extension = $file->getClientOriginalExtension();
                                $newName = \App\Helpers\SiteHelper::generateFilename('booking') .'.'. $extension;

                                $realPath = $file->getRealPath();
                                $size = $file->getSize();
                                $mimeType = $file->getMimeType();
                                $params['files'][] = [$realPath, $size, $mimeType, $newName, $extension];

                                $file->move(asset_path('uploads','safe'), $newName);

                                \DB::table('file')->insertGetId([
                                    'file_name' => $value->name,
                                    'file_path' => $newName,
                                    'file_site_id' => 0,
                                    'file_description' => $originalName,
                                    'file_relation_type' => 'booking',
                                    'file_relation_id' => $booking->id,
                                    'file_free_download' => 0,
                                    'file_ordering' => 0,
                                    'file_limit' => 0
                                ]);
                            }
                        }
                    }
                }
            }
        }

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($request->ajax()) {
            return $response;
        }
        else {
            if (empty($errors)) {
                session()->flash('message', trans('driver/jobs.message.saved'));
            }
            return redirect()->back()->withErrors($errors);
        }
    }

    public function download($id = 0, $fileId = 0)
    {
        $userId = auth()->user()->id;
        $booking = BookingRoute::whereDriver($userId)->findOrFail($id);

        if (config('eto_driver.booking_file_upload') && $booking->id) {
            $query = \DB::table('file')
                ->where('file_relation_type', 'booking')
                ->where('file_relation_id', $booking->id)
                ->where('file_id', $fileId)
                ->first();

            if (!empty($query)) {
                $filePath = asset_path('uploads','safe/'. $query->file_path);

                if (request('type') == 'show') {
                    return response()->file($filePath);
                }
                return response()->download($filePath, $query->file_path);
            }
        }

        return;
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('driver.jobs.destroy')) {
            return redirect_no_permission();
        }

        // $userId = auth()->user()->id;
        // $booking = BookingRoute::whereDriver($userId)->findOrFail($id);
        // $booking->delete();

        return redirect()->route('driver.jobs.index');
    }

    public function status($id, $status, Request $request)
    {
        if (!auth()->user()->hasPermission('driver.jobs.edit')) {
            return redirect_no_permission();
        }

        $allowed = (new \App\Models\BookingRoute)->allowed_statuses;
        $initStatuses = (new \App\Models\BookingRoute)->init_statuses;
        $response = [
            'statusChange' => false,
        ];

        if (in_array($status, $allowed)) {
            $userId = auth()->user()->id;
            $booking = BookingRoute::whereDriver($userId)->findOrFail($id);
            $runingJob = [];

            $runingJob = BookingRoute::whereDriver($userId)
                ->whereIn('status', ['onroute', 'arrived', 'onboard'])
                ->get()->pluck('id')->toArray();

            if (count((array)$runingJob) === 0) {
                $runingJob = [$booking->id];
            }

            if ((count($runingJob) > 0 && in_array($booking->id, $runingJob)) || in_array($status, ['accepted', 'rejected'])) {
                \App\Helpers\BookingHelper::setActiveDriver($booking, $status, $userId);

                $statusNotes = $request->get('note') ?: null;
                $booking->status = $status;
                $booking->status_notes = $statusNotes;
                $updateBooking = 1;

                if (in_array($booking->status, $initStatuses)) {
                    $req = \App\Models\BookingDriver::where('booking_id', $booking->id)->where('driver_id', $userId)->first();
                    if (!empty($req->id)) {
                        if ($booking->status == 'accepted') {
                            \App\Http\Controllers\DispatchDriverController::acceptRequest($req->id);
                        }
                        else {
                            \App\Http\Controllers\DispatchDriverController::rejectRequest($req->id, $statusNotes);
                            $updateBooking = 0;
                        }
                    }
                }

                if ($updateBooking) {
                    $booking->save();
                    $booking->setDriverStatus($userId);

                    event(new \App\Events\BookingStatusChanged($booking));
                }

                $response['statusChange'] = true;
            }

            $response['runingJob'] = $runingJob;
            $response['status'] = $booking->status;
            $response['status_formatted'] = $booking->getStatus(in_array($booking->status, ['assigned', 'auto_dispatch', 'accepted', 'onroute', 'arrived', 'onboard']) ? 'none' : 'label');
        }

        if ($request->ajax()) {
            return $response;
        }
        else {
            if (url()->previous() != url()->full()) {
                return redirect()->back();
            }
            else {
                return redirect()->route('driver.jobs.index');
            }
        }
    }

    public function meetingBoard($id, Request $request)
    {
        if (!auth()->user()->hasPermission('driver.jobs.show')) {
            return redirect_no_permission();
        }

        $userId = auth()->user()->id;
        $booking = BookingRoute::whereDriver($userId)->findOrFail($id);

        if ($request->get('action') && $request->get('action') == 'download') {
            $filename = trans('driver/jobs.subtitle.meeting_board') .'.pdf';
            $html = $booking->getMeetingBoard('pdf');

            $mpdf = new \Mpdf\Mpdf([
                'mode' => '',
                'format' => 'A4',
                'default_font_size' => 0,
                'default_font' => '',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_header' => 0,
                'margin_footer' => 0,
                'orientation' => 'L',
            ]);
            $mpdf->WriteHTML($html);

            return $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
        }
        else {
            return view('driver.jobs.meeting_board', [
                'job' => $booking,
            ]);
        }
    }
}
