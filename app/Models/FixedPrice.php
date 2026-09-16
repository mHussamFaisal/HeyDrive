<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class FixedPrice extends Model
{
    protected $table = 'fixed_prices';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'start_date',
        'end_date',
        'modified_date',
    ];

    public $timestamps = false;
    public $units = 0;

    function __construct()
    {
        parent::__construct();

        $this->units = config('site.booking_distance_unit') == 0 ? 1609.34 : 1000; // mi | km
    }

    public function zones($type = '')
    {
        $model = $this->belongsToMany('App\Models\Location', 'relations', 'relation_id', 'target_id')
          ->withPivot(['relation_type', 'type'])
          ->wherePivot('relation_type', 'fixed_price_location');

        if ($type == 'from') {
            $model->wherePivot('type', 'from');
        }
        elseif ($type == 'to') {
            $model->wherePivot('type', 'to');
        }

        $model->orderBy('id', 'desc');

        return $model;
    }

    public function scopeWithZoneDistance($query, $route, $inRadius = true)
    {
        $prefix = get_db_prefix();

        if (is_null($query->getQuery()->columns)) {
            $query->addSelect("*");
        }

        $query->addSelect(DB::raw("IF(IFNULL(`z_f_distance`, 0) <= IFNULL(`z_f_radius`, 0), 1, 0) AS `z_f_in_radius`"));
        $query->addSelect(DB::raw("IF(IFNULL(`z_t_distance`, 0) <= IFNULL(`z_t_radius`, 0), 1, 0) AS `z_t_in_radius`"));
        $query->leftJoin(DB::raw($this->__distanceSql($route->from->lat, $route->from->lng, 'from', 'f')), function($join) {
            $join->on('z_f_price_id', '=', 'fixed_prices.id');
        });
        $query->leftJoin(DB::raw($this->__distanceSql($route->to->lat, $route->to->lng, 'to', 't')), function($join) {
            $join->on('z_t_price_id', '=', 'fixed_prices.id');
        });

        $query->addSelect(DB::raw("IF(IFNULL(`z_r_f_distance`, 0) <= IFNULL(`z_r_f_radius`, 0), 1, 0) AS `z_r_f_in_radius`"));
        $query->addSelect(DB::raw("IF(IFNULL(`z_r_t_distance`, 0) <= IFNULL(`z_r_t_radius`, 0), 1, 0) AS `z_r_t_in_radius`"));
        $query->leftJoin(DB::raw($this->__distanceSql($route->to->lat, $route->to->lng, 'from', 'r_f')), function($join) {
            $join->on('z_r_f_price_id', '=', 'fixed_prices.id');
        });
        $query->leftJoin(DB::raw($this->__distanceSql($route->from->lat, $route->from->lng, 'to', 'r_t')), function($join) {
            $join->on('z_r_t_price_id', '=', 'fixed_prices.id');
        });

        $query->addSelect(DB::raw("(
            IFNULL(`z_f_distance`, 0) +
            IFNULL(`z_t_distance`, 0) +
            IFNULL(`z_r_f_distance`, 0) +
            IFNULL(`z_r_t_distance`, 0)
        ) AS `z_distance`"));

        if ($inRadius) {
            $query->whereRaw("CASE WHEN (`{$prefix}fixed_prices`.`direction` = 0)
                 THEN (
                        IFNULL(`z_f_distance`, 0) <= IFNULL(`z_f_radius`, 0)
                            AND
                        IFNULL(`z_t_distance`, 0) <= IFNULL(`z_t_radius`, 0)
                    )
                    OR (
                        IFNULL(`z_r_f_distance`, 0) <= IFNULL(`z_r_f_radius`, 0)
                            AND
                        IFNULL(`z_r_t_distance`, 0) <= IFNULL(`z_r_t_radius`, 0)
                    )
                 ELSE
                     IFNULL(`z_f_distance`, 0) <= IFNULL(`z_f_radius`, 0)
                         AND
                     IFNULL(`z_t_distance`, 0) <= IFNULL(`z_t_radius`, 0)
                 END");
        }

        $query->where('published', 1);
        $query->where('is_zone', 1);
        $query->distinct('id');
        $query->orderBy('ordering', 'asc');
        $query->orderBy('z_distance', 'asc');

        return $query;
    }

    public function __distanceSql($lat = 0, $lng = 0, $type = '', $name = '')
    {
        $prefix = get_db_prefix();

        $sql = "(SELECT DISTINCT `{$prefix}locations`.`id` AS `z_{$name}_id`,
                  `{$prefix}relations`.`relation_id` AS `z_{$name}_price_id`,
                  `{$prefix}locations`.`name` AS `z_{$name}_name`,
                  `{$prefix}locations`.`lat` AS `z_{$name}_lat`,
                  `{$prefix}locations`.`lng` AS `z_{$name}_lng`,
                  ('{$lat}'+0) AS `z_{$name}_lat_request`,
                  ('{$lng}'+0) AS `z_{$name}_lng_request`,
                  `{$prefix}locations`.`radius` AS `z_{$name}_radius`";

         $sql .= ", (SELECT 6370986 * acos(cos(radians({$lat}))
                    * cos(radians(`{$prefix}locations`.`lat`))
                    * cos(radians(`{$prefix}locations`.`lng`) - radians({$lng}))
                    + sin(radians({$lat}))
                    * sin(radians(`{$prefix}locations`.`lat`)))
                 ) / {$this->units} AS `z_{$name}_distance`";

        // $sql .= ", ST_Distance_Sphere(
        //              point(`{$prefix}locations`.`lng`, `{$prefix}locations`.`lat`),
        //              point({$lng}, {$lat})
        //          ) / {$this->units} AS `z_{$name}_distance_2`";

        $sql .= "FROM `{$prefix}relations`
              INNER JOIN `{$prefix}locations`
                  ON `{$prefix}locations`.`id`=`{$prefix}relations`.`target_id`
                  AND `{$prefix}locations`.`relation_id`='0'
                  AND `{$prefix}locations`.`relation_type`='site'
                  AND `{$prefix}locations`.`type`='zone'
                  AND `{$prefix}locations`.`status`='active'
              WHERE `{$prefix}relations`.`relation_type`='fixed_price_location'
              AND `{$prefix}relations`.`type`='{$type}'
              ORDER BY `z_{$name}_distance` ASC) AS `z_{$name}`";

        return $sql;
    }
}
