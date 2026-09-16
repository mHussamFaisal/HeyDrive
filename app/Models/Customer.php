<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'user';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;

    public function profile()
    {
        return $this->hasOne('App\Models\CustomerProfile', 'user_id');
    }

    public function getAvatarPath()
    {
        if (!empty($this->avatar) && \Storage::disk('avatars')->exists($this->avatar)) {
            $path = asset_url('uploads','avatars/'. $this->avatar);
        }
        else {
            $path = asset_url('images','placeholders/avatar.png');
        }

        return $path;
    }

    public function getDepartmentsAttribute($value) {
        return !empty($value) ? json_decode($value) : [];
    }

    public function setDepartmentsAttribute($value) {
        $this->attributes['departments'] = !empty($value) ? json_encode((array)$value) : null;
    }
}
