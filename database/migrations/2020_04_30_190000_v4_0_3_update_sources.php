<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V403UpdateSources extends Migration
{
    public function up()
    {
        if (Schema::hasTable('config')) {
            $q = \DB::table('config')->where('key', 'source_list')->get();
            $sources = [];
            foreach ($q as $k => $v) {
                if (!empty($v->value)) {
                    $list = json_decode($v->value, true) ?: [];
                    foreach ($list as $k2 => $v2) {
                        $v2 = trim($v2);
                        if (!empty($v2) && !in_array($v2, $sources)) {
                            $sources[] = $v2;
                        }
                    }
                }
            }
            sort($sources);
            $sources = json_encode($sources);

            $id = 0;
            $subscription = \App\Models\Subscription::select('id')->first();
            if (!empty($subscription->id)) {
                $id = (int)$subscription->id;
            }

            DB::table('settings')->insert([
                'relation_id' => $id,
                'relation_type' => 'subscription',
                'param' => 'eto_booking.sources',
                'value' => $sources
            ]);

            \DB::table('config')->where('key', 'source_list')->delete();
        }
    }

    public function down()
    {
        //
    }
}
