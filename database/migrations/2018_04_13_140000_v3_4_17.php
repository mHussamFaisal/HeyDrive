<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3417 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $inserts = [];
            $sites = \App\Models\Site::all();

            foreach($sites as $kS => $site) {
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'square')->first();
                if ( empty($check->id) ) {
                    $image_gallery = 'square.png';
                    $image_name = '';

                    if ( \Storage::disk('images-payments')->exists($image_gallery) ) {
                        $filepath = asset_path('images','payments/'. $image_gallery);
                        $filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. \File::extension($filepath);
                        \Storage::disk('payments')->put($filename, \Storage::disk('images-payments')->get($image_gallery));
                        if ( \Storage::disk('payments')->exists($filename) ) {
                            $image_name = $filename;
                        }
                    }

                    $inserts[] = [
                        'profile_id' => $site->id,
                        'name' => 'Square',
                        'description' => 'Credit or Debit Card',
                        'payment_page' => "Pay by Credit or Debit Card via Square\r\nYou will be passed over to secure Square payment pages to complete your booking.\r\nClick the button below to proceed with your booking.",
                        'image' => $image_name,
                        'params' => '{"live_location_id":"","live_access_token":"","test_location_id":"","test_access_token":"","currency_code":"GBP","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                        'method' => 'square',
                        'factor_type' => 1,
                        'price' => 0.00,
                        'default' => 0,
                        'ordering' => 11,
                        'published' => 0,
                        'is_backend' => 0
                    ];
                }
            }

            DB::beginTransaction();
            try {
                if (!empty($inserts)) {
                    DB::table('payment')->insert($inserts);
                }
                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration V3417 (rollback): '. $e->getMessage());
                DB::rollback();
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
