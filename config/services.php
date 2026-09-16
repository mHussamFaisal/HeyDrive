<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => eto_config('MAILGUN_DOMAIN'),
        'secret' => eto_config('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => eto_config('SES_KEY'),
        'secret' => eto_config('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => eto_config('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => eto_config('STRIPE_KEY'),
        'secret' => eto_config('STRIPE_SECRET'),
    ],

    'sms_service_type' => eto_config('SMS_SERVICE_TYPE', ''),
    'textlocal' => [
        'key' => eto_config('TEXTLOCAL_KEY'),
        'test' => eto_config('TEXTLOCAL_TEST_MODE', 0),
    ],
    'twilio' => [
        'sid' => eto_config('TWILIO_SID'),
        'token' => eto_config('TWILIO_TOKEN'),
        'phone_number' => eto_config('TWILIO_PHONE_NUMBER'),
    ],
    'smsgateway' => [
        'key' => eto_config('SMSGATEWAY_KEY'),
        'device_id' => eto_config('SMSGATEWAY_DEVICE_ID'),
    ],

    'pcapredict' => [
        'enabled' => eto_config('PCAPREDICT_ENABLED', 0),
        'key' => eto_config('PCAPREDICT_KEY'),
    ],

    'ringcentral' => [
        'environment' => eto_config('RINGCENTRAL_ENVIRONMENT', 'production'), // production | sandbox
        'app_key' => eto_config('RINGCENTRAL_APP_KEY', '19DAv_-xTiivGbTCYFocyQ'),
        'app_secret' => eto_config('RINGCENTRAL_APP_SECRET', 'l-ki4BN0TiSI1tYYjhF7oA8z2mMv4zTQWfKAf3j08lEg'),
        'widget_open' => eto_config('RINGCENTRAL_WIDGET_OPEN', 'all'),
        'popup_open' => eto_config('RINGCENTRAL_POPUP_OPEN', 'all'),
    ],

    'waynium' => [
        'enabled' => eto_config('WAYNIUM_ENABLED', 0),
        'limo_id' => eto_config('WAYNIUM_LIMO_ID'),
        'api_key' => eto_config('WAYNIUM_API_KEY'),
        'secret_key' => eto_config('WAYNIUM_SECRET_KEY'),
    ],

    'flightstats' => [
        'enabled' => eto_config('FLIGHTSTATS_ENABLED', 0),
        'app_id' => eto_config('FLIGHTSTATS_APP_ID'),
        'app_key' => eto_config('FLIGHTSTATS_APP_KEY'),
    ],
];
