<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V345 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking', function (Blueprint $table) {
            if ( Schema::hasColumn('booking', 'bak_routes') ) {
                $table->dropColumn('bak_routes');
            }

            if ( Schema::hasColumn('booking', 'bak_payment_id') ) {
                $table->dropColumn('bak_payment_id');
            }

            if ( Schema::hasColumn('booking', 'bak_payment_method') ) {
                $table->dropColumn('bak_payment_method');
            }

            if ( Schema::hasColumn('booking', 'bak_payment_name') ) {
                $table->dropColumn('bak_payment_name');
            }

            if ( Schema::hasColumn('booking', 'bak_payment_price') ) {
                $table->dropColumn('bak_payment_price');
            }

            if ( Schema::hasColumn('booking', 'bak_payment_status') ) {
                $table->dropColumn('bak_payment_status');
            }

            if ( Schema::hasColumn('booking', 'bak_payment_response') ) {
                $table->dropColumn('bak_payment_response');
            }

            if ( Schema::hasColumn('booking', 'bak_discount_code') ) {
                $table->dropColumn('bak_discount_code');
            }

            if ( Schema::hasColumn('booking', 'bak_discount_price') ) {
                $table->dropColumn('bak_discount_price');
            }

            if ( Schema::hasColumn('booking', 'bak_currency_id') ) {
                $table->dropColumn('bak_currency_id');
            }

            if ( Schema::hasColumn('booking', 'bak_general_extra_charges_list') ) {
                $table->dropColumn('bak_general_extra_charges_list');
            }

            if ( Schema::hasColumn('booking', 'bak_general_extra_charges_price') ) {
                $table->dropColumn('bak_general_extra_charges_price');
            }

            if ( Schema::hasColumn('booking', 'bak_general_total_price') ) {
                $table->dropColumn('bak_general_total_price');
            }

            if ( Schema::hasColumn('booking', 'bak_deposit_price') ) {
                $table->dropColumn('bak_deposit_price');
            }

            if ( Schema::hasColumn('booking', 'bak_source') ) {
                $table->dropColumn('bak_source');
            }

            if ( Schema::hasColumn('booking', 'bak_source_details') ) {
                $table->dropColumn('bak_source_details');
            }

            if ( Schema::hasColumn('booking', 'bak_ip') ) {
                $table->dropColumn('bak_ip');
            }

            if ( Schema::hasColumn('booking', 'bak_created_date') ) {
                $table->dropColumn('bak_created_date');
            }
        });

        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'bak_driver_name') ) {
                $table->dropColumn('bak_driver_name');
            }

            if ( Schema::hasColumn('booking_route', 'bak_driver_vehicle_reg_no') ) {
                $table->dropColumn('bak_driver_vehicle_reg_no');
            }

            if ( Schema::hasColumn('booking_route', 'bak_driver_status') ) {
                $table->dropColumn('bak_driver_status');
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
        //
    }
}
