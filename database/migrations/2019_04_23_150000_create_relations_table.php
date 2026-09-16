<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationsTable extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (!Schema::hasTable('relations')) {
            Schema::create('relations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('relation_type');
                $table->integer('relation_id')->default(0)->unsigned();
                $table->integer('target_id')->default(0)->unsigned();
                $table->string('type')->nullable();
                // $table->text('params')->nullable();
                // $table->primary(['relation_type', 'relation_id', 'target_id']);
            });
        }

        if (Schema::hasTable('fixed_prices')) {
            Schema::table('fixed_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('fixed_prices', 'is_zone')) {
                    $table->tinyInteger('is_zone')->default(0)->unsigned()->after('service_ids');
                }
            });

            $sql = "ALTER TABLE `". $prefix ."fixed_prices` CHANGE `start_type` `start_type` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '0 - Include | 1 - Exclude', CHANGE `end_type` `end_type` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '0 - Include | 1 - Exclude';";
            \DB::statement($sql);
        }
    }

    public function down()
    {
        Schema::dropIfExists('relations');

        if (Schema::hasTable('fixed_prices')) {
            Schema::table('fixed_prices', function (Blueprint $table) {
                if (Schema::hasColumn('fixed_prices', 'is_zone')) {
                    $table->dropColumn(['is_zone']);
                }
            });
        }
    }
}
