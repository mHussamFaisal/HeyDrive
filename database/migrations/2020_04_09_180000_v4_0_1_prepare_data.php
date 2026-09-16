<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401PrepareData extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('locations')) {
            DB::statement("ALTER TABLE `{$prefix}locations` ENGINE = InnoDB");
        }
        if (Schema::hasTable('scheduled_routes')) {
            DB::statement("ALTER TABLE `{$prefix}scheduled_routes` ENGINE = InnoDB");
        }
        if (Schema::hasTable('fields')) {
            DB::statement("ALTER TABLE `{$prefix}fields` ENGINE = InnoDB");
        }
        if (Schema::hasTable('feedback')) {
            DB::statement("ALTER TABLE `{$prefix}feedback` ENGINE = InnoDB");
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('param', 'last_verify')->delete();
        }
    }

    public function down()
    {
        //
    }
}
