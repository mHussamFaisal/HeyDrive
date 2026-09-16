<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3140 extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            if ( !Schema::hasColumn('profiles', 'availability_status') ) {
                $table->tinyInteger('availability_status')->default(0)->unsigned()->after('availability');
            }
        });
    }

    public function down()
    {
        //
    }
}
