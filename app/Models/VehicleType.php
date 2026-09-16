<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $table = 'vehicle';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];

    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value ?: 0;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function site()
    {
        return $this->hasOne('App\Models\Site', 'id', 'site_id');
    }

    public function getOriginalForm($key)
    {
        $value = null;

        if ( $this->getOriginal($key) ) {
            $value = $this->getOriginal($key);
        }

        return $value;
    }

    public function getName()
    {
        return ucfirst($this->name);
    }
}
