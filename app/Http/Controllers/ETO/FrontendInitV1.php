<?php

// Config
$allowedConfig = array(
  'locale_switcher_enabled',
  'locale_switcher_style',
  'booking_hide_vehicle_not_available_message',
  'booking_display_book_by_phone',
  'booking_attach_ical',
  'booking_show_preferred',
  'booking_show_second_passenger',
  'booking_time_picker_steps',
  'booking_time_picker_by_minute',
  'booking_force_home_address',
  'booking_show_requirements',
  'booking_service_dropdown',
  'booking_service_display_mode',
  'booking_location_search_min',
  'booking_display_widget_header',
  'booking_display_return_journey',
  'booking_display_via',
  'booking_display_swap',
  'booking_display_geolocation',
  'booking_display_book_button',
  'booking_member_benefits',
  'booking_member_benefits_enable',
  'booking_hide_cash_payment_if_airport',
  'booking_show_more_options',
  'booking_allow_guest_checkout',
  'booking_summary_display_mode',
  'booking_vehicle_display_mode',
  'booking_scroll_to_top_enable',
  'booking_scroll_to_top_offset',
  'min_booking_time_limit',
  'enable_passengers',
  'enable_luggage',
  'enable_hand_luggage',
  'enable_child_seats',
  'enable_baby_seats',
  'enable_infant_seats',
  'enable_wheelchair',
  'google_region_code',
  'quote_avoid_highways',
  'quote_avoid_tolls',
  'quote_avoid_ferries',
  'quote_enable_shortest_route',
  'company_telephone',
  'company_email',
  'terms_enable',
  'terms_type',
  'url_terms',
  'url_home',
  'url_booking',
  'url_customer',
  'url_feedback',
  'login_enable',
  'password_length_max',
  'password_length_min',
  'booking_required_contact_mobile',
  'booking_required_waiting_time',
  'booking_required_address_complete_from',
  'booking_required_address_complete_to',
  'booking_required_address_complete_via',
  'booking_required_flight_number',
  'booking_required_flight_landing_time',
  'booking_required_departure_city',
  'booking_required_departure_flight_number',
  'booking_required_departure_flight_time',
  'booking_required_departure_flight_city',
  'booking_flight_landing_time_enable',
  'booking_departure_flight_time_enable',
  'booking_departure_flight_time_check_enable',
  'booking_departure_flight_time_check_value',
  'booking_required_passengers',
  'booking_required_child_seats',
  'booking_required_baby_seats',
  'booking_required_infant_seats',
  'booking_required_wheelchair',
  'booking_required_luggage',
  'booking_required_hand_luggage',
  'booking_waiting_time_enable',
  'booking_map_enable',
  'booking_directions_enable',
  'google_analytics_tracking_id',
  'google_adwords_conversion_id',
  'google_adwords_conversions',
);

$config = array();

// Charges - start
$config['charge_meet_and_greet'] = 0;
$config['charge_child_seat'] = 0;
$config['charge_baby_seat'] = 0;
$config['charge_infant_seats'] = 0;
$config['charge_wheelchair'] = 0;
$config['charge_waiting_time'] = 0;

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
      case 'meet_and_greet':
        $config['charge_meet_and_greet'] = (float)$value->value;
      break;
      case 'child_seat':
        $config['charge_child_seat'] = (float)$value->value;
      break;
      case 'baby_seat':
        $config['charge_baby_seat'] = (float)$value->value;
      break;
      case 'infant_seats':
        $config['charge_infant_seats'] = (float)$value->value;
      break;
      case 'wheelchair':
        $config['charge_wheelchair'] = (float)$value->value;
      break;
      case 'waiting_time':
        $config['charge_waiting_time'] = (float)$value->value;
      break;
    }
  }
}
// Charges - end

