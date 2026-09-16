<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portuguese (pt-BR) - Email
    |--------------------------------------------------------------------------
    |
    | Used in email only.
    |
    */

    // Customer
    'customer_booking_created' => [
        'subject' => 'Detalhes da reserva :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Aqui estão seus dados de reserva.',
        'note_line1' => 'Se você tiver qualquer dificuldade para localizar o motorista, por favor ligar imediatamente para nós :company_phone. Para outra reserva por favor entrar em contato com algum membro interno da empresa, por e-mail ou telefone.',
        'note_line2' => 'As reservas feitas diretamente com os motoristas são ilegais, e em caso de acidente você e o motorista não serão cobertos pelo seguro e poderão ser processados pelo TFL.',
        'note_line3' => 'Nosso motorista estará aguardando no desembarque com uma placa em seu nome.',
        'section' => [
            'journey_details' => 'Detalhes da viagem',
            'customer_details' => 'Detalhes do cliente',
            'lead_passenger_details' => 'Passageiros adicional',
            'reservation_details' => 'Detalhes da reserva',
            'general_details' => 'Dados geral da reserva'
        ],
    ],
    'customer_booking_changed' => [
        'subject' => 'Atualização de status :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Sua reserva :ref_number foi alterada para :status.',
    ],
    'customer_booking_driver' => [
        'subject' => 'Detalhes do motorista :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Por favor, encontre os detalhes do seu motorista abaixo.',
        'booking' => 'Reserva',
        'driver' => 'Motorista',
        'vehicle' => 'Veículo',
    ],
    'customer_booking_onroute' => [
        'subject' => 'Status do driver :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Seu motorista está a caminho.',
        'booking' => 'Reserva',
        'driver' => 'Motorista',
        'vehicle' => 'Veículo',
    ],
    'customer_booking_completed' => [
        'subject' => 'Comentários :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Obrigado por usar :company_name para completar a sua viagem',
        'line2' => 'Gostaríamos muito de ouvir seus comentários em relação à reserva que :ref_number recentemente fez conosco.',
        'line3' => 'Para deixar o seu comentario, por favor clique neste :link.',
        'link' => 'ligação',
    ],
    'customer_booking_quoted' => [
        'subject' => 'Solicitação de cotação',
        'greeting' => 'Caro :Name',
        'line1' => 'Obrigado por solicitar um orçamento. Entraremos em contato em breve.',
        'line2' => 'Entretanto, aqui está um resumo dos detalhes que você enviou para cotação.',
        'section' => [
            'journey_details' => 'Detalhes da viagem',
            'customer_details' => 'Detalhes do cliente',
            'lead_passenger_details' => 'Passageiros adicional',
            'general_details' => 'Dados geral da reserva'
        ],
    ],
    'customer_payment_requested' => [
        'subject' => 'Pedido de Pagamento :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'A fim de garantir a sua jornada por favor, faça um pagamento de :price.',
        'line2' => 'Você pode fazer um pagamento, clicando neste :link.',
        'link' => 'link do',
    ],
    'customer_payment_confirmed' => [
        'subject' => 'Confirmação de pagamento :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Por favor, encontre a fatura anexa abaixo.',
        'line2' => 'Obrigado por reservar com :company_name.',
        'line3' => 'O pagamento para a reserva :ref_number foi :status.',
        'status' => 'aceito e confirmad',
    ],
    'customer_account_activation' => [
        'subject' => 'Ativação',
        'greeting' => 'Caro :Name',
        'line1' => 'Aqui está o link de activação.',
    ],
    'customer_account_welcome' => [
        'subject' => 'Bem vinda',
        'greeting' => 'Caro :Name',
        'line1' => 'Sua conta foi ativada com sucesso. Você pode acessar agora!',
    ],
    'customer_account_password' => [
        'subject' => 'Mudar senha',
        'greeting' => 'Caro :Name',
        'line1' => 'Aqui está seu token de redefinição da senha: :token',
    ],

    // Driver
    'driver_booking_changed' => [
        'subject' => 'Job status update :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Job :ref_number status has changed to :status.',
    ],

    // Admin
    'admin_booking_canceled' => [
        'subject' => 'Cancelamento de reserva :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Cliente :customer_name cancelou reserva :ref_number em :date.',
    ],
    'admin_booking_changed' => [
        'subject' => 'Job status update :ref_number',
        'greeting' => 'Caro :Name',
        'line1' => 'Job :ref_number status has changed from :old_status to :new_status.',
    ],

    // Common
    'header' => [
        'phone' => 'Tel',
    ],
    'footer' => [
        'phone' => 'Tel',
        'email' => 'Email',
        'site' => 'Site',
        'feedback' => 'Você pode deixar o seu feedback :link',
        'feedback_link' => 'aqui',
    ],
    'powered_by' => 'Powered by',

];
