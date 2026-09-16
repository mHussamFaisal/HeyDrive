<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3102 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( Schema::hasTable('booking_route') ) {
            // Check if booking has been opened
            Schema::table('booking_route', function (Blueprint $table) {
                if ( !Schema::hasColumn('booking_route', 'is_read') ) {
                    $table->tinyInteger('is_read')->nullable()->default(0)->unsigned()->after('locale');
                }
            });

            // Map confirm status to pending
            DB::table('booking_route')->where('status', 'confirmed')->update(['status' => 'pending']);
            DB::table('booking_route')->update(['is_read' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ( Schema::hasTable('booking_route') ) {
            Schema::table('booking_route', function (Blueprint $table) {
                if ( Schema::hasColumn('booking_route', 'is_read') ) {
                    $table->dropColumn('is_read');
                }
            });
        }
    }
}
