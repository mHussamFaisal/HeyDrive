
<input type="hidden" name="settings_group" id="settings_group" value="general">

<div class="form-group field-company_name field-size-md">
    <label for="company_name">Company name</label>
    <input type="text" name="company_name" id="company_name" placeholder="Company name" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
</div>
<div class="form-group field-company_address field-size-md">
    <label for="company_address">Company address</label>
    <textarea name="company_address" id="company_address" placeholder="Company address" class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission></textarea>
</div>
<div class="form-group field-company_number field-size-md">
    <label for="company_number">Company registration number</label>
    <input type="text" name="company_number" id="company_number" placeholder="Company registration number" class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
</div>
<div class="clearfix">
    <div class="form-group field-company_email field-size-md" style="float:left; width: 300px; max-width: 100%;">
        <label for="company_email">Company email</label>
        <input type="text" name="company_email" id="company_email" placeholder="Company email" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
    </div>
    <i class="ion-ios-information-outline" style="display:inline-block; margin-top:4px; margin-left:10px; font-size:18px;" data-toggle="popover" data-title="" data-content='In case you have a problem with sending/receiving emails you might have to set up <b>SMTP</b> connection instead of <b>Mail</b>.</br></br>This can be done in "Settings -> Integration -> Mail settings"'></i>
</div>
<div class="form-group field-company_telephone field-size-md">
    <label for="company_telephone">Company telephone</label>
    <input type="text" name="company_telephone" id="company_telephone" placeholder="Company telephone" class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
</div>

<div id="dmodal" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
@permission('admin.settings.general.edit')
<a href="#" class="btn btn-sm btn-default btnUploadLogo" style="margin-bottom: 20px;">
    <i class="fa fa-upload"></i> <span>Change logo</span>
