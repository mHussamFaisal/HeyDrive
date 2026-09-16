<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('services') ) {
            Schema::create('services', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0);
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('factor_type')->default('addition');
                $table->double('factor_value', 8, 2)->default('0.00');
                $table->tinyInteger('duration')->default(0);
                $table->smallInteger('duration_min')->default(0);
                $table->smallInteger('duration_max')->default(0);
                $table->tinyInteger('hide_location')->default(0);
                $table->integer('ordering')->default(0);
                $table->tinyInteger('selected')->default(0);
                $table->string('status')->nullable();
                $table->timestamps();

                $table->index('site_id');
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
        Schema::dropIfExists('services');
    }
}
