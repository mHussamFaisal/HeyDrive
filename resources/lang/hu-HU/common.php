<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hungarian (hu-HU) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Utas',
        'driver' => 'Sofőr',
        'admin' => 'Adminisztrátor',
    ],
    'user_status_options' => [
        'approved' => 'Jóváhagyva',
        'awaiting_admin_review' => 'Jóváhagyásra vár',
        'awaiting_email_confirmation' => 'E-mail visszaigazolásra vár',
        'inactive' => 'Nem aktív',
        'rejected' => 'Visszautasítva',
    ],
    'user_profile_type_options' => [
        'private' => 'Privát',
        'company' => 'Cég',
    ],
    'vehicle_status_options' => [
        'activated' => 'Aktivált',
        'inactive' => 'Nem aktív',
    ],
    'service_status_options' => [
        'activated' => 'Aktivált',
        'inactive' => 'Nem aktív',
    ],
    'event_status_options' => [
        'active' => 'Szabad',
        'inactive' => 'Foglalt',
    ],
    'transaction_status_options' => [
        'pending' => 'Fizetendő',
        'paid' => 'Fizetett',
        'refunded' => 'Visszafizetett',
        'declined' => 'Visszautasított',
        'canceled' => 'Törölt',
        'authorised' => 'Jóváhagyott',
    ],
    'booking_status_options' => [
        'pending' => 'Visszaigazolt', // Függőben lévő
        'confirmed' => 'Visszaigazolt',
        'assigned' => 'Kiosztott',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Fuvar elfogadva',
        'rejected' => 'Fuvar visszautasítva',
        'onroute' => 'Útban',
        'arrived' => 'Megérkezett',
        'onboard' => 'Az autóban',
        'completed' => 'Befejezve',
        'canceled' => 'Törölt',
        'incomplete' => 'Hiányos',
        'requested' => 'Szükséges',
        'quote' => 'Árajánlat',
    ],
    'loading' => 'Betölt...',
    'loading_warning' => 'A betöltés kicsit sokáig tart. Kérjük várjon pár percet és ha semmi nem történik, próbálja újra.',
    'loading_reload' => 'Újra betöltés',
    'powered_by' => 'Powered by',

];
