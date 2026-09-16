<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatus extends Model
{
    public $timestamps = false;

    protected $dates = [
        'timestamp',
    ];
}
