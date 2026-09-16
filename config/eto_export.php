<?php
return [
    'sections' => [
        'bookings' => [
            'filters' => [
                'latest' => [
                    'type' => 'basic',
                    'data' => [
                        'status' => [
                            'pending',
                            'confirmed',
                            'assigned',
                            'auto_dispatch',
                            'accepted',
                            'rejected',
                            'onroute',
                            'arrived',
                            'onboard',
                            'quote'
                        ],
                    ],
                ],
                'completed' => [
                    'name' => 'completed',
                    'type' => 'basic',
                    'data' => [
                        'status' => [
                            'completed'
                        ],
                    ],
                ],
                'canceled' => [
                    'type' => 'basic',
                    'data' => [
                        'status' => [
                            'canceled',
                            'unfinished'
                        ],
                    ],
                ],
            ],
            'columns' => [
                'created_date'=>true,
                'ref_number'=>true,
                'status'=>true,
                'date'=>true,
                'flight_number'=>true,
                'flight_landing_time'=>true,
                'departure_city'=>true,
                'departure_flight_number'=>true,
                'departure_flight_time'=>true,
                'departure_flight_city'=>true,
                'contact_mobile'=>true,
                'address_start'=>true,
                'address_end'=>true,
                'waypoints'=>true,
                'total'=>true,
                'total_price'=>false,
                'discount'=>false,
                'discount_code'=>false,
                'commission'=>true,
                'cash'=>true,
                'driver_name'=>true,
                'vehicle_name'=>true,
                'vehicle'=>true,
                'route'=>true,
                'waiting_time'=>true,
                'contact_name'=>true,
                'contact_email'=>true,
                'meet_and_greet'=>true,
                'service_id'=>false,
                'service_duration'=>true,
                'source'=>true,
                'user_name'=>true,
                'lead_passenger_name'=>true,
                'lead_passenger_email'=>true,
                'lead_passenger_mobile'=>true,
                'modified_date'=>true,
                'id'=>false,
                'user_id'=>false,
                'driver_id'=>false,
                'vehicle_id'=>false,
                'site_id'=>false,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        'pending' => [
                            'color' => '#2d34d9'
                        ],
                        'requested' => [
                            'color' => '#ff7300'
                        ],
                        'quote' => [
                            'color' => '#51c0c5'
                        ],
                        'assigned' => [
                            'color' => '#9e2659'
                        ],
                        'auto_dispatch' => [
                            'color' => '#e64cde'
                        ],
                        'accepted' => [
                            'color' => '#00c0ef'
                        ],
                        'rejected' => [
                            'color' => '#dd4b39'
                        ],
                        'onroute' => [
                            'color' => '#605ca8'
                        ],
                        'arrived' => [
                            'color' => '#605ca8'
                        ],
                        'onboard' => [
                            'color' => '#605ca8'
                        ],
                        'completed' => [
                            'color' => '#00a65a'
                        ],
                        'canceled' => [
                            'color' => '#dd4b39'
                        ],
                        'unfinished' => [
                            'color' => '#dd4b39'
                        ],
                        'incomplete' => [
                            'color' => '#FF0000'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'common.booking_status_options.',
                ],
                'from' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
                'to' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
                'date_type' => [
                    'items' => [
                        'date' => [],
                        'created_date' => [],
                        'modified_date' => [],
                    ],
                    'type' => 'select',
                    'multiple' => false,
                    'translations' => 'export.filter.',
                ],
            ],
        ],
        'users' => [
            'filters' => [],
            'columns' => [
                'roles' => true,
                'name'=>true,
                'username'=>true,
                'email'=>true,
                'profile' => [
                    'title'=>true,
                    'first_name'=>true,
                    'last_name'=>true,
                    'date_of_birth'=>true,
                    'mobile_no'=>true,
                    'telephone_no'=>true,
                    'emergency_no'=>true,
                    'address'=>true,
                    'city'=>true,
                    'postcode'=>true,
                    'state'=>true,
                    'country'=>true,
                    'profile_type'=>true,
                    'company_name'=>true,
                    'company_number'=>true,
                    'company_tax_number'=>true,
                    'national_insurance_no'=>true,
                    'bank_account'=>true,
                    'unique_id'=>true,
                    'commission'=>true,
                    'availability'=>true,
                    'availability_status'=>true,
                    'insurance'=>true,
                    'insurance_expiry_date'=>true,
                    'driving_licence'=>true,
                    'driving_licence_expiry_date'=>true,
                    'pco_licence'=>true,
                    'pco_licence_expiry_date'=>true,
                    'phv_licence'=>true,
                    'phv_licence_expiry_date'=>true,
                    'description'=>true,
                ],
                'avatar'=>false,
                'activated'=>false,
                'status'=>true,
                'id'=>false,
                'created_at'=>true,
                'updated_at'=>false,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        'approved' => [],
                        'awaiting_admin_review' => [],
                        'awaiting_email_confirmation' => [],
                        'inactive' => [],
                        'rejected' => [],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'common.user_status_options.',
                ],
//                'profile_type' => [
//                    'items' => [
//                        'private' => [],
//                        'company' => [],
//                    ],
//                    'type' => 'select',
//                    'multiple' => true,
//                    'translations' => 'common.user_profile_type_options.',
//                ],
                'profile_created_from' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
                'profile_created_to' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
            ],
        ],
        'customers' => [
            'filters' => [],
            'columns' => [
                'site' => true,
                'roles' => true,
                'name'=>true,
                'email'=>true,
                'profile' => [
                    'title'=>true,
                    'first_name'=>true,
                    'last_name'=>true,
                    'mobile_no'=>true,
                    'telephone_no'=>true,
                    'emergency_no'=>true,
                    'company_name'=>true,
                    'company_number'=>true,
                    'company_tax_number'=>true,
                    'address'=>true,
                    'city'=>true,
                    'postcode'=>true,
                    'state'=>true,
                    'country'=>true,
                ],
                'ip'=>false,
                'id'=>false,
                'created_at'=>true,
                'activated'=>false,
                'status'=>true,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        'approved' => [],
                        'inactive' => [],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'common.user_status_options.',
                ],
                'profile_created_from' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
                'profile_created_to' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
            ],
        ],
        'feedback' => [
            'filters' => [],
            'columns' => [
                'type' => true,
                'name'=>true,
                'description'=>true,
                'ref_number'=>true,
                'email'=>true,
                'phone'=>true,
                'id'=>false,
                'created_at'=>true,
//                'params'=>false,
                'status'=>true,
                'additional_files'=>false,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        'active' => [],
                        'inactive' => [],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'admin/feedback.statuses.',
                ],
                'feedback_created_from' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
                'feedback_created_to' => [
                    'items' => [
                        'date' => [
                            'class' => 'eto-set-daterangepicker'
                        ],
                    ],
                    'type' => 'text',
                    'multiple' => false,
                    'translations' => false,
                ],
                'type' => [
                    'items' => [
                        'comment' => [],
                        'lost_found' => [],
                        'complaint' => [],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'admin/feedback.types.',
                ],
            ],
        ],
        'fixedprices' => [
            'filters' => [],
            'columns' => [
                'start_postcode' => true,
                'end_postcode' => true,
                'direction' => true,
                'price' => true,
                'deposit' => false,
                'start_date' => true,
                'end_date' => true,
                'status' => true,
                'is_zone' => false,
                'service_ids' => false,
//                'type' => false,
                'id'=>false,
                'modified_date' => false,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        1 => [
                            'trans_key' => 'active'
                        ],
                        0 => [
                            'trans_key' => 'inactive'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'admin/feedback.statuses.',
                ],
                'direction' => [
                    'items' => [
                        0 => [
                            'trans_key' => 'both_ways'
                        ],
                        1 => [
                            'trans_key' => 'from_to'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'export.fields.direction.',
                ],
                'is_zone' => [
                    'items' => [
                        1 => [
                            'trans_key' => 'zones'
                        ],
                        0 => [
                            'trans_key' => 'postcodes'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'export.fields.is_zone.',
                ],
            ],
        ],
        'vehicletypes' => [
            'filters' => [],
            'translations' => 'export.fields.is_zone.',
            'columns' => [
                'site' => [
                    'name' => 'export.fields.is_zone.site_id',
                    'view' => true
                ],

                'site' => true,
                'services_ids' => false,
                'hourly_rate' => false,
                'name' => true,
                'description' => false,
                'driver' => true,
                'disable_info' => false,
                'image' => true,
                'max_amount' => true,
                'passengers' => true,
                'luggage' => true,
                'hand_luggage' => true,
                'child_seats' => true,
                'baby_seats' => true,
                'infant_seats' => true,
                'wheelchair' => true,
                'factor_type' => false,
                'price' => true,
                'default' => false,
                'status' => true,
                'is_backend' => false,
                'id'=>false,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        1 => [
                            'trans_key' => 'active'
                        ],
                        0 => [
                            'trans_key' => 'inactive'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'admin/feedback.statuses.',
                ],
                'is_backend' => [
                    'items' => [
                        1 => [
                            'trans_key' => 'backend'
                        ],
                        0 => [
                            'trans_key' => 'backend_frontend'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'export.fields.is_backend.',
                ],
                'capacity' => [
                    'items' => [
                        'max_amount' => [],
                        'passenger' => [],
                        'luggage' => [],
                        'hand_luggage' => [],
                        'child_seats' => [],
                        'baby_seats' => [],
                        'infant_seats' => [],
                        'wheelchair' => [],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'export.column.vehicletypes.',
                ],
                'factor_type' => [
                    'items' => [
                        0 => [
                            'trans_key' => 'flat'
                        ],
                        1 => [
                            'trans_key' => 'multiply'
                        ],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'export.fields.factor_type.',
                ],
            ],
        ],
        'vehicles' => [
            'filters' => [],
            'columns' => [
                'name' => true,
                'driver' => true,
                'image' => true,
                'mot' => true,
                'mot_expiry_date' => true,
                'make' => true,
                'model' => true,
                'colour' => true,
                'body_type' => true,
                'no_of_passengers' => true,
                'registered_keeper_name' => true,
                'registered_keeper_address' => true,
                'description' => false,
                'status' => true,
                'selected' => false,
                'created_at' => false,
                'updated_at' => false,
                'id'=>false,
            ],
            'params' => [
                'status' => [
                    'items' => [
                        'activated' => [],
                        'inactive' => [],
                    ],
                    'type' => 'select',
                    'multiple' => true,
                    'translations' => 'common.vehicle_status_options.',
                ],
            ],
        ]
    ],
];
