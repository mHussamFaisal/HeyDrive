<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookingParamsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('booking_params')) {
            $prefix = get_db_prefix();

            Schema::create('booking_params', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('booking_id')->unsigned()->index();
                $table->foreign('booking_id', $prefix . 'bookpar_booking_id')
                    ->references('id')
                    ->on('booking_route')
                    ->onDelete('cascade');
                $table->string('key');
                $table->string('value')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('booking_params');
    }
}
