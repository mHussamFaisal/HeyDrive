<?php

return [

    /*
    |--------------------------------------------------------------------------
    | German (de-DE) - Notifications
    |--------------------------------------------------------------------------
    */

    'booking_pending' => [
        'subject' => 'New booking :ref_number',
        'message' => 'New booking :ref_number has been created.',
        // 'subject_customer' => ':company_name booking :ref_number',
        // 'message_customer' => 'Thank you for your booking :ref_number.',
        'subject_customer' => ':company_name booking confirmation :ref_number',
        'message_customer' => 'Your booking :ref_number has been confirmed.',
        'info' => "Bookings made directly with the driver are illegal and in case of an accident you will not be covered by the insurance.\r\n\r\n".
                  "For Airport pickups, the driver will be waiting in the terminal (meeting point) with a sign with your name (please switch on your mobile once landed).\r\n\r\n".
                  "For Address pickups, the driver will be waiting in front of the door. If there are parking restrictions, he will be waiting around pickup location (the closest spot).",
    ],
    'booking_quote' => [
        'subject' => 'New booking :ref_number quote request',
        'message' => 'New booking :ref_number quote request is awaiting review.',
        'subject_customer' => ':company_name booking :ref_number quote request',
        'message_customer' => "Thank you for your booking :ref_number quote request.\r\nWe thank you for your patience and we will contact you shortly.",
    ],
    'booking_requested' => [
        'subject' => 'New booking :ref_number request',
        'message' => 'New booking :ref_number request has been made and is awaiting confirmation.',
        'subject_customer' => ':company_name booking :ref_number request',
        'message_customer' => "Thank you for your booking :ref_number request. Please allow us :request_time to confirm your reservation.\r\nWe thank you for your patience and we will contact you shortly.",
    ],
    'booking_confirmed' => [
        'subject' => ':company_name booking confirmation :ref_number',
        'message' => 'Your booking :ref_number has been confirmed.',
    ],
    'booking_assigned' => [
        'subject' => 'New job :ref_number assigned',
        'message' => 'You have been assigned a new job :ref_number.',
        'subject_customer' => 'Driver details. Booking :ref_number',
        'message_customer' => 'Please find your driver details below.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
    ],
    'booking_auto_dispatch' => [
        'subject' => 'New job :ref_number assigned',
        'message' => 'You have been assigned a new job :ref_number.',
        'subject_customer' => 'Driver details. Booking :ref_number',
        'message_customer' => 'Please find your driver details below.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
    ],
    'booking_accepted' => [
        'subject' => 'Job accepted :ref_number',
        'message' => 'Job :ref_number. Driver :driver_name has accepted the job.',
        'subject_customer' => 'Driver details. Booking :ref_number',
        'message_customer' => 'Please find your driver details below.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
    ],
    'booking_rejected' => [
        'subject' => 'Job rejected :ref_number',
        'message' => 'Job :ref_number. Driver :driver_name has rejected the job.',
    ],
    'booking_onroute' => [
        'subject' => 'Driver on route :ref_number',
        'message' => 'Job :ref_number. Driver :driver_name is on route.',
        'subject_customer' => 'Driver is on the way. Booking :ref_number',
        'message_customer' => "Driver is on the way.",
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark is on the way to :booking_from.',
    ],
    'booking_arrived' => [
        'subject' => 'Driver has arrived :ref_number',
        'message' => 'Job :ref_number. Driver :driver_name has arrived to pickup destination and is awaiting the customer.',
        'subject_customer' => 'Driver has arrived :ref_number',
        'message_customer' => 'Driver :driver_name has arrived and is waiting at agreed pickup location. Booking :ref_number.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name has arrived and is waiting at agreed pickup location. Vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_onboard' => [
        'subject' => 'Customer on board :ref_number',
        'message' => 'Job :ref_number. Customer is on board.',
    ],
    'booking_completed' => [
        'subject' => 'Job completed :ref_number',
        'message' => 'Job :ref_number has been completed.',
        'subject_customer' => 'Feedback :ref_number',
        'message_customer' => "Thank you for using :company_name service.\r\nWe hope you had a great experience, and we would love to hear your feedback.",
        'message_customer_sms' => "Thank you for using :company_name service. We hope you had a great experience, and we would love to hear your feedback.\r\n:action_url",
        'link_view_customer' => 'Leave feedback',
    ],
    'booking_canceled' => [
        'subject' => 'Booking cancellation :ref_number',
        'message' => 'Booking :ref_number has been cancelled.',
    ],
    'booking_unfinished' => [
        'subject' => 'Driver cancelled :ref_number',
        'message' => 'Driver :driver_name has cancelled the booking :ref_number.',
    ],
    'booking_incomplete' => [
        'subject' => 'New incomplete/unpaid job :ref_number',
        'message' => 'New incomplete/unpaid job :ref_number.',
        'subject_customer' => 'Payment required :ref_number',
        'message_customer' => "Please make the payment to complete your booking :ref_number.",
    ],
    'booking_invoice' => [
        'subject' => 'Invoice :ref_number',
        'message' => 'Please find your invoice :ref_number below.',
    ],
    'greeting' => [
        'general' => 'Sehr geehrte/r Frau/Herr :Name,',
        'default' => 'Hallo!',
        'error' => 'Ups!',
    ],
    'reason' => 'Grund',
    'link_view' => 'Ansicht',
    'salutation' => 'Mit freundlichen Grüßen',
    'sub_copy' => 'Wenn Sie Probleme beim Klicken der Schaltfläche ":name" haben, kopieren Sie die untenstehende URL und fügen Sie sie in Ihren Webbrowser ein:',
    'footer' => [
        'phone' => 'Tel',
        'email' => 'E-Mail',
        'site' => 'Website',
    ],

];
