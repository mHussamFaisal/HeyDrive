<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\SiteHelper;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\Activitylog\Traits\LogsActivity;

class BookingRoute extends Model
{
    use SoftDeletes;
    // use LogsActivity;

    // protected static $logAttributes = ['*'];
    // protected static $logOnlyDirty = true;

    protected $table = 'booking_route';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'date',
        'modified_date',
        'created_date'
    ];

    public $timestamps = false;
    public $options = [];
    public $charges = [];
    protected $softDelete = true;

    public $init_statuses = [
        'accepted',
        'rejected'
    ];

    public $tracking_statuses = [
        'onroute',
        'arrived',
        'onboard',
    ];

    public $untracking_statuses = [
        'completed',
        'canceled',
        'unfinished',
    ];

    public $customer_statuses = [
        'onroute',
        'arrived',
        'onboard',
        'completed',
    ];

    public $passenger_statuses = [
        'onroute',
        'arrived',
        'onboard',
        'completed',
    ];

    public $driver_statuses = [
        'accepted',
        'rejected',
        'onroute',
        'arrived',
        'onboard',
        'completed',
    ];

    public $admin_statuses = [
        'accepted',
        'rejected',
        'onroute',
        'arrived',
        'onboard',
        'completed',
    ];

    public $allowed_statuses = [];

    function __construct()
    {
        // This is needed for SoftDeletes to work properly. Without it "is not null" was not added to query in some cases eg. driver booking listing.
        parent::__construct();

        $this->allowed_statuses = array_merge($this->init_statuses, $this->tracking_statuses, $this->untracking_statuses);

        $this->options = (object)[
            'status' => [
                'pending' => [
                    // 'name' => trans('common.booking_status_options.pending'),
                    // 'color' => '#f39c12'
                    'name' => trans('common.booking_status_options.confirmed'),
                    'color' => config('eto_booking.status_color.pending')
                ],
                'requested' => [
                    'name' => trans('common.booking_status_options.requested'),
                    'color' => config('eto_booking.status_color.requested')
                ],
                'quote' => [
                    'name' => trans('common.booking_status_options.quote'),
                    'color' => config('eto_booking.status_color.quote')
                ],
                // Todo: Remove confirm status from the system as it is not used anywhere.
                // 'confirmed' => [
                //     'name' => trans('common.booking_status_options.confirmed'),
                //     'color' => '#2d34d9'
                // ],
                'auto_dispatch' => [
                    'name' => trans('common.booking_status_options.auto_dispatch'),
                    'color' => config('eto_booking.status_color.auto_dispatch')
                ],
                'assigned' => [
                    'name' => trans('common.booking_status_options.assigned'),
                    'color' => config('eto_booking.status_color.assigned')
                ],
                'accepted' => [
                    'name' => trans('common.booking_status_options.accepted'),
                    'color' => config('eto_booking.status_color.accepted')
                ],
                'rejected' => [
                    'name' => trans('common.booking_status_options.rejected'),
                    'color' => config('eto_booking.status_color.rejected')
                ],
                'onroute' => [
                    'name' => trans('common.booking_status_options.onroute'),
                    'color' => config('eto_booking.status_color.onroute')
                ],
                'arrived' => [
                    'name' => trans('common.booking_status_options.arrived'),
                    'color' => config('eto_booking.status_color.arrived')
                ],
                'onboard' => [
                    'name' => trans('common.booking_status_options.onboard'),
                    'color' => config('eto_booking.status_color.onboard')
                ],
                'completed' => [
                    'name' => trans('common.booking_status_options.completed'),
                    'color' => config('eto_booking.status_color.completed')
                ],
                'canceled' => [
                    'name' => trans('common.booking_status_options.canceled'),
                    'color' => config('eto_booking.status_color.canceled')
                ],
                'unfinished' => [
                    'name' => trans('common.booking_status_options.unfinished'),
                    'color' => config('eto_booking.status_color.unfinished')
                ],
                'incomplete' => [
                    'name' => trans('common.booking_status_options.incomplete'),
                    'color' => config('eto_booking.status_color.incomplete')
                ],
            ]
        ];
    }

    public function getParamsAttribute($value)
    {
        return empty($this->attributes['params']) ? new \stdClass() : \GuzzleHttp\json_decode($this->attributes['params']);
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = !empty($value) && count((array)$value) ? \GuzzleHttp\json_encode($value) : null;
    }

    public function getTracking($driverId = null, $timestamp = false, $getTimestamp = true)
    {
        $tracking = [];
        $dir = 'bookings' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'drivers';

        if ($driverId) {
            $dir .= DIRECTORY_SEPARATOR . $driverId;
        }

        if (\Storage::disk('archive')->exists($dir)) {
            if ($driverId && \Storage::disk('archive')->exists($dir . DIRECTORY_SEPARATOR . 'tracking.json')) {
                $file = \Storage::disk('archive')->get($dir . DIRECTORY_SEPARATOR . 'tracking.json');

                if ($file) {
                    $file = json_decode($file);

                    foreach($file as $iid=>$item) {
                        $tracking[$driverId][$iid] = new \stdClass();
                        $tracking[$driverId][$iid]->lat = $item[0];
                        $tracking[$driverId][$iid]->lng = $item[1];
                        if ($getTimestamp) {
                            $tracking[$driverId][$iid]->timestamp = $item[2];
                        }
                    }
                }
            }
            else {
                foreach (\Storage::disk('archive')->directories($dir) as $dirPath) {
                    if (\File::exists($dirPath . DIRECTORY_SEPARATOR . 'tracking.json')) {
                        $file = \File::get($dirPath . DIRECTORY_SEPARATOR . 'tracking.json');
                        if ($file) {
                            $file = json_decode($file);
                            foreach($file as $iid=>$item) {
                                $tracking[basename($dirPath)][$iid] = new \stdClass();
                                $tracking[basename($dirPath)][$iid]->lat = $item[0];
                                $tracking[basename($dirPath)][$iid]->lng = $item[1];
                                if ($getTimestamp) {
                                    $tracking[basename($dirPath)][$iid]->timestamp = $item[2];
                                }
                            }
                        }
                    }
                }
            }
        }
        else {
            $trackingActive = \App\Models\BookingDriverTracking::where('booking_id', $this->id);
            if ($driverId) {
                $trackingActive->where('driver_id', $driverId);
            }

            if ($timestamp) {
                $trackingActive->where('timestamp', '>', $timestamp);
            }

            foreach($trackingActive->get() as $iid=>$item) {
                $tracking[$item->driver_id][$iid] = new \stdClass();
                $tracking[$item->driver_id][$iid]->lat = $item->lat;
                $tracking[$item->driver_id][$iid]->lng = $item->lng;
                if ($getTimestamp) {
                    $tracking[$item->driver_id][$iid]->timestamp = $item->timestamp;
                }
            }
        }

        return $tracking;
    }

    public function getDriverStatuses($userId = false, $timestamp = false)
    {
        $driverStatuses = [];
        // $statuses = BookingStatus::where('booking_id', $this->id);
        $statuses = $this->bookingStatuses();

        if ($timestamp) {
            $statuses->where('timestamp', '>', Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s'));
        }

        if ($userId) {
            $statuses->where('user_id', $userId);
            $driverStatuses = $statuses->get();
            // $driverStatuses = $statuses;
        }
        else {
            $statuses = $statuses->get();
            // $statuses = $statuses;

            foreach ($statuses as $status) {
                $driverStatuses[$status->user_id][] = $status;
            }
        }

        if (request()->ajax()) {
            $statuses = [];

            if ($userId) {
                foreach ($driverStatuses as $ids => $status) {
                    $statuses[$status->timestamp->timestamp] = [
                        'lat' => $status->lat,
                        'lng' => $status->lng,
                        'status' => $status->status,
                        'user_id' => $status->user_id
                    ];
                }
            }
            else {
                foreach ($driverStatuses as $idd => $driver) {
                    foreach ($driver as $ids => $status) {
                        $statuses[$status->timestamp->timestamp] = [
                            'lat' => $status->lat,
                            'lng' => $status->lng,
                            'status' => $status->status,
                            'user_id' => $status->user_id
                        ];
                    }
                }
            }

            $driverStatuses = $statuses;
        }
        return $driverStatuses;
    }

    public function getDriverStatusesHtml($typeUser = 'admin', $format = 'table', $userId = false, $timestamp = false)
    {
        $statuses = $this->getDriverStatuses($userId, $timestamp);
        $userStatusesVar = $typeUser . '_statuses';
        $html = '';
        $hasItems = false;

        if (!empty($statuses)) {
            if ($format == 'table') {
                if ($typeUser != 'customer') {
                    $html .= '<table class="table table-condensed">
                        <thead>
                        <tr><th colspan="2"><b>' . trans('booking.heading.status_history') . '</b></th></tr>
                        </thead><tbody>';
                }
                else {
                    $html .= '<p><b>' . trans('booking.heading.status_history') . '</b></p>
                    <table class="table table-condensed"><tbody>';
                }
            }
            elseif ($format == 'list') {
                $html .= '<ul class="list-group list-group-unbordered details-list">
                      <li class="list-group-item">
                          <span class="details-list-title">' . trans('booking.heading.status_history') . '</span>
                          <span class="details-list-value"></span>
                      </li>';
            }
            elseif ($format == 'object') {
                $objectStatuses = [];
            }

            if (!empty($statuses) && count((array)$statuses) > 0) {
                foreach ($statuses as $user => $userStatus) {
                    if (!empty($userStatus['status'])) {
                        $userStatus = (object)$userStatus;

                        if (empty($userStatus->timestamp)) {
                            $userStatus->timestamp = Carbon::createFromTimestamp($user)->format('Y-m-d H:i:s');
                        }
                    }

                    if (!empty($userStatus->status)) {
                        $userStatus = [$userStatus];
                    }

                    foreach ($userStatus as $status) {
                        if (in_array($status->status, $this->$userStatusesVar)) {
                            $statusName = $this->options->status[$status->status]['name'];
                            $statusDate = format_date_time($status->timestamp, 'datetime');
                            $pinMap = !empty($status->lat)
                                ? ' <i class="fa fa-map-marker eto-show-status-on-map hidden" data-eto-lat="' . $status->lat . '" data-eto-lng="' . $status->lng . '" ></i >'
                                : '';

                            if ($format == 'table') {
                                $hasItems = true;
                                if ($typeUser != 'customer' && $typeUser != 'admin') {
                                    $html .= '<tr><td>' . $statusName . ':</td><td>' . $statusDate . $pinMap . '</td></tr>';
                                }
                                else {
                                    $html .= '<tr><td class="col-xs-4 col-sm-4">' . $statusName . ':</td>'.
                                              '<td class="col-xs-8 col-sm-8 col-md-8">'. $statusDate . $pinMap . '</td></tr>';
                                }
                            }
                            elseif ($format == 'list') {
                                $hasItems = true;
                                $html .= '<li class="list-group-item"><span class="details-list-title">'
                                    . $statusName . ':</span><span class="details-list-value">'
                                    . $statusDate . $pinMap . '</span></li>';
                            }
                            elseif ($format == 'export') {
                                if (!empty($html)) {
                                    $html .= " | "; //  . PHP_EOL
                                }
                                $html .= $statusName . ', ' . $statusDate;

                                if (!empty($status->lat)) {
                                    $html .= ', ' . $status->lat . ', ' . $status->lng;
                                }
                            }
                            elseif ($format == 'object') {
                                $objectStatuses[] = (object)[
                                    // 'status' => $status->status,
                                    'name' => $statusName,
                                    'lat' => $status->lat,
                                    'lng' => $status->lng,
                                    // 'timestamp' => $status->timestamp,
                                    'date' => $statusDate,
                                ];
                            }
                        }
                    }
                }
            }

            if ($format == 'table') {
                $html .= '</tbody></table>';
            }
            elseif ($format == 'list') {
                $html .= '</ul>';
            }

            if (!$hasItems && ($format == 'table' || $format == 'list')) {
                $html = '';
            }

            if ($format == 'object') {
                return $objectStatuses;
            }
        }
        return $html;
    }

    public function setDriverStatus($userId = false)
    {
        auth()->user()->getParams();
        $now = Carbon::now();
        $lct = !empty(auth()->user()->params->last_coordinates_timestamp) ? auth()->user()->params->last_coordinates_timestamp : null;

        $status = new BookingStatus();
        $status->booking_id = $this->id;
        $status->user_id = $userId ?: $this->driver_id;
        $status->status = $this->status;
        $status->timestamp = $now;
        $diff = null;

        if (null !== $lct) {
            $lct = \Carbon\Carbon::createFromTimestamp($lct);
            if ($diff = $lct->diffInMinutes($now) <= 2) {
                $status->lat = auth()->user()->lat;
                $status->lng = auth()->user()->lng;
            }
        }

        $status->save();

        if (in_array($this->status, $this->untracking_statuses)) {
            \App\Models\BookingParam::where('key', 'access_uuid')->where('booking_id', $this->id)->delete();

            $dir = 'bookings' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR . 'drivers';

            if ($tracking = BookingDriverTracking::where('booking_id', $this->id)->orderBy('timestamp', 'asc')->get()) {
                $coordinates = [];

                foreach ($tracking as $item) {
                    if (!empty($item->driver_id)) {
                        $coordinates[$item->driver_id][] = [
                            $item->lat,
                            $item->lng,
                            $item->timestamp,
                        ];
                    }
                }

                if (!empty($coordinates)) {
                    if (!empty($diff) && $diff > 2) {
                        $lastCoordinates = end($coordinates[$this->driver_id]);
                        $status->lat = $lastCoordinates[0];
                        $status->lng = $lastCoordinates[1];
                        $status->save();
                    }

                    foreach ($coordinates as $driver => $item) {
                        \Storage::disk('archive')->makeDirectory($dir . DIRECTORY_SEPARATOR . $driver, 0755, true, true);
                        \Storage::disk('archive')->put(
                            $dir . DIRECTORY_SEPARATOR . $driver . DIRECTORY_SEPARATOR . 'tracking.json', json_encode($coordinates[$driver])
                        );
                    }
                }

                \DB::table((new BookingDriverTracking)->getTable())->where('booking_id', $this->id)->delete();
            }
        }
        elseif (in_array($this->status, $this->tracking_statuses)) {
            $param = \App\Models\BookingParam::where('key', 'access_uuid')->where('booking_id', $this->id)->first();
            if (!$param) {
                $param = new \App\Models\BookingParam();
                $param->booking_id = $this->id;
                $param->key = 'access_uuid';
                $param->value = uuid();
                $param->save();
            }
        }
    }

    public static function scopeScheduledConfirmed($query)
    {
        return $query->whereNotIn('status', ['canceled', 'incomplete']);
    }

    public static function scopeWhereDriver($query, $userId)
    {
        $query->where(function($query) use($userId) {
            $query->whereHas('parentBooking', function($query) use($userId) {
                $query->where('booking_route.driver_id', $userId);
            });

            $query->orWhere('booking_route.driver_id', $userId);

            $query->orWhereHas('bookingDrivers', function($query) use($userId) {
                $query->where('booking_drivers.driver_id', $userId);
            });
        });

        return $query;
    }

    public static function scopeWithBookingDriver($query, $userId, $select = true)
    {
        $prefix = get_db_prefix();

        if ($select) {
            $query->addSelect('booking_route.*');
            $query->addSelect(\DB::raw("`{$prefix}booking_drivers`.`expired_at` as `expired_at`"));
            $query->addSelect(\DB::raw("IFNULL(`{$prefix}booking_drivers`.`commission`, `{$prefix}booking_route`.`commission`) as `commission`"));
            $query->addSelect(\DB::raw("IFNULL(`{$prefix}booking_drivers`.`cash`, `{$prefix}booking_route`.`cash`) as `cash`"));
            $query->addSelect(\DB::raw(self::__driverStatusSql() ." as `status`"));
        }

        $query->leftJoin('booking_drivers', function($join) use($userId) {
            $join->on('booking_drivers.booking_id', '=', 'booking_route.id');
            $join->on('booking_drivers.driver_id', '=', \DB::raw("{$userId}"));
        });

        return $query;
    }

    public static function scopeWhereInDriverStatus($query, $status)
    {
        return $query->whereIn(DB::raw(self::__driverStatusSql()), $status);
    }

    public static function __driverStatusSql()
    {
        $prefix = get_db_prefix();

        $sql = "(case
          when (`{$prefix}booking_drivers`.`status` = 2) then 'rejected'
          when (`{$prefix}booking_route`.`status` = 'pending') then 'assigned'
          when (`{$prefix}booking_route`.`status` = 'requested') then 'assigned'
          when (`{$prefix}booking_route`.`status` = 'quote') then 'assigned'
          when (`{$prefix}booking_route`.`status` = 'auto_dispatch') then 'assigned'
          else (`{$prefix}booking_route`.`status`)
        end)";

        return $sql;
    }

    public static function scopeToSqlWithBindings($query, $clear = false)
    {
        return \App\Helpers\DatabeseHelper::toSqlWithBindings($query, $clear);
    }

    public function booking()
    {
        return $this->belongsTo('App\Models\Booking');
    }

    public function parentBooking()
    {
        return $this->hasOne('App\Models\BookingRoute', 'id', 'parent_booking_id');
    }

    public function childBookings()
    {
        return $this->hasMany('App\Models\BookingRoute', 'parent_booking_id', 'id');
    }

    public function bookingAllRoutes()
    {
        return $this->hasMany('App\Models\BookingRoute', 'booking_id', 'booking_id');
    }

    // public function transactions()
    // {
    //     $transactions = \App\Models\Transaction::where('relation_type', '=', 'booking')
    //         ->where('relation_id', '=', $this->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();
    //
    //     return $transactions;
    // }

    public function bookingTransactions()
    {
        return $this->hasMany('App\Models\Transaction', 'relation_id', 'booking_id')
            ->where('relation_type', 'booking')
            ->orderBy('created_at', 'desc');
    }

    public function bookingFeedback()
    {
        return $this->hasMany('App\Models\Feedback', 'ref_number', 'ref_number')->orderBy('created_at', 'desc');
    }

    public function bookingService()
    {
        return $this->hasOne('App\Models\Service', 'id', 'service_id');
    }

    public function bookingScheduledRoute()
    {
        return $this->hasOne('App\Models\ScheduledRoute', 'id', 'scheduled_route_id');
    }

    public function bookingParams()
    {
        return $this->hasMany('App\Models\BookingParam', 'booking_id', 'id');
    }

    public function bookingStatuses()
    {
        return $this->hasMany('App\Models\BookingStatus', 'booking_id', 'id');
    }

    public function bookingDrivers()
    {
        return $this->hasMany('App\Models\BookingDriver', 'booking_id', 'id');
    }

    public function bookingFleet()
    {
        return $this->hasOne('App\Models\User', 'id', 'fleet_id');
    }

    public function bookingDriver()
    {
        return $this->hasOne('App\Models\User', 'id', 'driver_id');
    }

    public function bookingVehicle()
    {
        return $this->hasOne('App\Models\Vehicle', 'id', 'vehicle_id');
    }

    public function assignedCustomer()
    {
        $customer = null;

        if (!empty($this->booking->user_id)) {
            $customer = DB::table('user')
              ->join('user_customer', 'user.id', '=', 'user_customer.user_id')
              ->select('user.*', 'user_customer.*')
              ->where('user.id', $this->booking->user_id)
              ->first();
        }

        return $customer;
    }

    public function assignedDriver()
    {
        $driver = null;

        if ($this->driver_id) {
            $driver = $this->bookingDriver;
        }

        if (empty($driver)) {
            $driver = new \App\Models\User;
            $driver->profile = new \App\Models\UserProfile;
        }

        return $driver;
    }

    public function assignedVehicle()
    {
        $vehicle = null;

        if ($this->vehicle_id) {
            $vehicle = $this->bookingVehicle;
        }

        if (empty($vehicle)) {
            $vehicle = new \App\Models\Vehicle;
        }

        return $vehicle;
    }

    public function getStatus($type = '')
    {
        $value = ucfirst($this->status);

        if ( !empty($this->options->status[$this->status]) ) {
            $status = $this->options->status[$this->status];

            switch( $type ) {
                case 'label':
                    $value = '<span class="label" style="background:'. $status['color'] .';">'. $status['name'] .'</span>';
                break;
                case 'color':
                    $value = '<span style="color:'. $status['color'] .';">'. $status['name'] .'</span>';
                break;
                case 'color_value':
                    $value = $status['color'];
                break;
                default:
                    $value = $status['name'];
                break;
            }
        }

        return $value;
    }

    public function getStatusList($type = 'none')
    {
        $value = [];

        foreach($this->options->status as $k => $v) {
            if (!config('eto_dispatch.enable_autodispatch') && $k == 'auto_dispatch') {
                continue;
            }

            $value[] = (object)[
                'value' => $k,
                'text' => $v['name'],
                'color' => $v['color'],
            ];
        }

        if ($type == 'json') {
            $value = json_encode($value);
        }

        return $value;
    }

    public function getDirections()
    {
        return $this->getOriginal('address_start') .' - '. $this->getOriginal('address_end');
    }

    public function getFrom($type = '')
    {
        $address = '';
        $complete = '';
        $lat = null;
        $lng = null;

        if ( !empty($this->getOriginal('address_start')) ) {
            $address = $this->getOriginal('address_start');
        }
        if ( !empty($this->getOriginal('address_start_complete')) ) {
            $complete = str_replace(array("\r\n", "\r", "\n"), ' ', $this->getOriginal('address_start_complete'));
        }
        if ( !empty($this->getOriginal('coordinate_start_lat')) ) {
            $lat = $this->getOriginal('coordinate_start_lat');
        }
        if ( !empty($this->getOriginal('coordinate_start_lon')) ) {
            $lng = $this->getOriginal('coordinate_start_lon');
        }

        if ( $type == 'raw' ) {
            $value = (object)[
                'address' => $address,
                'complete' => $complete,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }
        else {
            $value = $address;

            if ( $complete ) {
                if ( $type == 'no_html' ) {
                    $value .= ', '. $complete;
                }
                else {
                    $value .= ', <span style="color:gray;">'. $complete .'</span>';
                }
            }
        }

        return $value;
    }

    public function getTo($type = '')
    {
        $address = '';
        $complete = '';
        $lat = null;
        $lng = null;

        if ( !empty($this->getOriginal('address_end')) ) {
            $address = $this->getOriginal('address_end');
        }
        if ( !empty($this->getOriginal('address_end_complete')) ) {
            $complete = str_replace(array("\r\n", "\r", "\n"), ' ', $this->getOriginal('address_end_complete'));
        }
        if ( !empty($this->getOriginal('coordinate_end_lat')) ) {
            $lat = $this->getOriginal('coordinate_end_lat');
        }
        if ( !empty($this->getOriginal('coordinate_end_lon')) ) {
            $lng = $this->getOriginal('coordinate_end_lon');
        }

        if ( $type == 'raw' ) {
            $value = (object)[
                'address' => $address,
                'complete' => $complete,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }
        else {
            $value = $address;

            if ( $complete ) {
                if ( $type == 'no_html' ) {
                    $value .= ', '. $complete;
                }
                else {
                    $value .= ', <span style="color:gray;">'. $complete .'</span>';
                }
            }
        }

        return $value;
    }

    public function getVia($type = '')
    {
        $via = [];

        if ( !empty($this->getOriginal('waypoints')) ) {
            $viaAddress = json_decode($this->getOriginal('waypoints'), true);
            $viaComplete = json_decode($this->getOriginal('waypoints_complete'), true);

            foreach( $viaAddress as $k => $v ) {
                $address = '';
                $complete = '';
                $lat = null;
                $lng = null;

                if ( !empty($viaAddress[$k]) ) {
                    $address = $viaAddress[$k];
                }

                if ( !empty($viaComplete[$k]) ) {
                    $complete = str_replace(array("\r\n", "\r", "\n"), ' ', $viaComplete[$k]);
                }

                $via[] = (object)[
                    'address' => $address,
                    'complete' => $complete,
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            }
        }

        if ( $type == 'raw' ) {
            $value = $via;
        }
        else {
            $value = '';

            foreach( $via as $k => $v ) {
                if ( !empty($value) ) {
                    if ( $type == 'no_html' ) {
                        $value .= "\r\n";
                    }
                    else {
                        $value .= '<br />';
                    }
                }

                $value .= $v->address;

                if ( $v->complete ) {
                    if ( $type == 'no_html' ) {
                        $value .= ', '. $v->complete;
                    }
                    else {
                        $value .= ', <span style="color:gray;">'. $v->complete .'</span>';
                    }
                }
            }
        }

        return $value;
    }

    public function getContactFullName()
    {
        $value = '';
        if ( $this->contact_name ) {
            $value = trim(ucfirst($this->contact_title) .' '. ucfirst($this->contact_name));
        }
        return $value;
    }

    public function getLeadPassengerFullName()
    {
        $value = '';
        if ( $this->lead_passenger_name ) {
            $value = trim(ucfirst($this->lead_passenger_title) .' '. ucfirst($this->lead_passenger_name));
        }
        return $value;
    }

    public function getFeedbackLink()
    {
        if (config('site.feedback_type')) {
            $params = [
                'ref_number' => $this->ref_number,
            ];

            if (!empty(session('locale'))) {
                $params['locale'] = session('locale');
            }
            elseif (!empty($this->locale)) {
                $params['locale'] = $this->locale;
            }
            elseif (!empty($this->booking->site_id)) {
                $settings = \App\Models\Config::ofSite($this->booking->site_id)->whereKeys(['language'])->toObject();

                if (!empty($settings->language)) {
                    $params['locale'] = $settings->language;
                }
            }
            else {
                $params['locale'] = app()->getLocale();
            }

            $site = \App\Models\Site::find($this->booking->site_id);

            if (!empty($site->key)) {
                $params['site_key'] = $site->key;
            }

            return route('feedback.create', $params);
        }
        else {
            return config('site.url_feedback');
        }
    }

    public function getTelLink($key, $params = [])
    {
        $class = '';
        $style = '';

        if ( !empty($params) ) {
            if ( !empty($params['class']) ) {
                $class = 'class="'. $params['class'] .'"';
            }

            if ( !empty($params['style']) ) {
                $style = 'style="'. $params['style'] .'"';
            }
        }

        return !empty($this->{$key}) ? '<a href="tel:'. $this->{$key} .'" '. $class .' '. $style .'>'. $this->{$key} .'</a>' : '';
    }

    public function getEmailLink($key, $params = [])
    {
        $class = '';
        $style = '';

        if ( !empty($params) ) {
            if ( !empty($params['class']) ) {
                $class = 'class="'. $params['class'] .'"';
            }

            if ( !empty($params['style']) ) {
                $style = 'style="'. $params['style'] .'"';
            }
        }

        return !empty($this->{$key}) ? '<a href="mailto:'. $this->{$key} .'" '. $class .' '. $style .'>'. $this->{$key} .'</a>' : '';
    }

    public function getRefNumber()
    {
        return '#'. $this->ref_number;
    }

    public function getTotal($type = '', $format = '')
    {
        $data = (object)[
            'total_return' => 0,
            'total' => 0,
            'remaining' => 0,
            'payment_amount' => 0,
            'payment_charge' => 0,
            'payment_total' => 0,
            'payment_paid' => 0,
            'payment_list' => [],
            'cash' => 0,
        ];

        $data->total = $this->total_price - $this->discount;

        $allRoutes = $this->bookingAllRoutes;

        $secondRoute = null;
        $currentId = $this->id;
        foreach ($allRoutes as $k => $v) {
            if ($v->id != $currentId) {
                $secondRoute = $v;
                break;
            }
        }

        if ( !empty($secondRoute) ) {
            $data->total_return = $secondRoute->total_price - $secondRoute->discount;
        }

        $transactions = $this->bookingTransactions;

        foreach( $transactions as $transaction ) {
            if ( !empty($secondRoute) && ($data->total + $data->total_return) ) {
                $amount = ($transaction->amount / ($data->total + $data->total_return)) * $data->total;
                $charge = ($transaction->payment_charge / ($data->total + $data->total_return)) * $data->total;
            }
            else {
                $amount = $transaction->amount;
                $charge = $transaction->payment_charge;
            }

            $amount = round($amount, 2);
            $charge = round($charge, 2);
            $total = round($amount + $charge, 2);

            $data->payment_amount += $amount;
            $data->payment_charge += $charge;
            $data->payment_total += $total;

            if ($transaction->status == 'paid') {
                $data->payment_paid += $total;
            }

            if ($transaction->payment_method == 'cash') {
                $data->cash += $total;
            }

            $data->payment_list[] = (object)[
                'title' => $transaction->getName(),
                'status' => $transaction->getStatus(),
                'status_color' => $transaction->getStatus('color'),
                'name' => $transaction->getPaymentName(),
                'method' => $transaction->getPaymentMethod(),
                'amount' => $amount,
                'charge' => $charge,
                'total' => $total,
                'formatted' => (object)[
                    'amount' => SiteHelper::formatPrice($amount),
                    'charge' => SiteHelper::formatPrice($charge),
                    'total' => SiteHelper::formatPrice($total)
                ]
            ];
        }

        $data->total = round($data->total + $data->payment_charge, 2);
        $data->payment_paid = round($data->payment_paid, 2);
        $data->remaining = $data->payment_paid - $data->total;

        switch( $type ) {
            case 'payment_amount':
                $value = $data->payment_amount;
            break;
            case 'payment_charge':
                $value = $data->payment_charge;
            break;
            case 'payment_total':
                $value = $data->payment_total;
            break;
            case 'payment_paid':
                $value = $data->payment_paid;
            break;
            case 'payment_list':
                $value = $data->payment_list;
            break;
            case 'data':
                $value = $data;
            break;
            default:
                $value = $data->total;
            break;
        }

        switch( $format ) {
            case 'raw':
                $value = $value;
            break;
            default:
                $value = SiteHelper::formatPrice($value);
            break;
        }

        return $value;
    }

    public function getExcludePerRoute()
    {
        $exclude = 0;
        $this->getCharges('route');

        if ((int)config('eto_booking.discount.child_seats') === 0) {
            $exclude += $this->charges->child_seats;
        }
        if ((int)config('eto_booking.discount.additional_items') === 0) {
            $exclude += $this->charges->additional_items;
        }
        if ((int)config('eto_booking.discount.parking_charges') === 0) {
            $exclude += $this->charges->parking_charges;
        }
        if ((int)config('eto_booking.discount.payment_charges') === 0) {
            $exclude += $this->charges->payment_charges;
        }
        if ((int)config('eto_booking.discount.meet_and_greet') === 0) {
            $exclude += $this->charges->meet_and_greet;
        }

        return  $exclude;
    }

    public function getCharges($formatType = '', $dataType = 'income')
    {
        $routeCharges = [
            'id' => 0,
            'total' => 0,
            'child_seats' => 0,
            'additional_items' => 0,
            'parking_charges' => 0,
            'payment_charges' => 0,
            'meet_and_greet' => 0,
        ];

        if ($dataType == 'income') {
            $routeCharges['discounts'] = 0;
        }

        $charges = (object)[
            'total' => 0,
            'routes' => []
        ];

        $charges->routes[$this->route] = (object)$routeCharges;
        $charges->routes[$this->route]->id = $this->id;
        $charges->routes[$this->route]->total = $this->total_price - $this->discount;

        if ($this->id) {
            $charges->routes[$this->route]->id = $this->id;
        }
        if ($dataType == 'income' && $this->discount) {
            $charges->routes[$this->route]->discounts = $this->discount;
        }

        $charges->total = $charges->routes[$this->route]->total;

        $secondRoute = null;
        $currentId = $this->id;
        foreach ($this->bookingAllRoutes as $k => $v) {
            if ($v->id != $currentId) {
                $secondRoute = $v;
                break;
            }
        }

        $returnRoute = ($formatType != 'route' && $this->id) ? $secondRoute : null;

        if (!empty($returnRoute)) {
            $charges->routes[$returnRoute->route] = (object)$routeCharges;
            $charges->routes[$returnRoute->route]->id = $returnRoute->id;
            $charges->routes[$returnRoute->route]->total = $returnRoute->total_price - $returnRoute->discount;
            if ($returnRoute->id) {
                $charges->routes[$returnRoute->route]->id = $returnRoute->id;
            }
            if ($dataType == 'income' && $returnRoute->discount) {
                $charges->routes[$returnRoute->route]->discounts = $returnRoute->discount;
            }
            $charges->total += $charges->routes[$returnRoute->route]->total;
        }

        // get payment charges with total
        if ($transactions = $this->bookingTransactions) {
            foreach ($transactions as $transaction) {
                if ($returnRoute && ($charges->routes[$this->route]->total + $charges->routes[$returnRoute->route]->total)) {
                    $charges->routes[$this->route]->payment_charges
                        += ($transaction->payment_charge / ($charges->routes[$this->route]->total + $charges->routes[$returnRoute->route]->total)) * $charges->routes[$this->route]->total;

                    $charges->routes[$returnRoute->route]->payment_charges
                        += ($transaction->payment_charge / ($charges->routes[$this->route]->total + $charges->routes[$returnRoute->route]->total)) * $charges->routes[$returnRoute->route]->total;
                }
                else {
                    $charges->routes[$this->route]->payment_charges += $transaction->payment_charge;
                }
            }

            $charges->total += $charges->routes[$this->route]->payment_charges;

            if ($returnRoute){
                $charges->total += $charges->routes[$returnRoute->route]->payment_charges;
            }
        }

        $charges->total = round($charges->total, 2);

        // Get charges from items
        if ($items = json_decode($this->items)) {
            foreach($items as $item) {
                if ($item->type) { // item_key
                    $item->value = $item->value ?: (!empty($item->total) ? $item->total : 0);
                    $itemTotal = $item->value ? ($item->value) * ($item->amount > 0 ? $item->amount : 1) : 0;

                    if (preg_match('#^other#', $item->type)) {
                        $charges->routes[$this->route]->additional_items += $itemTotal;
                    }
                    elseif ( $item->type == 'parking') {
                        $charges->routes[$this->route]->parking_charges += $itemTotal;
                    }
                    elseif ( $item->type == 'meet_and_greet') {
                        $charges->routes[$this->route]->meet_and_greet += $itemTotal;
                    }
                    elseif (in_array($item->type, ['infant_seat', 'child_seat', 'baby_seat'])) {
                        $charges->routes[$this->route]->child_seats += $itemTotal;
                    }
                }
            }
        }

        if (!empty($returnRoute) && $returnItems = json_decode($returnRoute->items)) {
            foreach($returnItems as $item) {
                if ($item->type) {
                    $item->value = $item->value ?: (!empty($item->total) ? $item->total : 0);
                    $itemTotal = $item->value ? ($item->value) * ($item->amount > 0 ? $item->amount : 1) : 0;

                    if (preg_match('#^other#', $item->type)) {
                        $charges->routes[$returnRoute->route]->additional_items += $itemTotal;
                    }
                    elseif ( $item->type == 'parking') {
                        $charges->routes[$returnRoute->route]->parking_charges += $itemTotal;
                    }
                    elseif ( $item->type == 'meet_and_greet') {
                        $charges->routes[$returnRoute->route]->meet_and_greet += $itemTotal;
                    }
                    elseif (in_array($item->type, ['infant_seat', 'child_seat', 'baby_seat'])) {
                        $charges->routes[$returnRoute->route]->child_seats += $itemTotal;
                    }
                }
            }
        }

        switch( $formatType ) {
            case 'route':
                $charges = $charges->routes[$this->route];
            break;
        }

        $this->charges = $charges;
        return $charges;
    }

    public function getFleetCommission()
    {
        return SiteHelper::formatPrice($this->fleet_commission);
    }

    public function getCommission()
    {
        return SiteHelper::formatPrice($this->commission);
    }

    public function getCash()
    {
        return SiteHelper::formatPrice($this->cash);
    }

    public function getTotalPrice()
    {
        return SiteHelper::formatPrice($this->total_price);
    }

    public function getDiscount()
    {
        return SiteHelper::formatPrice($this->discount);
    }

    public function getVehicleList()
    {
        return SiteHelper::nl2br2($this->getOriginal('vehicle_list'));
    }

    public function getVehicleTypes($format = null)
    {
        $vehicles = [];
        $list = !empty($this->vehicle) ? json_decode($this->vehicle) : [];

        foreach ($list as $k => $v) {
            if ($format == 'ids') {
                $vehicles[] = (int)$v->id;
            }
            else {
                $vehicles[] = (object)[
                    'id' => (int)$v->id,
                    'amount' => (int)$v->amount,
                ];
            }
        }

        return $vehicles;
    }

    public function getSummary($format = 'html')
    {
        $results = [];
        $grandTotal = 0;
        $types = [
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
        $items = json_decode($this->items);

        if ( !empty($items) ) {
            foreach ($items as $k => $v) {
                $type = (string)$v->type;
                $name = (string)$v->name;
                $original_name = !empty($v->original_name) ? (string)$v->original_name : '';
                $value = (float)$v->value;
                $amount = (int)$v->amount;
                $total = round($value * $amount, 2);
                $grandTotal += $total;

                if (!in_array($type, $types)) {
                    $type = 'other';
                }

                if (empty($name)) {
                    if ($this->parent_booking_id && $this->scheduled_route_id && $type == 'journey') {
                        $name = trans('booking.summary_types.ticket');
                    }
                    else {
                        if ($type == 'other' && !empty($original_name)) {
                            $name = $original_name;
                        }
                        else {
                            $name = trans('booking.summary_types.'. $type);
                        }
                    }
                }

                // if ($type == 'journey' && $total <= 0) {
                //     continue;
                // }

                $results[] = (object)[
                    'type' => $type,
                    'name' => $name,
                    'value' => $value,
                    'amount' => $amount,
                    'total' => $total,
                ];
            }
        }

        if ( $format == 'html' ) {
            $html = '';
            foreach ($results as $k => $v) {
                $html .= trim($v->name . ($v->amount > 1 ? ' ('. SiteHelper::formatPrice($v->value) .' x '. $v->amount .')' : '') . ($v->total ? ' '. SiteHelper::formatPrice($v->total) : '')) .'<br>';
            }
            $results = $html;
        }
        elseif ( $format == 'meeting_board' ) {
            $html = '';
            foreach ($results as $k => $v) {
                if (in_array($v->type, [
                    'child_seat',
                    'baby_seat',
                    'infant_seat',
                    'wheelchair',
                    'waiting_time',
                    'luggage',
                    'hand_luggage',
                    'other',
                ])) {
                    $html .= ($html ? ', ' : ''). trim($v->name .': '. $v->amount);
                }
            }
            $results = $html;
        }

        return $results;
    }

    public function getServiceType()
    {
        return !empty($this->service_id) && !empty($this->bookingService->name) ? $this->bookingService->getName() : '';
    }

    public function getServiceDuration()
    {
        return $this->service_duration ? $this->service_duration .'h' : '';
    }

    public function getScheduledRouteName()
    {
        return !empty($this->scheduled_route_id) && !empty($this->bookingScheduledRoute->name) ? $this->bookingScheduledRoute->getName() : '';
    }

    public function getRouteName()
    {
        $value = '';

        if ( $this->route == 1 ) {
            $value = trans('booking.one_way');
        }
        elseif ( $this->route == 2 ) {
            $value = trans('booking.return');
        }

        return $value;
    }

    public function generateRefNumber($params = [])
    {
        $ref_number = !empty($params['ref_number']) ? (string)$params['ref_number'] : config('site.ref_format');
        $id = !empty($params['id']) ? (int)$params['id'] : $this->id;
        $createDateTime = !empty($params['createDateTime']) ? Carbon::parse($params['createDateTime']) : Carbon::now();
        $pickupDateTime = !empty($params['pickupDateTime']) ? (string)$params['pickupDateTime'] : $this->date;
        $pickupDateTime = !empty($pickupDateTime) ? Carbon::parse($pickupDateTime) : Carbon::now();
        $exclude = !empty($params['exclude']) ? (array)$params['exclude'] : [];

        $replace = [
            'createDateTime' => $createDateTime->format('YmdHi'),
            'createDate' => $createDateTime->format('Ymd'),
            'createTime' => $createDateTime->format('Hi'),
            'createDateTimeFormatted' => $createDateTime->format('Y-m-d_H-i'),
            'createDateFormatted' => $createDateTime->format('Y-m-d'),
            'createTimeFormatted' => $createDateTime->format('H-i'),
            'pickupDateTime' => $pickupDateTime->format('YmdHi'),
            'pickupDate' => $pickupDateTime->format('Ymd'),
            'pickupTime' => $pickupDateTime->format('Hi'),
            'pickupDateTimeFormatted' => $pickupDateTime->format('Y-m-d_H-i'),
            'pickupDateFormatted' => $pickupDateTime->format('Y-m-d'),
            'pickupTimeFormatted' => $pickupDateTime->format('H-i'),
            'year' => $createDateTime->format('Y'),
            'month' => $createDateTime->format('m'),
            'day' => $createDateTime->format('d'),
            'hour' => $createDateTime->format('H'),
            'minute' => $createDateTime->format('i'),
            'second' => $createDateTime->format('s'),
            'rand' => rand(1, 1000),
            'rand2' => SiteHelper::generateRandomString(2),
            'rand3' => SiteHelper::generateRandomString(3),
            'rand4' => SiteHelper::generateRandomString(4),
            'rand5' => SiteHelper::generateRandomString(5),
            'rand6' => SiteHelper::generateRandomString(6),
            'rand7' => SiteHelper::generateRandomString(7),
            'rand8' => SiteHelper::generateRandomString(8),
            'rand9' => SiteHelper::generateRandomString(9),
            'rand10' => SiteHelper::generateRandomString(10),
            'id' => $id,
        ];

        if (!empty($exclude)) {
            if (in_array('pickup', $exclude)) {
                $exclude = array_merge($exclude, [
                    'pickupDateTime',
                    'pickupDate',
                    'pickupTime',
                    'pickupDateTimeFormatted',
                    'pickupDateFormatted',
                    'pickupTimeFormatted'
                ]);
            }

            if (in_array('create', $exclude)) {
                $exclude = array_merge($exclude, [
                    'createDateTime',
                    'createDate',
                    'createTime',
                    'createDateTimeFormatted',
                    'createDateFormatted',
                    'createTimeFormatted'
                ]);
            }
        }

        foreach ($replace as $k => $v) {
            if (!empty($exclude) && in_array($k, $exclude)) { continue; }
            $ref_number = str_replace('{'. $k .'}', $v, $ref_number);
        }

        if (empty($exclude)) {
            $booking = DB::table('booking_route')->where('ref_number', $ref_number)->first();
            if (!empty($booking->id)) {
                $ref_number .= '-'. ($id ? $id : rand(1, 10000));
            }
        }

        return $ref_number;
    }

    public function getInvoice($type = '', $ids = [])
    {
        $invoice = (object)[
            'type' => 'single',
            'invoice_number' => '',
            'invoice_date' => '',
            'payment_date' => '',
            'logo' => '',
            'additional_info' => '',
            'bill_from' => '',
            'bill_to' => '',
            'items' => [],
            'subtotal' => 0,
            'discount' => 0,
            'payment_charge' => 0,
            'total' => 0,
            'amount_due' => 0,
            'taxes' => [],
            'payments' => [],
        ];

        if ( !empty($ids) ) {

            $invoice->type = 'group';
            $invoice->invoice_number = Carbon::now()->format( config('site.date_format') ) .'-'. rand(1000, 100000);
            $invoice->invoice_date = Carbon::now()->format( config('site.date_format') );
            $invoice->payment_date = '';

            $bookings = \App\Models\BookingRoute::whereIn('id', $ids)->get();

            foreach($bookings as $key => $booking) {
                $data = $booking->getInvoice('raw');

                if ( $key == 0 ) {
                    $invoice->logo = $data->logo;
                    $invoice->additional_info = $data->additional_info;
                    $invoice->bill_from = $data->bill_from;
                    $invoice->bill_to = $data->bill_to;
                }

                foreach($data->items as $item) {
                    $invoice->items[] = $item;
                }

                $invoice->subtotal += $data->subtotal;
                $invoice->discount += $data->discount;
                $invoice->payment_charge += $data->payment_charge;
                $invoice->total += $data->total;
                $invoice->amount_due += $data->amount_due;

                foreach($data->taxes as $tax) {
                    $slug = md5($tax->tax_name . $tax->tax_percent);

                    if ( empty($invoice->taxes[$slug]) ) {
                        $invoice->taxes[$slug] = $tax;
                    }
                    else {
                        $invoice->taxes[$slug]->amount += $tax->amount;
                    }
                }

                foreach($data->payments as $payment) {
                    $slug = md5($payment->title . $payment->method . $payment->status);

                    if ( empty($invoice->payments[$slug]) ) {
                        $invoice->payments[$slug] = $payment;
                    }
                    else {
                        $invoice->payments[$slug]->amount += $payment->amount;
                        $invoice->payments[$slug]->charge += $payment->charge;
                        $invoice->payments[$slug]->total += $payment->total;
                    }
                }
            }

        }
        else {
            // $masterBooking = $this->booking()->withTrashed()->first();
            $masterBooking = $this->booking;

            // $config = \App\Models\Config::getBySiteId($masterBooking->site_id)->getData();

            $config = \App\Models\Config::getBySiteId($masterBooking->site_id);
            if (!empty($this->locale)) {
                $config->loadLocale($this->locale);
            }
            $config = $config->mapData()->getData();

            // Bill from
            if ( $config->invoice_bill_from ) {
                $invoice->bill_from = SiteHelper::nl2br2($config->invoice_bill_from);
            }
            else {
                if ( $config->company_name ) {
                    // $invoice->bill_from .= "<span style=\"font-weight:bold;\">". $config->company_name ."</span><br>";
                    $invoice->bill_from .= $config->company_name ."<br>";
                }

                if ( $config->company_address ) {
                    $invoice->bill_from .= SiteHelper::nl2br2($config->company_address) ."<br>";
                }

                if ( $config->company_number ) {
                    $invoice->bill_from .= $config->company_number ."<br>";
                }

                if ( $config->company_tax_number ) {
                    $invoice->bill_from .= $config->company_tax_number ."<br>";
                }
            }

            // Bill to
            if ( $this->getContactFullName() ) {
                $invoice->bill_to .= $this->getContactFullName() ."<br>";
            }

            if ( $this->contact_mobile ) {
                $invoice->bill_to .= $this->contact_mobile ."<br>";
            }

            if ( $this->contact_email ) {
                $invoice->bill_to .= $this->contact_email ."<br>";
            }

            if ( $masterBooking->user_id ) {
                $db = DB::connection();
                $dbPrefix = get_db_prefix();

                $sql = "SELECT `a`.*, `b`.*
                        FROM `{$dbPrefix}user` AS `a`
                        LEFT JOIN `{$dbPrefix}user_customer` AS `b`
                        ON (`a`.`id`=`b`.`user_id`)
                        WHERE `a`.`id`='". $masterBooking->user_id ."'
                        LIMIT 1";

                $qCustomer = $db->select($sql);
                if (!empty($qCustomer[0])) {
                    $qCustomer = $qCustomer[0];
                }

                if ( !empty($qCustomer) ) {
                    $billTo = '';

                    if ( $qCustomer->is_company ) {
                        if ( $qCustomer->company_name ) {
                            $billTo .= $qCustomer->company_name ."<br>";
                        }

                        if ( config('site.invoice_display_company_number') && $qCustomer->company_number ) {
                            $billTo .= $qCustomer->company_number ."<br>";
                        }

                        if ( config('site.invoice_display_company_tax_number') && $qCustomer->company_tax_number ) {
                            $billTo .= $qCustomer->company_tax_number ."<br>";
                        }
                    }

                    if ( $qCustomer->first_name || $qCustomer->last_name ) {
                        $billTo .= trim($qCustomer->title .' '. $qCustomer->first_name .' '. $qCustomer->last_name) ."<br>";
                    }

                    if ( $qCustomer->address ) {
                        $billTo .= $qCustomer->address ."<br>";
                    }

                    if ( $qCustomer->city || $qCustomer->postcode ) {
                        $billTo .= trim($qCustomer->city .' '. $qCustomer->postcode) ."<br>";
                    }

                    if ( $qCustomer->state ) {
                    	$billTo .= $qCustomer->state ."<br>";
                    }

                    if ( $qCustomer->country ) {
                        $billTo .= $qCustomer->country ."<br>";
                    }

                    if ( $billTo ) {
                        $invoice->bill_to = $billTo;
                    }
                }
            }

            // Items
            $desc = trans('booking.ref_number') .": ". $this->ref_number ."<br>";

            if (!empty($config->invoice_display_custom_field) && !empty($this->custom)) {
                if (!empty(config('eto_booking.custom_field.name'))) {
                    $title = config('eto_booking.custom_field.name');
                } else {
                    $title = trans('booking.customPlaceholder');
                }
                $desc .= $title .": ". $this->custom ."<br>";
            }

            $desc .= trans('booking.date') .": ". SiteHelper::formatDateTime($this->date) ."<br>";
            $desc .= trans('booking.vehicle') .": ". $this->getVehicleList() ."<br>";


            $passanger = "";

            if ( $this->getContactFullName() ) {
                $passanger .= $this->getContactFullName();
            }

            // if ( $this->contact_mobile ) {
            //     $passanger .= " / ". $this->contact_mobile;
            // }

            if ( $passanger ) {
                $desc .= trans('booking.contact_name') .": ". $passanger ."<br>";
            }

            // $lead = "";

            // if ( $this->getLeadPassengerFullName() ) {
            //     $lead .= $this->getLeadPassengerFullName();
            // }

            // if ( $this->lead_passenger_mobile ) {
            //     $lead .= " / ". $this->lead_passenger_mobile;
            // }

            // if ( $lead ) {
            //     $desc .= trans('booking.lead_passenger_name') .": ". $lead ."<br>";
            // }

            if ( $this->passengers ) {
                $desc .= trans('booking.passengers') .": ". $this->passengers ."<br>";
            }

            if ( $this->baby_seats ) {
                $desc .= trans('booking.baby_seats') .": ". $this->baby_seats ."<br>";
            }

            if ( $this->child_seats ) {
                $desc .= trans('booking.child_seats') .": ". $this->child_seats ."<br>";
            }

            if ( $this->infant_seats ) {
                $desc .= trans('booking.infant_seats') .": ". $this->infant_seats ."<br>";
            }

            if ( $this->wheelchair ) {
                $desc .= trans('booking.wheelchair') .": ". $this->wheelchair ."<br>";
            }

            // if ( $this->luggage ) {
            //     $desc .= trans('booking.luggage') .": ". $this->luggage ."<br>";
            // }

            // if ( $this->hand_luggage ) {
            //     $desc .= trans('booking.hand_luggage') .": ". $this->hand_luggage ."<br>";
            // }

            // if ( $this->getFrom() ) {
            //     $desc .= trans('booking.from') .": ". $this->getFrom() ."<br>";
            // }

            // if ( $this->getVia() ) {
            //     $desc .= trans('booking.via') .": ". $this->getVia() ."<br>";
            // }

            // if ( $this->getTo() ) {
            //     $desc .= trans('booking.to') .": ". $this->getTo() ."<br>";
            // }

            if ( $this->flight_number ) {
                $desc .= trans('booking.flight_number') .": ". $this->flight_number ."<br>";
            }
            if ( $this->flight_landing_time ) {
                $desc .= trans('booking.flight_landing_time') .": ". $this->flight_landing_time ."<br>";
            }
            if ( $this->departure_city ) {
                $desc .= trans('booking.departure_city') .": ". $this->departure_city ."<br>";
            }

            if ( $this->departure_flight_number ) {
                $desc .= trans('booking.departure_flight_number') .": ". $this->departure_flight_number ."<br>";
            }
            if ( $this->departure_flight_time ) {
                $desc .= trans('booking.departure_flight_time') .": ". $this->departure_flight_time ."<br>";
            }
            if ( $this->departure_flight_city ) {
                $desc .= trans('booking.departure_flight_city') .": ". $this->departure_flight_city ."<br>";
            }

            if ( $this->getSummary() && config('site.booking_summary_enable') ) {
                $desc .= '<br>'. $this->getSummary();
            }

            if ( $this->department ) {
                $desc .= trans('booking.department') .": ". $this->department ."<br>";
            }

            $invoice->items[] = (object)[
                'name' => $this->getDirections(),
                'description' => ($config->invoice_display_details == 1) ? SiteHelper::nl2br2($desc) : '',
                'amount' => $this->total_price,
            ];

            $data = $this->getTotal('data', 'raw');

            $invoice->invoice_number = $this->ref_number;
            $invoice->invoice_date = SiteHelper::formatDateTime($this->created_date, 'date');
            $invoice->payment_date = SiteHelper::formatDateTime($this->date, 'date');
            $invoice->logo = ($config->invoice_display_logo == 1) ? $config->logo : '';
            $invoice->additional_info = SiteHelper::nl2br2($config->invoice_info);

            foreach($invoice->items as $item) {
                $invoice->subtotal += $item->amount;
            }

            $invoice->discount = $this->discount;
            $invoice->payment_charge = $data->payment_charge;
            $invoice->total = $data->total;

            $amount_due = $data->remaining * -1;
            if ( $amount_due <= 0 ) {
                $amount_due = 0;
            }
            $invoice->amount_due = $amount_due;

            if ( $config->tax_percent ) {
                $invoice->taxes[] = (object)[
                    'name' => $config->tax_name ? $config->tax_name : trans('invoices.tax_name'),
                    'percent' => $config->tax_percent,
                    'amount' => round(($invoice->total / 100) * $config->tax_percent, 2),
                ];
            }

            if ( $config->invoice_display_payments ) {
                $invoice->payments = $data->payment_list;
            }

        }

        switch( $type ) {
            case 'both':
                $data = (object)[
                    'raw' => $invoice,
                    'html' => view('invoices.invoice', ['invoice' => $invoice])->render(),
                ];
            break;
            case 'raw':
                $data = $invoice;
            break;
            default:
                $data = view('invoices.invoice', ['invoice' => $invoice])->render();
            break;
        }

        return $data;
    }

    public function getInvoiceFilename()
    {
        $filename = '[company_name] - '. trans('invoices.invoice') .' [ref_number]';
        $filename = str_replace('[company_name]', config('site.company_name'), $filename);
        $filename = str_replace('[timestamp]', Carbon::now()->format('Y-m-d_H-i-s'), $filename);
        $filename = str_replace('[journey_datetime]', $this->date ? $this->date->format('Y-m-d_H-i-s') : '', $filename);
        $filename = str_replace('[journey_date]', $this->date ? $this->date->format('Y-m-d') : '', $filename);
        $filename = str_replace('[journey_time]', $this->date ? $this->date->format('H-i-s') : '', $filename);
        $filename = str_replace('[ref_number]', $this->ref_number ? $this->ref_number : '', $filename);
        $filename = str_replace('[id]', $this->id ? $this->id : '', $filename);
        $filename = str_replace('  ', ' ', $filename);
        // $filename = urlencode($filename);
        return trim($filename);
    }

    public function getMeetingBoard($mode = 'print')
    {
        $customer = $this->assignedCustomer();
        $avatar = !empty($customer->avatar) ? $customer->avatar : '';
        $companyName = !empty($customer->company_name) ? $customer->company_name : '';
        $headerInfo = '';
        $bodyInfo = $this->getContactFullName();
        $footerInfo = '';
        $isLogo = 0;
        $fontSize = config('site.booking_meeting_board_font_size') ?: 100;

        if (config('site.booking_meeting_board_header')) {
            if (config('site.booking_meeting_board_header') == 2 && $avatar && \Storage::disk('avatars')->exists($avatar)) {
                $headerInfo .= '<img src="'. asset_url('uploads','avatars/'. $avatar) .'" alt="'. $companyName .'" />';
                $isLogo = 1;
            }
            elseif (config('site.booking_meeting_board_header') == 2 && $companyName) {
                $headerInfo .= $companyName;
            }
        		elseif (config('site.logo')) {
                $headerInfo .= '<img src="'. asset_url('uploads','logo/'. config('site.logo')) .'" alt="'. config('site.company_name') .'" />';
                $isLogo = 1;
            }
        		else {
                $headerInfo .= config('site.company_name');
            }
        }

        if (config('site.booking_meeting_board_footer')) {
            // Line 1
            $line = [];
            $line[] = SiteHelper::formatDateTime($this->date);
            if ($this->getVehicleList()) {
                $line[] = $this->getVehicleList();
            }
            if ($this->flight_number) {
                $line[] = trans('booking.flight_number') .': '. $this->flight_number;
            }
            if ($this->ref_number) {
                $line[] = trans('booking.ref_number') .': '. $this->ref_number;
            }
            if (!empty($line)) {
                $footerInfo .= '<div>'. implode(', ', $line) .'</div>';
            }

            // Line 2
            $line = [];
            if ($this->passengers) {
                $line[] = trans('booking.passengers') .': '. $this->passengers;
            }
            if ($this->getSummary('meeting_board')) {
                $line[] = $this->getSummary('meeting_board');
            }
            if (!empty($line)) {
                $footerInfo .= '<div>'. implode(', ', $line) .'</div>';
            }

            // Line 3
            $line = [];
            if ($this->getFrom() && $this->getTo()) {
                $line[] = $this->getFrom() .' -> '. $this->getTo();
            }
            else {
                $line[] = $this->getFrom();
            }
            if (!empty($line)) {
                $footerInfo .= '<div>'. implode(', ', $line) .'</div>';
            }

            // Line 4
            if ($this->requirements) {
                $footerInfo .= trans('booking.requirements') .': '. $this->requirements;
            }
        }

        return view('partials.meeting_board', [
            'mode' => $mode,
            'headerInfo' => $headerInfo,
            'bodyInfo' => $bodyInfo,
            'footerInfo' => $footerInfo,
            'isLogo' => $isLogo,
            'fontSize' => $fontSize,
        ])->render();
    }

    public function getIcal()
    {
        $meeting_duration = 3600 * 1; // 1 hour
        $meetingstamp = strtotime( $this->date . " UTC");
        $createdstamp = gmdate('Ymd\THis\Z', strtotime($this->created_date ." UTC"));
        $updatedStamp = gmdate('Ymd\THis\Z', strtotime($this->modified_date ." UTC"));
        $dtstart = gmdate('Ymd\THis\Z', $meetingstamp);
        $dtend = gmdate('Ymd\THis\Z', $meetingstamp + $meeting_duration);
        $todaystamp = gmdate('Ymd\THis\Z');
        // $organizer = "CN=Organizer name:".config('site.company_email');

        $ical = [];
        $ical[] = "BEGIN:VCALENDAR";
        $ical[] = "PRODID:-//". config('site.company_name') ."//ETO Calendar ". config('app.version') ."//EN";
        $ical[] = "VERSION:2.0";
        $ical[] = "CALSCALE:GREGORIAN";
        $ical[] = "METHOD:REQUEST";
        $ical[] = "BEGIN:VEVENT";
        $ical[] = "DTSTART;TZID=". config('app.timezone') .":" . $dtstart;
        $ical[] = "DTEND;TZID=". config('app.timezone') .":" . $dtend;
        $ical[] = "DTSTAMP;TZID=". config('app.timezone') .":" . $todaystamp;
        $ical[] = "ORGANIZER;CN=". config('site.company_email') .":mailto:". config('site.company_email');
        $ical[] = "UID:". date('Ymd') .'T'. date('His') .'-'. rand() .'@'. request()->getHost();
        // $ical[] = "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=TRUE".PHP_EOL
        // ." ;CN=".config('site.company_email').";X-NUM-GUESTS=0:mailto:".config('site.company_email');
        // $ical[] = "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=TRUE".PHP_EOL
        // ." ;CN=".$this->contact_email.";X-NUM-GUESTS=0:mailto:".$this->contact_email;
        $ical[] = "X-MICROSOFT-CDO-OWNERAPPTID:1311537758";
        $ical[] = "CREATED:".$createdstamp;
        $ical[] = "DESCRIPTION:FROM: ". $this->getFrom('no_html') ."\\nTO: ". $this->getTo('no_html');
        $ical[] = "LAST-MODIFIED:". $updatedStamp;
        $ical[] = "LOCATION:". $this->getFrom('no_html');
        $ical[] = "SEQUENCE:0";
        $ical[] = "STATUS:CONFIRMED";
        $ical[] = "SUMMARY:". trans('booking.ical_journey_tile') .' '. $this->getRefNumber();
        $ical[] = "TRANSP:OPAQUE";
        $ical[] = "BEGIN:VALARM";
        $ical[] = "ACTION:EMAIL";
        $ical[] = "TRIGGER;TZID=". config('app.timezone') .":". $dtend;
        $ical[] = "REPEAT:4";
        $ical[] = "END:VALARM";
        $ical[] = "END:VEVENT";
        $ical[] = "END:VCALENDAR";

        return implode(PHP_EOL, $ical);
    }

    public function getFiles($json = false, $type = 'driver')
    {
        $files = [];
        $id = $this->id;

        if ($id) {
            $query = \DB::table('file')
                ->where('file_relation_type', 'booking')
                ->where('file_relation_id', $id)
                ->orderBy('file_id', 'asc')
                ->get();

            foreach($query as $v) {
                $files[] = (object)[
                    'id' => $v->file_id,
                    'name' => $v->file_name,
                    'file_path' => $v->file_path,
                    'path' => route(($type == 'admin' ? 'admin.bookings.download' : 'driver.jobs.download'), ['id' => $id, 'file_id' => $v->file_id])
                ];
            }
        }

        if ($json) {
            $files = json_encode($files);
        }

        return $files;
    }

    public function deleteFiles()
    {
        $id = $this->id;

        if ($id) {
            $query = \DB::table('file')
               ->where('file_relation_type', 'booking')
               ->where('file_relation_id', $id)
               ->get();

            foreach($query as $v) {
                if (\Storage::disk('safe')->exists($v->file_path)) {
                    \Storage::disk('safe')->delete($v->file_path);
                }
                \DB::table('file')->where('file_id', $v->file_id)->delete();
            }
        }
    }

    public function getFlightDetails($type = '', $format = 'raw')
    {
        $data = null;

        if ($type) {
            $key = 'flight_details_'. ($type == 'pickup' ? 'pickup' : 'dropoff');

            foreach ($this->bookingParams as $k => $v) {
                if ($v->key == $key && !empty($v->value)) {
                    $data = is_object($v->value) ? $v->value : json_decode($v->value);
                    break;
                }
            }

            if ($data && $format == 'html') {
                $data = \App\Http\Controllers\FlightController::getFlightTable($data, $this->id, $type);
            }
        }

        return $data;
    }
}
