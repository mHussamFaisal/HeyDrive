<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3344 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $query = \DB::table('config')->where('key', 'url_logo')->get();

            foreach($query as $k => $config) {
                if ( $config->value ) {
                    $url = $config->value;

                    if ( \App\Helpers\SiteHelper::remoteFileExists($url) ) {
                        $contents = file_get_contents($url);
                        if ( strlen($contents) ) {
                            $name = substr($url, strrpos($url, '/') + 1);
                            $name = \App\Helpers\SiteHelper::generateFilename('logo') .'.'. substr($name, strrpos($name, '.') + 1);
                            \Storage::disk('logo')->put($name, $contents);

                            $config->key = 'logo';
                            $config->value = $name;

                            \DB::table('config')->where('id', $config->id)->update((array)$config);
                        }
                    }
                }
            }

            \DB::table('config')->where('key', 'url_admin')->delete();
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
