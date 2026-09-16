<?php

return [

    /*
    |--------------------------------------------------------------------------
    | German (de-DE) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Das :attribute muss akzeptiert werden.',
    'active_url'           => 'Das :attribute ist keine gültige URL.',
    'after'                => 'Das :attribute muss ein Datum nach dem :date sein.',
    'alpha'                => 'Das :attribute darf nur Buchstaben enthalten.',
    'alpha_dash'           => 'Das :attribute darf nur Buchstaben, Zahlen und Bindestriche enthalten.',
    'alpha_num'            => 'Das :attribute darf nur Buchstaben und Zahlen enthalten.',
    'array'                => 'Das :attribute muss ein Array sein.',
    'before'               => 'Das :attribute muss ein Datum vor dem :date sein.',
    'between'              => [
        'numeric' => 'Das :attribute muss zwischen :min und :max liegen.',
        'file'    => 'Das :attribute muss zwischen :min und :max Kilobyte liegen.',
        'string'  => 'Das :attribute muss zwischen :min und :max Zeichen liegen.',
        'array'   => 'Das :attribute muss zwischen :min und :max Artikeln liegen.',
    ],
    'boolean'              => 'Das :attribute Feld muss wahr oder falsch sein.',
    'confirmed'            => 'Die :attribute Bestätigung stimmt nicht überein.',
    'date'                 => 'Das :attribute ist kein gültiges Datum.',
    'date_format'          => 'Das :attribute entspricht nicht dem Format :format.',
    'different'            => 'Das :attribute und :other müssen verschieden sein.',
    'digits'               => 'Das :attribute muss aus :digits Ziffern bestehen.',
    'digits_between'       => 'Das :attribute muss zwischen :min und :max Ziffern liegen.',
    'dimensions'           => 'Das :attribute hat ungültige Bildabmessungen.',
    'distinct'             => 'Das :attribute hat einen doppelten Wert.',
    'email'                => 'Das :attribute muss eine gültige E-Mail-Adresse sein.',
    'exists'               => 'Das ausgewählte :attribute ist ungültig.',
    'file'                 => 'Das :attribute muss eine Datei sein.',
    'filled'               => 'Das Feld für das :attribute ist ein Pflichtfeld.',
    'image'                => 'Das :attribute muss ein Bild sein.',
    'in'                   => 'Das ausgewählte :attribute ist ungültig.',
    'in_array'             => 'Das :attribute Feld existiert nicht in :other.',
    'integer'              => 'Das :attribute muss eine ganze Zahl sein.',
    'ip'                   => 'Das :attribute muss eine gültige IP-Adresse sein.',
    'json'                 => 'Das :attribute muss ein gültiger JSON-String sein.',
    'max'                  => [
        'numeric' => 'Das :attribute darf nicht größer sein als :max.',
        'file'    => 'Das :attribute darf nicht größer sein als :max Kilobyte.',
        'string'  => 'Das :attribute darf nicht größer sein als :max Zeichen.',
        'array'   => 'Das :attribute darf nicht mehr als :max Items haben.',
    ],
    'mimes'                => 'Das :attribute muss eine Datei vom Typ: :values sein.',
    'mimetypes'            => 'Das :attribute muss eine Datei vom Typ: :values sein.',
    'min'                  => [
        'numeric' => 'Das :attribute muss mindestens :min sein.',
        'file'    => 'Das :attribute muss mindestens :min Kilobyte betragen.',
        'string'  => 'Das :attribute muss aus mindestens :min Zeichen bestehen.',
        'array'   => 'Das :attribute muss mindestens :min Items enthalten.',
    ],
    'not_in'               => 'Das ausgewählte :attribute ist ungültig.',
    'numeric'              => 'Das :attribute muss eine Zahl sein.',
    'present'              => 'Das :attribute muss vorhanden sein.',
    'regex'                => 'Das :attribute Format ist ungültig.',
    'required'             => 'Das :attribute Feld ist ein Pflichtfeld.',
    'required_if'          => 'Das :attribute Feld ist benötigt wenn :other ist :value.',
    'required_unless'      => 'Das :attribute Feld ist benötigt, es sei denn :other ist in :values.',
    'required_with'        => 'Das :attribute Feld ist benötigt, wenn :values ist vorhanden.',
    'required_with_all'    => 'Das :attribute Feld ist erforderlich, wenn :values vorhanden ist.',
    'required_without'     => 'Das :attribute Feld ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => 'Das :attribute Feld wird benötigt, wenn keiner der :values vorhanden ist.',
    'same'                 => 'Das :attribute und :other müssen übereinstimmen.',
    'size'                 => [
        'numeric' => 'Das :attribute muss :size sein.',
        'file'    => 'Das :attribute muss :size Kilobytes haben.',
        'string'  => 'Das :attribute muss aus :size Zeichen bestehen.',
        'array'   => 'Das :attribute muss :size Elemente enthalten.',
    ],
    'string'               => 'Das :attribute muss ein String sein.',
    'timezone'             => 'Das :attribute muss eine gültige Zone sein.',
    'unique'               => 'Das :attribute wurde bereits übernommen.',
    'uploaded'             => 'Das :attribute konnte nicht hochgeladen werden.',
    'url'                  => 'Das :attribute Format ist ungültig.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
