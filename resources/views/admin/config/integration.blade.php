
<input type="hidden" name="settings_group" id="settings_group" value="integration">

<div style="margin-bottom:5px;">
    <span style="font-weight:bold; float:left; margin-right:5px;">Add code to the < HEAD > tag</span>
    <i class="ion-ios-information-outline" style="font-size:18px; line-height:22px; float:left;" data-toggle="popover" data-title="" data-content="<div style='text-align:left;''><b>HEAD tag</b><br>- This option allows to add custom code eg. tracking code to HEAD section of HTML.<br>- If you don't know what is HTML is just leave it as it is.</div>"></i>
    <a href="#" onclick="$('.field-code_head').toggle(); $(this).html($('.field-code_head').is(':hidden') ? 'Show' : 'Hide'); return false;" style="margin-left:5px;">Show</a>
    <div style="clear:both;"></div>
</div>

<div class="form-group field-code_head field-size-lg" style="display:none;">
    <textarea name="code_head" id="code_head" placeholder="" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission></textarea>
</div>

<div style="margin-bottom:5px;">
    <span style="font-weight:bold; float:left; margin-right:5px;">Add code to the < BODY > tag</span>
    <i class="ion-ios-information-outline" style="font-size:18px; line-height:22px; float:left;" data-toggle="popover" data-title="" data-content="<div style='text-align:left;''><b>BODY tag</b><br>- This option allows to add custom code eg. tracking code to BODY section of HTML.<br>- If you don't know what is HTML is just leave it as it is.</div>"></i>
    <a href="#" onclick="$('.field-code_body').toggle(); $(this).html($('.field-code_body').is(':hidden') ? 'Show' : 'Hide'); return false;" style="margin-left:5px;">Show</a>
    <div style="clear:both;"></div>
</div>

<div class="form-group field-code_body field-size-lg" style="display:none;">
    <textarea name="code_body" id="code_body" placeholder="" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission></textarea>
</div>

<div class="form-group field-eto_branding field-size-fw" style="margin-top:10px;">
    <label for="eto_branding" class="checkbox-inline">
        <input type="checkbox" name="eto_branding" id="eto_branding" value="1" @permission('admin.settings.integration.edit')@else readonly @endpermission>Enable EasyTaxiOffice footer
    </label>
</div>

<div class="clearfix" style="margin-top:20px;">
    <div class="form-group field-force_https field-size-md" style="float:left; width:220px;">
        <label for="force_https">Force HTTPS (Secure) connection</label>
        <select name="force_https" id="force_https" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>
    <i class="ion-ios-information-outline" style="display:inline-block; margin-top:4px; margin-left:10px; font-size:18px;" data-toggle="popover" data-title="Force HTTPS (Secure) connection" data-content='If this option is enabled it will force HTTPS (Secure) connection in entire booking software.<br>Please make sure that you have the SSL certificatate installed on your server before activating this option.'></i>
</div>


<div style="margin-bottom:20px; margin-top:40px;">
    <b>Mail settings</b> - Check how to integrate <a href="{{ config('app.docs_url') }}/general/email-setup/" target="_blank">guide</a>
</div>

<div class="form-group field-mail_driver field-size-sm">
    <label for="mail_driver">Connection type</label>
    <select name="mail_driver" id="mail_driver" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
        {{-- <option value="mail">PHP Mail</option> --}}
        <option value="sendmail">Sendmail</option>
        <option value="smtp">SMTP</option>
        {{--<option value="log">Log</option>--}}
    </select>
</div>

