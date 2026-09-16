<?php

return [

    /*
    |--------------------------------------------------------------------------
    | French (fr-FR) - Validation
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => "L' :attribute doit être accepté.",
    'active_url'           => "L' :attribute n'est pas une URL valide.",
    'after'                => "L' :attribute doit être une date après :date.",
    'alpha'                => "L' :attribute peut contenir uniquement des lettres.",
    'alpha_dash'           => "L' :attribute peut contenir uniquement des lettres, des chiffres et des tirets.",
    'alpha_num'            => "L' :attribute peut contenir uniquement des lettres et des chiffres.",
    'array'                => "L' :attribute doit être un tableau.",
    'before'               => "L' :attribute doit être une date avant :date.",
    'between'              => [
        'numeric' => "L' :attribute doit être compris entre :min et :max.",
        'file'    => "L' :attribute doit être compris entre :min et :max kilobytes",
        'string'  => "L' :attribute doit être compris entre :min et :max items.",
        'array'   => "L' :attribute doit avoir entre :min et :max items.",
    ],
    'boolean'              => "L' :attribute champ doit avoir la valeur vrai ou faux.",
    'confirmed'            => "L' :attribute confirmation ne correspond pas.",
    'date'                 => "L' :attribute n'a pas une date valide.",
    'date_format'          => "L' :attribute ne correspond pas au format :format.",
    'different'            => "L' :attribute et :other doit être différent.",
    'digits'               => "L' :attribute doit être :digits digits.",
    'digits_between'       => "L' :attribute doit être compris entre :min et :max digits.",
    'dimensions'           => "L' :attribute a des dimensions d'image non valides.",
    'distinct'             => "L' :attribute champ a une valeur dupliquée.",
    'email'                => "L' :attribute doit être une adresse de messagerie valide.",
    'exists'               => "L' :attribute sélectionné n'est pas valide.",
    'file'                 => "L' :attribute doit être un fichier.",
    'filled'               => "L' :attribute champ est requis.",
    'image'                => "L' :attribute doit être une image.",
    'in'                   => "L' :attribute sélectionné n'est pas valide.",
    'in_array'             => "L' :attribute champ n'existe pas dans :other.",
    'integer'              => "L' :attribute doit être un entier.",
    'ip'                   => "L' :attribute doit être une adresse IP valide.",
    'json'                 => "L' :attribute doit être une chaîne JSON valide.",
    'max'                  => [
        'numeric' => "L' :attribute peut ne pas être supérieur à :max.",
        'file'    => "L' :attribute peut ne pas être supérieur à :max kilobytes.",
        'string'  => "L' :attribute peut ne pas être supérieur à :max characters.",
        'array'   => "L' :attribute peut ne pas avoir plus de :max items.",
    ],
    'mimes'                => "L' :attribute doit être un fichier de type: :values.",
    'mimetypes'            => "L' :attribute doit être un fichier de type: :values.",
    'min'                  => [
        'numeric' => "L' :attribute doit être au moins :min.",
        'file'    => "L' :attribute doit être au moins :min kilo-octets.",
        'string'  => "L' :attribute doit être au moins :min characters.",
        'array'   => "L' :attribute doit avoir au moins :min items.",
    ],
    'not_in'               => "Le sélectionné :attribute est invalide",
    'numeric'              => "L' :attribute doit être un nombre.",
    'present'              => "L' :attribute champ doit être présent.",
    'regex'                => "L' :attribute format est invalide.",
    'required'             => "L' :attribute champ est requis.",
    'required_if'          => "L' :attribute champ est requis lorsque :other est :value.",
    'required_unless'      => "L' :attribute champ est obligatoire à moins que :other est dans :values.",
    'required_with'        => "L' :attribute champ est obligatoire lorsque :values est présent.",
    'required_with_all'    => "L' :attribute champ est obligatoire lorsque :values est présent.",
    'required_without'     => "L' :attribute champ est obligatoire lorsque :values n'est pas présent.",
    'required_without_all' => "L' :attribute champ est requis lorsqu'aucune des :values sont présents.",
    'same'                 => "L' :attribute et :other doit correspondre",
    'size'                 => [
        'numeric' => "L' :attribute doit être :size.",
        'file'    => "L' :attribute doit être :size kilobytes.",
        'string'  => "L' :attribute doit être :size characters.",
        'array'   => "L' :attribute doit contenir :size items.",
    ],
    'string'               => "L' :attribute doit être une chaîne de caractère.",
    'timezone'             => "L' :attribute doit être une zone valide.",
    'unique'               => "L' :attribute a déjà été pris.",
    'uploaded'             => "L' :attribute n'a pas pu être téléchargé.",
    'url'                  => "L' :attribute format est invalide.",

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
