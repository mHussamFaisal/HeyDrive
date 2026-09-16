<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
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

    public function getIsCorruptedAttribute($value)
    {
        try {
            $params = decrypt($this->attributes['params']);
        }
        catch (\Exception $e) {
            $params = null;
            \Log::error('Model Subscription::getIsCorruptedAttribute decrypt error: '. $e->getMessage());
        }

        $this->attributes['params'] = $params;
        return md5($this->attributes['license'].$this->attributes['params'].$this->attributes['expire_at']) != $this->attributes['hash'];
    }

    public function setLicenseAttribute($value)
    {
        $this->attributes['license'] = encrypt($value);
    }

    public function getLicenseAttribute($value)
    {
        try {
            $value = decrypt($value);
        }
        catch (\Exception $e) {
            $value = null;
            \Log::error('Model Subscription::getLicenseAttribute decrypt error: '. $e->getMessage());
        }

        return !empty($value) ? $value : null;
    }

    public function getStatusTextAttribute()
    {
        $status = $this->status == 1 ? 'active' : 'inactive';
        return trans('scheduled_routes.statuses.'. $status);
    }

    public function getParamsAttribute($value)
    {
        try {
            $params = decrypt($this->attributes['params']);
        }
        catch (\Exception $e) {
            $params = null;
            \Log::error('Model Subscription::getParamsAttribute decrypt error: '. $e->getMessage());
        }

        return !empty($params) ? \GuzzleHttp\json_decode($params) : new \stdClass();
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = encrypt(\GuzzleHttp\json_encode($value));
    }
}
