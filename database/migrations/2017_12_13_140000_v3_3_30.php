<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3330 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment', function (Blueprint $table) {
            $sites = \App\Models\Site::all();

            foreach($sites as $kS => $site) {
                // Cash
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'cash')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Cash',
                            'description' => 'Cash',
                            'payment_page' => "",
                            'image' => 'cash.png',
                            'params' => '{}',
                            'method' => 'cash',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 1,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // ePDQ
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'epdq')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Barclaycard (ePDQ)',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via Barclaycard\r\nYou will be passed over to secure Barclaycard payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'creditcards.png',
                            'params' => '{"pspid":"","pass_phrase":"","paramvar":"","operation_mode":"SAL","currency_code":"GBP","language_code":"auto","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'epdq',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 4,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // Cardsave
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'cardsave')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Cardsave',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via Cardsave\r\nYou will be passed over to secure Worldpay payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'worldpay.png',
                            'params' => '{"pre_shared_key":"","merchant_id":"","password":"","operation_mode":"SALE","country_code":"826","currency_code":"826","language_code":"auto","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'cardsave',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 8,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // PayPal
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'paypal')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'PayPal',
                            'description' => 'PayPal',
                            'payment_page' => "Pay with PayPal\r\nYou will be passed over to secure PayPal payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'paypal.png',
                            'params' => '{"paypal_email":"","currency_code":"GBP","language_code":"auto","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'paypal',
                            'factor_type' => 1,
                            'price' => 2.90,
                            'default' => 0,
                            'ordering' => 2,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // Redsys
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'redsys')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Redsys',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via RedSys\r\nYou will be passed over to secure RedSys payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'redsys.png',
                            'params' => '{"merchant_id":"","terminal_id":"001","encryption_key":"","signature_version":"HMAC_SHA256_V1","operation_mode":"0","currency_code":"978","language_code":"auto","test_mode":"0","test_amount":"1.00","deposit":"1"}',
                            'method' => 'redsys',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 5,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // Stripe
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'stripe')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Stripe',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via Stripe\r\nYou will be passed over to secure Stripe payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'stripe.png',
                            'params' => '{"pk_live":"","sk_live":"","pk_test":"","sk_test":"","sca_mode":"1","zip_code":"true","three_d_secure":"false","currency_code":"GBP","language_code":"auto","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'stripe',
                            'factor_type' => 1,
                            'price' => 1.40,
                            'default' => 0,
                            'ordering' => 3,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // Worldpay
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'worldpay')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Worldpay',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via Worldpay\r\nYou will be passed over to secure Worldpay payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'worldpay.png',
                            'params' => '{"inst_id":"","md5_secret":"","signature_fields":"instId:amount:currency:cartId","currency_code":"GBP","language_code":"auto","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'worldpay',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 6,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // Account
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'account')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Reserve now',
                            'description' => '',
                            'payment_page' => "",
                            'image' => 'account.png',
                            'params' => '{}',
                            'method' => 'account',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 9,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
                }

                // Payzone
                $check = DB::table('payment')->where('profile_id', '=',  $site->id)->where('method', '=', 'payzone')->first();
                if ( empty($check->id) ) {
                    DB::table('payment')->insert([
                        [
                            'profile_id' => $site->id,
                            'name' => 'Payzone',
                            'description' => 'Credit or Debit Card',
                            'payment_page' => "Pay by Credit or Debit Card via Payzone\r\nYou will be passed over to secure Payzone payment page to complete your booking.\r\nClick the button below to proceed with your booking.",
                            'image' => 'payzone.png',
                            'params' => '{"pre_shared_key":"","merchant_id":"","password":"","operation_mode":"SALE","country_code":"826","currency_code":"826","language_code":"auto","test_mode":"0","test_amount":"0.00","deposit":"1"}',
                            'method' => 'payzone',
                            'factor_type' => 1,
                            'price' => 0.00,
                            'default' => 0,
                            'ordering' => 7,
                            'published' => 0,
                            'is_backend' => 0,
                        ],
                    ]);
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
        //
    }
}
