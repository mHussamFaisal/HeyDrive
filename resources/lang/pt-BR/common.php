<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portuguese (pt-BR) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Customer',
        'driver' => 'Driver',
        'admin' => 'Admin',
    ],
    'user_status_options' => [
        'approved' => 'Approved',
        'awaiting_admin_review' => 'Pending review',
        'awaiting_email_confirmation' => 'Waiting e-mail confirmation',
        'inactive' => 'Inactive',
        'rejected' => 'Rejected',
    ],
    'user_profile_type_options' => [
        'private' => 'Private',
        'company' => 'Company',
    ],
    'vehicle_status_options' => [
        'activated' => 'Activated',
        'inactive' => 'Inactive',
    ],
    'service_status_options' => [
        'activated' => 'Activated',
        'inactive' => 'Inactive',
    ],
    'event_status_options' => [
        'active' => 'Available',
        'inactive' => 'Busy',
    ],
    'transaction_status_options' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
        'declined' => 'Declined',
        'canceled' => 'Cancelled',
        'authorised' => 'Authorised',
    ],
    'booking_status_options' => [
        'pending' => 'Confirmed', // Pending
        'confirmed' => 'Confirmed',
        'assigned' => 'Assigned',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Job Accepted',
        'rejected' => 'Job Rejected',
        'onroute' => 'On Route',
        'arrived' => 'Arrived',
        'onboard' => 'On Board',
        'completed' => 'Completed',
        'canceled' => 'Cancelled',
        'incomplete' => 'Incomplete',
        'requested' => 'Requested',
        'quote' => 'Quote',
    ],
    'loading' => 'Loading...',
    'loading_warning' => 'Loading it taking a bit too long, please wait few more seconds and if nothing happens then please try to reload the page.',
    'loading_reload' => 'RELOAD NOW',
    'powered_by' => 'Powered by',

];
