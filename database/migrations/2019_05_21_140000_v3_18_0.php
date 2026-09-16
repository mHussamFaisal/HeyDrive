<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3180 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('loyalty_program')) {
            Schema::dropIfExists('loyalty_program');
        }
    }

    public function down()
    {
        //
    }
}
