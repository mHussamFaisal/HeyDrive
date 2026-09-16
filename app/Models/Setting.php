<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'relation_id',
        'relation_type',
        'param',
        'value',
    ];

    protected $hidden = [];
    protected $guarded = [];
    public $timestamps = false;

    public function relation()
    {
        return $this->morphTo();
    }

    public static function scopeToObject($query, $group = false)
    {
        $settings = [];
        $q = $query->orderBy('param', 'asc')->get();

        foreach ($q as $k => $v) {
            $v->value = value_cast_to($v->value, value_type_of($v->value));

            if ($group == true) {
                $settings[$v->relation_type][$v->param] = $v->value;
            }
            else {
                $settings[$v->param] = $v->value;
            }
        }

        return (object)$settings;
    }
}
