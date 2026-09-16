<?php

return [

    /*
    |--------------------------------------------------------------------------
    | English (en-GB) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    'install_welcome' => [
        'subject' => 'Instalation data for :name application',
        'greeting' => 'Dear',
        'license_key' => 'The installation was carried out for the license key  :key',
        'login_data' => 'Below are the login details for your ETO application.',
        'login_email' => 'Email: :email',
        'login_password' => 'Password: :password',
    ],
    'fleet_report' => [
        'greeting' => 'Dear :Name, please find your report below.',
    ],
    'driver_report' => [
        'greeting' => 'Dear :Name, please find your report below.',
    ],

    // Customer
    'customer_booking_created' => [
        'subject' => 'Booking details :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Please find your booking details below.',
        'note_line1' => 'Always keep our telephone number to hand. You must call us immediately if you have any difficulty locating your driver. If your journey is taking place outside our office hours, you will be provided with the drivers mobile contact number.',
        'note_line2' => 'Bookings made directly with the driver are illegal and in case of an accident you will not be covered by the insurance.',
        'note_line3' => 'For Airport pickups, the driver will be waiting in the terminal (meeting point) with a sign with your name (please switch on your mobile once landed). When the pickup is from an address, the driver will be waiting in front of the door. If there are parking restrictions, he will be waiting around pickup location (the closest spot).',
        'section' => [
            'journey_details' => 'Journey Details',
            'customer_details' => 'Customer Details',
            'lead_passenger_details' => 'Lead Passenger Details',
            'reservation_details' => 'Reservation Details',
            'general_details' => 'General Booking Details',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Status update :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Your booking :ref_number status has changed to :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Driver details :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Please find details of your driver below.',
        'booking' => 'Booking',
        'driver' => 'Driver',
        'vehicle' => 'Vehicle',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Driver status :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Your driver is on the way.',
        'booking' => 'Booking',
        'driver' => 'Driver',
        'vehicle' => 'Vehicle',
    ],
    'customer_booking_completed' => [
        'subject' => 'Feedback :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Thank you for using :company_name to complete your journey.',
        'line2' => 'We would love to hear your feedback in regards to booking :ref_number that you have recently made with us.',
        'line3' => 'To leave your feedback, please click in this :link.',
        'link' => 'link',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Quote request',
        'greeting' => 'Dear :Name',
        'line1' => 'Thank you for requesting a quote. We will contact you shortly.',
        'line2' => 'In the meantime here is a summary of the details you have submitted for quotation.',
        'section' => [
            'journey_details' => 'Journey Details',
            'customer_details' => 'Customer Details',
            'lead_passenger_details' => 'Lead Passenger Details',
            'general_details' => 'General Details',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Payment request :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'In order to secure your booking please make a payment of :price.',
        'line2' => 'You can make a payment by clicking on this :link.',
        'link' => 'link',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Payment confirmation :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Please find attached invoice below.',
        'line2' => 'Thank you for booking with :company_name.',
        'line3' => 'Payment for booking :ref_number has been :status.',
        'status' => 'ACCEPTED AND CONFIRMED',
    ],
    'customer_account_activation' => [
        'subject' => 'Activation',
        'greeting' => 'Dear :Name',
        'line1' => 'Here is your activation link.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Welcome',
        'greeting' => 'Dear :Name',
        'line1' => 'Your account has been successfully activated. You can login now!',
    ],
    'customer_account_password' => [
        'subject' => 'Reset password',
        'greeting' => 'Dear :Name',
        'line1' => 'Here is your token for resetting the password: :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Job status update :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Job :ref_number status has changed to :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Booking cancellation :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Customer :customer_name has cancelled booking :ref_number on :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Job status update :ref_number',
        'greeting' => 'Dear :Name',
        'line1' => 'Job :ref_number status has changed from :old_status to :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'Email',
        'site' => 'Website',
        'feedback' => 'You can leave your feedback :link',
        'feedback_link' => 'here',
    ],
    'powered_by' => 'Powered by',

];
