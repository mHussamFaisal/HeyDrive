
<input type="hidden" name="settings_group" id="settings_group" value="booking">

<div class="form-group field-ref_format">
    <label for="ref_format">Booking reference number</label>
    <div class="input-group">
        <input type="text" name="ref_format" id="ref_format" placeholder="Booking reference number" required class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
        @php
            $info = "<div style='font-size:12px;'>You can construct your own reference number by mixing these tags below. At least one tag is required in order to make booking reference number unique.<br>
              <b>{pickupDateTime}</b> - the pickup date and time in format 'YmdHi',<br>
              <b>{pickupDate}</b> - the pickup date in format 'Ymd',<br>
              <b>{pickupTime}</b> - the pickup time in format 'Hi',<br>
              <b>{pickupDateTimeFormatted}</b> - the pickup date and time in format 'Y-m-d_H-i',<br>
              <b>{pickupDateFormatted}</b> - the pickup date in format 'Y-m-d',<br>
              <b>{pickupTimeFormatted}</b> - the pickup time in format 'H-i',<br>
              <b>{createDateTime}</b> - the create date and time in format 'YmdHi',<br>
              <b>{createDate}</b> - the create date in format 'Ymd',<br>
              <b>{createTime}</b> - the create time in format 'Hi',<br>
              <b>{createDateTimeFormatted}</b> - the create date and time in format 'Y-m-d_H-i',<br>
              <b>{createDateFormatted}</b> - the create date in format 'Y-m-d',<br>
              <b>{createTimeFormatted}</b> - the create time in format 'H-i',<br>
              <b>{year}</b> - the year of booking creation,<br>
              <b>{month}</b> - the month of booking creation,<br>
              <b>{day}</b> - the day of booking creation,<br>
              <b>{hour}</b> - the hour of booking creation,<br>
              <b>{minute}</b> - the minute of booking creation,<br>
              <b>{second}</b> - the second of booking creation,<br>
              <b>{rand}</b> - random number between 1-1000,<br>
              <b>{rand2}</b> - 2 random letters or digits,<br>
              <b>{rand3}</b> - 3 random letters or digits,<br>
              <b>{rand4}</b> - 4 random letters or digits,<br>
              <b>{rand5}</b> - 5 random letters or digits,<br>
              <b>{rand6}</b> - 6 random letters or digits,<br>
              <b>{rand7}</b> - 7 random letters or digits,<br>
              <b>{rand8}</b> - 8 random letters or digits,<br>
              <b>{rand9}</b> - 9 random letters or digits,<br>
              <b>{rand10}</b> - 10 random letters or digits,<br>
              <b>{id}</b> - id of the booking entry in the database</div>";
        @endphp
        <div class="input-group-addon" data-toggle="popover" data-title="Booking reference number" data-content="{!! $info !!}">
            <i class="ion-ios-information-outline" style="font-size:18px;"></i>
        </div>
    </div>
</div>

<div class="form-group field-currency_symbol">
    <label for="currency_symbol">Currency symbol</label>
    <input type="text" name="currency_symbol" id="currency_symbol" placeholder="Currency symbol" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
</div>

<div class="form-group field-currency_code">
    <label for="currency_code">Currency code</label>
    <input type="text" name="currency_code" id="currency_code" placeholder="Currency code" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
</div>

<div class="form-group placeholder-desabled1 field-booking_min_price_type field-size-fw" style="margin:20px 0px 0px 0px;">
    <label for="booking_min_price_type" style="font-weight: normal;">Min price per journey</label>
    <span style="display:inline-block;max-width:330px; margin-right:5px;">
        <select name="booking_min_price_type" id="booking_min_price_type" required class="form-control select2" data-minimum-results-for-search="Infinity" @permission('admin.settings.booking.edit')@else readonly @endpermission>
          <option value="0">Auto calculated price cannot be lower than</option>
          <option value="1">Set minimum price and then add auto calculated price to it</option>
        </select>
    </span>
    <span>
        {{--<span>£</span> --}}
        <input type="number" name="distance_min" id="distance_min" placeholder="0" value="0" required class="form-control" step="0.01" min="0" style="display:inline-block; width:auto; padding:5px; height:30px;" @permission('admin.settings.booking.edit')@else readonly @endpermission>
        <i class="ion-ios-information-outline" style="margin-left:5px; font-size:18px;" data-toggle="popover" data-title="Min price per journey" data-content='"Auto calculated price cannot be lower than" - this option will override quoted price if it is lower then the value set in the box next to.<br><br>"Set minimum price and then add auto calculated price to it" - this option will set base price as the value set in the box next to it and then it will add quoted price per km/mi'></i>
    </span>
