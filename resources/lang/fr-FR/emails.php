<?php

return [

    /*
    |--------------------------------------------------------------------------
    | French (fr-FR) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Détails de la réservation :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Gardez toujours notre numéro de téléphone prêt de vous. Vous devez nous appeler immédiatement si vous avez des difficultés à localiser votre chauffeur. Si votre voyage se déroule en dehors de nos heures de bureau, vous recevrez le numéro de téléphone mobi.',
        'note_line2' => "Les réservations faites directement avec le conducteur sont illégales et en cas d'accident vous ne serez pas couvert par l'assurance.",
        'note_line3' => "Pour les camionnettes d'aéroport, le conducteur attendra à la fin du terminal (point de rencontre) avec un signe avec votre nom (s'il vous plaît allumer votre mobile une fois débarqué). Lorsque le pick-up est à partir d'une adresse, le conducteur sera en.",
        'section' => [
            'journey_details' => 'Détails du voyage',
            'customer_details' => 'Détails du client',
            'lead_passenger_details' => 'Détails du passager principal',
            'reservation_details' => 'Détails de la réservation',
            'general_details' => 'Détails de la réservation générale',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Mise à jour du statut :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Votre réservation statu :ref_number a changé à :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Détails du pilote :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => "S'il vous plaît trouver les détails de votre pilote ci-dessous.",
        'booking' => 'Réservation',
        'driver' => 'Pilote',
        'vehicle' => 'Véhicule',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Statut du pilote :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Votre chauffeur est en route.',
        'booking' => 'Réservation',
        'driver' => 'Pilote',
        'vehicle' => 'Véhicule',
    ],
    'customer_booking_completed' => [
        'subject' => 'Feedback :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => "Merci d'utiliser :company_name pour compléter votre voyage.",
        'line2' => "Nous serions ravis d'entendre vos commentaires en ce qui concerne la réservation :ref_number que vous avez récemment fait avec nous.",
        'line3' => "Pour laisser vos commentaires, s'il vous plaît cliquez sur ce :link.",
        'link' => 'lien',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Demande de devis',
        'greeting' => 'Cher(e) :Name',
        'line1' => "Merci d'avoir demandé un devis. Nous vous contacterons prochainement.",
        'line2' => "Dans l'intervalle, voici un résumé des détails que vous avez soumis pour la cotation.",
        'section' => [
            'journey_details' => 'Détails du voyage',
            'customer_details' => 'Détails du client',
            'lead_passenger_details' => 'Détails du passager principal',
            'general_details' => 'Détails généraux',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Demande de paiement :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => "Afin de sécuriser votre voyage s'il vous plaît faire un paiement de :price.",
        'line2' => 'Vous pouvez effectuer un paiement en cliquant sur le :link ci-dessous.',
        'link' => 'lien',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Confirmation de paiement :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Veuillez trouver ci-joint la facture ci-dessous.',
        'line2' => 'Merci de votre réservation avec :company_name.',
        'line3' => 'Paiement pour la réservation :ref_number a été :status.',
        'status' => 'Acceptés et confirmés',
    ],
    'customer_account_activation' => [
        'subject' => 'Activation',
        'greeting' => 'Cher(e) :Name',
        'line1' => "Voici votre lien d'activation.",
    ],
    'customer_account_welcome' => [
        'subject' => 'Bienvenue',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Votre compte a été activé avec succès. Vous pouvez vous connecter maintenant!',
    ],
    'customer_account_password' => [
        'subject' => 'Réinitialiser le mot de passe',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Voici votre jeton pour la réinitialisation du mot de passe: :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => "Mise à jour de l'état du travail :ref_number",
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Job :ref_number statut a changé en :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Annulation de réservation :ref_number',
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Client :customer_name a annulé la réservation :ref_number le :date.',
    ],
    'admin_booking_changed' => [
        'subject' => "Mise à jour de l'état du travail :ref_number",
        'greeting' => 'Cher(e) :Name',
        'line1' => 'Job statut :ref_number changé de :old_status à :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tél',
    ],
    'footer' => [
        'phone' => 'Tél',
        'email' => 'Messagerie',
        'site' => 'Site internet',
        'feedback' => 'Vous pouvez laisser vos commentaires :link',
        'feedback_link' => 'ici',
    ],
    'powered_by' => 'Alimenté par',

];
