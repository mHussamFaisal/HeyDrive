<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserParam extends Model
{
    protected $table = 'user_params';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;
}
