<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Czech (cs-CZ) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute musí být přijat.',
    'active_url' => ':attribute není platná adresa URL.',
    'after' => ':attribute musí být datum po :date',
    'alpha' => ':attribute může obsahovat pouze písmena.',
    'alpha_dash' => ':attribute může obsahovat pouze písmena, čísla a pomlčky.',
    'alpha_num' => ':attribute mohou obsahovat pouze písmena a čísla.',
    'array' => ':attribute musí být pole.',
    'before' => ':attribute musí být datum před :date.',
    'between' => [
        'numeric' => ':attribute musí být mezi :min a :max.',
        'file' => ':attribute musí být mezi :min a :max kilobajty.',
        'string' => ':attribute musí obsahovat mezi :min a :max znaky.',
        'array' => ':attribute musí mít mezi :min a :max položkami.',
    ],
    'boolean' => ':attribute pole musí být pravdivé nebo nepravdivé.',
    'confirmed' => ':attribute potvrzení neodpovídá.',
    'date' => ':attribute není platným datem.',
    'date_format' => ':attribute neodpovídá formátu :format.',
    'different' => ':attribute a :other musí být různé.',
    'digits' => ':attribute musí mít :digits číslic.',
    'digits_between' => ':attribute musí mít mezi :min a :max číslicemi.',
    'dimensions' => ':attribute má neplatné rozměry obrázku.',
    'distinct' => ':attribute má duplicitní hodnotu.',
    'email' => ':attribute musí být platná e-mailová adresa.',
    'exists' => 'Vybraný :attribute je neplatný.',
    'file' => ':attribute musí být soubor.',
    'filled' => ':attribute pole je povinné.',
    'image' => ':attribute musí být obrázek.',
    'in' => 'Vybraný :attribute je neplatný.',
    'in_array' => ':attribute pole neexistuje v :other.',
    'integer' => ':attribute musí být celé číslo.',
    'ip' => ':attribute musí být platnou adresou IP.',
    'json' => ':attribute musí být platný řetězec JSON.',
    'max' => [
        'numeric' => ':attribute nesmí být větší než :max.',
        'file' => ':attribute nesmí být větší než :max kilobytů.',
        'string' => ':attribute nesmí mít více než :max znaků.',
        'array' => ':attribute nesmí mít více než :max položky.',
    ],
    'mimes' => ':attribute musí být soubor typu: :values.',
    'mimetypes' => ':attribute musí být soubor typu: :values.',
    'min' => [
        'numeric' => ':attribute musí mít nejméně :min.',
        'file' => ':attribute musí mít nejméně :min kilobytů.',
        'string' => ':attribute musí mít nejméně :min znaků.',
        'array' => ':attribute musí mít nejméně :min položek.',
    ],
    'not_in' => 'Vybraný :attribute je neplatný.',
    'numeric' => ':attribute musí být číslo.',
    'present' => 'Musí být k dispozici :attribute pole.',
    'regex' => 'Formát :attribute je neplatný.',
    'required' => 'Pole :attribute je povinné.',
    'required_if' => 'Pole :attribute je povinné, pokud :other je :value.',
    'required_unless' => 'Pole :attribute je povinné, až na případ, kdy :other je v :values.',
    'required_with' => 'Pole :attribute je požadováno, když jsou :values přítomny.',
    'required_with_all' => 'Pole :attribute je požadováno, když jsou :values přítomny.',
    'required_without' => 'Pole :attribute je požadováno, když nejsou :values přítomny.',
    'required_without_all' => 'Pole :attribute je požadováno, když nejsou přítomny žádné z :values.',
    'same' => ':attribute a :other musí být stejné.',
    'size' => [
        'numeric' => ':attribute musí být :size.',
        'file' => ':attribute musí mít :size kilobytů.',
        'string' => ':attribute musí mít :size kilobytů.',
        'array' => ':attribute musí obsahovat :size položek.',
    ],
    'string' => ':attribute musí být řetězec.',
    'timezone' => ':attribute musí být platnou zónou.',
    'unique' => ':attribute je již zabrán.',
    'uploaded' => ':attribute se nepodařilo nahrát.',
    'url' => 'Formát :attribute je neplatný.',

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
            'rule-name' => 'vlastní zpráva',
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
