<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Traits\Roles\HasRoleAndPermission;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoleAndPermission;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar',
        'password',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'push_token'
    ];

    protected $appends = [
        'role',
    ];

    protected $guarded = [];

    protected $dates = [
        'last_seen_at'
    ];

    public $params;
    public $units = 0;
    public $statusOptions = [];
    private $minDistance = 0.01;
    private $userSettings;
    private $role_model;
    public $role;

    function __construct()
    {
        parent::__construct();

        $this->params = new \stdClass();
        $this->userSettings = new \stdClass();
        $this->units = config('site.booking_distance_unit') == 0 ? 1609.34 : 1000; // mi | km

        $this->statusOptions = [
            'approved' => [
                'name' => trans('common.user_status_options.approved'),
                'color' => '#00a65a'
            ],
            'awaiting_admin_review' => [
                'name' => trans('common.user_status_options.awaiting_admin_review'),
                'color' => '#f39c12'
            ],
            'awaiting_email_confirmation' => [
                'name' => trans('common.user_status_options.awaiting_email_confirmation'),
                'color' => '#908cdc'
            ],
            'inactive' => [
                'name' => trans('common.user_status_options.inactive'),
                'color' => '#dd4b39'
            ],
            'rejected' => [
                'name' => trans('common.user_status_options.rejected'),
                'color' => '#00c0ef'
            ]
        ];
    }

    public function profile()
    {
        return $this->hasOne('App\Models\UserProfile');
    }

    public function teams()
    {
        return $this->belongsToMany('App\Models\Team', 'team_user', 'user_id', 'team_id');
    }

    public function vehicles()
    {
        return $this->hasMany('App\Models\Vehicle');
    }

    public function driverBookings()
    {
        return $this->hasMany('App\Models\BookingDriver', 'driver_id', 'id');
    }

    public function settings()
    {
        return $this->morphMany('App\Models\Setting', 'relation');
    }

    public function driverDefaultVehicle()
    {
        return $this->vehicles()
          ->where('status', 'activated')
          ->orderBy('selected', 'desc')
          ->orderBy('name', 'asc')
          ->first();
    }

    public function scopeWithDistance($query, $lat = 0, $lng = 0)
    {
        if (is_null($query->getQuery()->columns)) {
            $query->addSelect("*");
        }

        $sql = "(SELECT 6370986 * acos(cos(radians({$lat}))
           * cos(radians(`lat`))
           * cos(radians(`lng`) - radians({$lng}))
           + sin(radians({$lat}))
           * sin(radians(`lat`)))
        ) / {$this->units} AS `distance`";

        return $query->addSelect(\DB::raw($sql));
    }

    public function scopeCloseTo($query, $lat = 0, $lng = 0, $radius = 0)
    {
        $sql = "(SELECT 6370986 * acos(cos(radians({$lat}))
           * cos(radians(`lat`))
           * cos(radians(`lng`) - radians({$lng}))
           + sin(radians({$lat}))
           * sin(radians(`lat`)))
        ) / {$this->units} <= {$radius}";

        return $query->whereRaw($sql);
    }

    public function getSetting($key = null)
    {
        $this->userSettings = count((array)$this->userSettings) !== 0 ? $this->userSettings : $this->settings()->toObject();

        if (!empty($key)) {
            return isset($this->userSettings->$key) ? $this->userSettings->$key : null;
        }
        else {
            return $this->userSettings ?: null;
        }
    }

    public function getOriginalForm($key)
    {
        $value = null;

        if ( $this->getOriginal($key) ) {
            $value = $this->getOriginal($key);
        }

        return $value;
    }

    public function getAvatarPath()
    {
        if (!empty($this->avatar) && \Storage::disk('avatars')->exists($this->avatar)) {
            $path = asset_url('uploads','avatars/'. $this->avatar);
        }
        else {
            $path = asset_url('images','placeholders/avatar.png');
        }

        return $path;
    }

    public function getName($display = false)
    {
        $value = '';

        if (config('site.driver_show_unique_id') && $display) {
            if (!empty($this->unique_id)) {
                $value = $this->unique_id .' - ';
            }
            elseif (!isset($this->unique_id) && !empty($this->profile->unique_id) ) {
                $value = $this->profile->unique_id .' - ';
            }
        }

        $value .= ucfirst($this->name);

        return $value;
    }

    public function fleet()
    {
        return $this->hasOne('App\Models\User', 'id', 'fleet_id');
    }

    public function getFleetName()
    {
        return !empty($this->fleet_id) && !empty($this->fleet) ? $this->fleet->getName(false) : '';
    }

    public function scopeRole($query, $role)
    {
        if (is_string($role)) {
            $role = [$role];
        }

        $tnUser = (new \App\Models\User)->getTable();

        if (is_null($query->getQuery()->columns)) {
            $query->addSelect($tnUser .'.*');
        } else {
            // $query->select($tnUser .'.*'); // This code causes problem in driver listing tab.
        }

        $query->leftJoin('role_user', 'role_user.user_id', '=', $this->getTable() .'.id')
            ->distinct('user_id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id');

        $query->where(function ($q) use ($role) {
            foreach ($role as $id=>$item) {
                $method = $id === 0 ? 'where' : 'orWhere';

                if (preg_match('#\.\*$#', $item)) {
                    $q->$method('roles.slug', 'like', preg_replace('#\.\*$#', '', $item) .'%');
                }
                else {
                    $q->$method('roles.slug', $item);
                }
            }
        });

        return $query;
    }

    public function usedRoleRel()
    {
        return $this->hasOne('App\Models\Role', 'id', 'used_role');
    }

    public function usedRole() {
        // if ($this->role_model === null) {
        //     $this->role_model = $this->usedRoleRel;
        // }

        if ($this->role_model === null && !empty($this->usedRoleRel)) {
            $this->role_model = $this->usedRoleRel;
        }
        elseif (empty($this->usedRoleRel) && !empty($this->roles)) {
            $this->role_model = $this->roles[0];
        }

        return $this->role_model;
    }

    public function usedRoleGroup() {
        $userRole = $this->usedRole();
        return $userRole ? $userRole->getSlugGroup() : null;
    }

    public function getRoleAttribute() {
        if ($this->role === null) {
            $this->role = $this->usedRoleGroup();
        }
        return $this->role;
        // return $this->usedRoleGroup();
    }

    public function getStatus($type = 'none')
    {
        $value = $this->status;
        $statusOptions = $this->statusOptions;

        if ( !empty($statusOptions[$value]) ) {
            if ( $type == 'label' ) {
                $value = '<span class="label" style="background:'. $statusOptions[$value]['color'] .';">'. $statusOptions[$value]['name'] .'</span>';
            }
            elseif ( $type == 'color' ) {
                $value = '<span style="color:'. $statusOptions[$value]['color'] .';">'. $statusOptions[$value]['name'] .'</span>';
            }
            else {
                $value = $statusOptions[$value]['name'];
            }
        }

        return $value;
    }

    public function getEmailLink($params = [])
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

        return !empty($this->email) ? '<a href="mailto:'. $this->email .'" '. $class .' '. $style .'>'. $this->email .'</a>' : '';
    }

    public function isOnline($type = '')
    {
        $minutes = 10;
        $status = false;

        if ($type == 'session') {
            $session = \DB::table('sessions')
              ->where('user_id', $this->id)
              ->where('last_activity', '>=', Carbon::now()->subSeconds($minutes * 60))
              ->first();

            if (!empty($session->id)) {
                $status = true;
            }
        }
        else {
            if ($this->last_seen_at >= Carbon::now()->subSeconds($minutes * 60)) {
                $status = true;
            }
        }

        return $status;
    }

    public function setLastActivity($type = '')
    {
        $minutes = 10;
        $last_seen_at = \Carbon\Carbon::now();

        if ($type == 'logout') {
            $last_seen_at->subSeconds($minutes * 60);
        }

        $this->last_seen_at = $last_seen_at;
        $this->save();
    }

    public function setTracking()
    {
        if (session('isMobileApp')) {
            $timestamp = time();
            $newLat = request('lat');
            $newLng = request('lng');
            $distance = calculate_distance($newLat, $newLng, $this->lat, $this->lng);

            if ($distance >= $this->minDistance) {
                $this->accuracy = request('accuracy');
                $this->heading = request('heading');
                $this->lat = $newLat;
                $this->lng = $newLng;
                $this->last_seen_at = Carbon::now();
                $this->save();

                UserParam::updateOrCreate(
                    ['user_id'=> $this->id, 'param'=>'last_coordinates_timestamp'],
                    ['value'=> $timestamp]
                );

                $trackingStatuses = (new \App\Models\BookingRoute)->tracking_statuses;
                $runingJob = BookingRoute::whereDriver($this->id)
                    ->whereIn('status', $trackingStatuses)
                    ->orderBy('date', 'asc')
                    ->first();

                if ($runingJob && !in_array($runingJob->status, (array)$runingJob->untracking_statuses)) {
                    $newBookingTracking = new BookingDriverTracking();
                    $newBookingTracking->driver_id = $this->id;
                    $newBookingTracking->booking_id = $runingJob->id;
                    $newBookingTracking->lat = $this->lat;
                    $newBookingTracking->lng = $this->lng;
                    $newBookingTracking->timestamp = $timestamp;
                    $newBookingTracking->save();

                    $bookingStatus = BookingStatus::where('user_id', $this->id)
                        ->where('booking_id', $runingJob->id)
                        ->where('status', $runingJob->status)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($bookingStatus && empty($bookingStatus->lat)) {
                        $bookingStatus->lat = $this->lat;
                        $bookingStatus->lng = $this->lng;
                        $bookingStatus->save();
                    }
                }
            }
        }
    }

    public function getParams()
    {
        if (count((array)$this->params) === 0) {
            $params = \App\Models\UserParam::where('user_id', $this->id)->get();

            foreach($params as $param) {
                $this->params->{$param->param} = $param->value;
            }
        }
    }
}
