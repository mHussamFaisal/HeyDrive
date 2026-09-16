<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWpopPayment extends Migration
{
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $inserts = [];
            $sites = \App\Models\Site::all();

            foreach($sites as $kS => $site) {
                // Worldpay Online Payments
                $check = DB::table('payment')->where('site_id', '=', $site->id)->where('method', '=', 'wpop')->first();
                if ( empty($check->id) ) {
                    $image = 'wpop.png';
                    if ( \Storage::disk('images-payments')->exists('wpop.png') ) {
                        $filepath = asset_url('images','payments/wpop.png');
                        $filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. \File::extension($filepath);
                        \Storage::disk('payments')->put($filename, \Storage::disk('images-payments')->get('wpop.png'));
                        if ( \Storage::disk('payments')->exists($filename) ) {
                            $image = $filename;
                        }
                    }

                    $inserts[] = [
                        'site_id' => $site->id,
                        'name' => 'Worldpay Online',
                        'description' => 'Credit or Debit Card',
                        'payment_page' => "Pay by Credit or Debit Card via Worldpay Online Payments\r\nYou will be passed over to secure payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                        'image' => $image,
                        'params' => '{"pk_live":"","sk_live":"","template_code_live":"","pk_test":"","sk_test":"","template_code_test":"","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                        'method' => 'wpop',
                        'factor_type' => 1,
                        'price' => 0,
                        'default' => 0,
                        'ordering' => 13,
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
                \Log::error('Migration AddWpopPayment (rollback): '. $e->getMessage());
                DB::rollback();
            }
        });
    }

    public function down()
    {
        //
    }
}
