<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public $statusOptions = [];
    public $units = 0;

    function __construct()
    {
        parent::__construct();

        $this->statusOptions = [
            'active' => [
                'name' => trans('common.vehicle_status_options.activated'),
                'color' => '#00a65a'
            ],
            'inactive' => [
                'name' => trans('common.vehicle_status_options.inactive'),
                'color' => '#dd4b39'
            ]
        ];

        $this->units = config('site.booking_distance_unit') == 0 ? 1609.34 : 1000; // mi | km
    }

    public function relation()
    {
        return $this->morphTo();
    }

    public function getLatAttribute($value)
    {
        return number_format($value, 6, '.', '');
    }

    public function getLngAttribute($value)
    {
        return number_format($value, 6, '.', '');
    }

    public function getRadiusAttribute($value)
    {
        return number_format($value, 2, '.', '');
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

    public function scopeIsActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, $type = '')
    {
        return $query->where('type', $type);
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

        // $sql = "(ST_Distance_Sphere(
        //     point(`lng`, `lat`),
        //     point(". $lng .", ". $lat .")
        // ) / ". $this->units .") AS `distance`";

        return $query->addSelect(\DB::raw($sql));
    }

    public function scopeCloseTo($query, $lat = 0, $lng = 0)
    {
        // $query->withDistance($lat, $lng);

        $sql = "(SELECT 6370986 * acos(cos(radians({$lat}))
           * cos(radians(`lat`))
           * cos(radians(`lng`) - radians({$lng}))
           + sin(radians({$lat}))
           * sin(radians(`lat`)))
        ) / {$this->units} <= `radius`";

        // $sql = "ST_Distance_Sphere(
        //     point(`lng`, `lat`),
        //     point({$lng}, {$lat})
        // ) / ". $this->units ." <= `radius`";

        return $query->whereRaw($sql);
    }
}
