
<input type="hidden" name="settings_group" id="settings_group" value="other_discounts">

<div class="form-group field-booking_return_discount">
    <label for="booking_return_discount">Return journey discount (%)</label>
    <input type="text" name="booking_return_discount" id="booking_return_discount" placeholder="Return journey discount (%)" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.other_discounts.edit')@else readonly @endpermission>
</div>

<div class="form-group field-booking_account_discount">
    <label for="booking_account_discount">Account discount (%)</label>
    <input type="text" name="booking_account_discount" id="booking_account_discount" placeholder="Account discount (%)" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" @permission('admin.settings.other_discounts.edit')@else readonly @endpermission>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('driver/jobs.child_seats') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-booking_discount_child_seats field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="booking_discount_child_seats_1" class="checkbox-inline">
                <input type="radio" name="booking_discount_child_seats" id="booking_discount_child_seats_1" value="1" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="booking_discount_child_seats_0" class="checkbox-inline">
                <input type="radio" name="booking_discount_child_seats" id="booking_discount_child_seats_0" value="0" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('admin/config.driver_income.additional_items') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-booking_discount_additional_items field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="booking_discount_additional_items_1" class="checkbox-inline">
                <input type="radio" name="booking_discount_additional_items" id="booking_discount_additional_items_1" value="1" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="booking_discount_additional_items_0" class="checkbox-inline">
                <input type="radio" name="booking_discount_additional_items" id="booking_discount_additional_items_0" value="0" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('admin/config.driver_income.parking_charges') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-booking_discount_parking_charges field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="booking_discount_parking_charges_1" class="checkbox-inline">
                <input type="radio" name="booking_discount_parking_charges" id="booking_discount_parking_charges_1" value="1" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="booking_discount_parking_charges_0" class="checkbox-inline">
                <input type="radio" name="booking_discount_parking_charges" id="booking_discount_parking_charges_0" value="0" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>

{{-- <div>
   <div style="display: inline-block; margin-right: 10px;width: 150px;">
       {{ trans('admin/config.driver_income.payment_charges') }}
   </div>
   <div style="display:inline-block; margin-bottom:5px;" class="form-group field-booking_discount_payment_charges field-size-fw">
       <div class="radio">
           <label for="booking_discount_payment_charges_1" class="checkbox-inline">
               <input type="radio" name="booking_discount_payment_charges" id="booking_discount_payment_charges_1" value="1" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
               {{ trans('admin/config.include') }}
           </label>
           <label for="booking_discount_payment_charges_0" class="checkbox-inline">
               <input type="radio" name="booking_discount_payment_charges" id="booking_discount_payment_charges_0" value="0" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
               {{ trans('admin/config.exclude') }}
           </label>
       </div>
   </div>
</div> --}}

<div>
    <div style="display: inline-block; margin-right: 10px;width: 150px;">
        {{ trans('driver/jobs.meet_and_greet') }}
    </div>
    <div style="display:inline-block; margin-bottom:5px;" class="form-group field-booking_discount_meet_and_greet field-size-fw">
        {{-- <div class="radio"> --}}
            <label for="booking_discount_meet_and_greet_1" class="checkbox-inline">
                <input type="radio" name="booking_discount_meet_and_greet" id="booking_discount_meet_and_greet_1" value="1" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.include') }}
            </label>
            <label for="booking_discount_meet_and_greet_0" class="checkbox-inline">
                <input type="radio" name="booking_discount_meet_and_greet" id="booking_discount_meet_and_greet_0" value="0" @permission('admin.settings.other_discount.edit')@else readonly @endpermission>
                {{ trans('admin/config.exclude') }}
            </label>
        {{-- </div> --}}
    </div>
</div>
