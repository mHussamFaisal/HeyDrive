<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Italian (it-IT) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'L\':attribute deve essere accettato.',
    'active_url'           => 'L\':attribute non è un URL valido.',
    'after'                => 'L\':attribute deve essere una data successiva a :date.',
    'alpha'                => 'L\':attribute può contenere solo lettere.',
    'alpha_dash'           => 'L\':attribute può contenere solo lettere, numeri e trattini.',
    'alpha_num'            => 'L\':attribute può contenere solo lettere e numeri.',
    'array'                => 'L\':attribute deve essere una serie.',
    'before'               => 'L\':attribute deve essere una data precedente a :date.',
    'between'              => [
        'numeric' => 'L\':attribute deve essere compreso tra :min e :max.',
        'file'    => 'L\':attribute deve essere compreso tra :min e :max kilobyte.',
        'string'  => 'L\':attribute deve essere compreso tra :min e :max caratteri.',
        'array'   => 'L\':attribute deve avere tra :min e :max elementi.',
    ],
    'boolean'              => 'Il campo dell\':attribute deve essere vero o falso.',
    'confirmed'            => 'La conferma dell\':attribute non corrisponde.',
    'date'                 => 'L\':attribute non è una data valida.',
    'date_format'          => 'L\':attribute non corrisponde al formato :format.',
    'different'            => 'L\':attribute e :other devono essere diversi.',
    'digits'               => 'L\':attribute deve essere :digits cifre.',
    'digits_between'       => 'L\':attribute deve essere compreso tra :min e :max cifre.',
    'dimensions'           => 'L\':attribute ha dimensioni di immagine non valide.',
    'distinct'             => 'Il campo dell\':attribute ha un valore duplicato.',
    'email'                => 'L\':attribute deve essere un indirizzo email valido.',
    'exists'               => 'L\':attribute selezionato non è valido.',
    'file'                 => 'L\':attribute deve essere un file.',
    'filled'               => 'Il campo dell\':attribute è obbligatorio.',
    'image'                => 'L\':attribute deve essere un\'immagine.',
    'in'                   => 'L\':attribute selezionato non è valido.',
    'in_array'             => 'Il campo dell\':attribute non esiste in :other.',
    'integer'              => 'L\':attribute deve essere un numero intero.',
    'ip'                   => 'L\':attribute deve essere un indirizzo IP valido.',
    'json'                 => 'L\':attribute deve essere una stringa JSON valida.',
    'max'                  => [
        'numeric' => 'L\':attribute non può essere maggiore di :max.',
        'file'    => 'L\':attribute non può essere maggiore di :max kilobyte.',
        'string'  => 'L\':attribute non può essere maggiore di :max caratteri.',
        'array'   => 'L\':attribute non può avere più di :max articoli.',
    ],
    'mimes'                => 'L\':attribute deve essere un file di tipo: :values.',
    'mimetypes'            => 'L\':attribute deve essere un file di tipo: :values.',
    'min'                  => [
        'numeric' => 'L\':attribute deve essere almeno :min.',
        'file'    => 'L\':attribute deve essere almeno :min kilobyte.',
        'string'  => 'L\':attribute deve essere almeno :min caratteri.',
        'array'   => 'L\':attribute deve avere almeno :min elementi.',
    ],
    'not_in'               => 'L\':attribute selezionato non è valido.',
    'numeric'              => 'L\':attribute deve essere un numero.',
    'present'              => 'Il campo dell\':attribute deve essere presente.',
    'regex'                => 'Il formato dell\':attribute non è valido.',
    'required'             => 'Il campo dell\':attribute è obbligatorio.',
    'required_if'          => 'Il campo dell\':attribute è obbligatorio quando :other è :value.',
    'required_unless'      => 'Il campo dell\':attribute è obbligatorio a meno che :other sia in :values.',
    'required_with'        => 'Il campo dell\':attribute è obbligatorio quando :values sono presenti.',
    'required_with_all'    => 'Il campo dell\':attribute è obbligatorio quando :values sono presenti.',
    'required_without'     => 'Il campo dell\':attribute è obbligatorio quando :values non sono presenti.',
    'required_without_all' => 'Il campo dell\':attribute è obbligatorio quando nessuno dei :values è presenti.',
    'same'                 => 'L\':attribute e :other devono corrispondere.',
    'size'                 => [
        'numeric' => 'L\':attribute deve essere :size.',
        'file'    => 'L\':attribute deve essere :size kilobytes.',
        'string'  => 'L\':attribute deve essere :size caratteri.',
        'array'   => 'L\':attribute deve contenere :size elementi.',
    ],
    'string'               => 'L\':attribute deve essere una stringa.',
    'timezone'             => 'L\':attribute deve essere una zona valida.',
    'unique'               => 'L\':attribute è stato già usato.',
    'uploaded'             => 'Non è possibile caricare l\':attribute.',
    'url'                  => 'Il formato dell\':attribute non è valido.',

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
            'rule-name' => 'Messaggio-cliente',
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
