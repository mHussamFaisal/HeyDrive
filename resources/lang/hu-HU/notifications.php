<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hungarian (hu-HU) - Notifications
    |--------------------------------------------------------------------------
    */

    'booking_pending' => [
        'subject' => 'Új foglalás :ref_number',
        'message' => 'Új foglalás :ref_number történt.',
        // 'subject_customer' => ':company_name foglalás :ref_number',
        // 'message_customer' => 'Köszönjük a foglalást :ref_number.',
        'subject_customer' => ':company_name foglalás visszaigazolása :ref_number',
        'message_customer' => 'A foglalását :ref_number visszaigazoltuk.',
        'info' => "A GÉPKOCSIVEZETŐVEL KÖZVETLENÜL FOGLALNI ILLEGÁLIS ÉS BALESET ESETÉN ÖNRE NEM VONATKOZIK AZ UTASBIZTOSÍTÁS!\r\n\r\n".
                  "Repülőtéri felvétel esetén a sofőr a terminálban táblával várja. Kérjük, mobiltelefonját leszálláskor kapcsolja be! Címen történő felvétel esetén a sofőr a ház előtt várakozik.",
    ],
    'booking_quote' => [
        'subject' => 'Új ajánlatkérés :ref_number',
        'message' => ':ref_number azonosítóval ellátott foglalása felülvizsgálatra vár.',
        'subject_customer' => ':company_name foglalás :ref_number ajánlatkérés',
        'message_customer' => "Köszönjük a :ref_number azonosítójú ajánlatkérés.\r\nKöszönjük a türelmet, és hamarosan kapcsolatba lépünk Önnel.",
    ],
    'booking_requested' => [
        'subject' => ':ref_number azonosítóval ellátott foglalás',
        'message' => ':ref_number azonosítóval ellátott ajánlatkérés, megerősítésre vár.',
        'subject_customer' => ':company_name foglalás :ref_number kérelem',
        'message_customer' => "Köszönjük a foglalást :ref_number. :request_time belül megerősítsük a foglalását.\r\nKöszönjük a türelmet, és hamarosan kapcsolatba lépünk Önnel.",
    ],
    'booking_confirmed' => [
        'subject' => ':company_name foglalás visszaigazolása :ref_number',
        'message' => ':ref_number azonosítóval ellátott foglalását visszaigazoljuk.',
    ],
    'booking_assigned' => [
        'subject' => 'Új munka :ref_number kiadva',
        'message' => 'Új munkát kaptál :ref_number.',
        'subject_customer' => 'Sofőr adatlapja. Foglalás azonosító :ref_number',
        'message_customer' => 'Az alábbiakban megtalálja sofőrünk adatait.',
        'message_customer_sms' => 'Foglalás :ref_number. Sofőr részletek :driver_name, :driver_mobile_no. Autó: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_auto_dispatch' => [
        'subject' => 'Új munka :ref_number kiadva',
        'message' => 'Új munkát kaptál :ref_number.',
        'subject_customer' => 'Sofőr adatlapja. Foglalás azonosító :ref_number',
        'message_customer' => 'Az alábbiakban megtalálja sofőrünk adatait.',
        'message_customer_sms' => 'Foglalás :ref_number. Sofőr részletek :driver_name, :driver_mobile_no. Autó: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_accepted' => [
        'subject' => 'Munka elfogadva :ref_number',
        'message' => 'Munka :ref_number. A sofőr :driver_name elfogadta a munkát.',
        'subject_customer' => 'Sofőr adatlapja. Foglalás :ref_number',
        'message_customer' => 'Az alábbiakban megtalálja sofőrünk adatait.',
        'message_customer_sms' => 'Foglalás :ref_number. Sofőr részletek :driver_name, :driver_mobile_no. Autó: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_rejected' => [
        'subject' => 'Munka visszautasítva :ref_number',
        'message' => 'Munka :ref_number. A sofőr :driver_name visszautasította a munkát.',
    ],
    'booking_onroute' => [
        'subject' => 'A sofőr útban van :ref_number',
        'message' => 'Munka :ref_number. A sofőr :driver_name útban van.',
        'subject_customer' => 'A sofőr útban van Önhöz. Foglalás :ref_number',
        'message_customer' => "A sofőr útban van Önhöz.",
        'message_customer_sms' => 'Foglalás :ref_number. A sofőr :driver_name útban van, :driver_mobile_no. Autó: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_arrived' => [
        'subject' => 'A sofőr megérkezett :ref_number',
        'message' => 'Munka :ref_number. A sofőr :driver_name megérkezett és az utasra vár.',
        'subject_customer' => 'A sofőr megérkezett :ref_number',
        'message_customer' => 'A sofőr :driver_name megérkezett és a megbeszélt találkozási ponton várakozik. Foglalás :ref_number.',
        'message_customer_sms' => 'Foglalás :ref_number. A sofőr :driver_name megérkezett és a megbeszélt címen várakozik. Autó: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_onboard' => [
        'subject' => 'Utas felvéve :ref_number',
        'message' => 'Munka :ref_number. Utas felvéve.',
    ],
    'booking_completed' => [
        'subject' => 'Munka befejezve :ref_number',
        'message' => 'Munka :ref_number befejezve.',
        'subject_customer' => 'Visszajelzés :ref_number',
        'message_customer' => "Köszönjük, hogy a :company_name szolgáltatását választotta.\r\nReméljük, hogy elégedett volt és szívesen vennénk a visszajelzését.",
        'message_customer_sms' => "Köszönjük, hogy a :company_name szolgáltatását választotta. Reméljük, hogy elégedett volt és szívesen vennénk a visszajelzését.\r\n:action_url",
        'link_view_customer' => 'Kérjük hagyjon véleményt',
    ],
    'booking_canceled' => [
        'subject' => 'Foglalás törlése :ref_number',
        'message' => 'Foglalás :ref_number törölve.',
    ],
    'booking_unfinished' => [
        'subject' => 'Driver cancelled :ref_number',
        'message' => 'Driver :driver_name has cancelled the booking :ref_number.',
    ],
    'booking_incomplete' => [
        'subject' => 'Új hiányos/fizetetlen munka :ref_number',
        'message' => 'Új befejezetlen/fizetetlen munka :ref_number.',
        'subject_customer' => 'Fizetés szükséges :ref_number',
        'message_customer' => "Kérjük fizessen a foglalás befejezéshez :ref_number.",
    ],
    'booking_invoice' => [
        'subject' => 'Számla :ref_number',
        'message' => ':ref_number azonosítóval ellátott számláját itt találja.',
    ],
    'greeting' => [
        'general' => 'Kedves :Name,',
        'default' => 'Üdvözöljük!',
        'error' => 'Hoppsz!',
    ],
    'reason' => 'Indíték',
    'link_view' => 'Megtekintés',
    'salutation' => 'Üdvözlettel',
    'sub_copy' => 'Amennyiben nem tud a ":name" gombra kattintani, másolja be az URL-t a böngészője keresőjébe:',
    'footer' => [
        'phone' => 'Tel',
        'email' => 'E-mail',
        'site' => 'Weboldal',
    ],

];
