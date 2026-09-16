<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Installation - EasyTaxiOffice</title>

    <meta name="format-detection" content="telephone=no">
    <meta name="robots" content="noindex, nofollow" />

    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','font-awesome/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','ionicons/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('css','AdminLTE.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','jquery-webui-popover/jquery.webui-popover.css') }}">
    <style>
    .control-label {
        text-align: left !important;
        font-weight: normal;
    }
    </style>
</head>
<body class="skin-blue layout-top-nav">
<div class="content-wrapper">
    <section class="content">
        <section class="content-header">
            <a href="https://easytaxioffice.co.uk" style="display:inline-block;"><img src="{{ asset_url('images','eto-logo.png') }}?_dc={{ config('app.timestamp') }}" alt="EasyTaxiOffice" id="logo"></a>
            <a class="btn btn-default pull-right" href="{{ config('app.docs_url') }}" target="_blank">Documentation</a>
        </section>

        <section class="content">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Installation</h3>
                </div>
                <form method="post" class="form-horizontal eto-install">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="broadcast_driver" value="log">
                    <input type="hidden" name="cache_driver" value="file">
                    <input type="hidden" name="session_driver" value="file">
                    <input type="hidden" name="queue_driver" value="sync">

                    <div class="box-body">
                        <div class="row clearfix">
                            <div class="col-lg-6" style="margin-bottom:40px;">
                                <h4>{{ trans('installer.defaultConfig') }}</h4>
                                <div class="form-group {{ $errors->has('app_license') ? ' has-error ' : '' }}">
                                    <label for="app_name" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_license_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="app_license" id="app_license" value="{{ isset($license) ? $license : old('app_license') }}" placeholder="{{ trans('installer.form.app_license_placeholder') }}" required/>
                                        @if ($errors->has('app_license'))
                                        <span class="error-block">
                                             <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('app_license') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('app_email') ? ' has-error ' : '' }}">
                                    <label for="app_name" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_email_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="app_email" id="app_email" value="{{ old('app_email') }}" placeholder="{{ trans('installer.form.app_email_placeholder') }}" required/>
                                        @if ($errors->has('app_email'))
                                        <span class="error-block">
                                             <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('app_email') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('app_email') ? ' has-error ' : '' }}">
                                    <label for="app_name" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_password_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="app_password" id="app_password" value="{{ old('app_password') }}" autocomplete="new-password" placeholder="{{ trans('installer.form.app_password_placeholder') }}" required/>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-flat eto-pass-view">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </span>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-flat eto-pass-generate">Generate</button>
                                            </span>
                                        </div>

                                        {{--<input type="password" class="form-control" name="app_password" id="app_password" value="{{ old('app_password') }}" placeholder="{{ trans('installer.form.app_password_placeholder') }}" required/>--}}
                                        @if ($errors->has('app_password'))
                                        <span class="error-block">
                                             <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('app_password') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('app_name') ? ' has-error ' : '' }}">
                                    <label for="app_name" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_name_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="app_name" id="app_name" value="{{ old('app_name') }}" placeholder="{{ trans('installer.form.app_name_placeholder') }}" required/>
                                        @if ($errors->has('app_name'))
                                        <span class="error-block">
                                             <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('app_name') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($isSuperadmin === true)
                                <div class="form-group {{ $errors->has('app_url') ? ' has-error ' : '' }}">
                                    <label for="app_url" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_url_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        {{--<input type="url" class="form-control" name="app_url" id="app_url" value="{{ old()->getSchemeAndHttpHost() }}" placeholder="{{ trans('installer.form.app_url_placeholder') }}" required/>--}}
                                        <input type="url" class="form-control" name="app_url" id="app_url" value="{{ url('/') }}/" placeholder="{{ trans('installer.form.app_url_placeholder') }}" required/>
                                        @if ($errors->has('app_url'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('app_url') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('environment') ? ' has-error ' : '' }}">
                                    <label for="environment" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_environment_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                    <select name="environment" class="form-control" id="environment" onchange='checkEnvironment(this.value);'>
                                        <option value="local" selected>{{ trans('installer.form.app_environment_label_local') }}</option>
                                        <option value="development">{{ trans('installer.form.app_environment_label_developement') }}</option>
                                        <option value="qa">{{ trans('installer.form.app_environment_label_qa') }}</option>
                                        <option value="production">{{ trans('installer.form.app_environment_label_production') }}</option>
                                        <option value="other">{{ trans('installer.form.app_environment_label_other') }}</option>
                                    </select>
                                    <div id="environment_text_input" style="display: none;">
                                        <input type="text" name="environment_custom" id="environment_custom" placeholder="{{ trans('installer.form.app_environment_placeholder_other') }}"/>
                                    </div>
                                    @if ($errors->has('app_name'))
                                    <span class="error-block">
                                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                        {{ $errors->first('app_name') }}
                                    </span>
                                    @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('app_debug') ? ' has-error ' : '' }}">
                                    <label for="app_debug" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_debug_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <label for="app_debug_true">
                                            <input type="radio" class="form-control" name="app_debug" id="app_debug_true" value="true" />
                                            {{ trans('installer.form.app_debug_label_true') }}
                                        </label>
                                        <label for="app_debug_false">
                                            <input type="radio" class="form-control" name="app_debug" id="app_debug_false" value="false" checked/>
                                            {{ trans('installer.form.app_debug_label_false') }}
                                        </label>
                                        @if ($errors->has('app_debug'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('app_debug') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('app_log_level') ? ' has-error ' : '' }}">
                                    <label for="app_log_level" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_log_level_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                    <select name="app_log_level" class="form-control" id="app_log_level">
                                        <option value="debug" selected>{{ trans('installer.form.app_log_level_label_debug') }}</option>
                                        <option value="info">{{ trans('installer.form.app_log_level_label_info') }}</option>
                                        <option value="notice">{{ trans('installer.form.app_log_level_label_notice') }}</option>
                                        <option value="warning">{{ trans('installer.form.app_log_level_label_warning') }}</option>
                                        <option value="error">{{ trans('installer.form.app_log_level_label_error') }}</option>
                                        <option value="critical">{{ trans('installer.form.app_log_level_label_critical') }}</option>
                                        <option value="alert">{{ trans('installer.form.app_log_level_label_alert') }}</option>
                                        <option value="emergency">{{ trans('installer.form.app_log_level_label_emergency') }}</option>
                                    </select>
                                    @if ($errors->has('app_log_level'))
                                    <span class="error-block">
                                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                        {{ $errors->first('app_log_level') }}
                                    </span>
                                    @endif
                                    </div>
                                </div>
                                @else
                                    <input type="hidden" name="environment" value="production">
                                    <input type="hidden" name="app_debug" value="false" />
                                    <input type="hidden" name="app_log_level" value="debug" />
                                    <input type="hidden" name="app_url" value="{{ url('/') }}/" />
                                @endif

                                <div class="form-group {{ $errors->has('app_url_schema') ? ' has-error ' : '' }}">
                                    <label for="app_url_schema" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_url_schema') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <select name="app_url_schema" class="form-control eto-url-schema">
                                            <option value="1" @if (!empty(old('app_url_schema')) && old('app_url_schema') == 'sendmail' ) selected @elseif (empty(old('app_url_schema'))) selected  @endif>
                                                https
                                            </option>
                                            <option value="0" @if (!empty(old('app_url_schema')) && old('app_url_schema') == 'smtp' ) selected @endif>
                                                http
                                            </option>
                                        </select>
                                        @if ($errors->has('app_url_schema'))
                                            <span class="error-block">
                                                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                                {{ $errors->first('app_url_schema') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{------------------------------------------------------------}}
                            <div class="col-lg-6" style="margin-bottom:40px;">
                                <h4>{{ trans('installer.databaseConfig') }}</h4>
                                @if ($isSuperadmin === true)
                                    <div class="form-group {{ $errors->has('database_connection') ? ' has-error ' : '' }}">
                                        <label for="database_connection" class="col-sm-4 control-label">
                                            {{ trans('installer.form.db_connection_label') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <select name="database_connection" class="form-control" id="database_connection">
                                                <option value="mysql" selected>{{ trans('installer.form.db_connection_label_mysql') }}</option>
                                                <option value="sqlite">{{ trans('installer.form.db_connection_label_sqlite') }}</option>
                                                <option value="pgsql">{{ trans('installer.form.db_connection_label_pgsql') }}</option>
                                                <option value="sqlsrv">{{ trans('installer.form.db_connection_label_sqlsrv') }}</option>
                                            </select>
                                            @if ($errors->has('database_connection'))
                                            <span class="error-block">
                                                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                                {{ $errors->first('database_connection') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="database_connection" value="mysql">
                                @endif

                                <div class="eto-connection form-group {{ $errors->has('database_hostname') ? ' has-error ' : '' }}">
                                    <label for="database_hostname" class="col-sm-4 control-label">
                                        {{ trans('installer.form.db_host_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="database_hostname" id="database_hostname" @if (!empty(old('database_hostname')) ) value="{{ old('database_hostname') }}" @else value="127.0.0.1" @endif placeholder="{{ trans('installer.form.db_host_placeholder') }}" required/>
                                        @if ($errors->has('database_hostname'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('database_hostname') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="eto-connection form-group {{ $errors->has('database_port') ? ' has-error ' : '' }}">
                                    <label for="database_port" class="col-sm-4 control-label">
                                        {{ trans('installer.form.db_port_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control" name="database_port" id="database_port"  @if (!empty(old('database_port')) ) value="{{ old('database_port') }}" @else value="3306" @endif placeholder="{{ trans('installer.form.db_port_placeholder') }}" required/>
                                        @if ($errors->has('database_port'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('database_port') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="eto-connection form-group {{ $errors->has('database_name') ? ' has-error ' : '' }}">
                                    <label for="database_name" class="col-sm-4 control-label">
                                        {{ trans('installer.form.db_name_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="database_name" id="database_name" value="{{ old('database_name') }}" placeholder="{{ trans('installer.form.db_name_placeholder') }}" required/>
                                        @if ($errors->has('database_name'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('database_name') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="eto-connection form-group {{ $errors->has('database_prefix') ? ' has-error ' : '' }}">
                                    <label for="database_name" class="col-sm-4 control-label">
                                        {{ trans('installer.form.db_prefix_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="database_prefix" id="database_prefix" @if (!empty(old('database_prefix')) ) value="{{ old('database_prefix') }}" @else value="eto_" @endif placeholder="{{ trans('installer.form.db_prefix_placeholder') }} : eto_" required/>
                                        @if ($errors->has('database_prefix'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('database_prefix') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="eto-connection form-group {{ $errors->has('database_username') ? ' has-error ' : '' }}">
                                    <label for="database_username" class="col-sm-4 control-label">
                                        {{ trans('installer.form.db_username_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="database_username" id="database_username" value="{{ old('database_username') }}" placeholder="{{ trans('installer.form.db_username_placeholder') }}" required/>
                                        @if ($errors->has('database_username'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('database_username') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="eto-connection form-group {{ $errors->has('database_password') ? ' has-error ' : '' }}">
                                    <label for="database_password" class="col-sm-4 control-label">
                                        {{ trans('installer.form.db_password_label') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="database_password" id="database_password" value="{{ old('database_password') }}" autocomplete="new-password" placeholder="{{ trans('installer.form.db_password_placeholder') }}" />
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default btn-flat eto-pass-view">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="eto-connection form-group {{ $errors->has('database_password') ? ' has-error ' : '' }}">
                                    <div class="col-sm-4">
                                    </div>
                                    <div class="col-sm-8">
                                        <button type="button" class="btn btn-default pull-right eto-check-db-connection">
                                            {{ trans('installer.form.buttons.check_connection') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{------------------------------------------------------------}}
                        <div class="row clearfix">
                            <div class="col-lg-6">
                                <div class="clearfix">
                                    <h4 style="float:left;">{{ trans('installer.emailConfig') }}</h4>
                                    <a style="float:left; margin: 8px 0 0 5px; font-size: 16px;" href="{{ config('app.docs_url') }}/general/email-setup/" target="_blank" title="{{ trans('installer.form.app_tabs.more_info') }}">
                                        <i class="fa fa-info-circle fa-fw" aria-hidden="true"></i>
                                        <span class="sr-only">{{ trans('installer.form.app_tabs.more_info') }}</span>
                                    </a>
                                </div>
                                <div class="form-group {{ $errors->has('mail_driver') ? ' has-error ' : '' }}">
                                    <label for="mail_driver" class="col-sm-4 control-label">
                                        {{ trans('installer.form.app_tabs.mail_connection_type') }}
                                    </label>
                                    <div class="col-sm-8">
                                        <select name="mail_driver" class="form-control eto-mail-driver">
                                            <option value="sendmail" @if (!empty(old('mail_driver')) && old('mail_driver') == 'sendmail' ) selected @elseif (empty(old('mail_driver'))) selected  @endif>
                                                {{ trans('installer.form.app_tabs.mail_sendmail') }}
                                            </option>
                                            <option value="smtp" @if (!empty(old('mail_driver')) && old('mail_driver') == 'smtp' ) selected @endif>
                                                {{ trans('installer.form.app_tabs.mail_smtp') }}
                                            </option>
                                        </select>
                                        @if ($errors->has('mail_driver'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('mail_driver') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="config-mail-sendmail" style="display:none;">
                                    <div class="form-group {{ $errors->has('mail_sendmail') ? ' has-error ' : '' }}">
                                        <label for="mail_sendmail" class="col-sm-4 control-label">
                                            {{ trans('installer.form.app_tabs.mail_sendmail_path') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="mail_sendmail" placeholder="{{ config('mail.sendmail') }}" class="form-control" autocomplete="false" value="{{ old('mail_sendmail') ?: '' }}">
                                        </div>
                                        @if ($errors->has('mail_sendmail'))
                                        <span class="error-block">
                                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                            {{ $errors->first('mail_sendmail') }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="config-mail-smtp" style="display:none;">
                                    <div class="form-group {{ $errors->has('mail_host') ? ' has-error ' : '' }}">
                                        <label for="mail_host" class="col-sm-4 control-label">
                                            {{ trans('installer.form.app_tabs.mail_host_label') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="mail_host" id="mail_host" value="{{ old('mail_host') }}" placeholder="smtp.mailtrap.io" autocomplete="false"/>
                                            @if ($errors->has('mail_host'))
                                            <span class="error-block">
                                                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                                {{ $errors->first('mail_host') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('mail_port') ? ' has-error ' : '' }}">
                                        <label for="mail_port" class="col-sm-4 control-label">
                                            {{ trans('installer.form.app_tabs.mail_port_label') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="number" class="form-control" name="mail_port" id="mail_port" value="{{ old('mail_port') }}" placeholder="25"  autocomplete="false"/>
                                            @if ($errors->has('mail_port'))
                                            <span class="error-block">
                                                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                                {{ $errors->first('mail_port') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('mail_username') ? ' has-error ' : '' }}">
                                        <label for="mail_username" class="col-sm-4 control-label">
                                            {{ trans('installer.form.app_tabs.mail_username_label') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="mail_username" id="mail_username" value="{{ old('mail_username') }}" placeholder="{{ trans('installer.form.app_tabs.mail_username_placeholder') }}" autocomplete="false"/>
                                            @if ($errors->has('mail_username'))
                                            <span class="error-block">
                                                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                                {{ $errors->first('mail_username') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('mail_password') ? ' has-error ' : '' }}">
                                        <label for="mail_password" class="col-sm-4 control-label">
                                            {{ trans('installer.form.app_tabs.mail_password_label') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="mail_password" id="mail_password" value="{{ old('mail_password') }}" autocomplete="new-password" placeholder="{{ trans('installer.form.app_tabs.mail_password_placeholder') }}" autocomplete="false"/>
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default btn-flat eto-pass-view">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('mail_encryption') ? ' has-error ' : '' }}">
                                        <label for="mail_encryption" class="col-sm-4 control-label">
                                            {{ trans('installer.form.app_tabs.mail_encryption_label') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="mail_encryption" id="mail_encryption">
                                                <option value=""  @if (empty(old('mail_encryption'))) selected  @endif>{{ trans('installer.form.app_tabs.mail_encryption_placeholder') }}</option>
                                                <option value="tls" @if (!empty(old('mail_encryption')) && old('mail_encryption') == 'tls' ) selected @endif>TLS</option>
                                                <option value="ssl" @if (!empty(old('mail_encryption')) && old('mail_encryption') == 'ssl' ) selected @endif>SSL</option>
                                            </select>
                                            @if ($errors->has('mail_encryption'))
                                            <span class="error-block">
                                                <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                                {{ $errors->first('mail_encryption') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="pull-left">
                            {{--<button type="reset" class="btn btn-default">Reset</button>--}}
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="autologin" value="1" @if (empty(old('autologin')) || old('autologin') == '1' ) checked @endif>  {{ trans('installer.form.autologin') }}
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="send_welcome_mail" value="1" @if (empty(old('autologin')) || old('autologin') == '1' ) checked @endif>  {{ trans('installer.form.send_welcome_mail') }}
                                </label>
                            </div>

                            <a href="#" onclick="$('.check-requirements-box').toggle(); return false;" class="btn btn-default" style="margin-top:20px;">Check Requirements</a>
                            <div class="check-requirements-box" style="display:none;">
                                @php
                                function debugSetLine($name = '', $value = '') {
                                    if ($name == 'PHP version') {
                                        $phpVersion = $value;
                                        if (version_compare($phpVersion, '7.1', '<') || version_compare($phpVersion, '7.4', '>=')) {
                                            $value = '<span style="color:red;">'. $phpVersion .'</span><span style="color:#b2b2b2; margin-left:5px;">(Required 7.1, 7.2 or 7.3)</span>';
                                        } else {
                                            $value = '<span style="color:green;">'. $phpVersion .'</span>';
                                        }
                                    }
                                    echo '<tr class="eto-debug-line">
                                      <td class="eto-debug-name" style="min-width:130px;">'. $name .':</td>
                                      <td class="eto-debug-value">'. $value .'</td>
                                    </tr>';
                                }
                                function debugIsEnabled($status) {
                                    return $status ? '<span style="color:green;">ON</span>' : '<span style="color:red;">OFF</span>';
                                }
                                @endphp
                                <table class="eto-debug-table" style="margin-top:20px;">
                                    <tbody>
                                    {!! debugSetLine('PHP version', phpversion()) !!}
                                    {!! debugSetLine('fileinfo', debugIsEnabled(extension_loaded('fileinfo'))) !!}
                                    {!! debugSetLine('Openssl', debugIsEnabled(extension_loaded('openssl'))) !!}
                                    {!! debugSetLine('Pdo', debugIsEnabled(extension_loaded('pdo'))) !!}
                                    {!! debugSetLine('Mbstring', debugIsEnabled(extension_loaded('mbstring'))) !!}
                                    {!! debugSetLine('Tokenizer', debugIsEnabled(extension_loaded('tokenizer'))) !!}
                                    {!! debugSetLine('JSON', debugIsEnabled(extension_loaded('JSON'))) !!}
                                    {!! debugSetLine('cURL', debugIsEnabled(extension_loaded('cURL'))) !!}
                                    {!! debugSetLine('Zip', debugIsEnabled(extension_loaded('Zip'))) !!}
                                    {!! debugSetLine('ZipArchive', debugIsEnabled(class_exists('ZipArchive'))) !!}
                                    {!! debugSetLine('allow_url_fopen', debugIsEnabled(ini_get('allow_url_fopen'))) !!}
                                    {!! debugSetLine('file_get_contents', debugIsEnabled(function_exists('file_get_contents'))) !!}
                                    {!! debugSetLine('curl', debugIsEnabled(function_exists('curl_version'))) !!}
                                    {!! debugSetLine('mail', debugIsEnabled(function_exists('mail'))) !!}
                                    {!! debugSetLine('gethostbyname', debugIsEnabled(function_exists('gethostbyname'))) !!}
                                    {!! debugSetLine('mysqli_connect', debugIsEnabled(function_exists('mysqli_connect'))) !!}
                                    {!! debugSetLine('escapeshellarg', debugIsEnabled(function_exists('escapeshellarg'))) !!}
                                    {!! debugSetLine('escapeshellcmd', debugIsEnabled(function_exists('escapeshellcmd'))) !!}
                                    {!! debugSetLine('proc_open', debugIsEnabled(function_exists('proc_open'))) !!}
                                    {!! debugSetLine('proc_close', debugIsEnabled(function_exists('proc_close'))) !!}
                                    {!! debugSetLine('getservbyport', debugIsEnabled(function_exists('getservbyport'))) !!}
                                    {!! debugSetLine('My IP', $_SERVER['REMOTE_ADDR']) !!}
                                    {!! debugSetLine('Server IP', !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '') !!}
                                    {{-- {!! debugSetLine('disable_functions', '<span style="color:gray;">'. str_replace(',', ', ', ini_get('disable_functions')) .'</span>') !!} --}}
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <button type="submit" class="btn btn-info pull-right">
                            {{ trans('installer.form.buttons.install') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </section>
</div>

<script src="{{ asset_url('plugins','jquery/jquery.min.js') }}"></script>
<script src="{{ asset_url('plugins','jquery/jquery-migrate.min.js') }}"></script>
<script src="{{ asset_url('plugins','bootstrap/bootstrap.min.js') }}"></script>

 HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries 
 WARNING: Respond.js doesn't work if you view the page via file:// 
[if lt IE 9]>
<script src="{{ asset_url('plugins','html5shiv/html5shiv.min.js') }}"></script>
<script src="{{ asset_url('plugins','respond/respond.min.js') }}"></script>
<![endif]-->

<script src="{{ asset_url('plugins','jquery-loading-overlay/loadingoverlay.min.js') }}"></script>
<script src="{{ asset_url('plugins','sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset_url('js','eto/eto.js') }}?_dc={{ config('app.timestamp') }}"></script>
<script src="{{ asset_url('plugins','jquery-cookie/jquery.cookie.js') }}?_dc={{ config('app.timestamp') }}"></script>
<script src="{{ asset_url('plugins','jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset_url('plugins','fastclick/fastclick.min.js') }}"></script>
<script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
<script src="{{ asset_url('js','app.js') }}?_dc={{ config('app.timestamp') }}"></script>
<script src="{{ asset_url('js','eto/eto.js') }}?_dc={{ config('app.timestamp') }}"></script>

<script>
$(function() {
    $.LoadingOverlaySetup({
        image: "",
        fontawesome: "fa fa-spinner fa-spin",
        maxSize: "80px",
        minSize: "20px",
        resizeInterval: 0,
        size: "50%",
        fade: [0,200]
    });

    ETO.setConfig({!! json_encode(\App\Helpers\SettingsHelper::getJsConfig()) !!});

    var responseConnnection = false,
        submited = false;

    function checkConnection(infoDB) {
        if(submited === false && $('[name="database_hostname"]').val().length > 0 && $('[name="database_port"]').val().length > 0 && $('[name="database_name"]').val().length > 0 && $('[name="database_username"]').val().length > 0) {
            var timerInterval;

            if(infoDB !== true) {
                $.LoadingOverlay('show');
            }

            ETO.ajax('install/check-connection', {
                data: {
                    _token: '{{ csrf_token() }}',
                    database_connection: $('[name="database_connection"]').val(),
                    database_hostname: $('[name="database_hostname"]').val(),
                    database_port: $('[name="database_port"]').val(),
                    database_name: $('[name="database_name"]').val(),
                    database_prefix: $('[name="database_prefix"]').val(),
                    database_username: $('[name="database_username"]').val(),
                    database_password: $('[name="database_password"]').val(),
                },
                async: false,
                success: function (resp) {
                    if(infoDB === true) {
                        responseConnnection = resp.status;
                    }
                    else {
                        if (resp.status === true) {
                            Swal.fire({
                                html: '{{ trans('installer.connectionOk') }}',
                                timer: 5000,
                                onClose: function () {
                                    clearInterval(timerInterval);
                                }
                            })
                        } else {
                            if(resp.message != '') {
                                Swal.fire({
                                    type: 'warning',
                                    html: resp.message,
                                    onClose: function () {
                                        clearInterval(timerInterval);
                                    }
                                })
                            }
                            else {
                                Swal.fire({
                                    type: 'warning',
                                    html: '{{ trans('installer.connectionFail') }}',
                                    timer: 5000,
                                    onClose: function () {
                                        clearInterval(timerInterval);
                                    }
                                })
                            }
                        }
                    }
                    if(infoDB !== true) {
                        $.LoadingOverlay('hide');
                    }
                },
                error: function () {
                    if(infoDB !== true) {
                        $.LoadingOverlay('hide');
                    }
                    Swal.fire({
                        type: 'warning',
                        html: '{{ trans('installer.connectionFail') }}',
                        timer: 5000,
                        onClose: function () {
                            clearInterval(timerInterval);
                        }
                    })
                }
            });

            if(infoDB === true) {
                return responseConnnection;
            }
        }
        else {
            if(infoDB === true) {
                return false;
            }
            else {
                Swal.fire({
                    type: 'warning',
                    html: '{{ trans('installer.connectionFail') }}',
                    // timer: 10000,
                    onClose: function () {
                        clearInterval(timerInterval);
                    }
                });
            }
        }
    }

    function viewErrors(errors) {
        var timerInterval;
        var message = ['{{ trans('installer.bad_data') }}'];

        if (typeof errors != 'undefined') {
            $.each(errors, function (k,v) {
                $.each(v, function (k1,v1) {
                    message.push(v1);
                })
            })
        }

        message = message.join('<br>');

        Swal.fire({
            type: 'warning',
            html: message,
            timer: 10000,
            onClose: function() {
                clearInterval(timerInterval);
            }
        });
    }

    $('body').on('click', '.eto-check-db-connection', function(e) {
        checkConnection();
    })
    .on('click', '.eto-pass-generate', function(e) {
        $(this).closest('.input-group').find('input').val(generatePassword());
    })
    .on('click', '.eto-pass-view', function(e) {
        $(this).closest('.input-group').find('input').attr('type', 'text');
        $(this).removeClass('eto-pass-view').addClass('eto-pass-hide');
        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
    })
    .on('click', '.eto-pass-hide', function(e) {
        $(this).closest('.input-group').find('input').attr('type', 'password');
        $(this).removeClass('eto-pass-hide').addClass('eto-pass-view');
        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
    })
    .on('submit', '.eto-install', function(e) {
        if(submited === false) {
            e.preventDefault();
            $.LoadingOverlay("show", {
                image       : "",
                fontawesome : "fa fa-cog fa-spin",
                text        : "Installing, please wait"
            });

            var form = $(this);

            setTimeout(function() {
                var infoDB = checkConnection(true);

                if (infoDB === true) {
                    submited = true;

                    var data = form.serialize(),
                        autologin = $('[name="autologin"]').attr('checked') == 'checked';

                    $.LoadingOverlay("text", "Installing, please wait");

                    ETO.ajax('install/setConfigFile', {
                        data: data,
                        async: false,
                        success: function(response) {
                            if (parseBoolean(response.status) === true) {
                                $.LoadingOverlay("text", "Loading data into the Database, please wait");

                                ETO.ajax('install/setDataToDB', {
                                    data: data,
                                    async: false,
                                    success: function (response) {
                                        if (parseBoolean(response.status) === true) {
                                            $.LoadingOverlay("text", "Completing the installation process, please wait");

                                            ETO.ajax('install/final', {
                                                data: data,
                                                async: false,
                                                success: function (response) {
                                                    if (response.status == 'OK') {
                                                        if (autologin === true) {
                                                            ETO.ajax('install/loginAfterInstall', {
                                                                data: data,
                                                                async: false,
                                                                success: function (response) {
                                                                    if (parseBoolean(response.status) === true) {
                                                                        window.location = ETO.config.appPath + '/admin';
                                                                    } else {
                                                                        window.location = ETO.config.appPath + '/login';
                                                                    }
                                                                },
                                                                error: function () {
                                                                    $.LoadingOverlay('hide');
                                                                    submited = false;
                                                                }
                                                            });
                                                        } else {
                                                            window.location = ETO.config.appPath + '/login';
                                                        }
                                                    } else {
                                                        viewErrors(response.errors);
                                                        submited = false;
                                                        $.LoadingOverlay('hide');
                                                    }
                                                },
                                                error: function () {
                                                    $.LoadingOverlay('hide');
                                                    submited = false;
                                                }
                                            });
                                        }
                                        else {
                                            viewErrors(response.errors);
                                            submited = false;
                                        }
                                    },
                                    error: function () {
                                        $.LoadingOverlay('hide');
                                        submited = false;
                                    }
                                });
                            }
                            else {
                                viewErrors(response.errors);
                                submited = false;
                            }
                        },
                        error: function() {
                            $.LoadingOverlay('hide');
                            submited = false;
                        },
                        complete: function() {
                            $.LoadingOverlay('hide');
                        }
                    });
                } else {
                    var timerInterval;

                    Swal.fire({
                        type: 'warning',
                        html: '{{ trans('installer.connectionFail') }}',
                        timer: 5000,
                        onClose: function () {
                            clearInterval(timerInterval);
                        }
                    });
                    $.LoadingOverlay('hide');
                }
            }, 300);
        }
    });

    // Mail
    $('.eto-mail-driver').change(function() {
        // SMTP
        if( $(this).val() == 'smtp' ) {
            $('.config-mail-smtp').show();
        }
        else {
            $('.config-mail-smtp').hide();
        }

        // Sendmail
        if( $(this).val() == 'sendmail' ) {
            $('.config-mail-sendmail').show();
        }
        else {
            $('.config-mail-sendmail').hide();
        }
    }).change();
});
</script>
</body>
</html>
