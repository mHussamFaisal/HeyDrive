<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('events') ) {
            Schema::create('events', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ref_type')->nullable();
                $table->unsignedInteger('ref_id')->default(0);
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->dateTime('start_at')->nullable();
                $table->dateTime('end_at')->nullable();
                $table->string('repeat_type')->nullable();
                $table->unsignedSmallInteger('repeat_interval')->default(0);
                $table->string('repeat_days')->nullable();
                $table->dateTime('repeat_end')->nullable();
                $table->unsignedSmallInteger('repeat_limit')->default(0);
                $table->integer('ordering')->default(0);
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
        Schema::dropIfExists('events');
    }
}
