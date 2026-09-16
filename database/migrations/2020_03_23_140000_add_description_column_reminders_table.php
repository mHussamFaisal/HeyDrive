<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDescriptionColumnRemindersTable extends Migration
{
    public function up()
    {
        Schema::table('reminders', function (Blueprint $table) {
            if (!Schema::hasColumn('reminders', 'description')) {
                $table->string('description')->nullable()->after('type');
            }
        });
    }

    public function down()
    {
        //
    }
}
