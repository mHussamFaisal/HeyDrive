<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3424 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_route', function (Blueprint $table) {
            if ( !Schema::hasColumn('booking_route', 'locale') ) {
                $table->string('locale')->nullable()->after('notes');
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
            if ( Schema::hasColumn('booking_route', 'locale') ) {
                $table->dropColumn('locale');
            }
        });
    }
}
