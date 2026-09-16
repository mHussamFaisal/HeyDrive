
<input type="hidden" name="settings_group" id="settings_group" value="deposit_payments">

<div id="depositList" style="margin-bottom:20px;"></div>

<div class="form-group field-booking_deposit_balance">
    <label for="booking_deposit_balance">Remaining amount will be paid with</label>
    <div class="input-group">
        <select name="booking_deposit_balance" id="booking_deposit_balance" data-placeholder="" required class="form-control select2" data-minimum-results-for-search="Infinity" @permission('admin.settings.deposit_payments.edit')@else readonly @endpermission>
        <option value="card">Credit Card</option>
        <option value="cash">Cash</option>
        </select>
        <div class="input-group-addon">
            <a href="#" class="help-button" data-toggle="popover" data-title="" data-content="This option is used when customer chooses to pay deposit only. If so then two payments are created, deposit (paid with Credit Card) and balance (paid with Credit Card or Cash to hand).">
                <i class="ion-ios-information-outline"></i>
            </a>
        </div>
    </div>
</div>

<div class="form-group field-booking_deposit_selected">
    <label for="booking_deposit_selected">Select value by default</label>
    <select name="booking_deposit_selected" id="booking_deposit_selected" data-placeholder="" required class="form-control select2" data-minimum-results-for-search="Infinity "@permission('admin.settings.deposit_payments.edit')@else readonly @endpermission>
    <option value="deposit">Deposit</option>
    <option value="full_amount">Full amount</option>
    </select>
</div>

<div class="form-group field-fixed_prices_deposit_enable" style="margin-top:40px;">
    <label for="fixed_prices_deposit_enable" class="checkbox-inline">
        <input type="checkbox" name="fixed_prices_deposit_enable" id="fixed_prices_deposit_enable" value="1" @permission('admin.settings.deposit_payments.edit')@else readonly @endpermission>Active <span style="text-decoration:underline;">Fixed Prices</span> deposit
    </label>
</div>

<div class="form-group field-fixed_prices_deposit_type" style="display:none;">
    <label for="fixed_prices_deposit_type">Fixed prices deposit type</label>
    <select name="fixed_prices_deposit_type" id="fixed_prices_deposit_type" class="form-control" @permission('admin.settings.deposit_payments.edit')@else readonly @endpermission>
    <option value="0">Percent</option>
    <option value="1">Flat</option>
    </select>
</div>
