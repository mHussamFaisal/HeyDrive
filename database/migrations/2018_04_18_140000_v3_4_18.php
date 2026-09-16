<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3418 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            if ( !Schema::hasColumn('user', 'push_token') ) {
                $table->string('push_token')->nullable()->after('ip');
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
        Schema::table('user', function (Blueprint $table) {
            if ( Schema::hasColumn('user', 'push_token') ) {
                $table->dropColumn('push_token');
            }
        });
    }
}
