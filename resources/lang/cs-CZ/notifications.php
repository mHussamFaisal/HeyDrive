<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Czech (cs-CZ) - Notifications
    |--------------------------------------------------------------------------
    */

    'booking_pending' => [
        'subject' => 'Nová rezervace :ref_number',
        'message' => 'Nová rezervace :ref_number byla vytvořena.',
        'subject_customer' => ':company_name potvrzení rezervace :ref_number',
        'message_customer' => '<span style="color:#000; font-weight:bold;"><span style="color:#008000;">Potvrzení rezervace:</span> Děkujeme za rezervaci s :company_name. Vaše číslo rezervace je <span style="color:#CE0000;">:ref_number</span>.</span>',
        'message_customer_sms' => 'VAŠE TAXI Z :booking_from JE REZERVOVÁNO NA :booking_date. DĚKUJEME VÁM, ŽE POUŽÍVÁTE :company_name.',
        'message_customer_sms_full' => 'Rezervace :ref_number byla potvrzena.',
        'info' => "<span style=\"color:#000; font-weight:bold;\">Důležité informace o Vaší rezervaci:</span>\r\n".
                  "- Pokud si ji přejete zrušit, provést změny nebo zaplatit debetní či kreditní kartou, prosím, kontaktujte nás.\r\n".
                  "- Provedení rezervace přímo s řidičem jsou proti pravidlům. V případě nehody nebudete kryti pojištěním.\r\n".
                  "- Při vyzvedávání na letišti na Vás bude řidič čekat na terminálu (na místě setkání) s cedulí s Vaším jménem (po přistání si prosím ihned zapněte mobil).\r\n".
                  "- Při vyzvedávání na konkrétní adresu na Vás bude řidič čekat přede dveřmi. V případě, že jsou zde parkovací omezení, bude na Vás čekat na nejbližším možném místě k vyzvednutí.",
    ],
    'booking_quote' => [
        'subject' => 'Nová žádost nabídky rezervace :ref_number',
        'message' => 'Nová žádost nabídky rezervace :ref_number čeká na kontrolu.',
        'subject_customer' => ':company_name rezervace :ref_number žádost o nabídku',
        'message_customer' => "Děkujeme za žádost o nabídku na rezervaci :ref_number.\r\nDěkujeme za vaši trpělivost a brzy vás budeme kontaktovat.",
    ],
    'booking_requested' => [
        'subject' => 'Nový požadavek rezervace :ref_number',
        'message' => 'Nový požadavek rezervace :ref_number byl proveden a čeká se na potvrzení.',
        'subject_customer' => ':company_name rezervace :ref_number žádost o nabídku',
        'message_customer' => '<span style="color:#000; font-weight:bold;">Vaše rezervace <span style="color:blue;">čeká na potvrzení</span>. Děkujeme za provedení rezervace s :company_name. Číslo Vaší rezervace je <span style="color:#CE0000;">:ref_number</span>.</span>'.
                              "\r\n\r\n".
                              '<span style="color:#000; font-weight:bold;">Potvrzovací email bude zaslán na Vaši e-mailovou adresu v nejbližších :request_time hodinách.',
    ],
    'booking_confirmed' => [
        'subject' => ':company_name potvrzení rezervace :ref_number',
        'message' => 'Vaše rezervace :ref_number byla potvrzena.',
        'message_customer' => '<span style="color:#000; font-weight:bold;"><span style="color:#008000;">Potvrzení rezervace:</span>Děkujeme za rezervaci s :company_name. Číslo Vaší rezervace je <span style="color:#CE0000;">:ref_number</span>.</span>',
    ],
    'booking_assigned' => [
        'subject' => 'Nová zakázka :ref_number byla přiřazena',
        'message' => 'Byla přidělena nová zakázka :ref_number.',
        'subject_customer' => 'Údaje o řidiči. Rezervace :ref_number',
        'message_customer' => 'Údaje o řidiči naleznete níže.',
        'message_customer_sms' => 'VAŠE TAXI BYLO POSLÁNO NA :booking_from, :booking_date. DĚKUJEME, ŽE VYUŽÍVÁTE :company_name.',
    ],
    'booking_auto_dispatch' => [
        'subject' => 'Nová zakázka :ref_number byla přiřazena',
        'message' => 'Byla přidělena nová zakázka :ref_number.',
        'subject_customer' => 'Údaje o řidiči. Rezervace :ref_number',
        'message_customer' => 'Údaje o řidiči naleznete níže.',
        'message_customer_sms' => 'VAŠE TAXI BYLO POSLÁNO NA :booking_from, :booking_date. DĚKUJEME, ŽE VYUŽÍVÁTE :company_name.',
    ],
    'booking_accepted' => [
        'subject' => 'Zakázka přijata :ref_number',
        'message' => 'Zakázka :ref_number. Řidič :driver_name zakázku přijal.',
        'subject_customer' => 'Údaje o řidiči. Rezervace :ref_number',
        'message_customer' => 'Údaje o řidiči naleznete níže.',
        'message_customer_sms' => 'VAŠE TAXI BYLO POSLÁNO NA :booking_from, :booking_date. DĚKUJEME, ŽE VYUŽÍVÁTE :company_name.',
    ],
    'booking_rejected' => [
        'subject' => 'Zakázka odmítnuta :ref_number',
        'message' => 'Zakázka :ref_number. Řidič :driver_name odmítl zakázku.',
    ],
    'booking_onroute' => [
        'subject' => 'Řidič je na trase :ref_number',
        'message' => 'Zakázka :ref_number. Řidič :driver_name je na trase.',
        'subject_customer' => 'Řidič je na trase. Rezervace :ref_number',
        'message_customer' => 'Řidič je na trase. ',
        'message_customer_sms' => 'VAŠE TAXI BYLO POSLÁNO NA :booking_from, :booking_date. DĚKUJEME, ŽE VYUŽÍVÁTE :company_name.',
    ],
    'booking_arrived' => [
        'subject' => 'Řidič dorazil :ref_number',
        'message' => 'Zakázka :ref_number. Řidič :driver_name dorazil na místo vyzvednutí a čeká na zákazníka.',
        'subject_customer' => 'Řidič dorazil :ref_number',
        'message_customer' => 'Řidič :driver_name přijel a čeká na vyzvednutí na domluveném místě. Rezervace :ref_number.',
        'message_customer_sms' => 'VÁŠ ŘIDIČ :driver_name DORAZIL V :vehicle_details. PŘEJEME BEZPEČNOU CESTU. DĚKUJEME VÁM ZA VYUŽITÍ :company_name.',
    ],
    'booking_onboard' => [
        'subject' => 'Zákazník na palubě :ref_number',
        'message' => 'Zakázka :ref_number. Zákazník je na palubě.',
    ],
    'booking_completed' => [
        'subject' => 'Zakázka dokončena :ref_number',
        'message' => 'Zakkázka :ref_number byla dokončena.',
        'subject_customer' => 'Zpětná vazba :ref_number',
        'message_customer' => "Děkujeme za využití :company_name.\r\nDoufáme, že se Vám cesta líbila a budeme rádi, když nám poskytnete zpětnou vazbu.",
        'message_customer_sms' => "Děkujeme za využití :company_name. Doufáme, že se Vám cesta líbila a budeme rádi, když nám poskytnete zpětnou vazbu.\r\n:action_url",
        'link_view_customer' => 'Zanechat zpětnou vazbu',
    ],
    'booking_canceled' => [
        'subject' => 'Zrušení rezervace :ref_number',
        'message' => 'Rezervace :ref_number byla zrušena.',
    ],
    'booking_unfinished' => [
        'subject' => 'Řidič zrušen :ref_number',
        'message' => 'Řidič :driver_name zrušil rezervaci :ref_number.',
    ],
    'booking_incomplete' => [
        'subject' => 'Nová neúplná / nezaplacená úloha :ref_number',
        'message' => 'Nová neúplná / nezaplacená úloha :ref_number.',
        'subject_customer' => 'Požadována platba :ref_number',
        'message_customer' => 'Prosím, pro dokončení rezervace  :ref_number proveďte platbu.',
    ],
    'booking_invoice' => [
        'subject' => 'Faktura :ref_number',
        'message' => 'Fakturu :ref_number nalezněte, prosím, níže.',
    ],
    'greeting' => [
        'general' => 'Vážený/á :Name,',
        'default' => 'Dobrý den',
        'error' => 'Jejda!',
    ],
    'reason' => 'Důvod',
    'link_view' => 'Zobrazit',
    'salutation' => 'S pozdravem',
    'sub_copy' => 'Pokud máte potíže s klepnutím na tlačítko ":name", zkopírujte a vložte níže uvedenou adresu URL do webového prohlížeče:',
    'footer' => [
        'phone' => 'Tel.',
        'email' => 'E-mail',
        'site' => 'Web',
    ],

];
