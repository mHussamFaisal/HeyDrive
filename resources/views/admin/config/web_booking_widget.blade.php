
<input type="hidden" name="settings_group" id="settings_group" value="web_booking_widget">

<div class="panel-group" id="accordion-web-booking-widget" role="tablist" aria-multiselectable="true" style="margin:0;">
    <div class="panel active">
        <div class="panel-heading" role="tab" id="web-booking-widget-general-link" style="background:#fff8f8;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion-web-booking-widget" href="#web-booking-widget-general" aria-expanded="false" aria-controls="web-booking-widget-general" style="color:#c19794;">
                    General
                </a>
            </h4>
        </div>
        <div id="web-booking-widget-general" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="web-booking-widget-general-link">
            <div class="panel-body">
                <div class="form-group field-booking_display_book_by_phone eto-config-form-group field-size-fw">
                    <label for="booking_display_book_by_phone" class="checkbox-inline">
                        <input type="checkbox" name="booking_display_book_by_phone" id="booking_display_book_by_phone" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Display book by phone option in 2nd and 3rd step just below the booking steps</span>
                    </label>
                </div>

                <div class="form-group field-booking_attach_ical eto-config-form-group field-size-fw clearfix">
                    <label for="booking_attach_ical" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_attach_ical" id="booking_attach_ical" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Attach journey details in iCal format to booking confirmation email</span>
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px;" data-toggle="popover" data-title='' data-content="After enabling this option, the client will receive a confirmation email with an additional file that will be used by the mailbox application to save the journey date on the client's calendar so that he can receive a reminder when this date is due."></i>
                </div>

                <div class="form-group field-booking_scroll_to_top_enable field-size-fw hide_advanced">
                    <label for="booking_scroll_to_top_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_scroll_to_top_enable" id="booking_scroll_to_top_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Enable scroll to top
                    </label>
                </div>

                <div class="form-group field-booking_scroll_to_top_offset hide_advanced">
                    <label for="booking_scroll_to_top_offset">Scroll to top offset (px)</label>
                    <input type="text" name="booking_scroll_to_top_offset" id="booking_scroll_to_top_offset" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                </div>


                <div class="form-group field-booking_pricing_mode field-size-fw" style="margin-top:30px; padding:20px 10px 20px 10px; border:1px #eaeaea solid;">
                    <div class="eto-config-section-header clearfix" style="margin-bottom:0; padding: 0 4px; display:inline-block; position:absolute; top:-12px; left:6px; background:#fff;">
                        <span style="float: left; padding-top: 2px;">Price settings</span>
                        <i class="ion-ios-information-outline" style="margin-left:5px; font-size:18px;" data-toggle="popover" data-title="" data-content='<b>Price settings</b> - this setting determines if a customer will be able to make a booking via web booking with or without a price.<br><br><b>Web Booking with price</b> - with this setting active, a price in web booking will be calculated and displayed. Additionally a type of pricing can be set which will be calculated in web booking: Fixed and Distance prices, Fixed prices only or Distance prices only. If the payment method(s) is integrated and activated then a customer can also make an online payment.<br><br><b>Web Booking without price</b> - with this setting active, the price in web booking will not be available. Instead a customer can make a booking without a price.<br><br><b>Allow booking without a price when FROM, TO or VIA address could not be found</b> - when active it allows a customer to make a booking without a price. Payment buttons in step3 will be replaced with a "Request a quote" button.<br><br><b>Allow booking without a price for vehicles which price is not available or equal to 0</b> - when active, an Enquiry button is displayed next to each type of vehicle in step2 of  web booking. By clicking the Enquiry button, a customer is redirected to an Url defined in Settings -> General -> URLs section -> Contact URL option.<br><br><b>Allow booking without a price</b> - when active, it enables a customer to make a full booking without price and finalize it by clicking a button "Request a Quote".<br><br><b>Allows a customer to use an outside contact form</b> - when active, an Enquiry button is displayed next to all types of vehicle in step2 of web booking. By clicking the Enquiry button, a customer is redirected to an Url defined in Settings -> General -> URLs section -> Contact URL option.'></i>
                    </div>
                    <div>
                        <label for="booking_price_status_1" class="checkbox-inline" style="display:block; margin:0 10px 5px 0; padding:0;">
                            <input type="radio" name="booking_price_status" id="booking_price_status_1" value="1" checked="checked" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            <span>Web Booking with price</span>
                        </label>
                        <label for="booking_price_status_0" class="checkbox-inline" style="display:block; margin:0 0 5px 0; padding:0;">
                            <input type="radio" name="booking_price_status" id="booking_price_status_0" value="0" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            <span>Web Booking without price</span>
                        </label>
                    </div>

                    <div class="eto-container-booking_price_status_1" style="margin-top:20px; padding-top:20px; border-top:1px #eaeaea solid;">
                        <div class="form-group field-booking_pricing_mode field-size-fw">
                            <label for="booking_pricing_mode_0" class="checkbox-inline" style="display:block; margin:0 0 5px 0; padding:0;">
                                <input type="radio" name="booking_pricing_mode" id="booking_pricing_mode_0" value="0" checked="checked" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                                <span>Fixed + Distance prices</span>
                            </label>
                            <label for="booking_pricing_mode_1" class="checkbox-inline" style="display:block; margin:0 0 5px 0; padding:0;">
                                <input type="radio" name="booking_pricing_mode" id="booking_pricing_mode_1" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                                <span>Fixed prices only</span>
                            </label>
                            <label for="booking_pricing_mode_2" class="checkbox-inline" style="display:block; margin:0; padding:0;">
                                <input type="radio" name="booking_pricing_mode" id="booking_pricing_mode_2" value="2" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                                <span>Distance prices only</span>
                            </label>
                        </div>
                        <div class="form-group field-booking_price_status_on eto-config-form-group field-size-fw" style="margin-top:10px;">
                            <label for="booking_price_status_on" class="checkbox-inline">
                                <input type="checkbox" name="booking_price_status_on" id="booking_price_status_on" value="1" checked="checked" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                                <span>Allow booking without a price when FROM, TO or VIA address could not be found.</span>
                            </label>
                        </div>
                        <div class="form-group field-booking_price_status_on_enquiry eto-config-form-group field-size-fw" style="margin-top:10px;">
                            <label for="booking_price_status_on_enquiry" class="checkbox-inline">
                                <input type="checkbox" name="booking_price_status_on_enquiry" id="booking_price_status_on_enquiry" value="1" checked="checked" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                                <span>Allows a customer to use an outside contact form for the type of vehicle which price is not available or equal to 0.</span>
                            </label>
                        </div>
                    </div>

                    <div class="eto-container-booking_price_status_0" style="dispaly:none; margin-top:20px; padding-top:20px; border-top:1px #eaeaea solid;">
                        <label for="booking_price_status_off_0" class="checkbox-inline" style="display:block; margin:0 0 5px 0; padding:0;">
                            <input type="radio" name="booking_price_status_off" id="booking_price_status_off_0" value="0" checked="checked" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            <span>Allow booking without a price</span>
                        </label>
                        <label for="booking_price_status_off_1" class="checkbox-inline" style="display:block; margin:0 0 5px 0; padding:0;">
                            <input type="radio" name="booking_price_status_off" id="booking_price_status_off_1" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            <span>Allows a customer to use an outside contact form</span>
                        </label>
                    </div>
                </div>

                <div class="form-group field-booking_request_enable field-size-fw" style="margin-top:30px; padding:20px 10px 20px 10px; border:1px #eaeaea solid;">
                    <div class="eto-config-section-header clearfix" style="margin-bottom:0; padding: 0 4px; display:inline-block; position:absolute; top:-12px; left:6px; background:#fff;">
                        <span style="float: left; padding-top: 2px;">Booking status</span>
                        <i class="ion-ios-information-outline" style="margin-left:5px; font-size:18px;" data-toggle="popover" data-title="" data-content='<b>Booking status</b><br>It allows setting what message customer will see and what emails will receive when booking is made.<br><br><b>Booking status is confirmed</b><br>This is a default setting and all new booking made by customer are set as Confirmed.<br><br><b>Booking status is unconfirmed</b><br>This setting allows company to set booking to Unconfirmed. Customer will be informed that booking awaits confirmation. This is a useful option in case company has no capacity to confirmed all booking at any given time.'></i>
                    </div>
                    <label for="booking_request_enable_on" class="checkbox-inline" style="display:block; margin:0 0 5px 0; padding:0;">
                        <input type="radio" name="booking_request_enable" id="booking_request_enable_on" value="0" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Booking status is confirmed.</span>
                    </label>
                    <label for="booking_request_enable_off" class="checkbox-inline" style="display:block; margin:0; padding:0;">
                        <input type="radio" name="booking_request_enable" id="booking_request_enable_off" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Booking status is unconfirmed.</span>
                    </label>
                    <div style="margin-left:17px;">
                        Company will response to customer within <select name="booking_request_time" id="booking_request_time" required class="form-control" style="display:inline-block; width:auto; padding:5px; height:30px;" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            @for ($i = 0; $i <= 72; $i++)
                                @for ($j = 0; $j < 60; $j+=15)
                                    <option value="{{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}">
                                        {{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}
                                    </option>
                                    @php
                                        if($i == 72 && $j == 0) {break;}
                                    @endphp
                                @endfor
                            @endfor
                        </select> h.<br>
                        Set time for the booking to be automatically Confirmed after <select name="booking_auto_confirm_time" id="booking_auto_confirm_time" required class="form-control" style="display:inline-block; width:auto; padding:5px; height:30px;" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            @for ($i = 0; $i <= 72; $i++)
                                @for ($j = 0; $j < 60; $j+=15)
                                    <option value="{{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}">
                                        {{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}
                                    </option>
                                    @php
                                        if($i == 72 && $j == 0) {break;}
                                    @endphp
                                @endfor
                            @endfor
                        </select> h ahead of current time.
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading" role="tab" id="web-booking-widget-step1-link" style="background:#fff8f8;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion-web-booking-widget" href="#web-booking-widget-step1" aria-expanded="false" aria-controls="web-booking-widget-step1" style="color:#c19794;">
                    Step 1
                </a>
            </h4>
        </div>
        <div id="web-booking-widget-step1" class="panel-collapse collapse" role="tabpanel" aria-labelledby="web-booking-widget-step1-link">
            <div class="panel-body">
                <div class="form-group field-booking_display_widget_header eto-config-form-group field-size-fw">
                    <label for="booking_display_widget_header" class="checkbox-inline">
                        <input type="checkbox" name="booking_display_widget_header" id="booking_display_widget_header" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Display mini web booking widget header (QUOTE & BOOK)
                    </label>
                </div>

                <div class="form-group field-booking_show_preferred eto-config-form-group field-size-fw clearfix">
                    <label for="booking_show_preferred" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_show_preferred" id="booking_show_preferred" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Vehicle capacity automatic selection</span>
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px; height:18px;" data-toggle="popover" data-title="" data-content='This setting allows to enable/disable Passenger, Luggage and Hand luggage amount options in the first step of the booking. The choice of the amount of passengers and luggage determines availability of the vehicle type in Step 2 of the booking - a vehicle with the minimum requirement capacity will be automatically chosen.'></i>
                </div>

                <div class="form-group field-booking_display_return_journey eto-config-form-group field-size-fw">
                    <label for="booking_display_return_journey" class="checkbox-inline">
                        <input type="checkbox" name="booking_display_return_journey" id="booking_display_return_journey" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Display return journey button
                    </label>
                </div>

                <div class="form-group field-booking_display_via eto-config-form-group field-size-fw">
                    <label for="booking_display_via" class="checkbox-inline">
                        <input type="checkbox" name="booking_display_via" id="booking_display_via" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Display add via button
                    </label>
                </div>

                <div class="form-group field-booking_display_swap eto-config-form-group field-size-fw">
                    <label for="booking_display_swap" class="checkbox-inline">
                        <input type="checkbox" name="booking_display_swap" id="booking_display_swap" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Display swap address button
                    </label>
                </div>

                <div class="form-group field-booking_display_geolocation eto-config-form-group field-size-fw">
                    <label for="booking_display_geolocation" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_display_geolocation" id="booking_display_geolocation" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Display get current location button
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px;" data-toggle="popover" data-title="" data-content='This setting allows to allows to enable/disable Get current location option in Web Booking widget.'></i>
                    <div style="clear:both;"></div>
                </div>

                <div class="form-group field-booking_service_dropdown field-size-fw">
                    <label for="booking_service_dropdown" class="checkbox-inline">
                        <input type="checkbox" name="booking_service_dropdown" id="booking_service_dropdown" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Hide service dropdown menu if there is only one option available
                    </label>
                </div>

                <div class="form-group field-booking_service_display_mode">
                    <label for="booking_service_display_mode">Service display mode</label>
                    <select name="booking_service_display_mode" id="booking_service_display_mode" class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <option value="dropdown">Dropdown</option>
                        <option value="tabs">Tabs</option>
                    </select>
                </div>

                <div class="form-group field-booking_date_picker_style hide_advanced">
                    <label for="booking_date_picker_style">Date picker style</label>
                    <select name="booking_date_picker_style" id="booking_date_picker_style" required class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <option value="0">Calendar</option>
                        <option value="1">Dropdown</option>
                    </select>
                </div>

                <div class="form-group field-booking_time_picker_style hide_advanced">
                    <label for="booking_time_picker_style">Time picker style</label>
                    <select name="booking_time_picker_style" id="booking_time_picker_style" required class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <option value="0">24h</option>
                        <option value="1">24h with AM/PM</option>
                    </select>
                </div>

                <div class="form-group field-booking_time_picker_steps hide_advanced">
                    <label for="booking_time_picker_steps">Time picker steps</label>
                    <select name="booking_time_picker_steps" id="booking_time_picker_steps" required class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <option value="1">Every 1 minute</option>
                        <option value="5">Every 5 minutes</option>
                        <option value="10">Every 10 minutes</option>
                        <option value="15">Every 15 minutes</option>
                        <option value="20">Every 20 minutes</option>
                        <option value="30">Every 30 minutes</option>
                        <option value="60">Every 60 minutes</option>
                    </select>
                </div>

                <div class="form-group field-booking_time_picker_by_minute hide_advanced">
                    <label for="booking_time_picker_by_minute" class="checkbox-inline">
                        <input type="checkbox" name="booking_time_picker_by_minute" id="booking_time_picker_by_minute" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Show minute time picker</span>
                    </label>
                </div>

                <div class="form-group field-booking_force_home_address eto-config-form-group field-size-fw hide_advanced">
                    <label for="booking_force_home_address" class="checkbox-inline">
                        <input type="checkbox" name="booking_force_home_address" id="booking_force_home_address" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Force customer home address if available, when set then no address change will be possible in pickup address field</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading" role="tab" id="web-booking-widget-step2-link" style="background:#fff8f8;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion-web-booking-widget" href="#web-booking-widget-step2" aria-expanded="false" aria-controls="web-booking-widget-step2" style="color:#c19794;">
                    Step 2
                </a>
            </h4>
        </div>
        <div id="web-booking-widget-step2" class="panel-collapse collapse" role="tabpanel" aria-labelledby="web-booking-widget-step2-link">
            <div class="panel-body">
                <div class="form-group field-booking_meet_and_greet_enable eto-config-form-group field-size-fw">
                    <label for="booking_meet_and_greet_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_meet_and_greet_enable" id="booking_meet_and_greet_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Enable meet and greet
                    </label>
                </div>

                <div class="form-group field-booking_meet_and_greet_compulsory eto-config-form-group field-size-fw">
                    <label for="booking_meet_and_greet_compulsory" class="checkbox-inline">
                        <input type="checkbox" name="booking_meet_and_greet_compulsory" id="booking_meet_and_greet_compulsory" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Meet and greet is compulsory
                    </label>
                </div>

                <div class="form-group field-booking_hide_vehicle_not_available_message eto-config-form-group field-size-fw hide_advanced">
                    <label for="booking_hide_vehicle_not_available_message" class="checkbox-inline">
                        <input type="checkbox" name="booking_hide_vehicle_not_available_message" id="booking_hide_vehicle_not_available_message" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> When vehicle is not available show message "Currently Unavailable"
                    </label>
                </div>

                <div class="form-group field-booking_display_book_button eto-config-form-group field-size-fw hide_advanced">
                    <label for="booking_display_book_button" class="checkbox-inline">
                        <input type="checkbox" name="booking_display_book_button" id="booking_display_book_button" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Display book now button below vehicle list
                    </label>
                </div>

                <div class="form-group field-enable_passengers eto-config-form-group field-size-fw">
                    <label for="enable_passengers" class="checkbox-inline">
                        <input type="checkbox" name="enable_passengers" id="enable_passengers" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show passengers icon
                    </label>
                </div>

                <div class="form-group field-enable_luggage eto-config-form-group field-size-fw">
                    <label for="enable_luggage" class="checkbox-inline">
                        <input type="checkbox" name="enable_luggage" id="enable_luggage" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show luggage icon
                    </label>
                </div>

                <div class="form-group field-enable_hand_luggage eto-config-form-group field-size-fw">
                    <label for="enable_hand_luggage" class="checkbox-inline">
                        <input type="checkbox" name="enable_hand_luggage" id="enable_hand_luggage" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show hand luggage icon
                    </label>
                </div>

                <div class="form-group field-enable_child_seats eto-config-form-group field-size-fw">
                    <label for="enable_child_seats" class="checkbox-inline">
                        <input type="checkbox" name="enable_child_seats" id="enable_child_seats" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show child seats icon
                    </label>
                </div>

                <div class="form-group field-enable_baby_seats eto-config-form-group field-size-fw">
                    <label for="enable_baby_seats" class="checkbox-inline">
                        <input type="checkbox" name="enable_baby_seats" id="enable_baby_seats" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show booster seats icon
                    </label>
                </div>

                <div class="form-group field-enable_infant_seats eto-config-form-group field-size-fw">
                    <label for="enable_infant_seats" class="checkbox-inline">
                        <input type="checkbox" name="enable_infant_seats" id="enable_infant_seats" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show infant seats icon
                    </label>
                </div>

                <div class="form-group field-enable_wheelchair field-size-fw">
                    <label for="enable_wheelchair" class="checkbox-inline">
                        <input type="checkbox" name="enable_wheelchair" id="enable_wheelchair" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Show wheelchair icon
                    </label>
                </div>

                <div class="form-group field-booking_vehicle_display_mode">
                    <label for="booking_vehicle_display_mode">Vehicle display mode</label>
                    <select name="booking_vehicle_display_mode" id="booking_vehicle_display_mode" class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <option value="inline">Inline</option>
                        <option value="box">Box</option>
                    </select>
                </div>

                {{-- <div class="eto-config-section-header" style="">Summary</div> --}}
                <div class="form-group field-booking_summary_display_mode">
                    <label for="booking_summary_display_mode">Booking summary display mode</label>
                    <select name="booking_summary_display_mode" id="booking_summary_display_mode" class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <option value="over_map">Over map</option>
                        <option value="separated">Separated</option>
                    </select>
                </div>

                <div class="form-group field-booking_map_zoom" style="margin-top:20px;">
                    <label for="booking_map_zoom">Map zoom level</label>
                    <select name="booking_map_zoom" id="booking_map_zoom" data-placeholder="Map zoom level" class="form-control" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        @for ($i = 0; $i <= 18; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group field-booking_map_enable eto-config-form-group field-size-fw">
                    <label for="booking_map_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_map_enable" id="booking_map_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Enable route map
                    </label>
                </div>

                <div class="form-group field-booking_map_open eto-config-form-group field-size-fw">
                    <label for="booking_map_open" class="checkbox-inline">
                        <input type="checkbox" name="booking_map_open" id="booking_map_open" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Open map by default
                    </label>
                </div>

                <div class="form-group field-booking_map_draggable eto-config-form-group field-size-fw">
                    <label for="booking_map_draggable" class="checkbox-inline">
                        <input type="checkbox" name="booking_map_draggable" id="booking_map_draggable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Enable map draggable
                    </label>
                </div>

                <div class="form-group field-booking_map_zoomcontrol eto-config-form-group field-size-fw">
                    <label for="booking_map_zoomcontrol" class="checkbox-inline">
                        <input type="checkbox" name="booking_map_zoomcontrol" id="booking_map_zoomcontrol" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Enable map zoom control
                    </label>
                </div>

                <div class="form-group field-booking_map_scrollwheel eto-config-form-group field-size-fw">
                    <label for="booking_map_scrollwheel" class="checkbox-inline">
                        <input type="checkbox" name="booking_map_scrollwheel" id="booking_map_scrollwheel" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Enable map scroll wheel
                    </label>
                </div>

                <div class="form-group field-booking_directions_enable eto-config-form-group field-size-fw">
                    <label for="booking_directions_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_directions_enable" id="booking_directions_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Enable route directions
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading" role="tab" id="web-booking-widget-step3-link" style="background:#fff8f8;">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion-web-booking-widget" href="#web-booking-widget-step3" aria-expanded="false" aria-controls="web-booking-widget-step3" style="color:#c19794;">
                    Step 3
                </a>
            </h4>
        </div>
        <div id="web-booking-widget-step3" class="panel-collapse collapse" role="tabpanel" aria-labelledby="web-booking-widget-step3-link">
            <div class="panel-body">
                <div class="form-group field-booking_hide_cash_payment_if_airport eto-config-form-group field-size-fw clearfix">
                    <label for="booking_hide_cash_payment_if_airport" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_hide_cash_payment_if_airport" id="booking_hide_cash_payment_if_airport" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Disable cash payment for airport pickups</span>
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px;" data-toggle="popover" data-title="Disable cash payment for airport pickups" data-content='This setting allows to disable cash payment for airport pickups. This is particularly useful as a security measure for customer not waiting for their driver (No Show).'></i>
                </div>

                <div class="form-group field-booking_allow_account_payment eto-config-form-group field-size-fw clearfix">
                    <label for="booking_allow_account_payment" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_allow_account_payment" id="booking_allow_account_payment" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Enable Account payment method for all new Company Account users</span>
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px;" data-toggle="popover" data-title="Enable Account payment method for all new Company Account users" data-content='This setting allows a newly registered Company Account to use Account payment method. Account payment method allows to Reserved a booking without upfront payment.<br><br>For <b>Enable Account payment method</b> to work, the <b>Account</b> payment method has to be activated in Settings -> Payment Methods -> Account'></i>
                </div>

                <div class="form-group field-booking_show_more_options eto-config-form-group field-size-fw clearfix">
                    <label for="booking_show_more_options" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_show_more_options" id="booking_show_more_options" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Open "More Options" section by default</span>
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px;" data-toggle="popover" data-title='Open "More Options" section by default' data-content='When enabled all advanced options will be displayed by default.'></i>
                </div>

                <div class="form-group field-booking_allow_guest_checkout eto-config-form-group field-size-fw clearfix">
                    <label for="booking_allow_guest_checkout" class="checkbox-inline" style="float:left;">
                        <input type="checkbox" name="booking_allow_guest_checkout" id="booking_allow_guest_checkout" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Allow guest bookings</span>
                    </label>
                    <i class="ion-ios-information-outline" style="float:left; margin-left:5px; margin-top:-2px; font-size:18px;" data-toggle="popover" data-title="Allow guest bookings" data-content='When enabled anyone can make a booking online, even if customer does not have an account.'></i>
                </div>

                <div class="form-group field-booking_member_benefits_enable eto-config-form-group field-size-fw" style="margin-bottom:15px;">
                    <label for="booking_member_benefits_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_member_benefits_enable" id="booking_member_benefits_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Show member benefits list</span>
                    </label>
                </div>

                <div class="form-group field-booking_member_benefits eto-config-form-group field-size-md">
                    <label for="booking_member_benefits">Member benefits (use new line as separator)</label>
                    <textarea name="booking_member_benefits" id="booking_member_benefits" class="form-control" placeholder="Member benefits (use new line as separator). If this option is left empty then default list will be used." @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission></textarea>
                </div>

                <div class="form-group field-booking_show_second_passenger eto-config-form-group field-size-fw">
                    <label for="booking_show_second_passenger" class="checkbox-inline">
                        <input type="checkbox" name="booking_show_second_passenger" id="booking_show_second_passenger" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Allow book for someone else</span>
                    </label>
                </div>

                <div class="form-group field-booking_show_requirements eto-config-form-group field-size-fw">
                    <label for="booking_show_requirements" class="checkbox-inline">
                        <input type="checkbox" name="booking_show_requirements" id="booking_show_requirements" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Allow comments (customer requirements)</span>
                    </label>
                </div>

                <div class="form-group field-booking_required_contact_mobile eto-config-form-group field-size-fw">
                    <label for="booking_required_contact_mobile" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_contact_mobile" id="booking_required_contact_mobile" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require contact mobile</span>
                    </label>
                </div>

                <div class="hide_advanced">
                    <div class="form-group field-booking_account_autocompletion eto-config-form-group field-size-fw">
                        <label for="booking_account_autocompletion" class="checkbox-inline">
                            <input type="checkbox" name="booking_account_autocompletion" id="booking_account_autocompletion" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                            <span>Enable user details autocompletion</span>
                        </label>
                    </div>

                    <div class="form-group field-auto_payment_redirection field-size-fw">
                        <label for="auto_payment_redirection" class="checkbox-inline">
                            <input type="checkbox" name="auto_payment_redirection" id="auto_payment_redirection" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Auto payment redirection
                        </label>
                    </div>

                    <div class="form-group field-booking_terms_disable_button field-size-fw">
                        <label for="booking_terms_disable_button" class="checkbox-inline">
                            <input type="checkbox" name="booking_terms_disable_button" id="booking_terms_disable_button" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>Disable book now button if terms are not checked
                        </label>
                    </div>
                </div>


                <div style="margin-top:20px; margin-bottom:2px; font-weight:bold;">Address</div>

                <div class="form-group field-booking_required_address_complete_from eto-config-form-group field-size-fw">
                    <label for="booking_required_address_complete_from" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_address_complete_from" id="booking_required_address_complete_from" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require full address (pickup)</span>
                    </label>
                </div>
                <div class="form-group field-booking_required_address_complete_to eto-config-form-group field-size-fw">
                    <label for="booking_required_address_complete_to" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_address_complete_to" id="booking_required_address_complete_to" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require full address (dropoff)</span>
                    </label>
                </div>
                <div class="form-group field-booking_required_address_complete_via eto-config-form-group field-size-fw">
                    <label for="booking_required_address_complete_via" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_address_complete_via" id="booking_required_address_complete_via" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require full address (via)</span>
                    </label>
                </div>


                <div style="margin-top:20px; margin-bottom:2px; font-weight:bold;">Vehicle capacity</div>

                <div class="form-group field-booking_required_passengers eto-config-form-group field-size-fw">
                    <label for="booking_required_passengers" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_passengers" id="booking_required_passengers" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require passengers</span>
                    </label>
                </div>

                <div class="form-group field-booking_required_luggage eto-config-form-group field-size-fw">
                    <label for="booking_required_luggage" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_luggage" id="booking_required_luggage" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require luggage</span>
                    </label>
                </div>

                <div class="form-group field-booking_required_hand_luggage eto-config-form-group field-size-fw">
                    <label for="booking_required_hand_luggage" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_hand_luggage" id="booking_required_hand_luggage" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require hand luggage</span>
                    </label>
                </div>

                <div class="form-group field-booking_allow_one_type_of_child_seat eto-config-form-group field-size-fw">
                    <label for="booking_allow_one_type_of_child_seat" class="checkbox-inline">
                        <input type="checkbox" name="booking_allow_one_type_of_child_seat" id="booking_allow_one_type_of_child_seat" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission> Allow only one type of child seat
                    </label>
                </div>

                <div class="form-group field-booking_required_child_seats eto-config-form-group field-size-fw">
                    <label for="booking_required_child_seats" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_child_seats" id="booking_required_child_seats" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require child seats</span>
                    </label>
                </div>

                <div class="form-group field-booking_required_baby_seats eto-config-form-group field-size-fw">
                    <label for="booking_required_baby_seats" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_baby_seats" id="booking_required_baby_seats" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require booster seats</span>
                    </label>
                </div>

                <div class="form-group field-booking_required_infant_seats eto-config-form-group field-size-fw">
                    <label for="booking_required_infant_seats" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_infant_seats" id="booking_required_infant_seats" value="1">
                        <span>Require infant seats</span>
                    </label>
                </div>

                <div class="form-group field-booking_required_wheelchair eto-config-form-group field-size-fw">
                    <label for="booking_required_wheelchair" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_wheelchair" id="booking_required_wheelchair" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require wheelchair</span>
                    </label>
                </div>


                <div style="margin-top:20px; margin-bottom:2px; font-weight:bold;">Flight details - Pickup</div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Flight number</span>
                    <label for="booking_required_flight_number" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_flight_number" id="booking_required_flight_number" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Flight time</span>
                    <label for="booking_flight_landing_time_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_flight_landing_time_enable" id="booking_flight_landing_time_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Enable</span>
                    </label>
                    <label for="booking_required_flight_landing_time" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_flight_landing_time" id="booking_required_flight_landing_time" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Flight from (city)</span>
                    <label for="booking_required_departure_city" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_departure_city" id="booking_required_departure_city" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Waiting time after landing</span>
                    <label for="booking_waiting_time_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_waiting_time_enable" id="booking_waiting_time_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Enable</span>
                    </label>
                    <label for="booking_required_waiting_time" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_waiting_time" id="booking_required_waiting_time" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>


                <div style="margin-top:20px; margin-bottom:2px; font-weight:bold;">Flight details - Dropoff</div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Flight number</span>
                    <label for="booking_required_departure_flight_number" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_departure_flight_number" id="booking_required_departure_flight_number" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Flight time</span>
                    <label for="booking_departure_flight_time_enable" class="checkbox-inline">
                        <input type="checkbox" name="booking_departure_flight_time_enable" id="booking_departure_flight_time_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Enable</span>
                    </label>
                    <label for="booking_required_departure_flight_time" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_departure_flight_time" id="booking_required_departure_flight_time" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>

                <div class="form-group field-size-fw clearfix" style="margin-bottom:5px;">
                    <span style="float:left; margin-right:10px; min-width:160px; display:block;">Flight to (city)</span>
                    <label for="booking_required_departure_flight_city" class="checkbox-inline">
                        <input type="checkbox" name="booking_required_departure_flight_city" id="booking_required_departure_flight_city" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span>Require</span>
                    </label>
                </div>

                <div class="form-group field-booking_departure_flight_time_check_enable eto-config-form-group field-size-fw">
                    <label for="booking_departure_flight_time_check_enable" class="checkbox-inline clearfix">
                        <input type="checkbox" name="booking_departure_flight_time_check_enable" id="booking_departure_flight_time_check_enable" value="1" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission>
                        <span style="float:left; line-height:20px;">
                            Adjust a customer pickup time for airport drop-offs to (x) minutes before plane departure time
                            <input type="number" name="booking_departure_flight_time_check_value" id="booking_departure_flight_time_check_value" placeholder="0" required class="form-control" min="0" step="5" onchange="var currentValue = Math.round(parseInt($(this).val())/5) * 5; $(this).val(currentValue ? currentValue : 0);" @permission('admin.settings.web_booking_widget.edit')@else readonly @endpermission style="display:inline-block; width:80px; margin-left:5px; padding:2px 4px; height:22px;">
                            <i class="ion-ios-information-outline" style="margin-left:5px; font-size:18px;" data-toggle="popover" data-title="" data-content="This setting adjusts customer pickup time for airports drop-offs by the set value.<br><br>For example if a customer sets up pickup time to the airport at 8:00 and plane departure time to 10:00 but admin sets the [ value ] to 3h. The pickup time will be changed to 7:00 thus giving the driver 3h of time before the plane departure.<br><br>This setting should minimise mistakes done by a customer by not choosing realistic time needed for journey to airport, airport procedures and to get before boarding is closed."></i>
                        </span>
                    </label>
                </div>

            </div>
        </div>
    </div>
</div>
