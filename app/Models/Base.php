<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jackpopp\GeoDistance\GeoDistanceTrait;

class Base extends Model
{
    protected $table = 'bases';

    protected $fillable = [
        // 'lat',
        // 'lng'
    ];

    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public $statusOptions = [];

    function __construct()
    {
        parent::__construct();

        $this->statusOptions = [
            'activated' => [
                'name' => trans('common.base_status_options.activated'),
                'color' => '#00a65a'
            ],
            'inactive' => [
                'name' => trans('common.base_status_options.inactive'),
                'color' => '#dd4b39'
            ]
        ];
    }

    public function setSiteIdAttribute($value)
    {
        $this->attributes['site_id'] = $value ?: 0;
    }

    public function setOrderingAttribute($value)
    {
        $this->attributes['ordering'] = $value ? 1 : 0;
    }

    public function setSelectedAttribute($value)
    {
        $this->attributes['selected'] = $value ? 1 : 0;
    }

    public function getOriginalForm($key)
    {
        $value = null;

        if ( $this->getOriginal($key) ) {
            $value = $this->getOriginal($key);
        }

        return $value;
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
}
