<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rusian (ru-RU) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Клиент',
        'driver' => 'Водитель',
        'admin' => 'Администратор',
    ],
    'user_status_options' => [
        'approved' => 'Подтверждённый',
        'awaiting_admin_review' => 'В ожидании рассмотрения',
        'awaiting_email_confirmation' => 'В ожидании подтверждения на e-mail',
        'inactive' => 'Неактивный',
        'rejected' => 'Отвёргнутый',
    ],
    'user_profile_type_options' => [
        'private' => 'Личный',
        'company' => 'Компания',
    ],
    'vehicle_status_options' => [
        'activated' => 'Активирован',
        'inactive' => 'Неактивный',
    ],
    'service_status_options' => [
        'activated' => 'Активирован',
        'inactive' => 'Неактивный',
    ],
    'event_status_options' => [
        'active' => 'Активирован',
        'inactive' => 'Неактивный',
    ],
    'transaction_status_options' => [
        'pending' => 'В ожидании',
        'paid' => 'Оплаченный',
        'refunded' => 'Возвращено',
        'declined' => 'Отклонено',
        'canceled' => 'Отменен',
        'authorised' => 'Авторизованный',
    ],
    'booking_status_options' => [
        'pending' => 'Подтвердил', // В ожидании
        'confirmed' => 'Подтвердил',
        'assigned' => 'Назначенный',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Работа Принимается',
        'rejected' => 'Отказ от работы',
        'onroute' => 'На маршруте',
        'arrived' => 'Прибывший',
        'onboard' => 'На борту',
        'completed' => 'Завершенный',
        'canceled' => 'Отменен',
        'incomplete' => 'Незавершенный',
        'requested' => 'Запрошенный',
        'quote' => 'Котировка',
    ],
    'loading' => 'Загружается...',
    'loading_warning' => 'Загрузка его занимает слишком много времени, подождите несколько секунд, и если ничего не произойдет, попробуйте перезагрузить страницу.',
    'loading_reload' => 'ЗАГРУЗИТЬ СЕЙЧАС',
    'powered_by' => 'Разработанный',

];
