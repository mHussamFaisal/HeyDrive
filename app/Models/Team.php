<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    // use SoftDeletes;

    protected $table = 'teams';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'subscription_id' => 'integer',
        'name' => 'string',
        'status' => 'integer',
        'order' => 'integer',
        'internal_note' => 'string'
    ];

    function __construct()
    {
        parent::__construct();
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'team_user', 'team_id', 'user_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    public function scopeInactive($q)
    {
        return $q->where('status', 0);
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function getStatus($format = null)
    {
        return \App\Helpers\FormHelper::getStatus($this->status, $format, 'team');
    }
}
