<?php

return [

    /*
    |--------------------------------------------------------------------------
    | German (de-DE) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Kunde',
        'driver' => 'Fahrer',
        'admin' => 'Admin',
    ],
    'user_status_options' => [
        'approved' => 'Genehmigt',
        'awaiting_admin_review' => 'Noch ausstehende Überprüfung',
        'awaiting_email_confirmation' => 'Warten auf E-Mail-Bestätigung',
        'inactive' => 'Inaktiv',
        'rejected' => 'Abgelehnt',
    ],
    'user_profile_type_options' => [
        'private' => 'Privat',
        'company' => 'Unternehmen',
    ],
    'vehicle_status_options' => [
        'activated' => 'Aktiviert',
        'inactive' => 'Inaktiv',
    ],
    'service_status_options' => [
        'activated' => 'Aktiviert',
        'inactive' => 'Inaktiv',
    ],
    'event_status_options' => [
        'active' => 'Verfügbar',
        'inactive' => 'Beschäftigt',
    ],
    'transaction_status_options' => [
        'pending' => 'Ausstehend',
        'paid' => 'Bezahlt',
        'refunded' => 'Zurückerstattet',
        'declined' => 'Abgelehnt',
        'canceled' => 'Storniert',
        'authorised' => 'Autorisiert',
    ],
    'booking_status_options' => [
        'pending' => 'Bestätigt', // Ausstehend
        'confirmed' => 'Bestätigt',
        'assigned' => 'Zugewiesen',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Job angenommen',
        'rejected' => 'Job abgelehnt',
        'onroute' => 'Unterwegs',
        'arrived' => 'Angekommen',
        'onboard' => 'An Bord',
        'completed' => 'Abgeschlossen',
        'canceled' => 'Storniert',
        'incomplete' => 'Unvollständig',
        'requested' => 'Angefragt',
        'quote' => 'Kostenvoranschlag',
    ],
    'loading' => 'Laden...',
    'loading_warning' => 'Das Laden dauert etwas zu lange, bitte warten Sie noch ein paar Sekunden und wenn nichts passiert, versuchen Sie bitte, die Seite neu zu laden.',
    'loading_reload' => 'JETZT NEU LADEN',
    'powered_by' => 'Unterstützt von',

];
