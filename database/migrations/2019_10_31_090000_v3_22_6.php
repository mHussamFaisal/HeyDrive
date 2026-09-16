<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3226 extends Migration
{
    public function up()
    {
        // Update value (base price)
        Schema::table('fixed_prices', function (Blueprint $table) {
            $prefix = get_db_prefix();

            if ( Schema::hasColumn('fixed_prices', 'value') ) {
                DB::statement("ALTER TABLE `{$prefix}fixed_prices` CHANGE `value` `value` DOUBLE(8,2) NOT NULL DEFAULT '0.00';");
            }
        });
    }

    public function down()
    {
        //
    }
}
