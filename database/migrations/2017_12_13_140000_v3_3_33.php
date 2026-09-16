<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3333 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $sites = \App\Models\Site::all();

            foreach($sites as $kS => $site) {
                // BACS
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'bacs')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'BACS',
                            'description' => 'BACS - Bank transfer',
                            'payment_page' => "",
                            'image' => 'bacs.png',
                            'params' => '{}',
                            'method' => 'bacs',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 10,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
