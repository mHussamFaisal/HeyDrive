<?php

return [
    'booking_pending' => [
        'subject' => 'Uusi varaus :ref_number',
        'message' => 'Uusi varaus :ref_number on luotu.',
        'subject_customer' => ':company_name varausvahvistus :ref_number',
        'message_customer' => '<span style="color:#000; font-weight:bold;"><span style="color:#008000;">Varausvahvistus:</span> Kiitos varauksestasi :company_name kanssa. Varausnumerosi on <span style="color:#CE0000;">:ref_number</span>.</span>',
        'message_customer_sms' => 'TAKSISI :booking_from ON VARATTU :booking_date. KIITOS KUN VARASIT MEILTÄ :company_name.',
        'message_customer_sms_full' => 'Varaus :ref_number on vahvistettu.',
        'info' => '<span style="color:#000; font-weight:bold;">Tärkeää tietoa varaukseesi liittyen:</span>
- Mikäli haluat peruuttaa, tehdä muutoksia tai maksaa pankki/luottokortilla, ole meihin yhteydessä.
- Suoraan kuljettajalle tehdyt varaukset ovat laittomia, ja tapaturman sattuessa vakuutus ei kata sinua.',
    ],
    'booking_quote' => [
        'subject' => 'Uusi tilaus :ref_number tarjouspyyntö',
        'message' => 'Uusi tilaus :ref_number tarjouspyyntö odottaa tarkistusta.',
        'subject_customer' => ':company_name tilaus :ref_number tarjouspyyntö',
        'message_customer' => 'Kiitos tilauksestasi :ref_number tarjouspyyntö. Kiitämme kärsivällisyydestäsi, ja olemme sinuun pian yhteydessä.',
    ],
    'booking_requested' => [
        'subject' => 'Uusi tilaus :ref_number pyyntö',
        'message' => 'Uusi tilaus :ref_number pyyntö on tehty ja odottaa vahvistusta.',
        'subject_customer' => ':company_name tilaus :ref_number pyyntö',
        'message_customer' => '<span style="color:#000; font-weight:bold;">Varauksesi <span style="color:blue;">odottaa vahvistusta</span>. Kiitos kun teit varauksen :company_name kanssa. Varausnumerosi on <span style="color:#CE0000;">:ref_number</span>.</span>
<span style="color:#000; font-weight:bold;">Vahvistusviesti lähetetään sähköpostiisi :request_time tunnin sisällä.',
    ],
    'booking_confirmed' => [
        'subject' => ':company_name varausvahvistus :ref_number',
        'message' => 'Varauksesi :ref_number on vahvistettu',
        'message_customer' => '<span style="color:#000; font-weight:bold;"><span style="color:#008000;">Varausvahvistus:</span> Kiitos varauksestasi :company_name kanssa. Varausnumerosi on <span style="color:#CE0000;">:ref_number</span>.</span>',
    ],
    'booking_assigned' => [
        'subject' => 'Uusi työ :ref_number osoitettu.',
        'message' => 'Sinulle on osoitettu uusi työ :ref_number.',
        'subject_customer' => 'Kuljettajan tiedot. Varaus :ref_number',
        'message_customer' => 'Löydät kuljettajan tiedot alta.',
        'message_customer_sms' => 'Varaus :ref_number. Kuljettajasi :driver_name, :driver_mobile_no ajoneuvolla: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark on osoitettu varaukseesi.',
        'footer_customer_email' => '',
    ],
    'booking_auto_dispatch' => [
        'subject' => 'New job :ref_number assigned',
        'message' => 'You have been assigned a new job :ref_number.',
        'subject_customer' => 'Driver details. Booking :ref_number',
        'message_customer' => 'Please find your driver details below.',
        'message_customer_sms' => 'Booking :ref_number. Your driver :driver_name, :driver_mobile_no with vehicle: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark has been assigned to your booking.',
        'footer_customer_email' => '',
    ],
    'booking_accepted' => [
        'subject' => 'Työ vastaanotettu  :ref_number',
        'message' => 'Työ :ref_number. Kuljettaja :driver_name on ottanut työn vastaan.',
        'subject_customer' => 'Kuljettajan tiedot. Varaus :ref_number',
        'message_customer' => 'Löydät kuljettajan tiedot alta.',
        'message_customer_sms' => 'Varaus :ref_number. Kuljettajasi :driver_name, :driver_mobile_no ajoneuvolla: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark on osoitettu varaukseesi.',
        'footer_customer_email' => '',
    ],
    'booking_rejected' => [
        'subject' => 'Työ hylätty :ref_number',
        'message' => 'Työ :ref_number. Kuljettaja :driver_name on hylännyt työn.',
    ],
    'booking_onroute' => [
        'subject' => 'Kuljettaja matkalla :ref_number',
        'message' => 'Työ :ref_number. Kuljettaja :driver_name on matkalla.',
        'subject_customer' => 'Kuljettaja on matkalla Varaus :ref_number',
        'message_customer' => 'Kuljettaja on matkalla.',
        'message_customer_sms' => 'Varaus :ref_number. Kuljettajasi :driver_name, :driver_mobile_no ajoneuvolla: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark on matkalla osoitteeseen :booking_from.',
        'footer_customer_email' => '',
    ],
    'booking_arrived' => [
        'subject' => 'Kuljettaja on saapunut :ref_number',
        'message' => 'Työ :ref_number. Kuljettaja :driver_name on saapunut aloitusosoitteeseen.',
        'subject_customer' => 'Kuljettaja on saapunut :ref_number',
        'message_customer' => 'Kuljettaja :driver_name on saapunut. Varaus :ref_number.',
        'message_customer_sms' => 'Varaus :ref_number. Kuljettajasi :driver_name on saapunut. Ajoneuvo: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
        'footer_customer_email' => '',
    ],
    'booking_onboard' => [
        'subject' => 'Tilaus aloitettu :ref_number',
        'message' => 'Työ :ref_number. Tilaus on aloitettu.',
    ],
    'booking_completed' => [
        'subject' => 'Työ suoritettu :ref_number',
        'message' => 'Työ :ref_number on suoritettu.',
        'subject_customer' => 'Palaute :ref_number',
        'message_customer' => 'Kiitos että käytit :company_name. Toivomme että kokemuksesi oli hyvä ja kuulisimme mielellämme palautteesi.',
        'message_customer_sms' => 'Kiitos että käytit :company_name. Toivomme että kokemuksesi oli hyvä ja kuulisimme mielellämme palautteesi. :action_url',
        'link_view_customer' => 'Jätä palautetta',
    ],
    'booking_canceled' => [
        'subject' => 'Varauksen peruutus :ref_number',
        'message' => 'Varaus :ref_number on peruttu.',
    ],
    'booking_unfinished' => [
        'subject' => 'Kuljettaja perui :ref_number',
        'message' => 'Kuljettaja :driver_name on perunut tilauksen :ref_number',
    ],
    'booking_incomplete' => [
        'subject' => 'Uusi keskeneräinen/maksamaton työ :ref_number',
        'message' => 'Uusi keskeneräinen/maksamaton työ :ref_number.',
        'subject_customer' => 'Maksua vaaditaan :ref_number',
        'message_customer' => 'Suorita maksu suorittaaksesi varauksen :ref_number loppuun.',
    ],
    'booking_invoice' => [
        'subject' => 'Lasku :ref_number',
        'message' => 'Löydät laskusi :ref_number alta.',
    ],
    'greeting' => [
        'general' => 'Hei :Name,',
        'default' => 'Hei!',
        'error' => 'Oho!',
    ],
    'reason' => 'Syy',
    'link_view' => 'Katso',
    'salutation' => 'Terveisin',
    'sub_copy' => 'Mikäli sinulla on vaikeuksia ":name" painikkeen klikkaamisessa, kopioi ja liitä alla oleva URL -osoite selaimeesi:',
    'footer' => [
        'phone' => 'Puh.',
        'email' => 'Sähköposti',
        'site' => 'Verkkosivu',
    ],
    'test_email_msg' => 'Tämä on vain testiviesti! Kaikki asiakas- ja kuljettajaviestit on reititetty järjestelmänvalvojan postilaatikkoon testaustarkoitusta varten. Kun testaus on valmis, voit poistaa sähköpostin testausasetuksen käytöstä Asetukset -> Ilmoitukset -välilehdessä.',
    'test_phone_msg' => 'TESTI!',
    'status' => 'Status',
    'role' => 'Role',
    'notifications' => 'Notifications',
];
