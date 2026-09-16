<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dutch (nl-NL) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Boekingsdetails :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Hieronder vindt u uw boekingsgegevens.',
        'note_line1' => 'Houd altijd ons telefoonnummer bij de hand. U moet ons onmiddellijk bellen als u problemen ondervindt bij het vinden van een bestuurder. Als uw reis buiten kantooruren plaatsvindt, ontvangt u het mobiele telefoonnummer van de bestuurder.',
        'note_line2' => 'Boekingen die rechtstreeks met de chauffeur zijn gemaakt zijn niet wettig. In het geval van een ongeluk wordt u niet gedekt door de verzekering.',
        'note_line3' => 'Voor ophalingen aan de luchthaven wacht de chauffeur in de aankomstterminal (ontmoetingspunt) met een bord met uw naam (schakel uw mobiele telefoon in zodra u bent geland). Wanneer de ophaling op locatie gebeurt, wacht de chauffeur voor de deur. Als er parkeerbeperkingen zijn, zal hij in de nabije omgeving van de ophaallocatie wachten (de dichtstbijzijnde plek).',
        'section' => [
            'journey_details' => 'Reisgegevens',
            'customer_details' => 'Klantengegevens',
            'lead_passenger_details' => 'Details hoofdpassagier',
            'reservation_details' => 'Reserveringsgegevens',
            'general_details' => 'Algemene boekingsdetails',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Statusupdate :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Uw boeking :ref_number status werd gewijzigd naar :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Details bestuurder :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Hieronder vindt u informatie over uw bestuurder.',
        'booking' => 'Boeking',
        'driver' => 'Bestuurder',
        'vehicle' => 'Voertuig',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Status bestuurder :ref_nummer',
        'greeting' => 'Beste :Name',
        'line1' => 'Uw bestuurder is op weg.',
        'booking' => 'Boeking',
        'driver' => 'Bestuurder',
        'vehicle' => 'Voertuig',
    ],
    'customer_booking_completed' => [
        'subject' => 'Feedback :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Bedankt om :company_name te gebruiken om uw reis te voltooien.',
        'line2' => 'We horen graag uw feedback met betrekking tot boeking :ref_number die u recent bij ons maakte.',
        'line3' => 'Om feedback achter te laten, gelieve te klikken op deze :link.',
        'link' => 'link',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Offerte-aanvraag',
        'greeting' => 'Beste :Name',
        'line1' => 'Bedankt voor het aanvragen van een offerte. We nemen binnenkort contact met u op.',
        'line2' => 'In de tussentijd vindt u hier een samenvatting van de gegevens die u voor de offerte hebt opgegeven.',
        'section' => [
            'journey_details' => 'Reisgegevens',
            'customer_details' => 'Klantengegevens',
            'lead_passenger_details' => 'Details hoofdpassagier',
            'general_details' => 'Algemene details',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Betalingsverzoek :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Om uw boeking te bevestigen, gelieve het bedrag van :price te betalen.',
        'line2' => 'U kunt betalen door te klikken op deze :link.',
        'link' => 'link',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Betalingsbevestiging :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'U kunt de factuur hieronder in bijlage terugvinden.',
        'line2' => 'Bedankt voor uw boeking bij :company_name',
        'line3' => 'Betaling voor boeking :ref_number werd :status.',
        'status' => 'GEACCEPTEERD EN BEVESTIGD',
    ],
    'customer_account_activation' => [
        'subject' => 'Activatie',
        'greeting' => 'Beste :Name',
        'line1' => 'Hier is uw activatielink.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Welkom',
        'greeting' => 'Beste :Name',
        'line1' => 'Uw account werd succesvol geactiveerd. U kunt nu inloggen!',
    ],
    'customer_account_password' => [
        'subject' => 'Wachtwoord opnieuw instellen',
        'greeting' => 'Beste :Name',
        'line1' => 'Hier is uw token om uw wachtwoord te resetten: :token',
    ],
    'driver_booking_changed' => [
        'subject' => 'Statusupdate opdracht :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'De status van opdracht :ref_number is gewijzigd naar :status.',
    ],
    'admin_booking_canceled' => [
        'subject' => 'Annulering boeking :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Klant :customer_name heeft de boeking :ref_number geannuleerd op :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Statusupdate opdracht :ref_number',
        'greeting' => 'Beste :Name',
        'line1' => 'Status opdracht :ref_number gewijzigd van :old_status naar :new_status',
    ],
    'header' => [
        'phone' => 'Tel.',
    ],
    'footer' => [
        'phone' => 'Tel.',
        'email' => 'E-mail',
        'site' => 'Website',
        'feedback' => 'U kunt uw feedback :link achterlaten',
        'feedback_link' => 'hier',
    ],
    'powered_by' => 'Mogelijk gemaakt door',

];
