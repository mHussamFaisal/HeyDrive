<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V356 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $prefix = get_db_prefix();

            if ( Schema::hasColumn('users', 'accuracy') ) {
                DB::statement("ALTER TABLE `{$prefix}users` CHANGE `accuracy` `accuracy` DOUBLE(8,2) NULL DEFAULT NULL;");
            }

            if ( Schema::hasColumn('users', 'heading') ) {
                DB::statement("ALTER TABLE `{$prefix}users` CHANGE `heading` `heading` DOUBLE(8,2) NULL DEFAULT NULL;");
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
