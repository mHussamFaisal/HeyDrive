<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3201 extends Migration
{
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $inserts = [];
            $sites = DB::table('profile')->get();

            foreach($sites as $kS => $site) {
                $check = DB::table('config')->where('site_id', '=', $site->id)->where('key', '=', 'booking_required_address_complete')->where('value', '1')->first();
                if (!empty($check->id)) {
                    $required = !empty($check->value) ? 1 : 0;
                }
                else {
                    $required = 0;
                }

                $inserts[] = [
                    'site_id' => $site->id,
                    'key' => 'booking_required_address_complete_from',
                    'value' => $required,
                    'type' => 'int',
                    'browser' => 1
                ];

                $inserts[] = [
                    'site_id' => $site->id,
                    'key' => 'booking_required_address_complete_to',
                    'value' => $required,
                    'type' => 'int',
                    'browser' => 1
                ];

                $inserts[] = [
                    'site_id' => $site->id,
                    'key' => 'booking_required_address_complete_via',
                    'value' => $required,
                    'type' => 'int',
                    'browser' => 1
                ];
            }

            DB::beginTransaction();
            try {
                if (!empty($inserts)) {
                    DB::table('config')->insert($inserts);
                }
                DB::table('config')->where('key', '=', 'booking_required_address_complete')->delete();
                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration V3201 (rollback): '. $e->getMessage());
                DB::rollback();
            }
        });
    }

    public function down()
    {
        //
    }
}
