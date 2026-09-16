
<input type="hidden" name="settings_group" id="settings_group" value="bases">

<p style="font-size:14px;color:#e70001;margin-bottom:10px;">
    For Operating Area to work, please ensure you enter correct full address for each area e.g. "1 Threadneedle St, London EC2R 8AH"<br />
</p>

<div id="basesList" style="display:inline-block; margin-bottom:25px;"></div>

<div class="clearfix">
    <div style="float:left;">
        <div class="form-group field-booking_base_action field-size-lg">
            <label for="booking_base_action">What to do when journey is not in operating area?</label>
            <select name="booking_base_action" id="booking_base_action" class="form-control" @permission('admin.settings.bases.edit')@else readonly @endpermission>
                <option value="disallow">Booking is not allowed</option>
                <option value="quote">Allow submitting booking without price</option>
                <option value="allow">Allow booking and add driver journey to total price</option>
            </select>
        </div>
    </div>
    <i class="ion-ios-information-outline" style="margin-left:10px; font-size:24px;" data-toggle="popover" data-title="Operating Area(s)" data-content='Setting operating area allows additional modification how pricing works.<br><br>- You set Operating Area by setting Base (center of your operation) and radius in mi/km.<br><br>- Any journey that starts or finishes within Operating Area is calculated as normal.<br><br>- Price of any journey that neither start or finish in Operating Area depends choice of following option:<br><br><div style="margin-left:10px;"><b>Booking is not allowed</b> - This will not allow booking to be made. A message will be display that company is not operating within this area.<br><b><br>Allow submitting booking without price</b> - It allows booking to be made but pricing is not displayed. A message appear "Please finish booking reservation without price and we will provide you with a quote". Prices are not displayed but booking can be made as normal with Reserve button at the end instead of Case or Credit Card payment.<br><br><b>Allow booking and add driver journey to total price</b> - It allows booking to be made but adds cost of driver journey to the nearest point (Pickup/Dropoff).</div>'></i>
</div>

<div class="hide_advanced">
    <div class="form-group field-booking_exclude_driver_journey_from_fixed_price field-size-fw">
        <label for="booking_exclude_driver_journey_from_fixed_price" class="checkbox-inline">
            <input type="checkbox" name="booking_exclude_driver_journey_from_fixed_price" id="booking_exclude_driver_journey_from_fixed_price" value="1" @permission('admin.settings.bases.edit')@else readonly @endpermission> Exclude driver journey from fixed price
        </label>
    </div>

    <div class="form-group field-booking_base_calculate_type_enable field-size-fw">
        <label for="booking_base_calculate_type_enable" class="checkbox-inline">
            <input type="checkbox" name="booking_base_calculate_type_enable" id="booking_base_calculate_type_enable" value="1" @permission('admin.settings.bases.edit')@else readonly @endpermission> Enable option "Driver journey will be calculated as follows"
        </label>
    </div>

    <div class="form-group field-booking_base_calculate_type field-size-md" style="display:none;">
        <label for="booking_base_calculate_type">Driver journey will be calculated as follows</label>
        <select name="booking_base_calculate_type" id="booking_base_calculate_type" class="form-control" @permission('admin.settings.bases.edit')@else readonly @endpermission>
            <option value="from">Base - Pickup - Dropoff</option>
            <option value="to">Pickup - Dropoff - Base</option>
            <option value="both">Base - Pickup - Dropoff - Base</option>
        </select>
    </div>
</div>
