<?php

return [

    /*
    |--------------------------------------------------------------------------
    | German (de-DE) - Admin Pages
    |--------------------------------------------------------------------------
    */

    'web_widget' => [
        'page_title' => 'Web Widgets',
    ],
    'mobile_app' => [
        'page_title' => 'Mobile Apps',
        'customer' => [
            'heading' => 'Passenger App',
            'benefits_user' => [
                'heading' => 'Benefits for customer',
                'desc' => "Customer can make booking on the go.\r\n".
                          "Simplified view and layout, making a booking never been easier.\r\n".
                          "Instant notification - no need for emails or sms.",
            ],
            'benefits_company' => [
                'heading' => 'Benefits for company',
                'desc' => "Booking never been easier - meaning more booking will be made.\r\n".
                          "Instant notification - no more issues with email going to Spam, no need to pay for sms.\r\n".
                          "Greater integration with customer.",
            ],
            'usage' => [
                'heading' => 'How to download and install the Passenger App?',
                'subheading' => 'The process is very easy and only takes a minute.',
                'desc' => "Download and install \"<b>ETO Passenger</b>\" app (links to app stores below).\r\n".
                          "In search box type in \"<b>:host_name</b>\", click the search button and then select an option from the list below. Then click the \"ADD\" button.\r\n".
                          "If you can not find your company's name, just click the \"GO\" button to add a new account manually and then type in this Host URL \"<b>:host_url</b>\".\r\n".
                          "If you already have an account, just log in using the same details. If you don’t have an account yet, then click Register in order to create one, then confirm it via our activation email and log in.\r\n".
                          "Once you log in, you are ready to go!",
            ],
            'note' => 'Need a Personalized Passenger app which allows full company branding? For pricing, click :link.',
            'note_link' => 'here',
        ],
        'driver' => [
            'heading' => 'Driver App',
            'benefits_user' => [
                'heading' => 'Benefits for drivers',
                'desc' => "Driver can use it on the go.\r\n".
                          "Simplified view and layout - quick and easy use.\r\n".
                          "Instant notification - no need for emails or sms.",
            ],
            'benefits_company' => [
                'heading' => 'Benefits for company',
                'desc' => "Instant notification - no more issues with email going to Spam, no need to pay for sms.\r\n".
                          "Tracking system - operator can see all driver in real time on the map.",
            ],
            'usage' => [
                'heading' => 'How to setup a Driver App?',
                'desc' => "First of all Admin needs to create Driver account. This can be done here in Dashboard -> Users -> Drivers -> <a href=\":driver_url\">Add New</a>.\r\n".
                          "Ask Driver to go to <b>Google Play</b> or <b>Apple iTunes</b> store and download the \"<b>ETO Driver</b>\".\r\n".
                          "Give driver access (email, password) to account that Admin has created and this Host URL \"<b>:host_url</b>\".\r\n".
                          "Once the Driver downloaded and installed the app then he can login with provided details and start using the app immediately.\r\n".
                          "That's all.",
            ],
            'note' => 'Need a Personalized Driver app which allows full company branding? For pricing, click :link.',
            'note_link' => 'here',
        ],
        'download_google' => 'Download App from Google Play',
        'download_apple' => 'Download App from Apple Store',
    ],
    'license' => [
        'page_title' => 'Licence',
        'no_licence' => "The license file doesn't exist",
    ],

];
