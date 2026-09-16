<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSourceList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('config')) {
            $sources = \DB::table('config')->where('key', 'source_list')->get();

            DB::beginTransaction();
            try {
                foreach($sources as $source) {
                    $source = (object)$source;
                    if (!empty($source->value)) {
                        $data = json_decode($source->value, true);

                        foreach($data as $key=>$value) {
                            if (trim($value) == '') {
                                unset($data[$key]);
                            }
                        }

                        $source->value = json_encode(array_values($data));

                        \DB::table('config')->where('id', $source->id)->update(['value' => $source->value]);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration UpdateSourceList (rollback): '. $e->getMessage());
                DB::rollback();
            }
        }
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
