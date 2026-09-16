<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReminderTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('reminders')) {
            Schema::create('reminders', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('api_id')->nullable();
                $table->tinyInteger('disable')->default(0);
                $table->string('type', 60)->default('general');
                $table->date('remind_at')->nullable();
                $table->dateTime('expire_at')->nullable();
                $table->dateTime('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::drop('reminders');
    }
}
