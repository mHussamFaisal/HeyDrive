<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Helpers\SiteHelper;

class Event extends Model
{
    protected $table = 'events';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'start_at',
        'end_at',
        'repeat_end',
        'created_at',
        'updated_at',
    ];

    public $options = [];

    function __construct()
    {
        parent::__construct();

        $this->options = (object)[
            'status' => [
                'active' => [
                    'name' => trans('common.event_status_options.active'),
                    'color' => '#00a65a'
                ],
                'inactive' => [
                    'name' => trans('common.event_status_options.inactive'),
                    'color' => '#dd4b39'
                ]
            ],
            'repeat_type' => [
                'none' => trans('driver/calendar.options.repeat_type.none'),
                'daily' => trans('driver/calendar.options.repeat_type.daily'),
                'weekly' => trans('driver/calendar.options.repeat_type.weekly'),
                'monthly' => trans('driver/calendar.options.repeat_type.monthly'),
                'yearly' => trans('driver/calendar.options.repeat_type.yearly')
            ]
        ];
    }

    public function setRelationIdAttribute($value)
    {
        $this->attributes['relation_id'] = $value ?: 0;
    }

    public function setStartAtAttribute($value)
    {
        $this->attributes['start_at'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setEndAtAttribute($value)
    {
        $this->attributes['end_at'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setRepeatEndAttribute($value)
    {
        $this->attributes['repeat_end'] = empty($value) ? null : Carbon::parse($value);
    }

    public function setRepeatLimitAttribute($value)
    {
        $this->attributes['repeat_limit'] = $value ?: 0;
    }

    public function setRepeatIntervalAttribute($value)
    {
        $this->attributes['repeat_interval'] = $value ?: 0;
    }

    public function setOrderingAttribute($value)
    {
        $this->attributes['ordering'] = $value ?: 0;
    }

    public function getOriginalForm($key)
    {
        $value = null;

        if ( $this->getOriginal($key) ) {
            $value = $this->getOriginal($key);
        }

        return $value;
    }

    public function getStatus($type = 'none')
    {
        $value = $this->status;

        if ( !empty($this->options->status[$value]) )
        {
            if ( $type == 'label' ) {
                $value = '<span class="label" style="background:'. $this->options->status[$value]['color'] .';">'. $this->options->status[$value]['name'] .'</span>';
            }
            elseif ( $type == 'color' ) {
                $value = '<span style="color:'. $this->options->status[$value]['color'] .';">'. $this->options->status[$value]['name'] .'</span>';
            }
            else {
                $value = $this->options->status[$value]['name'];
            }
        }

        return $value;
    }

    public function availability($type = 'check', $start = null, $end = null)
    {
        $available = 0;
        $events = [];

        $rsd = !empty($start) ? Carbon::parse($start) : Carbon::now();
        $red = !empty($end) ? Carbon::parse($end) : $rsd->copy();

        $vsd = $rsd->copy()->startOfDay();
        $ved = $red->copy()->endOfDay();

        $this->repeat_days = !empty($this->repeat_days) ? json_decode($this->repeat_days) : [$this->start_at->dayOfWeek];
        $this->repeat_interval = !empty($this->repeat_interval) ? $this->repeat_interval : 1;

        for ($i = 0; $i <= $vsd->diffInDays($ved); $i++) {
            $sd = $vsd->copy()->addDays($i)->setTime($this->start_at->hour, $this->start_at->minute, $this->start_at->second);
            $ed = $sd->copy()->addSeconds($this->start_at->diffInSeconds($this->end_at));

            $skip = 0;
            $diff = 0;
            $time = 0;

            switch ($this->repeat_type) {
                case 'daily':
                    $diff = $sd->diffInDays($this->start_at);
                    $time = $sd->copy()->subDays($diff)->diffInSeconds($this->start_at);
                break;
                case 'weekly':
                    $diff = $sd->diffInDays($this->start_at);
                    $time = $sd->copy()->subDays($diff)->diffInSeconds($this->start_at);
                break;
                case 'monthly':
                    $diff = $sd->diffInMonths($this->start_at);
                    $time = $sd->copy()->subMonths($diff)->diffInSeconds($this->start_at);
                break;
                case 'yearly':
                    $diff = $sd->diffInYears($this->start_at);
                    $time = $sd->copy()->subYears($diff)->diffInSeconds($this->start_at);
                break;
                default:
                    $diff = $sd->diffInDays($this->start_at);
                    $time = $sd->copy()->subDays($diff)->diffInSeconds($this->start_at);

                    $this->repeat_interval = 1;
                    $this->repeat_limit = 1;
                break;
            }

            // Weekly
            if ($this->repeat_type == 'weekly' && !in_array($sd->dayOfWeek, $this->repeat_days)) {
                $skip = 1;
            }

            // The same day of week
            if ($time != 0) {
                $skip = 2;
            }

            // Lower then start date
            if ($sd->lt($this->start_at)) {
                $skip = 3;
            }

            // Repeat end date
            if (!empty($this->repeat_end) && $ed->gte($this->repeat_end)) {
                $skip = 4;
            }

            // Repeat interval
            if (!empty($this->repeat_interval) && !empty($diff % $this->repeat_interval)) {
                $skip = 5;
            }

            // Repeat limit
            if (!empty($this->repeat_limit) && $diff >= ($this->repeat_limit * $this->repeat_interval)) {
                $skip = 6;
            }

            // echo $skip;

            if ($skip) {
                continue;
            }

            if ($rsd->gte($sd) && $red->lte($ed)) {
                $available = 1;
            }

            // if ( $this->status == 'active' ) {
                // if ( $rsd->gte($sd) && $red->lte($ed) ) {
                //     $available = 1;
                // }
            // }
            // else {
                // if ( $ed->gt($rsd) && $sd->lt($red) ) {
                //     $available = 0;
                // }
            // }

            $events[] = [
                'id' => $this->id,
                'title' => $this->name,
                'start' => $sd->toDateTimeString(),
                'end' => $ed->toDateTimeString()
                // 'available' => $available,
            ];
        }

        // dd($available, $events, $start, $end, $rsd, $red);

        return $type == 'check' ? $available : $events;
    }

    public function getSummary($format = 'html')
    {
        $results = [];

        if ($this->start_at) {
            $results[] = trans('admin/scheduled_routes.start_at') .': '. SiteHelper::formatDateTime($this->start_at->toDateTimeString(), 'datetime');
        }

        if ($this->end_at) {
            $results[] = trans('admin/scheduled_routes.end_at') .': '. SiteHelper::formatDateTime($this->end_at->toDateTimeString(), 'datetime');
        }

        if ($this->repeat_type != 'none') {
            $results[] = trans('admin/scheduled_routes.repeat_type') .': '. trans('admin/scheduled_routes.repeat_types.'. $this->repeat_type);

            if ($this->repeat_days && $this->repeat_type == 'weekly') {
                $days = '';
                foreach (json_decode($this->repeat_days) as $k => $v) {
                    $days .= ($days ? ', ' : '') . str_limit(trans('admin/scheduled_routes.repeat_days_'. $v), 3, '');
                }
                if ($days) {
                    $results[] = trans('admin/scheduled_routes.repeat_days_title') .': '. $days;
                }
            }

            if ($this->repeat_interval) {
                $results[] = trans('admin/scheduled_routes.repeat_interval') .': '. $this->repeat_interval;
            }

            if ($this->repeat_limit) {
                $results[] = trans('admin/scheduled_routes.repeat_limit') .': '. $this->repeat_limit;
            }

            if ($this->repeat_end) {
                $results[] = trans('admin/scheduled_routes.repeat_end') .': '. SiteHelper::formatDateTime($this->repeat_end->toDateTimeString(), 'datetime');
            }
        }

        if ( $format == 'html' ) {
            $html = '';
            foreach ($results as $k => $v) {
                $html .= trim($v) .'<br>';
            }
            $results = $html;
        }

        return $results;
    }
}
