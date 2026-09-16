<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use UsesUuid;

    protected $table = 'news';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];

    function __construct()
    {
        parent::__construct();
    }
}
