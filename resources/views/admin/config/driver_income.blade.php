
<input type="hidden" name="settings_group" id="settings_group" value="driver_income">

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('driver/jobs.child_seats') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-driver_income_child_seats field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="driver_income_child_seats_1" class="checkbox-inline">
                <input type="radio" name="driver_income_child_seats" id="driver_income_child_seats_1" value="1" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="driver_income_child_seats_0" class="checkbox-inline">
                <input type="radio" name="driver_income_child_seats" id="driver_income_child_seats_0" value="0" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('admin/config.driver_income.additional_items') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-driver_income_additional_items field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="driver_income_additional_items_1" class="checkbox-inline">
                <input type="radio" name="driver_income_additional_items" id="driver_income_additional_items_1" value="1" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="driver_income_additional_items_0" class="checkbox-inline">
                <input type="radio" name="driver_income_additional_items" id="driver_income_additional_items_0" value="0" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('admin/config.driver_income.parking_charges') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-driver_income_parking_charges field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="driver_income_parking_charges_1" class="checkbox-inline">
                <input type="radio" name="driver_income_parking_charges" id="driver_income_parking_charges_1" value="1" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="driver_income_parking_charges_0" class="checkbox-inline">
                <input type="radio" name="driver_income_parking_charges" id="driver_income_parking_charges_0" value="0" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('admin/config.driver_income.payment_charges') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-driver_income_payment_charges field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="driver_income_payment_charges_1" class="checkbox-inline">
                <input type="radio" name="driver_income_payment_charges" id="driver_income_payment_charges_1" value="1" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="driver_income_payment_charges_0" class="checkbox-inline">
                <input type="radio" name="driver_income_payment_charges" id="driver_income_payment_charges_0" value="0" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('driver/jobs.meet_and_greet') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-driver_income_meet_and_greet field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="driver_income_meet_and_greet_1" class="checkbox-inline">
                <input type="radio" name="driver_income_meet_and_greet" id="driver_income_meet_and_greet_1" value="1" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="driver_income_meet_and_greet_0" class="checkbox-inline">
                <input type="radio" name="driver_income_meet_and_greet" id="driver_income_meet_and_greet_0" value="0" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('invoices.discount') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-driver_income_discounts field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="driver_income_discounts_1" class="checkbox-inline">
                <input type="radio" name="driver_income_discounts" id="driver_income_discounts_1" value="1" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="driver_income_discounts_0" class="checkbox-inline">
                <input type="radio" name="driver_income_discounts" id="driver_income_discounts_0" value="0" @permission('admin.settings.driver_income.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>
