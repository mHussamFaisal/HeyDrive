<?php

use Illuminate\Database\Seeder;

class ETOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::connection()->getPdo();

            if (\DB::connection()->getDatabaseName() && !\DB::connection()->getSchemaBuilder()->hasTable('migrations')) {
                $file = realpath(__DIR__.'/sql/eto_v3.sql');

                if ($file) {
                    $content = \File::get($file);
                    $content = str_replace('[DB_PREFIX]', config('database.connections.'. config('database.default') .'.prefix'), $content);

                    $configInstallData = (object)request()->all();

                    if (!empty($configInstallData->mail_driver) ) {
                        $content = str_replace('[MAIL_DRIVER]', $configInstallData->mail_driver, $content);
                    } else {
                        $content = str_replace('[MAIL_DRIVER]', 'sendmail', $content);
                    }

                    if ($configInstallData->mail_driver == 'sendmail' ) {
                        if( !empty($configInstallData->mail_sendmail) ) {
                            $content = str_replace('[MAIL_SENDMAIL]', trim($configInstallData->mail_sendmail), $content);
                        } else {
                            $content = str_replace('[MAIL_SENDMAIL]', '', $content); // trim(ini_get('sendmail_path'))
                        }
                    } else {
                        $content = str_replace('[MAIL_SENDMAIL]', '', $content);
                    }

                    if (!empty($configInstallData->mail_host) ) {
                        $content = str_replace('[MAIL_HOST]', $configInstallData->mail_host, $content);
                    } else {
                        $content = str_replace('[MAIL_HOST]', '', $content);
                    }

                    if (!empty($configInstallData->mail_port) ) {
                        $content = str_replace('[MAIL_PORT]', $configInstallData->mail_port, $content);
                    } else {
                        $content = str_replace('[MAIL_PORT]', '', $content);
                    }

                    if (!empty($configInstallData->mail_username) ) {
                        $content = str_replace('[MAIL_USERNAME]', $configInstallData->mail_username, $content);
                    } else {
                        $content = str_replace('[MAIL_USERNAME]', '', $content);
                    }

                    if (!empty($configInstallData->mail_password) ) {
                        $content = str_replace('[MAIL_PASS]', $configInstallData->mail_password, $content);
                    } else {
                        $content = str_replace('[MAIL_PASS]', '', $content);
                    }

                    if (!empty($configInstallData->mail_encryption) ) {
                        $content = str_replace('[MAIL_ENCRYPT]', $configInstallData->mail_encryption, $content);
                    } else {
                        $content = str_replace('[MAIL_ENCRYPT]', '', $content);
                    }

                    if (!empty($configInstallData->app_name) ) {
                        $content = str_replace('[APP_NAME]', $configInstallData->app_name, $content);
                    }

                    if (!empty($configInstallData->app_email) ) {
                        $content = str_replace('[APP_EMAIL]', $configInstallData->app_email, $content);
                    }

                    if (!empty($configInstallData->app_password) ) {
                        $content = str_replace('[APP_PASSWORD]', bcrypt($configInstallData->app_password), $content);
                    }

                    $siteKey = md5('ETO-'. date('Y-m-d-H:i:s') .'-'. rand(10000, 100000000));
                    $content = str_replace('[APP_KEY]', $siteKey, $content);

                    if (!empty($configInstallData->app_license) ) {
                        $content = str_replace('[APP_LICENSE]', $configInstallData->app_license, $content);
                    }

                    // Copy vehicle type images
                    $files = [
                        'vehicle_type1515618352.png' => 'black_saloon.png', // Saloon
                        'vehicle_type1515618388.png' => 'black_MPV.png', // MPV
                        'vehicle_type1515618396.png' => 'black_8_seater.png', // 8 Seater
                        'vehicle_type1515618380.png' => 'black_executive.png', // Executive
                        'vehicle_type1515618369.png' => 'black_estate.png', // Estate
                    ];

                    foreach ($files as $k => $v) {
                        if (\Storage::disk('images-vehicles-types')->exists($v)) {
                            $filepath = asset_path('images','vehicles-types/'. $v);
                            $filename = \App\Helpers\SiteHelper::generateFilename('vehicle_type') .'.'. \File::extension($filepath);

                            \Storage::disk('vehicles-types')->put($filename, \Storage::disk('images-vehicles-types')->get($v));
                            if (\Storage::disk('vehicles-types')->exists($filename)) {
                                $content = str_replace($k, $filename, $content);
                            }
                        }
                    }

                    // Copy payment method images
                    $files = [
                        'cash.png' => 'cash.png', // Cash
                        'creditcards.png' => 'creditcards.png', // Barclaycard
                        'cardsave.png' => 'worldpay.png', // Cardsave
                        'paypal.png' => 'paypal.png', // PayPal
                        'redsys.png' => 'redsys.png', // Redsys
                        'stripe.png' => 'stripe.png', // Stripe
                        'worldpay.png' => 'worldpay.png', // Worldpay
                        'account.png' => 'account.png', // Account
                        'payzone.png' => 'payzone.png', // Payzone
                        'bacs.png' => 'bacs.png', // BACS
                    ];

                    foreach ($files as $k => $v) {
                        if (\Storage::disk('images-payments')->exists($v)) {
                            $filepath = asset_path('images','payments/'. $v);
                            $filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. \File::extension($filepath);

                            \Storage::disk('payments')->put($filename, \Storage::disk('images-payments')->get($v));
                            if (\Storage::disk('payments')->exists($filename)) {
                                $content = str_replace($k, $filename, $content);
                            }
                        }
                    }

                    // Import SQL
                    \DB::unprepared($content);
                }
            }
        }
        catch (\Exception $e) {
            // Could not connect to the database.
        }
    }
}
