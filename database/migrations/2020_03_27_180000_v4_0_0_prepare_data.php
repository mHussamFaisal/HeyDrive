<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V400PrepareData extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('booking_drivers')) {
            DB::statement("ALTER TABLE `{$prefix}booking_drivers` ENGINE = InnoDB");
        }
        if (Schema::hasTable('category')) {
            DB::statement("ALTER TABLE `{$prefix}category` ENGINE = InnoDB");
        }
        if (Schema::hasTable('charge')) {
            DB::statement("ALTER TABLE `{$prefix}charge` ENGINE = InnoDB");
        }
        if (Schema::hasTable('config')) {
            DB::statement("ALTER TABLE `{$prefix}config` ENGINE = InnoDB");
        }
        if (Schema::hasTable('discount')) {
            DB::statement("ALTER TABLE `{$prefix}discount` ENGINE = InnoDB");
        }
        if (Schema::hasTable('excluded_routes')) {
            DB::statement("ALTER TABLE `{$prefix}excluded_routes` ENGINE = InnoDB");
        }
        if (Schema::hasTable('failed_jobs')) {
            DB::statement("ALTER TABLE `{$prefix}failed_jobs` ENGINE = InnoDB");
        }
        if (Schema::hasTable('file')) {
            DB::statement("ALTER TABLE `{$prefix}file` ENGINE = InnoDB");
        }
        if (Schema::hasTable('fixed_prices')) {
            DB::statement("ALTER TABLE `{$prefix}fixed_prices` ENGINE = InnoDB");
        }
        if (Schema::hasTable('jobs')) {
            DB::statement("ALTER TABLE `{$prefix}jobs` ENGINE = InnoDB");
        }
        if (Schema::hasTable('language_lines')) {
            DB::statement("ALTER TABLE `{$prefix}language_lines` ENGINE = InnoDB");
        }
        if (Schema::hasTable('location')) {
            DB::statement("ALTER TABLE `{$prefix}location` ENGINE = InnoDB");
        }
        if (Schema::hasTable('meeting_point')) {
            DB::statement("ALTER TABLE `{$prefix}meeting_point` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing')) {
            DB::statement("ALTER TABLE `{$prefix}pricing` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing_event')) {
            DB::statement("ALTER TABLE `{$prefix}pricing_event` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing_location')) {
            DB::statement("ALTER TABLE `{$prefix}pricing_location` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing_service')) {
            DB::statement("ALTER TABLE `{$prefix}pricing_service` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing_site')) {
            DB::statement("ALTER TABLE `{$prefix}pricing_site` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing_user')) {
            DB::statement("ALTER TABLE `{$prefix}pricing_user` ENGINE = InnoDB");
        }
        if (Schema::hasTable('pricing_vehicle_type')) {
            DB::statement("ALTER TABLE `{$prefix}pricing_vehicle_type` ENGINE = InnoDB");
        }
        if (Schema::hasTable('profile')) {
            DB::statement("ALTER TABLE `{$prefix}profile` ENGINE = InnoDB");
        }
        if (Schema::hasTable('relations')) {
            DB::statement("ALTER TABLE `{$prefix}relations` ENGINE = InnoDB");
        }
        if (Schema::hasTable('transactions')) {
            DB::statement("ALTER TABLE `{$prefix}transactions` ENGINE = InnoDB");
        }
        if (Schema::hasTable('user')) {
            DB::statement("ALTER TABLE `{$prefix}user` ENGINE = InnoDB");
        }
        if (Schema::hasTable('user_customer')) {
            DB::statement("ALTER TABLE `{$prefix}user_customer` ENGINE = InnoDB");
        }
        if (Schema::hasTable('user_profile')) {
            DB::statement("ALTER TABLE `{$prefix}user_profile` ENGINE = InnoDB");
        }
        if (Schema::hasTable('vehicle')) {
            DB::statement("ALTER TABLE `{$prefix}vehicle` ENGINE = InnoDB");
        }
        if (Schema::hasTable('vehicles')) {
            DB::statement("ALTER TABLE `{$prefix}vehicles` ENGINE = InnoDB");
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('slug', 'admin.subscription.destroy')
              ->orWhere('slug', 'admin.subscription.trash')
              ->orWhere('slug', 'admin.subscription.restore')
              ->orWhere('slug', 'admin.reports.index')
              ->delete();

            DB::table('permissions')->insert(['slug' => 'admin.reports.index']);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('param', 'app.version')->delete();
        }
    }

    public function down()
    {
        //
    }
}
