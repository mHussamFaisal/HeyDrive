<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3120 extends Migration
{
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $inserts = [];
            $sites = \App\Models\Site::all();

            foreach($sites as $kS => $site) {
                // GP webpay
                $check = DB::table('payment')->where('profile_id', '=', $site->id)->where('method', '=', 'gpwebpay')->first();
                if ( empty($check->id) ) {
                    $image = 'gpwebpay.png';
                    if ( \Storage::disk('images-payments')->exists('gpwebpay.png') ) {
                        $filepath = asset_url('images','payments/gpwebpay.png');
                        $filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. \File::extension($filepath);
                        \Storage::disk('payments')->put($filename, \Storage::disk('images-payments')->get('gpwebpay.png'));
                        if ( \Storage::disk('payments')->exists($filename) ) {
                            $image = $filename;
                        }
                    }

                    $inserts[] = [
                        'profile_id' => $site->id,
                        'name' => 'GP webpay',
                        'description' => 'Credit or Debit Card',
                        'payment_page' => "Pay by Credit or Debit Card via GP webpay\r\nYou will be passed over to secure payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                        'image' => $image,
                        'params' => '{"merchant_number":"","private_key":"","private_key_password":"","public_key":"","currency_code":"203","operation_mode":"1","language_code":"","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                        'method' => 'gpwebpay',
                        'factor_type' => 1,
                        'price' => 0,
                        'default' => 0,
                        'ordering' => 12,
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
                \Log::error('Migration V3120 (rollback): '. $e->getMessage());
                DB::rollback();
            }
        });
    }

    public function down()
    {
        //
    }
}
