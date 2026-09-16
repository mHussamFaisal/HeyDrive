
<input type="hidden" name="settings_group" id="settings_group" value="users">

<div class="eto-config-section-header" style="">Customers</div>
<div class="form-group field-login_enable eto-config-form-group field-size-fw">
    <label for="login_enable" class="checkbox-inline">
        <input type="checkbox" name="login_enable" id="login_enable" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Enable account login
    </label>
</div>

<div class="form-group field-register_enable eto-config-form-group field-size-fw">
    <label for="register_enable" class="checkbox-inline">
        <input type="checkbox" name="register_enable" id="register_enable" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Enable new account registration
    </label>
</div>

<div class="form-group field-register_activation_enable field-size-fw">
    <label for="register_activation_enable" class="checkbox-inline">
        <input type="checkbox" name="register_activation_enable" id="register_activation_enable" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Enable account activation
    </label>
</div>

<div class="form-group field-password_length_min">
    <label for="password_length_min">Min password length</label>
    <input type="text" name="password_length_min" id="password_length_min" placeholder="Min password length" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" @permission('admin.settings.users.edit')@else readonly @endpermission>
</div>

<div class="form-group field-password_length_max">
    <label for="password_length_max">Max password length</label>
    <input type="text" name="password_length_max" id="password_length_max" placeholder="Max password length" value="0" required class="form-control touchspin" data-bts-step="1" data-bts-min="0" @permission('admin.settings.users.edit')@else readonly @endpermission>
</div>

<div class="form-group field-customer_allow_company_number eto-config-form-group field-size-fw">
    <label for="customer_allow_company_number" class="checkbox-inline">
        <input type="checkbox" name="customer_allow_company_number" id="customer_allow_company_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Enable company registration number
    </label>
</div>

<div class="form-group field-customer_require_company_number eto-config-form-group field-size-fw">
    <label for="customer_require_company_number" class="checkbox-inline">
        <input type="checkbox" name="customer_require_company_number" id="customer_require_company_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Require company registration number
    </label>
</div>

<div class="form-group field-customer_allow_company_tax_number eto-config-form-group field-size-fw">
    <label for="customer_allow_company_tax_number" class="checkbox-inline">
        <input type="checkbox" name="customer_allow_company_tax_number" id="customer_allow_company_tax_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Enable company VAT number
    </label>
</div>

<div class="form-group field-customer_require_company_tax_number eto-config-form-group field-size-fw">
    <label for="customer_require_company_tax_number" class="checkbox-inline">
        <input type="checkbox" name="customer_require_company_tax_number" id="customer_require_company_tax_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Require company VAT number
    </label>
</div>

<div class="form-group field-user_show_company_name eto-config-form-group field-size-fw">
    <label for="user_show_company_name" class="checkbox-inline">
        <input type="checkbox" name="user_show_company_name" id="user_show_company_name" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
        Show "Company Name" next to customer name
    </label>
</div>

<div class="form-group field-customer_attach_booking_details_to_sms eto-config-form-group field-size-fw">
    <label for="customer_attach_booking_details_to_sms" class="checkbox-inline">
        <input type="checkbox" name="customer_attach_booking_details_to_sms" id="customer_attach_booking_details_to_sms" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Attach booking details to customer booking confirmation notification sms
    </label>
</div>

<div class="form-group field-customer_attach_booking_details_access_link eto-config-form-group field-size-fw hide_advanced">
    <label for="customer_attach_booking_details_access_link" class="checkbox-inline">
        <input type="checkbox" name="customer_attach_booking_details_access_link" id="customer_attach_booking_details_access_link" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Attach booking tracking link to on-route notification, it will allow customer to view journey details and driver on map
    </label>
</div>

<div style="margin-top:15px; margin-bottom:2px;" class="clearfix">
  <span style="float:left;">Hide Customer details from notification when Lead passenger is available</span>
  <i class="ion-ios-information-outline" style="float:left; margin-top:-2px; margin-left:5px; font-size:18px;" data-toggle="popover" data-title="" data-content="This setting enables to hide all Customer details information when Lead passenger information is present. It simplifies the amount of information the passenger (Lead passenger) sees in the notifications."></i>
</div>
<div class="form-group field-customer_show_only_lead_passenger field-size-fw">
    <div class="radio" style="margin-top:0;">
        <label for="customer_show_only_lead_passenger_1" class="checkbox-inline">
            <input type="radio" name="customer_show_only_lead_passenger" id="customer_show_only_lead_passenger_1" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Yes
        </label>
        <label for="customer_show_only_lead_passenger_0" class="checkbox-inline">
            <input type="radio" name="customer_show_only_lead_passenger" id="customer_show_only_lead_passenger_0" value="0" @permission('admin.settings.users.edit')@else readonly @endpermission> No
        </label>
    </div>
</div>


<div class="eto-config-section-header" style="margin-top:20px;">Drivers</div>
<div class="form-group field-driver_show_total eto-config-form-group field-size-fw">
    <label for="driver_show_total" class="checkbox-inline">
        <input type="checkbox" name="driver_show_total" id="driver_show_total" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show total price to driver
    </label>
</div>

