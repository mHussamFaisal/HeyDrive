<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionUserTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permission_user')) {
            $prefix = get_db_prefix();

            Schema::create('permission_user', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('permission_id')->unsigned()->index();
                $table->foreign('permission_id', $prefix . 'peruser_permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');
                $table->integer('user_id')->unsigned()->index();
                $table->foreign('user_id', $prefix . 'peruser_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('permission_user');
    }
}