</a>
@endpermission
<script>
$(document).ready(function(){
    $('#dmodal').modal({
        show: false
    })
    .on('hidden.bs.modal', function(){
        var modalBox = $(this);

        if (modalBox.hasClass('modal-settings-general-urls')) {
            var urlLocales = $('#url_locales').val() ? JSON.parse($('#url_locales').val()) : [];

            modalBox.find('input[type="text"]').each(function() {
                var inputParams = {
                    code: $(this).attr('data-url-input-code'),
                    type: $(this).attr('data-url-input-type'),
                    value: $(this).val()
                };

                var index = -1;
                $.each(urlLocales, function(k2, v2) {
                    if (inputParams.type == v2.type && inputParams.code == v2.code) {
                        index = k2;
                        return;
                    }
                });

                if (index >= 0) {
                    if (inputParams.value) {
                        urlLocales[index] = inputParams;
                    }
                    else {
                        urlLocales.splice(index, 1);
                    }
                } else {
                    if (inputParams.value) {
                        urlLocales.push(inputParams);
                    }
                }
            });

            $('#url_locales').val(JSON.stringify(urlLocales)).change();
        }

        $('#dmodal').removeClass('modal-settings-general-logo');
        $('#dmodal').removeClass('modal-settings-general-urls');
    });

    // Urls
    $('body').on('change', '#url_locales', function(e) {
        var urlLocales = $('#url_locales').val() ? JSON.parse($('#url_locales').val()) : [];
        var counts = [];

        $.each(urlLocales, function(k1, v1) {
            var index = -1;
            $.each(counts, function(k2, v2) {
                if (v1.type == v2.type) {
                    index = k2;
                    return;
                }
            });

            if (index >= 0) {
                counts[index].count += 1;
            } else {
                counts.push({type: v1.type, count: 1});
            }
        });

        $('.settings-urls-link-has-value').removeClass('settings-urls-link-has-value');
        $.each(counts, function(k, v) {
            $('button.settings-urls-link[data-url-type="'+ v.type +'"]').addClass('settings-urls-link-has-value');
        });
    });

    $('body').on('click', '.settings-urls-link', function(e) {
        e.preventDefault();
        $('[data-toggle="tooltip"]').tooltip('hide');
        $('[data-toggle="popover"]').popover('hide');

        var urlLocales = $('#url_locales').val() ? JSON.parse($('#url_locales').val()) : [];
        var locales = {!! json_encode(config('app.locales')) !!}
        var type = $(this).attr('data-url-type');
        var name = $(this).attr('data-url-name');
        var title = name + '<i class="ion-ios-information-outline" style="display:inline-block; margin-top:4px; margin-left:10px; font-size:18px;" data-toggle="popover" data-title="" data-content="In case your website is translated into different languages and you need to set different URL for each language then you can do so below. If there is no override, the system will use default URL instead."></i>';
        var html = '';

        $.each(locales, function(k, v) {
            // var placeholder = $('input#'+ type).val();
            var placeholder = '';
            var value = '';
            $.each(urlLocales, function(k2, v2) {
                if (type == v2.type && v.code == v2.code) {
                    value = v2.value;
                    return;
                }
            });

            html += '<div class="form-group input-group placeholder-disabled clearfix">\
              <label for="'+ type +'_'+ v.code +'" title="'+ v.native +' ('+ v.code +')">'+ v.name +'</label>\
              <input type="text" id="'+ type +'_'+ v.code +'" value="'+ value +'" data-url-input-type="'+ type +'" data-url-input-code="'+ v.code +'" placeholder="'+ placeholder +'" class="form-control" onkeyup="var inputGroup = $(this).closest(\'.form-group\').find(\'.input-group-btn\'); if($(this).val()) { inputGroup.removeClass(\'hidden\') } else { inputGroup.addClass(\'hidden\'); }" >\
              <span class="input-group-btn '+ (value ? '' : 'hidden') +'">\
                <button type="button" class="btn btn-link btn-flat" onclick="$(this).closest(\'.form-group\').find(\'input[type=text]\').val(\'\'); $(this).closest(\'.form-group\').find(\'.input-group-btn\').addClass(\'hidden\');" title="Clear">\
                  <i class="fa fa-trash"></i>\
                </button>\
              </span>\
            </div>';
        });

        $('#dmodal').addClass('modal-settings-general-urls');
        $('#dmodal .modal-title').html(title);
        $('#dmodal .modal-body').html(html);
        $('#dmodal').modal('show');

        $('#dmodal').find('[data-toggle="popover"]').popover({
            placement: 'auto right',
            container: 'body',
            trigger: 'click focus hover',
            html: true
        });
    });

    // Logo
    $('#config .btnUploadLogo').on('click', function() {
        $('#dmodal').addClass('modal-settings-general-logo');
        $('#dmodal .modal-title').html('Change logo');
        $('#dmodal .modal-body').html('<iframe src="{{ route('admin.settings.general', ['tmpl' => 'body']) }}" style="border:0;width:100%;height:400px;"></iframe>');
        $('#dmodal').modal('show');
    });
});
</script>

<div class="form-group field-status_list">
    <label for="status_list">Status list</label>
    <textarea name="status_list" id="status_list" placeholder="Status list" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission></textarea>
</div>

<div style="margin-top:20px; margin-bottom:10px; font-weight:bold;" id="general-urls">URLs</div>

<textarea name="url_locales" id="url_locales" class="form-control hidden" @permission('admin.settings.general.edit')@else readonly @endpermission></textarea>

<div class="form-group field-url_home field-size-lg">
    <label for="url_home">Home URL</label>
    <input type="text" name="url_home" id="url_home" placeholder="Home URL" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
    <button type="button" class="btn btn-link btn-flat settings-urls-link" data-toggle="tooltip" data-title="Override default URL for specific language" data-url-type="url_home" data-url-name="Home URL">
      <i class="fa fa-cog"></i>
    </button>
</div>

<div class="form-group field-url_booking field-size-lg">
    <label for="url_booking">Booking URL</label>
    <input type="text" name="url_booking" id="url_booking" placeholder="Booking URL" required class="form-control"@permission('admin.settings.general.edit')@else readonly @endpermission>
    <button type="button" class="btn btn-link btn-flat settings-urls-link" data-toggle="tooltip" data-title="Override default URL for specific language" data-url-type="url_booking" data-url-name="Booking URL">
      <i class="fa fa-cog"></i>
    </button>
</div>

