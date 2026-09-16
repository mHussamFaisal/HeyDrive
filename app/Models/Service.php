<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function getParams($type = 'none')
    {
        $params = json_decode($this->getOriginal('params'));

        if ($type == 'raw') {
            $value = (object)[
                'availability' => isset($params->availability) ? $params->availability : 0,
                'hide_location' => isset($params->hide_location) ? $params->hide_location : 0,
                'duration' => isset($params->duration) ? $params->duration : 0,
                'duration_min' => isset($params->duration_min) ? $params->duration_min : 0,
                'duration_max' => isset($params->duration_max) ? $params->duration_max : 0,
                'factor_type' => isset($params->factor_type) ? $params->factor_type : 'addition',
                'factor_value' => isset($params->factor_value) ? $params->factor_value : 0,
            ];
        }
        else {
            $value = '';

            if (!empty($params->availability)) {
                $value .= trans('admin/services.availability') .'<br>';
            }

            if (!empty($params->hide_location)) {
                $value .= trans('admin/services.hide_location') .'<br>';
            }

            if (!empty($params->duration)) {
                $value .= trans('admin/services.duration') .' '. $params->duration_min .'-'. $params->duration_max .'h<br>';
            }

            if (!empty($params->factor_value)) {
                $factor_value = $params->factor_type == 'addition' ? \App\Helpers\SiteHelper::formatPrice($params->factor_value) : trans('admin/services.factor_types.'. $params->factor_type .'_symbol') . $params->factor_value;
                $value .= trans('admin/services.factor') .' '. $factor_value .'<br>';
            }
        }

        return $value;
    }

    public function getStatus($type = 'none')
    {
        switch ($this->status) {
            case 'active':
                $name = trans('admin/services.statuses.active');
                $color = '#00a65a';
            break;
            default:
                $name = trans('admin/services.statuses.inactive');
                $color = '#dd4b39';
            break;
        }

        switch ($type) {
            case 'label':
                $value = '<span class="label" style="background:'. $color .';">'. $name .'</span>';
            break;
            case 'color':
                $value = '<span style="color:'. $color .';">'. $name .'</span>';
            break;
            default:
                $value = $name;
            break;
        }

        return $value;
    }
}
