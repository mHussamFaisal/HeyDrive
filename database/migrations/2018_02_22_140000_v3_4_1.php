<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V341 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_route', function (Blueprint $table) {
            if ( !Schema::hasColumn('booking_route', 'driver_notes') ) {
                $table->text('driver_notes')->nullable()->after('driver_data');
            }

            if ( !Schema::hasColumn('booking_route', 'status_notes') ) {
                $table->text('status_notes')->nullable()->after('status');
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
        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'driver_notes') ) {
                $table->dropColumn('driver_notes');
            }

            if ( Schema::hasColumn('booking_route', 'status_notes') ) {
                $table->dropColumn('status_notes');
            }
        });
    }
}
