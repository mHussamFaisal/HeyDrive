<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDriver extends Model
{
    use SoftDeletes;

    protected $table = 'booking_drivers';

    protected $casts = [
        'status' => 'integer',
        'commission' => 'double',
        'cash' => 'double',
        'auto_assigned' => 'integer'
    ];

    protected $dates = [
        'expire_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    function __construct()
    {
        parent::__construct();
    }

    public function booking()
    {
        return $this->belongsTo('App\Models\BookingRoute', 'booking_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo('App\Models\User', 'driver_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id', 'id');
    }

    public function getStatus()
    {
        $status = 'Unknown';

        switch ($this->status) {
            case 0:
                $status = trans('common.booking_status_options.assigned');
            break;
            case 1:
                $status = trans('common.booking_status_options.accepted');
            break;
            case 2:
                $status = trans('common.booking_status_options.rejected');
            break;
        }

        return $status;
    }
}
