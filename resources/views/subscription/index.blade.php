@extends('admin.index')
@if (!empty($coreUpdate->params->plane))
    @section('title', trans('subscription.plan_page_tille.' . $coreUpdate->params->plane))
@else
    @section('title', trans('subscription.plan_page_tille.0'))
@endif
@section('subtitle', /*'<i class="fa fa-arrow-circle-up"></i> '.*/ trans('subscription.menu_label') )

@section('subcontent')
    <div class="box-header" style="padding: 10px 0;">
        <h4 class="box-title">
            @if (!empty($coreUpdate->params->plane))
                {{ trans('subscription.plan_page_tille.' . $coreUpdate->params->plane) }}
            @else
                {{ trans('subscription.plan_page_tille.0') }}
            @endif
        </h4>

        @permission('admin.subscription.deactivation')
        @if (config('eto.allow_deactivation'))
            <div class="box-tools pull-right" style="right: 0px;">
                <button type="button" class="btn btn-sm btn-default eto-btn-settings" data-toggle="modal" data-target=".eto-modal-settings">
                    <i class="fa fa-cogs"></i>
                </button>
            </div>
        @endif
        @endpermission
    </div>
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
        <div class="row">
            @if ($newVersionsIsset)
                <div class="col-md-12 clearfix">
                    <div class="alert alert-info alert-dismissible callout callout-info clearfix">
                        <div class="pull-left">
                            <i class="icon fa fa-arrow-circle-up" style="font-size: 30px;"></i>
                            <p class="msg" style="padding-left: 40px; padding-top: 6px; font-size: 18px;">
                                {{ trans('subscription.message.available') }}
                            </p>
                        </div>

                        @permission('admin.subscription.update')
                        @if (!empty($coreUpdate) && !empty($coreUpdate->params) && $coreUpdate->params->noUpdates == false)
                            <button class="btn btn-md btn-warning pull-right eto-button-view-changelog eto-core">{{ trans('subscription.button.viewChangelogCore') }}</button>
                        @endif
                        @if (!empty($coreUpdate->maxUpdateVersion) && can_update($licenseUpdate, config('app.version'), $coreUpdate->maxUpdateVersion))
                            <button type="button" class="btn btn-md btn-success pull-right eto-button-update-core" style="text-decoration:none; margin-right:5px;">
                                <span>{{ trans('subscription.button.upgrade') }}</span>
                            </button>
                        @else
                            <a href="https://easytaxioffice.co.uk/pricing/software/" target="_blank" class="btn btn-md btn-success pull-right" style="text-decoration:none; margin-right:5px;">
                                <span>{{ trans('subscription.button.extend_license') }}</span>
                            </a>
                        @endif
                        @endpermission
                    </div>
                </div>
            @endif
            <div class="col-md-12 clearfix" style="margin-bottom: 10px;">
                @if ($licenseErrorMsg)
                    <div class="alert alert-error">{{ $licenseErrorMsg }}</div>
                @else
                    @if (null !== $licenseExpire)
                        @php
                        $cTime = time();
                        $lTime = strtotime($licenseExpire);
                        $reminder = config('eto.license_expiry_reminder') * 86400;
                        $className = $lTime - ($reminder/2) < $cTime ? 'text-red' : ($lTime - $reminder < $cTime ? 'text-yellow' : '');
                        @endphp
                        <div class="subscription-message subscription-message-expired">
                            {!! trans('subscription.date.subscriptionExpired', ['date'=>'<span class="'. $className .'" style="font-weight:bold;">'. format_date_time($licenseExpire, 'date') .'</span>']) !!}
                        </div>
                    @endif
                    @if (null !== $licenseSupport)
                        @php
                        $cTime = time();
                        $lTime = strtotime($licenseSupport);
                        $reminder = config('eto.license_support_reminder') * 86400;
                        $className = $lTime - ($reminder/2) < $cTime ? 'text-red' : ($lTime - $reminder < $cTime ? 'text-yellow' : '');
                        @endphp
                        <div class="subscription-message subscription-message-support">
                            {!! trans('subscription.date.subscriptionSupport', ['date'=>'<span class="'. $className .'" style="font-weight:bold;">'. format_date_time($licenseSupport, 'date') .'</span>']) !!}
                        </div>
                    @endif
                    @if (null !== $licenseUpdate)
                        @php
                        $cTime = time();
                        $lTime = strtotime($licenseUpdate);
                        $reminder = config('eto.license_update_reminder') * 86400;
                        $className = $lTime - ($reminder/2) < $cTime ? 'text-red' : ($lTime - $reminder < $cTime ? 'text-yellow' : '');
                        @endphp
                        <div class="subscription-message subscription-message-update">
                            {!! trans('subscription.date.subscriptionUpdate', ['date'=>'<span class="'. $className .'" style="font-weight:bold;">'. format_date_time($licenseUpdate, 'date') .'</span>']) !!}
                        </div>
                    @endif
                @endif
                <div style="margin-top:10px;">{{ trans('subscription.software_version') }} <b>{{ config('app.version') }}</b></div>
                <div style="margin-bottom:10px;">{{ trans('subscription.info_versions') }} <a href="{{ config('app.docs_url') }}/general/updates/" target="_blank"><i class="fa fa-external-link" style="color: #00c0ef;"></i></a></div>
                @permission('admin.subscription.updates')
                <button type="button" class="btn btn-md btn-info eto-button-check" style="text-decoration: none;">
                    <span>{{ trans('subscription.button.check') }}</span>
                </button>
                @endpermission
            </div>
        </div>
        <div class="row">
            <table class="col-lg-12{{--table table-bordered table-hover--}} ">
                <thead class="hidden">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($modules as $id=>$module)
                        <tr class="eto-module-installed" data-eto-id="{{ $module->id }}" data-eto-type="{{ $module->type }}">
                            <td class="module-title column-primary" style="padding: 20px 16px;">
                                <strong class="eto-title">{{ $module->name }}</strong>
                                @if (!empty($module->subscriptions[0]->params->mode))
                                    @if ($module->subscriptions[0]->params->mode == 'trial')
                                        <b class="text-red">{{ trans('subscription.trial') }}</b>
                                    @elseif ($module->subscriptions[0]->params->mode == 'free')
                                        <b class="text-green">{{ trans('subscription.free') }}</b>
                                    @endif

                                    @if (!empty($module->errors))
                                        <div class="clearfix"></div>
                                        @foreach($module->errors as $key=>$error)
                                        <span class="text-red">{{ trans('subscription.message.'.$key) }}</span>
                                        @endforeach
                                    @endif
                                    <div class="row-actions visible" style="margin-top:10px;">
                                        @if ($module->type != 'eto' && empty($module->errors))
                                            <div style="display:none;">
                                                @permission('admin.subscription.install')
                                                <button class="btn btn-xs btn-default eto-module-status" data-eto-status="{{ $module->subscriptions[0]->status }}">
                                                    {{ $module->subscriptions[0]->status == '1' ? trans('subscription.button.disable') : trans('subscription.button.enable') }}
                                                </button>
                                                @endpermission
                                                @permission('admin.subscription.install')
                                                <button class="btn btn-xs btn-danger eto-button-uninstall">{{ trans('subscription.button.uninstall_module') }}</button>
                                                @endpermission
                                            </div>

                                            @if ($module->subscriptions[0]->params->mode == 'trial')
                                                <a href="https://easytaxioffice.co.uk/pricing/driver-app/" target="_blank" class="btn btn-xs btn-success">{{ trans('subscription.button.extend_license') }}</a>
                                            @endif
                                        @endif

                                        @if (!empty($module->errors))
                                            @permission('admin.subscription.install')
                                            <button class="btn btn-xs btn-danger eto-button-uninstall">{{ trans('subscription.button.uninstall_module') }}</button>
                                            @endpermission
                                            <a href="https://easytaxioffice.co.uk/pricing/driver-app/" target="_blank" class="btn btn-xs btn-success">{{ trans('subscription.button.extend_license') }}</a>
                                        @elseif (!empty($module->available_version) && $module->available_version != '' && $module->version != $module->available_version)
                                            <button class="btn btn-xs btn-info eto-button-view-changelog">{{ trans('subscription.button.viewChangelog') }}</button>
                                            {{--@if ($module->available_version != '')--}}
                                                {{--<a class="btn btn-xs btn-success" href="{{ url('/subscription/'.$module->id) }}">{{ trans('subscription.button.upgrade') }}</a>--}}
                                            {{--@endif--}}
                                        @endif
                                    </div>
                                @else
                                    @permission('admin.subscription.install')
                                    <button class="btn btn-xs btn-success eto-button-install" data-eto-id="" data-eto-type="{{ $module->type }}">{{ trans('subscription.button.install') }}</button>
                                    @endpermission
                                @endif
                                <div class="column-description desc">
                                    @if (!empty($module->version))
                                        <span style="margin-right: 20px">{{ trans('subscription.current_version') }} <span style="font-weight:bold;">{{ $module->version }}</span></span>
                                    @endif

                                    @if (!empty($module->available_version) && $module->version != $module->available_version)
                                        | {{ trans('subscription.available_version') }} <b>{{ $module->available_version }}</b>
                                        @if ( !empty($module->max_Version) && $module->available_version != $module->max_Version)
                                            <abbr style="color: #ababab" title="Check requirements">{{ $module->maxVersion }}</abbr>
                                        @endif
                                    @endif
                                    @if (!empty($module->subscriptions[0]->params->expire_at))
                                        <span>{{ trans('subscription.date.expiry') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->expire_at, 'date') }}</span></span>
                                    @endif
                                    @if (!empty($module->subscriptions[0]->params->support_at))
                                        <span>{{ trans('subscription.date.support') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->support_at, 'date') }}</span></span>
                                    @endif
                                    @if (!empty($module->subscriptions[0]->params->update_at))
                                        <span>{{ trans('subscription.date.updates') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->update_at, 'date') }}</span></span>
                                    @endif

                                    @if (!empty($module->description))
                                        <div class="module-description">{{ $module->description }}</div>
                                    @endif
                                </div>
                            </td>
{{--                            <td class="column-description desc " style="padding: 20px 10px;">--}}
{{--                                @if (!empty($module->description))--}}
{{--                                    <div class="module-description">{{ $module->description }}</div>--}}
{{--                                @endif--}}
{{--                                <div class="">--}}
{{--                                    @if (!empty($module->version))--}}
{{--                                        {{ trans('subscription.current_version') }} <span style="font-weight:bold;">{{ $module->version }}</span>--}}
{{--                                    @endif--}}

