<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingParam extends Model
{
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;

    public function booking() {
        return $this->belongsTo('App\Models\BookingRoute', 'booking_id', 'id');
    }

    public function getValueAttribute($value) {
        return !empty($value) && preg_match('#^{#', $value) ? json_decode($value) : $value;
    }

    public function setValueAttribute($value) {
        $this->attributes['value'] = !empty($value) && (is_array($value) || is_object($value)) ? json_encode((array)$value) : $value;
    }
}
