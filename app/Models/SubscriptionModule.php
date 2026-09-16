<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionModule extends Model
{
    // use SoftDeletes;

    protected $casts = [
        'status' => 'integer'
    ];

    protected $appends = [
        'is_corrupted',
        'status_text'
    ];

    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'expire_at',
        'support_at',
        'update_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    function __construct()
    {
        parent::__construct();
    }

    public function getStatusTextAttribute()
    {
        $status = $this->status == 1 ? 'active' : 'inactive';
        return trans('scheduled_routes.statuses.'. $status);
    }

    public function getIsCorruptedAttribute($value)
    {
        $this->attributes['params'] = encrypt($value);
        return md5($this->attributes['params'] . $this->attributes['expire_at']) != $this->attributes['hash'];
    }

    public function getParamsAttribute($value)
    {
        try {
            $value = decrypt($value);
        }
        catch (\Exception $e) {
            $value = null;
            \Log::error('Model SubscriptionModule::getParamsAttribute decrypt error: '. $e->getMessage());
        }

        return !empty($value) ? \GuzzleHttp\json_decode($value) : new \stdClass();
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = encrypt(\GuzzleHttp\json_encode($value));
    }
}
