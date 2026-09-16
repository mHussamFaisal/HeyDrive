<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDeletedAt extends Migration
{
    public function up()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_route', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
        if (Schema::hasTable('booking')) {
            Schema::table('booking', function (Blueprint $table) {
                if (!Schema::hasColumn('booking', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (Schema::hasColumn('booking_route', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
        if (Schema::hasTable('booking')) {
            Schema::table('booking', function (Blueprint $table) {
                if (Schema::hasColumn('booking', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
    }
}
