<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('remote_id')->nullable();
                $table->uuid('uuid');
                $table->string('name');
                $table->string('slug');
                $table->tinyInteger('status')->default(1)->unsigned();
                $table->text('excerpt')->nullable();
                $table->text('description')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::drop('news');
    }
}
