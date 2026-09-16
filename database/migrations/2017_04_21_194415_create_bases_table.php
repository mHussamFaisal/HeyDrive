<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('bases') ) {
            Schema::create('bases', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ref_type')->nullable();
                $table->unsignedInteger('ref_id')->default(0);
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->string('address')->nullable();
                $table->float('lat', 10, 7)->nullable();
                $table->float('lng', 10, 7)->nullable();
                $table->smallInteger('radius')->default(0);
                $table->tinyInteger('calculate_route')->default(0);
                $table->integer('ordering')->default(0);
                $table->tinyInteger('selected')->default(0);
                $table->string('status')->nullable();
                $table->timestamps();

                $table->index('ref_id');
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
        Schema::dropIfExists('bases');
    }
}
