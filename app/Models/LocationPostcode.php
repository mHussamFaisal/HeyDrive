<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationPostcode extends Model
{
    protected $table = 'location';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];

    public function getTableName() {
        return $this->table;
    }
}
