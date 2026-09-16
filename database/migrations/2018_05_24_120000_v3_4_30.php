<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class V3430 extends Migration
{
    public function up()
    {
        if ( Schema::hasTable('services') ) {
            Schema::table('services', function (Blueprint $table) {
                $table->integer('parent_id')->unsigned()->after('id');
                $table->string('parent_type')->after('parent_id');
                $table->index(['parent_id', 'parent_type']);
                $table->text('description')->nullable()->after('name');
                $table->text('params')->nullable()->after('description');
                $table->tinyInteger('is_featured')->default(0)->unsigned()->after('params');
                $table->integer('order')->default(0)->unsigned()->after('is_featured');
                $table->string('status')->nullable(false)->change();
                $table->softDeletes()->after('updated_at');
            });

            $services = DB::table('services')->get();

            foreach ($services as $service) {
                if ($service->status == 'activated') {
                    $service->status = 'active';
                }

                if ($service->type == 'availability') {
                    $service->type = 'standard';
                    $availability = 1;
                }
                else {
                    $availability = 0;
                }

                $params = [
                    'availability' => $availability,
                    'hide_location' => $service->hide_location,
                    'duration' => $service->duration,
                    'duration_min' => $service->duration_min,
                    'duration_max' => $service->duration_max,
                    'factor_type' => $service->factor_type,
                    'factor_value' => $service->factor_value,
                ];

                DB::table('services')->where('id', $service->id)->update([
                    'parent_type' => 'site',
                    'parent_id' => $service->site_id,
                    'type' => $service->type,
                    'params' => json_encode($params),
                    'is_featured' => $service->selected,
                    'order' => $service->ordering,
                    'status' => $service->status,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn([
                    'site_id',
                    'factor_type',
                    'factor_value',
                    'duration',
                    'duration_min',
                    'duration_max',
                    'hide_location',
                    'selected',
                    'ordering',
                ]);
            });
        }
    }

    public function down()
    {
        //
    }
}
