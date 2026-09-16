<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hungarian (hu-HU) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'A :attribute el kell fogadni.',
    'active_url'           => 'A :attribute nem érvényes URL.',
    'after'                => 'A :attribute dátumnak kell lennie a :date után.',
    'alpha'                => 'A :attribute csak betűket tartalmazhat.',
    'alpha_dash'           => 'A :attribute csak betűket, számokat és gondolatjeleket tartalmazhat.',
    'alpha_num'            => 'A :attribute csak betűket és számokat tartalmazhat.',
    'array'                => 'A :attribute tömbnek kell lennie.',
    'before'               => 'A :attribute dátumnak kell lennie a :date után.',
    'between'              => [
        'numeric' => 'A :attribute :min és :max között kell lennie.',
        'file'    => 'A :attribute :min és :max kilobytes között kell lennie.',
        'string'  => 'A :attribute :min és :max között kell lennie.',
        'array'   => 'A :attribute rendelkeznie kell :min és :max közötti elemekkel.',
    ],
    'boolean'              => 'A :attribute mezőnek igaz vagy hamisnak kell lennie.',
    'confirmed'            => 'A :attribute visszaigazolás nem egyezik.',
    'date'                 => 'A :attribute nem érvényes dátum.',
    'date_format'          => 'A :attribute nem egyezik meg a :format formátummal.',
    'different'            => 'A :attribute és :other nak különböznie kell.',
    'digits'               => 'A :attribute :digits számnak kell lennie.',
    'digits_between'       => 'A :attribute :min és :max szám között kell lennie.',
    'dimensions'           => 'A :attribute a kér méretei nem érvényesek.',
    'distinct'             => 'A :attribute mező duplikált értéket tartalmaz.',
    'email'                => 'A :attribute érvényes e-mail címnek kell lennie.',
    'exists'               => 'A :attribute nem érvényes.',
    'file'                 => 'A :attribute fájlnak kell lennie.',
    'filled'               => 'A :attribute mező szükséges.',
    'image'                => 'A :attribute képnek kell lennie.',
    'in'                   => 'A :attribute nem érvényes.',
    'in_array'             => 'A :attribute nem létező mező a :other ban.',
    'integer'              => 'A :attribute egész számnak kell lennie.',
    'ip'                   => 'A :attribute érvényes IP címnek kell lennie.',
    'json'                 => 'A :attribute érvényes JSON karakterláncnak kell lennie.',
    'max'                  => [
        'numeric' => 'A :attribute nem lehet nagyobb mint :max.',
        'file'    => 'A :attribute nem lehet nagyobb mint :max kilobytes.',
        'string'  => 'A :attribute nem lehet nagyobb mint :max karakterek.',
        'array'   => 'A :attribute nek nem lehet több mint :max elemei.',
    ],
    'mimes'                => 'A :attribute a következő típusú fájlnak kell lennie :values.',
    'mimetypes'            => 'A :attribute a következő típusú fájlnak kell lennie :values.',
    'min'                  => [
        'numeric' => 'A :attribute legalább :min nak kell lennie.',
        'file'    => 'A :attribute legalább :min kilobytenak kell lennie.',
        'string'  => 'A :attribute legalább :min karakternek kell lennie.',
        'array'   => 'A :attribute rendelkeznie kell legalább :min elemekkel.',
    ],
    'not_in'               => 'A kiválasztott :attribute érvénytelen.',
    'numeric'              => 'A :attribute számnak kell lennie.',
    'present'              => 'A :attribute mezőnek jelen kell lennie.',
    'regex'                => 'A :attribute formátum nem érvényes.',
    'required'             => 'A :attribute mező szükséges.',
    'required_if'          => 'A :attribute mező akkor szükséges ha az :other egy :value.',
    'required_unless'      => 'A :attribute mezőt csak akkor kell megadni ha a :other :values ben van.',
    'required_with'        => 'A :attribute mező akkor szükséges, ha :value jelen van.',
    'required_with_all'    => 'A :attribute mező akkor szükséges, ha :value jelen van.',
    'required_without'     => 'A :attribute mező akkor szükséges, ha :value nincs jelen.',
    'required_without_all' => 'A :attribute mező akkor szükséges, ha egyik :values sincs jelen.',
    'same'                 => 'A :attribute és :other meg kell egyeznie.',
    'size'                 => [
        'numeric' => 'A :attribute :size nak kell lennie.',
        'file'    => 'A :attribute :size kilobytenak kell lennie.',
        'string'  => 'A :attribute :size karakternek kell lennie.',
        'array'   => 'A :attribute :size elemeket kell tartalmaznia.',
    ],
    'string'               => 'A :attribute karakterláncnak kell lennie.',
    'timezone'             => 'A :attribute érvényes zónának kell lennie.',
    'unique'               => 'A :attribute már foglalt.',
    'uploaded'             => 'A :attribute nem sikerült feltölteni.',
    'url'                  => 'A :attribute formátuma érvénytelen.',

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
