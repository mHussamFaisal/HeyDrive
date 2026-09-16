<?php

$etoAPI->success = true;

// Internal terms
if (config('site.terms_type')) {
  $etoAPI->configBrowser->url_terms = route('booking.terms');
}
if (!isset($etoAPI->configBrowser->terms_enable)) {
  $etoAPI->configBrowser->terms_enable = config('site.terms_enable');
}
if (!isset($etoAPI->configBrowser->terms_type)) {
  $etoAPI->configBrowser->terms_type = config('site.terms_type');
}

$etoAPI->configBrowser->services_flightstats_enabled = config('eto.allow_flightstats') && config('services.flightstats.enabled');
$etoAPI->configBrowser->locales = config('app.locales');
$etoAPI->configBrowser->locale_active = config('app.locale_active');
$etoAPI->configBrowser->locale_current = app()->getLocale();
$etoAPI->configBrowser->locale_switcher_enabled = config('app.locale_switcher_enabled');
$etoAPI->configBrowser->locale_switcher_style = config('app.locale_switcher_style');
$etoAPI->configBrowser->booking_hide_vehicle_not_available_message = config('site.booking_hide_vehicle_not_available_message');
$etoAPI->configBrowser->booking_display_book_by_phone = config('site.booking_display_book_by_phone');
$etoAPI->configBrowser->booking_attach_ical = config('site.booking_attach_ical');
$etoAPI->configBrowser->booking_show_preferred = config('site.booking_show_preferred');
$etoAPI->configBrowser->booking_show_second_passenger = config('site.booking_show_second_passenger');
$etoAPI->configBrowser->booking_time_picker_steps = config('site.booking_time_picker_steps');
$etoAPI->configBrowser->booking_time_picker_by_minute = config('site.booking_time_picker_by_minute');
$etoAPI->configBrowser->booking_required_address_complete_from = config('site.booking_required_address_complete_from');
$etoAPI->configBrowser->booking_required_address_complete_to = config('site.booking_required_address_complete_to');
$etoAPI->configBrowser->booking_required_address_complete_via = config('site.booking_required_address_complete_via');
$etoAPI->configBrowser->booking_force_home_address = config('site.booking_force_home_address');
$etoAPI->configBrowser->booking_show_requirements = config('site.booking_show_requirements');
$etoAPI->configBrowser->booking_service_dropdown = config('site.booking_service_dropdown');
$etoAPI->configBrowser->booking_service_display_mode = config('site.booking_service_display_mode');
$etoAPI->configBrowser->booking_location_search_min = config('site.booking_location_search_min');
$etoAPI->configBrowser->booking_display_widget_header = config('site.booking_display_widget_header');
$etoAPI->configBrowser->booking_display_return_journey = config('site.booking_display_return_journey');
$etoAPI->configBrowser->booking_display_via = config('site.booking_display_via');
$etoAPI->configBrowser->booking_display_swap = config('site.booking_display_swap');
$etoAPI->configBrowser->booking_display_geolocation = config('site.booking_display_geolocation');
$etoAPI->configBrowser->booking_display_book_button = config('site.booking_display_book_button');
$benefits = config('site.booking_member_benefits') ? config('site.booking_member_benefits') : trans('frontend.js.accountBenefits');
$etoAPI->configBrowser->booking_member_benefits = \App\Helpers\SiteHelper::translate($benefits);
$etoAPI->configBrowser->booking_member_benefits_enable = \App\Helpers\SiteHelper::translate(config('site.booking_member_benefits_enable'));
$etoAPI->configBrowser->booking_hide_cash_payment_if_airport = config('site.booking_hide_cash_payment_if_airport');
$etoAPI->configBrowser->booking_show_more_options = config('site.booking_show_more_options');
$etoAPI->configBrowser->booking_allow_guest_checkout = config('site.booking_allow_guest_checkout');
$etoAPI->configBrowser->booking_summary_display_mode = config('site.booking_summary_display_mode');
$etoAPI->configBrowser->booking_vehicle_display_mode = config('site.booking_vehicle_display_mode');
$etoAPI->configBrowser->booking_scroll_to_top_enable = config('site.booking_scroll_to_top_enable');
$etoAPI->configBrowser->booking_scroll_to_top_offset = config('site.booking_scroll_to_top_offset');

$etoAPI->configBrowser->google_analytics_tracking_id = config('site.google_analytics_tracking_id');
$etoAPI->configBrowser->google_adwords_conversion_id = config('site.google_adwords_conversion_id');
$etoAPI->configBrowser->google_adwords_conversions = config('site.google_adwords_conversions');

$etoAPI->configBrowser->customer_allow_company_number = config('site.customer_allow_company_number');
$etoAPI->configBrowser->customer_require_company_number = config('site.customer_require_company_number');
$etoAPI->configBrowser->customer_allow_company_tax_number = config('site.customer_allow_company_tax_number');
$etoAPI->configBrowser->customer_require_company_tax_number = config('site.customer_require_company_tax_number');

$data['config'] = $etoAPI->configBrowser;
$data['userId'] = $etoAPI->userId;
$data['userAvatarPath'] = $etoAPI->userAvatarPath;
$data['userName'] = $etoAPI->userName;
$data['userSince'] = $etoAPI->userSince;
$data['success'] = $etoAPI->success;
$data['message'] = $etoAPI->message;
