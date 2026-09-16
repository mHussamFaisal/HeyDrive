@extends('admin.index')

@section('title', trans('modules.page_title'))
@section('subtitle', '<i class="fa fa-arrow-circle-up"></i> '. trans('modules.page_title') )

@section('subcontent')
    <div id="upgrade">
        @if ( $errors->any() )
            <div class="alert alert-danger alert-dismissible callout callout-danger">
                <i class="icon fa fa-exclamation-triangle" style="margin-top: 0; font-size: 30px;"></i>
                @foreach($errors->all() as $error)
                    <p class="msg" style="padding-left: 40px; padding-top: 3px; font-size: 18px;">{!! $error !!}</p>
                @endforeach
            </div>
            <div style="font-size: 200px; color: #f6f6f6; text-align: center;">
                <i class="fa fa-meh-o"></i>
            </div>
        @else
            <div class="clearfix" style="margin-bottom: 10px;">
                <h3 class="section-header pull-left" style="margin: 5px 0;">
                    {{ trans('modules.headers.modules') }}
                </h3>
                {{-- <button class="btn btn-md btn-info pull-right eto-button-change-license-key">
                    {{ trans('modules.button.changeLicenseKey') }}
                </button> --}}
                <button type="button" class="btn btn-md btn-default pull-right eto-button-check" style="text-decoration: none;">
                    <span>{{ trans('modules.button.check') }}</span>
                </button>
            </div>

            @if ($newVersionsIsset)
                <div class="alert alert-info alert-dismissible callout callout-info clearfix">
                    <i class="icon fa fa-arrow-circle-up" style="font-size: 30px;"></i>
                    <p class="msg pull-left" style="padding-left: 40px; padding-top: 6px; font-size: 18px;">
                        {{ trans('modules.message.available') }}
                    </p>

                    @if (!empty($coreUpdate) && !empty($coreUpdate->params) && $coreUpdate->params->noUpdates == false)
                        <button class="btn btn-md btn-warning pull-right eto-button-view-changelog eto-core">{{ trans('modules.button.viewChangelogCore') }}</button>
                    @endif

                    @if (null === $licenseUpdate || strtotime($licenseUpdate) >= strtotime(\Carbon\Carbon::now()->format('Y-m-d')))
                        <button type="button" class="btn btn-md btn-success pull-right eto-button-update-all" style="text-decoration:none; margin-right:5px;">
                            <span>{{ trans('modules.button.upgrade') }}</span>
                        </button>
                    @else
                        <a href="https://easytaxioffice.co.uk/pricing/" target="_blank" class="btn btn-md btn-success pull-right" style="text-decoration:none; margin-right:5px;">
                            <span>{{ trans('modules.button.extend_license') }}</span>
                        </a>
                    @endif
                </div>
            @endif

            @if ($licenseErrorMsg)
                <div class="alert alert-error">{{ $licenseErrorMsg }}</div>
            @else
                @if (null !== $licenseExpire)
                    @php
                    $cTime = time();
                    $lTime = strtotime($licenseExpire);
                    $reminder = config('settings.license_expiry_reminder') * 86400;
                    $className = $lTime - ($reminder/2) < $cTime ? 'text-red' : ($lTime - $reminder < $cTime ? 'text-yellow' : '');
                    @endphp
                    <div class="subscription-message subscription-message-expired">
                        {!! trans('modules.date.subscriptionExpired', ['date'=>'<span class="'. $className .'" style="font-weight:bold;">'. format_date_time($licenseExpire, 'date') .'</span>']) !!}
                    </div>
                @endif
                @if (null !== $licenseSupport)
                    @php
                    $cTime = time();
                    $lTime = strtotime($licenseSupport);
                    $reminder = config('settings.license_support_reminder') * 86400;
                    $className = $lTime - ($reminder/2) < $cTime ? 'text-red' : ($lTime - $reminder < $cTime ? 'text-yellow' : '');
                    @endphp
                    <div class="subscription-message subscription-message-support">
                        {!! trans('modules.date.subscriptionSupport', ['date'=>'<span class="'. $className .'" style="font-weight:bold;">'. format_date_time($licenseSupport, 'date') .'</span>']) !!}
                    </div>
                @endif
                @if (null !== $licenseUpdate)
                    @php
                    $cTime = time();
                    $lTime = strtotime($licenseUpdate);
                    $reminder = config('settings.license_update_reminder') * 86400;
                    $className = $lTime - ($reminder/2) < $cTime ? 'text-red' : ($lTime - $reminder < $cTime ? 'text-yellow' : '');
                    @endphp
                    <div class="subscription-message subscription-message-update">
                        {!! trans('modules.date.subscriptionUpdate', ['date'=>'<span class="'. $className .'" style="font-weight:bold;">'. format_date_time($licenseUpdate, 'date') .'</span>']) !!}
                    </div>
                @endif
            @endif

            <div style="margin-top:10px;">Software version <b>{{ config('app.version') }}</b></div>
            <div style="margin-bottom:10px;">View what has been done in last version <a href="https://docs.easytaxioffice.com/updates/latest-updates/" target="_blank"><i class="fa fa-external-link" style="color: #00c0ef;"></i></a></div>

            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($modules as $id=>$module)
                        <tr class="eto-module-installed" data-eto-id="{{ $module->id }}" data-eto-type="{{ $module->type }}">
                            <td class="module-title column-primary" style="padding: 20px 10px;">
                                <strong class="eto-title">{{ $module->name }}</strong>
                                @if (!empty($module->subscriptions[0]->params->mode))
                                    @if ($module->subscriptions[0]->params->mode == 'trial')
                                        <b class="text-red">{{ trans('modules.trial') }}</b>
                                    @elseif ($module->subscriptions[0]->params->mode == 'free')
                                        <b class="text-green">{{ trans('modules.free') }}</b>
                                    @endif

                                    @if (!empty($module->errors))
                                        <div class="clearfix"></div>
                                        @foreach($module->errors as $key=>$error)
                                        <span class="text-red">{{ trans('modules.message.'.$key) }}</span>
                                        @endforeach
                                    @endif
                                    <div class="row-actions visible" style="margin-top:10px;">
                                        @if ($module->type != 'eto' && empty($module->errors))
                                            <div style="display:none;">
                                                <button class="btn btn-xs btn-default eto-module-status" data-eto-status="{{ $module->subscriptions[0]->status }}">
                                                    {{ $module->subscriptions[0]->status == '1' ? trans('modules.button.disable') : trans('modules.button.enable') }}
                                                </button>
                                                <button class="btn btn-xs btn-danger eto-button-uninstall">{{ trans('modules.button.uninstall_module') }}</button>
                                            </div>

                                            @if ($module->subscriptions[0]->params->mode == 'trial')
                                                <a href="https://easytaxioffice.co.uk/pricing/" target="_blank" class="btn btn-xs btn-success">{{ trans('modules.button.extend_license') }}</a>
                                            @endif
                                        @endif

                                        @if (!empty($module->errors))
                                            <button class="btn btn-xs btn-danger eto-button-uninstall">{{ trans('modules.button.uninstall_module') }}</button>
                                            <a href="https://easytaxioffice.co.uk/pricing/" target="_blank" class="btn btn-xs btn-success">{{ trans('modules.button.extend_license') }}</a>
                                        @elseif (!empty($module->available_version) && $module->available_version != '' && $module->version != $module->available_version)
                                            <button class="btn btn-xs btn-info eto-button-view-changelog">{{ trans('modules.button.viewChangelog') }}</button>
                                            {{--@if ($module->available_version != '')--}}
                                                {{--<a class="btn btn-xs btn-success" href="{{ url('/modules/'.$module->id) }}">{{ trans('modules.button.upgrade') }}</a>--}}
                                            {{--@endif--}}
                                        @endif
                                    </div>
                                @else
                                    <button class="btn btn-xs btn-success eto-button-install" data-eto-id="" data-eto-type="{{ $module->type }}">{{ trans('modules.button.install') }}</button>
                                @endif
                            </td>
                            <td class="column-description desc" style="padding: 20px 10px;">
                                @if (!empty($module->description))
                                    <div class="module-description">{{ $module->description }}</div>
                                @endif
                                <div class="">
                                    @if (!empty($module->version))
                                        {{ trans('modules.current_version') }} <span style="font-weight:bold;">{{ $module->version }}</span>
                                    @endif

                                    @if (!empty($module->available_version) && $module->version != $module->available_version)
                                        | {{ trans('modules.available_version') }} <b>{{ $module->available_version }}</b>
                                        @if ( !empty($module->max_Version) && $module->available_version != $module->max_Version)
                                            <abbr style="color: #ababab" title="Check requirements">{{ $module->maxVersion }}</abbr>
                                        @endif
                                    @endif
                                </div>
                                @if (!empty($module->subscriptions[0]->params->expire_at))
                                    <div class="">{{ trans('modules.date.expiry') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->expire_at, 'date') }}</span></div>
                                @endif
                                @if (!empty($module->subscriptions[0]->params->support_at))
                                    <div class="">{{ trans('modules.date.support') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->support_at, 'date') }}</span></div>
                                @endif
                                @if (!empty($module->subscriptions[0]->params->update_at))
                                    <div class="">{{ trans('modules.date.updates') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->update_at, 'date') }}</span></div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="eto-addons-container">
              <div class="eto-addons-title">Available Add-ons</div>
              <ul class="eto-addons-list">
                <li>Driver App</li>
                <li>Driver App Personalised</li>
                <li>Passenger App Personalised</li>
                <li>Business Profile</li>
                <li>Driver Availability</li>
                <li>Hourly Rate</li>
                <li>Schedule Service</li>
              </ul>
            </div>
        @endif
    </div>
    <div class="modal fade" id="update-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('modules.button.close') }}</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('subfooter')
    <script>
    var updateUrl = '{{ route('modules.update') }}',
        storeUrl = 'https://easytaxioffice.co.uk/pricing/',
        errors = [],
        modules = {!! \GuzzleHttp\json_encode($modules) !!},
        client = {!! \GuzzleHttp\json_encode($client) !!},
        coreUpdate = {!! \GuzzleHttp\json_encode($coreUpdate) !!};
        licenseKey = '{{ $licenseKey }}';

    function disableModule(type, status, button) {
        ETO.ajax('modules/status', {
            data: {
                type: type,
                status: status,
            },
            async: true,
            success: function(update) {
                if (update.status === true) {
                    if (status === 0) {
                        button.attr('data-eto-status', status).html('{{ trans('modules.button.enable') }}');
                    }
                    else {
                        button.attr('data-eto-status', status).html('{{ trans('modules.button.disable') }}');
                    }
                    ETO.swalWithBootstrapButtons({
                        type: 'success',
                        title: 'Module has been updated'
                    });
                }
                else {
                    ETO.swalWithBootstrapButtons({
                        type: 'error',
                        title: 'Module could not be updated'
                    });
                }
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({
                    type: 'error',
                    title: 'An error has occurred during module update'
                });
            }
        });
    }

    function updateAll() {
        if (typeof coreUpdate.versions != 'undefined') {
            if (typeof coreUpdate.maxVersion != 'undefined') {
                setUpdate(coreUpdate.type, coreUpdate.maxVersion, coreUpdate);
            }
        }
        for(var i in modules) {
            if (typeof modules[i].subscriptions != 'undefined' && typeof modules[i].subscriptions[0].params.mode != 'undefined' && null != modules[i].max_version) {
                setUpdate(modules[i].type, modules[i].max_version, modules[i]);
            }
        }

        setTimeout(function() {
            if (errors.length === 0) {
                ETO.swalWithBootstrapButtons({
                    type: 'success',
                    title: 'The system has been successfully updated'
                })
                .then(function (result) {
                    window.location.reload();
                });
            }
            else {
                var errorHtml = '';
                for(var i in errors) {
                    errorHtml += errors[i].module + ' - ' + errors[i].message;
                }
                ETO.swalWithBootstrapButtons({
                    type: 'warning',
                    title: 'The system could not be updated',
                    html: errorHtml,
                })
                .then(function (result) {
                    window.location.reload();
                });
            }
        }, 0);
    }

    function setUpdate(type, maxVersion, moduleData) {
        // console.log({ type: type, maxVersion: maxVersion });

        if(typeof moduleData.errors == 'undefined' || (typeof moduleData.errors != 'undefined' && typeof moduleData.errors.module_update_expired == 'undefined')) {
            var module = moduleData.name;
            $.LoadingOverlay('show');
            setTimeout(function () {
                ETO.ajax(updateUrl, {
                    data: {type: type, maxVersion: maxVersion},
                    async: false,
                    success: function (data) {
                        if (data.status === true) {
                            ETO.ajax('updater.php', {
                                data: {folder: data.folder},
                                async: false,
                                success: function (update) {
                                    if (update.status === true) {
                                        ETO.ajax('modules/migrate', {
                                            data: {type: type},
                                            async: false,
                                            success: function (update) {
                                                if (update.status === false) {
                                                    errors.push({
                                                        module: module,
                                                        message: 'The update could not be completed ' + update.message
                                                    });
                                                }
                                            },
                                            error: function () {
                                                $.LoadingOverlay('hide');
                                                errors.push({
                                                    module: module,
                                                    message: 'The update could not be completed ' + update.message
                                                });
                                            }
                                        });
                                    } else {
                                        errors.push({
                                            module: module,
                                            message: 'The update could not be completed ' + update.message
                                        });
                                    }
                                },
                                error: function (data) {
                                    $.LoadingOverlay('hide');
                                    errors.push({
                                        module: module,
                                        message: 'The update could not be completed ' + data.message
                                    });
                                }
                            });
                        } else {
                            errors.push({
                                module: module,
                                message: 'The update could not be completed ' + data.message
                            });
                        }
                    },
                    error: function (data) {
                        $.LoadingOverlay('hide');
                        errors.push({module: module, message: 'The update could not be completed ' + data.message});
                    },
                    complete: function () {
                        $.LoadingOverlay('hide');
                    }
                });
            }, 0);
        }
    }

    function install(module) {
        var installation_type = typeof module.params.free != 'undefined' && module.params.free === 1
            ? 'free'
            : (typeof module.params.trial != 'undefined' && module.params.trial > 0
                && (typeof module.license == 'undefined'
                    || (typeof module.license.mode != 'undefined' && module.license.mode != 'pro' && module.license.mode != 'free'))
                ? 'trial'
                : 'pro');

        $.LoadingOverlay('show');
        ETO.ajax('modules/install', {
            data: {
                type: module.type,
                installation_type: installation_type
            },
            async: true,
            success: function(instalation) {
                $.LoadingOverlay('hide');
                if (instalation.status === true) {
                    ETO.swalWithBootstrapButtons({type: 'success', title: 'Module has been successfully installed'})
                        .then(function (result) { if (result.value) { window.location.reload(); } })
                }
                else {
                    var title = 'Module could not be installed',
                        message = typeof instalation.message != 'undefined' ? instalation.message : '';

                    ETO.swalWithBootstrapButtons({type: 'error', title: title, html: message});
                }
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({type: 'error', title: 'An error has occurred during module installation'});
            }
        });
    }

    function uninstall(module) {
        var installation_type = typeof module.params.free != 'undefined' && module.params.free === 1
            ? 'free'
            : (typeof module.params.trial != 'undefined' && module.params.trial > 0
            && (typeof module.license == 'undefined'
                || (typeof module.license.mode != 'undefined' && module.license.mode != 'pro' && module.license.mode != 'free'))
                ? 'trial'
                : 'pro');

        ETO.ajax('modules/uninstall', {
            data: {
                key: module.license,
                type: module.type,
                client_id: client.id,
                installation_type: installation_type
            },
            async: true,
            success: function(instalation) {
                if (instalation.status === true) {
                    ETO.swalWithBootstrapButtons({type: 'success', title: 'Module has been successfully uninstalled'})
                        .then(function (result) { if (result.value) { window.location.reload(); } });
                }
                else {
                    ETO.swalWithBootstrapButtons({type: 'error', title: 'Module could not be uninstalled'});
                }
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({type: 'error', title: 'An error has occurred during module uninstallation'});
            }
        });
    }

    function checkUpdates() {
        Swal.fire({
            allowOutsideClick: false,
            title: '{{ trans('modules.message.updatesChecking') }}',
            onBeforeOpen: function() {
                Swal.showLoading();
            },
        });

        ETO.ajax('modules/check', {
            data: {},
            async: true,
            success: function(instalation) {
                if (instalation.status === true) {
                    if (instalation.new_versions === true) {
                        var timerInterval;
                        Swal.fire({
                            title: '{{ trans('modules.message.available') }}',
                            html: '{{ trans('modules.message.reloadStart') }} <strong></strong> {{ trans('modules.message.reloadEnd') }}<br/><br/>',
                            timer: 5000,
                            onBeforeOpen: function() {
                                Swal.showLoading();
                                timerInterval = setInterval(function() {
                                    Swal.getContent().querySelector('strong')
                                        .textContent = (Swal.getTimerLeft() / 1000)
                                        .toFixed(0)
                                }, 100)
                            },
                            onClose: function() {
                                clearInterval(timerInterval)
                            }
                        }).then(function(result) {
                            window.location.reload();
                        });
                    }
                    else {
                        ETO.swalWithBootstrapButtons({
                            allowOutsideClick: false,
                            title: '{{ trans('modules.message.noAvailable') }}'
                        });
                    }
                }
                else {
                    if (typeof instalation.code != 'undefined') {
                        ETO.swalWithBootstrapButtons({type: 'varning', title: 'Response Fail Code '+instalation.code, html: instalation.message});
                    }
                    else {
                        ETO.swalWithBootstrapButtons({type: 'error', title: 'Please try again'});
                    }
                }
                $.LoadingOverlay('hide');
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({type: 'error', title: 'Please try again'});
            },
            complete: function() {
                $.LoadingOverlay('hide');
            }
        });
    }

    function doModal(placementId, heading, formContent, strSubmitFunc, btnText) {
        var buttons = '';
        if (btnText != '' && typeof btnText != 'undefined') {
            buttons += '<span class="btn btn-success eto-close-modal" onclick="'+strSubmitFunc+'">'+btnText+'</span>';
        }
        // buttons += '<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('modules.button.close') }}</button>';

        var html = '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
            '<h4 class="modal-title">'+heading+'</h4>' +
            '</div>' +
            '<div class="modal-body">' + formContent + '</div>' +
            (buttons ? '<div class="modal-footer">'+buttons+'</div>' : '');

        $("#"+placementId+' .modal-content').html(html);
        $("#"+placementId).modal('show');
    }

    function hideModal() { $('.modal.in').modal('hide'); }

    $(document).ready(function(){
        if (ETO.model === false) {
            ETO.init({ config: ['page', 'icons', 'config_site'], lang: ['user'] }, 'update');
        }

        $.LoadingOverlay('show');

        $('body').on('click', '.eto-close-modal', function(e) {
            $(this).closest('#update-modal').modal('hide')
        });

        $('body').on('change', '[name="typeLicense"]', function(e) {
            var val = $('[name="typeLicense"]:checked').val();

            if (val == 'pro') {
                $('.typeLicense').removeClass('hidden');
            }
            else {
                $('.typeLicense').addClass('hidden');
            }
        });

        $('body').on('click', '.eto-button-install', function(e) {
            var type = $(this).closest('tr').attr('data-eto-type'),
                module = [],
                html = '<h4>{{ trans('modules.install_header') }}</h4><input name="license_key" class="form-control" placeholder="{{ trans('modules.install_placeholder') }}">';

            for(var i in modules) { if (modules[i].type == type) { module = modules[i]; break;} }

            if (typeof module.type != 'undefined') {
                if ((module.free === 1 && module.pro === 0) || (typeof module.license != 'undefined' && typeof module.license.diff != 'undefined' && module.license.isExpire === false)) { install( module ); return true; }
                else if (module.pro === 1 && parseInt(module.trial) > 0) {
                    html = '{{ trans('modules.message.info_trial') }}' +
                        '<br>{{ trans('modules.message.installTrial') }}' + module.trial + ' days' +
                        '<br><a href="'+storeUrl+'" target="_blank">{{ trans('modules.action.installTrial') }}</a>';
                }
            }

            ETO.swalWithBootstrapButtons({
                title: module.name,
                showCancelButton: true,
                html: html,
                confirmButtonText: '{{ trans('modules.button.install') }}',
            })
            .then(function (result) {
                if (result.value) {
                    install(module);
                }
            });
        });

        $('body').on('click', '.eto-button-uninstall', function(e) {
            var type = $(this).closest('tr').attr('data-eto-type'),
                module = [];

            for(var i in modules) { if (modules[i].type == type) { module = modules[i]; break;} }

            ETO.swalWithBootstrapButtons({
                title: 'Are you sure?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, uninstall it',
            })
            .then(function (result) { if (result.value) { uninstall(module); } });
        });

        $('body').on('click', '.eto-button-update-all', function(e) {
            var button = $(this),
                conditions = '',
                header = '{{ trans('modules.message.update_conditions') }}',
                btnText = '{{ trans('modules.button.continue') }}',
                updatesCount = 0,
                failsCount = 0;

            if (typeof coreUpdate.versions != 'undefined') {
                if (typeof coreUpdate.params.noUpdates != 'undefined' && coreUpdate.params.noUpdates === false) { updatesCount++; }
                if (typeof coreUpdate.params.conditions != 'undefined' && coreUpdate.params.conditions.length) {
                    conditions +=  coreUpdate.params.conditions + "<br>";
                }
                if (coreUpdate.params.noUpdates === true) {
                    failsCount++;
                    conditions += '<b style="color: darkred">Core update couldn\'t be completed "'+coreUpdate.name+'"</b>';
                }
            }

            for(var i in modules) {
                if (null !== modules[i].available_version) { updatesCount++; }
                if (typeof modules[i].params.conditions != 'undefined' && modules[i].params.conditions.length) {
                    conditions +=  modules[i].params.conditions + "<br>";
                }
                if (modules[i].params.noUpdates === true) {
                    failsCount++;
                    conditions += '<b style="color: darkred">Modul update couldn\'t be completed "'+modules[i].name+'"</b>';
                }
            }

            if (failsCount < updatesCount) {
                conditions += '{!! trans('modules.message.update_conditions_message') !!}';
                conditions.replace(/\n\r/g, '<br />').replace(/\n/g, '<br />').replace(/\r/g, '<br />');
                doModal('update-modal', header, conditions, 'updateAll()', btnText);
            }
            else {
                doModal('update-modal', header, conditions);
            }
        });

        $('body').on('click', '.eto-module-status', function(e) {
            var button = $(this),
                type = button.closest('tr').attr('data-eto-type'),
                status = button.attr('data-eto-status') == '1' ? 0 : 1;

            if (status === 0) {
                ETO.swalWithBootstrapButtons({
                    title: 'Are you sure?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, deactivate it',
                })
                .then(function (result) {
                    if (result.value) {
                        disableModule(type, status, button);
                    }
                });
            }
            else {
                disableModule(type, status, button);
            }
        });

        $('body').on('click', '.eto-button-view-changelog', function(e) {
            var button = $(this),
                isCore = button.hasClass('eto-core'),
                licenseKey = button .closest('tr').attr('data-eto-key'),
                changelog = '',
                moduleTitle = '';

            if (isCore) {
                for (var ii in coreUpdate.versions) {
                    if (coreUpdate.versions[ii].changelog) {
                        changelog += '<div style="margin-bottom:20px;">';
                            changelog += '<h3 style="margin:0 0 5px 0;">v'+ coreUpdate.versions[ii].version + '</h3>';
                            changelog += '<div>'+ coreUpdate.versions[ii].changelog + '</div>';
                        changelog += '</div>';
                    }
                }
                moduleTitle = coreUpdate.name;
            }
            else {
                for (var i in modules) {
                    if (modules[i].license == licenseKey) {
                        for (var ii in modules[i].versions) {
                            if (modules[i].versions[ii].changelog) {
                                changelog += '<div style="margin-bottom:20px;">';
                                    changelog += '<h3 style="margin:0 0 5px 0;">v'+ modules[i].versions[ii].version + '</h3>';
                                    changelog += '<div>'+ modules[i].versions[ii].changelog + '</div>';
                                changelog += '</div>';
                            }
                        }
                        moduleTitle = modules[i].name;
                    }
                }
            }

            changelog.replace(/\n\r/g, '<br />').replace(/\n/g, '<br />').replace(/\r/g, '<br />');

            if (changelog == '') {
                changelog = '{{ trans('modules.changelog_msg') }}';
            }

            doModal('update-modal', '{{ trans('modules.changelog') }} - ' + moduleTitle, changelog);
        });

        $('body').on('click', '.eto-button-check', function(e) {
            checkUpdates();
        });

        $('body').on('click', '.eto-button-change-license-key', function(e) {
            ETO.swalWithBootstrapButtons({
                title: '{{ trans('modules.headers.changeLicense') }}',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                // html: '<input class="license_key" value="'+licenseKey+'">',
                confirmButtonText: '{{ trans('modules.button.change') }}',
            })
            .then(function (result) {
                if (result.value) {
                    install(module);
                }
            });

            Swal.fire({
                title: 'Submit your Github username',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Look up',
                showLoaderOnConfirm: true,
                preConfirm: function(login) {
                    return fetch(`//api.github.com/users/${login}`)
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error(response.statusText);
                            }
                            return response.json();
                        })
                        .catch(function (error) {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                },
                allowOutsideClick: function() { !Swal.isLoading() }
            })
            .then(function(result) {
                if (result.value) {
                    Swal.fire({
                        title: `${result.value.login}'s avatar`,
                        imageUrl: result.value.avatar_url
                    });
                }
            });
        });

        // $('body').on('click', '.eto-button-update', function(e) {
        //     ETO.swalWithBootstrapButtons({
        //         title: 'Conditions',
        //         // type: 'warning',
        //         showCancelButton: true,
        //         html: "<p>"+conditions+"</p>",
        //         confirmButtonText: 'Yes, I agree',
        //     }).then(function(result){
        //         if (maxVersion != lastVersion) {
        //             if (result.value) {
        //                 ETO.swalWithBootstrapButtons({
        //                     title: 'Warning',
        //                     type: 'warning',
        //                     showCancelButton: true,
        //                     html: "<p>The maximum version to which we can carry out the update is "+maxVersion+" due to the server's not fulfilled server requirements, which were listed in Changelog.<br>You can update this update or improve server parameters.</p>",
        //                     confirmButtonText: 'Continue',
        //                 }).then(function (result) {
        //                     if (result.value) { setUpdate(); }
        //                 });
        //             }
        //         }
        //         else{ setUpdate(); }
        //     });
        // });
    });

    $(window).load(function(){
        $.LoadingOverlay('hide');
    });
    </script>
@stop
