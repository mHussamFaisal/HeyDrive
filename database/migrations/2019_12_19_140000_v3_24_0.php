<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\DatabaseHelper;

class V3240 extends Migration
{
    private $helperDB;

    public function __construct()
    {
        $this->helperDB = new DatabaseHelper('V3240');
    }

    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('user_params')) {
            $this->helperDB->dropIndexIfExist('user_params', 'user_params_user_id_foreign', $prefix);
            $this->helperDB->dropIndexIfExist('user_params', 'upar_user_id', $prefix);

            Schema::table('user_params', function (Blueprint $table) use ($prefix) {
                $table->foreign('user_id', $prefix . 'upar_user_id')
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