if ( !empty($gConfig) ) {
  foreach($gConfig as $key => $value) {
    if ( in_array($key, $allowedConfig) ) {
      $config[$key] = $value;
    }
  }

  // From new api
  foreach($etoAPI->configBrowser as $key => $value) {
    $config[$key] = $value;
  }

  // Internal terms
  if (config('site.terms_type')) {
    $config['url_terms'] = route('booking.terms');
  }
  if (!isset($config['terms_enable'])) {
    $config['terms_enable'] = config('site.terms_enable');
  }
  if (!isset($config['terms_type'])) {
    $config['terms_type'] = config('site.terms_type');
  }

  $config['site_key'] = config('site.site_key');
  $config['services_flightstats_enabled'] = config('eto.allow_flightstats') && config('services.flightstats.enabled');
  $config['booking_return_as_oneway'] = config('site.booking_return_as_oneway');
  $config['booking_postcode_match'] = config('site.booking_postcode_match');
  $config['locale_switcher_enabled'] = config('app.locale_switcher_enabled');
  $config['locale_switcher_style'] = config('app.locale_switcher_style');
  $config['booking_hide_vehicle_not_available_message'] = config('site.booking_hide_vehicle_not_available_message');
  $config['booking_display_book_by_phone'] = config('site.booking_display_book_by_phone');
  $config['booking_attach_ical'] = config('site.booking_attach_ical');
  $config['booking_show_preferred'] = config('site.booking_show_preferred');
  $config['booking_show_second_passenger'] = config('site.booking_show_second_passenger');
  $config['booking_time_picker_steps'] = config('site.booking_time_picker_steps');
  $config['booking_time_picker_by_minute'] = config('site.booking_time_picker_by_minute');
  $config['booking_required_address_complete_from'] = config('site.booking_required_address_complete_from');
  $config['booking_required_address_complete_to'] = config('site.booking_required_address_complete_to');
  $config['booking_required_address_complete_via'] = config('site.booking_required_address_complete_via');
  $config['booking_force_home_address'] = config('site.booking_force_home_address');
  $config['booking_show_requirements'] = config('site.booking_show_requirements');
  $config['booking_service_dropdown'] = config('site.booking_service_dropdown');
  $config['booking_service_display_mode'] = config('site.booking_service_display_mode');
  $config['booking_location_search_min'] = config('site.booking_location_search_min');
  $config['booking_display_widget_header'] = config('site.booking_display_widget_header');
  $config['booking_display_return_journey'] = config('site.booking_display_return_journey');
  $config['booking_display_via'] = config('site.booking_display_via');
  $config['booking_display_swap'] = config('site.booking_display_swap');
  $config['booking_display_geolocation'] = config('site.booking_display_geolocation');
  $config['booking_display_book_button'] = config('site.booking_display_book_button');
  $benefits = config('site.booking_member_benefits') ? config('site.booking_member_benefits') : trans('frontend.js.accountBenefits');
  $config['booking_member_benefits'] = \App\Helpers\SiteHelper::translate($benefits);
  $config['booking_member_benefits_enable'] = config('site.booking_member_benefits_enable');
  $config['booking_hide_cash_payment_if_airport'] = config('site.booking_hide_cash_payment_if_airport');
  $config['booking_show_more_options'] = config('site.booking_show_more_options');
  $config['booking_allow_guest_checkout'] = config('site.booking_allow_guest_checkout');
  $config['booking_summary_display_mode'] = config('site.booking_summary_display_mode');
  $config['booking_vehicle_display_mode'] = config('site.booking_vehicle_display_mode');
  $config['booking_scroll_to_top_enable'] = config('site.booking_scroll_to_top_enable');
  $config['booking_scroll_to_top_offset'] = config('site.booking_scroll_to_top_offset');

  $config['google_analytics_tracking_id'] = config('site.google_analytics_tracking_id');
  $config['google_adwords_conversion_id'] = config('site.google_adwords_conversion_id');
  $config['google_adwords_conversions'] = config('site.google_adwords_conversions');

  $data['config'] = $config;
} else {
  $data['message'][] = $gLanguage['API']['ERROR_NO_CONFIG'];
}

/*
// Language
if ( !empty($gLanguage) ) {
  $jsLanguage = $gLanguage;
  unset($jsLanguage['API']);
  $data['language'] = $jsLanguage;
} else {
  $data['message'][] = $gLanguage['API']['ERROR_NO_LANGUAGE'];
}
*/

// Categories
$sql = "SELECT `id`, `name`, `type`
        FROM `{$dbPrefix}category`
        WHERE `published`='1'
        AND `site_id`='".$siteId."'
        ORDER BY `ordering` ASC, `name` ASC";

$resultsCategory = $db->select($sql);

