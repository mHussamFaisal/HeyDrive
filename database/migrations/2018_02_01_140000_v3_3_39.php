<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3339 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('charge', function (Blueprint $table) {
            $charges = \DB::table('charge')
                ->whereIn('type', ['geocode_start', 'geocode_start2', 'geocode_end'])
                ->get();

            foreach($charges as $k => $v) {
                if ( in_array($v->type, ['geocode_start', 'geocode_start2']) ) {
                    $type = 'from';
                }
                elseif ( in_array($v->type, ['geocode_end']) ) {
                    $type = 'to';
                }
                else {
                    $type = 'all';
                }

                $list = [];
                if ( !empty($v->params) ) {
                    foreach ( json_decode($v->params) as $kP => $vP ) {
                        $list[] = [
                            'address' => $vP,
                            'postcode' => $vP,
                            'lat' => 0,
                            'lng' => 0,
                        ];
                    }
                }

                $params = [
                    'location' => [
                        'enabled' => 1,
                        'type' => $type,
                        'list' => $list
                    ]
                ];

                $row = [
                    'profile_id' => $v->profile_id,
                    'note' => $v->note,
                    'note_published' => $v->note_published,
                    'type' => 'parking',
                    'params' => json_encode($params),
                    'value' => $v->value,
                    'start_date' => $v->start_date,
                    'end_date' => $v->end_date,
                    'published' => $v->published
                ];

                $ok = DB::table('charge')->insertGetId((array)$row);
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
