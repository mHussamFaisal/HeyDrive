<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->default(0)->unsigned();
                $table->string('name')->nullable();
                $table->string('type')->nullable();
                $table->string('version')->nullable();
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->text('description')->nullable();
                $table->text('params')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('subscription_modules')) {
            Schema::create('subscription_modules', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('subscription_id')->default(0)->unsigned();
                $table->integer('module_id')->default(0)->unsigned();
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->text('params')->nullable();
                $table->string('hash');
                $table->dateTime('install_at')->nullable();
                $table->dateTime('expire_at')->nullable();
                $table->dateTime('support_at')->nullable();
                $table->dateTime('update_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('modules');
        Schema::dropIfExists('subscription_modules');
    }
}
