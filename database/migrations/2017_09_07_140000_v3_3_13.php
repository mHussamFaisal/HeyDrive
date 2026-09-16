<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3313 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if ( !Schema::hasColumn('users', 'push_token') ) {
                $table->string('push_token', 100)->nullable()->after('remember_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if ( Schema::hasColumn('users', 'push_token') ) {
                $table->dropColumn('push_token');
            }
        });
    }
}
