<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveIdColumnSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

    public function down()
    {
        //
    }
}