<div id="config-mail-smtp" style="display:none;">
    <div class="form-group field-mail_host field-size-md">
        <label for="mail_host">SMTP Host</label>
        <input type="text" name="mail_host" id="mail_host" placeholder="SMTP Host" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>

    <div class="form-group field-mail_port field-size-md">
        <label for="mail_port">SMTP Port</label>
        <input type="text" name="mail_port" id="mail_port" placeholder="SMTP Port" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>

    <div class="form-group field-mail_username field-size-md">
        <label for="mail_username">SMTP Username</label>
        <input type="text" name="mail_username" id="mail_username" placeholder="SMTP Username" class="form-control" autocomplete="false" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>

    <div class="form-group input-group field-mail_password field-size-md">
        <label for="mail_password">SMTP Password</label>
        <input type="password" name="mail_password" id="mail_password" placeholder="SMTP Password" class="form-control" autocomplete="new-password" @permission('admin.settings.integration.edit')@else readonly @endpermission>
        <span class="input-group-btn">
            <button type="button" class="btn btn-default btn-flat eto-pass-view">
                <i class="fa fa-eye"></i>
            </button>
        </span>
    </div>

    <div class="form-group field-mail_encryption field-size-sm placeholder-visible">
        <label for="mail_encryption">SMTP Security</label>
        <select name="mail_encryption" id="mail_encryption" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
            <option value="">None</option>
            <option value="ssl">SSL</option>
            <option value="tls">TLS</option>
        </select>
    </div>
</div>

<div id="config-mail-sendmail" style="display:none;">
    <div class="form-group field-mail_sendmail field-size-md">
        <label for="mail_sendmail">Sendmail path</label>
        <input type="text" name="mail_sendmail" id="mail_sendmail" placeholder="{{ (!empty(ini_get('sendmail_path')) ? trim(ini_get('sendmail_path')) : '/usr/sbin/sendmail -bs') }}" class="form-control" autocomplete="false" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>
</div>

@permission('admin.settings.integration.edit')
<a href="#" onclick="$('.eto-send-test-email').toggle(); $(this).hide(); return false;" style="display:inline-block; margin-top:15px;">{{ trans('admin/settings.send_test_email_button') }}</a>
<div class="eto-send-test-email" style="display:none; margin-top:40px;">
    <div style="margin-bottom:15px; font-weight:bold;">{{ trans('admin/settings.send_test_email_button') }}</div>
    <div class="form-group field-size-md">
        <label for="test_mail">{{ trans('admin/settings.send_test_email') }}</label>
        <div class="input-group">
            <input id="test_mail" type="email" class="form-control" placeholder="{{ trans('admin/settings.send_test_email') }}" value="{{ config('mail.from.address') }}" />
            <div class="input-group-btn">
                <button type="button" class="btn btn-default">Send</button>
            </div>
        </div>
    </div>
</div>
@endpermission

<div class="hide_advanced1">
    <div style="margin-bottom:5px; margin-top:40px;">
        <b>Caller ID</b> - Check how to integrate <a href="{{ config('app.docs_url') }}/getting-started/ringcentral-caller-id/" target="_blank">guide</a>
    </div>
    <div class="form-group field-callerid_type field-size-sm placeholder-visible">
        {{-- <label for="callerid_type">Caller Type</label> --}}
        <select name="callerid_type" id="callerid_type" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
            <option value="">Disabled</option>
            <option value="ringcentral">RingCentral</option>
        </select>
    </div>

    <div class="hide_advanced">
        <div id="config-callerid-ringcentral" style="display:none;">
            <div class="form-group field-ringcentral_environment field-size-sm placeholder-visible">
                <label for="ringcentral_environment">Environment</label>
                <select name="ringcentral_environment" id="ringcentral_environment" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
                    <option value="production">Production</option>
                    <option value="sandbox">Sandbox</option>
                </select>
            </div>

            <div class="form-group field-ringcentral_app_key field-size-md">
                <label for="ringcentral_app_key">Client ID</label>
                <input type="text" name="ringcentral_app_key" id="ringcentral_app_key" placeholder="Client ID" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
            </div>

            <div class="form-group field-ringcentral_app_secret field-size-md">
                <label for="ringcentral_app_secret">Client Secret</label>
                <input type="password" name="ringcentral_app_secret" id="ringcentral_app_secret" placeholder="Client Secret" class="form-control" autocomplete="new-password" @permission('admin.settings.integration.edit')@else readonly @endpermission>
            </div>
        </div>
    </div>

    <div id="config-callerid-general" style="display:none;">
        <div class="form-group field-ringcentral_widget_open field-size-md placeholder-visible">
            <label for="ringcentral_widget_open">Auto open RingCentral widget on call</label>
            <select name="ringcentral_widget_open" id="ringcentral_widget_open" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
                <option value="none">Disabled</option>
                <option value="all">Inbound & Outbound</option>
                <option value="inbound">Inbound</option>
                <option value="outbound">Outbound</option>
            </select>
        </div>

        <div class="form-group field-ringcentral_popup_open field-size-md placeholder-visible">
            <label for="ringcentral_popup_open">Auto open Caller ID popup on call</label>
            <select name="ringcentral_popup_open" id="ringcentral_popup_open" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
                <option value="none">Disabled</option>
                <option value="all">Inbound & Outbound</option>
                <option value="inbound">Inbound</option>
                <option value="outbound">Outbound</option>
            </select>
        </div>
    </div>
