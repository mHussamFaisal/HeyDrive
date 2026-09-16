<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401BookingRouteAddLeadEmail extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_route', 'lead_passenger_email')) {
                    $table->string('lead_passenger_email')->nullable()->default(null)->after('lead_passenger_name');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (Schema::hasColumn('booking_route', 'lead_passenger_email')) {
                    $table->dropColumn('lead_passenger_email');
                }
            });
        }
    }
}
