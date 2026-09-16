<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingTrackingTables extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        DB::statement("ALTER TABLE `{$prefix}booking` ENGINE = InnoDB");
        DB::statement("ALTER TABLE `{$prefix}booking_route` ENGINE = InnoDB");
        DB::statement("ALTER TABLE `{$prefix}users` ENGINE = InnoDB");

        if (!Schema::hasTable('booking_tracking_active')) {
            Schema::create('booking_tracking_active', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';

                $table->increments('id');
                $table->integer('booking_id')->unsigned();
                $table->foreign('booking_id', $prefix.'bta_booking_id')
                    ->references('id')
                    ->on('booking_route')
                    ->onDelete('cascade');
                $table->integer('driver_id')->unsigned();
                $table->foreign('driver_id', $prefix.'bta_driver_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->double('lat', 10, 6)->nullable();
                $table->double('lng', 10, 6)->nullable();
                $table->string('status', 25);
                $table->timestamp('timestamp')->default(\DB::raw('CURRENT_TIMESTAMP'));
            });
        }

        if (!Schema::hasTable('booking_tracking')) {
            Schema::create('booking_tracking', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';

                $table->increments('id');
                $table->integer('booking_id')->unsigned();
                $table->foreign('booking_id', $prefix.'bt_booking_id')
                    ->references('id')
                    ->on('booking_route')
                    ->onDelete('cascade');
                $table->integer('driver_id')->unsigned();
                $table->foreign('driver_id', $prefix.'bt_driver_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->mediumText('params');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('booking_tracking_active');
        Schema::dropIfExists('booking_tracking');
    }
}
