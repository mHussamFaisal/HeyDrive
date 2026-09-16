
<input type="hidden" name="settings_group" id="settings_group" value="google">

<div class="form-group field-booking_distance_unit">
    <label for="booking_distance_unit">Measure distance in</label>
    <select name="booking_distance_unit" id="booking_distance_unit" data-placeholder="Measure distance in" required class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
        <option value="0">Miles</option>
        <option value="1">Kilometers</option>
    </select>
</div>

<div class="form-group field-quote_enable_shortest_route">
    <label for="quote_enable_shortest_route">Route type</label>
    <select name="quote_enable_shortest_route" id="quote_enable_shortest_route" data-placeholder="Route type" required class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
        <option value="0">Suggested by Google</option>
        <option value="1">Shortest</option>
        <option value="2">Fastest (with traffic)</option>
        <option value="3">Fastest (without traffic)</option>
    </select>
</div>

<div class="form-group field-quote_avoid_highways eto-config-form-group field-size-fw">
    <label for="quote_avoid_highways" class="checkbox-inline">
        <input type="checkbox" name="quote_avoid_highways" id="quote_avoid_highways" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>Avoid highways
    </label>
</div>

<div class="form-group field-quote_avoid_tolls eto-config-form-group field-size-fw">
    <label for="quote_avoid_tolls" class="checkbox-inline">
        <input type="checkbox" name="quote_avoid_tolls" id="quote_avoid_tolls" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>Avoid tolls
    </label>
</div>

<div class="form-group field-quote_avoid_ferries eto-config-form-group field-size-fw">
    <label for="quote_avoid_ferries" class="checkbox-inline">
        <input type="checkbox" name="quote_avoid_ferries" id="quote_avoid_ferries" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>Avoid ferries
    </label>
</div>

<div class="form-group field-quote_duration_in_traffic eto-config-form-group field-size-fw hide_advanced">
    <label for="quote_duration_in_traffic" class="checkbox-inline">
        <input type="checkbox" name="quote_duration_in_traffic" id="quote_duration_in_traffic" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission> Use traffic time
    </label>
</div>

<div class="form-group field-quote_traffic_model" style="display:none;">
    <label for="quote_traffic_model">Traffic time type</label>
    <select name="quote_traffic_model" id="quote_traffic_model" data-placeholder="Traffic time type" required class="form-control select2" @permission('admin.settings.google.edit')@else readonly @endpermission>
        <option value="pessimistic">Pessimistic</option>
        <option value="optimistic">Optimistic</option>
        <option value="best_guess">Best guess</option>
    </select>
</div>

<div class="form-group field-quote_enable_straight_line eto-config-form-group field-size-fw">
    <label for="quote_enable_straight_line" class="checkbox-inline">
        <input type="checkbox" name="quote_enable_straight_line" id="quote_enable_straight_line" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>Straight line distance
    </label>
</div>

<div class="form-group field-booking_return_as_oneway eto-config-form-group field-size-fw">
    <label for="booking_return_as_oneway" class="checkbox-inline">
        <input type="checkbox" name="booking_return_as_oneway" id="booking_return_as_oneway" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission> Calculate return journey price the same way as outbound
    </label>
</div>

<div class="form-group field-booking_postcode_match eto-config-form-group field-size-fw">
    <label for="booking_postcode_match" class="checkbox-inline" style="float:left;">
        <input type="checkbox" name="booking_postcode_match" id="booking_postcode_match" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>  Better use of postcode based Fixed Price system
    </label>
    <span style="margin-left:5px; font-size:18px; line-height:22px; float:left;" data-toggle="popover" data-title="Better use of postcode based Fixed Price system" data-content="<div style='text-align:left;'>If enabled, we recommend only using the Zones system for Fixed Prices.This setting ensures the system makes a better use of postcode based Fixed Price system.<br><br>When enabled, the system will not allow making a booking if Google is not able to match a location with a postcode in its database. This setting applies to both admin and customer Web Booking forms.<br><br><span style='font-style: italic;'>Tip.<br>If using only Zones in Fixed Pricing, this setting should be disabled as it will not bring any benefits but rather create unnecessary limitation.<br><br>If you are outside of the UK, we strongly recommend to use Zones (Settings -> Zones) in Fixed Prices instead of postcodes. The Zones system is simply more accurate outside of the UK.</span></div>">
        <i class="ion-ios-information-outline" style="font-size:18px;"></i>
    </span>
    <div style="clear:both;"></div>