<div class="form-group field-driver_show_unique_id eto-config-form-group field-size-fw">
    <label for="driver_show_unique_id" class="checkbox-inline">
        <input type="checkbox" name="driver_show_unique_id" id="driver_show_unique_id" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show "Unique ID" next to driver name
    </label>
</div>

<div class="form-group field-driver_show_onroute_button eto-config-form-group field-size-fw">
    <label for="driver_show_onroute_button" class="checkbox-inline">
        <input type="checkbox" name="driver_show_onroute_button" id="driver_show_onroute_button" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Show "On route" status button in driver panel
    </label>
</div>

<div class="form-group field-driver_show_arrived_button eto-config-form-group field-size-fw">
    <label for="driver_show_arrived_button" class="checkbox-inline">
        <input type="checkbox" name="driver_show_arrived_button" id="driver_show_arrived_button" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Show "Arrived" status button in driver panel
    </label>
</div>

<div class="form-group field-driver_show_onboard_button eto-config-form-group field-size-fw">
    <label for="driver_show_onboard_button" class="checkbox-inline">
        <input type="checkbox" name="driver_show_onboard_button" id="driver_show_onboard_button" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Show "On board" status button in driver panel
    </label>
</div>

<div class="form-group field-driver_show_reject_button eto-config-form-group field-size-fw">
    <label for="driver_show_reject_button" class="checkbox-inline">
        <input type="checkbox" name="driver_show_reject_button" id="driver_show_reject_button" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Allow driver to "Reject" the job
    </label>
</div>

<div class="form-group field-driver_show_restart_button eto-config-form-group field-size-fw hide_advanced">
    <label for="driver_show_restart_button" class="checkbox-inline">
        <input type="checkbox" name="driver_show_restart_button" id="driver_show_restart_button" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Allow driver to "Restart" the job
    </label>
</div>

<div class="form-group field-driver_allow_cancel field-size-fw placeholder-disabled">
    <div class="radio">
        <label for="driver_allow_cancel_0">
            <input type="radio" name="driver_allow_cancel" id="driver_allow_cancel_0" value="0" checked="checked" @permission('admin.settings.users.edit')@else readonly @endpermission>
            <span>Allow driver to change status to "Driver Cancelled" and send an email notification to the admin</span>
        </label>
    </div>

    <div class="radio">
        <label for="driver_allow_cancel_1">
            <input type="radio" name="driver_allow_cancel" id="driver_allow_cancel_1" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>
            <span>Allow driver to change status to "Cancelled" and automatically send an email notification to the customer</span>
        </label>
    </div>

    <div class="radio">
        <label for="driver_allow_cancel_2">
            <input type="radio" name="driver_allow_cancel" id="driver_allow_cancel_2" value="2" @permission('admin.settings.users.edit')@else readonly @endpermission>
            <span>Do not allow driver to cancel the job</span>
        </label>
    </div>
</div>

<div class="form-group field-driver_show_passenger_phone_number eto-config-form-group field-size-fw">
    <label for="driver_show_passenger_phone_number" class="checkbox-inline">
        <input type="checkbox" name="driver_show_passenger_phone_number" id="driver_show_passenger_phone_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show passenger phone number to driver
    </label>
</div>

<div class="form-group field-driver_show_passenger_email eto-config-form-group field-size-fw">
    <label for="driver_show_passenger_email" class="checkbox-inline">
        <input type="checkbox" name="driver_show_passenger_email" id="driver_show_passenger_email" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show passenger email to driver
    </label>
</div>

<div class="form-group field-driver_show_edit_profile_button eto-config-form-group field-size-fw">
    <label for="driver_show_edit_profile_button" class="checkbox-inline">
        <input type="checkbox" name="driver_show_edit_profile_button" id="driver_show_edit_profile_button" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Show edit profile button in driver account
    </label>
</div>

<div class="form-group field-driver_show_edit_profile_insurance eto-config-form-group field-size-fw">
    <label for="driver_show_edit_profile_insurance" class="checkbox-inline">
        <input type="checkbox" name="driver_show_edit_profile_insurance" id="driver_show_edit_profile_insurance" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Allow driver to edit insurance number
    </label>
</div>

<div class="form-group field-driver_show_edit_profile_driving_licence eto-config-form-group field-size-fw">
    <label for="driver_show_edit_profile_driving_licence" class="checkbox-inline">
        <input type="checkbox" name="driver_show_edit_profile_driving_licence" id="driver_show_edit_profile_driving_licence" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Allow driver to edit driving license number
    </label>
</div>

<div class="form-group field-driver_show_edit_profile_pco_licence eto-config-form-group field-size-fw">
    <label for="driver_show_edit_profile_pco_licence" class="checkbox-inline">
        <input type="checkbox" name="driver_show_edit_profile_pco_licence" id="driver_show_edit_profile_pco_licence" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Allow driver to edit PCO license number
    </label>
</div>

<div class="form-group field-driver_show_edit_profile_phv_licence eto-config-form-group field-size-fw">
    <label for="driver_show_edit_profile_phv_licence" class="checkbox-inline">
        <input type="checkbox" name="driver_show_edit_profile_phv_licence" id="driver_show_edit_profile_phv_licence" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission> Allow driver to edit PHV license number
    </label>
