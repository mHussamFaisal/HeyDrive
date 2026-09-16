<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\SiteHelper;

class ScheduledRoute extends Model
{
    protected $table = 'scheduled_routes';
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

    public function from()
    {
        return $this->morphOne('App\Models\Location', 'relation')->where('type', 'from');
    }

    public function to()
    {
        return $this->morphOne('App\Models\Location', 'relation')->where('type', 'to');
    }

    public function event()
    {
        return $this->morphOne('App\Models\Event', 'relation');
    }

    public function driver()
    {
        return $this->hasOne('App\Models\User', 'id', 'driver_id');
    }

    public function vehicle()
    {
        return $this->hasOne('App\Models\Vehicle', 'id', 'vehicle_id');
    }

    public function vehicleType()
    {
        return $this->hasOne('App\Models\VehicleType', 'id', 'vehicle_type_id');
    }

    public function getName()
    {
        $value = '';

        if (!empty($this->from)) {
            $value .= $this->from->address;
        }

        if (!empty($this->to)) {
            $value .= ($value ? ' - ' : ''). $this->to->address;
        }

        if (!empty($this->event->start_at)) {
            $value .= ($value ? ' ' : ''). SiteHelper::formatDateTime($this->event->start_at, 'time');
        }

        return $value;
    }

    public function getParams($type = 'none')
    {
        $params = json_decode($this->getOriginal('params'));

        if ($type == 'raw') {
            $value = (object)[
                'factor_type' => isset($params->factor_type) ? $params->factor_type : 'addition',
                'factor_value' => isset($params->factor_value) ? $params->factor_value : 0,
                'commission' => isset($params->commission) ? $params->commission : 0,
            ];
        }
        else {
            $value = '';

            if (!empty($params->factor_value)) {
                $value .= trans('admin/scheduled_routes.factor') .': '. SiteHelper::formatPrice($params->factor_value) .'<br>';
            }

            if (!empty($params->commission)) {
                $value .= trans('admin/scheduled_routes.commission') .': '. SiteHelper::formatPrice($params->commission) .'<br>';
            }

            if (!empty($this->event->id)) {
                $value .= $this->event->getSummary();
            }
        }

        return $value;
    }

    public function getStatus($type = 'none')
    {
        switch ($this->status) {
            case 'active':
                $name = trans('admin/scheduled_routes.statuses.active');
                $color = '#00a65a';
            break;
            default:
                $name = trans('admin/scheduled_routes.statuses.inactive');
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
