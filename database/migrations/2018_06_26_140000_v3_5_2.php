<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V352 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $p = [];

            $query = DB::table('config')->whereIn('key', [
                'night_charge_factor_type',
                'night_charge_factor',
                'night_charge_start',
                'night_charge_end',
            ])->get();

            foreach($query as $k => $v) {
                if (empty($p[$v->profile_id])) {
                    $p[$v->profile_id] = [];
                }
                $p[$v->profile_id][$v->key] = $v->value;
            }

            foreach($p as $k => $v) {
                if (!empty($v['night_charge_factor'])) {
                    DB::table('config')->insertGetId([
                        'profile_id' => $k,
                        'key' => 'booking_night_surcharge',
                        'value' => json_encode([(object)[
                            'vehicle_id' => 0,
                            'repeat_days' => [],
                            'time_start' => $v['night_charge_start'],
                            'time_end' => $v['night_charge_end'],
                            'factor_type' => (isset($v['night_charge_factor_type']) && $v['night_charge_factor_type'] == 1) ? 'addition' : 'multiplication',
                            'factor_value' => (float)$v['night_charge_factor'],
                        ]]),
                        'type' => 'object',
                        'browser' => '0',
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
