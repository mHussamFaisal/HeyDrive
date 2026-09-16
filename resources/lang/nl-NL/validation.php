<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dutch (nl-NL) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute moet worden geaccepteerd.',
    'active_url' => ':attribute is geen geldige URL.',
    'after' => ':attribute moet een datum na zijn :date.',
    'alpha' => ':attribute mag alleen letters bevatten.',
    'alpha_dash' => ':attribute mag alleen letters, cijfers en streepjes bevatten.',
    'alpha_num' => ':attribute mag alleen letters en cijfers bevatten.',
    'array' => ':attribute moet een array zijn.',
    'before' => ':attribute moet een datum vóór :date zijn',
    'between' => [
        'numeric' => ':attribute moet tussen :min en :max zijn.',
        'file' => ':attribute moet minimaal :min en maximaal :max kilobytes groot zijn.',
        'string' => ':attribute moet minimaal :min en maximaal :max tekens lang zijn.',
        'array' => ':attribute moet minimaal :min en maximaal :max items hebben.',
    ],
    'boolean' => 'Veld :attribute  moet waar of onwaar zijn.',
    'confirmed' => 'Bevestiging :attribute komt niet overeen.',
    'date' => ':attribute is geen geldige datum.',
    'date_format' => ':attribute  komt niet overeen met het formaat :format.',
    'different' => ':attribute en :other moeten van elkaar verschillen.',
    'digits' => ':attribute moet :digits cijfers lang zijn.',
    'digits_between' => ':attribute moet minimaal :min en maximaal :max cijfers lang zijn.',
    'dimensions' => ':attribute heeft ongeldige afmetingen.',
    'distinct' => 'Veld :attribute bevat een dubbele waarde.',
    'email' => ':attribute moet een geldig e-mailadres zijn.',
    'exists' => 'Het geselecteerde :attribute is ongeldig.',
    'file' => ':attribute moet een bestand zijn.',
    'filled' => 'Veld :attribute is vereist.',
    'image' => ':attribute moet een afbeelding zijn.',
    'in' => 'Het geselecteerde :attribute is ongeldig.',
    'in_array' => 'Veld :attribute bestaat niet in :other.',
    'integer' => ':attribute moet een geheel getal zijn.',
    'ip' => ':attribute moet een geldig IP-adres zijn.',
    'json' => ':attribute moet een geldige JSON-string zijn.',
    'max' => [
        'numeric' => ':attribute mag niet groter zijn dan :max.',
        'file' => ':attribute mag niet groter zijn dan :max kilobytes.',
        'string' => ':attribute mag niet langer zijn dan :max tekens.',
        'array' => ':attribute mag niet meer dan :max items hebben.',
    ],
    'mimes' => ':attribute moet een bestand zijn van type: :values.',
    'mimetypes' => ':attribute moet een bestand zijn van type: :values.',
    'min' => [
        'numeric' => ':attribute moet tenminste :min zijn.',
        'file' => ':attribute moet tenminste :min kilobytes groot zijn.',
        'string' => ':attribute moet tenminste :min tekens lang zijn.',
        'array' => ':attribute moet tenminste :min items hebben.',
    ],
    'not_in' => 'Het geselecteerde :attribute is ongeldig.',
    'numeric' => 'Het :attribute moet een getal zijn.',
    'present' => 'Veld :attribute moet aanwezig zijn.',
    'regex' => 'Formaat :attribute is ongeldig.',
    'required' => 'Veld :attribute is vereist.',
    'required_if' => 'Veld :attribute is vereist wanneer :other is :value.',
    'required_unless' => 'Veld :attribute is vereist tenzij :other is in :values.',
    'required_with' => 'Veld :attribute is vereist wanneer :values aanwezig is.',
    'required_with_all' => 'Veld :attribute is vereist wanneer :values aanwezig is.',
    'required_without' => 'Veld :attribute is vereist wanneer :values niet aanwezig is.',
    'required_without_all' => 'Veld :attribute is vereist wanneer geen van :values aanwezig zijn.',
    'same' => ':attribute en :other moeten gelijk zijn.',
    'size' => [
        'numeric' => ':attribute moet :size groot zijn.',
        'file' => ':attribute moet :size kilobytes groot zijn.',
        'string' => ':attribute moet :size tekens lang zijn.',
        'array' => ':attribute moet :size items bevatten.',
    ],
    'string' => ':attribute moet een string zijn.',
    'timezone' => ':attribute moet een geldige zone zijn.',
    'unique' => ':attribute wordt reeds gebruikt.',
    'uploaded' => ':attribute kon niet geüpload worden.',
    'url' => 'Formaat :attribute is ongeldig.',

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
            'rule-name' => 'aangepast-bericht',
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
