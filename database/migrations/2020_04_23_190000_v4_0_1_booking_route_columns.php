<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401BookingRouteColumns extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_route', 'departure_flight_number')) {
                    $table->string('departure_flight_number')->nullable()->default(null)->after('departure_city');
                }
                if (!Schema::hasColumn('booking_route', 'departure_flight_time')) {
                    $table->string('departure_flight_time')->nullable()->default(null)->after('departure_flight_number');
                }
                if (!Schema::hasColumn('booking_route', 'departure_flight_city')) {
                    $table->string('departure_flight_city')->nullable()->default(null)->after('departure_flight_time');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (Schema::hasColumn('booking_route', 'departure_flight_number')) {
                    $table->dropColumn('departure_flight_number');
                }
                if (Schema::hasColumn('booking_route', 'departure_flight_time')) {
                    $table->dropColumn('departure_flight_time');
                }
                if (Schema::hasColumn('booking_route', 'departure_flight_city')) {
                    $table->dropColumn('departure_flight_city');
                }
            });
        }
    }
}
