<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];
    protected $dates = [];
    public $timestamps = false;
    public $configData = null;
    public $mapping = [];

    function __construct()
    {
        parent::__construct();

        $this->configData = new \stdClass();

        $this->mapping = [
            'debug' => ['int', 'app.debug'],
            'language' => ['string', ['app.locale', 'site.language']],
            'locale_switcher_enabled' => ['int', 'app.locale_switcher_enabled'],
            'locale_switcher_display_name_code' => ['int', 'app.locale_switcher_display_name_code'],
            'locale_switcher_style' => ['string', 'app.locale_switcher_style'],
            'locale_switcher_display' => ['string', 'app.locale_switcher_display'],
            'locale_active' => ['array', 'app.locale_active'],
            'timezone' => ['string', 'app.timezone'],
            'mail_driver' => ['string', 'mail.driver'],
            'mail_host' => ['string', 'mail.host'],
            'mail_port' => ['int', 'mail.port'],
            'mail_username' => ['string', 'mail.username'],
            'mail_password' => ['string', 'mail.password'],
            'mail_encryption' => ['string', 'mail.encryption'],
            'mail_sendmail' => ['string', 'mail.sendmail'],
            'callerid_type' => ['string', 'site.callerid_type'],
            'ringcentral_environment' => ['string', 'services.ringcentral.environment'],
            'ringcentral_app_key' => ['string', 'services.ringcentral.app_key'],
            'ringcentral_app_secret' => ['string', 'services.ringcentral.app_secret'],
            'ringcentral_widget_open' => ['string', 'services.ringcentral.widget_open'],
            'ringcentral_popup_open' => ['string', 'services.ringcentral.popup_open'],
            'flightstats_enabled' => ['string', 'services.flightstats.enabled'],
            'flightstats_app_id' => ['string', 'services.flightstats.app_id'],
            'flightstats_app_key' => ['string', 'services.flightstats.app_key'],
            'sms_service_type' => ['', 'services.sms_service_type'],
            'textlocal_api_key' => ['string', 'services.textlocal.key'],
            'textlocal_test_mode' => ['', 'services.textlocal.test'],
            'twilio_sid' => ['', 'services.twilio.sid'],
            'twilio_token' => ['', 'services.twilio.token'],
            'twilio_phone_number' => ['', 'services.twilio.phone_number'],
            'smsgateway_api_key' => ['string', 'services.smsgateway.key'],
            'smsgateway_device_id' => ['int', 'services.smsgateway.device_id'],
            'pcapredict_enabled' => ['', 'services.pcapredict.enabled'],
            'pcapredict_api_key' => ['string', 'services.pcapredict.key'],
            'force_https' => ['int', 'site.force_https'],
            'company_name' => ['string', ['site.company_name', 'app.name', 'mail.from.name']],
            'company_email' => ['string', ['site.company_email', 'mail.from.address']],
            'company_address' => ['string', 'site.company_address'],
            'company_number' => ['string', 'site.company_number'],
            'company_tax_number' => ['string', 'site.company_tax_number'],
            'company_telephone' => ['string', 'site.company_telephone'],
            'eto_branding' => ['int', 'site.branding'],
            'embedded' => ['int', 'site.embed'],
            'date_format' => ['string', 'site.date_format'],
            'time_format' => ['string', 'site.time_format'],
            'start_of_week' => ['', 'site.start_of_week'],
            'logo' => ['string', 'site.logo'],
            'url_locales' => ['array', 'site.url_locales'],
            'url_home' => ['string', 'site.url_home'],
            'url_booking' => ['string', 'site.url_booking'],
            'url_customer' => ['string', 'site.url_customer'],
            'url_driver' => ['string', 'site.url_driver'],
            'url_contact' => ['string', 'site.url_contact'],
            'url_feedback' => ['string', 'site.url_feedback'],
            'url_terms' => ['string', 'site.url_terms'],
            'feedback_type' => ['string', 'site.feedback_type'],
            'terms_type' => ['string', 'site.terms_type'],
            'terms_text' => ['string', 'site.terms_text'],
            'terms_email' => ['string', 'site.terms_email'],
            'terms_download' => ['string', 'site.terms_download'],
            'terms_enable' => ['string', 'site.terms_enable'],
            'ref_format' => ['string', 'site.ref_format'],
            'currency_symbol' => ['string', 'site.currency_symbol'],
            'currency_code' => ['string', 'site.currency_code'],
            'booking_distance_unit' => ['int', 'site.booking_distance_unit'],
            'booking_return_as_oneway' => ['int', 'site.booking_return_as_oneway'],
            'booking_postcode_match' => ['int', 'site.booking_postcode_match'],
            'booking_hide_vehicle_not_available_message' => ['int', 'site.booking_hide_vehicle_not_available_message'],
            'booking_pricing_mode' => ['int', 'site.booking_pricing_mode'],
            'booking_display_book_by_phone' => ['int', 'site.booking_display_book_by_phone'],
            'booking_attach_ical' => ['int', 'site.booking_attach_ical'],
            'booking_show_preferred' => ['int', 'site.booking_show_preferred'],
            'booking_show_second_passenger' => ['int', 'site.booking_show_second_passenger'],
            'booking_time_picker_steps' => ['int', 'site.booking_time_picker_steps'],
            'booking_time_picker_by_minute' => ['int', 'site.booking_time_picker_by_minute'],
            'booking_required_address_complete_from' => ['int', 'site.booking_required_address_complete_from'],
            'booking_required_address_complete_to' => ['int', 'site.booking_required_address_complete_to'],
            'booking_required_address_complete_via' => ['int', 'site.booking_required_address_complete_via'],
            'booking_force_home_address' => ['int', 'site.booking_force_home_address'],
            'booking_show_requirements' => ['int', 'site.booking_show_requirements'],
            'booking_service_dropdown' => ['int', 'site.booking_service_dropdown'],
            'booking_service_display_mode' => ['', 'site.booking_service_display_mode'],
            'booking_location_search_min' => ['', 'site.booking_location_search_min'],
            'booking_display_widget_header' => ['', 'site.booking_display_widget_header'],
            'booking_display_return_journey' => ['', 'site.booking_display_return_journey'],
            'booking_display_via' => ['', 'site.booking_display_via'],
            'booking_display_swap' => ['', 'site.booking_display_swap'],
            'booking_display_geolocation' => ['', 'site.booking_display_geolocation'],
            'booking_display_book_button' => ['', 'site.booking_display_book_button'],
            'booking_member_benefits' => ['', 'site.booking_member_benefits'],
            'booking_member_benefits_enable' => ['', 'site.booking_member_benefits_enable'],
            'booking_hide_cash_payment_if_airport' => ['', 'site.booking_hide_cash_payment_if_airport'],
            'booking_allow_account_payment' => ['', 'site.booking_allow_account_payment'],
            'booking_show_more_options' => ['', 'site.booking_show_more_options'],
            'booking_allow_guest_checkout' => ['', 'site.booking_allow_guest_checkout'],
            'booking_summary_display_mode' => ['', 'site.booking_summary_display_mode'],
            'booking_vehicle_display_mode' => ['', 'site.booking_vehicle_display_mode'],
            'booking_scroll_to_top_enable' => ['int', 'site.booking_scroll_to_top_enable'],
            'booking_scroll_to_top_offset' => ['int', 'site.booking_scroll_to_top_offset'],
            'booking_advanced_geocoding' => ['', 'site.booking_advanced_geocoding'],
            'booking_price_status' => ['', 'site.booking_price_status'],
            'booking_price_status_on' => ['', 'site.booking_price_status_on'],
            'booking_price_status_on_enquiry' => ['', 'site.booking_price_status_on_enquiry'],
            'booking_price_status_off' => ['', 'site.booking_price_status_off'],
            'booking_request_enable' => ['', 'site.booking_request_enable'],
            'booking_request_time' => ['', 'site.booking_request_time'],
            'booking_auto_confirm_time' => ['', 'site.booking_auto_confirm_time'],
            'booking_base_action' => ['', 'site.booking_base_action'],
            'booking_base_calculate_type' => ['', 'site.booking_base_calculate_type'],
            'booking_base_calculate_type_enable' => ['', 'site.booking_base_calculate_type_enable'],
            'booking_exclude_driver_journey_from_fixed_price' => ['', 'site.booking_exclude_driver_journey_from_fixed_price'],
            'booking_listing_refresh_type' => ['', 'site.booking_listing_refresh_type'],
            'booking_listing_refresh_interval' => ['', 'site.booking_listing_refresh_interval'],
            'booking_listing_refresh_counter' => ['', 'site.booking_listing_refresh_counter'],
            'booking_summary_enable' => ['int', 'site.booking_summary_enable'],
            'fixed_prices_priority' => ['', 'site.fixed_prices_priority'],
            'fixed_prices_deposit_enable' => ['', 'site.fixed_prices_deposit_enable'],
            'fixed_prices_deposit_type' => ['', 'site.fixed_prices_deposit_type'],
            'user_show_company_name' => ['', 'site.user_show_company_name'],
            'customer_allow_company_number' => ['', 'site.customer_allow_company_number'],
            'customer_require_company_number' => ['', 'site.customer_require_company_number'],
            'customer_allow_company_tax_number' => ['', 'site.customer_allow_company_tax_number'],
            'customer_require_company_tax_number' => ['', 'site.customer_require_company_tax_number'],
            'driver_show_total' => ['', 'site.driver_show_total'],
            'driver_show_unique_id' => ['', 'site.driver_show_unique_id'],
            'driver_show_edit_profile_button' => ['', 'site.driver_show_edit_profile_button'],
            'driver_show_edit_profile_insurance' => ['', 'site.driver_show_edit_profile_insurance'],
            'driver_show_edit_profile_driving_licence' => ['', 'site.driver_show_edit_profile_driving_licence'],
            'driver_show_edit_profile_pco_licence' => ['', 'site.driver_show_edit_profile_pco_licence'],
            'driver_show_edit_profile_phv_licence' => ['', 'site.driver_show_edit_profile_phv_licence'],
            'driver_show_reject_button' => ['', 'site.driver_show_reject_button'],
            'driver_show_onroute_button' => ['', 'site.driver_show_onroute_button'],
            'driver_show_arrived_button' => ['', 'site.driver_show_arrived_button'],
            'driver_show_onboard_button' => ['', 'site.driver_show_onboard_button'],
            'driver_allow_cancel' => ['', 'site.driver_allow_cancel'],
            'booking_meeting_board_enabled' => ['', 'site.booking_meeting_board_enabled'],
            'booking_meeting_board_attach' => ['', 'site.booking_meeting_board_attach'],
            'booking_meeting_board_font_size' => ['', 'site.booking_meeting_board_font_size'],
            'booking_meeting_board_header' => ['', 'site.booking_meeting_board_header'],
            'booking_meeting_board_footer' => ['', 'site.booking_meeting_board_footer'],
            'driver_show_restart_button' => ['', 'site.driver_show_restart_button'],
            'driver_show_passenger_phone_number' => ['', 'site.driver_show_passenger_phone_number'],
            'driver_show_passenger_email' => ['', 'site.driver_show_passenger_email'],
            'driver_attach_booking_details_to_email' => ['', 'site.driver_attach_booking_details_to_email'],
            'driver_attach_booking_details_to_sms' => ['', 'site.driver_attach_booking_details_to_sms'],
            'driver_calendar_show_ref_number' => ['', 'site.driver_calendar_show_ref_number'],
            'driver_calendar_show_from' => ['', 'site.driver_calendar_show_from'],
            'driver_calendar_show_to' => ['', 'site.driver_calendar_show_to'],
            'driver_calendar_show_via' => ['', 'site.driver_calendar_show_via'],
            'driver_calendar_show_vehicle_type' => ['', 'site.driver_calendar_show_vehicle_type'],
            'driver_calendar_show_estimated_time' => ['', 'site.driver_calendar_show_estimated_time'],
            'driver_calendar_show_actual_time_slot' => ['', 'site.driver_calendar_show_actual_time_slot'],
            'driver_calendar_show_passengers' => ['', 'site.driver_calendar_show_passengers'],
            'driver_calendar_show_custom' => ['', 'site.driver_calendar_show_custom'],
            'customer_attach_booking_details_to_sms' => ['', 'site.customer_attach_booking_details_to_sms'],
            'customer_attach_booking_details_access_link' => ['', 'site.customer_attach_booking_details_access_link'],
            'customer_show_only_lead_passenger' => ['', 'site.customer_show_only_lead_passenger'],
            'admin_booking_listing_highlight' => ['', 'site.admin_booking_listing_highlight'],
            'admin_calendar_show_ref_number' => ['', 'site.admin_calendar_show_ref_number'],
            'admin_calendar_show_name_passenger' => ['', 'site.admin_calendar_show_name_passenger'],
            'admin_calendar_show_name_customer' => ['', 'site.admin_calendar_show_name_customer'],
            'admin_calendar_show_service_type' => ['', 'site.admin_calendar_show_service_type'],
            'admin_calendar_show_from' => ['', 'site.admin_calendar_show_from'],
            'admin_calendar_show_to' => ['', 'site.admin_calendar_show_to'],
            'admin_calendar_show_via' => ['', 'site.admin_calendar_show_via'],
            'admin_calendar_show_vehicle_type' => ['', 'site.admin_calendar_show_vehicle_type'],
            'admin_calendar_show_estimated_time' => ['', 'site.admin_calendar_show_estimated_time'],
            'admin_calendar_show_actual_time_slot' => ['', 'site.admin_calendar_show_actual_time_slot'],
            'admin_default_page' => ['', 'site.admin_default_page'],
            'invoice_enabled' => ['', 'site.invoice_enabled'],
            'invoice_display_details' => ['', 'site.invoice_display_details'],
            'invoice_display_logo' => ['', 'site.invoice_display_logo'],
            'invoice_display_payments' => ['', 'site.invoice_display_payments'],
            'invoice_display_custom_field' => ['', 'site.invoice_display_custom_field'],
            'invoice_display_company_number' => ['', 'site.invoice_display_company_number'],
            'invoice_display_company_tax_number' => ['', 'site.invoice_display_company_tax_number'],
            'invoice_info' => ['', 'site.invoice_info'],
            'invoice_bill_from' => ['', 'site.invoice_bill_from'],
            'invoice_styles_default_bg_color' => ['string', 'site.invoice_styles_default_bg_color'],
            'invoice_styles_default_text_color' => ['string', 'site.invoice_styles_default_text_color'],
            'invoice_styles_active_bg_color' => ['string', 'site.invoice_styles_active_bg_color'],
            'invoice_styles_active_text_color' => ['string', 'site.invoice_styles_active_text_color'],
            'tax_name' => ['string', 'site.tax_name'],
            'tax_percent' => ['', 'site.tax_percent'],
            'styles_border_radius' => ['int', 'site.styles_border_radius'],
            'styles_default_bg_color' => ['string', 'site.styles_default_bg_color'],
            'styles_default_border_color' => ['string', 'site.styles_default_border_color'],
            'styles_default_text_color' => ['string', 'site.styles_default_text_color'],
            'styles_active_bg_color' => ['string', 'site.styles_active_bg_color'],
            'styles_active_border_color' => ['string', 'site.styles_active_border_color'],
            'styles_active_text_color' => ['string', 'site.styles_active_text_color'],
            'custom_css' => ['string', 'site.custom_css'],
            'mobile_app_styles_border_radius' => ['int', 'site.mobile_app_styles_border_radius'],
            'mobile_app_styles_default_bg_color' => ['string', 'site.mobile_app_styles_default_bg_color'],
            'mobile_app_styles_default_border_color' => ['string', 'site.mobile_app_styles_default_border_color'],
            'mobile_app_styles_default_text_color' => ['string', 'site.mobile_app_styles_default_text_color'],
            'mobile_app_styles_active_bg_color' => ['string', 'site.mobile_app_styles_active_bg_color'],
            'mobile_app_styles_active_border_color' => ['string', 'site.mobile_app_styles_active_border_color'],
            'mobile_app_styles_active_text_color' => ['string', 'site.mobile_app_styles_active_text_color'],
            'mobile_app_custom_css' => ['string', 'site.mobile_app_custom_css'],
            'code_head' => ['string', 'site.code_head'],
            'code_body' => ['string', 'site.code_body'],
            'google_maps_javascript_api_key' => ['string', 'site.google_maps_javascript_api_key'],
            'google_maps_embed_api_key' => ['string', 'site.google_maps_embed_api_key'],
            'google_maps_directions_api_key' => ['string', 'site.google_maps_directions_api_key'],
            'google_maps_geocoding_api_key' => ['string', 'site.google_maps_geocoding_api_key'],
            'google_places_api_key' => ['string', 'site.google_places_api_key'],
            'google_cache_expiry_time' => ['int', 'site.google_cache_expiry_time'],
            'google_analytics_tracking_id' => ['string', 'site.google_analytics_tracking_id'],
            'google_adwords_conversion_id' => ['string', 'site.google_adwords_conversion_id'],
            'google_adwords_conversions' => ['string', 'site.google_adwords_conversions'],
            'notifications' => ['object', 'site.notifications'],
            'notification_booking_pending_info' => ['string', 'site.notification_booking_pending_info'],
            'notification_test_email' => ['string', 'site.notification_test_email'],
            'notification_test_phone' => ['string', 'site.notification_test_phone'],
        ];
    }

    public static function scopeOfSite($query, $id = 0)
    {
        if (!$id) {
            $id = config('site.site_id');
        }
        return $query->where('site_id', $id);
    }

    public static function scopeWhereKeys($query, $keys = [])
    {
        if (!empty($keys)) {
            $query->whereIn('key', $keys);
        }
        return $query;
    }

    public static function scopeToObject($query)
    {
        $config = $query->orderBy('key', 'asc')->get()->pluck('value', 'key')->all();

        foreach($query->getQuery()->wheres as $k => $v) {
            if ( $v['column'] == 'key' ) {
                if ( $v['type'] == 'In' ) {
                    foreach($v['values'] as $value) {
                        if ( !isset($config[$value]) ) {
                            $config[$value] = null;
                        }
                    }
                }
                else {
                    if ( !isset($config[$v['value']]) ) {
                        $config[$v['value']] = null;
                    }
                }
            }
        }

        $mapping = (new Config)->mapping;

        foreach ($mapping as $k => $v) {
            if (is_array($v[1])) {
                $key = $v[1][0];
            }
            else {
                $key = $v[1];
            }

            $value = null;

            if (isset($config[$k])) {
                $value = $config[$k];

                switch ($v[0]) {
                    case 'int':
                        $value = (int)$value;
                    break;
                    case 'float':
                        $value = (float)$value;
                    break;
                    case 'object':
                        $value = json_decode($value);
                    break;
                    case 'array':
                        $value = json_decode($value, true);
                    break;
                    default:
                        $value = (string)$value;
                    break;
                }
            }

            if (is_null($value)) {
                $value = config($key);
            }

            $config[$k] = $value;
        }

        // dd($config, $query->getQuery()->wheres);

        return (object)$config;
    }

    public function scopeToConfig($query, $type = 0)
    {
        $config = $query->toObject($query);

        $mapping = (new Config)->mapping;
        $list = [];

        foreach ($mapping as $k => $v) {
            if (is_array($v[1])) {
                $map = (array)$v[1];
            }
            else {
                $map = [$v[1]];
            }

            foreach ($map as $mapK => $mapV) {
                switch ($k) {
                    case 'google_maps_javascript_api_key':
                    case 'google_maps_embed_api_key':
                    case 'google_maps_directions_api_key':
                    case 'google_maps_geocoding_api_key':
                    case 'google_places_api_key':
                    case 'google_analytics_tracking_id':
                    case 'google_adwords_conversion_id':
                    case 'textlocal_api_key':
                    case 'pcapredict_api_key':
                    case 'ringcentral_app_key':
                    case 'ringcentral_app_secret':
                    case 'flightstats_app_id':
                    case 'flightstats_app_key':
                    case 'language':
                    case 'timezone':
                    case 'date_format':
                    case 'time_format':
                    case 'mail_driver';
                    case 'mail_host';
                    case 'mail_port';
                    case 'mail_username';
                    case 'mail_password';
                    case 'mail_encryption';
                    case 'mail_sendmail';
                    case 'styles_default_bg_color';
                    case 'styles_default_border_color';
                    case 'styles_default_text_color';
                    case 'styles_active_bg_color';
                    case 'styles_active_border_color';
                    case 'styles_active_text_color';
                    case 'invoice_styles_default_bg_color';
                    case 'invoice_styles_default_text_color';
                    case 'invoice_styles_active_bg_color';
                    case 'invoice_styles_active_text_color';
                        if (!empty($config->{$k})) {
                            $list[$mapV] = $config->{$k};
                        }
                    break;
                    default:
                        if (isset($config->{$k})) {
                            $list[$mapV] = $config->{$k};
                        }
                    break;
                }
            }
        }

        ksort($list);

        // $list['app.debug'] = 1;
        // $list['mail.driver'] = 'log';
        // dd($list);

        if ( !empty($list) ) {
            config($list);
        }

        return $type ? $config : $list;
    }

    public static function getBySiteId($id = 0)
    {
        $config = [];

        if ( $id > 0 ) {
            $query = Config::where('site_id', $id)->orderBy('key', 'asc')->get();

            foreach($query as $v) {
                switch( $v->type ) {
                    case 'int':
                        $v->value = (int)$v->value;
                    break;
                    case 'float':
                        $v->value = (float)$v->value;
                    break;
                    case 'string':
                        $v->value = (string)$v->value;
                    break;
                    case 'object':
                        $v->value = json_decode($v->value);
                    break;
                    case 'array':
                        $v->value = json_decode($v->value, true);
                    break;
                    default:
                        $v->value = $v->value;
                    break;
                }

                $config[$v->key] = $v->value;
            }
        }

        $config = (object)$config;

        $response = new Config;
        $response->configData = $config;

        if (!empty($config->language)) {
            $response->loadLocale($config->language);
        }

        return $response;
    }

    public function loadLocale($locale = '')
    {
        $config = $this->configData;
        $urls = !empty($config->url_locales) ? json_decode($config->url_locales, true) : config('site.url_locales');

        if (empty($locale)) {
            $locale = app()->getLocale();
        }

        if (isset($urls[$locale])) {
            foreach($urls[$locale] as $k => $v) {
                if (!empty($v)) {
                    $config->{$k} = $v;
                }
            }
        }

        $config->language = $locale;
        $this->configData = $config;

        return $this;
    }

    public function mapData()
    {
        $map = [
            ['app.debug', 'debug'],
            ['app.name', 'company_name'],
            ['app.locale', 'language'],
            ['app.locale_switcher_enabled', 'locale_switcher_enabled'],
            ['app.locale_switcher_display_name_code', 'locale_switcher_display_name_code'],
            ['app.locale_switcher_style', 'locale_switcher_style'],
            ['app.locale_switcher_display', 'locale_switcher_display'],
            ['app.locale_active', 'locale_active'],
            ['app.timezone', 'timezone'],
            ['mail.from.name', 'company_name'],
            ['mail.from.address', 'company_email'],
            ['mail.driver', 'mail_driver'],
            ['mail.host', 'mail_host'],
            ['mail.port', 'mail_port'],
            ['mail.username', 'mail_username'],
            ['mail.password', 'mail_password'],
            ['mail.encryption', 'mail_encryption'],
            ['mail.sendmail', 'mail_sendmail'],
            ['site.callerid_type', 'callerid_type'],
            ['services.ringcentral.environment', 'ringcentral_environment'],
            ['services.ringcentral.app_key', 'ringcentral_app_key'],
            ['services.ringcentral.app_secret', 'ringcentral_app_secret'],
            ['services.ringcentral.widget_open', 'ringcentral_widget_open'],
            ['services.ringcentral.popup_open', 'ringcentral_popup_open'],
            ['services.flightstats.enabled', 'flightstats_enabled'],
            ['services.flightstats.app_id', 'flightstats_app_id'],
            ['services.flightstats.app_key', 'flightstats_app_key'],
            ['services.sms_service_type', 'sms_service_type'],
            ['services.textlocal.key', 'textlocal_api_key'],
            ['services.textlocal.test', 'textlocal_test_mode'],
            ['services.twilio.sid', 'twilio_sid'],
            ['services.twilio.token', 'twilio_token'],
            ['services.twilio.phone_number', 'twilio_phone_number'],
            ['services.smsgateway.key', 'smsgateway_api_key'],
            ['services.smsgateway.device_id', 'smsgateway_device_id'],
            ['services.pcapredict.enabled', 'pcapredict_enabled'],
            ['services.pcapredict.key', 'pcapredict_api_key'],
            ['site.language', 'language'],
            ['site.force_https', 'force_https'],
            ['site.company_name', 'company_name'],
            ['site.company_address', 'company_address'],
            ['site.company_number', 'company_number'],
            ['site.company_tax_number', 'company_tax_number'],
            ['site.company_email', 'company_email'],
            ['site.company_telephone', 'company_telephone'],
            ['site.branding', 'eto_branding'],
            ['site.embed', 'embedded'],
            ['site.date_format', 'date_format'],
            ['site.time_format', 'time_format'],
            ['site.start_of_week', 'start_of_week'],
            ['site.logo', 'logo'],
            ['site.url_locales', 'url_locales'],
            ['site.url_home', 'url_home'],
            ['site.url_booking', 'url_booking'],
            ['site.url_customer', 'url_customer'],
            ['site.url_driver', 'url_driver'],
            ['site.url_contact', 'url_contact'],
            ['site.url_feedback', 'url_feedback'],
            ['site.url_terms', 'url_terms'],
            ['site.feedback_type', 'feedback_type'],
            ['site.terms_type', 'terms_type'],
            ['site.terms_text', 'terms_text'],
            ['site.terms_email', 'terms_email'],
            ['site.terms_download', 'terms_download'],
            ['site.terms_enable', 'terms_enable'],
            ['site.ref_format', 'ref_format'],
            ['site.currency_symbol', 'currency_symbol'],
            ['site.currency_code', 'currency_code'],
            ['site.booking_distance_unit', 'booking_distance_unit'],
            ['site.booking_return_as_oneway', 'booking_return_as_oneway'],
            ['site.booking_postcode_match', 'booking_postcode_match'],
            ['site.booking_hide_vehicle_not_available_message', 'booking_hide_vehicle_not_available_message'],
            ['site.booking_pricing_mode', 'booking_pricing_mode'],
            ['site.booking_display_book_by_phone', 'booking_display_book_by_phone'],
            ['site.booking_attach_ical', 'booking_attach_ical'],
            ['site.booking_show_preferred', 'booking_show_preferred'],
            ['site.booking_show_second_passenger', 'booking_show_second_passenger'],
            ['site.booking_time_picker_steps', 'booking_time_picker_steps'],
            ['site.booking_time_picker_by_minute', 'booking_time_picker_by_minute'],
            ['site.booking_required_address_complete_from', 'booking_required_address_complete_from'],
            ['site.booking_required_address_complete_to', 'booking_required_address_complete_to'],
            ['site.booking_required_address_complete_via', 'booking_required_address_complete_via'],
            ['site.booking_force_home_address', 'booking_force_home_address'],
            ['site.booking_show_requirements', 'booking_show_requirements'],
            ['site.booking_service_dropdown', 'booking_service_dropdown'],
            ['site.booking_service_display_mode', 'booking_service_display_mode'],
            ['site.booking_location_search_min', 'booking_location_search_min'],
            ['site.booking_display_widget_header', 'booking_display_widget_header'],
            ['site.booking_display_return_journey', 'booking_display_return_journey'],
            ['site.booking_display_via', 'booking_display_via'],
            ['site.booking_display_swap', 'booking_display_swap'],
            ['site.booking_display_geolocation', 'booking_display_geolocation'],
            ['site.booking_display_book_button', 'booking_display_book_button'],
            ['site.booking_member_benefits', 'booking_member_benefits'],
            ['site.booking_member_benefits_enable', 'booking_member_benefits_enable'],
            ['site.booking_hide_cash_payment_if_airport', 'booking_hide_cash_payment_if_airport'],
            ['site.booking_allow_account_payment', 'booking_allow_account_payment'],
            ['site.booking_show_more_options', 'booking_show_more_options'],
            ['site.booking_allow_guest_checkout', 'booking_allow_guest_checkout'],
            ['site.booking_summary_display_mode', 'booking_summary_display_mode'],
            ['site.booking_vehicle_display_mode', 'booking_vehicle_display_mode'],
            ['site.booking_scroll_to_top_enable', 'booking_scroll_to_top_enable'],
            ['site.booking_scroll_to_top_offset', 'booking_scroll_to_top_offset'],
            ['site.booking_advanced_geocoding', 'booking_advanced_geocoding'],
            ['site.booking_price_status', 'booking_price_status'],
            ['site.booking_price_status_on', 'booking_price_status_on'],
            ['site.booking_price_status_on_enquiry', 'booking_price_status_on_enquiry'],
            ['site.booking_price_status_off', 'booking_price_status_off'],
            ['site.booking_request_enable', 'booking_request_enable'],
            ['site.booking_request_time', 'booking_request_time'],
            ['site.booking_auto_confirm_time', 'booking_auto_confirm_time'],
            ['site.booking_base_action', 'booking_base_action'],
            ['site.booking_base_calculate_type', 'booking_base_calculate_type'],
            ['site.booking_base_calculate_type_enable', 'booking_base_calculate_type_enable'],
            ['site.booking_exclude_driver_journey_from_fixed_price', 'booking_exclude_driver_journey_from_fixed_price'],
            ['site.booking_listing_refresh_type', 'booking_listing_refresh_type'],
            ['site.booking_listing_refresh_interval', 'booking_listing_refresh_interval'],
            ['site.booking_listing_refresh_counter', 'booking_listing_refresh_counter'],
            ['site.booking_summary_enable', 'booking_summary_enable'],
            ['site.fixed_prices_priority', 'fixed_prices_priority'],
            ['site.fixed_prices_deposit_enable', 'fixed_prices_deposit_enable'],
            ['site.fixed_prices_deposit_type', 'fixed_prices_deposit_type'],
            ['site.user_show_company_name', 'user_show_company_name'],
            ['site.customer_allow_company_number', 'customer_allow_company_number'],
            ['site.customer_require_company_number', 'customer_require_company_number'],
            ['site.customer_allow_company_tax_number', 'customer_allow_company_tax_number'],
            ['site.customer_require_company_tax_number', 'customer_require_company_tax_number'],
            ['site.driver_show_total', 'driver_show_total'],
            ['site.driver_show_unique_id', 'driver_show_unique_id'],
            ['site.driver_show_edit_profile_button', 'driver_show_edit_profile_button'],
            ['site.driver_show_edit_profile_insurance', 'driver_show_edit_profile_insurance'],
            ['site.driver_show_edit_profile_driving_licence', 'driver_show_edit_profile_driving_licence'],
            ['site.driver_show_edit_profile_pco_licence', 'driver_show_edit_profile_pco_licence'],
            ['site.driver_show_edit_profile_phv_licence', 'driver_show_edit_profile_phv_licence'],
            ['site.driver_show_reject_button', 'driver_show_reject_button'],
            ['site.driver_show_onroute_button', 'driver_show_onroute_button'],
            ['site.driver_show_arrived_button', 'driver_show_arrived_button'],
            ['site.driver_show_onboard_button', 'driver_show_onboard_button'],
            ['site.driver_allow_cancel', 'driver_allow_cancel'],
            ['site.booking_meeting_board_enabled', 'booking_meeting_board_enabled'],
            ['site.booking_meeting_board_attach', 'booking_meeting_board_attach'],
            ['site.booking_meeting_board_font_size', 'booking_meeting_board_font_size'],
            ['site.booking_meeting_board_header', 'booking_meeting_board_header'],
            ['site.booking_meeting_board_footer', 'booking_meeting_board_footer'],
            ['site.driver_show_restart_button', 'driver_show_restart_button'],
            ['site.driver_show_passenger_phone_number', 'driver_show_passenger_phone_number'],
            ['site.driver_show_passenger_email', 'driver_show_passenger_email'],
            ['site.driver_attach_booking_details_to_email', 'driver_attach_booking_details_to_email'],
            ['site.driver_attach_booking_details_to_sms', 'driver_attach_booking_details_to_sms'],
            ['site.driver_calendar_show_ref_number', 'driver_calendar_show_ref_number'],
            ['site.driver_calendar_show_from', 'driver_calendar_show_from'],
            ['site.driver_calendar_show_to', 'driver_calendar_show_to'],
            ['site.driver_calendar_show_via', 'driver_calendar_show_via'],
            ['site.driver_calendar_show_vehicle_type', 'driver_calendar_show_vehicle_type'],
            ['site.driver_calendar_show_estimated_time', 'driver_calendar_show_estimated_time'],
            ['site.driver_calendar_show_actual_time_slot', 'driver_calendar_show_actual_time_slot'],
            ['site.driver_calendar_show_passengers', 'driver_calendar_show_passengers'],
            ['site.driver_calendar_show_custom', 'driver_calendar_show_custom'],
            ['site.customer_attach_booking_details_to_sms', 'customer_attach_booking_details_to_sms'],
            ['site.customer_attach_booking_details_access_link', 'customer_attach_booking_details_access_link'],
            ['site.customer_show_only_lead_passenger', 'customer_show_only_lead_passenger'],
            ['site.admin_booking_listing_highlight', 'admin_booking_listing_highlight'],
            ['site.admin_calendar_show_ref_number', 'admin_calendar_show_ref_number'],
            ['site.admin_calendar_show_name_passenger', 'admin_calendar_show_name_passenger'],
            ['site.admin_calendar_show_name_customer', 'admin_calendar_show_name_customer'],
            ['site.admin_calendar_show_service_type', 'admin_calendar_show_service_type'],
            ['site.admin_calendar_show_from', 'admin_calendar_show_from'],
            ['site.admin_calendar_show_to', 'admin_calendar_show_to'],
            ['site.admin_calendar_show_via', 'admin_calendar_show_via'],
            ['site.admin_calendar_show_vehicle_type', 'admin_calendar_show_vehicle_type'],
            ['site.admin_calendar_show_estimated_time', 'admin_calendar_show_estimated_time'],
            ['site.admin_calendar_show_actual_time_slot', 'admin_calendar_show_actual_time_slot'],
            ['site.admin_default_page', 'admin_default_page'],
            ['site.invoice_enabled', 'invoice_enabled'],
            ['site.invoice_display_details', 'invoice_display_details'],
            ['site.invoice_display_logo', 'invoice_display_logo'],
            ['site.invoice_display_payments', 'invoice_display_payments'],
            ['site.invoice_display_custom_field', 'invoice_display_custom_field'],
            ['site.invoice_display_company_number', 'invoice_display_company_number'],
            ['site.invoice_display_company_tax_number', 'invoice_display_company_tax_number'],
            ['site.invoice_info', 'invoice_info'],
            ['site.invoice_bill_from', 'invoice_bill_from'],
            ['site.invoice_styles_default_bg_color', 'invoice_styles_default_bg_color'],
            ['site.invoice_styles_default_text_color', 'invoice_styles_default_text_color'],
            ['site.invoice_styles_active_bg_color', 'invoice_styles_active_bg_color'],
            ['site.invoice_styles_active_text_color', 'invoice_styles_active_text_color'],
            ['site.tax_name', 'tax_name'],
            ['site.tax_percent', 'tax_percent'],
            ['site.styles_border_radius', 'styles_border_radius'],
            ['site.styles_default_bg_color', 'styles_default_bg_color'],
            ['site.styles_default_border_color', 'styles_default_border_color'],
            ['site.styles_default_text_color', 'styles_default_text_color'],
            ['site.styles_active_bg_color', 'styles_active_bg_color'],
            ['site.styles_active_border_color', 'styles_active_border_color'],
            ['site.styles_active_text_color', 'styles_active_text_color'],
            ['site.custom_css', 'custom_css'],
            ['site.mobile_app_styles_border_radius', 'mobile_app_styles_border_radius'],
            ['site.mobile_app_styles_default_bg_color', 'mobile_app_styles_default_bg_color'],
            ['site.mobile_app_styles_default_border_color', 'mobile_app_styles_default_border_color'],
            ['site.mobile_app_styles_default_text_color', 'mobile_app_styles_default_text_color'],
            ['site.mobile_app_styles_active_bg_color', 'mobile_app_styles_active_bg_color'],
            ['site.mobile_app_styles_active_border_color', 'mobile_app_styles_active_border_color'],
            ['site.mobile_app_styles_active_text_color', 'mobile_app_styles_active_text_color'],
            ['site.mobile_app_custom_css', 'mobile_app_custom_css'],
            ['site.code_head', 'code_head'],
            ['site.code_body', 'code_body'],
            ['site.google_maps_javascript_api_key', 'google_maps_javascript_api_key'],
            ['site.google_maps_embed_api_key', 'google_maps_embed_api_key'],
            ['site.google_maps_directions_api_key', 'google_maps_directions_api_key'],
            ['site.google_maps_geocoding_api_key', 'google_maps_geocoding_api_key'],
            ['site.google_places_api_key', 'google_places_api_key'],
            ['site.google_cache_expiry_time', 'google_cache_expiry_time'],
            ['site.google_analytics_tracking_id', 'google_analytics_tracking_id'],
            ['site.google_adwords_conversion_id', 'google_adwords_conversion_id'],
            ['site.google_adwords_conversions', 'google_adwords_conversions'],
            ['site.notifications', 'notifications'],
            ['site.notification_booking_pending_info', 'notification_booking_pending_info'],
            ['site.notification_test_email', 'notification_test_email'],
            ['site.notification_test_phone', 'notification_test_phone'],
        ];

        $config = $this->configData;
        $list = [];

        foreach( $map as $value ) {
            $new = $value[0];
            $old = $value[1];

            if ( isset($config->{$old}) ) {
                switch( $old ) {
                    case 'google_maps_javascript_api_key':
                    case 'google_maps_embed_api_key':
                    case 'google_maps_directions_api_key':
                    case 'google_maps_geocoding_api_key':
                    case 'google_places_api_key':
                    case 'google_analytics_tracking_id':
                    case 'google_adwords_conversion_id':
                    case 'smsgateway_api_key':
                    case 'textlocal_api_key':
                    case 'pcapredict_api_key':
                    case 'ringcentral_app_key':
                    case 'ringcentral_app_secret':
                    case 'flightstats_app_id':
                    case 'flightstats_app_key':
                    case 'language':
                    case 'timezone':
                    case 'date_format':
                    case 'time_format':
                    case 'mail_driver';
                    case 'mail_host';
                    case 'mail_port';
                    case 'mail_username';
                    case 'mail_password';
                    case 'mail_encryption';
                    case 'mail_sendmail';
                    case 'styles_default_bg_color';
                    case 'styles_default_border_color';
                    case 'styles_default_text_color';
                    case 'styles_active_bg_color';
                    case 'styles_active_border_color';
                    case 'styles_active_text_color';
                    case 'invoice_styles_default_bg_color';
                    case 'invoice_styles_default_text_color';
                    case 'invoice_styles_active_bg_color';
                    case 'invoice_styles_active_text_color';
                        if ( !empty($config->{$old}) ) {
                            $list[$new] = $config->{$old};
                        }
                    break;
                    default:
                        if ( isset($config->{$old}) ) {
                            $list[$new] = $config->{$old};
                        }
                    break;
                }
            }
        }

        // $list['app.debug'] = 1;
        if (!empty(eto_config('FORCE_GOOGLE_API_KEY'))) {
            if (eto_config('SITE_GOOGLE_MAPS_JAVASCRIPT_API_KEY')) { $list['site.google_maps_javascript_api_key'] = eto_config('SITE_GOOGLE_MAPS_JAVASCRIPT_API_KEY'); }
            if (eto_config('SITE_GOOGLE_MAPS_EMBED_API_KEY')) { $list['site.google_maps_embed_api_key'] = eto_config('SITE_GOOGLE_MAPS_EMBED_API_KEY'); }
            if (eto_config('SITE_GOOGLE_MAPS_DIRECTIONS_API_KEY')) { $list['site.google_maps_directions_api_key'] = eto_config('SITE_GOOGLE_MAPS_DIRECTIONS_API_KEY'); }
            if (eto_config('SITE_GOOGLE_MAPS_GEOCODING_API_KEY')) { $list['site.google_maps_geocoding_api_key'] = eto_config('SITE_GOOGLE_MAPS_GEOCODING_API_KEY'); }
            if (eto_config('SITE_GOOGLE_PLACES_API_KEY')) { $list['site.google_places_api_key'] = eto_config('SITE_GOOGLE_PLACES_API_KEY'); }
        }

        // Force english in admin panel
        if ( \Route::is('admin.*') ) {
            session(['locale' => 'en-GB']);
            $list['app.locale'] = 'en-GB';
        }

        if ( !empty($list) ) {
            config($list);
        }

        // Set timezone
        // date_default_timezone_set(config('app.timezone'));

        return $this;
    }

    public function getData()
    {
        return $this->configData;
    }
}
