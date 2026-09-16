<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3231 extends Migration
{
    public function up()
    {
        DB::beginTransaction();
        try {
            DB::table('config')
              ->where('key', 'notification_test_email')
              ->orWhere('key', 'notification_test_phone')
              ->update(['value' => '']);

            DB::commit();
        } catch (\Exception $e) {
            \Log::error('Migration V3231 (rollback): '. $e->getMessage());
            DB::rollback();
        }
    }

    public function down()
    {
        //
    }
}
