<?php

return [
    'custom_field' => [
        'display' => false,
        'name' => '',
    ],
    'status_color' => [
        'pending' => '#2d34d9',
        'requested' => '#ff7300',
        'quote' => '#51c0c5',
        'assigned' => '#9e2659',
        'auto_dispatch' => '#e64cde',
        'accepted' => '#00c0ef',
        'rejected' => '#dd4b39',
        'onroute' => '#605ca8',
        'arrived' => '#605ca8',
        'onboard' => '#605ca8',
        'completed' => '#00a65a',
        'canceled' => '#dd4b39',
        'unfinished' => '#dd4b39',
        'incomplete' => '#ff0000',
    ],
    'form' => [
        'checked' => [
            'send_notification' => false,
        ],
        'view' => [
            'add_vehicle_button' => false,
            'advance_open' => false,
            'amounts_view_carry_on' => true,
            'amounts_view_passenger' => true,
            'amounts_view_suitcase' => true,
            'instant_dispatch_color_system' => true,
            'show_inactive_drivers_form' => false,
            'waiting_time' => false,
        ],
    ],
    'discount' => [
        'child_seats' => 1,
        'additional_items' => 1,
        'parking_charges' => 1,
        // 'payment_charges' => 1,
        'meet_and_greet' => 1,
    ],
    'admin_check_incomplete_bookings_cache_time' => 15, // Minutes
    'admin_check_user_documents_cache_time' => 15, // Minutes
    'admin_check_vehicle_documents_cache_time' => 15, // Minutes
    'driver_check_user_documents_cache_time' => 15, // Minutes
    'sources' => [], // 'Website','By phone','Direct','Admin','Other'
    'admin_bookings_state' => null,
    'admin_dispatch_state' => null,
];
