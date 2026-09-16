<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Czech (cs-CZ) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Podrobnosti rezervace :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Níže naleznete podrobnosti o rezervaci.',
        'note_line1' => 'Vždy mějte telefon po ruce. Máte-li problém s nalezením řidiče, hned nám zavolejte. Je-li Vaše jízda mimo standardní provozní dobu, dostanete k dispozici číslo řidiče.',
        'note_line2' => 'Vytvoření rezervace přímo u řidiče je proti pravidlům. V případě nehody se pak na Vás nebude vztahovat pojištění.',
        'note_line3' => 'Při vyzvedávání na letištích bude řidič čekat na terminálu (na domluveném místě setkání) s cedulí s Vaším jménem (prosím, zapněte mobil ihned po přistání). Jestliže vyzvednutí probíhá na konkrétní adrese, bude řidič čekat přede dveřmi. Pokud budou v daném okolí omezení parkování, bude čekat na místě setkání (na nejbližším možném místě).',
        'section' => [
            'journey_details' => 'Podrobnosti cesty',
            'customer_details' => 'Podrobnosti o zákazníkovi',
            'lead_passenger_details' => 'Údaje o hlavním cestujícím',
            'reservation_details' => 'Podrobnosti rezervace',
            'general_details' => 'Všeobecné informace o rezervaci',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Aktualizace stavu :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Stav Vaší rezervace :ref_number se změnil na :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Podrobnosti o řidiči :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Podrobné informace o Vašem řidiči naleznete níže.',
        'booking' => 'Rezervace',
        'driver' => 'Řidič',
        'vehicle' => 'Vozidlo',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Stav řidiče :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Váš řidič je na cestě.',
        'booking' => 'Rezervace',
        'driver' => 'Řidič',
        'vehicle' => 'Vozidlo',
    ],
    'customer_booking_completed' => [
        'subject' => 'Zpětná vazba :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Děkujeme, že jste využili služeb :company_name pro Vaší cestu.',
        'line2' => 'Rádi bychom slyšeli Vaši zpětnou vazbu ohledně rezervace :ref_number, kterou jste s námi nedávno podnikli.',
        'line3' => 'Chcete-li poslat zpětnou vazbu, klikněte prosím na tento odkaz.',
        'link' => 'odkaz',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Žádost o nabídku',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Děkujeme za žádost o nabídku. Budeme vás brzy kontaktovat.',
        'line2' => 'Mezitím zde najdete souhrn podrobností, které jste vyplnili pro nabídku.',
        'section' => [
            'journey_details' => 'Podrobnosti cesty',
            'customer_details' => 'Podrobnosti o zákazníkovi',
            'lead_passenger_details' => 'Údaje o hlavním cestujícím',
            'general_details' => 'Obecné podrobnosti',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Žádost o platbu :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Pro zajištění Vaší rezervace prosím uhraďte :price.',
        'line2' => 'Platbu můžete provést kliknutím na tento odkaz :link.',
        'link' => 'odkaz',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Potvrzení platby :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Níže je přiložena faktura.',
        'line2' => 'Děkujeme za rezervaci s :company_name.',
        'line3' => 'Platba za rezervaci :ref_number byla :status.',
        'status' => 'PŘIJATO A POTVRZENO',
    ],
    'customer_account_activation' => [
        'subject' => 'Aktivace',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Zde je váš aktivační odkaz.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Vítejte',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Váš účet byl úspěšně aktivován. Můžete se přihlásit!',
    ],
    'customer_account_password' => [
        'subject' => 'Obnovit heslo',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Zde je kód pro resetování hesla :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Aktualizace stavu rezervace :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Stav rezervace :ref_number se změnil na :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Storno rezervace :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Zákazník :customer_name zrušil rezervaci :ref_number dne :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Aktualizace stavu rezervace :ref_number',
        'greeting' => 'Vážený/á :Name',
        'line1' => 'Stav rezervace :ref_number se změnil z :old_status na :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel.',
    ],
    'footer' => [
        'phone' => 'Tel.',
        'email' => 'E-mail',
        'site' => 'Webová stránka',
        'feedback' => 'Svoji zpětnou vazbu můžete zanechat na :link',
        'feedback_link' => 'zde',
    ],
    'powered_by' => 'Běží na',

];
