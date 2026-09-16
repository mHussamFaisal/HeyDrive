<?php

return [

    /*
    |--------------------------------------------------------------------------
    | English (en-GB) - Admin Index
    |--------------------------------------------------------------------------
    */

    'page_title' => 'Admin',
    'toggle_navigation' => 'Toggle navigation',
    'member_since' => 'Member since',
    'online' => 'Online',
    'profile' => 'Profile',
    'logout' => 'Sign out',
    'mobile_app' => 'Mobile Apps',
    'licence' => 'Licence',
    'rights' => 'All rights reserved.',
    'version' => 'Version',

    'close' => 'Close',

    'bookings_latest' => 'Latest',
    'bookings_next24' => 'Next 24h',
    'bookings_requested' => 'Unconfirmed',
    'bookings_completed' => 'Completed',
    'bookings_canceled' => 'Cancelled',
    'bookings_all' => 'All',
    'bookings_trash' => 'Trash',

    'menu' => [
        'reports' => [
            'index' => 'Reports',
            'fleet' => 'Fleet Report',
            'driver' => 'Driver Report',
            'customer' => 'Customer Report',
            'payment' => 'Payment Report',
            // 'saved' => 'Saved Reports',
        ],
        'settings' => [
            'translations' => 'Translations',
            'driver_income' => 'Driver Income',
            'driver_discount' => 'Driver Discount',
            'debug' => 'Debug',
        ],
        'news' => 'News',
        'system_logs' => 'System Logs',
    ],
    'alerts' => [
        'license_suspended' => [
            'title' => 'Your account has been suspended for non-payment',
            'message' => "Your account has been suspended due to an outstanding balance. If you wish to restore your account and any attached services, please make a payment promptly and let us know about it to support@easytaxioffice.co.uk. Please be aware that all of your EasyTaxiOffice services will remain suspended until your payment has been processed.\n\rIf you believe this suspension has been made in error please contact us at support@easytaxioffice.co.uk immediately.",
        ],
        'license_suspension_warning' => [
            'title' => 'Your account will be suspended soon for non-payment',
            'message' => "Your account will be suspended in :days day(s) due to an outstanding balance. To avoid it being suspended please make the payment promptly and let us know about it to support@easytaxioffice.co.uk.",
        ],
    ],
    'reminder' => [
        'header' => 'Reminder',
        'type' => [
            'subscription' => 'Your subscription will expire in :days days',
            'update' => 'Your Software Update subscription is valid until, contact EasyTaxiOffice to extend access',
            'update_expired' => 'Your Software Update subscription has finished, to continue to receive regular updates please purchase addition 6 months software update',
            'update_expired_1_month' => 'Your Software Update subscription has already finished one month ago, we strongly recommend to purchase access to updates to enhance user experience as well as not to improve data security',
            'update_expired_3_month' => 'Your Software Update subscription has already finished three months ago, we strongly recommend to purchase access to updates to enhance user experience as well as not to improve data security',
        ]
    ]

];
