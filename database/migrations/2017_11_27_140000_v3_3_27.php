<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3327 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $configs = \DB::table('config')->get();

            foreach($configs as $k => $config) {
                $old = $config->key;

                switch($config->key) {
                    case 'company_url_logo':
                        $config->key = 'url_logo';
                    break;
                    case 'company_url_website':
                        $config->key = 'url_home';
                    break;
                    case 'company_url_booking':
                        $config->key = 'url_booking';
                    break;
                    case 'company_url_customer':
                        $config->key = 'url_customer';
                    break;
                    case 'company_url_driver':
                        $config->key = 'url_driver';
                    break;
                    case 'company_url_manager':
                        $config->key = 'url_admin';
                    break;
                    case 'company_url_contact':
                        $config->key = 'url_contact';
                    break;
                    case 'company_url_feedback':
                        $config->key = 'url_feedback';
                    break;
                    case 'company_url_terms_conditions':
                        $config->key = 'url_terms';
                    break;
                }

                if ($old != $config->key) {
                    \DB::table('config')->where('id', $config->id)->update((array)$config);
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
