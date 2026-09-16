
<input type="hidden" name="settings_group" id="settings_group" value="night_surcharge">

<p style="font-size:14px;color:#9E9E9E;margin-bottom:10px;">
    - This is how the factor will change total amount (Total * X) or (Total + X).
</p>

<div class="form-group field-night_charge_enable">
    <label for="night_charge_enable" class="checkbox-inline">
        <input type="checkbox" name="night_charge_enable" id="night_charge_enable" value="1" @permission('admin.settings.night_surcharge.edit')@else readonly @endpermission>Active night surcharge
    </label>
</div>

<div id="nightSurchargeList"></div>

<style>
    #nightSurchargeList table {
        margin-bottom:0 !important;
    }
    #nightSurchargeList table tfoot td {
        border:0;
    }
</style>

<div style="margin-top:20px;" class="hide">
    <div class="form-group field-night_charge_start">
        <label for="night_charge_start">From</label>
        <select name="night_charge_start" id="night_charge_start" data-placeholder="From" required class="form-control select2" @permission('admin.settings.night_surcharge.edit')@else readonly @endpermission>
        @for ($i = 0; $i <= 23; $i++)
            @for ($j = 0; $j < 60; $j+=5)
                <option value="{{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}">
                    {{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}
                </option>
            @endfor
        @endfor
        </select>
    </div>

    <div class="form-group field-night_charge_end">
        <label for="night_charge_end">To</label>
        <select name="night_charge_end" id="night_charge_end" data-placeholder="To" required class="form-control select2" @permission('admin.settings.night_surcharge.edit')@else readonly @endpermission>
        @for ($i = 0; $i <= 23; $i++)
            @for ($j = 0; $j < 60; $j+=5)
                <option value="{{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}">
                    {{ ($i < 10) ? '0'. $i : $i }}:{{ ($j < 10) ? '0'. $j : $j }}
                </option>
            @endfor
        @endfor
        </select>
    </div>

    <div class="form-group field-night_charge_factor_type">
        <label for="night_charge_factor_type">Action</label>
        <select name="night_charge_factor_type" id="night_charge_factor_type" required class="form-control" @permission('admin.settings.night_surcharge.edit')@else readonly @endpermission>
        <option value="0" selected>Multiply</option>
        <option value="1">Add</option>
        </select>
    </div>

    <div class="form-group field-night_charge_factor">
        <label for="night_charge_factor">Factor value</label>
        <input type="text" name="night_charge_factor" id="night_charge_factor" placeholder="Factor value" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.night_surcharge.edit')@else readonly @endpermission>
    </div>
</div>
