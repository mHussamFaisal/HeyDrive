<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingDriversTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('booking_drivers')) {
            Schema::create('booking_drivers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('booking_id')->default(0)->unsigned();
                $table->integer('driver_id')->default(0)->unsigned();
                $table->integer('vehicle_id')->default(0)->unsigned();
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->double('commission', 8, 2)->default('0.00');
                $table->double('cash', 8, 2)->default('0.00');
                $table->text('admin_notes')->nullable();
                $table->text('driver_notes')->nullable();
                $table->text('status_notes')->nullable();
                $table->text('driver_data')->nullable();
                $table->text('vehicle_data')->nullable();
                $table->tinyInteger('auto_assigned')->default(0)->unsigned();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('booking_drivers');
    }
}