</div>

<div style="margin-bottom:5px; margin-top:40px;">
    <b>SMS Service</b>
</div>
<div class="form-group field-sms_service_type field-size-sm placeholder-visible">
    {{-- <label for="sms_service_type">SMS services type</label> --}}
    <select name="sms_service_type" id="sms_service_type" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
        <option value="">Disabled</option>
        <option value="twilio">Twilio</option>
        <option value="textlocal">Textlocal</option>
        <option value="smsgateway" style="display:none;">SMS Gateway</option>
    </select>
</div>

<div class="config-sms_service_type-twilio" style="display:none;">
    <div style="margin-bottom:15px; margin-top:0px;">
        Check how to integrate <a href="{{ config('app.docs_url') }}/getting-started/twilio-integration/" target="_blank">guide</a>
    </div>
    <div class="form-group field-twilio_sid field-size-md">
        <label for="twilio_sid">Account SID</label>
        <input type="text" name="twilio_sid" id="twilio_sid" placeholder="Account SID" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>
    <div class="form-group input-group field-twilio_token field-size-md">
        <label for="twilio_token">Auth token</label>
        <input type="password" name="twilio_token" id="twilio_token" placeholder="Auth token" class="form-control" autocomplete="new-password" @permission('admin.settings.integration.edit')@else readonly @endpermission>
        <span class="input-group-btn">
            <button type="button" class="btn btn-default btn-flat eto-pass-view">
                <i class="fa fa-eye"></i>
            </button>
        </span>
    </div>
    <div class="form-group field-twilio_phone_number field-size-md">
        <label for="twilio_phone_number">Twillo phone number</label>
        <input type="text" name="twilio_phone_number" id="twilio_phone_number" placeholder="Twillo phone number" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>
</div>
<div class="config-sms_service_type-textlocal" style="display:none;">
    <div style="margin-bottom:15px; margin-top:0px;">
        Check how to integrate <a href="{{ config('app.docs_url') }}/getting-started/textlocal-integration/" target="_blank">guide</a>
    </div>
    <div class="form-group input-group field-textlocal_api_key field-size-md">
        <label for="textlocal_api_key">API key</label>
        <input type="password" name="textlocal_api_key" id="textlocal_api_key" placeholder="API key" class="form-control" autocomplete="new-password" @permission('admin.settings.integration.edit')@else readonly @endpermission>
        <span class="input-group-btn">
            <button type="button" class="btn btn-default btn-flat eto-pass-view">
                <i class="fa fa-eye"></i>
            </button>
        </span>
    </div>
    <div class="form-group field-textlocal_test_mode field-size-fw hide_advanced">
        <label for="textlocal_test_mode" class="checkbox-inline">
            <input type="checkbox" name="textlocal_test_mode" id="textlocal_test_mode" value="1" @permission('admin.settings.integration.edit')@else readonly @endpermission>Enable test mode <span style="color:#888;">(keep this option off when in LIVE mode)</span>
        </label>
    </div>
