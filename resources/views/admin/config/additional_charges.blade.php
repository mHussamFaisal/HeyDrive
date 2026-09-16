
<input type="hidden" name="settings_group" id="settings_group" value="additional_charges">

<p style="font-size:14px;color:#9E9E9E;margin-bottom:20px;">
    - All prices are displayed in GBP (£) currency.
</p>

<div class="form-group field-meet_and_greet">
    <label for="meet_and_greet">Meet and greet</label>
    <input type="text" name="meet_and_greet" id="meet_and_greet" placeholder="Meet and greet" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-child_seat">
    <label for="child_seat">Child seat</label>
    <input type="text" name="child_seat" id="child_seat" placeholder="Child seat" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-baby_seat">
    <label for="baby_seat">Booster seat</label>
    <input type="text" name="baby_seat" id="baby_seat" placeholder="Booster seat" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-infant_seats">
    <label for="infant_seats">Infant seat</label>
    <input type="text" name="infant_seats" id="infant_seats" placeholder="Infant seat" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-wheelchair">
    <label for="wheelchair">Wheelchair</label>
    <input type="text" name="wheelchair" id="wheelchair" placeholder="Wheelchair" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-waiting_time">
    <label for="waiting_time">Waiting time after landing</label>
    <input type="text" name="waiting_time" id="waiting_time" placeholder="Waiting time after landing" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-geocode_start hide_advanced">
    <label for="geocode_start">Pick up airport</label>
    <input type="text" name="geocode_start" id="geocode_start" placeholder="Pick up airport" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-geocode_end hide_advanced">
    <label for="geocode_end">Drop off airport</label>
    <input type="text" name="geocode_end" id="geocode_end" placeholder="Drop off airport" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="form-group field-waypoint">
    <label for="waypoint">Waypoint</label>
    <input type="text" name="waypoint" id="waypoint" placeholder="Waypoint" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.additional_charges.edit')@else readonly @endpermission>
</div>

<div class="field-itemsList" style="margin-top:30px;">
    <div style="margin-bottom:10px; font-weight:bold;">Additional options (e.g. Newspaper, Bottle of water, etc.)</div>
    <div id="itemsList" style="display:inline-block;"></div>
</div>
