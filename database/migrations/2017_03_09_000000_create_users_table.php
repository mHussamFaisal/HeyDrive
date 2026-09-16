<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !Schema::hasTable('users') ) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('role')->default('customer');
                $table->string('name');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('avatar')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->string('status');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
