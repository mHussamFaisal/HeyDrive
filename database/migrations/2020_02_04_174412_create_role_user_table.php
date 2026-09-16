<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRoleUserTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('role_user')) {
            $prefix = get_db_prefix();

            Schema::create('role_user', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('role_id')->unsigned()->index();
                $table->foreign('role_id', $prefix . 'roleuser_role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');
                $table->integer('user_id')->unsigned()->index();
                $table->foreign('user_id', $prefix . 'roleuser_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
