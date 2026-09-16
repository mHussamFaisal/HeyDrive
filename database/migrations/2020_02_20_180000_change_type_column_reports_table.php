<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeColumnReportsTable extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('reports')) {
            DB::statement("ALTER TABLE `{$prefix}reports` CHANGE `type` `type` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        }
    }

    public function down()
    {
        //
    }
}
