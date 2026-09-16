<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3312 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if ( !Schema::hasColumn('users', 'lat') ) {
                $table->float('lat', 10, 6)->nullable()->after('status');
            }

            if ( !Schema::hasColumn('users', 'lng') ) {
                $table->float('lng', 10, 6)->nullable()->after('lat');
            }

            if ( !Schema::hasColumn('users', 'accuracy') ) {
                $table->float('accuracy', 8, 2)->nullable()->after('lng');
            }

            if ( !Schema::hasColumn('users', 'heading') ) {
                $table->float('heading', 8, 2)->nullable()->after('accuracy');
            }

            if ( !Schema::hasColumn('users', 'last_seen_at') ) {
                $table->timestamp('last_seen_at')->nullable()->after('heading');
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
        Schema::table('users', function (Blueprint $table) {
            if ( Schema::hasColumn('users', 'lat') ) {
                $table->dropColumn('lat');
            }

            if ( Schema::hasColumn('users', 'lng') ) {
                $table->dropColumn('lng');
            }

            if ( Schema::hasColumn('users', 'accuracy') ) {
                $table->dropColumn('accuracy');
            }

            if ( Schema::hasColumn('users', 'heading') ) {
                $table->dropColumn('heading');
            }

            if ( Schema::hasColumn('users', 'last_seen_at') ) {
                $table->dropColumn('last_seen_at');
            }
        });
    }
}
