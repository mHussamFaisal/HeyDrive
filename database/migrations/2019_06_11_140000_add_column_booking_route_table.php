<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBookingRouteTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_route', 'custom')) {
                    $table->text('custom')->nullable()->after('notes');
                }
            });
        }
    }

    public function down()
    {
        //
    }
}
