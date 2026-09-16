<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rusian (ru-RU) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute должен быть принят.',
    'active_url'           => ':attribute не является допустимым URL.',
    'after'                => ':attribute должен быть датой после :date.',
    'alpha'                => ':attribute может содержать только буквы.',
    'alpha_dash'           => ':attribute может содержать только буквы, цифры и тире.',
    'alpha_num'            => ':attribute может содержать только буквы и цифры.',
    'array'                => ':attribute должен быть массивом.',
    'before'               => ':attribute должен быть дата до :date.',
    'between'              => [
        'numeric' => ':attribute должен быть между :min и :max.',
        'file'    => ':attribute должен быть между :min и :max килобайт.',
        'string'  => ':attribute должен быть между :min и :max.',
        'array'   => ':attribute должен находиться между :min и :max элементами.',
    ],
    'boolean'              => 'Поле :attribute должно быть истинным или ложным.',
    'confirmed'            => 'Подтверждение :attribute не соответствует.',
    'date'                 => ':attribute недействительная дата.',
    'date_format'          => ':attribute не соответствует формату :format.',
    'different'            => ':attribute и :other должны быть разными.',
    'digits'               => ':attribute должен быть :digits цифр.',
    'digits_between'       => ':attribute должен быть между :min и :max.',
    'dimensions'           => ':attribute имеет недопустимые размеры изображения.',
    'distinct'             => 'Поле :attribute имеет двойное значение.',
    'email'                => ':attribute должен быть действительным адресом электронной почты.',
    'exists'               => 'Выбранный :attribute недействителен.',
    'file'                 => ':attribute должен быть файлом.',
    'filled'               => 'Поле :attribute обязательно.',
    'image'                => ':attribute должен быть изображением.',
    'in'                   => 'Выбранный :attribute недействителен.',
    'in_array'             => 'Поле :attribute не существует в :other.',
    'integer'              => ':attribute должен быть целым числом.',
    'ip'                   => ':attribute должен быть действительным IP-адресом.',
    'json'                 => ':attribute должен быть допустимой строкой JSON.',
    'max'                  => [
        'numeric' => ':attribute не может быть больше :max',
        'file'    => ':attribute не может быть больше :max килобайт.',
        'string'  => ':attribute не может быть больше :max символов.',
        'array'   => ':attribute может быть не больше :max элементов.',
    ],
    'mimes'                => ':attribute должен быть файл типа: :values.',
    'mimetypes'            => ':attribute должен быть файл типа: :values.',
    'min'                  => [
        'numeric' => ':attribute должен быть не менее :min.',
        'file'    => ':attribute должен быть не менее :min. Килобайт.',
        'string'  => ':attribute должен быть не менее :min. Символов.',
        'array'   => ':attribute должен содержать не менее :min.',
    ],
    'not_in'               => 'Выбранный :attribute недействителен.',
    'numeric'              => ':attribute должен быть числом.',
    'present'              => 'Поле :attribute должно присутствовать.',
    'regex'                => 'Формат :attribute недействителен.',
    'required'             => 'Поле :attribute обязательно.',
    'required_if'          => 'Поле :attribute требуется, когда :other is :value.',
    'required_unless'      => 'Поле :attribute требуется, если :other находится в :value.',
    'required_with'        => 'Поле :attribute требуется, когда есть :values',
    'required_with_all'    => 'Поле :attribute требуется, когда есть :values',
    'required_without'     => 'Поле :attribute требуется, когда :values нет.',
    'required_without_all' => 'Поле :attribute требуется, если ни один из :values не присутствует.',
    'same'                 => ':attribute и :other должны совпадать',
    'size'                 => [
        'numeric' => ':attribute должен быть :size.',
        'file'    => ':attribute должен быть :size килобайт.',
        'string'  => ':attribute должен быть :size символы.',
        'array'   => ':attribute должен содержать :size размера.',
    ],
    'string'               => ':attribute должен быть строкой.',
    'timezone'             => ':attribute должен быть допустимой зоной.',
    'unique'               => ':attribute уже выполнен.',
    'uploaded'             => ':attribute не удалось загрузить.',
    'url'                  => 'Формат :attribute недействителен.',

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
