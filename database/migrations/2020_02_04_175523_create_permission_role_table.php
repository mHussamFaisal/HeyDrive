<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionRoleTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permission_role')) {
            $prefix = get_db_prefix();

            Schema::create('permission_role', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                $table->increments('id')->unsigned();
                $table->integer('permission_id')->unsigned()->index();
                $table->foreign('permission_id', $prefix . 'perrol_permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');
                $table->integer('role_id')->unsigned()->index();
                $table->foreign('role_id', $prefix . 'perrol_role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('permission_role');
    }
}
