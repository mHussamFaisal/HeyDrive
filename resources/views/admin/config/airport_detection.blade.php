
<input type="hidden" name="settings_group" id="settings_group" value="airport_detection">

<div style="font-size:14px;color:#9E9E9E;margin-bottom:20px;">
    - Please enter all airport <b><u>postcodes</u></b> separated by a comma and new line.<br />
    - The system only accepts postcodes e.g. "E16 2PX". Addresses e.g. "London City Airport, E16 2PX, UK" won't be detected.<br />
    - Options below will be for applying prices based on airport location.
</div>

<div class="form-group field-geocode_start_postcodes hide_advanced">
    <label for="geocode_start_postcodes">Pick up charge</label>
    <textarea name="geocode_start_postcodes" id="geocode_start_postcodes" placeholder="Pick up charge" class="form-control" style="height:100px;" @permission('admin.settings.airport_detection.edit')@else readonly @endpermission></textarea>
</div>

<div class="form-group field-geocode_end_postcodes hide_advanced">
    <label for="geocode_end_postcodes">Drop off charge</label>
    <textarea name="geocode_end_postcodes" id="geocode_end_postcodes" placeholder="Drop off charge" class="form-control" style="height:100px;" @permission('admin.settings.airport_detection.edit')@else readonly @endpermission></textarea>
</div>

<div class="form-group field-airport_postcodes">
    <label for="airport_postcodes">Meet and Greet, Flight Details</label>
    <textarea name="airport_postcodes" id="airport_postcodes" placeholder="Meet and Greet, Flight Details" class="form-control" style="height:100px;" @permission('admin.settings.airport_detection.edit')@else readonly @endpermission></textarea>
</div>
