<?php

namespace App\Helpers;

class UpdateOrCreate
{
    public static function records($SettingsTag, $query) {
        return \DB::statement($SettingsTag, $query);
    }
}
