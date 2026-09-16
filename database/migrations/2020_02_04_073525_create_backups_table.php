<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('backups')) {
            Schema::create('backups', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('file');
                $table->string('name')->nullable();
                $table->string('type');
                $table->string('disk')->default('local');
                $table->integer('size')->nullable();
                $table->integer('parent_id')->nullable();
                $table->text('comments')->nullable();
                $table->tinyInteger('status')->default(0)->unsigned();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
