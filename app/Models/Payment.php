<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';

    protected $appends = [
        'image_path'
    ];

    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;
    public $options = [];

    public function getImagePathAttribute()
    {
        if ( \Storage::disk('payments')->exists($this->attributes['image']) ) {
            $path = asset_url('uploads','payments/'. $this->attributes['image']);
        }
        else {
            $path = '';
        }

        return $path;
    }

    function __construct()
    {
        parent::__construct();

        $this->options = (object)[
            'status' => [
                // 'activated' => [
                //     'name' => trans('common.payment_status_options.activated'),
                //     'color' => '#00a65a'
                // ],
                // 'inactive' => [
                //     'name' => trans('common.payment_status_options.inactive'),
                //     'color' => '#dd4b39'
                // ]
            ]
        ];
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction', 'payment_id', 'id');
    }

    public function setSiteIdAttribute($value)
    {
        $this->attributes['site_id'] = $value ?: 0;
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

        if ( !empty($this->options->status[$value]) ) {
            if ( $type == 'label' ) {
                $value = '<span class="label" style="background:'. $this->options->status[$value]['color'] .';">'. $this->options->status[$value]['name'] .'</span>';
            }
            elseif ( $type == 'color' ) {
                $value = '<span style="color:'. $this->options->status[$value]['color'] .';">'. $this->options->status[$value]['name'] .'</span>';
            }
            else {
                $value = $this->options->status[$value]['name'];
            }
        }

        return $value;
    }
}
