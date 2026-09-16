<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V360 extends Migration
{
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            if ( !Schema::hasColumn('payment', 'service_ids') ) {
                $table->string('service_ids')->nullable()->after('profile_id');
            }
        });
    }

    public function down()
    {
        Schema::table('payment', function (Blueprint $table) {
            if ( Schema::hasColumn('payment', 'service_ids') ) {
                $table->dropColumn('service_ids');
            }
        });
    }
}
