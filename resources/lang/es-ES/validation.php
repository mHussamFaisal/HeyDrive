<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spanish (es-ES) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'El :attribute debe ser aceptado.',
    'active_url'           => 'El :attribute no tiene una URL válida.',
    'after'                => 'La fecha del :attribute debe ser después de :date.',
    'alpha'                => 'El :attribute debe contener únicamente letras.',
    'alpha_dash'           => 'El :attribute debe contener únicamente letras, números y guiones.',
    'alpha_num'            => 'El :attribute debe contener únicamente letras y números.',
    'array'                => 'El :attribute debe ser una serie.',
    'before'               => 'La fecha del :attribute debe ser una fecha anterior a :date.',
    'between'              => [
        'numeric' => 'El :attribute debe estar comprendido entre :min y :max.',
        'file'    => 'El :attribute debe estar comprendido entre :min y :max kilobyte.',
        'string'  => 'El :attribute debe estar comprendido entre :min y :max caracteres.',
        'array'   => 'El :attribute debe estar comprendido entre :min y :max elementos.',
    ],
    'boolean'              => 'El campo del :attribute debe ser verdadero o falso.',
    'confirmed'            => 'La confirmación del :attribute no corresponde.',
    'date'                 => 'El :attribute no es una fecha válida.',
    'date_format'          => 'El :attribute no corresponde con el formato :format.',
    'different'            => 'El :attribute y :other deben ser diferentes.',
    'digits'               => 'El :attribute debe tener :digits cifras.',
    'digits_between'       => 'El :attribute debe estar comprendido entre :min y :max cifras.',
    'dimensions'           => 'El :attribute tiene dimensiones de imagen  no válidas.',
    'distinct'             => 'El campo del :attribute tiene un valor duplicado.',
    'email'                => 'El :attribute debe ser una dirección de correo electrónico válida.',
    'exists'               => 'El :attribute seleccionado no es válido.',
    'file'                 => 'El :attribute debe ser un archivo.',
    'filled'               => 'El campo del :attribute es obligatorio.',
    'image'                => 'El :attribute debe ser una imagen.',
    'in'                   => 'El :attribute seleccionado no es válido.',
    'in_array'             => 'El campo del :attribute no existe en :other.',
    'integer'              => 'El :attribute debe ser un número entero.',
    'ip'                   => 'El :attribute debe ser una dirección IP válida.',
    'json'                 => 'El :attribute debe ser una cadena JSON.',
    'max'                  => [
        'numeric' => 'El :attribute no puede ser mayor que :max.',
        'file'    => 'El :attribute no puede ser mayor que :max kilobyte.',
        'string'  => 'El :attribute no puede ser mayor que :max caracteres.',
        'array'   => 'El :attribute no puede tener más de :max artículos.',
    ],
    'mimes'                => 'El :attribute debe ser un archivo de tipo: :values.',
    'mimetypes'            => 'El :attribute debe ser un archivo de tipo: :values.',
    'min'                  => [
        'numeric' => 'El :attribute debe ser al menos :min.',
        'file'    => 'El :attribute debe ser de al menos :min kilobyte.',
        'string'  => 'El :attribute debe ser de al menos :min caracteres.',
        'array'   => 'El :attribute debe ser de al menos :min elementos.',
    ],
    'not_in'               => 'El :attribute seleccionado no es válido.',
    'numeric'              => 'El :attribute debe ser un número.',
    'present'              => 'El campo del :attribute debe estar presente.',
    'regex'                => 'El formato del :attribute no es válido.',
    'required'             => 'El campo del :attribute es obligatorio.',
    'required_if'          => 'El campo del :attribute es obligatorio.',
    'required_unless'      => 'El campo del :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El campo del :attribute es obligatorio cuando :values están presentes.',
    'required_with_all'    => 'El campo del :attribute es obligatorio cuando :values están presentes.',
    'required_without'     => 'El campo del :attribute es obligatorio cuando :values no están presentes.',
    'required_without_all' => 'El campo del :attribute es obligatorio cuando ninguno de los :values están presentes.',
    'same'                 => 'El :attribute y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'El :attribute debe ser :size.',
        'file'    => 'El :attribute debe ser de :size kilobyte.',
        'string'  => 'El :attribute debe ser de :size caracteres.',
        'array'   => 'El :attribute debe ser de :size elementos.',
    ],
    'string'               => 'El :attribute debe ser una cadena.',
    'timezone'             => 'El :attribute debe ser una zona valida.',
    'unique'               => 'El :attribute ya fue usado.',
    'uploaded'             => 'No es posible cargar el :attribute.',
    'url'                  => 'El formato del :attribute no es válido.',

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
