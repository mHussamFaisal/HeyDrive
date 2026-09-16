<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401BookingParams extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_params')) {
            \DB::table('booking_params')->where('key', 'passenger_tracking')->update(['key' => 'access_uuid']);
        }

        if (Schema::hasTable('bases')) {
            \DB::table('bases')->whereNull('relation_type')->where('relation_id', 0)->delete();
        }
    }

    public function down()
    {
        //
    }
}
