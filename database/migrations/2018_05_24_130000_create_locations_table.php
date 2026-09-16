<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('parent');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('postcode')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->double('lat', 10, 6)->default(0);
            $table->double('lng', 10, 6)->default(0);
            $table->double('radius', 5, 2)->default(0)->unsigned();
            $table->text('params')->nullable();
            $table->integer('order')->default(0)->unsigned();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