</div>
<div class="config-sms_service_type-smsgateway" style="display:none;">
    <div style="margin-bottom:15px; margin-top:0px;">
        Check how to integrate <a href="{{ config('app.docs_url') }}/getting-started/sms-gateway-integration/" target="_blank">guide</a>
    </div>
    <div class="form-group field-smsgateway_api_key field-size-md">
        <label for="smsgateway_api_key">API Token</label>
        <input type="text" name="smsgateway_api_key" id="smsgateway_api_key" placeholder="API Token" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>
    <div class="form-group field-smsgateway_device_id field-size-md">
        <label for="smsgateway_device_id">Device ID</label>
        <input type="text" name="smsgateway_device_id" id="smsgateway_device_id" placeholder="Device ID" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>
</div>


<div style="margin-bottom:10px; margin-top:40px;">
    <b>Loqate</b> - Address Lookup service <a href="https://www.loqate.com" target="_blank">Read more</a>
</div>
<div class="form-group field-pcapredict_enabled field-size-fw">
    <label for="pcapredict_enabled" class="checkbox-inline">
        <input type="checkbox" name="pcapredict_enabled" id="pcapredict_enabled" value="1" @permission('admin.settings.integration.edit')@else readonly @endpermission>Activate this service
    </label>
</div>
<div class="form-group input-group field-pcapredict_api_key field-size-md">
    <label for="pcapredict_api_key">API key</label>
    <input type="password" name="pcapredict_api_key" id="pcapredict_api_key" placeholder="API key" class="form-control" autocomplete="new-password" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    <span class="input-group-btn">
        <button type="button" class="btn btn-default btn-flat eto-pass-view">
            <i class="fa fa-eye"></i>
        </button>
    </span>
</div>


<div style="margin-top:40px;" class="@if (!config('eto.allow_flightstats')) hide_advanced @endif">
    <div style="margin-bottom:10px;">
        <b>FlightStats</b> - Flight details service <a href="https://docs.easytaxioffice.com/getting-started/flightstats-integration/" target="_blank">Read more</a>
    </div>
    <div class="form-group field-flightstats_enabled field-size-fw">
        <label for="flightstats_enabled" class="checkbox-inline">
            <input type="checkbox" name="flightstats_enabled" id="flightstats_enabled" value="1" @permission('admin.settings.integration.edit')@else readonly @endpermission>Activate this service
        </label>
    </div>
    <div class="form-group field-flightstats_app_id field-size-md">
        <label for="flightstats_app_id">App ID</label>
        <input type="text" name="flightstats_app_id" id="flightstats_app_id" placeholder="App ID" class="form-control" @permission('admin.settings.integration.edit')@else readonly @endpermission>
    </div>
    <div class="form-group input-group field-flightstats_app_key field-size-md">
        <label for="flightstats_app_key">App key</label>
        <input type="password" name="flightstats_app_key" id="flightstats_app_key" placeholder="App key" class="form-control" autocomplete="new-password" @permission('admin.settings.integration.edit')@else readonly @endpermission>
        <span class="input-group-btn">
            <button type="button" class="btn btn-default btn-flat eto-pass-view">
                <i class="fa fa-eye"></i>
            </button>
        </span>
    </div>

    <a href="#" class="etoSettingsUpdateAirlines" style="display:inline-block; padding-right:10px; margin-right:5px; border-right:1px #c9c9c9 solid;" title="All Airlines: {{ \App\Models\FlightAirline::count() }}">Update Airlines List</a>
    <a href="#" class="etoSettingsUpdateAirports" style="display:inline-block;" title="All Airports: {{ \App\Models\FlightAirport::count() }}">Update Airports List</a>
</div>
<script>
$(document).ready(function(){
    $('.etoSettingsUpdateAirlines').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            headers : {
              'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/updateAirlines',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
              if (response.status == true) {
                  alert('Airlines successfully updated.');
              }
              else {
                  alert('Airlines update failed: '+ response.error);
              }
            },
            error: function(response) {
                alert('Airlines update error.');
            },
        });
    });

    $('.etoSettingsUpdateAirports').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            headers : {
              'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/updateAirports',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
              if (response.status == true) {
                  alert('Airports successfully updated.');
              }
              else {
                  alert('Airports update failed: '+ response.error);
              }
            },
            error: function(response) {
                alert('Airports update error.');
            },
        });
    });
});
</script>
