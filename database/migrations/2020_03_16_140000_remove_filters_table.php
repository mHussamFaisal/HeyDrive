<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveFiltersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('filters')) {
            Schema::dropIfExists('filters');
        }
    }

    public function down()
    {
        //
    }
}
