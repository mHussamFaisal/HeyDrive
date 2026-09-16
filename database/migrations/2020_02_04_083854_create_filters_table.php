<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiltersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('filters')) {
            Schema::create('filters', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id');
                $table->string('name');
                $table->string('short_name', 60)->nullable();
                $table->string('type', 60); // booking | user | fixedprices ...
                $table->text('params');
                $table->tinyInteger('menu')->default(0)->unsigned();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('filters');
    }
}
