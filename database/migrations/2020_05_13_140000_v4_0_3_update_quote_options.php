<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V403UpdateQuoteOptions extends Migration
{
    public function up()
    {
        if (Schema::hasTable('config')) {
            DB::beginTransaction();
            try {
                $inserts = [];
                $config = \DB::table('config')
                  ->where('key', 'booking_manual_quote_enable')
                  ->orWhere('key', 'booking_hide_vehicle_without_price')
                  ->get();

                foreach($config as $k => $v) {
                    $v = (object)$v;

                    if ($v->key == 'booking_manual_quote_enable' && $v->value == '1') {
                        $check = \DB::table('config')->where('key', 'booking_price_status_on')->get();
                        if ($check->count() == 0) {
                            $inserts[] = [
                                'site_id' => $v->site_id,
                                'key' => 'booking_price_status_on',
                                'value' => '1',
                                'type' => 'int',
                                'browser' => '0'
                            ];
                        }
                    }

                    if ($v->key == 'booking_hide_vehicle_without_price' && $v->value == '1') {
                        $check = \DB::table('config')->where('key', 'booking_price_status_on_enquiry')->get();
                        if ($check->count() == 0) {
                            $inserts[] = [
                                'site_id' => $v->site_id,
                                'key' => 'booking_price_status_on_enquiry',
                                'value' => '1',
                                'type' => 'int',
                                'browser' => '0'
                            ];
                        }

                        $check = \DB::table('config')->where('key', 'booking_price_status_off')->get();
                        if ($check->count() == 0) {
                            $inserts[] = [
                                'site_id' => $v->site_id,
                                'key' => 'booking_price_status_off',
                                'value' => '1',
                                'type' => 'int',
                                'browser' => '0'
                            ];
                        }
                    }
                }

                if (!empty($inserts)) {
                    DB::table('config')->insert($inserts);
                }

                DB::table('config')
                  ->where('key', 'booking_manual_quote_enable')
                  ->orWhere('key', 'booking_hide_vehicle_without_price')
                  ->delete();

                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration V403UpdateQuoteOptions (rollback): '. $e->getMessage());
                DB::rollback();
            }
        }
    }

    public function down()
    {
        //
    }
}
