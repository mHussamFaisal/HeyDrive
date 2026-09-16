<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'profile';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;

    public function getName()
    {
        return ucfirst($this->name);
    }
}
