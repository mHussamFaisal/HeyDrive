<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsUsersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user')) {
            Schema::table('user', function (Blueprint $table) {
                if (!Schema::hasColumn('user', 'avatar')) {
                    $table->string('avatar')->nullable()->after('is_company');
                }
                if (!Schema::hasColumn('user', 'departments')) {
                    $table->text('departments')->nullable()->after('avatar');
                }
            });
        }

        if (Schema::hasTable('booking_route')) {
            Schema::table('booking_route', function (Blueprint $table) {
                if (!Schema::hasColumn('booking_route', 'department')) {
                    $table->string('department')->nullable()->after('contact_mobile');
                }
            });
        }
    }

    public function down()
    {
        //
    }
}