</div>

<div style="margin:15px 0px 5px 0px;">Vehicle min price</div>
<div id="vehicleMinPriceList" style="display:inline-block; margin-bottom:10px;"></div>
<div class="form-group field-fixed_prices_priority clearfix">
    <div style="float:left; min-width:160px;">
        <label for="fixed_prices_priority">Fixed prices priority</label>
        <select name="fixed_prices_priority" id="fixed_prices_priority" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
            <option value="0">Postcodes</option>
            <option value="1">Zones</option>
        </select>
    </div>
    <i class="ion-ios-information-outline" style="display:inline-block; margin-top:5px; margin-left:8px; font-size:18px; line-height:22px;" data-toggle="popover" data-title="Fixed prices priority" data-content='Choose which type of fixed prices the system should use first Postcodes or Zones'></i>
</div>

<div class="form-group field-booking_round_total_price">
    <label for="booking_round_total_price">Round total price</label>
    <select name="booking_round_total_price" id="booking_round_total_price" data-placeholder="Round total price" required class="form-control select2" @permission('admin.settings.booking.edit')@else readonly @endpermission>
        <option value="0">No round up</option>
        <option value="1">Up or Down to nearest integer</option>
        <option value="2">Up to the nearest integer</option>
        <option value="3">Down to the nearest integer</option>
        <option value="4">Up to nearest integer or half (.50)</option>
    </select>
</div>

<div class="form-group field-booking_include_aiport_charges eto-config-form-group field-size-fw">
    <label for="booking_include_aiport_charges" class="checkbox-inline">
        <input type="checkbox" name="booking_include_aiport_charges" id="booking_include_aiport_charges" value="1"  @permission('admin.settings.booking.edit')@else readonly @endpermission>Include aiport charges in quote
    </label>
</div>

<div class="form-group field-booking_summary_enable eto-config-form-group field-size-fw">
    <label for="booking_summary_enable" class="checkbox-inline">
        <input type="checkbox" name="booking_summary_enable" id="booking_summary_enable" value="1" @permission('admin.settings.booking.edit')@else readonly @endpermission>Enable booking price breakdown
    </label>
</div>

<div class="form-group field-incomplete_bookings_display field-size-fw">
    <label for="incomplete_bookings_display" class="checkbox-inline">
        <input type="checkbox" name="incomplete_bookings_display" id="incomplete_bookings_display" value="1" @permission('admin.settings.booking.edit')@else readonly @endpermission> <span style="float:left;">Display incomplete bookings</span> <i class="ion-ios-information-outline" style="margin-left:5px; font-size:18px; line-height:22px; float:left;" data-toggle="popover" data-title="Display incomplete booking" data-content='When enabled, it will display bookings which were made by customer but payment have not been completed.'></i>
        <div style="clear:both;"></div>
    </label>
</div>

<div class="form-group field-incomplete_bookings_delete_enable field-size-fw">
    <label for="incomplete_bookings_delete_enable" class="checkbox-inline">
        <input type="checkbox" name="incomplete_bookings_delete_enable" id="incomplete_bookings_delete_enable" value="1" @permission('admin.settings.booking.edit')@else readonly @endpermission> <span>Auto delete incomplete bookings after</span>
    </label>
    <span>
        <input type="number" name="incomplete_bookings_delete_after" id="incomplete_bookings_delete_after" placeholder="0" value="0" required class="form-control" step="1" min="0" style="display:inline-block; width:auto; padding:5px; height:30px;" @permission('admin.settings.booking.edit')@else readonly @endpermission>
        <span>h</span> <i class="ion-ios-information-outline" style="margin-left:5px; font-size:18px;" data-toggle="popover" data-title="Auto delete incomplete bookings" data-content='When enabled, it will delete all incomplete bookings after X amount of hours.'></i>
    </span>
