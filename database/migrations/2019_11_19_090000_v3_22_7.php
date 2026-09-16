<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3227 extends Migration
{
    public function up()
    {
        Schema::table('charge', function (Blueprint $table) {
            $prefix = get_db_prefix();

            if ( Schema::hasColumn('charge', 'value') ) {
                DB::statement("ALTER TABLE `{$prefix}charge` CHANGE `value` `value` DOUBLE(8,2) NOT NULL DEFAULT '0.00';");
            }
        });
    }

    public function down()
    {
        //
    }
}
