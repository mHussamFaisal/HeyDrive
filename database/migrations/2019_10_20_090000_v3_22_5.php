<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3225 extends Migration
{
    public function up()
    {
        Schema::table('user_customer', function (Blueprint $table) {
            if ( !Schema::hasColumn('user_customer', 'is_account_payment') ) {
                $table->tinyInteger('is_account_payment')->default(0)->unsigned()->after('company_tax_number');
            }
        });

        DB::table('user')
          ->join('user_customer', 'user.id', '=', 'user_customer.user_id')
          ->where('user.is_company', '1')
          ->update(['user_customer.is_account_payment' => 1]);
    }

    public function down()
    {
        Schema::table('user_customer', function (Blueprint $table) {
            if ( Schema::hasColumn('user_customer', 'is_account_payment') ) {
                $table->dropColumn('is_account_payment');
            }
        });
    }
}
