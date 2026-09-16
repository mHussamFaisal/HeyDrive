<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\DatabaseHelper;

class V3233 extends Migration
{
    private $helperDB;

    public function __construct()
    {
        $this->helperDB = new DatabaseHelper('V3233');
    }

    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('booking_tracking_active')) {
            DB::statement("ALTER TABLE `{$prefix}booking_tracking_active` ENGINE = InnoDB");

            $this->helperDB->dropIndexIfExist('booking_tracking_active', 'bta_booking_id', $prefix);
            $this->helperDB->dropIndexIfExist('booking_tracking_active', 'bta_driver_id', $prefix);
            Schema::table('booking_tracking_active', function (Blueprint $table) use ($prefix) {
                $table->foreign('booking_id', $prefix.'bta_booking_id')
                    ->references('id')
                    ->on('booking_route')
                    ->onDelete('cascade');

                $table->foreign('driver_id', $prefix.'bta_driver_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('booking_tracking')) {
            DB::statement("ALTER TABLE `{$prefix}booking_tracking` ENGINE = InnoDB");

            $this->helperDB->dropIndexIfExist('booking_tracking', 'bt_booking_id', $prefix);
            $this->helperDB->dropIndexIfExist('booking_tracking', 'bt_driver_id', $prefix);

            // Delete tracking data for non-existing booking routes
            DB::table('booking_tracking')->whereRaw("`{$prefix}booking_tracking`.`booking_id` NOT IN (SELECT `{$prefix}booking_route`.`id` FROM `{$prefix}booking_route`)")->delete();

            Schema::table('booking_tracking', function (Blueprint $table) use ($prefix) {
                $table->foreign('booking_id', $prefix.'bt_booking_id')
                    ->references('id')
                    ->on('booking_route')
                    ->onDelete('cascade');

                $table->foreign('driver_id', $prefix.'bt_driver_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        //
    }
}
