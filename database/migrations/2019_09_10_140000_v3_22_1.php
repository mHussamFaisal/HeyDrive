<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3221 extends Migration
{
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $sites = DB::table('profile')->get();

            foreach($sites as $kS => $site) {
                $rows = [];

                $check = DB::table('config')->where('site_id', $site->id)->where('key', 'customer_allow_company_number')->first();
                if (empty($check->id)) {
                    $rows[] = [
                        'site_id' => $site->id,
                        'key' => 'customer_allow_company_number',
                        'value' => 1,
                        'type' => 'int',
                        'browser' => 0,
                    ];
                }

                $check = DB::table('config')->where('site_id', $site->id)->where('key', 'customer_require_company_number')->first();
                if (empty($check->id)) {
                    $rows[] = [
                        'site_id' => $site->id,
                        'key' => 'customer_require_company_number',
                        'value' => 0,
                        'type' => 'int',
                        'browser' => 0,
                    ];
                }

                $check = DB::table('config')->where('site_id', $site->id)->where('key', 'customer_allow_company_tax_number')->first();
                if (empty($check->id)) {
                    $rows[] = [
                        'site_id' => $site->id,
                        'key' => 'customer_allow_company_tax_number',
                        'value' => 1,
                        'type' => 'int',
                        'browser' => 0,
                    ];
                }

                $check = DB::table('config')->where('site_id', $site->id)->where('key', 'customer_require_company_tax_number')->first();
                if (empty($check->id)) {
                    $rows[] = [
                        'site_id' => $site->id,
                        'key' => 'customer_require_company_tax_number',
                        'value' => 0,
                        'type' => 'int',
                        'browser' => 0,
                    ];
                }

                if (!empty($rows)) {
                    DB::table('config')->insert($rows);
                }
            }
        });
    }

    public function down()
    {
        //
    }
}
