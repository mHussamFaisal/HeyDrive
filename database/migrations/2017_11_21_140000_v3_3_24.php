<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3324 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profile', function (Blueprint $table) {
            // Add column
            if ( !Schema::hasColumn('profile', 'license_key') ) {
                $table->string('license_key')->nullable()->after('key');
            }

            // Drop column
            if ( Schema::hasColumn('profile', 'cms_user_id') ) {
                $table->dropColumn('cms_user_id');
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
        Schema::table('profile', function (Blueprint $table) {
            if ( Schema::hasColumn('profile', 'license_key') ) {
                $table->dropColumn('license_key');
            }
        });
    }
}
