<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dutch (nl-NL) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Klant',
        'driver' => 'Bestuurder',
        'admin' => 'Beheerder',
    ],
    'user_status_options' => [
        'approved' => 'Goedgekeurd',
        'awaiting_admin_review' => 'In afwachting van beoordeling',
        'awaiting_email_confirmation' => 'In afwachting van e-mailbevestiging',
        'inactive' => 'Inactief',
        'rejected' => 'Verworpen',
    ],
    'user_profile_type_options' => [
        'private' => 'Privé',
        'company' => 'Bedrijf',
    ],
    'vehicle_status_options' => [
        'activated' => 'Actief',
        'inactive' => 'Inactief',
    ],
    'service_status_options' => [
        'activated' => 'Actief',
        'inactive' => 'Inactief',
    ],
    'event_status_options' => [
        'active' => 'Beschikbaar',
        'inactive' => 'Druk',
    ],
    'transaction_status_options' => [
        'pending' => 'In afwachting',
        'paid' => 'Betaald',
        'refunded' => 'Terugbetaald',
        'declined' => 'Afgewezen',
        'canceled' => 'Geannuleerd',
        'authorised' => 'Geautoriseerd',
    ],
    'booking_status_options' => [
        'pending' => 'Bevestigd',
        'confirmed' => 'Bevestigd',
        'assigned' => 'Toegewezen',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Aangenomen',
        'rejected' => 'Geweigerd',
        'onroute' => 'Naar locatie',
        'arrived' => 'Op locatie',
        'onboard' => 'Aan boord',
        'completed' => 'Voltooid',
        'canceled' => 'Geannuleerd',
        'unfinished' => 'Bestuurder geannuleerd',
        'incomplete' => 'Incompleet',
        'requested' => 'In afwachting',
        'quote' => 'Aanvraag',
    ],
    'loading' => 'Bezig met laden...',
    'loading_warning' => 'Het laden duurt iets te lang, wacht een paar seconden en als er niets gebeurt, probeer dan de pagina opnieuw te laden.',
    'loading_reload' => 'NU HERLADEN',
    'powered_by' => 'Mogelijk gemaakt door',

];
