<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepareSettingsData extends Migration
{
    public function up()
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                $keys = [
                    'available_versions',
                    'booking',
                    'booking.form.settings',
                    'calendar',
                    'core.version',
                    'cron',
                    'cron.update',
                    'global.settings',
                    'disks',
                    'map.settings',
                    'news',
                ];

                $keysFirstLevel = [
                    // 'disks',
                ];

                $keysParseDot = [
                    'booking_form_settings',
                    'core_version',
                    'cron_update',
                    'global_settings',
                    'map_settings',
                ];

                if (!function_exists('recursive_merge_dot_array')) {
                    function recursive_merge_dot_array($array, $fullKey = '', $newArray = [], $firstLevel = false) {
                        foreach($array as $key=>$value) {
                            $realKey = !empty($fullKey) ? $fullKey .'.'. $key : $key;
                            if ($firstLevel) {
                                $newArray[$realKey] = $value;
                            } elseif (is_array($value) || is_object($value)) {
                                $newArray = array_merge($newArray, recursive_merge_dot_array($value, $realKey, $newArray));
                            } else {
                                $newArray[$realKey] = $value;
                            }
                        }
                        return $newArray;
                    }
                }

                if (!function_exists('recursive_cast_dot_array')) {
                    function recursive_cast_dot_array($array) {
                        $keysNotParse = [
                            'allowed_times',
                            'driver_name',
                            'inactive_drivers_form',
                            'private_key',
                            'secret_key',
                            'send_notification',
                            'waiting_time',
                        ];
                        foreach($array as $key => $item) {
                            unset($array[$key]);

                            if (is_array($item) || is_object($item)) {
                                $array[$key] = recursive_cast_dot_array($item);
                            } elseif (in_array($key, $keysNotParse)) {
                                $array[$key] = $item;
                            } else {
                                $type = value_type_of($item);
                                $item = value_cast_to($item, $type, false);

                                if (!preg_match('#(^last_verify|^available_versions)#', $key)) {
                                    $key = str_replace('_', '.', $key);
                                }

                                $key = str_replace('custom.field.', 'custom_field.', $key);
                                $key = str_replace('show.driver.name', 'show.driver_name', $key);
                                $key = str_replace('status.color.', 'status_color.', $key);
                                $key = str_replace('-', '_', $key);
                                $key = snake_case($key);
                                $array[$key] = $item;
                            }
                        }
                        return $array;
                    }
                }

                $settings = \App\Models\Setting::all();
                $newSettings = [];
                foreach($settings as $item) {
                    $type = !empty($casts[$item->param]) ? $casts[$item->param] : value_type_of($item->value);
                    $item->value = value_cast_to($item->value, $type);
                    data_set($newSettings[$item->relation_type][$item->relation_id], $item->param, $item->value);
                    $item->delete();
                }
                $newSettings = recursive_cast_dot_array($newSettings);

                foreach($newSettings as $key=>$item) {
                    foreach($item as $id=>$data) {
                        foreach($data as $k=>$v) {
                            unset($newSettings[$key][$id][$k]);
                            if (in_array($k, $keysParseDot)) {
                                $k = str_replace('_', '.', $k);
                            }

                            if ((in_array($k, $keys) || in_array($k, $keysFirstLevel)) && is_array($v)) {
                                $newSettings[$key][$id] = array_merge($newSettings[$key][$id], recursive_merge_dot_array($v, $k, [], in_array($k, $keysFirstLevel)));
                            } else {
                                $newSettings[$key][$id][$k] = $v;
                            }
                        }
                    }
                }

                $settingsToSet = [];

                foreach ($newSettings as $key=>$item) {
                    foreach ($item as $id=>$data) {
                        foreach ($data as $k=>$v) {
                            if (is_array($v)) { continue; }
                            $k = str_replace(['.settings','map_'], '', $k);
                            $k = preg_replace('#^booking.#', 'eto_booking.', $k);
                            $k = preg_replace('#^map.#', 'eto_map.', $k);
                            $k = preg_replace('#^cron.#', 'eto_cron.', $k);
                            $k = preg_replace('#^calendar.#', 'eto_calendar.', $k);
                            $k = preg_replace('#^(locale|timezone)#', 'app.'.$k, $k);
                            $k = preg_replace('#^disks.#', 'filesystems.disks.', $k);
                            $k = preg_replace('#^global.#', 'eto.', $k);
                            $k = preg_replace('#^last_verify#', 'eto.last_verify', $k);
                            $k = preg_replace('#^available_versions.#', 'eto.available_versions.', $k);
                            $k = preg_replace('#^core.version#', 'app.version', $k);

                            $settingsToSet[] = [$k, $v, $key, $id];
                        }
                    }
                }

                settings_save($settingsToSet);
            });
        }
    }

    public function down()
    {
        //
    }
}
