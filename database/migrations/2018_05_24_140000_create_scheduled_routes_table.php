<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledRoutesTable extends Migration
{
    public function up()
    {
        Schema::create('scheduled_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('parent');
            $table->integer('driver_id')->default(0)->unsigned();
            $table->integer('vehicle_id')->default(0)->unsigned();
            $table->integer('vehicle_type_id')->default(0)->unsigned();
            $table->text('params')->nullable();
            $table->tinyInteger('is_featured')->default(0)->unsigned();
            $table->integer('order')->default(0)->unsigned();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_routes');
    }
}
