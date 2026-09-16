<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFlightTables extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('booking_params')) {
            \DB::statement("ALTER TABLE `{$prefix}booking_params` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
        }

        if (!Schema::hasTable('flight_airports')) {
            Schema::create('flight_airports', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->string('fs')->nullable();
                $table->string('iata')->nullable();
                $table->string('icao')->nullable();
                $table->string('faa')->nullable();
                $table->string('name')->nullable();
                $table->string('city')->nullable();
                $table->string('state_code')->nullable();
                $table->string('country_code')->nullable();
                $table->string('country_name')->nullable();
                $table->string('region_name')->nullable();
                $table->double('lat', 10, 6)->nullable();
                $table->double('lng', 10, 6)->nullable();
                $table->tinyInteger('active')->default(0)->unsigned();
            });
        }

        if (!Schema::hasTable('flight_airlines')) {
            Schema::create('flight_airlines', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->string('fs')->nullable();
                $table->string('iata')->nullable();
                $table->string('icao')->nullable();
                $table->string('name')->nullable();
                $table->tinyInteger('active')->default(0)->unsigned();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('flight_airports');
        Schema::dropIfExists('flight_airlines');
    }
}
