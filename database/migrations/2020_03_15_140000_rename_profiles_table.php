<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameProfilesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('profiles')) {
            Schema::rename('profiles', 'user_profile');
        }
    }

    public function down()
    {
        if (Schema::hasTable('user_profile')) {
            Schema::rename('user_profile', 'profiles');
        }
    }
}
