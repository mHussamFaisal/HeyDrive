<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spanish (es-ES) - Common
    |--------------------------------------------------------------------------
    |
    | Used globally across application.
    |
    */

    'user_role_options' => [
        'customer' => 'Cliente',
        'driver' => 'Conductor',
        'admin' => 'Adminitrador',
    ],
    'user_status_options' => [
        'approved' => 'Aprobado',
        'awaiting_admin_review' => 'Esperando aprobacion',
        'awaiting_email_confirmation' => 'Esperando confirmacion de correo electronico',
        'inactive' => 'Inactivo',
        'rejected' => 'Rechazado',
    ],
    'user_profile_type_options' => [
        'private' => 'Privado',
        'company' => 'Empresa',
    ],
    'vehicle_status_options' => [
        'activated' => 'Activado',
        'inactive' => 'Inactivo',
    ],
    'service_status_options' => [
        'activated' => 'Activado',
        'inactive' => 'Inactivo',
    ],
    'event_status_options' => [
        'active' => 'Disponible',
        'inactive' => 'Ocupado',
    ],
    'transaction_status_options' => [
        'pending' => 'Esperando',
        'paid' => 'Pagado',
        'refunded' => 'Reintegrado',
        'declined' => 'Rechazado',
        'canceled' => 'Cancelado',
        'authorised' => 'Autorizado',
    ],
    'booking_status_options' => [
        'pending' => 'Confirmado', // Esperando
        'confirmed' => 'Confirmado',
        'assigned' => 'Asignado',
        'auto_dispatch' => 'Auto Dispatch',
        'accepted' => 'Aceptado',
        'rejected' => 'Rechazado',
        'onroute' => 'En ruta',
        'arrived' => 'Llegado',
        'onboard' => 'A bordo',
        'completed' => 'Completado',
        'canceled' => 'Cancelado',
        'incomplete' => 'Incompleto',
        'requested' => 'Pedido',
        'quote' => 'Presupuesto',
    ],
    'loading' => 'Cargando...',
    'loading_warning' => 'Si lo carga tarda demasiado, espera algunos segundos más y, si no ocurre nada, intenta volver a cargar la página.',
    'loading_reload' => 'CARGA AHORA',
    'powered_by' => 'Desarrollado por',

];
