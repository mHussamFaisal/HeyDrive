<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightAirline extends Model
{
    protected $table = 'flight_airlines';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];

    function __construct()
    {
        parent::__construct();
    }
}
