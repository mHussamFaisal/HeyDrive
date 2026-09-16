<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\Activitylog\Traits\LogsActivity;

class Booking extends Model
{
    use SoftDeletes;
    // use LogsActivity;

    // protected static $logAttributes = ['*'];
    // protected static $logOnlyDirty = true;

    protected $table = 'booking';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    protected $softDelete = true;
    public $timestamps = false;

    function __construct()
    {
        parent::__construct();
    }

    public function routes()
    {
        return $this->hasMany('App\Models\BookingRoute', 'booking_id', 'id');
    }

    // public function transactions()
    // {
    //     $transactions = \App\Models\Transaction::where('relation_type', 'booking')
    //         ->where('relation_id', '=', $this->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();
    //
    //     return $transactions;
    // }

    // public function getRefNumber()
    // {
    //     return '#'. $this->ref_number;
    // }
}
