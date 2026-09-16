<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portuguese (pt-BR) - Admin Settings
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
        'button' => [
            'clear' => 'Clear',
            'save' => 'Save',
        ],
        'message' => [
            'no_vehicles' => 'No vehicles.',
        ],
    ],

];
