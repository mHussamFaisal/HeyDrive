<?php

return [

    /*
    |--------------------------------------------------------------------------
    | German (de-DE) - Admin Settings
    |--------------------------------------------------------------------------
    |
    | Used in admin settings tab.
    |
    */

    'page_title' => 'Settings',
    'charges' => [
        'subtitle' => 'Parking charge',
        'type' => 'Charge',
        'type_options' => [
            'select' => '-- Select --',
            'parking' => 'Parking',
            'waiting' => 'Waiting',
        ],
        'price' => 'Price',
        'name' => 'Name',
        'name_enabled' => 'Display charge name in summary',
        'location_enabled' => 'Limit to location',
        'location_type' => 'Apply on',
        'location_type_options' => [
            'all' => 'Any',
            'from' => 'Pickup',
            'to' => 'Dropoff',
            'via' => 'Via',
        ],
        'vehicle_enabled' => 'Limit to vehicle type',
        'vehicle_all' => 'All',
        'datetime_enabled' => 'Limit to date & time',
        'datetime_start' => 'Starts on',
        'datetime_end' => 'Ends on',
        'status' => 'Active',
        'message' => [
            'no_vehicles' => 'No vehicles.',
        ],
    ],
    'notifications' => [
        'subtitle' => 'Notifications',
        'subtitle_desc' => "Notification system allows setting up:\r\n- which notification will be send\r\n- to whom it will be send\r\n- and how it will be send\r\n\r\n".
                            "In order to make SMS system working, first you need to integrate it. Go to Settings -> Integration -> and setup SMS service\r\n\r\n".
                            "In order to Push notification working properly, your drivers need to download, setup and login to driver app. Please check Driver App section how to do it.\r\n\r\n".
                            "In order to Push notification working properly, your customer need to download, setup and login to customer app. Customer App will be available from 1 April.",
        'types' => [
            'booking_pending' => [
                'title' => 'Status - Confirmed', // Status - Pending
                'desc' => "This notification is sent when New Booking has been created.\r\n\r\nNew booking can be created by:\r\n- customer through website Web Booking\r\n- admin through dashboard New Booking form",
            ],
            'booking_quote' => [
                'title' => 'Status - Request Quote',
                'desc' => 'This notification is sent when New Booking has been created but price has not been display to customer.',
            ],
            'booking_requested' => [
                'title' => 'Status - Unconfirmed',
                'desc' => 'This notification is sent when New Booking has been created but company has enable option "Booking status is unconfirmed" (go to Settings -> Web Booking Widget -> General)',
            ],
            'booking_confirmed' => [
                'title' => 'Status - Confirmed',
                'desc' => 'This notification is sent when admin change status to Confirmed.',
            ],
            'booking_assigned' => [
                'title' => 'Status - Job Assigned',
                'desc' => 'This notification is sent when admin assigned a driver to booking.',
            ],
            'booking_auto_dispatch' => [
                'title' => 'Status - Auto Dispatch',
                'desc' => 'This notification is sent when admin auto dispatch a driver.',
            ],
            'booking_accepted' => [
                'title' => 'Status - Job Accepted',
                'desc' => 'This notification is sent when driver accept the job by changing status to Job Accepted.',
            ],
            'booking_rejected' => [
                'title' => 'Status - Job Rejected',
                'desc' => 'This notification is sent when driver rejected the job by changing status to Job Rejected.',
            ],
            'booking_onroute' => [
                'title' => 'Status - On Route',
                'desc' => 'This notification is sent when driver starts his journey to pickup destination and change status to On Route.',
            ],
            'booking_arrived' => [
                'title' => 'Status - Arrived',
                'desc' => 'This notification is sent when driver arrives to pickup destination and change status to Arrived.',
            ],
            'booking_onboard' => [
                'title' => 'Status - On Board',
                'desc' => 'This notification is sent when customer is in the vehicle and driver change status to On Board.',
            ],
            'booking_completed' => [
                'title' => 'Status - Completed',
                'desc' => 'This notification is sent when driver finish the job and change status to Completed.',
            ],
            'booking_canceled' => [
                'title' => 'Status - Cancelled',
                'desc' => 'This notification is sent when admin change status to Cancelled.',
            ],
            'booking_incomplete' => [
                'title' => 'Status - Incomplete',
                'desc' => 'This notification is sent when customer choose to pay by card.',
            ],
        ],
        'roles' => [
            'admin' => 'Admin',
            'driver' => 'Driver',
            'customer' => 'Customer',
        ],
        'options' => [
            'email' => 'Email',
            'sms' => 'SMS',
            'push' => 'Push',
            'db' => 'Panel',
        ],
        'notification_booking_pending_info' => 'Booking details footer info',
        'notification_test_email' => 'Test email',
        'notification_test_phone' => 'Test phone',
    ],
    'general' => [
        'subtitle' => 'General',
        'logo' => 'Logo',
        'logo_delete' => 'Delete logo',
    ],
    'button' => [
        'save' => 'Save',
        'saving' => 'Saving...',
        'clear' => 'Clear',
    ],
    'message' => [
        'saved' => 'Saved.',
        'save_error' => 'Settings could not be saved.',
        'connection_error' => 'An error occurred while processing your request.',
    ],
];
