<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3222 extends Migration
{
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $payments = \DB::table('payment')->where('method', 'square')->get();

            DB::beginTransaction();
            try {
                foreach($payments as $payment) {
                    $params = json_decode($payment->params) ?: [];
                    $params = (object)$params;

                    if (!empty($params) && !isset($params->legacy_mode)) {
                        $params->legacy_mode = '1';
                        \DB::table('payment')->where('id', $payment->id)->update(['params' => json_encode($params)]);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration V3222 (rollback): '. $e->getMessage());
                DB::rollback();
            }
        });
    }

    public function down()
    {
        //
    }
}