<div class="form-group field-url_customer field-size-lg">
    <label for="url_customer">My account URL</label>
    <input type="text" name="url_customer" id="url_customer" placeholder="My account URL" required class="form-control"@permission('admin.settings.general.edit')@else readonly @endpermission>
    <button type="button" class="btn btn-link btn-flat settings-urls-link" data-toggle="tooltip" data-title="Override default URL for specific language" data-url-type="url_customer" data-url-name="My account URL">
      <i class="fa fa-cog"></i>
    </button>
</div>

<div class="form-group field-url_contact field-size-lg">
    <label for="url_contact">Contact URL</label>
    <input type="text" name="url_contact" id="url_contact" placeholder="Contact URL" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
    <button type="button" class="btn btn-link btn-flat settings-urls-link" data-toggle="tooltip" data-title="Override default URL for specific language" data-url-type="url_contact" data-url-name="Contact URL">
      <i class="fa fa-cog"></i>
    </button>
</div>

<div class="form-group field-embedded field-size-fw">
    <label for="embedded" class="checkbox-inline">
        <input type="checkbox" name="embedded" id="embedded" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission>Force widgets to be displayed in iframe
    </label>
</div>

<div style="margin-top:40px; margin-bottom:10px;" class="clearfix">
  <span style="float:left; font-weight:bold;">Feedback</span>
  <i class="ion-ios-information-outline" style="display:inline-block; margin-top:-2px; margin-left:5px; font-size:18px;" data-toggle="popover" data-title="Feedback" data-content='This module allows you to manage customer feedback. There are three settings:<br><br>Internal setting enables customer to leave a feedback after the booking has been completed. Customer feedback can be view and manage in Software -> Feedback tab.<br><br>External setting allows directing customer to an external url possibly leading to a different feedback form  like Tripadvisor feedback or Google feedback.<br><br>None setting disable feedback from customer notification.'></i>
</div>
<div class="form-group field-feedback_type field-size-fw">
    <div class="radio">
        <label for="feedback_type_1" class="checkbox-inline">
            <input type="radio" name="feedback_type" id="feedback_type_1" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission> Internal
        </label>
        <label for="feedback_type_0" class="checkbox-inline">
            <input type="radio" name="feedback_type" id="feedback_type_0" value="0" checked="checked" @permission('admin.settings.general.edit')@else readonly @endpermission> External
        </label>
        <label for="feedback_type_2" class="checkbox-inline">
            <input type="radio" name="feedback_type" id="feedback_type_2" value="2" @permission('admin.settings.general.edit')@else readonly @endpermission> None
        </label>
    </div>
</div>

<div class="feedback-type0-container">
    <div class="form-group field-url_feedback field-size-lg">
        <label for="url_feedback">Feedback URL</label>
        <input type="text" name="url_feedback" id="url_feedback" placeholder="Feedback URL" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
        <button type="button" class="btn btn-link btn-flat settings-urls-link" data-toggle="tooltip" data-title="Override default URL for specific language" data-url-type="url_feedback" data-url-name="Feedback URL">
          <i class="fa fa-cog"></i>
        </button>
    </div>
</div>

<div style="margin-top:40px; margin-bottom:10px;"><b>Terms & Conditions</b></div>
<div class="form-group field-terms_enable field-size-fw">
    <label for="terms_enable" class="checkbox-inline">
        <input type="checkbox" name="terms_enable" id="terms_enable" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission> Enable
    </label>
</div>

