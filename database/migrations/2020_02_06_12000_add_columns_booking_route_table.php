<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsBookingRouteTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_route', 'fleet_id')) {
                    $table->integer('fleet_id')->default(0)->unsigned()->after('vehicle_data');
                }
                if (!Schema::hasColumn('booking_route', 'fleet_commission')) {
                    $table->double('fleet_commission', 8, 2)->default('0.00')->after('fleet_id');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'fleet_id')) {
                    $table->integer('fleet_id')->default(0)->unsigned()->after('id');
                }
            });
        }

        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicles', 'fleet_id')) {
                    $table->integer('fleet_id')->default(0)->unsigned()->after('user_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (Schema::hasColumn('booking_route', 'fleet_id')) {
                    $table->dropColumn('fleet_id');
                }
                if (Schema::hasColumn('booking_route', 'fleet_commission')) {
                    $table->dropColumn('fleet_commission');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'fleet_id')) {
                    $table->dropColumn('fleet_id');
                }
            });
        }

        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                if (Schema::hasColumn('vehicles', 'fleet_id')) {
                    $table->dropColumn('fleet_id');
                }
            });
        }
    }
}
