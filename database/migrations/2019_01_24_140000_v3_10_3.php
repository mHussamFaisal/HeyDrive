<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3103 extends Migration
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
                // Stripe iDEAL
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'stripe_ideal')->first();
                if ( empty($check->id) ) {
                    $image = 'ideal.png';
                    if ( \Storage::disk('images-payments')->exists('ideal.png') ) {
                        $filepath = asset_path('images','payments/ideal.png');
                        $filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. \File::extension($filepath);
                        \Storage::disk('payments')->put($filename, \Storage::disk('images-payments')->get('ideal.png'));
                        if ( \Storage::disk('payments')->exists($filename) ) {
                            $image = $filename;
                        }
                    }

                    $check = DB::table('payment')->where('profile_id', $site->id)->where('method', 'stripe_ideal')->get();

                    if ($check->count() <= 0) {
                        $inserts[] = [
                            'profile_id' => $site->id,
                            'name' => 'Stripe iDEAL',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via iDEAL\r\nYou will be passed over to secure iDEAL payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => $image,
                            'params' => '{"pk_live":"","sk_live":"","pk_test":"","sk_test":"","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'stripe_ideal',
                            'factor_type' => 1,
                            'price' => 1.40,
                            'default' => 0,
                            'ordering' => 3,
                            'published' => 0,
                            'is_backend' => 0
                        ];
                    }
                }
            }

            DB::beginTransaction();
            try {
                if (!empty($inserts)) {
                    DB::table('payment')->insert($inserts);
                }
                DB::commit();
            } catch (\Exception $e) {
                \Log::error('Migration V3103 (rollback): '. $e->getMessage());
                DB::rollback();
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if ( !Schema::hasColumn('transactions', 'params') ) {
                $table->text('params')->nullable()->after('response');
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
        Schema::table('transactions', function (Blueprint $table) {
            if ( Schema::hasColumn('transactions', 'params') ) {
                $table->dropColumn('params');
            }
        });
    }
}
