<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $appends = [
        'mot_expiry_date_formatted',
        'image_path'
    ];

    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'mot_expiry_date'
    ];

    public $statusOptions = [];

    function __construct()
    {
        parent::__construct();

        $this->statusOptions = [
            'activated' => [
                'name' => trans('common.vehicle_status_options.activated'),
                'color' => '#00a65a'
            ],
            'inactive' => [
                'name' => trans('common.vehicle_status_options.inactive'),
                'color' => '#dd4b39'
            ]
        ];
    }

    public function getMotExpiryDateFormattedAttribute()
    {
        $value = empty($this->attributes['mot_expiry_date']) ? '' : \App\Helpers\SiteHelper::formatDateTime($this->attributes['mot_expiry_date'], 'date');
        return $value;
    }

    public function getImagePathAttribute()
    {
        if (!empty($this->attributes['image']) && \Storage::disk('vehicles')->exists($this->attributes['image'])) {
            $path = asset_url('uploads','vehicles/'. $this->attributes['image']);
        }
        else {
            $path = asset_url('images','placeholders/vehicle.png');
        }

        return $path;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value ?: 0;
    }

    public function setNoOfPassengersAttribute($value)
    {
        $this->attributes['no_of_passengers'] = $value ?: 0;
    }

    public function setMotExpiryDateAttribute($value)
    {
        $this->attributes['mot_expiry_date'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setSelectedAttribute($value)
    {
        $this->attributes['selected'] = $value ? 1 : 0;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function vehicleType()
    {
        return $this->hasOne('App\Models\VehicleType', 'id', 'vehicle_type_id');
    }

    public function getOriginalForm($key)
    {
        $value = null;

        if ( $this->getOriginal($key) ) {
            $value = $this->getOriginal($key);
        }

        return $value;
    }

    public function getImagePath()
    {
        if ( \Storage::disk('vehicles')->exists($this->image) ) {
            $path = asset_url('uploads','vehicles/'. $this->image);
        }
        else {
            $path = asset_url('images','placeholders/vehicle.png');
        }

        return $path;
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function getStatus($type = 'none')
    {
        $value = $this->status;

        if ( !empty($this->statusOptions[$value]) ) {
            if ( $type == 'label' ) {
                $value = '<span class="label" style="background:'. $this->statusOptions[$value]['color'] .';">'. $this->statusOptions[$value]['name'] .'</span>';
            }
            elseif ( $type == 'color' ) {
                $value = '<span style="color:'. $this->statusOptions[$value]['color'] .';">'. $this->statusOptions[$value]['name'] .'</span>';
            }
            else {
                $value = $this->statusOptions[$value]['name'];
            }
        }

        return $value;
    }

    public function getExpiryDate($key)
    {
        $value = \App\Helpers\SiteHelper::formatDateTime($this->{$key});
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
}
