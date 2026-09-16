<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401VehiclesAddColumn extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicles', 'vehicle_type_id')) {
                    $table->integer('vehicle_type_id')->default(0)->unsigned()->after('fleet_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                if (Schema::hasColumn('vehicles', 'vehicle_type_id')) {
                    $table->dropColumn('vehicle_type_id');
                }
            });
        }
    }
}
