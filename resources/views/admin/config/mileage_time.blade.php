
<input type="hidden" name="settings_group" id="settings_group" value="mileage_time">

<div id="distanceRangesList"></div>

<div class="form-group field-booking_duration_rate field-size-fw" style="margin-top:40px; max-width:150px;">
    <label for="booking_duration_rate">Price per minute</label>
    <input type="text" name="booking_duration_rate" id="booking_duration_rate" placeholder="Price per minute" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0"  @permission('admin.settings.mileage_time.edit')@else readonly @endpermission>
</div>