{{--                                    @if (!empty($module->available_version) && $module->version != $module->available_version)--}}
{{--                                        | {{ trans('subscription.available_version') }} <b>{{ $module->available_version }}</b>--}}
{{--                                        @if ( !empty($module->max_Version) && $module->available_version != $module->max_Version)--}}
{{--                                            <abbr style="color: #ababab" title="Check requirements">{{ $module->maxVersion }}</abbr>--}}
{{--                                        @endif--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                                @if (!empty($module->subscriptions[0]->params->expire_at))--}}
{{--                                    <div class="">{{ trans('subscription.date.expiry') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->expire_at, 'date') }}</span></div>--}}
{{--                                @endif--}}
{{--                                @if (!empty($module->subscriptions[0]->params->support_at))--}}
{{--                                    <div class="">{{ trans('subscription.date.support') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->support_at, 'date') }}</span></div>--}}
{{--                                @endif--}}
{{--                                @if (!empty($module->subscriptions[0]->params->update_at))--}}
{{--                                    <div class="">{{ trans('subscription.date.updates') }} <span style="font-weight:bold;">{{ format_date_time($module->subscriptions[0]->params->update_at, 'date') }}</span></div>--}}
{{--                                @endif--}}
{{--                            </td>--}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="eto-addons-container">
      <div class="eto-addons-title">Available Add-ons</div>
      <ul class="eto-addons-list">
        <li>Driver App</li>
        <li>Driver App Personalised</li>
        <li>Passenger App Personalised</li>
        <li>Business Profile</li>
        {{-- <li>Driver Availability</li>
        <li>Hourly Rate</li>
        <li>Schedule Service</li> --}}
      </ul>
    </div>

    <a href="{{ route('admin.license') }}" style="position:absolute; bottom:10px; right:10px;">{{ trans('admin/index.licence') }}</a>

    <div class="modal fade" id="update-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('subscription.button.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade eto-modal-settings" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('subscription.settings') }}</h4>
                </div>
                <div class="modal-body">
                    @permission('admin.subscription.deactivation')
                    <div class="row clearfix">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-md btn-danger eto-button-disable-license">
                                <span>{{ trans('subscription.button.disableLicense') }}</span>
                            </button>
                        </div>
                    </div>
                    @endpermission
{{--                    <div class="row clearfix">--}}
{{--                        <div class="col-lg-12">--}}
{{--                            <button class="btn btn-md btn-info eto-button-change-license-key">--}}
{{--                                {{ trans('subscription.button.changeLicenseKey') }}--}}
{{--                            </button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>
    </div>
@stop

@section('subfooter')
<script src="{{ asset_url('js','recovery.js') }}"></script>
<script src="{{ asset_url('js','eto/eto-subscription.js') }}?_dc={{ config('app.timestamp') }}"></script>

<script>
$(document).ready(function(){
    var coreUpdate = {!! \GuzzleHttp\json_encode($coreUpdate) !!};
    coreUpdate.maxVersion = coreUpdate.maxUpdateVersion;

    if (typeof ETO.Subscription != "undefined") {
        if (typeof ETO.Subscription.init != "undefined") {
            ETO.Subscription.init({
                updateUrl: '{{ route('subscription.update.index') }}',
                storeUrl: '{{ config('eto.url.pricing') }}',
                modules: {!! \GuzzleHttp\json_encode($modules) !!},
                client: {!! \GuzzleHttp\json_encode($client) !!},
                coreUpdate: coreUpdate,
                licenseKey: '{{ $licenseKey }}',
            });
        }
    }
});

$(window).load(function(){
    $.LoadingOverlay('hide');
});
</script>
@stop
