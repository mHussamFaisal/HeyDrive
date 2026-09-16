<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dutch (nl-NL) - Notifications
    |--------------------------------------------------------------------------
    */

    'booking_pending' => [
        'subject' => 'Nieuwe boeking :ref_number',
        'message' => 'Nieuwe boeking :ref_number werd aangemaakt.',
        'subject_customer' => ':company_name boekingbevestiging :ref_number',
        'message_customer' => '<span style="color:#000; font-weight:bold;"><span style="color:#008000;">Boekingbevestiging:</span> bedankt voor uw boeking bij :company_name. Uw boekingnummer is <span style="color:#CE0000;">:ref_number</span>.</span>',
        'message_customer_sms' => 'UW TAXI VAN :booking_from IS GEBOEKT OP :booking_date. BEDANKT DAT U GEKOZEN HEEFT VOOR :company_name.',
        'message_customer_sms_full' => 'Boeking :ref_number werd bevestigd.',
        'info' => "<span style=\"color:#000; font-weight:bold;\">Belangrijke informatie over uw boeking:</span>\r\n".
                  "- Als u wilt annuleren, wijzigingen wilt aanbrengen of wilt betalen met uw debet-/kredietkaart, neem dan contact met ons op.\r\n".
                  "- Rechtstreeks boeken met de bestuurder is onwettig en in geval van een ongeluk wordt u niet gedekt door de verzekering.\r\n".
                  "- Voor ophalingen aan de luchthaven wacht de chauffeur in de aankomstterminal (ontmoetingspunt) met een bord met uw naam (schakel uw mobiele telefoon in zodra u bent geland).\r\n".
                  "- Voor ophalingen op locatie wacht de chauffeur voor de deur. Als er parkeerbeperkingen zijn, zal hij in de omgeving van de ophaallocatie wachten (de dichtstbijzijnde plek).",
    ],
    'booking_quote' => [
        'subject' => 'Nieuwe boeking :ref_number offerteaanvraag',
        'message' => 'Nieuwe boeking :ref_number offerteaanvraag wacht op beoordeling.',
        'subject_customer' => ':company_name boeking :ref_number offerteaanvraag',
        'message_customer' => "Bedankt voor uw boeking :ref_number offerteaanvraag.\r\nWe bedanken u voor uw geduld en nemen spoedig contact met u op.",
    ],
    'booking_requested' => [
        'subject' => 'Nieuwe boeking :ref_number aanvraag',
        'message' => 'Nieuwe boeking :ref_number aanvraag werd ingediend en wacht op bevestiging.',
        'subject_customer' => ':company_name boeking :ref_number aanvraag',
        'message_customer' => '<span style="color:#000; font-weight:bold;">Uw boeking<span style="color:blue;">wacht op bevestiging</span>. Bedankt voor uw boeking bij :company_name. Uw reservatienummer is <span style="color:#CE0000;">:ref_number</span>.</span>'.
                              "\r\n\r\n".
                              '<span style="color:#000; font-weight:bold;">Een bevestigingsmail wordt naar uw e-mailadres verzonden in :request_time uur.',
    ],
    'booking_confirmed' => [
        'subject' => ':company_name boekingbevestiging :ref_number',
        'message' => 'Uw boeking :ref_number werd bevestigd.',
        'message_customer' => '<span style="color:#000; font-weight:bold;"><span style="color:#008000;">Boekingbevestiging:</span> bedankt voor uw boeking bij :company_name. Uw reservatienummer is <span style="color:#CE0000;">:ref_number</span>.</span>',
    ],
    'booking_assigned' => [
        'subject' => 'Nieuwe opdracht :ref_number toegewezen',
        'message' => 'Er werd een nieuwe opdracht :ref_number aan u toegewezen.',
        'subject_customer' => 'Bestuurdergegevens. Boeking :ref_number',
        'message_customer' => 'U kunt de gegevens van de bestuurder hieronder terugvinden.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
    ],
    'booking_auto_dispatch' => [
        'subject' => 'Nieuwe opdracht :ref_number toegewezen',
        'message' => 'Er werd een nieuwe opdracht :ref_number aan u toegewezen.',
        'subject_customer' => 'Bestuurdergegevens. Boeking :ref_number',
        'message_customer' => 'U kunt de gegevens van de bestuurder hieronder terugvinden.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
    ],
    'booking_accepted' => [
        'subject' => 'Opdracht aanvaard :ref_number',
        'message' => 'Opdracht :ref_number. Bestuurder :driver_name heeft de opdracht aanvaard.',
        'subject_customer' => 'Bestuurdergegevens. Boeking :ref_number',
        'message_customer' => 'U kunt de gegevens van de bestuurder hieronder terugvinden.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
    ],
    'booking_rejected' => [
        'subject' => 'UW TAXI WERD UITGESTUURD NAAR :booking_from, :booking_date. BEDANKT DAT U GEKOZEN HEEFT VOOR :company_name.',
        'message' => 'Opdracht :ref_number. Bestuurder :driver_name heeft de opdracht geweigerd.',
    ],
    'booking_onroute' => [
        'subject' => 'Bestuurder op weg :ref_number',
        'message' => 'Opdracht :ref_number. Bestuurder :driver_name is op weg.',
        'subject_customer' => 'Bestuurder is op weg. Boeking :ref_number',
        'message_customer' => 'Bestuurder is op weg.',
        'message_customer_sms' => 'UW TAXI WERD UITGESTUURD NAAR :booking_from, :booking_date. BEDANKT DAT U GEKOZEN HEEFT VOOR :company_name.',
    ],
    'booking_arrived' => [
        'subject' => 'Bestuurder op locatie :ref_number',
        'message' => 'Opdracht :ref_number. Bestuurder :driver_name is op locatie op ophaallocatie en wacht op de klant.',
        'subject_customer' => 'Bestuurder op locatie :ref_number',
        'message_customer' => 'Bestuurder :driver_name is op locatie en wacht op de afgesproken ophaallocatie. Boeking :ref_number',
        'message_customer_sms' => 'UW BESTUURDER :driver_name IS TOEGEKOMEN IN :vehicle_details. EEN VEILIGE REIS GEWENST. BEDANKT DAT U GEKOZEN HEEFT VOOR :company_name.',
    ],
    'booking_onboard' => [
        'subject' => 'Klant aan boord :ref_number',
        'message' => 'Opdracht :ref_number. Klant is aan boord.',
    ],
    'booking_completed' => [
        'subject' => 'Opdracht voltooid :ref_number',
        'message' => 'Opdracht :ref_number werd voltooid.',
        'subject_customer' => 'Feedback :ref_number',
        'message_customer' => "Bedankt om te kiezen voor :company_name.\r\nWe hopen dat u een aangename ervaring heeft gehad en zouden graag uw feedback horen.",
        'message_customer_sms' => "Bedankt om te kiezen voor :company_name.\r\nWe hopen dat u een aangename ervaring heeft gehad en zouden graag uw feedback horen.\r\n:action_url",
        'link_view_customer' => 'Feedback geven',
    ],
    'booking_canceled' => [
        'subject' => 'Annulatie boeking :ref_number',
        'message' => 'Boeking :ref_number werd geannuleerd.',
    ],
    'booking_unfinished' => [
        'subject' => 'Annulatie bestuurder :ref_number',
        'message' => 'Bestuurder :driver_name heeft de boeking :ref_number geannuleerd',
    ],
    'booking_incomplete' => [
        'subject' => 'Nieuwe onvoltooide/onbetaalde opdracht :ref_number',
        'message' => 'Nieuwe onvoltooide/onbetaalde opdracht :ref_number.',
        'subject_customer' => 'Betaling vereist :ref_number',
        'message_customer' => 'Gelieve de betaling te doen om uw boeking :ref_number te voltooien.',
    ],
    'booking_invoice' => [
        'subject' => 'Factuur :ref_number',
        'message' => 'Hieronder kunt u uw factuur :ref_number terugvinden.',
    ],
    'greeting' => [
        'general' => 'Beste :Name',
        'default' => 'Hallo!',
        'error' => 'Woeps!',
    ],
    'reason' => 'Reden',
    'link_view' => 'Bekijken',
    'salutation' => 'Groeten',
    'sub_copy' => 'Als u problemen ondervindt bij het klikken op de knop \':name\', kopieer en plak dan de onderstaande URL in uw webbrowser:',
    'footer' => [
        'phone' => 'Tel.',
        'email' => 'E-mail',
        'site' => 'Website',
    ],

];
