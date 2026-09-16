<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polish (pl-PL) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Szczegóły rezerwacji :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Poniżej szczegóły Twojej rezerwacji',
        'note_line1' => 'Zadzwoń do nas od razu jeśli nie możesz zlokalizować swojego kierowcy. Jeśli będzie się to działo poza godzinami naszej pracy, to w odpowiedzi otrzymasz informacje kontaktowe kierowcy.',
        'note_line2' => 'Rezerwacje uzgodnione bezpośrednio z kierowcą są nielegalne, a jeśli zdarzy się wypadek, nie przysługuje Ci prawo do ubezpieczenia.',
        'note_line3' => 'Przy odbiorze z lotniska kierowca będzie czekał na końcu terminalu (punkt odbioru) z tabliczką z Twoim imieniem (prosimy o włączenie telefonu komórkowego po wylądowaniu). Przy odbiorze z innego adresu, kierowca będzie czekał przed drzwiami. Jeśli w tym miejscu nie można parkować, kierowca będzie czekał w najbliższym możliwym miejscu, gdzie jest to dozwolone.',
        'section' => [
            'journey_details' => 'Szczegóły podróży',
            'customer_details' => 'Dane klienta',
            'lead_passenger_details' => 'Dane głównego pasażera',
            'reservation_details' => 'Szczegóły rezerwacji',
            'general_details' => 'Ogólne informacje o rezerwacji',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Aktualizacja statusu dla :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Status Twojej rezerwacji :ref_number zmienił się na :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Dane kierowcy :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Poniżej znajdziesz dane Twojego kierowcy',
        'booking' => 'Rezerwacja',
        'driver' => 'Kierowca',
        'vehicle' => 'Samochód',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Status kierowcy :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Twój kierowca jest w drodze.',
        'booking' => 'Rezerwacja',
        'driver' => 'Kierowca',
        'vehicle' => 'Samochód',
    ],
    'customer_booking_completed' => [
        'subject' => 'Opinie :ref_number',
        'greeting' => 'Drogi :Name',
        'line1' => 'Dziękuje za skorzystanie z usług :company_name w Twojej podróży.',
        'line2' => 'Bardzo chętnie poznamy Twoją opinię na temat niedawnej rezerwacji :ref_number',
        'line3' => 'Żeby podzielić się z nami swoją opinią, kliknij tutaj :link',
        'link' => 'link',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Potrzebna wycena',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Dziękujemy za przesłanie prośby o wycenę usługi. Skontaktujemy się z Toba niedługo.',
        'line2' => 'Poniżej zestawienie szczegółów, które podałeś w swojej prośbie o wycenę.',
        'section' => [
            'journey_details' => 'Szczegóły podróży',
            'customer_details' => 'Dane klienta',
            'lead_passenger_details' => 'Dane głównego pasażera',
            'general_details' => 'Ogólne dane',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Żądanie zapłaty :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'W celu zabezpieczenia swojej podóży, proszę wpłać :price.',
        'line2' => 'Możesz dokonać wpłaty, klikając w ten :link.',
        'link' => 'link',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Potwierdzenie płatności dla :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Twoja faktura jest załączona poniżej.',
        'line2' => 'Dziękujemy za rezerwację usługi :company_name.',
        'line3' => 'Płatność za rezerwację :ref_number została :status.',
        'status' => 'POTWIERDZONA I ZAAKCEPTOWANA',
    ],
    'customer_account_activation' => [
        'subject' => 'Aktywacja',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Tu Twój link aktywacyjny.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Witaj',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Twoje konto zostało aktywowane. Możesz się już zalogować!',
    ],
    'customer_account_password' => [
        'subject' => 'Zresetuj hasło',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Tu możesz zresetować swoje hasło :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Aktualizacja statusu zlecenia :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Status zlecenia :ref_number zmienił się na :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Rezerwacja anulowana :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Klient :customer_name anulował rezerwację :ref_number z dnia :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Aktualizacja statusu zlecenia :ref_number',
        'greeting' => 'Szanowny/a :Name',
        'line1' => 'Status zlecenia :ref_number zmienił się z :old_status na :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'E-mail',
        'site' => 'WWW',
        'feedback' => 'Swoją opinią możesz podzielić się :link',
        'feedback_link' => 'tutaj',
    ],
    'powered_by' => 'Obsługiwane przez',

];
