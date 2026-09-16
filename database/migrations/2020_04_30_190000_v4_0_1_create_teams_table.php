<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401CreateTeamsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('subscription_id')->default(0)->unsigned();
                $table->string('name')->nullable()->default(null);
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->integer('order')->default(0)->unsigned();
                $table->text('internal_note')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('team_user')) {
            Schema::create('team_user', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('team_id')->default(0)->unsigned();
                $table->integer('user_id')->default(0)->unsigned();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('teams');
        Schema::dropIfExists('team_user');
    }
}
