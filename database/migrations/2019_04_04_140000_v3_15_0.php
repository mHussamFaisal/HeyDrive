<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3150 extends Migration
{
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $inserts = [];
            $sites = DB::table('profile')->get();

            foreach($sites as $kS => $site) {
                $check_textlocal = DB::table('config')->where('site_id', '=', $site->id)->where('key', '=', 'textlocal_enabled')->where('value', '1')->first();
                $check_smsgateway = DB::table('config')->where('site_id', '=', $site->id)->where('key', '=', 'smsgateway_enabled')->where('value', '1')->first();

                if ( !empty($check_textlocal->id) ) {
                    $sms_service = 'textlocal';
                }
                elseif ( !empty($check_smsgateway->id) ) {
                    $sms_service = 'smsgateway';
                }
                else {
                    $sms_service = '';
                }

                if ($sms_service) {
                    $inserts[] = [
                        'site_id' => $site->id,
                        'key' => 'sms_service_type',
                        'value' => $sms_service,
                        'type' => 'string',
                        'browser' => 0,
                    ];
                }
            }

            DB::beginTransaction();
            try {
                if (!empty($inserts)) {
                    DB::table('config')->insert($inserts);
                }
                DB::table('config')->where('key', '=', 'textlocal_enabled')->delete();
                DB::table('config')->where('key', '=', 'smsgateway_enabled')->delete();
                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration V3150 (rollback): '. $e->getMessage());
                DB::rollback();
            }
        });
    }

    public function down()
    {
        //
    }
}
