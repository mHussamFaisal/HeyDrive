<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3110 extends Migration
{
    public function up()
    {
        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'items') ) {
                $table->text('items')->nullable()->default(null)->change();
            }
        });
    }

    public function down()
    {
        //
    }
}
