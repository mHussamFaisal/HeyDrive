<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('vehicles') ) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0);
                $table->string('name')->nullable();
                $table->string('image')->nullable();
                $table->string('registration_mark')->nullable();
                $table->string('mot')->nullable();
                $table->dateTime('mot_expiry_date')->nullable();
                $table->string('make')->nullable();
                $table->string('model')->nullable();
                $table->string('colour')->nullable();
                $table->string('body_type')->nullable();
                $table->unsignedInteger('no_of_passengers')->default(0);
                $table->string('registered_keeper_name')->nullable();
                $table->string('registered_keeper_address')->nullable();
                $table->text('description')->nullable();
                $table->string('status')->nullable();
                $table->tinyInteger('selected')->default(0);
                $table->timestamps();

                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