</div>

<div class="form-group field-min_booking_time_limit">
    <label for="min_booking_time_limit">Min booking time (h)</label>
    <input type="text" name="min_booking_time_limit" id="min_booking_time_limit" placeholder="Min booking time (h)" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" @permission('admin.settings.booking.edit')@else readonly @endpermission>
</div>

<div class="form-group field-booking_cancel_enable field-size-fw">
    <label for="booking_cancel_enable" class="checkbox-inline">
        <input type="checkbox" name="booking_cancel_enable" id="booking_cancel_enable" value="1" @permission('admin.settings.booking.edit')@else readonly @endpermission>Enable booking cancellation
    </label>
</div>

<div class="form-group field-booking_cancel_time">
    <label for="booking_cancel_time">Min booking cancellation time (h)</label>
    <input type="text" name="booking_cancel_time" id="booking_cancel_time" placeholder="Min booking cancellation time (h)" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" @permission('admin.settings.booking.edit')@else readonly @endpermission>
</div>

<div class="eto-config-section-header" style="margin-top:20px;margin-bottom:15px;">Admin booking listing</div>
<div class="form-group field-booking_listing_refresh_type">
    <label for="booking_listing_refresh_type">Auto refresh</label>
    <select name="booking_listing_refresh_type" id="booking_listing_refresh_type" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
    <option value="0">Inactive</option>
    <option value="1">Active</option>
    </select>
</div>

<div class="form-group field-booking_listing_refresh_interval">
    <label for="booking_listing_refresh_interval">Refresh time (in seconds)</label>
    <input type="number" name="booking_listing_refresh_interval" id="booking_listing_refresh_interval" placeholder="Refresh time (in seconds)" value="0" required class="form-control" min="30" step="1" style="padding-right:10px;" @permission('admin.settings.booking.edit')@else readonly @endpermission>
</div>

<div class="form-group field-booking_listing_refresh_counter">
    <label for="booking_listing_refresh_counter">Time counter</label>
    <select name="booking_listing_refresh_counter" id="booking_listing_refresh_counter" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
    <option value="0">Hide</option>
    <option value="1">Show</option>
    </select>
</div>

<div class="eto-config-section-header" style="margin-top:20px;">Meeting board</div>
<div class="form-group field-booking_meeting_board_enabled eto-config-form-group field-size-fw">
    <label for="booking_meeting_board_enabled" class="checkbox-inline">
        <input type="checkbox" name="booking_meeting_board_enabled" id="booking_meeting_board_enabled" value="1" @permission('admin.settings.booking.edit')@else readonly @endpermission>Enable meeting board
    </label>
</div>

<div class="form-group field-booking_meeting_board_attach eto-config-form-group field-size-fw">
    <label for="booking_meeting_board_attach" class="checkbox-inline">
        <input type="checkbox" name="booking_meeting_board_attach" id="booking_meeting_board_attach" value="1" @permission('admin.settings.booking.edit')@else readonly @endpermission>Attach meeting board to driver email
    </label>
</div>

<div class="form-group field-booking_meeting_board_font_size" style="margin-top:15px;">
    <label for="booking_meeting_board_font_size">Name font size (pixels)</label>
    <input type="number" name="booking_meeting_board_font_size" id="booking_meeting_board_font_size" placeholder="Name font size (pixels)" value="0" required class="form-control" min="30" max="120" step="1" style="padding-right:10px;" @permission('admin.settings.booking.edit')@else readonly @endpermission>
</div>

<div class="form-group field-booking_meeting_board_header">
    <label for="booking_meeting_board_header">Show in header</label>
    <select name="booking_meeting_board_header" id="booking_meeting_board_header" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
    <option value="1">Company logo</option>
    <option value="2">Customer logo</option>
    <option value="0">Disable</option>
    </select>
</div>

<div class="form-group field-booking_meeting_board_footer">
    <label for="booking_meeting_board_footer">Show in footer</label>
    <select name="booking_meeting_board_footer" id="booking_meeting_board_footer" class="form-control" @permission('admin.settings.booking.edit')@else readonly @endpermission>
    <option value="1">Journey details</option>
    <option value="0">Disable</option>
    </select>
</div>