if ( !empty($resultsCategory) ) {
  $data['category'] = $resultsCategory;
} else {
  $data['message'][] = $gLanguage['API']['ERROR_NO_CATEGORY'];
}

/*
// Locations
if ( !empty($resultsCategory) ) {

  $categoriesFiltered = array();
  foreach($resultsCategory as $key => $value){
    $categoriesFiltered[] = $value->id;
  }

  if ( !empty($categoriesFiltered) ) {

    $categoriesListFiltered = implode(",", $categoriesFiltered);

    // Locations
    $sql = "SELECT `id`, `category_id`, `name`, `address`
            FROM `{$dbPrefix}location`
            WHERE `published`='1'
            AND `site_id`='".$siteId."'
            AND `category_id` IN (".$categoriesListFiltered.")
            ORDER BY `category_id` ASC, `ordering` ASC, `name` ASC";

    $resultsLocation = $db->select($sql);

    if ( !empty($resultsLocation) ) {
      $data['location'] = $resultsLocation;
    } else {
      $data['message'][] = $gLanguage['API']['ERROR_NO_LOCATION'];
    }

  } else {
    $data['message'][] = $gLanguage['API']['ERROR_CATEGORY_FILTERED_EMPTY'];
  }

} else {
  // $data['message'][] = $gLanguage['API']['ERROR_NO_CATEGORY'];
}
*/

// Vehicles
$sql = "SELECT *
        FROM `{$dbPrefix}vehicle`
        WHERE `site_id`='".$siteId."'
        AND `published`='1'
        AND `is_backend`='0'
        ORDER BY `ordering` ASC, `name` ASC";
$qVehicle = $db->select($sql);

if ( !empty($qVehicle) ) {
    foreach ($qVehicle as $k => $v) {
        if (!empty($v->image) && \Storage::disk('vehicles-types')->exists($v->image)) {
            $v->image_path = asset_url('uploads','vehicles-types/'. $v->image);
        }
        else {
            $v->image_path = '';
            $v->image = '';
        }
        $qVehicle[$k] = $v;
    }
    $data['vehicle'] = $qVehicle;
}
else {
    $data['message'][] = $gLanguage['API']['ERROR_NO_VEHICLE'];
}


// Payments
$sql = "SELECT *
        FROM `{$dbPrefix}payment`
        WHERE `site_id`='".$siteId."'
        AND `published`='1'
        AND `is_backend`='0'
        ORDER BY `ordering` ASC, `name` ASC";
$qPayment = $db->select($sql);

if ( !empty($qPayment) ) {
    foreach($qPayment as $k => $v) {
        if (!empty($v->image) && \Storage::disk('payments')->exists($v->image)) {
            $v->image_path = asset_url('uploads','payments/'. $v->image);
        }
        else {
            $v->image_path = '';
            $v->image = '';
        }
        if (!empty($v->params)) {
            $v->params = json_decode($v->params);
        }
        unset($v->params);
        $qPayment[$k] = $v;
    }
    $data['payment'] = $qPayment;
}
else {
    $data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENT'];
}


// Services List
$services = \App\Models\Service::where('relation_type', 'site')
  ->where('relation_id', $siteId)
  ->where('status', 'active')
  ->orderBy('order')
  ->orderBy('name')
  ->get();

$servicesList = [];

foreach($services as $k => $v) {
  $params = $v->getParams('raw');

  $servicesList[] = array(
    'id' => (int)$v->id,
    'name' => (string)$v->name,
    'type' => (string)$v->type,
    'availability' => (int)$params->availability,
    'hide_location' => (int)$params->hide_location,
    'duration' => (int)$params->duration,
    'duration_min' => (int)$params->duration_min,
    'duration_max' => (int)$params->duration_max,
    'selected' => $v->is_featured
  );
}

$data['services'] = $servicesList;


// User
$user = $etoAPI->getUser();

// Customer home address
$addressParts = [
    trim($user->address),
    trim($user->city),
    trim($user->postcode),
    trim($user->state),
    trim($user->country),
];
foreach ($addressParts as $k => $v) {
    if (empty($v)) { unset($addressParts[$k]); }
}
$user->homeAddress = trim(implode(', ', $addressParts));

$data['user'] = $user;
//$data['success'] = $etoAPI->success;
