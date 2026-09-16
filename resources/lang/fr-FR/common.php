<?php

return [

    /*
    |--------------------------------------------------------------------------
    | French (fr-FR) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Client',
        'driver' => 'Conducteur',
        'admin' => 'Administrateur',
    ],
    'user_status_options' => [
        'approved' => 'Approuvé',
        'awaiting_admin_review' => 'Examen en suspens',
        'awaiting_email_confirmation' => "Confirmation de l'e-mail en attente",
        'inactive' => 'Inactif',
        'rejected' => 'Rejeté',
    ],
    'user_profile_type_options' => [
        'private' => 'Privé',
        'company' => 'Société',
    ],
    'vehicle_status_options' => [
        'activated' => 'Activé',
        'inactive' => 'Inactif',
    ],
    'service_status_options' => [
        'activated' => 'Activé',
        'inactive' => 'Inactif',
    ],
    'event_status_options' => [
        'active' => 'Disponible',
        'inactive' => 'Occupé',
    ],
    'transaction_status_options' => [
        'pending' => 'En cours',
        'paid' => 'Payé',
        'refunded' => 'Remboursé',
        'declined' => 'Diminué',
        'canceled' => 'Annulé',
        'authorised' => 'Autorisé',
    ],
    'booking_status_options' => [
        'pending' => 'Confirmé', // En cours
        'confirmed' => 'Confirmé',
        'assigned' => 'Assignés',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Emploi accepté',
        'rejected' => 'Travail rejeté',
        'onroute' => 'Sur la route',
        'arrived' => 'Arrivée',
        'onboard' => 'À bord',
        'completed' => 'Terminé',
        'canceled' => 'Annulé',
        'incomplete' => 'Incomplet',
        'requested' => 'Demandé',
        'quote' => 'Citation',
    ],
    'loading' => 'Chargement...',
    'loading_warning' => "Le chargement prend un peu trop de temps, s'il vous plaît attendez quelques secondes de plus. Si rien ne se passe alors s'il vous plaît essayer de recharger la page.",
    'loading_reload' => 'Recharger maintenant',
    'powered_by' => 'Alimenté par',

];
