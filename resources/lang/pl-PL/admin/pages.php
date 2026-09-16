<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polish (pl-PL) - Admin Pages
    |--------------------------------------------------------------------------
    */

    'mobile_app' => [
        'page_title' => 'Driver App',
        'how_to' => 'How to set up a Driver App?',
        'steps' => [
            'step1' => 'First of all Admin needs to create Driver account. This can be done here in Dashboard -> Users -> Drivers -> <a href=\":driver_url\">Add New</a>.',
            'step2' => 'Ask Driver to go to <b>Google Play</b> or <b>Apple iTunes</b> store and download the "<b>ETO Driver</b>".',
            'step3' => 'Give driver access (email, password) to account that Admin has created and this Host URL "<b>:host_url</b>".',
            'step4' => 'Once the Driver downloaded and installed the app then he can login with provided details and start using the app immediately.',
            'step5' => "That's all.",
        ],
        'download' => 'Download Driver App',
        'download_google' => 'Download App from Google Play',
        'download_apple' => 'Download App from Apple Store',
    ],
    'license' => [
        'page_title' => 'Licence',
        'no_licence' => "The license file doesn't exist",
    ],

];