<div class="terms-container" style="display:none;">
    <div class="form-group field-terms_type field-size-fw">
        <div class="radio">
            <label for="terms_type_0" class="checkbox-inline">
                <input type="radio" name="terms_type" id="terms_type_0" value="0" checked="checked" @permission('admin.settings.general.edit')@else readonly @endpermission> External
            </label>
            <label for="terms_type_1" class="checkbox-inline">
                <input type="radio" name="terms_type" id="terms_type_1" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission> Internal
            </label>
        </div>
    </div>
    <div class="terms-type0-container">
        <div class="form-group field-url_terms field-size-lg">
            <label for="url_terms">Terms & Conditions URL</label>
            <input type="text" name="url_terms" id="url_terms" placeholder="Terms & Conditions URL" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
            <button type="button" class="btn btn-link btn-flat settings-urls-link" data-toggle="tooltip" data-title="Override default URL for specific language" data-url-type="url_terms" data-url-name="Terms & Conditions URL">
              <i class="fa fa-cog"></i>
            </button>
        </div>
    </div>
    <div class="terms-type1-container" style="display:none;">
        <div class="form-group field-terms_text field-size-lg">
            <label for="terms_text">Terms</label>
            <textarea name="terms_text" id="terms_text" placeholder="Terms" class="form-control" style="height:150px;" @permission('admin.settings.general.edit')@else readonly @endpermission></textarea>
        </div>
        <div class="form-group field-terms_email field-size-fw">
            <label for="terms_email" class="checkbox-inline">
                <input type="checkbox" name="terms_email" id="terms_email" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission> Attach a PDF file with terms to booking confirmation email
            </label>
        </div>
        <div class="form-group field-terms_download field-size-fw">
            <label for="terms_download" class="checkbox-inline">
                <input type="checkbox" name="terms_download" id="terms_download" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission> Allow terms download
            </label>
        </div>
    </div>
</div>


<div class="hide_advanced">
    <div style="margin-top:40px; margin-bottom:10px;"><b>System updates</b></div>
    <div class="form-group field-cron_update_auto field-size-fw">
        <div class="radio">
            <label for="cron_update_auto_0" class="checkbox-inline">
                <input type="radio" name="cron_update_auto" id="cron_update_auto_0" value="0" @permission('admin.settings.general.edit')@else readonly @endpermission> Manual
            </label>
            <label for="cron_update_auto_1" class="checkbox-inline">
                <input type="radio" name="cron_update_auto" id="cron_update_auto_1" value="1" @permission('admin.settings.general.edit')@else readonly @endpermission> Automatic
            </label>
        </div>
    </div>
    <div class="form-group field-cron_update_time field-size-lg">
        <label for="cron_update_time">What time of the day to run update check?</label>
        <input type="text" name="cron_update_time" id="cron_update_time" placeholder="00:00:00" required class="form-control" @permission('admin.settings.general.edit')@else readonly @endpermission>
    </div>
    <div class="form-group field-cron_update_interval field-size-lg">
        <label for="cron_update_interval">Check every (x) days</label>
        <input type="number" name="cron_update_interval" id="cron_update_interval" placeholder="Check every (x) days" required class="form-control" min="0" step="1" @permission('admin.settings.general.edit')@else readonly @endpermission>
    </div>
</div>


<div class="{{ config('eto.allow_cron') == 1 ? '' : 'hide_advanced' }}">
    <div style="margin-top:40px; margin-bottom:10px;"><b>Cron settings</b></div>
    <div class="form-group input-group field-cron_secret_key field-size-lg">
        <label for="cron_secret_key">Secret key that will allow to run cron task command</label>
        <input type="password" name="cron_secret_key" id="cron_secret_key" placeholder="Secret key" class="form-control" autocomplete="new-password" @permission('admin.settings.general.edit')@else readonly @endpermission>
        @permission('admin.settings.general.edit')
        <span class="input-group-btn">
            <button type="button" class="btn btn-default btn-flat eto-pass-view">
                <i class="fa fa-eye"></i>
            </button>
        </span>
        <span class="input-group-btn">
            <button type="button" class="btn btn-default btn-flat eto-pass-generate">Generate</button>
        </span>
        @endpermission
    </div>
    <div class="form-group field-cron_job_reminder_minutes field-size-lg">
        <label for="cron_job_reminder_minutes">Send job reminder (x) minutes before the journey date</label>
        <input type="number" name="cron_job_reminder_minutes" id="cron_job_reminder_minutes" placeholder="Job reminder minutes" required class="form-control" min="0" step="1" @permission('admin.settings.general.edit')@else readonly @endpermission>
    </div>
    <div class="form-group field-cron_job_reminder_allowed_times field-size-lg">
        <label for="cron_job_reminder_allowed_times">Max amount of reminders that driver can receive per booking</label>
        <input type="number" name="cron_job_reminder_allowed_times" id="cron_job_reminder_allowed_times" placeholder="Job reminder allowed times" required class="form-control" min="1" step="1" @permission('admin.settings.general.edit')@else readonly @endpermission>
    </div>
</div>