</div>

<div class="form-group field-autocomplete_google_places eto-config-form-group field-size-fw">
    <label for="autocomplete_google_places" class="checkbox-inline">
        <input type="checkbox" name="autocomplete_google_places" id="autocomplete_google_places" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>Autocomplete - Google places
    </label>
</div>

<div class="form-group field-autocomplete_force_selection eto-config-form-group field-size-fw">
    <label for="autocomplete_force_selection" class="checkbox-inline" style="float:left;">
        <input type="checkbox" name="autocomplete_force_selection" id="autocomplete_force_selection" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission>Autocomplete - Force selection
    </label>
    <span style="margin-left:5px; font-size:18px; line-height:22px; float:left;" data-toggle="popover" data-title="Autocomplete - Force selection" data-content="<div style='text-align:left;'>This option forces user to select the address from the Google suggestion list displayed in a dropdown menu. In case searched address is not in suggestion list, the address filed will be automatically reseted itself and user won't be able to use that addres and go to the next step of the booking.</div>">
        <i class="ion-ios-information-outline" style="font-size:18px;"></i>
    </span>
    <div style="clear:both;"></div>
</div>

<div class="form-group field-booking_advanced_geocoding eto-config-form-group field-size-fw hide_advanced">
    <label for="booking_advanced_geocoding" class="checkbox-inline">
        <input type="checkbox" name="booking_advanced_geocoding" id="booking_advanced_geocoding" value="1" @permission('admin.settings.google.edit')@else readonly @endpermission> Enable advanced geocoding
    </label>
</div>

<div style="margin-top:20px;">Min number of characters for location suggestions search</div>
<div class="form-group field-booking_location_search_min field-size-xs">
    {{-- <label for="booking_location_search_min">Min number of characters for location suggestions search</label> --}}
    <input type="number" name="booking_location_search_min" id="booking_location_search_min" placeholder="0" value="0" required class="form-control" min="1" step="1" style="padding: 6px 12px;" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div>Google restriction region code:</div>
<div class="form-group field-google_region_code field-size-sm">
  <div class="input-group">
      <input type="text" name="google_region_code" id="google_region_code" placeholder="eg. GB" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
      <div class="input-group-addon" data-toggle="popover" data-title="Google restriction region code" data-content="<div style='text-align:left;'>- This option will only influence, not fully restrict, results from Google Directions and Geocode services.<br>- You can also set spcific region code (max 1) eg. <b>'GB'</b> for United Kingdom, <b>'ES'</b> for Spain, <b>'DE'</b> for Germany etc.<br>- Here is a list of all supported region codes <br /><a href='https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2' target='_blank'>https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2</a><br>- If this option is left empty then no region restriction will be applied.</div>">
          <i class="ion-ios-information-outline" style="font-size:18px;"></i>
      </div>
  </div>
</div>

<div>Google restriction country code(s):</div>
<div class="form-group field-google_country_code field-size-sm">
    <div class="input-group">
        <input type="text" name="google_country_code" id="google_country_code" placeholder="eg. GB, ES" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
        <div class="input-group-addon" data-toggle="popover" data-title="Google restriction country code(s)" data-content="<div style='text-align:left;'>- This option will limit location suggestions to the countries listed in the field.<br>- You can set few country codes at once (max 5), separated with a comma eg. <b>'GB'</b>, <b>'ES'</b>, <b>'DE'</b> etc.<br>- Here is a list of all supported country codes <a href='https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2' target='_blank'>https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2</a><br>- If this field is empty then no country restriction will be applied.</div>">
            <i class="ion-ios-information-outline" style="font-size:18px;"></i>
        </div>
    </div>
</div>

<div>Google prefered language code:</div>
<div class="form-group field-google_language field-size-sm">
    <div class="input-group">
        <input type="text" name="google_language" id="google_language" placeholder="eg. EN" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
        <div class="input-group-addon" data-toggle="popover" data-title="Google prefered language code" data-content="<div style='text-align:left;'>- If this option is set then location suggestions will be displayed in desired language.<br />- You can set spcific language code (max 1) eg. <b>'EN'</b> for English, <b>'ES'</b> for Spanish, <b>'DE'</b> for German etc.<br>- Here is a list of all supported language codes <br /><a href='https://developers.google.com/maps/faq#languagesupport' target='_blank'>https://developers.google.com/maps/faq#languagesupport</a><br>- If this option is left empty then the system will auto detect code based on currently used language.</div>">
            <i class="ion-ios-information-outline" style="font-size:18px;"></i>
        </div>
    </div>
