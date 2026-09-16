<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'fields';

    // protected $fillable = [
    //    'relation_id',
    //    'relation_type',
    //    'params',
    // ];

    protected $hidden = [
        'relation_id',
        'relation_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_core' => 'integer',
        'is_edit' => 'integer',
        'is_required' => 'integer',
        'status' => 'integer',
        'order' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public static function scopeToList($query, $type, $id)
    {
        return $query->where('relation_type', $type)->whereIn('relation_id', [0, $id]);
    }

    public static function scopeIsActive($query)
    {
        return $query->where('status', 1);
    }

    public static function translateNames($fieldsList)
    {
        $result = [];
        for($i = 0; $i < count($fieldsList); $i++) {
            $id = $fieldsList[$i]->id;
            $translateKey = isset($fieldsList[$i]->params->trans_key)
                ? ($fieldsList[$i]->params->trans_key[0] == '|'
                    ? str_replace('|', '', $fieldsList[$i]->params->trans_key)
                    : $fieldsList[$i]->relation_type . '.' . $fieldsList[$i]->params->trans_key)
                : $fieldsList[$i]->relation_type . '.' . $fieldsList[$i]->field_key;

            if ($fieldsList[$i]->params->name !== null && $fieldsList[$i]->params->label !=  '') {
                $fieldsList[$i]->label = $fieldsList[$i]->params->label;
            }
            else {
                $fieldsList[$i]->label = trans($translateKey);
            }

            if ($fieldsList[$i]->params->help === null || $fieldsList[$i]->params->help ==  '') {
                $translateKeyToArrray = explode('.',$translateKey);
                $translateKey = str_replace($translateKeyToArrray[0], $translateKeyToArrray[0].'.help', $translateKey);
                $translatehelp = trans($translateKey);
                if ($translatehelp != $translateKey)
                    $fieldsList[$i]->params->help = $translatehelp;
            }
            $result[$id] = $fieldsList[$i];
        }
        return $result;
    }

    public function getSectionAttribute($value)
    {
        return explode('|', $value);
    }

    public function getParamsAttribute($value)
    {
        $value = empty($value) ? new \stdClass() : json_decode($value, true);
        $casts = [
            'is_override' => 'int',
            'is_price' => 'int',
        ];
        foreach ($value as $k => $v) {
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

                $value[$k] = $v;
            }
        }
        return (object)$value;
    }
}
