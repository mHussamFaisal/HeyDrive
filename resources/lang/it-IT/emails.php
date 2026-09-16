<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Italian (it-IT) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Dettagli della prenotazione :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Visualizza I dettagli della tua prenotazione di seguito.',
        'note_line1' => 'Tieni sempre il nostro numero di telefono a portata di mano. Devi chiamarci immediatamente se hai qualche difficoltà a localizzare il tuo autista. Se il tuo viaggio è in corso fuori dal nostro orario di ufficio, ti verrà fornito il numero di telefono del conducente.',
        'note_line2' => 'Le prenotazioni effettuate direttamente con il conducente sono illegali e in caso di incidente non sarà coperto dall\'assicurazione.',
        'note_line3' => 'Per le partenze aeroportuali, l\'autista aspetterà alla fine del terminale (punto d\'incontro) con un cartello con il tuo nome (si prega di accendere il cellulare una volta il volo sia atterrato).',
        'section' => [
            'journey_details' => 'Dettagli del Viaggio',
            'customer_details' => 'Dettagli Cliente',
            'lead_passenger_details' => 'Dettagli dei Viaggiatori Principali',
            'reservation_details' => 'Dettagli della Prenotazione',
            'general_details' => 'Dettagli Generali della Prenotazione',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Aggionamento di Stato :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Lo stato della tua prenotazione :ref_number è cambiato :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Dettagli del Conducente :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Visualizza I dettagli del conducente di seguito.',
        'booking' => 'Prenotazione',
        'driver' => 'Conducente',
        'vehicle' => 'Veicolo',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Stato del Conducente :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Il tuo autista è in arrivo.',
        'booking' => 'Prenotazione',
        'driver' => 'Conducente',
        'vehicle' => 'Veicolo',
    ],
    'customer_booking_completed' => [
        'subject' => 'Feedback :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Grazie per aver utilizzato :company_name per fare il viaggio.',
        'line2' => 'Saremo lieti di ricevere I tuoi commenti riguardo alla prenotazione :ref_number.',
        'line3' => 'Per lasciare il tuo feedback, per favora clicca su questo :link.',
        'link' => 'link',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Richiesta di Preventivo',
        'greeting' => 'Caro :Name',
        'line1' => 'Grazie per aver richiesto un preventivo. Ti contatteremo a breve.',
        'line2' => 'Nel frattempo, ecco un riepilogo dei dettagli che hai inviato per il preventivo.',
        'section' => [
            'journey_details' => 'Dettagli del Viaggio',
            'customer_details' => 'Dettagli Cliente',
            'lead_passenger_details' => 'Dettagli dei Viaggiatori Principali',
            'general_details' => 'Dettagli Generali',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Richiesta di Pagamento :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Per garantire la disponibilità del viaggio, si prega di effettuare un pagamento di :price.',
        'line2' => 'Puoi effettuare un pagamento cliccando su questo :link.',
        'link' => 'link',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Conferma di Pagamento :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Visualizza la fattura allegata di seguito.',
        'line2' => 'Grazie per aver effettuato la prenotazione con :company_name.',
        'line3' => 'Il pagamento per la prenotazione :ref_number è stato :status.',
        'status' => 'ACCETTATO E CONFERMATO',
    ],
    'customer_account_activation' => [
        'subject' => 'Attivazione',
        'greeting' => 'Caro :Name',
        'line1' => 'Ecco il tuo link di attivazione.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Benvenuto',
        'greeting' => 'Caro :Name',
        'line1' => 'Il tuo account è stato attivato con successo. Puoi accedere ora!',
    ],
    'customer_account_password' => [
        'subject' => 'Ripristinare Password',
        'greeting' => 'Caro :Name',
        'line1' => 'Ecco il gettone per ripristinare la password :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Aggionamento dello stato di lavoro :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Lo stato di lavoro :ref_number è cambiato a :status',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Annullamento di prenotazione :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Cliente :customer_name ha annullato la prenotazione :ref_number il :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Aggionamento dello stato di lavoro :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Lo stato di lavoro :ref_number è cambiato da :old_status a :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'Email',
        'site' => 'Sito Web',
        'feedback' => 'Puoi lasciare il tuo feedback :link',
        'feedback_link' => 'Qui',
    ],
    'powered_by' => 'Offerto da',

];