</div>

<div class="form-group field-google_analytics_tracking_id field-size-md">
    <label for="google_analytics_tracking_id">Google Analytics Tracking ID</label>
    <input type="text" name="google_analytics_tracking_id" id="google_analytics_tracking_id" placeholder="Google Analytics Tracking ID" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div class="form-group field-google_adwords_conversion_id field-size-md hide_advanced">
    <label for="google_adwords_conversion_id">Google Adwords Conversion ID</label>
    <input type="text" name="google_adwords_conversion_id" id="google_adwords_conversion_id" placeholder="Google Adwords Conversion ID" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div class="form-group field-google_adwords_conversions field-size-md hide_advanced">
    <label for="google_adwords_conversions">Google AdWords conversions (JSON format)</label>
    <textarea name="google_adwords_conversions" id="google_adwords_conversions" placeholder="Google AdWords conversions (JSON format)" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission></textarea>
</div>

<div class="form-group field-quote_address_suffix field-size-lg">
    <label for="quote_address_suffix">Quote address suffix</label>
    <input type="text" name="quote_address_suffix" id="quote_address_suffix" placeholder="Quote address suffix" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div class="form-group field-locations_skip_place_id field-size-lg">
    <label for="locations_skip_place_id">Locations skip place ids</label>
    <textarea name="locations_skip_place_id" id="locations_skip_place_id" placeholder="Locations skip place ids" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission></textarea>
</div>

<div style="margin-top:30px; font-weight:bold;">Google API keys</div>
<div style="margin-bottom:20px;">To make booking software fully operational you must set Google API keys in the fields below. You you can learn how to do it <a href="{{ config('app.docs_url') }}/getting-started/google-services-integration/" target="_blank" style="text-decoration: underline; color:#5b4eff;">here</a>.</div>

<div style="margin-bottom:10px;">Browser key:</div>
<div class="form-group field-google_maps_javascript_api_key field-size-lg">
  <label for="google_maps_javascript_api_key">Maps JavaScript API key</label>
  <input type="text" name="google_maps_javascript_api_key" id="google_maps_javascript_api_key" placeholder="Maps JavaScript API key" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div class="form-group field-google_maps_embed_api_key field-size-lg">
  <label for="google_maps_embed_api_key">Maps Embed API key</label>
  <input type="text" name="google_maps_embed_api_key" id="google_maps_embed_api_key" placeholder="Maps Embed API key" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>
<div style="margin-bottom:20px; color:#808080;">Below is the application restriction configuration for "HTTP referrers (websites)" option which has to be entered in your Google account.<br> <code>http://{{ str_replace(array('http://','https://'), '', url('/')) }}/*</code> and <code>https://{{ str_replace(array('http://','https://'), '', url('/')) }}/*</code></div>

<div style="margin-bottom:10px;">Server key:</div>
<div class="form-group field-google_maps_directions_api_key field-size-lg">
  <label for="google_maps_directions_api_key">Directions API key</label>
  <input type="text" name="google_maps_directions_api_key" id="google_maps_directions_api_key" placeholder="Directions API key" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div class="form-group field-google_places_api_key field-size-lg">
  <label for="google_places_api_key">Places API key</label>
  <input type="text" name="google_places_api_key" id="google_places_api_key" placeholder="Places API key" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div class="form-group field-google_maps_geocoding_api_key field-size-lg">
  <label for="google_maps_geocoding_api_key">Geocoding API key</label>
  <input type="text" name="google_maps_geocoding_api_key" id="google_maps_geocoding_api_key" placeholder="Geocoding API key" class="form-control" @permission('admin.settings.google.edit')@else readonly @endpermission>
</div>

<div style="color:#808080;">Below is the application restriction configuration for "IP addresses (web servers, cron jobs, etc.)" option which has to be entered in your Google account.<br><code>{{ !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '' }}</code></div>
