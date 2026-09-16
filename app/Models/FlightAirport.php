<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightAirport extends Model
{
    protected $table = 'flight_airports';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];

    function __construct()
    {
        parent::__construct();
    }
}
