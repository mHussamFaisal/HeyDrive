<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    // use SoftDeletes;

    protected $casts = [
        'subscription_id' => 'integer',
        'parent_id' => 'integer',
        'status' => 'integer'
    ];

    protected $appends = [
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
        'deleted_at'
    ];

    public $errors = []; // Needed in modules controller, line 100

    function __construct()
    {
        parent::__construct();
    }

    public function subscriptions() {
        return $this->hasMany('App\Models\SubscriptionModule', 'module_id');
    }

    public function getStatusTextAttribute()
    {
        $status = $this->status == 1 ? 'active' : 'inactive';
        return trans('scheduled_routes.statuses.'. $status);
    }

    public function getParamsAttribute($value)
    {
        try {
            $value = decrypt($value);
        }
        catch (\Exception $e) {
            $value = null;
            \Log::error('Model Module::getParamsAttribute decrypt error: '. $e->getMessage());
        }

        return !empty($value) ? \GuzzleHttp\json_decode($value) : new \stdClass();
    }

    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = encrypt(\GuzzleHttp\json_encode($value));
    }

    private function parseData($data = [], $casts = []) {
        foreach ($data as $k => $v) {
            if (isset($casts[$k])) {
                switch ($casts[$k]) {
                    case 'int':
                        $v = (int)$v;
                    break;
                    case 'float':
                        $v = (float)$v;
                    break;
                    case 'object':
                        $v = json_decode($v);
                    break;
                    case 'array':
                        $v = json_decode($v, true);
                    break;
                    default:
                        $v = (string)$v;
                    break;
                }

                $data[$k] = $v;
            }
        }

        return \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($data));
    }
}
