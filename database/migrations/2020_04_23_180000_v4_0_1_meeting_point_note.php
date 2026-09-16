<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V401MeetingPointNote extends Migration
{
    public function up()
    {
        if (Schema::hasTable('meeting_point')) {
            // Schema::table('meeting_point', function (Blueprint $table) {
            //     if (Schema::hasColumn('meeting_point', 'note')) {
            //         $table->text('note')->nullable()->default(null)->change();
            //     }
            // });

            $prefix = get_db_prefix();
            \DB::statement("ALTER TABLE `{$prefix}meeting_point` CHANGE `note` `note` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
        }
    }

    public function down()
    {
        //
    }
}
