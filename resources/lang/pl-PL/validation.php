<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Polish (pl-PL) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute musi zostać zaakceptowany',
    'active_url'           => ':attribute nie jest prawidłowym adresem.',
    'after'                => ':attribute musi być datą późniejszą niż :date.',
    'alpha'                => 'W :attribute można używać tylko liter.',
    'alpha_dash'           => ':attribute może zawierać tylko litery, numery i znaki specjalne.',
    'alpha_num'            => ':attribute może zawierać tylko litery i numery.',
    'array'                => ':attribute musi być szeregiem.',
    'before'               => ':attribute musi być datą przed :date.',
    'between'              => [
        'numeric' => ':attribute może być wartością między :min a :max.',
        'file'    => ':attribute może mieć rozmiar między :min a :max.',
        'string'  => ':attribute musi mięc między :min a :max znaków.',
        'array'   => ':attribute musi zawierać między :min a :max pozycji.',
    ],
    'boolean'              => 'W polu :attribute musisz zaznaczyć prawdę lub fałsz.',
    'confirmed'            => 'Potwierdzenie :attribute jest nieprawidłowo.',
    'date'                 => ':attribute nie jest prawidłową datą.',
    'date_format'          => ':attribute nie odpowiada formatowi :format.',
    'different'            => ':attribute i :other musi się od siebie różnić.',
    'digits'               => ':attribute musi składać się z :digits cyfr.',
    'digits_between'       => ':attribute musi składać się z od :min do :max cyfr.',
    'dimensions'           => ':attribute ma nieprawidłowe wymiary obrazka.',
    'distinct'             => 'W polu :attribute powtarzają się te same wartości.',
    'email'                => ':attribute musi być prawidłowym adresem e-mail.',
    'exists'               => 'Wybrany :attribute jest nieprawidłowy.',
    'file'                 => ':attribute musi być plikiem.',
    'filled'               => 'Pole :attribute jest wymagane.',
    'image'                => ':attribute musi być plikiem graficznym',
    'in'                   => 'Wybrany :attribute jest nieprawidłowy.',
    'in_array'             => 'Pole :attribute nie istnieje w :other.',
    'integer'              => ':attribute musi być liczbą całkowitą.',
    'ip'                   => ':attribute musi być prawidłowym adresem IP.',
    'json'                 => ':attribute musi być prawidłowym ciągiem JSON.',
    'max'                  => [
        'numeric' => ':attribute nie może być większy niż :max.',
        'file'    => ':attribute nie może być większy niż :max kilobajtów.',
        'string'  => ':attribute nie może być dłuższy niż :max znaków.',
        'array'   => ':attribute nie może zawierać więcej niż :max pozycji.',
    ],
    'mimes'                => ':attribute musi być plikiem typu :values.',
    'mimetypes'            => ':attribute musi być plikiem typu :values.',
    'min'                  => [
        'numeric' => ':attribute musi wynosić co najmniej :min.',
        'file'    => ':attribute musi mieć co najmniej :min kilobajtów.',
        'string'  => ':attribute musi składać się z :min znaków..',
        'array'   => ':attribute musi mieć co najmniej :min przedmiotów.',
    ],
    'not_in'               => 'Wybrany :attribute jest nieprawidłowy.',
    'numeric'              => ':attribute musi być liczbą.',
    'present'              => 'Pole :attribute musi być wypełnione.',
    'regex'                => 'Format :attribute jest nieprawidłowy.',
    'required'             => 'Pole :attribute jest wymagane.',
    'required_if'          => 'Pole :attribute jest wymagane kiedy :other ma wartość :value.',
    'required_unless'      => 'Pole :attribute jest wymagane, chyba, że :other jest w :value.',
    'required_with'        => 'Pole :attribute jest wymagane, gdy :values są obecne.',
    'required_with_all'    => 'Pole :attribute jest wymagane, gdy :values są obecne.',
    'required_without'     => 'Pole :attribute jest wymagane, gdy :values nie jest obecne.',
    'required_without_all' => 'Pole :attribute jest wymagane, gdy żadne z :values nie są obecne.',
    'same'                 => 'Pola :attribute i :other muszą się zgadzać.',
    'size'                 => [
        'numeric' => ':attribute musi być rozmiaru :size.',
        'file'    => ':attribute musi wynosić :size kilobajtów.',
        'string'  => ':attribute musi mieć :size znaków.',
        'array'   => ':attribute musi zawierać :size przedmiotów.',
    ],
    'string'               => ':attribute musi być ciągiem.',
    'timezone'             => ':attribute musi być prawidłową strefą.',
    'unique'               => ':attribute jest już zajęty.',
    'uploaded'             => ':attribute nie został wczytany.',
    'url'                  => 'Format :attribute jest nieprawidłowy.',

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
