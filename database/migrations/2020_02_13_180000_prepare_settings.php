<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepareSettings extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('settings')) {
            DB::statement("ALTER TABLE `{$prefix}settings` ENGINE = InnoDB");
            DB::statement("ALTER TABLE `{$prefix}settings` ADD UNIQUE `unique_index` (`relation_id`, `relation_type`, `param`)");
        }
    }

    public function down()
    {
        //
    }
}
