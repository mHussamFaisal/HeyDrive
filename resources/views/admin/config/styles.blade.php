
<input type="hidden" name="settings_group" id="settings_group" value="styles">

<div class="admin-settings-styles-section">
    <div class="admin-settings-styles-section-header">Web Booking (Buttons and Links)</div>
    <div class="row">
        <div class="col-sm-4 col-md-4">

            <div class="admin-settings-styles-header">Default</div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Background</div>
                <input type="text" name="styles_default_bg_color" id="styles_default_bg_color" value="" data-default_color="#1c70b1" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Border</div>
                <input type="text" name="styles_default_border_color" id="styles_default_border_color" value="" data-default_color="#1c70b1" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Text</div>
                <input type="text" name="styles_default_text_color" id="styles_default_text_color" value="" data-default_color="#ffffff" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <br>

        </div>
        <div class="col-sm-4 col-md-4">

            <div class="admin-settings-styles-header">Active</div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Background</div>
                <input type="text" name="styles_active_bg_color" id="styles_active_bg_color" value="" data-default_color="#185f96" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Border</div>
                <input type="text" name="styles_active_border_color" id="styles_active_border_color" value="" data-default_color="#185f96" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Text</div>
                <input type="text" name="styles_active_text_color" id="styles_active_text_color" value="" data-default_color="#ffffff" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <br>

        </div>
    </div>

    <div class="form-group field-styles_border_radius" style="margin-top:5px; max-width:150px;">
        <label for="styles_border_radius">Border radius (pixels)</label>
        <input type="number" name="styles_border_radius" id="styles_border_radius" value="0" placeholder="0" min="0" max="15" step="1" required class="form-control input-sm" style="padding-right:10px;" @permission('admin.settings.styles.edit')@else readonly @endpermission>
    </div>

    <div class="clearfix">
        <span style="float:left; margin-right:5px;">Custom CSS</span>
        <a style="float:left; line-height:22px;" href="#" class="help-button" data-toggle="popover" data-title="Custom CSS" data-content="This option allows to style booking widget form and customer account with custom CSS.<br>If you don't know what CSS is just leave it as is.">
            <i class="ion-ios-information-outline"></i>
        </a>
        <a href="#" onclick="$('.field-custom_css').toggle(); $(this).html($('.field-custom_css').is(':hidden') ? 'Show' : 'Hide'); return false;" style="margin-left:5px;">Show</a>
    </div>
    <div class="form-group field-custom_css field-size-lg" style="display:none; margin-top:5px;">
        <textarea name="custom_css" id="custom_css" placeholder="" class="form-control" @permission('admin.settings.styles.edit')@else readonly @endpermission></textarea>
    </div>
</div>


<div class="admin-settings-styles-section">
    <div class="admin-settings-styles-section-header">Mobile App (Buttons and Links)</div>
    <div class="row">
        <div class="col-sm-4 col-md-4">

            <div class="admin-settings-styles-header">Default</div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Background</div>
                <input type="text" name="mobile_app_styles_default_bg_color" id="mobile_app_styles_default_bg_color" value="" data-default_color="#1c70b1" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Border</div>
                <input type="text" name="mobile_app_styles_default_border_color" id="mobile_app_styles_default_border_color" value="" data-default_color="#1c70b1" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Text</div>
                <input type="text" name="mobile_app_styles_default_text_color" id="mobile_app_styles_default_text_color" value="" data-default_color="#ffffff" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <br>

        </div>
        <div class="col-sm-4 col-md-4">

            <div class="admin-settings-styles-header">Active</div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Background</div>
                <input type="text" name="mobile_app_styles_active_bg_color" id="mobile_app_styles_active_bg_color" value="" data-default_color="#185f96" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Border</div>
                <input type="text" name="mobile_app_styles_active_border_color" id="mobile_app_styles_active_border_color" value="" data-default_color="#185f96" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Text</div>
                <input type="text" name="mobile_app_styles_active_text_color" id="mobile_app_styles_active_text_color" value="" data-default_color="#ffffff" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <br>

        </div>
    </div>

    <div class="form-group field-mobile_app_styles_border_radius" style="margin-top:5px; max-width:150px;">
        <label for="mobile_app_styles_border_radius">Border radius (pixels)</label>
        <input type="number" name="mobile_app_styles_border_radius" id="mobile_app_styles_border_radius" value="0" placeholder="0" min="0" max="15" step="1" required class="form-control input-sm" style="padding-right:10px;" @permission('admin.settings.styles.edit')@else readonly @endpermission>
    </div>

    <div class="clearfix">
        <span style="float:left; margin-right:5px;">Custom CSS</span>
        <a style="float:left; line-height:22px;" href="#" class="help-button" data-toggle="popover" data-title="Custom CSS" data-content="This option allows to style mobile app booking form and customer account with custom CSS.<br>If you don't know what CSS is just leave it as is.">
            <i class="ion-ios-information-outline"></i>
        </a>
        <a href="#" onclick="$('.field-mobile_app_custom_css').toggle(); $(this).html($('.field-mobile_app_custom_css').is(':hidden') ? 'Show' : 'Hide'); return false;" style="margin-left:5px;">Show</a>
    </div>
    <div class="form-group field-mobile_app_custom_css field-size-lg" style="display:none; margin-top:5px;">
        <textarea name="mobile_app_custom_css" id="mobile_app_custom_css" placeholder="" class="form-control" @permission('admin.settings.styles.edit')@else readonly @endpermission></textarea>
    </div>
</div>


<div class="admin-settings-styles-section">
    <div class="admin-settings-styles-section-header">Invoice</div>
    <div class="row">
        <div class="col-sm-4 col-md-4">

            <div class="admin-settings-styles-header">Default</div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Background</div>
                <input type="text" name="invoice_styles_default_bg_color" id="invoice_styles_default_bg_color" value="" data-default_color="#3b8cc1" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Text</div>
                <input type="text" name="invoice_styles_default_text_color" id="invoice_styles_default_text_color" value="" data-default_color="#ffffff" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <br>

        </div>
        <div class="col-sm-4 col-md-4">

            <div class="admin-settings-styles-header">Active</div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Background</div>
                <input type="text" name="invoice_styles_active_bg_color" id="invoice_styles_active_bg_color" value="" data-default_color="#2f75a8" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <div class="admin-settings-styles-box clearfix">
                <div class="admin-settings-styles-label">Text</div>
                <input type="text" name="invoice_styles_active_text_color" id="invoice_styles_active_text_color" value="" data-default_color="#ffffff" class="form-control colorpicker" @permission('admin.settings.styles.edit')@else readonly @endpermission>
            </div>
            <br>

        </div>
    </div>
</div>
