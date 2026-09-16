<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserProfile extends Model
{
    protected $table = 'user_profile';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'date_of_birth',
        'insurance_expiry_date',
        'driving_licence_expiry_date',
        'pco_licence_expiry_date',
        'phv_licence_expiry_date'
    ];

    public $profileTypeOptions = [];
    public $availabilityStatusOptions = [];

    function __construct()
    {
        parent::__construct();

        $this->profileTypeOptions = [
            'private' => trans('common.user_profile_type_options.private'),
            'company' => trans('common.user_profile_type_options.company')
        ];

        $this->availabilityStatusOptions = [
            '0' => [
                'name' => trans('common.user_availability_status_options.unavailable'),
                'color' => '#ce4535'
            ],
            '1' => [
                'name' => trans('common.user_availability_status_options.available'),
                'color' => '#009e56'
            ],
            '2' => [
                'name' => trans('common.user_availability_status_options.onbreak'),
                'color' => '#dc8e12'
            ]
        ];
    }

    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setCommissionAttribute($value)
    {
        $this->attributes['commission'] = (float)$value;
    }

    public function setInsuranceExpiryDateAttribute($value)
    {
        $this->attributes['insurance_expiry_date'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setDrivingLicenceExpiryDateAttribute($value)
    {
        $this->attributes['driving_licence_expiry_date'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setPcoLicenceExpiryDateAttribute($value)
    {
        $this->attributes['pco_licence_expiry_date'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setPhvLicenceExpiryDateAttribute($value)
    {
        $this->attributes['phv_licence_expiry_date'] = empty($value) ? null : Carbon::parse($value);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getOriginalForm($key)
    {
        $value = null;

        if ( $this->getOriginal($key) ) {
            $value = $this->getOriginal($key);
        }

        return $value;
    }

    public function getFiles($json = false)
    {
        $files = [];

        $query = \DB::table('file')
           ->where('file_relation_type', 'user')
           ->where('file_relation_id', $this->user_id)
           ->orderBy('file_id', 'asc')
           ->get();

        if ( !empty($query) ) {
            foreach($query as $key => $value) {
                $files[] = (object)[
                    'id' => $value->file_id,
                    'name' => $value->file_name,
                    'path' => route('admin.users.download', $value->file_id)
                ];
            }
        }

        if ( $json ) {
            $files = json_encode($files);
        }

        return $files;
    }

    public function getAvailability($type = 'none')
    {
        if ( $this->availability ) {
            $availability = json_decode($this->availability);

            if ( $type == 'list' ) {
                $value = '';

                foreach($availability as $k => $v) {
                    $value .= '<div>';
                    $value .= '<span style="color:gray;">'. trans('admin/users.availability_start') .':</span> '. ($v->start_date ?: 'Unknown') .', ';
                    $value .= '<span style="color:gray;">'. trans('admin/users.availability_end') .':</span> '. ($v->end_date ?: 'Unknown') .', ';
                    $value .= '<span style="color:gray;">'. trans('admin/users.availability_available') .':</span> '. ($v->available_date ?: 'Unknown');
                    $value .= '</div>';
                }

                return $value;
            }
            else {
                return $availability;
            }
        }

        return '';
    }

    public function getExpiryDate($key)
    {
        if ( in_array($key, ['driving_licence_expiry_date', 'pco_licence_expiry_date', 'phv_licence_expiry_date']) ) {
            $value = \App\Helpers\SiteHelper::formatDateTime($this->{$key}, 'date');
        }
        else {
            $value = \App\Helpers\SiteHelper::formatDateTime($this->{$key});
        }

        $class = '';

        if ( Carbon::parse($this->getOriginal($key)) <= Carbon::now()->addDays(config('site.document_expired')) ) {
            $class = 'text-red';
        }
        elseif ( Carbon::parse($this->getOriginal($key)) <= Carbon::now()->addDays(config('site.document_warning')) ) {
            $class = 'text-yellow';
        }

        if ( $class ) {
            $value = '<span class="'. $class .'">'. $value .'</span>';
        }

        return $value;
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

    public function getFullName()
    {
        $name = trim(ucfirst($this->title) .' '. ucfirst($this->first_name) .' '. ucfirst($this->last_name));

        if ( empty($name) && !empty($this->user) ) {
            $name = $this->user->getName(false);
        }

        return $name;
    }

    public function getCommission()
    {
        return !empty($this->commission) ? $this->commission .'%' : '0%';
    }

    public function getProfileType()
    {
        return !empty($this->profileTypeOptions[$this->profile_type]) ? $this->profileTypeOptions[$this->profile_type] : $this->profile_type;
    }

    public function getAvailabilityStatus($type = 'none')
    {
        $value = $this->availability_status;
        $availabilityStatusOptions = $this->availabilityStatusOptions;

        if ( !empty($availabilityStatusOptions[$value]) ) {
            if ( $type == 'label' ) {
                $value = '<span class="label" style="background:'. $availabilityStatusOptions[$value]['color'] .';">'. $availabilityStatusOptions[$value]['name'] .'</span>';
            }
            elseif ( $type == 'color' ) {
                $value = '<span style="color:'. $availabilityStatusOptions[$value]['color'] .';">'. $availabilityStatusOptions[$value]['name'] .'</span>';
            }
            else {
                $value = $availabilityStatusOptions[$value]['name'];
            }
        }

        return $value;
    }
}
