<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3336 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category', function (Blueprint $table) {
            if ( !Schema::hasColumn('category', 'icon') ) {
                $table->string('icon')->nullable()->after('type');
            }
        });

        Schema::table('category', function (Blueprint $table) {
            if ( Schema::hasColumn('category', 'icon') ) {
                $categories = \DB::table('category')->get();

                foreach($categories as $k => $category) {
                    switch($category->type) {
                        case 'airport':
                            $icon = 'fa-plane';
                        break;
                        case 'seaport':
                            $icon = 'fa-ship';
                        break;
                        case 'hotel':
                            $icon = 'fa-h-square';
                        break;
                        case 'station':
                            $icon = 'fa-subway';
                        break;
                        case 'address':
                            $icon = 'fa-map-marker';
                        break;
                        case 'postcode':
                            $icon = 'fa-map-marker';
                        break;
                        default:
                            $icon = 'fa-map-marker';
                        break;
                    }

                    $category->icon = $icon;

                    \DB::table('category')->where('id', $category->id)->update((array)$category);
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
        Schema::table('category', function (Blueprint $table) {
            if ( Schema::hasColumn('category', 'icon') ) {
                $table->dropColumn('icon');
            }
        });
    }
}
