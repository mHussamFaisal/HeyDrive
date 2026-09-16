<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Czech (cs-CZ) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Zákazník',
        'driver' => 'Řidič',
        'admin' => 'Správce',
    ],
    'user_status_options' => [
        'approved' => 'Schválený',
        'awaiting_admin_review' => 'Hodnocení se posílá ',
        'awaiting_email_confirmation' => 'Čeká se na potvrzení e-mailu',
        'inactive' => 'Neaktivní',
        'rejected' => 'Odmítnuto',
    ],
    'user_profile_type_options' => [
        'private' => 'Soukromé',
        'company' => 'Společnost',
    ],
    'vehicle_status_options' => [
        'activated' => 'Aktivováno',
        'inactive' => 'Neaktivní',
    ],
    'service_status_options' => [
        'activated' => 'Aktivováno',
        'inactive' => 'Neaktivní',
    ],
    'event_status_options' => [
        'active' => 'Dostupný',
        'inactive' => 'Zaneprázdněný',
    ],
    'transaction_status_options' => [
        'pending' => 'Čekající',
        'paid' => 'Zaplaceno',
        'refunded' => 'Vráceno',
        'declined' => 'Odmítnuto',
        'canceled' => 'Zrušeno',
        'authorised' => 'Autorizováno',
    ],
    'booking_status_options' => [
        'pending' => 'Potvrzeno',
        'confirmed' => 'Potvrzeno',
        'assigned' => 'Přiřazeno',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Zakázka přijata',
        'rejected' => 'Zakázka odmítnuta',
        'onroute' => 'Na cestě',
        'arrived' => 'Na místě',
        'onboard' => 'Obsadil',
        'completed' => 'Dokončeno',
        'canceled' => 'Zrušeno',
        'unfinished' => 'Řidič zrušen',
        'incomplete' => 'Neúplný',
        'requested' => 'Nepotvrzený',
        'quote' => 'Žádost o nabídku',
    ],
    'loading' => 'Načítání...',
    'loading_warning' => 'Nahrávání trvá příliš dlouho, počkejte ještě několik vteřin a pokud se nic nestane, zkuste stránku načíst znovu.',
    'loading_reload' => 'OBNOVIT NYNÍ',
    'powered_by' => 'Běží na',

];
