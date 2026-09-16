
<input type="hidden" name="settings_group" id="settings_group" value="invoices">

<div class="form-group field-invoice_enabled eto-config-form-group field-size-fw">
    <label for="invoice_enabled" class="checkbox-inline">
        <input type="checkbox" name="invoice_enabled" id="invoice_enabled" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Enable invoices
    </label>
</div>

<div class="form-group field-invoice_display_details eto-config-form-group field-size-fw">
    <label for="invoice_display_details" class="checkbox-inline">
        <input type="checkbox" name="invoice_display_details" id="invoice_display_details" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Display journey details
    </label>
</div>

<div class="form-group field-invoice_display_logo eto-config-form-group field-size-fw">
    <label for="invoice_display_logo" class="checkbox-inline">
        <input type="checkbox" name="invoice_display_logo" id="invoice_display_logo" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Display company logo
    </label>
</div>

<div class="form-group field-invoice_display_payments eto-config-form-group field-size-fw">
    <label for="invoice_display_payments" class="checkbox-inline">
        <input type="checkbox" name="invoice_display_payments" id="invoice_display_payments" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Display payment history
    </label>
</div>

<div class="form-group field-invoice_display_custom_field eto-config-form-group field-size-fw">
    <label for="invoice_display_custom_field" class="checkbox-inline">
        <input type="checkbox" name="invoice_display_custom_field" id="invoice_display_custom_field" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Display custom field
    </label>
</div>

<div class="form-group field-invoice_display_company_number eto-config-form-group field-size-fw">
    <label for="invoice_display_company_number" class="checkbox-inline">
        <input type="checkbox" name="invoice_display_company_number" id="invoice_display_company_number" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Display customer company number
    </label>
</div>

<div class="form-group field-invoice_display_company_tax_number field-size-fw">
    <label for="invoice_display_company_tax_number" class="checkbox-inline">
        <input type="checkbox" name="invoice_display_company_tax_number" id="invoice_display_company_tax_number" value="1" @permission('admin.settings.invoices.edit')@else readonly @endpermission>Display customer company tax number
    </label>
</div>

<div class="form-group field-invoice_info field-size-lg">
    <label for="invoice_info">Additional info</label>
    <textarea name="invoice_info" id="invoice_info" placeholder="Additional info" class="form-control" @permission('admin.settings.invoices.edit')@else readonly @endpermission></textarea>
</div>

<div class="form-group field-invoice_bill_from field-size-lg">
    <label for="invoice_bill_from">Company info (eg. company name, address, etc.)</label>
    <textarea name="invoice_bill_from" id="invoice_bill_from" placeholder="Company info (eg. company name, address, etc.)" class="form-control" @permission('admin.settings.invoices.edit')@else readonly @endpermission></textarea>
</div>
