<?php

namespace App\Listeners;

use App\Events\BookingCreated;

class SendBookingDataToExternalService
{
    public function handle(BookingCreated $event)
    {
        $booking = $event->booking;
        $editMode = $event->editMode;

        if (!empty($booking->id) ) {
            // \App\Models\Config::ofSite($booking->booking->site_id)->toConfig();

            if (!empty($booking->booking->site_id)) {
                $config = \App\Models\Config::getBySiteId($booking->booking->site_id);
                if (!empty($booking->locale)) {
                    $config->loadLocale($booking->locale);
                }
                $config = $config->mapData()->getData();
            }

            // Waynium start
            if (config('services.waynium.enabled') && config('services.waynium.api_key') && config('services.waynium.secret_key')) {
                include_once(base_path('vendor/easytaxioffice/waynium.php'));

                $customer = $booking->assignedCustomer();
                $from = $booking->getFrom('raw');
                $to = $booking->getTo('raw');
                $via = $booking->getVia('raw');

                $apiKey = config('services.waynium.api_key');
                $secretKey = config('services.waynium.secret_key');
                $limoId = config('services.waynium.limo_id');

                $headers = ['apiKey' => $apiKey];

                $ETO_ID = 0;
                $CLI_ID = 0;
                $CLI_SOCIETE = trim(!empty($customer->company_name) ? $customer->company_name : 'ETO_CUSTOMER');
                $IDs = ['ETO_CUSTOMER'];

                if (!empty($CLI_SOCIETE) && !in_array($CLI_SOCIETE, $IDs)) {
                    $IDs[] = $CLI_SOCIETE;
                }

                $params = [[
                    'limo' => $limoId,
                    'params' => [
                        'C_Gen_Client' => [
                            'CLI_SOCIETE' => $IDs,
                        ]
                    ]
                ]];

                try {
                    $jwt = new \Zaf_Jwt($secretKey);
                    $results = $jwt->send('https://api.waynium.com/gdsv3/get-ressource/', $params, $headers);
                    $resultsJSON = json_decode($results, true);

                    if (!empty($resultsJSON)) {
                        if (!empty($resultsJSON[$limoId]['C_Gen_Client'])) {
                            $clients = (array)$resultsJSON[$limoId]['C_Gen_Client'];

                            foreach ($clients as $key => $value) {
                                if ($value['CLI_SOCIETE'] == 'ETO_CUSTOMER') {
                                    $ETO_ID = $value['CLI_ID'];
                                }
                                else {
                                    $CLI_ID = $value['CLI_ID'];
                                }
                            }

                            if ($CLI_ID == 0) {
                                $CLI_ID = $ETO_ID;
                            }
                        }

                        if (!isset($resultsJSON[$limoId]['C_Gen_Client'])) {
                            \Log::error('Waynium - Customer could not be found: '. (string)$resultsJSON[$limoId]);
                        }
                    }

                    // echo'<pre>'; print_r($results); echo'</pre>';
                    // echo'<pre>'; print_r($resultsJSON); echo'</pre>';
                    // dd('DONE '. $CLI_ID .' = '. $ETO_ID);
                }
                catch (\Exception $e) {
                    \Log::error('Waynium - Customer check error: '. $e->getMessage());
                }

                // Addresses
                $C_Gen_EtapePresence = [];
                $i = 0;

                $C_Gen_EtapePresence[] = [
                   'EPR_TRI' => $i,
                   'EPR_LIE_ID' => [
                      'LIE_TLI_ID' => '3',
                      'LIE_FORMATED' => $from->address, // Full google address
                      'LIE_INFO' => $from->complete, // Additional Informations
                      'LIE_LAT' => $from->lat, // latitude google
                      'LIE_LNG' => $from->lng, // longitude google
                      // 'LIE_VILLE' => '', // Antheuil portes
                      // 'LIE_CP' => '', // zip code
                      // 'LIE_PAY_ID' => '', // country id
                   ]
                ];

                foreach ($via as $key => $value) {
                    $i++;
                    $C_Gen_EtapePresence[] = [
                       'EPR_TRI' => $i,
                       'EPR_LIE_ID' => [
                          'LIE_TLI_ID' => '3',
                          'LIE_FORMATED' => $value->address, // Full google address
                          'LIE_INFO' => $value->complete, // Additional Informations
                          'LIE_LAT' => $value->lat, // latitude google
                          'LIE_LNG' => $value->lng, // longitude google
                       ]
                    ];
                }

                $C_Gen_EtapePresence[] = [
                   'EPR_TRI' => $i,
                   'EPR_LIE_ID' => [
                      'LIE_TLI_ID' => '3',
                      'LIE_FORMATED' => $to->address, // Full google address
                      'LIE_INFO' => $to->complete, // Additional Informations
                      'LIE_LAT' => $to->lat, // latitude google
                      'LIE_LNG' => $to->lng, // longitude google
                   ]
                ];

                // Passengers, luggages and equipments
                $C_Gen_Presence = [
                    [
                        'PRS_TRI' => '0',
                        'PRS_PAS_ID' => [
                           'PAS_NOM' => $booking->getContactFullName(), // passenger name
                           'PAS_EMAIL' => $booking->contact_email, // Email
                           'PAS_TELEPHONE' => $booking->contact_mobile, // phone number
                           'PAS_INFO_INTERNE' => $booking->requirements, // Internal note
                           'PAS_INFO_CHAUFFEUR' => $booking->notes, // Note to driver
                           'PAS_CIV_ID' => '', // passenger civility id
                           // 'PAS_PRENOM' => $booking->getContactFullName(), // passenger first name
                           // 'PAS_LAN_ID' => '', // language id
                           // 'PAS_FLAG_SMS' => '0', // Accept SMS
                        ],
                        'PRS_CMI' => [
                           'NB_ADULTE' => $booking->passengers, // Adults
                           'NB_BEBE' => $booking->infant_seats, // Babies
                           'NB_ENFANT' => $booking->child_seats, // Children
                           'NBRE_BAGAGE_CABINE' => $booking->hand_luggage, // Cabin luggages
                           'NBRE_BAGAGE_SOUTE' => $booking->luggage, // Checked baggages
                           'NB_HANDICAPE_FAUTEUIL' => $booking->wheelchair, // Wheelchairs
                           'NB_SIEGE_BEBE' => $booking->infant_seats, // Baby seats
                           'NB_SIEGE_REHAUSSEUR' => $booking->baby_seats, // Booster seats
                           // 'NB_GUIDE' => '1',
                           // 'NB_HANDICAPE_ASSISTE' => '1',
                           // 'NB_HANDICAPE_NON_ASSISTE' => '1',
                           // 'NB_GRAND_SAC' => '1', // Special baggage
                           // 'NB_SIEGE_AUTO' => '1', // Automotive seat
                        ]
                    ]
                ];

                // Invoice
                $C_Com_FraisMission = [
                    [
                       'FMI_LIBELLE' => 'Invoice '. $booking->getRefNumber(), // invoice title
                       'FMI_VENTE_HT' => $booking->total_price, // Invoice total without taxes
                       'FMI_QTE' => '1', // quantity
                       // 'FMI_SER_ID' => 'service id',
                       // 'FMI_POURCENTAGE_REMISE' => $booking->discount, // Invoice discount percent
                       // 'FMI_TVA' => '0' // VAT rate
                    ]
                ];

                // Order
                $MIS_NOTE_INTERNE = '';

                // if (!empty($booking->requirements)) {
                //     $MIS_NOTE_INTERNE .= (!empty($MIS_NOTE_INTERNE) ? "\r\Customer Requirements:\r\n" : ''). $booking->requirements;
                // }

                if (!empty($booking->getStatus())) {
                    $MIS_NOTE_INTERNE .= (!empty($MIS_NOTE_INTERNE) ? "\r\n\r\n" : '') ."** Booking Status**\r\n". $booking->getStatus();
                }

                if (!empty($booking->getVehicleList())) {
                    $MIS_NOTE_INTERNE .= (!empty($MIS_NOTE_INTERNE) ? "\r\n\r\n" : '') ."**Vehicle Types**\r\n". $booking->getVehicleList();
                }

                $MIS_TVE_ID = null;

                // if (config('services.waynium.vehicle_types_map')) {
                    // $MIS_TVE_ID = 1;
                // }

                $C_Gen_Mission = [
                    [
                       'ref' => 'eto_booking_'. (!empty($booking->id) ? $booking->id : rand(1000, 10000)) .'_'. rand(1000, 10000),
                       'MIS_TSE_ID' => '1', // Class of service id
                       // 'MIS_TVE_ID' => '1', // class of vehicle id
                       'MIS_TVE_ID' => $MIS_TVE_ID, // class of vehicle id
                       'MIS_DATE_DEBUT' => $booking->date->format('Y-m-d'), // Job date YYYY-MM-DD
                       'MIS_HEURE_DEBUT' => $booking->date->format('H:i'), // Passenger pickup time
                       'MIS_REF_MISSION_CLIENT' => $booking->getRefNumber(), // Ref number
                       'MIS_PC_NUM_TRANSPORT' => $booking->flight_number, // Pick-up: Flight/train number
                       'MIS_GOOGLE_KM_PREVU' => $booking->distance, // Expected Google distance
                       'MIS_GOOGLE_HEURE_PREVU' => $booking->duration, // Expected Google time
                       'MIS_NOTE_INTERNE' => $MIS_NOTE_INTERNE, // Internal note
                       'MIS_NOTE_CHAUFFEUR' => $booking->notes, // Note to chauffeur/resource
                       'MIS_TEL_PASSAGER' => $booking->contact_mobile, // Passenger/guest telephone no
                       'MIS_HEURE_FIN' => $booking->date->addMinutes($booking->duration)->format('H:i'), // passenger drop off time
                       'C_Com_FraisMission' => $C_Com_FraisMission,
                       'C_Gen_EtapePresence' => $C_Gen_EtapePresence,
                       'C_Gen_Presence' => $C_Gen_Presence
                    ]
                ];

                $C_Gen_Contact = [
                    [
                       'COT_CIV_ID' => '', // Civility ID 1= Mrs 2=Ms 3=Mr
                       'COT_NOM' => $booking->getContactFullName(), // Contact name
                       'COT_PRENOM' => $booking->getContactFullName(), // Contact first name
                       'COT_LAN_ID' => '', // contact language id
                       'COT_EMAIL' => $booking->contact_email, // contact email
                       'COT_TELEPHONE' => $booking->contact_mobile // Contact phone number
                    ]
                ];

                $C_Com_Commande = [
                     [
                        'COM_COT_ID' => 'C_Gen_Contact[0][COT_ID]',
                        'COM_SCO_ID' => '1',
                        'COM_DEMANDE' => '', // Customer request | Order create by connector.
                        'COM_COMMENTAIRE_INTERNE' => '', // Order comment
                        'C_Gen_Mission' => $C_Gen_Mission,
                     ]
                ];

                $params = [];

                if ($CLI_ID > 0) {
                    unset($C_Com_Commande[0]['COM_COT_ID']);
                    $C_Com_Commande[0]['COM_CLI_ID'] = $CLI_ID;
                    $params['C_Com_Commande'] = $C_Com_Commande;
                }
                else {
                    if ($CLI_SOCIETE == 'ETO_CUSTOMER') {
                        $C_Gen_Client = [
                            [
                               'ref' => 'eto_customer_'. rand(1000, 10000),
                               'CLI_SOCIETE' => $CLI_SOCIETE, // Name of client or company
                               'CLI_CCL_ID' => 1, // Class of customers
                               'C_Com_Commande' => $C_Com_Commande,
                            ]
                        ];
                    }
                    else {
                        $C_Gen_Client = [
                            [
                               'ref' => 'eto_customer_'. (!empty($booking->booking->user_id) ? $booking->booking->user_id : rand(1000, 10000)) .'_' . rand(1000, 10000),
                               'CLI_SOCIETE' => $CLI_SOCIETE, // Name of client or company
                               'CLI_SIRET' => !empty($customer->company_number) ? $customer->company_number : '', // Company number
                               'CLI_TVA_INTRA' => !empty($customer->company_tax_number) ? $customer->company_tax_number : '', // Company VAT
                               'CLI_TEL_FIXE' => !empty($customer->mobile_number) ? $customer->mobile_number : '', // Company phone
                               'CLI_FACT_NOM' => !empty($customer->first_name) || !empty($customer->last_name) ? trim($customer->first_name .' '. $customer->last_name) : $booking->getContactFullName(), // Name of client
                               'CLI_FACT_ADRESSE' => !empty($customer->address) ? $customer->address : '', // Client address
                               'CLI_FACT_CP' => !empty($customer->postcode) ? $customer->postcode : '', // Client zip code
                               'CLI_FACT_VILLE' => !empty($customer->city) ? $customer->city : '', // Client city
                               'CLI_FACT_PAY_ID' => !empty($customer->country) ? $customer->country : '', // Country ID
                               'CLI_COMMENTAIRE' => '', // This is a comment
                               'CLI_CCL_ID' => 1, // Class of customers
                               'C_Gen_Contact' => $C_Gen_Contact,
                               'C_Com_Commande' => $C_Com_Commande,
                            ]
                        ];
                    }

                    $params['C_Gen_Client'] = $C_Gen_Client;
                }

                $params = [[
                    'limo' => $limoId,
                    'params' => $params,
                ]];

                try {
                    $jwt = new \Zaf_Jwt($secretKey);
                    $results = $jwt->send('https://api.waynium.com/gdsv3/set-ressource-v2/', $params, $headers);
                    $resultsJSON = json_decode($results, true);

                    if (!empty($resultsJSON)) {
                        if ($CLI_ID > 0) {
                            if (!isset($resultsJSON[$limoId]['C_Com_Commande'])) {
                                \Log::error('Waynium - The job could not be created: '. (string)$resultsJSON[$limoId]);
                            }
                        }
                        else {
                            if (!isset($resultsJSON[$limoId]['C_Gen_Client'])) {
                                \Log::error('Waynium - Customer could not be created: '. (string)$resultsJSON[$limoId]);
                            }
                        }
                    }

                    // echo'<pre>'; print_r($results); echo'</pre>';
                    // echo'<pre>'; print_r($resultsJSON); echo'</pre>';
                    // dd('DONE END');
                }
                catch (\Exception $e) {
                    \Log::error('Waynium - Error while sending booking data to external server: '. $e->getMessage());
                }
            }
            // Waynium end

        }
    }
}
