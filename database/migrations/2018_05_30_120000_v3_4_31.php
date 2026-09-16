<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class V3431 extends Migration
{
    public function up()
    {
        Schema::table('booking_route', function (Blueprint $table) {
            $table->integer('parent_booking_id')->default(0)->unsigned()->after('booking_id');
            $table->integer('scheduled_route_id')->default(0)->unsigned()->after('parent_booking_id');
            $table->text('params')->nullable()->after('scheduled_route_id');
        });
    }

    public function down()
    {
        //
    }
}
