<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polish (pl-PL) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Klient',
        'driver' => 'Kierowca',
        'admin' => 'Admin',
    ],
    'user_status_options' => [
        'approved' => 'Potwierdzone',
        'awaiting_admin_review' => 'Czeka na modyfikację',
        'awaiting_email_confirmation' => 'Czeka na potwierdzenie adresu E-mail',
        'inactive' => 'Niekatywny',
        'rejected' => 'Odrzucony',
    ],
    'user_profile_type_options' => [
        'private' => 'Prywatne',
        'company' => 'Firma',
    ],
    'vehicle_status_options' => [
        'activated' => 'Aktywny',
        'inactive' => 'Niekatywny',
    ],
    'service_status_options' => [
        'activated' => 'Aktywny',
        'inactive' => 'Niekatywny',
    ],
    'event_status_options' => [
        'active' => 'Dostępny',
        'inactive' => 'Zajęty',
    ],
    'transaction_status_options' => [
        'pending' => 'Oczekujący',
        'paid' => 'Zapłacony',
        'refunded' => 'Zwrot kosztów',
        'declined' => 'Odrzucony',
        'canceled' => 'Anulowany',
        'authorised' => 'Autoryzowany',
    ],
    'booking_status_options' => [
        'pending' => 'Potwierdzony', // Oczekujący
        'confirmed' => 'Potwierdzony',
        'assigned' => 'Przypisany',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Zlecenie przyjęte',
        'rejected' => 'Zlecenie odrzucone',
        'onroute' => 'W drodze',
        'arrived' => 'Na miejscu',
        'onboard' => 'Na pokładzie',
        'completed' => 'Zakończone',
        'canceled' => 'Anulowane',
        'incomplete' => 'Niedokończone',
        'requested' => 'Niepotwierdzone',
        'quote' => 'Prośba o wycenę',
    ],
    'loading' => 'Wczytywanie...',
    'loading_warning' => 'Jeśli wczytywanie trwa za długo, poczekaj jeszcze kilka sekund, a jesli nic się nie stanie, odśwież stronę.',
    'loading_reload' => 'ODŚWIEŻ STRONĘ',
    'powered_by' => 'Obsługiwane przez',

];
