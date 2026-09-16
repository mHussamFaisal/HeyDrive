<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401ReminderDescription extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reminders')) {
            // Schema::table('reminders', function (Blueprint $table) {
            //     if (Schema::hasColumn('reminders', 'description')) {
            //         $table->text('description')->nullable()->default(null)->change();
            //     }
            // });

            $prefix = get_db_prefix();
            \DB::statement("ALTER TABLE `{$prefix}reminders` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
        }
    }

    public function down()
    {
        //
    }
}
