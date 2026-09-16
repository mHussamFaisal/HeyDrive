<?php

$request = request();

switch($action) {
    case 'read':

        $configList = array(
          'cron_update_auto' => config('eto_cron.update.auto'),
          'cron_update_time' => config('eto_cron.update.time'),
          'cron_update_interval' => config('eto_cron.update.interval'),
          'cron_secret_key' => config('eto_cron.secret_key'),
          'cron_job_reminder_minutes' => config('eto_cron.job_reminder.minutes'),
          'cron_job_reminder_allowed_times' => config('eto_cron.job_reminder.allowed_times'),

          'enable_autodispatch' => config('eto_dispatch.enable_autodispatch'),
          'time_every_minute' => config('eto_dispatch.time_every_minute'),
          'time_to_assign' => config('eto_dispatch.time_to_assign'),
          'time_to_confirm' => config('eto_dispatch.time_to_confirm'),
          'time_last_seen' => config('eto_dispatch.time_last_seen'),
          'extra_time_slot' => config('eto_dispatch.extra_time_slot'),
          'check_within_radius' => config('eto_dispatch.check_within_radius'),
          'check_availability_status' => config('eto_dispatch.check_availability_status'),
          'check_trashed' => config('eto_dispatch.check_trashed'),
          'delete_expired' => config('eto_dispatch.delete_expired'),
          'assign_max_drivers' => config('eto_dispatch.assign_max_drivers'),
          'only_auto_dispatch_status' => config('eto_dispatch.only_auto_dispatch_status'),
          'assign_driver_on_status_change' => config('eto_dispatch.assign_driver_on_status_change'),
          'assign_driver_on_reject' => config('eto_dispatch.assign_driver_on_reject'),

          'auto_payment_redirection' => 1,
          'booking_cancel_enable' => 0,
          'booking_cancel_time' => 2,
          'booking_directions_enable' => 1,
          'booking_distance_unit' => config('site.booking_distance_unit'),
          'booking_date_picker_style' => 1,
          'booking_time_picker_style' => 1,
          'booking_return_as_oneway' => config('site.booking_return_as_oneway'),
          'booking_postcode_match' => config('site.booking_postcode_match'),
          'booking_hide_vehicle_not_available_message' => config('site.booking_hide_vehicle_not_available_message'),
          'booking_pricing_mode' => config('site.booking_pricing_mode'),
          'booking_display_book_by_phone' => config('site.booking_display_book_by_phone'),
          'booking_attach_ical' => config('site.booking_attach_ical'),
          'booking_show_preferred' => config('site.booking_show_preferred'),
          'booking_show_second_passenger' => config('site.booking_show_second_passenger'),
          'booking_time_picker_steps' => config('site.booking_time_picker_steps'),
          'booking_time_picker_by_minute' => config('site.booking_time_picker_by_minute'),
          'booking_force_home_address' => config('site.booking_force_home_address'),
          'booking_show_requirements' => config('site.booking_show_requirements'),
          'booking_service_dropdown' => config('site.booking_service_dropdown'),
          'booking_service_display_mode' => config('site.booking_service_display_mode'),
          'booking_location_search_min' => config('site.booking_location_search_min'),
          'booking_display_widget_header' => config('site.booking_display_widget_header'),
          'booking_display_return_journey' => config('site.booking_display_return_journey'),
          'booking_display_via' => config('site.booking_display_via'),
          'booking_display_swap' => config('site.booking_display_swap'),
          'booking_display_geolocation' => config('site.booking_display_geolocation'),
          'booking_display_book_button' => config('site.booking_display_book_button'),
          'booking_member_benefits' => config('site.booking_member_benefits'),
          'booking_member_benefits_enable' => config('site.booking_member_benefits_enable'),
          'booking_hide_cash_payment_if_airport' => config('site.booking_hide_cash_payment_if_airport'),
          'booking_allow_account_payment' => config('site.booking_allow_account_payment'),
          'booking_show_more_options' => config('site.booking_show_more_options'),
          'booking_allow_guest_checkout' => config('site.booking_allow_guest_checkout'),
          'booking_summary_display_mode' => config('site.booking_summary_display_mode'),
          'booking_vehicle_display_mode' => config('site.booking_vehicle_display_mode'),
          'booking_scroll_to_top_enable' => config('site.booking_scroll_to_top_enable'),
          'booking_scroll_to_top_offset' => config('site.booking_scroll_to_top_offset'),
          'booking_advanced_geocoding' => config('site.booking_advanced_geocoding'),
          'booking_price_status' => config('site.booking_price_status'),
          'booking_price_status_on' => config('site.booking_price_status_on'),
          'booking_price_status_on_enquiry' => config('site.booking_price_status_on_enquiry'),
          'booking_price_status_off' => config('site.booking_price_status_off'),
          'booking_map_enable' => 1,
          'booking_map_zoom' => 10,
          'booking_map_open' => 0,
          'booking_map_draggable' => 0,
          'booking_map_zoomcontrol' => 0,
          'booking_map_scrollwheel' => 0,
          'booking_request_enable' => 0,
          'booking_request_time' => config('site.booking_request_time'),
          'booking_auto_confirm_time' => config('site.booking_auto_confirm_time'),
          'booking_required_address_complete_from' => config('site.booking_required_address_complete_from'),
          'booking_required_address_complete_to' => config('site.booking_required_address_complete_to'),
          'booking_required_address_complete_via' => config('site.booking_required_address_complete_via'),
          'booking_required_baby_seats' => 0,
          'booking_required_infant_seats' => 0,
          'booking_required_wheelchair' => 0,
          'booking_required_child_seats' => 0,
          'booking_required_contact_mobile' => 0,
          'booking_required_flight_number' => 0,
          'booking_required_flight_landing_time' => 0,
          'booking_required_departure_city' => 0,
          'booking_required_departure_flight_number' => 0,
          'booking_required_departure_flight_time' => 0,
          'booking_required_departure_flight_city' => 0,
          'booking_flight_landing_time_enable' => 0,
          'booking_departure_flight_time_enable' => 0,
          'booking_departure_flight_time_check_enable' => 0,
          'booking_departure_flight_time_check_value' => 120,
          'booking_required_hand_luggage' => 0,
          'booking_required_luggage' => 0,
          'booking_required_passengers' => 0,
          'booking_required_waiting_time' => 0,
          'booking_round_total_price' => 0,
          'booking_min_price_type' => 0,
          'booking_include_aiport_charges' => 0,
          'booking_duration_rate' => 0,
          'booking_account_discount' => 0,
          'booking_return_discount' => 0,
          'booking_summary_enable' => config('site.booking_summary_enable'),
          'booking_terms_disable_button' => 0,
          'booking_account_autocompletion' => 1,
          'booking_meet_and_greet_enable' => 1,
          'booking_meet_and_greet_compulsory' => 0,
          'booking_waiting_time_enable' => 0,
          'booking_base_action' => config('site.booking_base_action'),
          'booking_base_calculate_type' => config('site.booking_base_calculate_type'),
          'booking_base_calculate_type_enable' => config('site.booking_base_calculate_type_enable'),
          'booking_exclude_driver_journey_from_fixed_price' => config('site.booking_exclude_driver_journey_from_fixed_price'),
          'booking_listing_refresh_type' => config('site.booking_listing_refresh_type'),
          'booking_listing_refresh_interval' => config('site.booking_listing_refresh_interval'),
          'booking_listing_refresh_counter' => config('site.booking_listing_refresh_counter'),
          'user_show_company_name' => config('site.user_show_company_name'),
          'customer_allow_company_number' => config('site.customer_allow_company_number'),
          'customer_require_company_number' => config('site.customer_require_company_number'),
          'customer_allow_company_tax_number' => config('site.customer_allow_company_tax_number'),
          'customer_require_company_tax_number' => config('site.customer_require_company_tax_number'),
          'driver_show_total' => config('site.driver_show_total'),
          'driver_show_unique_id' => config('site.driver_show_unique_id'),
          'driver_show_edit_profile_button' => config('site.driver_show_edit_profile_button'),
          'driver_show_edit_profile_insurance' => config('site.driver_show_edit_profile_insurance'),
          'driver_show_edit_profile_driving_licence' => config('site.driver_show_edit_profile_driving_licence'),
          'driver_show_edit_profile_pco_licence' => config('site.driver_show_edit_profile_pco_licence'),
          'driver_show_edit_profile_phv_licence' => config('site.driver_show_edit_profile_phv_licence'),
          'driver_show_reject_button' => config('site.driver_show_reject_button'),
          'driver_show_onroute_button' => config('site.driver_show_onroute_button'),
          'driver_show_arrived_button' => config('site.driver_show_arrived_button'),
          'driver_show_onboard_button' => config('site.driver_show_onboard_button'),
          'driver_allow_cancel' => config('site.driver_allow_cancel'),
          'booking_meeting_board_enabled' => config('site.booking_meeting_board_enabled'),
          'booking_meeting_board_attach' => config('site.booking_meeting_board_attach'),
          'booking_meeting_board_font_size' => config('site.booking_meeting_board_font_size'),
          'booking_meeting_board_header' => config('site.booking_meeting_board_header'),
          'booking_meeting_board_footer' => config('site.booking_meeting_board_footer'),
          'driver_show_restart_button' => config('site.driver_show_restart_button'),
          'driver_show_passenger_phone_number' => config('site.driver_show_passenger_phone_number'),
          'driver_show_passenger_email' => config('site.driver_show_passenger_email'),
          'driver_attach_booking_details_to_email' => config('site.driver_attach_booking_details_to_email'),
          'driver_attach_booking_details_to_sms' => config('site.driver_attach_booking_details_to_sms'),
          'driver_calendar_show_ref_number' => config('site.driver_calendar_show_ref_number'),
          'driver_calendar_show_from' => config('site.driver_calendar_show_from'),
          'driver_calendar_show_to' => config('site.driver_calendar_show_to'),
          'driver_calendar_show_via' => config('site.driver_calendar_show_via'),
          'driver_calendar_show_vehicle_type' => config('site.driver_calendar_show_vehicle_type'),
          'driver_calendar_show_estimated_time' => config('site.driver_calendar_show_estimated_time'),
          'driver_calendar_show_actual_time_slot' => config('site.driver_calendar_show_actual_time_slot'),
          'driver_calendar_show_passengers' => config('site.driver_calendar_show_passengers'),
          'driver_calendar_show_custom' => config('site.driver_calendar_show_custom'),
          'driver_booking_file_upload' => config('eto_driver.booking_file_upload'),
          'customer_attach_booking_details_to_sms' => config('site.customer_attach_booking_details_to_sms'),
          'customer_attach_booking_details_access_link' => config('site.customer_attach_booking_details_access_link'),
          'customer_show_only_lead_passenger' => config('site.customer_show_only_lead_passenger'),
          'admin_booking_listing_highlight' => config('site.admin_booking_listing_highlight'),
          'admin_calendar_show_ref_number' => config('site.admin_calendar_show_ref_number'),
          'admin_calendar_show_name_passenger' => config('site.admin_calendar_show_name_passenger'),
          'admin_calendar_show_name_customer' => config('site.admin_calendar_show_name_customer'),
          'admin_calendar_show_service_type' => config('site.admin_calendar_show_service_type'),
          'admin_calendar_show_from' => config('site.admin_calendar_show_from'),
          'admin_calendar_show_to' => config('site.admin_calendar_show_to'),
          'admin_calendar_show_via' => config('site.admin_calendar_show_via'),
          'admin_calendar_show_vehicle_type' => config('site.admin_calendar_show_vehicle_type'),
          'admin_calendar_show_estimated_time' => config('site.admin_calendar_show_estimated_time'),
          'admin_calendar_show_actual_time_slot' => config('site.admin_calendar_show_actual_time_slot'),
          'admin_calendar_show_driver_name' => config('eto_calendar.show.driver_name'),
          'admin_calendar_show_passengers' => config('eto_calendar.show.passengers'),
          'admin_calendar_show_custom' => config('eto_calendar.show.custom'),
          'admin_default_page' => config('site.admin_default_page'),
          'company_address' => '',
          'company_email' => '',
          'company_name' => '',
          'company_number' => '',
          'company_tax_number' => '',
          'company_telephone' => '',
          'url_locales' => [],
          'url_home' => '',
          'url_booking' => '',
          'url_contact' => '',
          'url_customer' => '',
          'url_feedback' => '',
          'url_terms' => '',
          'feedback_type' => config('site.feedback_type'),
          'terms_type' => config('site.terms_type'),
          'terms_text' => config('site.terms_text'),
          'terms_email' => config('site.terms_email'),
          'terms_download' => config('site.terms_download'),
          'terms_enable' => config('site.terms_enable'),
          'currency_symbol' => '',
          'currency_code' => '',
          'debug' => 0,
          'incomplete_bookings_display' => 0,
          'incomplete_bookings_delete_enable' => 0,
          'incomplete_bookings_delete_after' => 0,
          'embedded' => 1,
          'enable_baby_seats' => 1,
          'enable_infant_seats' => 1,
          'enable_wheelchair' => 0,
          'booking_allow_one_type_of_child_seat' => 0,
          'enable_child_seats' => 1,
          'enable_hand_luggage' => 1,
          'enable_luggage' => 1,
          'enable_passengers' => 1,
          'eto_branding' => 1,
          'google_maps_javascript_api_key' => '',
          'google_maps_embed_api_key' => '',
          'google_maps_directions_api_key' => '',
          'google_maps_geocoding_api_key' => '',
          'google_places_api_key' => '',
          'google_cache_expiry_time' => config('site.google_cache_expiry_time'),
          'google_analytics_tracking_id' => config('site.google_analytics_tracking_id'),
          'google_adwords_conversion_id' => config('site.google_adwords_conversion_id'),
          'google_adwords_conversions' => config('site.google_adwords_conversions'),
          'autocomplete_google_places' => 1,
          'autocomplete_force_selection' => 0,
          'code_head' => '',
          'code_body' => '',
          'force_https' => config('site.force_https'),
          'mail_driver' => 'mail',
          'mail_host' => '',
          'mail_port' => '',
          'mail_username' => '',
          'mail_password' => '',
          'mail_encryption' => '',
          'mail_sendmail' => '',
          'callerid_type' => config('site.callerid_type'),
          'ringcentral_environment' => config('services.ringcentral.environment'),
          'ringcentral_app_key' => config('services.ringcentral.app_key'),
          'ringcentral_app_secret' => config('services.ringcentral.app_secret'),
          'ringcentral_widget_open' => config('services.ringcentral.widget_open'),
          'ringcentral_popup_open' => config('services.ringcentral.popup_open'),
          'flightstats_enabled' => config('services.flightstats.enabled'),
          'flightstats_app_id' => config('services.flightstats.app_id'),
          'flightstats_app_key' => config('services.flightstats.app_key'),
          'sms_service_type' => config('services.sms_service_type'),
          'textlocal_api_key' => config('services.textlocal.key'),
          'textlocal_test_mode' => config('services.textlocal.test'),
          'twilio_sid' => config('services.twilio.sid'),
          'twilio_token' => config('services.twilio.token'),
          'twilio_phone_number' => config('services.twilio.phone_number'),
          'smsgateway_api_key' => config('services.smsgateway.key'),
          'smsgateway_device_id' => config('services.smsgateway.device_id'),
          'pcapredict_enabled' => 0,
          'pcapredict_api_key' => '',
          'google_language' => '',
          'google_country_code' => '',
          'google_region_code' => '',
          'invoice_enabled' => config('site.invoice_enabled'),
          'invoice_display_details' => config('site.invoice_display_details'),
          'invoice_display_logo' => config('site.invoice_display_logo'),
          'invoice_display_payments' => config('site.invoice_display_payments'),
          'invoice_display_custom_field' => config('site.invoice_display_custom_field'),
          'invoice_display_company_number' => config('site.invoice_display_company_number'),
          'invoice_display_company_tax_number' => config('site.invoice_display_company_tax_number'),
          'invoice_info' => '',
          'invoice_bill_from' => '',
          'invoice_styles_default_bg_color' => config('site.invoice_styles_default_bg_color'),
          'invoice_styles_default_text_color' => config('site.invoice_styles_default_text_color'),
          'invoice_styles_active_bg_color' => config('site.invoice_styles_active_bg_color'),
          'invoice_styles_active_text_color' => config('site.invoice_styles_active_text_color'),
          'tax_name' => '',
          'tax_percent' => 0,
          'styles_border_radius' => config('site.styles_border_radius'),
          'styles_default_bg_color' => config('site.styles_default_bg_color'),
          'styles_default_border_color' => config('site.styles_default_border_color'),
          'styles_default_text_color' => config('site.styles_default_text_color'),
          'styles_active_bg_color' => config('site.styles_active_bg_color'),
          'styles_active_border_color' => config('site.styles_active_border_color'),
          'styles_active_text_color' => config('site.styles_active_text_color'),
          'custom_css' => config('site.custom_css'),
          'mobile_app_styles_border_radius' => config('site.mobile_app_styles_border_radius'),
          'mobile_app_styles_default_bg_color' => config('site.mobile_app_styles_default_bg_color'),
          'mobile_app_styles_default_border_color' => config('site.mobile_app_styles_default_border_color'),
          'mobile_app_styles_default_text_color' => config('site.mobile_app_styles_default_text_color'),
          'mobile_app_styles_active_bg_color' => config('site.mobile_app_styles_active_bg_color'),
          'mobile_app_styles_active_border_color' => config('site.mobile_app_styles_active_border_color'),
          'mobile_app_styles_active_text_color' => config('site.mobile_app_styles_active_text_color'),
          'mobile_app_custom_css' => config('site.mobile_app_custom_css'),
          'language' => config('app.locale'),
          'locale_switcher_enabled' => config('app.locale_switcher_enabled'),
          'locale_switcher_display_name_code' => config('app.locale_switcher_display_name_code'),
          'locale_switcher_style' => config('app.locale_switcher_style'),
          'locale_switcher_display' => config('app.locale_switcher_display'),
          'locale_active' => config('app.locale_active'),
          'timezone' => config('app.timezone'),
          'date_format' => config('site.date_format'),
          'time_format' => config('site.time_format'),
          'start_of_week' => config('site.start_of_week'),
          'locations_skip_place_id' => '',
          'login_enable' => 1,
          'min_booking_time_limit' => 0,
          'night_charge_enable' => 0,
          'night_charge_end' => '00:00',
          'night_charge_factor_type' => 0,
          'night_charge_factor' => 0,
          'night_charge_start' => '00:00',
          'password_length_max' => 5,
          'password_length_min' => 0,
          'quote_address_suffix' => '',
          'quote_avoid_ferries' => 1,
          'quote_avoid_highways' => 1,
          'quote_avoid_tolls' => 1,
          'quote_duration_in_traffic' => 1,
          'quote_traffic_model' => 'best_guess',
          'quote_distance_range' => array(),
          'booking_vehicle_min_price' => array(),
          'booking_items' => array(),
          'booking_night_surcharge' => array(),
          'booking_deposit' => array(),
          'booking_deposit_balance' => 'card',
          'booking_deposit_selected' => 'deposit',
          'fixed_prices_priority' => config('site.fixed_prices_priority'),
          'fixed_prices_deposit_enable' => config('site.fixed_prices_deposit_enable'),
          'fixed_prices_deposit_type' => config('site.fixed_prices_deposit_type'),

          'driver_income_child_seats' => config('eto_driver.income.child_seats'),
          'driver_income_additional_items' => config('eto_driver.income.additional_items'),
          'driver_income_parking_charges' => config('eto_driver.income.parking_charges'),
          'driver_income_payment_charges' => config('eto_driver.income.payment_charges'),
          'driver_income_meet_and_greet' => config('eto_driver.income.meet_and_greet'),
          'driver_income_discounts' => config('eto_driver.income.discounts'),

          'booking_discount_child_seats' => config('eto_booking.discount.child_seats'),
          'booking_discount_additional_items' => config('eto_booking.discount.additional_items'),
          'booking_discount_parking_charges' => config('eto_booking.discount.parking_charges'),
          'booking_discount_payment_charges' => config('eto_booking.discount.payment_charges'),
          'booking_discount_meet_and_greet' => config('eto_booking.discount.meet_and_greet'),

          'quote_enable_shortest_route' => 0,
          'quote_enable_straight_line' => 0,
          'ref_format' => config('site.ref_format'),
          'register_activation_enable' => 1,
          'register_enable' => 1,
          'status_list' => '',

          // Other table settings
          'distance_min' => 0,
          'meet_and_greet' => 0,
          'child_seat' => 0,
          'baby_seat' => 0,
          'infant_seats' => 0,
          'wheelchair' => 0,
          'waiting_time' => 0,
          'geocode_start' => 0,
          'geocode_end' => 0,
          'waypoint' => 0,
          'geocode_start_postcodes' => '',
          'geocode_end_postcodes' => '',
          'airport_postcodes' => '',
          'override' => array()
        );

        $configTemp = \App\Models\Config::getBySiteId($siteId)->getData();
        if ($configTemp->language) {
            $gConfig['language'] = $configTemp->language;
        }

        if ($configTemp->url_locales) {
            $url_json = $configTemp->url_locales;
            if (!empty($url_json)) {
                $url_json = json_decode($url_json);
            }
            $url_locales = [];
            if (!empty($url_json)) {
                foreach ($url_json as $k => $v) {
                    foreach ($v as $k2 => $v2) {
                        $url_locales[] = (object)[
                            'code' => $k,
                            'type' => $k2,
                            'value' => $v2
                        ];
                    }
                }
            }
            $url_locales = json_encode($url_locales);
            $gConfig['url_locales'] = $url_locales;
        }

        foreach($configList as $k => $v) {
            if (isset($gConfig[$k])) {
                $configList[$k] = $gConfig[$k];
            }
        }

        // Allowed languages
        if ( !empty($configList['locale_active']) && is_string($configList['locale_active']) ) {
            $configList['locale_active'] = json_decode($configList['locale_active']);
        }

        // Bases
        $bases = \App\Models\Base::where('relation_type', '=', 'site')
            ->where('relation_id', '=', $siteId)
            ->orderBy('ordering', 'ASC');

        $basesList = array();

        foreach($bases->get() as $kB => $vB) {
          $basesList[] = (object)[
            'id' => $vB->id,
            'address' => $vB->address,
            'radius' => $vB->radius,
            'status' => $vB->status
          ];
        }
        $configList['bases'] = $basesList;

        // Vehicle min price
        if ( isset($gConfig['booking_vehicle_min_price']) ){
          $vehicle_min_price = array();
          $temp = json_decode($gConfig['booking_vehicle_min_price']);
          if ( !empty($temp) ) {
            foreach($temp as $key => $value) {
              $row = new \stdClass();
              $row->id = (float)$value->id;
              $row->value = (float)$value->value;
              $vehicle_min_price[] = $row;
            }
          }
          $configList['booking_vehicle_min_price'] = $vehicle_min_price;
        }

        // Items
        if ( isset($gConfig['booking_items']) ){
          $items = array();
          $temp = json_decode($gConfig['booking_items']);
          if ( !empty($temp) ) {
            foreach($temp as $key => $value) {
              $slug = str_slug((string)$value->name, '_');
              $slug = \App\Helpers\SiteHelper::seoFriendlyUrl($slug);

              $row = new \stdClass();
              $row->id = $slug;
              $row->name = (string)$value->name;
              $row->value = (float)$value->value;
              $row->type = $value->type ? (string)$value->type : 'amount';
              $row->amount = (int)$value->amount;
              $row->custom = implode(', ', (array)$value->custom);
              $items[] = $row;
            }
          }
          $configList['booking_items'] = $items;
        }

        // Night surcharge
        if ( isset($gConfig['booking_night_surcharge']) ){
          $night_surcharge = array();
          $temp = json_decode($gConfig['booking_night_surcharge']);
          if ( !empty($temp) ) {
            foreach($temp as $key => $value) {
              $row = new \stdClass();
              $row->vehicle_id = (int)$value->vehicle_id;
              $row->repeat_days = (array)$value->repeat_days;
              $row->time_start = (string)$value->time_start;
              $row->time_end = (string)$value->time_end;
              $row->factor_type = (string)$value->factor_type;
              $row->factor_value = (float)$value->factor_value;
              $night_surcharge[] = $row;
            }
          }
          $configList['booking_night_surcharge'] = $night_surcharge;
        }

        // Deposit
        if ( isset($gConfig['booking_deposit']) ){
          $deposit = array();
          $temp = json_decode($gConfig['booking_deposit']);
          if ( !empty($temp) ) {
            foreach($temp as $key => $value) {
              $row = new \stdClass();
              $row->id = (int)$value->id;
              $row->type = (string)$value->type;
              $row->value = (float)$value->value;
              $deposit[] = $row;
            }
          }
          $configList['booking_deposit'] = $deposit;
        }

        // Distance Range - Read
        if ( isset($gConfig['quote_distance_range']) ){
          $distanceRange = array();

          $quoteDistanceRange = json_decode($gConfig['quote_distance_range']);
          if ( !empty($quoteDistanceRange) ) {
            foreach($quoteDistanceRange as $key => $value) {
              $row = new \stdClass();
              $row->id = (int)$key;
              $row->distance = (float)$value->distance;
              $row->value = (float)$value->value;
              $row->factor_type = (int)$value->factor_type;
              $row->vehicle = (array)$value->vehicle;

              $index = round((float)$row->distance * 100);
              $distanceRange[$index] = $row;
            }
          }

          ksort($distanceRange);
          $configList['quote_distance_range'] = $distanceRange;
        }


        // Charges
        $sql = "SELECT *
            FROM `{$dbPrefix}charge`
            WHERE `site_id`='". $siteId ."'
            ORDER BY `id` ASC";

        $resultsCharge = $db->select($sql);

        if ( !empty($resultsCharge) )
        {
          foreach($resultsCharge as $key => $value)
          {
            switch($value->type)
            {
              case 'distance_min':
                $configList['distance_min'] = $value->value;
              break;
              case 'meet_and_greet':
                $configList['meet_and_greet'] = $value->value;
              break;
              case 'child_seat':
                $configList['child_seat'] = $value->value;
              break;
              case 'baby_seat':
                $configList['baby_seat'] = $value->value;
              break;
              case 'infant_seats':
                $configList['infant_seats'] = $value->value;
              break;
              case 'wheelchair':
                $configList['wheelchair'] = $value->value;
              break;
              case 'waiting_time':
                $configList['waiting_time'] = $value->value;
              break;
              case 'geocode_start':
                $configList['geocode_start'] = $value->value;
                $configList['geocode_start_postcodes'] = implode(",\n", (array)json_decode($value->params, true));
              break;
              case 'geocode_end':
                $configList['geocode_end'] = $value->value;
                $configList['geocode_end_postcodes'] = implode(",\n", (array)json_decode($value->params, true));
              break;
              case 'geocode_both':

              break;
              case 'airport_postcodes':
                $configList['airport_postcodes'] = implode(",\n", (array)json_decode($value->params, true));
              break;
              case 'waypoint':
                $configList['waypoint'] = $value->value;
              break;
              case 'distance_override':
                $start_date = $value->start_date ? date('Y-m-d H:i', strtotime($value->start_date) ) : '';
                $end_date = $value->end_date ? date('Y-m-d H:i', strtotime($value->end_date) ) : '';

                $value->start_date = $start_date;
                $value->end_date = $end_date;

                $params = isset($value->params) ? (array)json_decode($value->params, true) : [];
                $value->factor_type = isset($params['factor_type']) ? (int)$params['factor_type'] : 0;

                $configList['override'][] = $value;
              break;
            }
          }
        }

        // Vehicles
        $vehicleList = array();

        $sql = "SELECT `id`, `name` FROM `{$dbPrefix}vehicle` WHERE `site_id`='" . $siteId . "' ORDER BY `ordering` ASC";
        $query = $db->select($sql);

        if ( !empty($query) ) {
          foreach($query as $k => $v) {
            $vehicleList[] = array(
              'id' => (int)$v->id,
              'name' => (string)$v->name,
            );
          }
        }

        $configList['site_id'] = $siteId;

        $data['vehicleList'] = $vehicleList;
        $data['configList'] = $configList;
        $data['success'] = true;

    break;
    case 'update':

        $subscriptionId = $request->system->subscription->id;
        $siteId = ($etoPost['site_id']) ? (int)$etoPost['site_id'] : (int)$siteId;
        $settingsGroup = isset($etoPost['settings_group']) ? $etoPost['settings_group'] : '';
        $configList = [];
        $settingsList = [];
        $chargesList = [];

        // General
        if (!$settingsGroup || $settingsGroup == 'general') {
            $url_json = (string)$etoPost['url_locales'];
            if (!empty($url_json)) {
                $url_json = json_decode($url_json);
            }
            $url_locales = [];
            if (!empty($url_json)) {
                foreach ($url_json as $k => $v) {
                    if (!empty($v->code) && !empty($v->type) && !empty($v->value)) {
                        $url_locales[$v->code][$v->type] = $v->value;
                    }
                }
            }
            $url_locales = json_encode($url_locales);

            $configList = array_merge($configList, [
                'company_name' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['company_name'])],
                'company_address' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['company_address'])],
                'company_number' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['company_number'])],
                'company_email' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['company_email'])],
                'company_telephone' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['company_telephone'])],
                'status_list' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['status_list']],
                'url_locales' => ['type' => 'string', 'browser' => '0', 'value' => $url_locales],
                'url_home' => ['type' => 'string', 'browser' => '1', 'value' => (string)$etoPost['url_home']],
                'url_booking' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['url_booking']],
                'url_customer' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['url_customer']],
                'url_contact' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['url_contact']],
                'embedded' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['embedded'] == '1' ) ? 1 : 0],
                'feedback_type' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['feedback_type']],
                'url_feedback' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['url_feedback']],
                'terms_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['terms_enable'] == '1' ) ? 1 : 0],
                'terms_type' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['terms_type'] == '1' ) ? 1 : 0],
                'url_terms' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['url_terms']],
                'terms_text' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['terms_text']],
                'terms_email' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['terms_email'] == '1' ) ? 1 : 0],
                'terms_download' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['terms_download'] == '1' ) ? 1 : 0],
            ]);

            $settingsList = array_merge($settingsList, [
                ['eto_cron.update.auto', isset($etoPost['cron_update_auto']) ? $etoPost['cron_update_auto'] : config('eto_cron.update.auto'), 'system', 0],
                ['eto_cron.update.time', isset($etoPost['cron_update_time']) ? $etoPost['cron_update_time'] : config('eto_cron.update.time'), 'system', 0],
                ['eto_cron.update.interval', isset($etoPost['cron_update_interval']) ? $etoPost['cron_update_interval'] : config('eto_cron.update.interval'), 'system', 0],
                ['eto_cron.secret_key', isset($etoPost['cron_secret_key']) ? $etoPost['cron_secret_key'] : config('eto_cron.secret_key'), 'system', 0],
                ['eto_cron.job_reminder.minutes', isset($etoPost['cron_job_reminder_minutes']) ? $etoPost['cron_job_reminder_minutes'] : config('eto_cron.job_reminder.minutes'), 'system', 0],
                ['eto_cron.job_reminder.allowed_times', isset($etoPost['cron_job_reminder_allowed_times']) ? $etoPost['cron_job_reminder_allowed_times'] : config('eto_cron.job_reminder.allowed_times'), 'system', 0],
            ]);
        }

        // Localization
        if (!$settingsGroup || $settingsGroup == 'localization') {
            $configList = array_merge($configList, [
                'language' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['language']],
                'locale_switcher_enabled' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['locale_switcher_enabled'] == '1' ) ? 1 : 0],
                'locale_switcher_style' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['locale_switcher_style']],
                'locale_switcher_display' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['locale_switcher_display']],
                'locale_switcher_display_name_code' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['locale_switcher_display_name_code'] == '1' ) ? 1 : 0],
                'locale_active' => ['type' => 'object', 'browser' => '0', 'value' => json_encode(array_unique(array_merge((array)$etoPost['locale_active'], [(string)$etoPost['language']])))],
                'timezone' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['timezone']],
                'date_format' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['date_format']],
                'time_format' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['time_format']],
                'start_of_week' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['start_of_week']],
            ]);
        }

        // Auto Dispatch
        if (config('eto.allow_auto_dispatch') == 1 && (!$settingsGroup || $settingsGroup == 'auto_dispatch')) {
            $settingsList = array_merge($settingsList, [
                ['eto_dispatch.enable_autodispatch', isset($etoPost['enable_autodispatch']) ? (int)$etoPost['enable_autodispatch'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.time_every_minute', isset($etoPost['time_every_minute']) ? (int)$etoPost['time_every_minute'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.time_to_assign', isset($etoPost['time_to_assign']) ? (int)$etoPost['time_to_assign'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.extra_time_slot', isset($etoPost['extra_time_slot']) ? (int)$etoPost['extra_time_slot'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.check_within_radius', isset($etoPost['check_within_radius']) ? (int)$etoPost['check_within_radius'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.time_to_confirm', isset($etoPost['time_to_confirm']) ? (int)$etoPost['time_to_confirm'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.check_availability_status', isset($etoPost['check_availability_status']) ? (int)$etoPost['check_availability_status'] : 0, 'subscription', $subscriptionId],
                ['eto_dispatch.only_auto_dispatch_status', isset($etoPost['only_auto_dispatch_status']) ? (int)$etoPost['only_auto_dispatch_status'] : 0, 'subscription', $subscriptionId],
                // ['eto_dispatch.time_last_seen', isset($etoPost['time_last_seen']) ? (int)$etoPost['time_last_seen'] : 0, 'subscription', $subscriptionId],
                // ['eto_dispatch.check_trashed', isset($etoPost['check_trashed']) ? (int)$etoPost['check_trashed'] : 0, 'subscription', $subscriptionId],
                // ['eto_dispatch.delete_expired', isset($etoPost['delete_expired']) ? (int)$etoPost['delete_expired'] : 0, 'subscription', $subscriptionId],
                // ['eto_dispatch.assign_max_drivers', isset($etoPost['assign_max_drivers']) ? (int)$etoPost['assign_max_drivers'] : 1, 'subscription', $subscriptionId],
                // ['eto_dispatch.assign_driver_on_status_change', isset($etoPost['assign_driver_on_status_change']) ? (int)$etoPost['assign_driver_on_status_change'] : 0, 'subscription', $subscriptionId],
                // ['eto_dispatch.assign_driver_on_reject', isset($etoPost['assign_driver_on_reject']) ? (int)$etoPost['assign_driver_on_reject'] : 0, 'subscription', $subscriptionId],
            ]);
        }

        // Booking
        if (!$settingsGroup || $settingsGroup == 'booking') {
            // Vehicle min price
            $vehicleMinPriceListPost = (array)json_decode($etoPost['vehicleMinPriceList'], true);
            $vehicleMinPriceList = array();
            foreach($vehicleMinPriceListPost as $key => $value) {
                if (!empty($value) && !empty($value['id'])) {
                    $row = new \stdClass();
                    $row->id = (int)$value['id'];
                    $row->value = (float)$value['value'];
                    $vehicleMinPriceList[] = $row;
                }
            }

            $configList = array_merge($configList, [
                'booking_vehicle_min_price' => ['type' => 'object',	'browser' => '0', 'value' => json_encode((array)$vehicleMinPriceList)],
                'ref_format' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['ref_format']],
                'currency_symbol' => ['type' => 'string', 'browser' => '1', 'value' => (string)$etoPost['currency_symbol']],
                'currency_code' => ['type' => 'string', 'browser' => '1', 'value' => (string)$etoPost['currency_code']],
                'booking_min_price_type' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['booking_min_price_type']],
                'fixed_prices_priority' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['fixed_prices_priority']],
                'booking_round_total_price' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['booking_round_total_price']],
                'booking_include_aiport_charges' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['booking_include_aiport_charges'] == '1' ) ? 1 : 0],
                'booking_summary_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_summary_enable'] == '1' ) ? 1 : 0],
                'incomplete_bookings_display' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['incomplete_bookings_display'] == '1' ) ? 1 : 0],
                'incomplete_bookings_delete_enable' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['incomplete_bookings_delete_enable'] == '1' ) ? 1 : 0],
                'incomplete_bookings_delete_after' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['incomplete_bookings_delete_after']],
                'min_booking_time_limit' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['min_booking_time_limit']],
                'booking_cancel_enable' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['booking_cancel_enable'] == '1' ) ? 1 : 0],
                'booking_cancel_time' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['booking_cancel_time']],
                'booking_listing_refresh_type' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['booking_listing_refresh_type']],
                'booking_listing_refresh_interval' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['booking_listing_refresh_interval']],
                'booking_listing_refresh_counter' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['booking_listing_refresh_counter']],
                'booking_meeting_board_enabled' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['booking_meeting_board_enabled']],
                'booking_meeting_board_attach' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['booking_meeting_board_attach']],
                'booking_meeting_board_font_size' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['booking_meeting_board_font_size']],
                'booking_meeting_board_header' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['booking_meeting_board_header']],
                'booking_meeting_board_footer' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['booking_meeting_board_footer']],
            ]);

            $chargesList = array_merge($chargesList, [
                'distance_min' => (float)$etoPost['distance_min'],
            ]);
        }

        // Web Booking Widget
        if (!$settingsGroup || $settingsGroup == 'web_booking_widget') {
            $configList = array_merge($configList, [
                'booking_price_status' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_price_status']],
                'booking_price_status_on' => ['type' => 'int', 'browser' => '1', 'value' => (string)$etoPost['booking_price_status_on'] == '1' ? 1 : 0],
                'booking_price_status_on_enquiry' => ['type' => 'int', 'browser' => '1', 'value' => (string)$etoPost['booking_price_status_on_enquiry'] == '1' ? 1 : 0],
                'booking_price_status_off' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_price_status_off']],
                'booking_display_widget_header' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_widget_header'] == '1' ) ? 1 : 0],
                'booking_show_preferred' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_show_preferred'] == '1' ) ? 1 : 0],
                'booking_display_return_journey' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_return_journey'] == '1' ) ? 1 : 0],
                'booking_display_via' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_via'] == '1' ) ? 1 : 0],
                'booking_display_swap' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_swap'] == '1' ) ? 1 : 0],
                'booking_display_geolocation' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_geolocation'] == '1' ) ? 1 : 0],
                'booking_service_dropdown' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_service_dropdown'] == '1' ) ? 1 : 0],
                'booking_service_display_mode' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['booking_service_display_mode']],
                'booking_date_picker_style' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_date_picker_style']],
                'booking_time_picker_style' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_time_picker_style']],
                'booking_time_picker_steps' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_time_picker_steps']],
                'booking_time_picker_by_minute' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_time_picker_by_minute'] == '1' ) ? 1 : 0],
                'booking_force_home_address' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_force_home_address'] == '1' ) ? 1 : 0],
                'booking_meet_and_greet_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_meet_and_greet_enable'] == '1' ) ? 1 : 0],
                'booking_meet_and_greet_compulsory' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_meet_and_greet_compulsory'] == '1' ) ? 1 : 0],
                'booking_hide_vehicle_not_available_message' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_hide_vehicle_not_available_message'] == '1' ) ? 1 : 0],
                'booking_display_book_button' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_book_button'] == '1' ) ? 1 : 0],
                'enable_passengers' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_passengers'] == '1' ) ? 1 : 0],
                'enable_luggage' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_luggage'] == '1' ) ? 1 : 0],
                'enable_hand_luggage' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_hand_luggage'] == '1' ) ? 1 : 0],
                'enable_child_seats' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_child_seats'] == '1' ) ? 1 : 0],
                'enable_baby_seats' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_baby_seats'] == '1' ) ? 1 : 0],
                'enable_infant_seats' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_infant_seats'] == '1' ) ? 1 : 0],
                'enable_wheelchair' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['enable_wheelchair'] == '1' ) ? 1 : 0],
                'booking_vehicle_display_mode' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['booking_vehicle_display_mode']],
                'booking_summary_display_mode' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['booking_summary_display_mode']],
                'booking_map_zoom' => ['type' => 'int',	'browser' => '1', 'value' => (int)$etoPost['booking_map_zoom']],
                'booking_map_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_map_enable'] == '1' ) ? 1 : 0],
                'booking_map_open' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_map_open'] == '1' ) ? 1 : 0],
                'booking_map_draggable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_map_draggable'] == '1' ) ? 1 : 0],
                'booking_map_zoomcontrol' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_map_zoomcontrol'] == '1' ) ? 1 : 0],
                'booking_map_scrollwheel' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_map_scrollwheel'] == '1' ) ? 1 : 0],
                'booking_directions_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_directions_enable'] == '1' ) ? 1 : 0],
                'booking_hide_cash_payment_if_airport' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_hide_cash_payment_if_airport'] == '1' ) ? 1 : 0],
                'booking_allow_guest_checkout' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_allow_guest_checkout'] == '1' ) ? 1 : 0],
                'booking_allow_account_payment' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_allow_account_payment'] == '1' ) ? 1 : 0],
                'booking_show_more_options' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_show_more_options'] == '1' ) ? 1 : 0],
                'booking_member_benefits_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_member_benefits_enable'] == '1' ) ? 1 : 0],
                'booking_member_benefits' => ['type' => 'string',	'browser' => '1', 'value' => (string)$etoPost['booking_member_benefits']],
                'booking_show_second_passenger' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_show_second_passenger'] == '1' ) ? 1 : 0],
                'booking_show_requirements' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_show_requirements'] == '1' ) ? 1 : 0],
                'booking_account_autocompletion' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_account_autocompletion'] == '1' ) ? 1 : 0],
                'booking_required_contact_mobile' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_contact_mobile'] == '1' ) ? 1 : 0],
                'booking_required_address_complete_from' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_address_complete_from'] == '1' ) ? 1 : 0],
                'booking_required_address_complete_to' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_address_complete_to'] == '1' ) ? 1 : 0],
                'booking_required_address_complete_via' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_address_complete_via'] == '1' ) ? 1 : 0],
                'booking_required_departure_city' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_departure_city'] == '1' ) ? 1 : 0],
                'booking_required_flight_number' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_flight_number'] == '1' ) ? 1 : 0],
                'booking_required_flight_landing_time' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_flight_landing_time'] == '1' ) ? 1 : 0],
                'booking_required_departure_flight_number' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_departure_flight_number'] == '1' ) ? 1 : 0],
                'booking_required_departure_flight_time' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_departure_flight_time'] == '1' ) ? 1 : 0],
                'booking_required_departure_flight_city' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_departure_flight_city'] == '1' ) ? 1 : 0],
                'booking_flight_landing_time_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_flight_landing_time_enable'] == '1' ) ? 1 : 0],
                'booking_departure_flight_time_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_departure_flight_time_enable'] == '1' ) ? 1 : 0],
                'booking_departure_flight_time_check_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_departure_flight_time_check_enable'] == '1' ) ? 1 : 0],
                'booking_departure_flight_time_check_value' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_departure_flight_time_check_value']],
                'booking_waiting_time_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_waiting_time_enable'] == '1' ) ? 1 : 0],
                'booking_required_waiting_time' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_waiting_time'] == '1' ) ? 1 : 0],
                'booking_required_passengers' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_passengers'] == '1' ) ? 1 : 0],
                'booking_required_luggage' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_luggage'] == '1' ) ? 1 : 0],
                'booking_required_hand_luggage' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_hand_luggage'] == '1' ) ? 1 : 0],
                'booking_allow_one_type_of_child_seat' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_allow_one_type_of_child_seat'] == '1' ) ? 1 : 0],
                'booking_required_child_seats' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_child_seats'] == '1' ) ? 1 : 0],
                'booking_required_baby_seats' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_baby_seats'] == '1' ) ? 1 : 0],
                'booking_required_infant_seats' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_infant_seats'] == '1' ) ? 1 : 0],
                'booking_required_wheelchair' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_required_wheelchair'] == '1' ) ? 1 : 0],
                'auto_payment_redirection' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['auto_payment_redirection'] == '1' ) ? 1 : 0],
                'booking_terms_disable_button' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_terms_disable_button'] == '1' ) ? 1 : 0],
                'booking_display_book_by_phone' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_display_book_by_phone'] == '1' ) ? 1 : 0],
                'booking_attach_ical' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_attach_ical'] == '1' ) ? 1 : 0],
                'booking_scroll_to_top_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_scroll_to_top_enable'] == '1' ) ? 1 : 0],
                'booking_scroll_to_top_offset' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['booking_scroll_to_top_offset']],
                'booking_pricing_mode' => ['type' => 'string', 'browser' => '0', 'value' => (int)$etoPost['booking_pricing_mode']],
                'booking_request_enable' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['booking_request_enable'] == '1' ) ? 1 : 0],
                'booking_request_time' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['booking_request_time']],
                'booking_auto_confirm_time' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['booking_auto_confirm_time']],
            ]);
        }

        // Google
        if (!$settingsGroup || $settingsGroup == 'google') {
            $configList = array_merge($configList, [
                'booking_distance_unit' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['booking_distance_unit']],
                'quote_enable_shortest_route' => ['type' => 'int', 'browser' => '0', 'value' => (string)$etoPost['quote_enable_shortest_route']],
                'quote_avoid_highways' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['quote_avoid_highways'] == '1' ) ? 1 : 0],
                'quote_avoid_tolls' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['quote_avoid_tolls'] == '1' ) ? 1 : 0],
                'quote_avoid_ferries' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['quote_avoid_ferries'] == '1' ) ? 1 : 0],
                'quote_duration_in_traffic' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['quote_duration_in_traffic'] == '1' ) ? 1 : 0],
                'quote_traffic_model' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['quote_traffic_model']],
                'quote_enable_straight_line' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['quote_enable_straight_line'] == '1' ) ? 1 : 0],
                'booking_return_as_oneway' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['booking_return_as_oneway'] == '1' ) ? 1 : 0],
                'booking_postcode_match' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['booking_postcode_match'] == '1' ) ? 1 : 0],
                'autocomplete_google_places' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['autocomplete_google_places'] == '1' ) ? 1 : 0],
                'autocomplete_force_selection' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['autocomplete_force_selection'] == '1' ) ? 1 : 0],
                'booking_advanced_geocoding' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_advanced_geocoding'] == '1' ) ? 1 : 0],
                'booking_location_search_min' => ['type' => 'string',	'browser' => '1', 'value' => (int)$etoPost['booking_location_search_min']],
                'google_region_code' => ['type' => 'string', 'browser' => '1', 'value' => (string)$etoPost['google_region_code']],
                'google_country_code' => ['type' => 'string', 'browser' => '1', 'value' => (string)$etoPost['google_country_code']],
                'google_language' => ['type' => 'string', 'browser' => '1', 'value' => (string)$etoPost['google_language']],
                'google_analytics_tracking_id' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['google_analytics_tracking_id'])],
                'google_adwords_conversion_id' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['google_adwords_conversion_id'])],
                'google_adwords_conversions' => ['type' => 'object', 'browser' => '1', 'value' => (string)$etoPost['google_adwords_conversions']],
                'quote_address_suffix' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['quote_address_suffix']],
                'locations_skip_place_id' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['locations_skip_place_id']],
                'google_maps_javascript_api_key' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['google_maps_javascript_api_key'])],
                'google_maps_embed_api_key' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['google_maps_embed_api_key'])],
                'google_maps_directions_api_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['google_maps_directions_api_key'])],
                'google_places_api_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['google_places_api_key'])],
                'google_maps_geocoding_api_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['google_maps_geocoding_api_key'])],
            ]);
        }

        // Distance & Time Pricing
        if (!$settingsGroup || $settingsGroup == 'mileage_time') {
            // Vehicle List
            $vehicleList = array();

            $sql = "SELECT `id`, `name`
                FROM `{$dbPrefix}vehicle`
                WHERE `site_id`='" . $siteId . "' ORDER BY `name`";
            $query = $db->select($sql);

            if ( !empty($query) ) {
                foreach($query as $k => $v) {
                    $vehicleList[] = array(
                        'id' => (int)$v->id,
                        'name' => (string)$v->name,
                    );
                }
            }

            // Distance ranges - Save
            $distanceRanges = (array)json_decode($etoPost['distanceRanges'], true);
            $distanceRange = array();
            foreach($distanceRanges as $key => $value) {
                if (!empty($value)) {
                    $row = new \stdClass();
                    $row->distance = (float)$value['distance'];
                    $row->value = (float)$value['value'];
                    $row->factor_type = (int)$value['factor_type'];

                    $vehicleTemp = array();
                    foreach($vehicleList as $k => $v) {
                      $vehicleId = $v['id'];
                      $vehicleTemp[] = array(
                        'id' => (int)$vehicleId,
                        'value' => (float)$value['vehicle'.$vehicleId]
                      );
                    }
                    $row->vehicle = $vehicleTemp;

                    $index = round((float)$row->distance * 100);
                    $distanceRange[$index] = $row;
                }
            }
            ksort($distanceRange);
            $distanceRangeTemp = array();
            foreach($distanceRange as $key => $value) {
                $distanceRangeTemp[] = $value;
            }
            $distanceRange = $distanceRangeTemp;

            $configList = array_merge($configList, [
                'quote_distance_range' => ['type' => 'object',	'browser' => '0', 'value' => json_encode((array)$distanceRange)],
                'booking_duration_rate' => ['type' => 'float', 'browser' => '0', 'value' => (float)$etoPost['booking_duration_rate']],
            ]);
        }

        // Deposit Payments
        if (!$settingsGroup || $settingsGroup == 'deposit_payments') {
            // Deposit
            $depositListPost = (array)json_decode($etoPost['depositList'], true);
            $depositList = array();
            foreach($depositListPost as $key => $value) {
                if (!empty($value)) {
                    $row = new \stdClass();
                    $row->id = (int)$value['id'];
                    $row->type = (string)$value['type'];
                    $row->value = (float)$value['value'];
                    $depositList[$row->id] = $row;
                }
            }
            ksort($depositList);
            $temp = array();
            foreach($depositList as $key => $value) {
                $temp[] = $value;
            }
            $depositList = $temp;

            $configList = array_merge($configList, [
                'booking_deposit' => ['type' => 'object',	'browser' => '0', 'value' => json_encode((array)$depositList)],
                'booking_deposit_balance' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['booking_deposit_balance']],
                'booking_deposit_selected' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['booking_deposit_selected']],
                'fixed_prices_deposit_enable' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['fixed_prices_deposit_enable']],
                'fixed_prices_deposit_type' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['fixed_prices_deposit_type']],
            ]);
        }

        // Driver Income
        if (!$settingsGroup || $settingsGroup == 'driver_income') {
            $settingsList = array_merge($settingsList, [
                ['eto_driver.income.child_seats', isset($etoPost['driver_income_child_seats']) ? $etoPost['driver_income_child_seats'] : config('eto_driver.income.child_seats'), 'site', $siteId],
                ['eto_driver.income.additional_items', isset($etoPost['driver_income_additional_items']) ? $etoPost['driver_income_additional_items'] : config('eto_driver.income.additional_items'), 'site', $siteId],
                ['eto_driver.income.parking_charges', isset($etoPost['driver_income_parking_charges']) ? $etoPost['driver_income_parking_charges'] : config('eto_driver.income.parking_charges'), 'site', $siteId],
                ['eto_driver.income.payment_charges', isset($etoPost['driver_income_payment_charges']) ? $etoPost['driver_income_payment_charges'] : config('eto_driver.income.payment_charges'), 'site', $siteId],
                ['eto_driver.income.meet_and_greet', isset($etoPost['driver_income_meet_and_greet']) ? $etoPost['driver_income_meet_and_greet'] : config('eto_driver.income.meet_and_greet'), 'site', $siteId],
                ['eto_driver.income.discounts', isset($etoPost['driver_income_discounts']) ? $etoPost['driver_income_discounts'] : config('eto_driver.income.discounts'), 'site', $siteId],
            ]);
        }

        // Operating Areas
        if (!$settingsGroup || $settingsGroup == 'bases') {
            // Bases
            function getGeocode($address) {
                global $gConfig;

                $language = explode('-', $gConfig['language']);

                $params = array(
                    'key' => config('site.google_maps_geocoding_api_key'),
                    'address' => trim($address),
                );

                if ( !empty($gConfig['google_language']) ) {
                    $params['language'] = strtolower($gConfig['google_language']);
                }
                else {
                    $params['language'] = ($language[0]) ? strtolower($language[0]) : 'en';
                }

                if ( !empty($gConfig['google_region_code']) ) {
                    $params['region'] = strtolower($gConfig['google_region_code']);
                }

                if ( !empty($gConfig['google_country_code']) ) {
                    $list = [];
                    $codes = explode(',', $gConfig['google_country_code']);
                    foreach ($codes as $kC => $vC) {
                        $vC = strtolower(trim($vC));
                        if ( !empty($vC) ) {
                            $list[] = 'country:'. $vC;
                        }
                    }
                    if ( !empty($list) ) {
                        $params['components'] = implode('|', $list);
                    }
                }

                $hash = 'g_geocode_'. md5(json_encode($params));
                $cache_expiry_time = config('site.google_cache_expiry_time') ? config('site.google_cache_expiry_time') : config('site.google_cache_runtime');
                $response = null;

                if ($cache_expiry_time && cache($hash)) {
                    $response = cache($hash);
                }

                if (empty($response) && (!empty($params['place_id']) || !empty($params['latlng']) || !empty($params['address']))) {
                    $client = new \GuzzleHttp\Client();
                    $request = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json', [
                        'headers' => [
                            'accept' => 'application/json',
                            'accept-encoding' => 'gzip, deflate',
                            'content-type' => 'application/json'
                        ],
                        'query' => $params
                    ]);
                    $response = json_decode($request->getBody());

                    if (!empty($response) && in_array($response->status, ['OK', 'ZERO_RESULTS']) && $cache_expiry_time && !cache($hash)) {
                        cache([$hash => $response], $cache_expiry_time);
                    }
                }
                return $response;
            }

            $basesListPost = (array)json_decode($etoPost['basesList'], true);
            $skipIds = [];
            foreach($basesListPost as $key => $value) {
                if (!empty($value) && !empty($value['address'])) {
                    $base = \App\Models\Base::where('relation_type', '=', 'site')
                      ->where('relation_id', '=', $siteId)
                      ->where('address', '=', $value['address'])
                      ->first();

                    if ( empty($base->id) ) {
                        $base = new \App\Models\Base;
                        $base->relation_type = 'site';
                        $base->relation_id = $siteId;
                    }

                    if ( $base->address != $value['address'] ) {
                        $geocode = getGeocode($value['address']);
                        if ( $geocode->status == 'OK' ) {
                            $location = $geocode->results[0]->geometry->location;
                            $base->lat = $location->lat;
                            $base->lng = $location->lng;
                        }
                        else {
                            $base->lat = null;
                            $base->lng = null;
                        }
                    }

                    $base->address = (string)$value['address'];
                    $base->radius = $value['radius'] ?: 0;
                    $base->status = (string)$value['status'];
                    $base->save();

                    $skipIds[] = $base->id;
                }
            }

            \App\Models\Base::where('relation_type', '=', 'site')
                ->where('relation_id', '=', $siteId)
                ->whereNotIn('id', $skipIds)
                ->delete();

            $configList = array_merge($configList, [
                'booking_base_action' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['booking_base_action']],
                'booking_exclude_driver_journey_from_fixed_price' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_exclude_driver_journey_from_fixed_price'] == '1' ) ? 1 : 0],
                'booking_base_calculate_type_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['booking_base_calculate_type_enable'] == '1' ) ? 1 : 0],
                'booking_base_calculate_type' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['booking_base_calculate_type']],
            ]);
        }

        // Night Surcharge
        if (!$settingsGroup || $settingsGroup == 'night_surcharge') {
            $nightSurchargeListPost = (array)json_decode($etoPost['nightSurchargeList'], true);
            $nightSurchargeList = array();
            $lastNightSurchargeIndex = 0;
            foreach($nightSurchargeListPost as $key => $value) {
                if (!empty($value)) {
                    $row = new \stdClass();
                    $row->vehicle_id = (int)$value['vehicle_id'];
                    $row->repeat_days = $value['repeat_days'] != '' ? (array)explode(',', $value['repeat_days']) : [];
                    $row->time_start = (string)$value['time_start'];
                    $row->time_end = (string)$value['time_end'];
                    $row->factor_type = (string)$value['factor_type'];
                    $row->factor_value = (float)$value['factor_value'];

                    $lastNightSurchargeIndex += 0.01;
                    $index = (string)($row->vehicle_id + $lastNightSurchargeIndex);
                    $nightSurchargeList[$index] = $row;
                }
            }
            ksort($nightSurchargeList);
            $temp = array();
            foreach($nightSurchargeList as $key => $value) {
                $temp[] = $value;
            }
            $nightSurchargeList = $temp;

            $configList = array_merge($configList, [
                'booking_night_surcharge' => ['type' => 'object',	'browser' => '0', 'value' => json_encode((array)$nightSurchargeList)],
                'night_charge_enable' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['night_charge_enable'] == '1' ) ? 1 : 0],
                'night_charge_start' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['night_charge_start']],
                'night_charge_end' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['night_charge_end']],
                'night_charge_factor_type' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['night_charge_factor_type']],
                'night_charge_factor' => ['type' => 'float', 'browser' => '0', 'value' => (float)$etoPost['night_charge_factor']],
            ]);
        }

        // Holiday / Rush Hours Surcharge
        if (!$settingsGroup || $settingsGroup == 'holiday_surcharge') {
            // Date mileage override
            $charges = (array)json_decode($etoPost['charges'], true);
            $skipEntries = array();

            foreach($charges as $key => $value) {
                if ( !empty($value) && (!empty($value['value']) || !empty($value['note'])) ) {
                    $params = [];
                    $params['factor_type'] = isset($value['factor_type']) ? (int)$value['factor_type'] : 0;

                    $row = new \stdClass();
                    $row->id = (int)$value['id'];
                    $row->site_id = ($site_id) ? $site_id : (int)$siteId;
                    $row->note = (string)$value['note'];
                    $row->note_published = 1;
                    $row->type = 'distance_override';
                    $row->params = json_encode($params);
                    $row->value = (float)$value['value'];
                    $row->start_date = (string)$value['start_date'] ?: null;
                    $row->end_date = (string)$value['end_date'] ?: null;
                    $row->published = 1;

                    $sql = "SELECT `id`
                            FROM `{$dbPrefix}charge`
                            WHERE `site_id`='" . $siteId . "'
                            AND `id`='". $row->id ."'
                            LIMIT 1";

                    $results = $db->select($sql);
                    if (!empty($results[0])) {
                        $results = $results[0];
                    }

                    if ( !empty($results) ) {
                        $row->id = (int)$results->id;
                        $results = \DB::table('charge')->where('id', $row->id)->update((array)$row);
                        $results = $row->id;

                        if ( empty($results) ) {
                            $data['message'][] = 'Date milage override could not be updated.';
                        }
                    }
                    else {
                        $row->id = null;
                        $results = \DB::table('charge')->insertGetId((array)$row);
                        $row->id = $results;

                        if ( empty($results) ) {
                            $data['message'][] = 'Date milage override could not be updated.';
                        }
                    }

                    $skipEntries[] = $row->id;
                }
            }

            $sql = "";
            if ( !empty($skipEntries) ) {
                $ids = implode(',', $skipEntries);
                $sql = "AND `id` NOT IN (". $ids .")";
            }

            $sql = "DELETE FROM `{$dbPrefix}charge`
                    WHERE `site_id`='" . $siteId . "'
                    AND `type`='distance_override'". $sql;
            $results = $db->delete($sql);

            if ( !empty($results) ) {
                $data['message'][] = 'Records could not be deleted.';
            }
        }

        // Additional Charges
        if (!$settingsGroup || $settingsGroup == 'additional_charges') {
            // Items
            $itemsListPost = (array)json_decode($etoPost['itemsList'], true);
            $itemsList = array();
            foreach($itemsListPost as $key => $value) {
                if (!empty($value) && !empty($value['name'])) {
                    $slug = str_slug((string)$value['name'], '_');
                    $slug = \App\Helpers\SiteHelper::seoFriendlyUrl($slug);

                    $row = new \stdClass();
                    $row->id = $slug;
                    $row->name = (string)$value['name'];
                    $row->value = (float)$value['value'];
                    $row->type = !empty($value['type']) ? (string)$value['type'] : 'amount';
                    $row->amount = (int)$value['amount'];

                    $custom = !empty($value['custom']) ? explode(',', (string)$value['custom']) : [];
                    foreach($custom as $kC => $vC) {
                        $custom[$kC] = trim($vC);
                    }
                    $row->custom = $custom;

                    $itemsList[] = $row;
                }
            }

            $configList = array_merge($configList, [
                'booking_items' => ['type' => 'object',	'browser' => '1', 'value' => json_encode((array)$itemsList)],
            ]);

            $chargesList = array_merge($chargesList, [
                'meet_and_greet' => (float)$etoPost['meet_and_greet'],
                'child_seat' => (float)$etoPost['child_seat'],
                'baby_seat' => (float)$etoPost['baby_seat'],
                'infant_seats' => (float)$etoPost['infant_seats'],
                'wheelchair' => (float)$etoPost['wheelchair'],
                'waiting_time' => (float)$etoPost['waiting_time'],
                'geocode_start' => (float)$etoPost['geocode_start'],
                'geocode_end' => (float)$etoPost['geocode_end'],
                'waypoint' => (float)$etoPost['waypoint'],
            ]);
        }

        // Return Journey and Account Discounts
        if (!$settingsGroup || $settingsGroup == 'other_discounts') {
            $configList = array_merge($configList, [
                'booking_return_discount' => ['type' => 'float', 'browser' => '1', 'value' => (float)$etoPost['booking_return_discount']],
                'booking_account_discount' => ['type' => 'float', 'browser' => '1', 'value' => (float)$etoPost['booking_account_discount']],
            ]);

            $settingsList = array_merge($settingsList, [
                ['eto_booking.discount.child_seats', isset($etoPost['booking_discount_child_seats']) ? $etoPost['booking_discount_child_seats'] : config('eto_booking.discount.child_seats'), 'site', $siteId],
                ['eto_booking.discount.additional_items', isset($etoPost['booking_discount_additional_items']) ? $etoPost['booking_discount_additional_items'] : config('eto_booking.discount.additional_items'), 'site', $siteId],
                ['eto_booking.discount.parking_charges', isset($etoPost['booking_discount_parking_charges']) ? $etoPost['booking_discount_parking_charges'] : config('eto_booking.discount.parking_charges'), 'site', $siteId],
                // ['eto_booking.discount.payment_charges', isset($etoPost['booking_discount_payment_charges']) ? $etoPost['booking_discount_payment_charges'] : config('eto_booking.discount.payment_charges'), 'site', $siteId],
                ['eto_booking.discount.meet_and_greet', isset($etoPost['booking_discount_meet_and_greet']) ? $etoPost['booking_discount_meet_and_greet'] : config('eto_booking.discount.meet_and_greet'), 'site', $siteId],
            ]);
        }

        // Tax
        if (!$settingsGroup || $settingsGroup == 'tax') {
            $configList = array_merge($configList, [
                'company_tax_number' => ['type' => 'string',	'browser' => '1', 'value' => trim((string)$etoPost['company_tax_number'])],
                'tax_name' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['tax_name']],
                'tax_percent' => ['type' => 'float', 'browser' => '0', 'value' => (float)$etoPost['tax_percent']],
            ]);
        }

        // Invoices
        if (!$settingsGroup || $settingsGroup == 'invoices') {
            $configList = array_merge($configList, [
                'invoice_enabled' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_enabled'] == '1' ) ? 1 : 0],
                'invoice_display_details' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_display_details'] == '1' ) ? 1 : 0],
                'invoice_display_logo' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_display_logo'] == '1' ) ? 1 : 0],
                'invoice_display_payments' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_display_payments'] == '1' ) ? 1 : 0],
                'invoice_display_custom_field' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_display_custom_field'] == '1' ) ? 1 : 0],
                'invoice_display_company_number' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_display_company_number'] == '1' ) ? 1 : 0],
                'invoice_display_company_tax_number' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['invoice_display_company_tax_number'] == '1' ) ? 1 : 0],
                'invoice_info' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['invoice_info']],
                'invoice_bill_from' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['invoice_bill_from']],
            ]);
        }

        // Airport Detection
        if (!$settingsGroup || $settingsGroup == 'airport_detection') {
            // geocode_start_postcodes
            $tempList = array();
            $tempString = (string)$etoPost['geocode_start_postcodes'];
            $tempString = str_replace(array("\r\n", "\r", "\n"), ",", $tempString);
            $tempString = str_replace(",,", ",", $tempString);
            $tempString = str_replace("  ", " ", $tempString);
            $tempExplode = explode(",", $tempString);
            foreach($tempExplode as $k => $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $tempList[] = $v;
                }
            }
            $geocode_start_postcodes = '[]';
            if (!empty($tempList)) {
                $geocode_start_postcodes = json_encode($tempList);
            }

            // geocode_end_postcodes
            $tempList = array();
            $tempString = (string)$etoPost['geocode_end_postcodes'];
            $tempString = str_replace(array("\r\n", "\r", "\n"), ",", $tempString);
            $tempString = str_replace(",,", ",", $tempString);
            $tempString = str_replace("  ", " ", $tempString);
            $tempExplode = explode(",", $tempString);
            foreach($tempExplode as $k => $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $tempList[] = $v;
                }
            }
            $geocode_end_postcodes = '[]';
            if (!empty($tempList)) {
                $geocode_end_postcodes = json_encode($tempList);
            }

            // airport_postcodes
            $tempList = array();
            $tempString = (string)$etoPost['airport_postcodes'];
            $tempString = str_replace(array("\r\n", "\r", "\n"), ",", $tempString);
            $tempString = str_replace(",,", ",", $tempString);
            $tempString = str_replace("  ", " ", $tempString);
            $tempExplode = explode(",", $tempString);
            foreach($tempExplode as $k => $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $tempList[] = $v;
                }
            }
            $airport_postcodes = '[]';
            if (!empty($tempList)) {
                $airport_postcodes = json_encode($tempList);
            }

            $chargesList = array_merge($chargesList, [
                'geocode_start_postcodes' => $geocode_start_postcodes,
                'geocode_end_postcodes' => $geocode_end_postcodes,
                'airport_postcodes' => $airport_postcodes,
            ]);
        }

        // Users
        if (!$settingsGroup || $settingsGroup == 'users') {
            $configList = array_merge($configList, [
                'login_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['login_enable'] == '1' ) ? 1 : 0],
                'register_enable' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['register_enable'] == '1' ) ? 1 : 0],
                'register_activation_enable' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['register_activation_enable'] == '1' ) ? 1 : 0],
                'password_length_min' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['password_length_min']],
                'password_length_max' => ['type' => 'int', 'browser' => '1', 'value' => (int)$etoPost['password_length_max']],
                'customer_allow_company_number' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['customer_allow_company_number']],
                'customer_require_company_number' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['customer_require_company_number']],
                'customer_allow_company_tax_number' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['customer_allow_company_tax_number']],
                'customer_require_company_tax_number' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['customer_require_company_tax_number']],
                'user_show_company_name' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['user_show_company_name']],
                'customer_attach_booking_details_to_sms' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['customer_attach_booking_details_to_sms'] == '1' ) ? 1 : 0],
                'customer_attach_booking_details_access_link' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['customer_attach_booking_details_access_link'] == '1' ) ? 1 : 0],
                'customer_show_only_lead_passenger' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['customer_show_only_lead_passenger'] == '1' ) ? 1 : 0],
                'driver_show_total' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_total']],
                'driver_show_unique_id' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_unique_id']],
                'driver_show_onroute_button' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_onroute_button']],
                'driver_show_arrived_button' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_arrived_button']],
                'driver_show_onboard_button' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_onboard_button']],
                'driver_show_reject_button' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_reject_button']],
                'driver_show_restart_button' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_restart_button']],
                'driver_allow_cancel' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_allow_cancel']],
                'driver_show_passenger_phone_number' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_passenger_phone_number']],
                'driver_show_passenger_email' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_passenger_email']],
                'driver_show_edit_profile_button' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_edit_profile_button']],
                'driver_show_edit_profile_insurance' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_edit_profile_insurance']],
                'driver_show_edit_profile_driving_licence' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_edit_profile_driving_licence']],
                'driver_show_edit_profile_pco_licence' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_edit_profile_pco_licence']],
                'driver_show_edit_profile_phv_licence' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['driver_show_edit_profile_phv_licence']],
                'driver_attach_booking_details_to_email' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['driver_attach_booking_details_to_email'] == '1' ) ? 1 : 0],
                'driver_attach_booking_details_to_sms' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['driver_attach_booking_details_to_sms'] == '1' ) ? 1 : 0],
                'driver_calendar_show_ref_number' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_ref_number']],
                'driver_calendar_show_from' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_from']],
                'driver_calendar_show_to' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_to']],
                'driver_calendar_show_via' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_via']],
                'driver_calendar_show_vehicle_type' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_vehicle_type']],
                'driver_calendar_show_estimated_time' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_estimated_time']],
                'driver_calendar_show_actual_time_slot' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_actual_time_slot']],
                'driver_calendar_show_passengers' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_passengers']],
                'driver_calendar_show_custom' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['driver_calendar_show_custom']],
                'admin_booking_listing_highlight' => ['type' => 'string',	'browser' => '0', 'value' => (int)$etoPost['admin_booking_listing_highlight']],
                'admin_calendar_show_ref_number' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_ref_number']],
                'admin_calendar_show_name_passenger' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_name_passenger']],
                'admin_calendar_show_name_customer' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_name_customer']],
                'admin_calendar_show_service_type' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_service_type']],
                'admin_calendar_show_from' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_from']],
                'admin_calendar_show_to' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_to']],
                'admin_calendar_show_via' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_via']],
                'admin_calendar_show_vehicle_type' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_vehicle_type']],
                'admin_calendar_show_estimated_time' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_estimated_time']],
                'admin_calendar_show_actual_time_slot' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['admin_calendar_show_actual_time_slot']],
                'admin_default_page' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['admin_default_page'])],
            ]);

            $settingsList = array_merge($settingsList, [
                // Problem with loading default values in config helper
                // ['eto_calendar.show.driver_name', isset($etoPost['admin_calendar_show_driver_name']) ? (int)$etoPost['admin_calendar_show_driver_name'] : config('eto_calendar.show.driver_name'), 'site', $siteId],
                // ['eto_calendar.show.passengers', isset($etoPost['admin_calendar_show_passengers']) ? (int)$etoPost['admin_calendar_show_passengers'] : config('eto_calendar.show.passengers'), 'site', $siteId],
                // ['eto_calendar.show.custom', isset($etoPost['admin_calendar_show_custom']) ? (int)$etoPost['admin_calendar_show_custom'] : config('eto_calendar.show.custom'), 'site', $siteId],

                ['eto_calendar.show.driver_name', isset($etoPost['admin_calendar_show_driver_name']) ? (int)$etoPost['admin_calendar_show_driver_name'] : 0, 'site', $siteId],
                ['eto_calendar.show.passengers', isset($etoPost['admin_calendar_show_passengers']) ? (int)$etoPost['admin_calendar_show_passengers'] : 0, 'site', $siteId],
                ['eto_calendar.show.custom', isset($etoPost['admin_calendar_show_custom']) ? (int)$etoPost['admin_calendar_show_custom'] : 0, 'site', $siteId],
                ['eto_driver.booking_file_upload', isset($etoPost['driver_booking_file_upload']) ? $etoPost['driver_booking_file_upload'] : 0, 'subscription', $subscriptionId],
            ]);
        }

        // Styles
        if (!$settingsGroup || $settingsGroup == 'styles') {
            $configList = array_merge($configList, [
                'styles_border_radius' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['styles_border_radius']],
                'styles_default_bg_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['styles_default_bg_color']],
                'styles_default_border_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['styles_default_border_color']],
                'styles_default_text_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['styles_default_text_color']],
                'styles_active_bg_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['styles_active_bg_color']],
                'styles_active_border_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['styles_active_border_color']],
                'styles_active_text_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['styles_active_text_color']],
                'custom_css' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['custom_css']],
                'mobile_app_styles_border_radius' => ['type' => 'int', 'browser' => '0', 'value' => (int)$etoPost['mobile_app_styles_border_radius']],
                'mobile_app_styles_default_bg_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_styles_default_bg_color']],
                'mobile_app_styles_default_border_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_styles_default_border_color']],
                'mobile_app_styles_default_text_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_styles_default_text_color']],
                'mobile_app_styles_active_bg_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_styles_active_bg_color']],
                'mobile_app_styles_active_border_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_styles_active_border_color']],
                'mobile_app_styles_active_text_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_styles_active_text_color']],
                'mobile_app_custom_css' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['mobile_app_custom_css']],
                'invoice_styles_default_bg_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['invoice_styles_default_bg_color']],
                'invoice_styles_default_text_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['invoice_styles_default_text_color']],
                'invoice_styles_active_bg_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['invoice_styles_active_bg_color']],
                'invoice_styles_active_text_color' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['invoice_styles_active_text_color']],
            ]);
        }

        // Integration
        if (!$settingsGroup || $settingsGroup == 'integration') {
            $configList = array_merge($configList, [
                'code_head' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['code_head']],
                'code_body' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['code_body']],
                'eto_branding' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['eto_branding'] == '1' ) ? 1 : 0],
                'force_https' => ['type' => 'int',	'browser' => '0', 'value' => (string)$etoPost['force_https']],
                'mail_driver' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['mail_driver']],
                'mail_host' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['mail_host'])],
                'mail_port' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['mail_port'])],
                'mail_username' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['mail_username'])],
                'mail_password' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['mail_password'])],
                'mail_encryption' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['mail_encryption']],
                'mail_sendmail' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['mail_sendmail'])],
                'callerid_type' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['callerid_type']],
                'ringcentral_environment' => ['type' => 'string',	'browser' => '0', 'value' => (string)$etoPost['ringcentral_environment']],
                'ringcentral_app_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['ringcentral_app_key'])],
                'ringcentral_app_secret' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['ringcentral_app_secret'])],
                'ringcentral_widget_open' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['ringcentral_widget_open'])],
                'ringcentral_popup_open' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['ringcentral_popup_open'])],
                'flightstats_enabled' => ['type' => 'int',	'browser' => '0', 'value' => (string)$etoPost['flightstats_enabled'] == '1' ? 1 : 0],
                'flightstats_app_id' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['flightstats_app_id'])],
                'flightstats_app_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['flightstats_app_key'])],
                'sms_service_type' => ['type' => 'string', 'browser' => '0', 'value' => (string)$etoPost['sms_service_type']],
                'textlocal_api_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['textlocal_api_key'])],
                'textlocal_test_mode' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['textlocal_test_mode'] == '1' ) ? 1 : 0],
                'twilio_sid' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['twilio_sid'])],
                'twilio_token' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['twilio_token'])],
                'twilio_phone_number' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['twilio_phone_number'])],
                'smsgateway_api_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['smsgateway_api_key'])],
                'smsgateway_device_id' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['smsgateway_device_id'])],
                'pcapredict_enabled' => ['type' => 'int', 'browser' => '0', 'value' => ( (string)$etoPost['pcapredict_enabled'] == '1' ) ? 1 : 0],
                'pcapredict_api_key' => ['type' => 'string',	'browser' => '0', 'value' => trim((string)$etoPost['pcapredict_api_key'])],
            ]);
        }

        // Debug
        if (!$settingsGroup || $settingsGroup == 'debug') {
            $configList = array_merge($configList, [
                'debug' => ['type' => 'int', 'browser' => '1', 'value' => ( (string)$etoPost['debug'] == '1' ) ? 1 : 0],
                'google_cache_expiry_time' => ['type' => 'int',	'browser' => '0', 'value' => (int)$etoPost['google_cache_expiry_time']],
            ]);
        }

        // Save config
        foreach($configList as $k => $v) {
            $row = new \stdClass();
            $row->value = $v['value'];
            $row->type = $v['type'];
            $row->browser = $v['browser'];

            $sql = "SELECT `id`
                FROM `{$dbPrefix}config`
                WHERE `site_id`='" . $siteId . "'
                AND `key`='". $k ."'
                LIMIT 1";
            $query = $db->select($sql);
            if (!empty($query[0])) {
                $query = $query[0];
            }

            if ( !empty($query) ) {
                $row->id = (int)$query->id;

                $results = \DB::table('config')->where('id', $row->id)->update((array)$row);
                $results = $row->id;
            }
            else {
                $row->id = null;
                $row->site_id = ($site_id) ? $site_id : (int)$siteId;
                $row->key = $k;

                $results = \DB::table('config')->insertGetId((array)$row);
                $row->id = $results;
            }

            if ( empty($results) ){
                $data['message'][] = $k .' could not be updated.';
            }
        }

        // Save charges
        foreach($chargesList as $k => $v) {
            if ( isset($v) ) {
                $paramsList = array(
                    'geocode_start_postcodes',
                    'geocode_end_postcodes',
                    'airport_postcodes'
                );

                if ( in_array($k, $paramsList) ) {
                    $kTemp = $k;

                    if ( $k == 'geocode_start_postcodes' ) {
                        $kTemp = 'geocode_start';
                    }
                    else if ( $k == 'geocode_end_postcodes' ) {
                        $kTemp = 'geocode_end';
                    }

                    $sql = "UPDATE `{$dbPrefix}charge` SET `params`='". $v ."' WHERE `site_id`='". $siteId ."' AND `type`='". $kTemp ."' LIMIT 1";
                    $query = $db->update($sql);
                }
                else {
                    $check = \DB::table('charge')->where('site_id', $siteId)->where('type', $k)->first();

                    if ( empty($check->id) ) {
                        $row = [
                          'id' => null,
                          'site_id' => $siteId,
                          'note' => '',
                          'note_published' => 1,
                          'type' => $k,
                          'params' => '',
                          'value' => $v,
                          'start_date' => null,
                          'end_date' => null,
                          'published' => 1,
                        ];

                        switch( $k ) {
                            case 'meet_and_greet':
                                $row['note'] = 'Meet and greet';
                            break;
                            case 'child_seat':
                                $row['note'] = 'Child seat';
                            break;
                            case 'baby_seat':
                                $row['note'] = 'Booster seat';
                            break;
                            case 'infant_seats':
                                $row['note'] = 'Infant seat';
                            break;
                            case 'wheelchair':
                                $row['note'] = 'Wheelchair';
                            break;
                            case 'waypoint':
                                $row['note'] = 'Extra pick up / drop off';
                                $row['note_published'] = 0;
                            break;
                            case 'waiting_time':
                                $row['note'] = 'Waiting time after landing';
                            break;
                        }

                        $query = \DB::table('charge')->insertGetId((array)$row);
                    }
                    else {
                        $sql = "UPDATE `{$dbPrefix}charge` SET `value`='". $v ."' WHERE `site_id`='". $siteId ."' AND `type`='". $k ."' LIMIT 1";
                        $query = $db->update($sql);
                    }
                }

                if ( empty($query) ) {
                    $data['message'][] = $k .' could not be updated.';
                }
            }
        }

        // Save settings
        if (!empty($settingsList)) {
            // dd($settingsList);
            settings_save($settingsList, null, null, null, true);
        }
        else {
            settings_load(null, null, true);
        }

        $data['message'][] = 'Settings has been saved';
        $data['success'] = true;

    break;
}
