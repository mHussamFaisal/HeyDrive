<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BookingRoute;
use App\Models\Transaction;
use App\Helpers\SiteHelper;

class BookingsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('admin.bookings.index')) {
            return redirect_no_permission();
        }

        session(['admin_booking_return_url' => url()->full()]);

        return view('admin.bookings.index');
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermission('admin.bookings.show')) {
            return redirect_no_permission();
        }

        $booking = BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            },
            'bookingTransactions' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->findOrFail($id);

        $booking->is_read = 1;
        $booking->save();

        return view('admin.bookings.show', compact('booking'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('admin.bookings.create')) {
            return redirect_no_permission();
        }

        return view('admin.bookings.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.bookings.create')) {
            return redirect_no_permission();
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermission('admin.bookings.edit')) {
            return redirect_no_permission();
        }

        $booking = BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->findOrFail($id);

        $booking->is_read = 1;
        $booking->save();

        return view('admin.bookings.edit', [
            'booking' => $booking,
            'siteId' => $booking->booking->site_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('admin.bookings.edit')) {
            return redirect_no_permission();
        }

        $booking = BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->findOrFail($id);

        $errors = [];
        $response = [];

        // Files
        if (!is_null($request->get('files')) && $booking->id) {
            \App\Helpers\SiteHelper::extendValidatorRules();

            $filesList = (object)$request->get('files');

            if ( !empty($filesList) ) {
                foreach($filesList as $key => $value) {
                    $value = (object)$value;
                    $value->name = trim($value->name);

                    if ( $value->id > 0 ) {
                        $query = \DB::table('file')
                            ->where('file_relation_type', 'booking')
                            ->where('file_relation_id', $booking->id)
                            ->where('file_id', $value->id)
                            ->first();

                        if ( !empty($query) ) {
                            if ( $value->delete > 0 ) {
                                if ( \Storage::disk('safe')->exists($query->file_path) ) {
                                    \Storage::disk('safe')->delete($query->file_path);
                                }
                                \DB::table('file')->where('file_id', $query->file_id)->delete();
                            }
                            else {
                                \DB::table('file')->where('file_id', $query->file_id)->update(['file_name' => $value->name]);
                            }
                        }
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

    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('admin.bookings.trash')) {
            return redirect_no_permission();
        }
    }

    public function sms($id)
    {
        $booking = BookingRoute::withTrashed()->findOrFail($id);
        $booking->is_read = 1;
        $booking->save();

        $driver = $booking->assignedDriver();
        $contacts = [];

        if (!empty($driver->profile)) {
            if ($driver->profile->mobile_no) {
                $contacts[] = (object)[
                    'value' => $driver->profile->mobile_no,
                    'text' => trans('admin/bookings.sms.driver_mobile') .': '. $driver->profile->mobile_no
                ];
            }
            if ($driver->profile->telephone_no) {
                $contacts[] = (object)[
                    'value' => $driver->profile->telephone_no,
                    'text' => trans('admin/bookings.sms.driver_telephone') .': '. $driver->profile->telephone_no
                ];
            }
            if ($driver->profile->emergency_no) {
                $contacts[] = (object)[
                    'value' => $driver->profile->emergency_no,
                    'text' => trans('admin/bookings.sms.driver_emergency') .': '. $driver->profile->emergency_no
                ];
            }
        }

        if ( $booking->contact_mobile ) {
            $contacts[] = (object)[
                'value' => $booking->contact_mobile,
                'text' => trans('admin/bookings.sms.customer_mobile') .': '. $booking->contact_mobile
            ];
        }

        return view('admin.bookings.sms', [
            'booking' => $booking,
            'contacts' => $contacts,
        ]);
    }

    public function invoice($id, Request $request)
    {
        $booking = BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->findOrFail($id);

        $booking->is_read = 1;
        $booking->save();

        if ( $request->get('action') ) {
            switch( $request->get('action') ) {
                case 'download':

                    $filename = $booking->getInvoiceFilename() .'.pdf';
                    $html = $booking->getInvoice();

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
                        'orientation' => 'P',
                    ]);
                    $mpdf->WriteHTML($html);

                    return $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);

                break;
            }
        }
        else {
            return view('admin.bookings.invoice', [
                'booking' => $booking,
            ]);
        }
    }

    public function copy($id, Request $request)
    {
        if (!auth()->user()->hasPermission('admin.bookings.create')
            || $request->system->subscription->license_status == 'suspended'
        ) {
            return redirect_no_permission();
        }

        $booking = BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            },
            'bookingTransactions' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->findOrFail($id);

        if ( $request->get('action') ) {
            $response = [];

            switch( $request->get('action') ) {
                case 'save':
                    $params = $request->get('params');

                    $date = !empty($params['date']) ? \Carbon\Carbon::parse($params['date'] .':00') : $booking->date;
                    $status = !empty($params['status']) ? $params['status'] : $booking->status;
                    $commission = !empty($params['commission']) ? (float)$params['commission'] : 0;
                    $cash = !empty($params['cash']) ? (float)$params['cash'] : 0;
                    $refNumber = 'REF-' . date('Ymd-His') . rand(1000, 100000);

                    $driver = User::find((int)$params['driver']);
                    if ( !empty($driver->id) ) {
                        $driver_id = $driver->id;
                        $driver_data = $driver->toJson();
                    }
                    else {
                        $driver_id = 0;
                        $driver_data = null;
                    }

                    $vehicle = \App\Models\Vehicle::find((int)$params['vehicle']);
                    if ( !empty($vehicle->id) ) {
                        $vehicle_id = $vehicle->id;
                        $vehicle_data = $vehicle->toJson();
                    }
                    else {
                        $vehicle_id = 0;
                        $vehicle_data = null;
                    }

                    $newBookingParent = $booking->booking->replicate();
                    $newBookingParent->unique_key = md5('booking_'. date('Y-m-d H:i:s') . rand(1000, 100000));
                    $newBookingParent->ref_number = $refNumber;
                    $newBookingParent->save();

                    $newBooking = $booking->replicate();
                    $newBooking->booking_id = $newBookingParent->id;
                    $newBooking->status = $status;
                    $newBooking->date = $date;
                    $newBooking->driver_id = $driver_id;
                    $newBooking->driver_data = $driver_data;
                    $newBooking->vehicle_id = $vehicle_id;
                    $newBooking->vehicle_data = $vehicle_data;
                    $newBooking->commission = $commission;
                    $newBooking->cash = $cash;
                    $newBooking->ref_number = $refNumber;
                    $newBooking->job_reminder = 0;
                    $newBooking->modified_date = date('Y-m-d H:i:s');
                    $newBooking->created_date = date('Y-m-d H:i:s');
                    $newBooking->is_read = 0;
                    $newBooking->save();

                    if ($newBookingParent->id && $newBooking->id) {
                        $refGenerator = new \App\Models\BookingRoute;
                        $refNumber = $refGenerator->generateRefNumber([
                            'id' => $newBookingParent->id,
                            'pickupDateTime' => $newBooking->date
                        ]);

                        $newBookingParent->ref_number = $refNumber;
                        $newBookingParent->save();

                        $newBooking->ref_number = $refNumber;
                        $newBooking->save();
                    }

                    foreach ($booking->bookingTransactions as $transaction) {
                        $newTransaction = $transaction->replicate();
                        $newTransaction->relation_id = $newBookingParent->id;
                        $newTransaction->unique_key = md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000));
                        $newTransaction->status = 'pending';
                        $newTransaction->ip = null;
                        $newTransaction->response = null;
                        $newTransaction->save();
                    }

                    if ( !empty($newBooking->id) ) {
                        $response['id'] = $newBooking->id;
                        $response['url'] = route('admin.bookings.edit', $newBooking->id);
                    }
                    else {
                        $response['error'] = trans('admin/bookings.copy.message.error_copy');
                    }

                break;
            }

            if ( $request->ajax() ) {
                return $response;
            }
            else {
                return redirect()->back();
            }
        }
        else {
            $drivers = [];
            $query = User::role('driver.*')
                ->where('status', 'approved')
                ->orderBy('name', 'asc')
                ->get();

            foreach($query as $value) {
                $drivers[] = (object)[
                    'value' => $value->id,
                    'text' => $value->getName(true)
                ];
            }

            $vehicles = [];
            $query = \App\Models\Vehicle::where('status', 'activated')
                ->orderBy('selected', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            foreach($query as $value) {
                $vehicles[] = (object)[
                    'value' => $value->id,
                    'text' => $value->getName(),
                    'user_id' => $value->user_id,
                ];
            }

            $statuses = (new BookingRoute)->getStatusList('array');

            return view('admin.bookings.copy', [
                'booking' => $booking,
                'drivers' => $drivers,
                'vehicles' => $vehicles,
                'statuses' => $statuses,
            ]);
        }
    }

    public function inlineEditing($action, Request $request)
    {
        if (!(auth()->user()->hasPermission('admin.bookings.edit') || auth()->user()->hasRole('admin.fleet_operator'))) {
            return redirect_no_permission();
        }

        $pk = $request->get('pk', 0);

        switch( $action ) {
            case 'update_status':

                $booking = BookingRoute::with([
                    'booking' => function($query) {
                        $query->withTrashed();
                    }
                ])->withTrashed()->findOrFail($pk);

                $status = $request->get('value', '');
                $ok = 0;

                foreach($booking->getStatusList() as $key => $value) {
                    if ( $status == $value->value ) {
                        $ok = 1;
                    }
                }

                if ( $ok ) {
                    $this->setBookingActivity($booking, ['status' => ['from'=>$booking->status, 'to' => $status]]);

                    if (!empty($booking->driver_id)) {
                        \App\Helpers\BookingHelper::setActiveDriver($booking, $status, $booking->driver_id);
                    }

                    $booking->status = $status;
                    $booking->is_read = 1;
                    $booking->save();

                    // if ((int)$booking->driver_id !== 0 && in_array($booking->status, $booking->untracking_statuses)) {
                        $booking->setDriverStatus(auth()->user()->id);
                    // }

                    if (config('eto_dispatch.enable_autodispatch') && config('eto_dispatch.assign_driver_on_status_change') &&
                        in_array($booking->status, ['auto_dispatch']) && $booking->driver_id == 0) {
                        \App\Http\Controllers\DispatchDriverController::availableBookings($booking->id);
                    }
                    else {
                        event(new \App\Events\BookingStatusChanged($booking, [], true));
                    }

                    return [
                        'new_value' => $booking->getStatus('label'),
                        'status' => true
                    ];
                }
                else {
                    return [
                        'new_value' => $booking->getStatus('label'),
                        'status' => false
                    ];
                }

            break;
            case 'update_notes':

                $booking = BookingRoute::withTrashed()->findOrFail($pk);
                $booking->notes = $request->get('value', null);
                $booking->is_read = 1;
                $booking->save();

                return [
                    'new_value' => '<i class="fa fa-info-circle"></i>',
                    'status' => true
                ];

            break;
            case 'update_status_notes':

                $booking = BookingRoute::withTrashed()->findOrFail($pk);
                $booking->status_notes = $request->get('value', null);
                $booking->is_read = 1;
                $booking->save();

                return [
                    'new_value' => '<i class="fa fa-info-circle"></i>',
                    'status' => true
                ];

            break;
            case 'update_fleet':

                if (config('eto.allow_fleet_operator') && !auth()->user()->hasRole('admin.fleet_operator')) {
                    if ($request->system->subscription->license_status == 'suspended') {
                        return redirect_no_permission();
                    }

                    $newValue = '';
                    $values = $request->get('value', []);
                    $fleetId = (int)$values['fleet'] ?: 0;
                    $commission = (float)$values['commission'] ?: 0;

                    $booking = BookingRoute::withTrashed()->findOrFail($pk);
                    $fleet = User::find($fleetId);

                    if ( !empty($fleet->id) ) {
                        $booking->fleet_id = $fleet->id;
                        $newValue = $fleet->getName();
                    }
                    else {
                        $booking->fleet_id = 0;
                    }

                    $booking->is_read = 1;
                    $booking->fleet_commission = $commission;
                    $booking->save();

                    return [
                        'new_value' => $newValue,
                        'status' => false
                    ];
                }

            break;
            case 'update_fleet_commission':

                if (config('eto.allow_fleet_operator') && !auth()->user()->hasRole('admin.fleet_operator')) {
                    if ($request->system->subscription->license_status == 'suspended') {
                        return redirect_no_permission();
                    }

                    $booking = BookingRoute::withTrashed()->findOrFail($pk);
                    $booking->fleet_commission = (float)$request->get('value', 0);
                    $booking->is_read = 1;
                    $booking->save();

                    return [
                        'new_value' => $booking->getFleetCommission(),
                        'status' => true
                    ];
                }

            break;
            case 'update_driver':

                if ($request->system->subscription->license_status == 'suspended') {
                    return redirect_no_permission();
                }

                $values = $request->get('value', []);
                $driverId = (int)$values['driver'];
                $vehicleId = (int)$values['vehicle'];
                $commission = (float)$values['commission'];
                $cash = (float)$values['cash'];
                $notification = (int)$values['notification'];

                $booking = BookingRoute::with([
                    'booking' => function($query) {
                        $query->withTrashed();
                    }
                ])->withTrashed()->findOrFail($pk);

                $driver = User::find($driverId);

                if ( !empty($driver->id) ) {
                    $vehicle = \App\Models\Vehicle::where('user_id', $driver->id)
                        ->where('status', 'activated')
                        ->orderBy('selected', 'desc')
                        ->orderBy('name', 'asc')
                        ->find($vehicleId);

                    if ( !empty($vehicle->id) ) {
                        $booking->vehicle_id = $vehicle->id;
                        $booking->vehicle_data = $vehicle->toJson();
                    }
                    else {
                        $booking->vehicle_id = 0;
                        $booking->vehicle_data = null;
                    }

                    $booking->driver_id = $driver->id;
                    $booking->driver_data = $driver->toJson();
                    $booking->status = 'assigned';
                    $booking->commission = $commission;
                    $booking->cash = $cash;
                    $booking->is_read = 1;
                    $booking->save();

                    if ( $notification ) {
                        event(new \App\Events\BookingStatusChanged($booking, [], true));
                    }

                    return [
                        'new_value' => $driver->getName(true),
                        'status' => true
                    ];
                }
                else {
                    $notifications = [[
                        'type' => 'canceled',
                        'role' => [
                            'driver' => []
                        ],
                    ]];

                    if ( $notification ) {
                        event(new \App\Events\BookingStatusChanged($booking, $notifications, [], true));
                    }

                    $booking->driver_id = 0;
                    $booking->driver_data = null;
                    $booking->vehicle_id = 0;
                    $booking->vehicle_data = null;
                    $booking->status = 'pending';
                    $booking->commission = 0;
                    $booking->cash = 0;
                    $booking->is_read = 1;
                    $booking->save();

                    return [
                        'new_value' => '',
                        'status' => false
                    ];
                }

            break;
            case 'update_vehicle':

                $booking = BookingRoute::withTrashed()->findOrFail($pk);

                $vehicle = \App\Models\Vehicle::where('user_id', $booking->driver_id)
                    ->where('id', $request->get('value', 0))
                    ->where('status', 'activated')
                    ->orderBy('selected', 'desc')
                    ->orderBy('name', 'asc')
                    ->first();

                if ( !empty($vehicle->id) ) {
                    $booking->vehicle_id = $vehicle->id;
                    $booking->vehicle_data = $vehicle->toJson();
                    $booking->is_read = 1;
                    $booking->save();

                    return [
                        'new_value' => $vehicle->getName(),
                        'status' => true
                    ];
                }
                else {
                    $booking->vehicle_id = 0;
                    $booking->vehicle_data = null;
                    $booking->is_read = 1;
                    $booking->save();

                    return [
                        'new_value' => '',
                        'status' => false
                    ];
                }

            break;
            case 'update_commission':

                $booking = BookingRoute::withTrashed()->findOrFail($pk);
                $booking->commission = (float)$request->get('value', 0);
                $booking->is_read = 1;
                $booking->save();

                return [
                    'new_value' => $booking->getCommission(),
                    'status' => true
                ];

            break;
            case 'update_cash':

                $booking = BookingRoute::withTrashed()->findOrFail($pk);
                $booking->cash = (float)$request->get('value', 0);
                $booking->is_read = 1;
                $booking->save();

                return [
                    'new_value' => $booking->getCash(),
                    'status' => true
                ];

            break;
            case 'status_list':

                return (new BookingRoute)->getStatusList('json');

            break;
            case 'driver_list':

                return \App\Helpers\BookingHelper::getDriverList('json');

            break;
            case 'vehicle_list':

                return \App\Helpers\BookingHelper::getVehicleList($request->get('id', 0), 'json');

            break;
        }
    }

    protected function setBookingActivity($booking, $updates) {
        activity()
          ->performedOn($booking)
          ->withProperties($updates)
          ->useLog('update')
          ->log('#:subject.ref_number');
    }

    public function transactions($id, Request $request)
    {
        $booking = BookingRoute::with([
            'booking' => function($query) {
                $query->withTrashed();
            },
            'bookingTransactions' => function($query) {
                $query->withTrashed();
            }
        ])->withTrashed()->findOrFail($id);

        $booking->is_read = 1;
        $booking->save();

        // if ( $booking && !empty($booking->booking->site_id) ) {
            /**
             * TODO -> This variable is not used, probobly to remove
             * @var $config
             */
            // $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->mapData()->getData();
        // }

        $payment_methods = \App\Models\Payment::where('site_id', '=', $booking->booking->site_id)
            ->where('published', '=', '1')
            ->orderBy('ordering', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $payments = [];
        foreach($payment_methods as $k => $v) {
            $payments[] = (object)[
                'id' => $v->id,
                'value' => $v->method,
                'text' => $v->name
            ];
        }

        // Actions
        if ( !empty($request->get('action')) ) {
            $transaction = $booking->bookingTransactions()->withTrashed()->where('unique_key', $request->get('tID'))->first();

            if ( $request->get('action') == 'send' ) { // Send payment link to customer

                if ( !empty($transaction->id) ) {
                    $qProfileConfig = \App\Models\Config::getBySiteId($booking->booking->site_id);
                    if (!empty($booking->locale)) {
                        $qProfileConfig->loadLocale($booking->locale);
                    }
                    $pConfig = (array)$qProfileConfig->mapData()->getData();

                    if (!empty($booking->locale)) {
                        app()->setLocale($booking->locale);
                    }
                    elseif (!empty($pConfig['language'])) {
                        app()->setLocale($pConfig['language']);
                    }

                    $eCompany = (object)[
                        'name' => $pConfig['company_name'],
                        'phone' => $pConfig['company_telephone'],
                        'email' => $pConfig['company_email'],
                        'address' => SiteHelper::nl2br2($pConfig['company_address']),
                        'url_home' => $pConfig['url_home'],
                        'url_feedback' => $pConfig['url_feedback'],
                        'url_contact' => $pConfig['url_contact'],
                        'url_booking' => $pConfig['url_booking'],
                        'url_customer' => $pConfig['url_customer']
                    ];

                    $eSettings = (object)[
                        'booking_summary_enable' => $pConfig['booking_summary_enable'],
                    ];

                    $sender = [
                        $eCompany->email,
                        $eCompany->name
                    ];

                    $recipient = [
                        $booking->contact_email,
                        $booking->contact_name
                    ];

                    $subject = trans('emails.customer_payment_requested.subject', [
                        'ref_number' => $booking->getRefNumber()
                    ]);


                    $urlMap = config('site.site_urls');
                    $siteId = 0;
                    $siteKey = '';

                    if (!empty($urlMap) && !empty($urlMap[$booking->booking->site_id])) {
                        $payUrl = $urlMap[$booking->booking->site_id] .'booking';

                        $site = \App\Models\Site::select('id', 'key')->where('published', '1')->find($booking->booking->site_id);
                        if ( !empty($site->id) ) {
                            $siteId = $site->id;
                            $siteKey = $site->key;
                        }
                    }
                    else {
                        $payUrl = url('/booking');
                    }

                    $payUrl .= '?finishType=payment&bID='. $booking->booking->unique_key .'&tID='. $transaction->unique_key .'&pMethod='. $transaction->payment_method .'&locale='. app()->getLocale();

                    if (!empty($siteKey)) {
                        $payUrl .= '&site_key='. $siteKey;
                    }

                    try {
                        $sent = Mail::send([
                              'html' => 'emails.customer_payment_requested',
                              // 'text' => 'emails.customer_payment_requested_plain'
                          ], [
                              'subject' => $subject,
                              'additionalMessage' => '',
                              'company' => $eCompany,
                              'settings' => $eSettings,
                              'booking' => $booking,
                              'price' => SiteHelper::formatPrice($transaction->amount + $transaction->payment_charge),
                              'payUrl' => $payUrl,
                              // 'payUrl' => url('/booking') .'?finishType=payment&bID='. $booking->booking->unique_key .'&tID='. $transaction->unique_key .'&pMethod='. $transaction->payment_method,
                          ],
                          function ($message) use ($sender, $recipient, $subject) {
                              $message->from($sender[0], $sender[1])
                                ->to($recipient[0], $recipient[1])
                                ->subject($subject);
                          }
                        );
                    }
                    catch (\Exception $e) {
                        $sent = false;
                        session()->flash('message', trans('admin/bookings.transaction.message.send_failure'));
                    }

                    session()->flash('message', trans('admin/bookings.transaction.message.send_success'));
                }
                else {
                    session()->flash('message', trans('admin/bookings.transaction.message.send_failure'));
                }

            }
            elseif ( $request->get('action') == 'destroy' ) { // Delete transaction

                if ( !empty($transaction->id) ) {
                    // $transaction->delete();
                    $transaction->forceDelete();

                    session()->flash('message', trans('admin/bookings.transaction.message.destroy_success'));
                }
                else {
                    session()->flash('message', trans('admin/bookings.transaction.message.destroy_failure'));
                }

            }
            elseif ( $request->get('action') == 'inline' ) { // Inline update transaction

                if ( !empty($transaction->id) ) {
                    $new_value = '';

                    switch( $request->get('name') ) {
                        case 'status':
                            foreach($transaction->options->status as $key => $value) {
                                if ( $request->get('value') == $key ) {
                                    $transaction->status = $key;
                                    $transaction->save();
                                    $new_value = $transaction->getStatus('color');
                                    break;
                                }
                            }
                        break;
                        case 'payment_method':
                            foreach($payments as $key => $value) {
                                if ( $request->get('value') == $value->value ) {
                                    $transaction->payment_id = $value->id;
                                    $transaction->payment_method = $value->value;
                                    $transaction->payment_name = $value->text;
                                    $transaction->save();
                                    $new_value = $transaction->getPaymentName();
                                    break;
                                }
                            }
                        break;
                        case 'name':
                            $transaction->name = $request->get('value');
                            $transaction->save();
                            $new_value = $transaction->getName();
                        break;
                        case 'amount':
                            $transaction->amount = $request->get('value');
                            $transaction->save();
                            $new_value = $transaction->getAmount();
                        break;
                        case 'payment_charge':
                            $transaction->payment_charge = $request->get('value');
                            $transaction->save();
                            $new_value = $transaction->getPaymentCharge();
                        break;
                    }

                    if ( !empty($new_value) ) {
                        return [
                            'new_value' => $new_value,
                            'message' => trans('admin/bookings.transaction.message.inline_update_success'),
                            'status' => true
                        ];
                    }
                }

                return [
                    'new_value' => $new_value,
                    'message' => trans('admin/bookings.transaction.message.inline_update_failure'),
                    'status' => false
                ];

            }

            return redirect()->back();
        }

        return view('admin.bookings.transactions', [
            'booking' => $booking,
            'transactions' => $booking->bookingTransactions,
            'payments' => $payments
        ]);
    }

    public function meetingBoard($id, Request $request)
    {
        $booking = BookingRoute::withTrashed()->findOrFail($id);
        $booking->is_read = 1;
        $booking->save();

        if ($request->get('action') && $request->get('action') == 'download') {

            $filename = trans('admin/bookings.subtitle.meeting_board') .'.pdf';
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
            return view('admin.bookings.meeting_board', [
                'booking' => $booking,
            ]);
        }
    }

    public function download($id = 0, $fileId = 0)
    {
        $booking = BookingRoute::findOrFail($id);

        if ($booking->id) {
            $query = \DB::table('file')
                ->where('file_relation_type', 'booking')
                ->where('file_relation_id', $booking->id)
                ->where('file_id', $fileId)
                ->first();

            if (!empty($query)) {
                $filePath = asset_path('uploads','safe/'. $query->file_path);
                return response()->download($filePath, $query->file_path);
            }
        }

        return false;
    }
}
