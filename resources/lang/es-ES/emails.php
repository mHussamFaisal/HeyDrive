<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spanish (es-ES) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Detalles de la reserva :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Aquí están los detalles de su reserva.',
        'note_line1' => 'Mantenga siempre nuestros números de teléfonos a mano, los puede necesitar en caso de no encontrar al conductor, si a la hora de su llegada nuestras oficinas estan cerradas le facilitaremos el numero del telefono del conductor.',
        'note_line2' => 'Hacer reservas directas con el chofer es ilegal. En caso de accidente no será cubierto por el seguro.',
        'note_line3' => 'Para recogidas en el aeropuerto, el conductos estará esperando al final de la terminal (punto de encuentro) con un cartel con su nombre (por favor encienda su teléfono móvil una vez haya aterrizado). Cuando la recogida es en una dirección, el conductor esperará en la puerta. Si hay restricciones de estacionamiento, estará esperando cerca de la dirección (en el lugar más cercano posible).',
        'section' => [
            'journey_details' => 'Detalles de su viaje',
            'customer_details' => 'Detalles del cliente',
            'lead_passenger_details' => 'Pasajero principal',
            'reservation_details' => 'Detalles de la reserva',
            'general_details' => 'Detalles generales de la reserva',
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Actualización de estado :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Su estado de reserva :ref_number ha cambiado a :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Detalles del conductor :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Encuentre por favor los detalles de su conductor abajo.',
        'booking' => 'Reserva',
        'driver' => 'Conductor',
        'vehicle' => 'Vehículo',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Estado del conductor :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Su conductor está en camino.',
        'booking' => 'Reserva',
        'driver' => 'Conductor',
        'vehicle' => 'Vehículo',
    ],
    'customer_booking_completed' => [
        'subject' => 'Comentarios :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Gracias por utilizar :company_name para realizar su viaje.',
        'line2' => 'Nos encantaría saber su opinión en cuanto a la reserva :ref_number que recientemente hizo con nosotros.',
        'line3' => 'Para dejarnos su opinión, por favor haga clic en el :link. Muchísimas Gracias',
        'link' => 'enlace',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Solicitud de presupuesto',
        'greeting' => 'Estimado :Name',
        'line1' => 'Gracias por solicitar el presupuesto. Nos pondremos en contacto con usted en breve.',
        'line2' => 'Mientras tanto, aquí hay un resumen de los detalles que ha solicitado.',
        'section' => [
            'journey_details' => 'Detalles de su viaje',
            'customer_details' => 'Detalles del cliente',
            'lead_passenger_details' => 'Pasajero principal',
            'general_details' => 'Detalles generales',
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Solicitud de pago :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Con el fin de asegurar su viaje por favor haga el pago de :price.',
        'line2' => 'Usted puede hacer el pago haciendo clic en este :link.',
        'link' => 'enlace',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Confirmación de pago :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'A continuación encontrará la factura adjunta.',
        'line2' => 'Gracias por reserva con :company_name.',
        'line3' => 'El pago de la reserva :ref_number ha sido :status.',
        'status' => 'ACEPTADO Y CONFIRMADO'
    ],
    'customer_account_activation' => [
        'subject' => 'Activación',
        'greeting' => 'Estimado :Name',
        'line1' => 'Aquí está su enlace de activación.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Bienvenido',
        'greeting' => 'Estimado :Name',
        'line1' => 'Su cuenta ha sido activado con éxito. Ahora usted puede iniciar sesión!',
    ],
    'customer_account_password' => [
        'subject' => 'Restablecer la contraseña',
        'greeting' => 'Estimado :Name',
        'line1' => 'Aquí está su ficha para restablecer la contraseña: :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Estado de la reserva actualizado :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Reserva :ref_number ha cambiado su estado a :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Cancelación de la reserva :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Cliente :customer_name ha cancelado la reserva de :ref_number en :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Estado de la reserva actualizado :ref_number',
        'greeting' => 'Estimado :Name',
        'line1' => 'Reserva :ref_number ha cambiado su estado de :old_status a :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'Correo electronico',
        'site' => 'Sitio web',
        'feedback' => 'Puede dejarnos sus comentarios :link',
        'feedback_link' => 'aquí',
    ],
    'powered_by' => 'Desarrollado por',

];
