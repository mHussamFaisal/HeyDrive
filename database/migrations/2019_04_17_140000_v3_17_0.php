<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3170 extends Migration
{
    public function up()
    {
        if ( Schema::hasTable('profile') ) {
            Schema::table('profile', function (Blueprint $table) {
                if ( Schema::hasColumn('profile', 'license_key') ) {
                    $table->dropColumn('license_key');
                }
            });
        }
    }

    public function down()
    {
        //
    }
}
