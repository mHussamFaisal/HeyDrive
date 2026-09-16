<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hungarian (hu-HU) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Foglalás részletei :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Kérjük, olvassa el az alábbi foglalási adatokat.',
        'note_line1' => 'Kérjük mentse el telefonszámunkat. Amennyiben nem találja a találkozási pontot, kérjük azonnal hívja sofőrünket vagy irodánkat.',
        'note_line2' => 'A GÉPKOCSIVEZETŐVEL KÖZVETLENÜL FOGLALNI ILLEGÁLIS ÉS BALESET ESETÉN ÖNRE NEM VONATKOZIK AZ UTASBIZTOSÍTÁS!',
        'note_line3' => 'Repülőtéri felvétel esetén a sofőr a terminálban táblával várja. Kérjük, mobiltelefonját leszálláskor kapcsolja be! Címen történő felvétel esetén a sofőr a ház előtt várakozik.',
        'section' => [
            'journey_details' => 'Utazás részletei',
            'customer_details' => 'Utas adatai',
            'lead_passenger_details' => 'Vezető utas adatai',
            'reservation_details' => 'Foglalás adatai',
            'general_details' => 'A foglalás általános adatai',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Állapotfrissítés :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Az Ön foglalása :ref_number status has changed to :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Sofőr adatai :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Sofőr adatai',
        'booking' => 'Foglalás',
        'driver' => 'Sofőr',
        'vehicle' => 'Autó',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Sofőr státus :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'A sofőr útban van Önhöz.',
        'booking' => 'Foglalás',
        'driver' => 'Sofőr',
        'vehicle' => 'Autó',
    ],
    'customer_booking_completed' => [
        'subject' => 'Visszajelzés :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Köszönjük, hogy a :company_name választotta utazásához.',
        'line2' => 'Kíváncsiak vagyunk a véleményére a:ref_number számú foglalásával kapcsoltban.',
        'line3' => 'Visszajelzéshez kérjük kattintson a linkre :link.',
        'link' => 'link',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Árajánlat kérés',
        'greeting' => 'Kedves :Name',
        'line1' => 'Köszönjük, hogy ajánlatot kért. Rövidesen kapcsolatba lépünk Önnel.',
        'line2' => 'Addig is itt megtalálja az ajánlatkérésben benyújtott adatokat.',
        'section' => [
            'journey_details' => 'Út Adatai',
            'customer_details' => 'Utas Adatai',
            'lead_passenger_details' => 'Vezető Utas Adatai',
            'general_details' => 'Általános Adatok',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Fizetési kérelem :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'A foglalás véglegesítéséhez kérjük az alábbi összeg befizetését :price.',
        'line2' => 'Az következő linkre kattintva tud fizetni :link.',
        'link' => 'link',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Fizetés visszaigazolása :ref number',
        'greeting' => 'Kedves :Name',
        'line1' => 'A számlát csatolva megtalálja.',
        'line2' => 'Thank you for booking with :company_name.',
        'line3' => 'A foglalás fizetése :ref_number has been :status.',
        'status' => 'ELFOGADOTT ÉS VISSZAIGAZOLT',
    ],
    'customer_account_activation' => [
        'subject' => 'Aktiválás',
        'greeting' => 'Kedves :Name',
        'line1' => 'Kérjük kattintson a linkre és aktiválja fiókját.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Üdvözöljük',
        'greeting' => 'Kedves :Name',
        'line1' => 'Fiókját sikeresen aktiválta. Kérjük lépjen be!',
    ],
    'customer_account_password' => [
        'subject' => 'Jelszó visszaállítás',
        'greeting' => 'Kedves :Name',
        'line1' => 'Itt van a token a jelszó visszaállításához: :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Fuvar státus frissítés :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Fuvar :ref_number státus megváltozott :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Foglalás törlése :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Utas :customer_name törölte a foglalást :ref_number án :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Fuvar státus frissítése :ref_number',
        'greeting' => 'Kedves :Name',
        'line1' => 'Fuvar :ref_number megváltozott régi :old_status új :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'Email',
        'site' => 'Weboldal',
        'feedback' => 'Kérjük hagyjon véleményt :link',
        'feedback_link' => 'itt',
    ],
    'powered_by' => 'Powered by',

];
