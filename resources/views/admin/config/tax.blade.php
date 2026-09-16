
<input type="hidden" name="settings_group" id="settings_group" value="tax">

<div class="form-group field-company_tax_number">
    <label for="company_tax_number">Company VAT number</label>
    <input type="text" name="company_tax_number" id="company_tax_number" placeholder="Company VAT number" class="form-control" @permission('admin.settings.tax.edit')@else readonly @endpermission>
</div>

<div class="form-group field-tax_name">
    <label for="tax_name">Tax name (eg. VAT)</label>
    <input type="text" name="tax_name" id="tax_name" placeholder="Tax name (eg. VAT)" value="" class="form-control" @permission('admin.settings.tax.edit')@else readonly @endpermission>
</div>

<div class="clearfix">
    <div class="form-group field-tax_percent" style="float:left;">
        <label for="tax_percent">Tax percent (%)</label>
        <input type="text" name="tax_percent" id="tax_percent" placeholder="Tax percent (%)" value="0" required class="form-control touchspin" data-bts-step="0.01" data-bts-decimals="2" data-bts-min="0" data-bts-max="100" @permission('admin.settings.tax.edit')@else readonly @endpermission>
    </div>
    <a style="float:left; margin: 5px 10px;" href="#" class="help-button" data-toggle="popover" data-title="" data-content="Tax is calculated as follows:<br>(Total Price / 100%) * X%">
        <i class="ion-ios-information-outline"></i>
    </a>
</div>
