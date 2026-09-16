<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    protected $table = 'user_customer';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;
}