</div>

<div class="form-group field-driver_attach_booking_details_to_email eto-config-form-group field-size-fw">
    <label for="driver_attach_booking_details_to_email" class="checkbox-inline">
        <input type="checkbox" name="driver_attach_booking_details_to_email" id="driver_attach_booking_details_to_email" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Attach booking details to driver assign notification email
    </label>
</div>

<div class="form-group field-driver_attach_booking_details_to_sms eto-config-form-group field-size-fw">
    <label for="driver_attach_booking_details_to_sms" class="checkbox-inline">
        <input type="checkbox" name="driver_attach_booking_details_to_sms" id="driver_attach_booking_details_to_sms" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Attach booking details to driver assign notification sms
    </label>
</div>

<div class="form-group field-driver_calendar_show_ref_number eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_ref_number" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_ref_number" id="driver_calendar_show_ref_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show reference number in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_from eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_from" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_from" id="driver_calendar_show_from" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show pickup location in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_to eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_to" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_to" id="driver_calendar_show_to" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show dropoff location in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_via eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_via" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_via" id="driver_calendar_show_via" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show via location in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_vehicle_type eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_vehicle_type" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_vehicle_type" id="driver_calendar_show_vehicle_type" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show vehicle type in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_estimated_time eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_estimated_time" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_estimated_time" id="driver_calendar_show_estimated_time" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show estimated time in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_actual_time_slot eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_actual_time_slot" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_actual_time_slot" id="driver_calendar_show_actual_time_slot" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show actual time slot in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_passengers eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_passengers" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_passengers" id="driver_calendar_show_passengers" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show number of passengers in calendar
    </label>
</div>

<div class="form-group field-driver_calendar_show_custom eto-config-form-group field-size-fw">
    <label for="driver_calendar_show_custom" class="checkbox-inline">
        <input type="checkbox" name="driver_calendar_show_custom" id="driver_calendar_show_custom" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show booking custom field in calendar
    </label>
</div>

<div class="form-group field-driver_booking_file_upload eto-config-form-group field-size-fw">
    <label for="driver_booking_file_upload" class="checkbox-inline">
        <input type="checkbox" name="driver_booking_file_upload" id="driver_booking_file_upload" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Allow driver to upload job attachments/files
    </label>
</div>

<div class="eto-config-section-header" style="margin-top:20px;">Admin</div>
<div class="form-group field-admin_booking_listing_highlight eto-config-form-group field-size-fw">
    <label for="admin_booking_listing_highlight" class="checkbox-inline">
        <input type="checkbox" name="admin_booking_listing_highlight" id="admin_booking_listing_highlight" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Highlight bookings with status colour in booking listing tab
    </label>
</div>

<div class="form-group field-admin_calendar_show_ref_number eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_ref_number" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_ref_number" id="admin_calendar_show_ref_number" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show reference number in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_name_passenger eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_name_passenger" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_name_passenger" id="admin_calendar_show_name_passenger" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show passenger name in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_name_customer eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_name_customer" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_name_customer" id="admin_calendar_show_name_customer" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show account name in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_service_type eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_service_type" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_service_type" id="admin_calendar_show_service_type" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show service type in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_from eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_from" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_from" id="admin_calendar_show_from" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show pickup location in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_to eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_to" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_to" id="admin_calendar_show_to" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show dropoff location in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_via eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_via" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_via" id="admin_calendar_show_via" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show via location in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_vehicle_type eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_vehicle_type" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_vehicle_type" id="admin_calendar_show_vehicle_type" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show vehicle type in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_estimated_time eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_estimated_time" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_estimated_time" id="admin_calendar_show_estimated_time" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show estimated time in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_actual_time_slot eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_actual_time_slot" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_actual_time_slot" id="admin_calendar_show_actual_time_slot" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show actual time slot in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_driver_name eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_driver_name" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_driver_name" id="admin_calendar_show_driver_name" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show driver name in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_passengers eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_passengers" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_passengers" id="admin_calendar_show_passengers" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show number of passengers in calendar
    </label>
</div>

<div class="form-group field-admin_calendar_show_custom eto-config-form-group field-size-fw">
    <label for="admin_calendar_show_custom" class="checkbox-inline">
        <input type="checkbox" name="admin_calendar_show_custom" id="admin_calendar_show_custom" value="1" @permission('admin.settings.users.edit')@else readonly @endpermission>Show booking custom field in calendar
    </label>
</div>

<div class="form-group field-admin_default_page field-size-md" style="margin-top:15px;">
    <label for="admin_default_page">Set default page after login</label>
    <select name="admin_default_page" id="admin_default_page" class="form-control" @permission('admin.settings.users.edit')@else readonly @endpermission>
        <option value="getting-started">Getting Started</option>
        <option value="dispatch">Dispatch</option>
        <option value="bookings-next24">Bookings -> Next 24</option>
        <option value="bookings-latest">Bookings -> Latest</option>
        <option value="bookings-unconfirmed">Bookings -> Unconfirmed</option>
    </select>
</div>
