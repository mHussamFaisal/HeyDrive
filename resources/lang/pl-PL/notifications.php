<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polish (pl-PL) - Notifications
    |--------------------------------------------------------------------------
    */

    'booking_pending' => [
        'subject' => 'Nowa rezerwacja :ref_number',
        'message' => 'Nowa rezerwacja :ref_number została utworzona.',
        // 'subject_customer' => ':company_name rezerwacja :ref_number',
        // 'message_customer' => 'Dziękujemy za dokonanie rezerwacji :ref_number.',
        'subject_customer' => ':company_name potwierdzenie rezerwacji :ref_number',
        'message_customer' => 'Twoja rezerwacja :ref_number została potwierdzona.',
        'info' => "Rezerwacje uzgodnione bezpośrednio z kierowcą są nielegalne, a jeśli zdarzy się wypadek, nie przysługuje Ci prawo do ubezpieczenia.\r\n\r\n".
                  "Przy odbiorze z lotniska kierowca będzie czekał na końcu terminalu (punkt odbioru) z tabliczką z Twoim imieniem (prosimy o włączenie telefonu komórkowego po wylądowaniu).\r\n\r\n".
                  "Przy odbiorze z innego adresu, kierowca będzie czekał przed drzwiami. Jeśli w tym miejscu nie można parkować, kierowca będzie czekał w najbliższym możliwym miejscu, gdzie jest to dozwolone.",
    ],
    'booking_quote' => [
        'subject' => 'Nowa prośba o wycenę rezerwacji :ref_number',
        'message' => 'Nowa prośba o wycenę rezerwacji :ref_number oczekuje potwierdzenia.',
        'subject_customer' => ':company_name prośba o wycenę rezerwacji :ref_number',
        'message_customer' => "Dziękujemy za zapytanie o wycenę rezerwacji :ref_number.\r\nDziękujemy za cierpliwość, wkrótce skontaktujemy się z Tobą.",
    ],
    'booking_requested' => [
        'subject' => 'Nowa prośba o rezerwację :ref_number',
        'message' => 'Nowa prośba o rezerwację :ref_number została utworzona i oczekuje na potwierdzenie.',
        'subject_customer' => ':company_name prośbę o rezerwację :ref_number',
        'message_customer' => "Dziękujemy za Twoją prośbę o rezerwację :ref_number. Proszę dać nam :request_time na potwierdzenie rezerwacji.\r\nDziękujemy za cierpliwość. Wkrótce skontaktujemy się z Tobą.",
    ],
    'booking_confirmed' => [
        'subject' => ':company_name potwierdzenie rezerwacji :ref_number',
        'message' => 'Twoja rezerwacja :ref_number została potwierdzona.',
    ],
    'booking_assigned' => [
        'subject' => 'Przydzielono nową prace :ref_number',
        'message' => 'Otrzymałeś nową pracę :ref_number.',
        'subject_customer' => 'Dane kierowcy. Rezerwacja :ref_number',
        'message_customer' => 'Poniżej znajdziesz informacje o swoim kierowcy.',
        'message_customer_sms' => 'Rezerwacja :ref_number. Dane kierowcy :driver_name, :driver_mobile_no. Pojazd: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_auto_dispatch' => [
        'subject' => 'Przydzielono nową prace :ref_number',
        'message' => 'Otrzymałeś nową pracę :ref_number.',
        'subject_customer' => 'Dane kierowcy. Rezerwacja :ref_number',
        'message_customer' => 'Poniżej znajdziesz informacje o swoim kierowcy.',
        'message_customer_sms' => 'Rezerwacja :ref_number. Dane kierowcy :driver_name, :driver_mobile_no. Pojazd: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_accepted' => [
        'subject' => 'Praca przyjęta :ref_number',
        'message' => 'Praca :ref_number. Kierowca :driver_name przyjął pracę.',
        'subject_customer' => 'Dane kierowcy. Rezerwacja :ref_number',
        'message_customer' => 'Poniżej znajdziesz informacje o swoim kierowcy.',
        'message_customer_sms' => 'Rezerwacja :ref_number. Dane kierowcy :driver_name, :driver_mobile_no. Pojazd: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_rejected' => [
        'subject' => 'Praca odrzucona :ref_number',
        'message' => 'Praca :ref_number. Kierowca :driver_name odrzucił pracę.',
    ],
    'booking_onroute' => [
        'subject' => 'Kierowca w drodze :ref_number',
        'message' => 'Praca :ref_number. Kierowca :driver_name w drodze',
        'subject_customer' => 'Twój kierowca jest w drodze. Rezerwacja :ref_numberr',
        'message_customer' => "Twój kierowca jest w drodze.",
        'message_customer_sms' => 'Rezerwacja :ref_number. Twój kierowca :driver_name jest w drodze, :driver_mobile_no. Pojazd: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_arrived' => [
        'subject' => 'Twój kierowca przyjechał :ref_number',
        'message' => 'Praca :ref_number. Kierowca :driver_name przybył do miejsca odbioru i czeka na klienta.',
        'subject_customer' => 'Twój kierowca przyjechał :ref_number',
        'message_customer' => 'Twój  kierowca :driver_name przyjechał i czeka w uzgodnionym miejscu odbioru. Rezerwacja :ref_number.',
        'message_customer_sms' => 'Rezerwacja :ref_number. Kierowca :driver_name przyjechał i czeka w uzgodnionej lokalizacji odbioru. Pojazd: :vehicle_make :vehicle_model :vehicle_color :vehicle_registration_mark.',
    ],
    'booking_onboard' => [
        'subject' => 'Klient na pokładzie :ref_number',
        'message' => 'Praca :ref_number. Klient jest na pokładzie.',
    ],
    'booking_completed' => [
        'subject' => 'Praca zakończona :ref_number',
        'message' => 'Praca :ref_number została zakończona.',
        'subject_customer' => 'Prześlij nam swoją opinię :ref_number',
        'message_customer' => "Dziękujemy za skorzystanie z usługi :company_name.\r\nMamy nadzieję, że Twoje wrażenia były wspaniałe, chcielibyśmy poznać Twoją opinie.",
        'message_customer_sms' => "Dziękujemy za skorzystanie z usługi :company_name. Mamy nadzieję, że Twoje wrażenia były wspaniałe, chcielibyśmy poznać Twoją opinie.\r\n:action_url",
        'link_view_customer' => 'Zostaw opinię',
    ],
    'booking_canceled' => [
        'subject' => 'Anulacja rezerwacji :ref_number',
        'message' => 'Rezerwacja :ref_number została anulowana.',
    ],
    'booking_unfinished' => [
        'subject' => 'Driver cancelled :ref_number',
        'message' => 'Driver :driver_name has cancelled the booking :ref_number.',
    ],
    'booking_incomplete' => [
        'subject' => 'Nowa niepełna / nieopłacona praca :ref_number',
        'message' => 'Nowa niepełna / nieopłacona praca :ref_number.',
        'subject_customer' => 'Płatność wymagana :ref_number',
        'message_customer' => "Dokonaj płatności, aby dokończyć rezerwację :ref_number.",
    ],
    'booking_invoice' => [
        'subject' => 'Faktura :ref_number',
        'message' => 'Poniżej znajduje się faktura :ref_number.',
    ],
    'greeting' => [
        'general' => 'Szanowny/a :Name,',
        'default' => 'Cześć!',
        'error' => 'Ups!',
    ],
    'reason' => 'Powód',
    'link_view' => 'Zobacz',
    'salutation' => 'Pozdrawiamy,',
    'sub_copy' => 'Jeśli masz problemy z kliknięciem w przycisk ":name", skopiuj i wklej adres do swojej przeglądarki.',
    'footer' => [
        'phone' => 'Tel',
        'email' => 'E-mail',
        'site' => 'WWW',
    ],

];
