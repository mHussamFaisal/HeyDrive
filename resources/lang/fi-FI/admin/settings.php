<?php

return [
    'page_title' => 'Asetukset',
    'charges' => [
        'subtitle' => 'Pysäköintimaksu',
        'type' => 'Veloitus',
        'type_options' => [
            'select' => '-- Valitse --',
            'parking' => 'Pysäköinti',
            'waiting' => 'Odotus',
        ],
        'price' => 'Hinta',
        'name' => 'Nimi',
        'name_enabled' => 'Sisällytä veloitusnimi yhteenvetoon',
        'location_enabled' => 'Rajoita sijaintiin',
        'location_type' => 'Käytä',
        'location_type_options' => [
            'all' => 'Mikä vain',
            'from' => 'Aloitusosoite',
            'to' => 'Kohde',
            'via' => 'Kautta',
        ],
        'vehicle_enabled' => 'Rajoita tilaustyyppiä',
        'vehicle_all' => 'Kaikki',
        'datetime_enabled' => 'Rajoita päivämäärään ja aikaan',
        'datetime_start' => 'Alkaa',
        'datetime_end' => 'Päättyy',
        'status' => 'Aktiivinen',
        'message' => [
            'no_vehicles' => 'Ei tilausta.',
        ],
    ],
    'notifications' => [
        'role' => 'Rooli',
        'status' => 'Tila',
        'notifications' => 'Ilmoitukset',
        'subtitle' => 'Ilmoitukset',
        'subtitle_desc' => 'Ilmoitusjärjestelmässä voit asettaa:
-mikä ilmoitus lähetetään
-kenelle se lähetetään
-ja miten se lähetetään
Jotta saat SMS järjestelmän toimimaan, se täytyy ensin integroida. Mene Asetukset -> Integrointi -> ja asenna Textlocal - SMS järjestelmä
Jotta saat Push-ilmoitukset toimimaan kunnolla, kuljettajiesi tulee ladata, asentaa ja kirjautua kuljettajasovellukseen. Katso ohjeet Kuljettajasovellus -kohdasta.
Jotta Push-ilmoitukset toimivat kunnolla, asiakkaasi tulee ladata, asentaa ja kirjautua asi.',
        'types' => [
            'booking_pending' => [
                'title' => 'Tila - Vahvistettu',
                'desc' => 'Tämä ilmoitus lähetetään kun Uusi Varaus on luotu.
Uuden varauksen voi luoda:
-asiakas nettisivujen Verkkovarauksen kautta
-ylläpitäjä alustan Uusi Varaus -lomakkeen kautta',
            ],
            'booking_quote' => [
                'title' => 'Tila - Pyydä tarjous',
                'desc' => 'Tämä ilmoitus lähetetään kun Uusi Varaus on luotu, mutta hintaa ei ole vielä esitetty asiakkaalle.',
            ],
            'booking_requested' => [
                'title' => 'Tila - Vahvistamaton',
                'desc' => 'Tämä ilmoitus lähetetään kun Uusi Varaus on luotu, mutta yritysellä on sallitu vaihtoehto "Varauksen tila on vahvistamaton" (Mene Asetukset -> verkkovaraus widget -> Yleinen)',
            ],
            'booking_confirmed' => [
                'title' => 'Tila - Vahvistettu',
                'desc' => 'Tämä ilmoitus lähetetään kun ylläpitäjä on vaihtanut tilaksi Vahvistettu.',
            ],
            'booking_assigned' => [
                'title' => 'Tila - Työ Osoitettu',
                'desc' => 'Tämä ilmoitus lähetetään kun ylläpitäjä on osoittanut työn kuljettajalle.',
            ],
            'booking_auto_dispatch' => [
                'title' => 'Status - Auto Dispatch',
                'desc' => 'This notification is sent when admin auto dispatch a driver.',
            ],
            'booking_accepted' => [
                'title' => 'Tila - Työ Vastaanotettu',
                'desc' => 'Tämä ilmoitus lähetetään kun kuljettaja ottaa työn vastaan vaihtamalla tilan Työ Vastaanotettu.',
            ],
            'booking_rejected' => [
                'title' => 'Tila - Työ Hylätty',
                'desc' => 'Tämä ilmoitus lähetetään kun kuljettaja hylkää työn vaihtamalla tilan Työ Hylätty.',
            ],
            'booking_onroute' => [
                'title' => 'Tila - Matkalla',
                'desc' => 'Tämä ilmoitus lähetetään kun kuljettaja aloittaa matkan aloitusosoitteeseen ja vaihtaa tilaksi Matkalla.',
            ],
            'booking_arrived' => [
                'title' => 'Tila - Saapunut',
                'desc' => 'Tämä ilmoitus lähetetään kun kuljettaja saapuu aloitusosoitteeseen ja vaihtaa tilaksi Saapunut.',
            ],
            'booking_onboard' => [
                'title' => 'Tila - Aloitettu',
                'desc' => 'Tämä ilmoitus lähetetään kun tilaus on aloitettu ja kuljettaja vaihtaa tilaksi Aloitettu.',
            ],
            'booking_completed' => [
                'title' => 'Tila - Valmis',
                'desc' => 'Tämä ilmoitus lähetetään kun kuljettaja päättää työn ja vaihtaa tilaksi Valmis.',
            ],
            'booking_canceled' => [
                'title' => 'Tila - Peruttu',
                'desc' => 'Tämä ilmoitus lähetetään kun ylläpitäjä vaihtaa tilaksi Peruttu.',
            ],
            'booking_unfinished' => [
                'title' => 'Tila - Kuljettaja Peruttu',
                'desc' => 'Tämä ilmoitus lähetetään kun kuljettaja vaihtaa tilaksi Kuljettaja Peruttu.',
            ],
            'booking_incomplete' => [
                'title' => 'Tila - Keskeneräinen',
                'desc' => 'Tämä ilmoitus lähetetään kun asiakas valitsee maksutavaksi kortin.',
            ],
        ],
        'roles' => [
            'admin' => 'Ylläpitäjä',
            'driver' => 'Kuljettaja',
            'customer' => 'Asiakas',
        ],
        'options' => [
            'email' => 'Sähköposti',
            'sms' => 'SMS',
            'push' => 'Push',
            'db' => 'Paneeli',
        ],
        'notification_booking_pending_info' => 'Varaustiedot sähköpostin alatunnisteviesti',
        'notification_booking_pending_info_help' => 'Tähän ruutuun kirjoitettu viesti ohittaa varaustiedot sisältävän sähköpostin alatunnisteen oletusviestin.',
        'notification_test_email' => 'Sähköpostitestaus',
        'notification_test_email_placeholder' => 'Syötä sähköpostiosoitteesi...',
        'notification_test_email_help' => 'Poistaaksesi testitilan käytöstä, jätä tämä kenttä tyhjäksi. Jos haluat nähdä miltä sähköpostiviestit näyttävät kun ne lähetetään järjestelmästä, voit syöttää sähköpostiosoitteesi tähän ja kaikki sähköpostiviestit menevät kyseiseen postilaatikkoon alkuperäisen vastaanottajan sijasta.',
        'notification_test_phone' => 'SMS testaus',
        'notification_test_phone_placeholder' => 'Syötä puhelinnumero',
        'notification_test_phone_help' => 'Poistaaksesi testitilan käytöstä, jätä tämä kenttä tyhjäksi. Jos haluat nähdä miltä tekstiviestit näyttävät kun ne lähetetään järjestelmästä, voit syöttää puhelinnumerosi tähän ja kaikki tekstiviestit menevät kyseiseen numeroon alkuperäisen vastaanottajan sijasta.',
        'add_new' => 'Add new notification',
        'send' => 'Send a notification',
        'select_notifications' => 'Select notifications',
    ],
    'general' => [
        'subtitle' => 'Yleinen',
        'logo' => 'Logo',
        'logo_delete' => 'Poista logo',
    ],
    'button' => [
        'save' => 'Tallenna',
        'saving' => 'Tallentaa...',
        'clear' => 'Tyhjennä',
    ],
    'message' => [
        'saved' => 'Tallennettu.',
        'save_error' => 'Asetuksia ei voitu tallentaa.',
        'connection_error' => 'Pyyntöäsi käsiteltäessä tapahtui virhe.',
    ],
    'send_test_email' => 'Syötä sähköpostiosoite',
    'send_test_email_button' => 'Lähetä testisähköposti',
    'send_test_email_failed' => 'Viestiä ei voitu lähettää, tarkasta asetuksesi.',
    'send_test_email_message_subject' => 'Testisähköposti',
    'send_test_email_message_body' => 'Testiviesti on onnistuneesti lähetetty sähköpostiisi.',
];
