<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends Model
{
    use SoftDeletes;
    // use LogsActivity;

    // protected static $logAttributes = ['*'];
    // protected static $logOnlyDirty = true;

    protected $table = 'transactions';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'requested_at',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public $options = [];

    function __construct()
    {
        parent::__construct();

        $this->options = (object)[
            'status' => [
                'pending' => [
                    'name' => trans('common.transaction_status_options.pending'),
                    'color' => '#f39c12'
                ],
                'paid' => [
                    'name' => trans('common.transaction_status_options.paid'),
                    'color' => '#00a65a'
                ],
                'refunded' => [
                    'name' => trans('common.transaction_status_options.refunded'),
                    'color' => '#6C00D9'
                ],
                'declined' => [
                    'name' => trans('common.transaction_status_options.declined'),
                    'color' => '#dd4b39'
                ],
                'canceled' => [
                    'name' => trans('common.transaction_status_options.canceled'),
                    'color' => '#dd4b39'
                ],
                'authorised' => [
                    'name' => trans('common.transaction_status_options.authorised'),
                    'color' => '#00c0ef'
                ]
            ]
        ];
    }

    public function booking()
    {
        return $this->hasOne('App\Models\BookingRoute', 'id', 'relation_id');
    }

    public function payment()
    {
        return $this->hasOne('App\Models\Payment', 'id', 'payment_id');
    }

    public function setRelationIdAttribute($value)
    {
        $this->attributes['relation_id'] = $value ?: 0;
    }

    public function setRequestedAtAttribute($value)
    {
        $this->attributes['requested_at'] = empty($value) ? null : \Carbon\Carbon::parse($value);
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
        $value = ucfirst($this->status);

        if ( !empty($this->options->status[$this->status]) ) {
            $status = $this->options->status[$this->status];

            if ( $type == 'label' ) {
                $value = '<span class="label" style="background:'. $status['color'] .';">'. $status['name'] .'</span>';
            }
            elseif ( $type == 'color' ) {
                $value = '<span style="color:'. $status['color'] .';">'. $status['name'] .'</span>';
            }
            elseif ( $type == 'color_value' ) {
                $value = $status['color'];
            }
            else {
                $value = $status['name'];
            }
        }

        return $value;
    }

    public function getStatusList($type = 'none')
    {
        $value = [];

        foreach($this->options->status as $k => $v) {
            $value[] = (object)[
                'value' => $k,
                'text' => $v['name']
            ];
        }

        if ( $type == 'json' ) {
            $value = json_encode($value);
        }

        return $value;
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function getAmount()
    {
        return \App\Helpers\SiteHelper::formatPrice($this->getOriginal('amount'));
    }

    public function getPaymentCharge()
    {
        return \App\Helpers\SiteHelper::formatPrice($this->getOriginal('payment_charge'));
    }

    public function getPaymentMethod()
    {
        return ucfirst($this->payment_method);
    }

    public function getPaymentName()
    {
        return ucfirst($this->payment_name);
    }
}
