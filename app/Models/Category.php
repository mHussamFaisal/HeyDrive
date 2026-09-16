<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';

    protected $casts = [
        'site_id' => 'integer',
        'featured' => 'integer',
        'ordering' => 'integer',
        'published' => 'integer',
    ];

    protected $appends = [];
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
}
