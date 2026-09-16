<?php

namespace App\Helpers;

class FormHelper
{
    public static function getStatusList($type = null, $format = null)
    {
        $collection = collect([
            0 => [
                'id' => 'inactive',
                'name' => trans('common.status.inactive'),
                'color' => '#ce4535'
            ],
            1 => [
                'id' => 'active',
                'name' => trans('common.status.active'),
                'color' => '#00a65a'
            ],
        ]);

        switch ($format) {
            case 'id':
            case 'name':
                return $collection->pluck($format)->toArray();
            break;
            default:
                return $collection;
            break;
        }
    }

    public static function getStatus($status = null, $format = null, $type = null)
    {
        $statusList = self::getStatusList($type);
        $value = trans('common.status.unknown');

        if (!is_null($status) && !is_null($statusList[$status])) {
            switch ($format) {
                case 'label':
                    $value = '<span class="label" style="background:'. $statusList[$status]['color'] .';">'. $statusList[$status]['name'] .'</span>';
                break;
                case 'color':
                    $value = '<span style="color:'. $statusList[$status]['color'] .';">'. $statusList[$status]['name'] .'</span>';
                break;
                default:
                    $value = $statusList[$status]['name'];
                break;
            }
        }

        return $value;
    }
}
