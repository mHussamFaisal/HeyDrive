<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('log_name')->nullable();
                $table->string('description');
                $table->integer('subject_id')->nullable();
                $table->string('subject_type')->nullable();
                $table->integer('causer_id')->nullable();
                $table->string('causer_type')->nullable();
                $table->text('properties')->nullable();
                $table->timestamps();

                $table->index('log_name');
            });
        }
    }

    public function down()
    {
        Schema::drop('activity_log');
    }
}
