<?php

return [

    /*
    |--------------------------------------------------------------------------
    | German (de-DE) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Buchungsdetails :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Nachfolgend finden Sie Ihre Buchungsdetails.',
        'note_line1' => 'Halten Sie unsere Telefonnummer immer griffbereit. Sie müssen uns sofort anrufen, wenn Sie Schwierigkeiten haben, Ihren Fahrer zu finden. Wenn Ihre Reise außerhalb unserer Bürozeiten stattfindet, erhalten Sie die mobile Kontaktnummer des Fahrers.',
        'note_line2' => 'Buchungen, die direkt beim Fahrer vorgenommen werden, sind illegal und im Falle eines Unfalls werden Sie nicht von der Versicherung gedeckt.',
        'note_line3' => 'Bei Abholungen vom Flughafen wartet der Fahrer am Ende des Terminals (Treffpunkt) mit einem Schild mit Ihrem Namen (bitte schalten Sie Ihr Handy nach der Landung ein). Wenn die Abholung von einer Adresse erfolgt, wartet der Fahrer vor der Tür. Wenn es Parkplatzbeschränkungen gibt, wird er in der Nähe der Abholstelle (der nächstgelegenen Stelle) warten.',
        'section' => [
            'journey_details' => 'Reisedetails',
            'customer_details' => 'Kundendetails',
            'lead_passenger_details' => 'Hauptpassagierdetails',
            'reservation_details' => 'Reservierungsdetails',
            'general_details' => 'Allgemeine Buchungsdetails',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Statusaktualisierung :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Der Status ihrer Buchung :ref_number hat sich zu :status geändert.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Fahrerdetails :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Details zu Ihrem Fahrer finden Sie untenstehend.',
        'booking' => 'Buchen',
        'driver' => 'Fahrer',
        'vehicle' => 'Fahrzeug',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Fahrerstatus :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Ihr Fahrer ist auf dem Weg.',
        'booking' => 'Buchen',
        'driver' => 'Fahrer',
        'vehicle' => 'Fahrzeug',
    ],
    'customer_booking_completed' => [
        'subject' => 'Feedback :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Vielen Dank, dass Sie :company_name verwendet haben, um Ihre Reise abzuschließen.',
        'line2' => 'Wir würden uns freuen, wenn Sie uns Ihr Feedback bezüglich der Buchung :ref_number geben könnten, die Sie kürzlich bei uns vorgenommen haben.',
        'line3' => 'Um Ihr Feedback zu hinterlassen, klicken Sie bitte auf diesen :link.',
        'link' => 'link',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Angebotsanfrage',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Vielen Dank für die Anforderung eines Kostenvoranschlags. Wir werden uns in Kürze mit Ihnen in Verbindung setzen.',
        'line2' => 'In der Zwischenzeit finden Sie hier eine Zusammenfassung der Details, die Sie uns zur Angebotserstellung vorgelegt haben.',
        'section' => [
            'journey_details' => 'Reisedetails',
            'customer_details' => 'Kundendetails',
            'lead_passenger_details' => 'Hauptpassagierdetails',
            'general_details' => 'Allgemeine Details',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Zahlungsaufforderung :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Um Ihre Reise sicherzustellen, bitten wir Sie, eine Zahlung in Höhe von :price zu leisten.',
        'line2' => 'Sie können eine Zahlung vornehmen, indem Sie auf diesen :link klicken.',
        'link' => 'link',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Zahlungsbestätigung :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Die Rechnung finden Sie im Anhang.',
        'line2' => 'Vielen Dank für Ihre Buchung bei :company_name.',
        'line3' => 'Die Zahlung für die Reservierung :ref_number ist :status.',
        'status' => 'AKZEPTIERT UND BESTÄTIGT',
    ],
    'customer_account_activation' => [
        'subject' => 'Aktivierung',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Hier ist Ihr Aktivierungslink.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Willkommen',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Ihr Konto wurde erfolgreich aktiviert. Sie können sich jetzt einloggen!',
    ],
    'customer_account_password' => [
        'subject' => 'Passwort zurücksetzen',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Hier ist Ihr Token für das Zurücksetzen des Passwortes: :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Job-Status-Update :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Der Status des Jobs :ref_number hat sich zu :status geändert.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Stornierung der Reservierung :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Der Kunde :customer_name hat die Buchung :ref_number storniert am :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Job-Status-Update :ref_number',
        'greeting' => 'Sehr geehrte/r Frau/Herr :Name',
        'line1' => 'Der Status von Job :ref_number hat sich von :old_status auf :new_status geändert.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'E-Mail',
        'site' => 'Website',
        'feedback' => 'Sie können uns Ihr Feedback hier :link hinterlassen',
        'feedback_link' => 'hier',
    ],
    'powered_by' => 'Unterstützt von',

];
