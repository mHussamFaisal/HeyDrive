<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Italian (it-IT) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Cliente',
        'driver' => 'Conducente',
        'admin' => 'Amministratore',
    ],
    'user_status_options' => [
        'approved' => 'Approvato',
        'awaiting_admin_review' => 'Revisione in atto',
        'awaiting_email_confirmation' => 'In attesa di e-mail di conferma',
        'inactive' => 'Inattivo',
        'rejected' => 'Rifiutato',
    ],
    'user_profile_type_options' => [
        'private' => 'Privato',
        'company' => 'Azienda',
    ],
    'vehicle_status_options' => [
        'activated' => 'Attivato',
        'inactive' => 'Inattivo',
    ],
    'service_status_options' => [
        'activated' => 'Attivato',
        'inactive' => 'Inattivo',
    ],
    'event_status_options' => [
        'active' => 'Disponibile',
        'inactive' => 'Occupato',
    ],
    'transaction_status_options' => [
        'pending' => 'In attesa',
        'paid' => 'Pagato',
        'refunded' => 'Rimborsato',
        'declined' => 'Rifiutato',
        'canceled' => 'Annullato',
        'authorised' => 'Autorizzato',
    ],
    'booking_status_options' => [
        'pending' => 'Confermato', // In attesa
        'confirmed' => 'Confermato',
        'assigned' => 'Assegnato',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Lavoro Accettato',
        'rejected' => 'Lavoro Rifiutato',
        'onroute' => 'In viaggio',
        'arrived' => 'Arrivato',
        'onboard' => 'A bordo',
        'completed' => 'Completato',
        'canceled' => 'Annullato',
        'incomplete' => 'Incompleto',
        'requested' => 'Richiesto',
        'quote' => 'Citazione',
    ],
    'loading' => 'Caricamento in corso...',
    'loading_warning' => 'Il caricamento ci sta mettendo troppo. Si prega di attendere qualche secondo in più e se non accade niente, si prega di provare a ricaricare la pagina.',
    'loading_reload' => 'RICARICA ORA',
    'powered_by' => 'Offerto da',

];
