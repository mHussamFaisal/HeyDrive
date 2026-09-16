@extends('admin.index')

@section('title', trans('admin/settings.notifications.subtitle'))
@section('subtitle', /*'<i class="fa fa-bullhorn"></i> '.*/ trans('admin/settings.notifications.subtitle'))


@section('subheader')

@endsection


@section('subcontent')

@include('partials.modals.popup', [
    'class' => 'modal-popup modal-popup-notifications-preview',
])

<div id="settings-notifications">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <h3 id="settings-header">
        {{ trans('admin/settings.notifications.subtitle') }}
        @if( trans('admin/settings.notifications.subtitle_desc') )
            <i class="ion-ios-information-outline" style="display:inline-block; margin-left:5px; font-size:18px;" data-toggle="popover" data-title="{{ trans('admin/settings.notifications.subtitle') }}" data-content='{{ \App\Helpers\SiteHelper::nl2br2(trans('admin/settings.notifications.subtitle_desc')) }}'></i>
        @endif
    </h3>

    <div style="margin:10px 0 20px 0;">By clicking eye symbol you can see how each notification will look when sent.</div>

    <form method="post" action="{{ route('admin.settings.notifications', ['action' => 'save']) }}" id="notifications-form" autocomplete="off">
        {{ csrf_field() }}

        @foreach( $notifications as $notification => $roles )
            <table class="table table-hover table-condensed text-center @if($notification == 'booking_confirmed' || (!config('eto_dispatch.enable_autodispatch') && $notification == 'booking_auto_dispatch')) hidden @endif" style="width:auto; margin-bottom:10px;">
                <thead>
                    <tr style="background:#f8f8f8;">
                        <td style="font-weight:bold; width:200px; text-align:left;">
                            {{ trans('admin/settings.notifications.types.'. $notification .'.title') }}
                            @if( trans('admin/settings.notifications.types.'. $notification .'.desc') )
                                <i class="ion-ios-information-outline" style="display:inline-block; margin:1px 5px 0px 5px; font-size:18px; line-height:18px; float:right;" data-toggle="popover" data-title="{{ trans('admin/settings.notifications.types.'. $notification .'.title') }}" data-content='{{ \App\Helpers\SiteHelper::nl2br2(trans('admin/settings.notifications.types.'. $notification .'.desc')) }}'></i>
                            @endif
                        </td>
                        @if( $loop->iteration == 1 )
                            <td style="width:80px;">{{ trans('admin/settings.notifications.options.email') }}</td>
                            <td style="width:80px;">{{ trans('admin/settings.notifications.options.sms') }}</td>
                            <td style="width:80px;">{{ trans('admin/settings.notifications.options.push') }}</td>
                            {{-- <td style="width:50px;">{{ trans('admin/settings.notifications.options.db') }}</td> --}}
                        @else
                            <td style="width:80px;"></td>
                            <td style="width:80px;"></td>
                            <td style="width:80px;"></td>
                            {{-- <td style="width:50px;"></td> --}}
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach( $roles as $role => $channels )
                        <tr>
                            <td style="color:#888; text-align:left;" onclick="if( $(this).closest('tr').find('input:not(:checked)').length > 0 ){ $(this).closest('tr').find('input').attr('checked', true); } else { $(this).closest('tr').find('input').attr('checked', false); }">
                                {{ trans('admin/settings.notifications.roles.'. $role) }}
                            </td>
                            <td style="color:#888;">
                                @if( in_array('email', $channels) )
                                    <input type="checkbox" name="notifications[{{ $notification }}][{{ $role }}][]" id="{{ $notification }}_{{ $role }}_email" value="email" @if( isset($settings->notifications->{$notification}->{$role}) && in_array('email', $settings->notifications->{$notification}->{$role}) ) checked @endif  @permission('admin.settings.notifications.edit')@else disabled @endpermission/>

                                    <a href="{{ route('admin.settings.notifications') }}?action=preview&status={{ str_replace('booking_', '', $notification) }}&role={{ $role }}&channel=email" class="notification-preview notification-preview-email" target="_blank" title="Preview">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif
                            </td style="color:#888;">
                            <td>
                                @if( in_array('sms', $channels) )
                                    <input type="checkbox" name="notifications[{{ $notification }}][{{ $role }}][]" id="{{ $notification }}_{{ $role }}_sms" value="sms" @if( isset($settings->notifications->{$notification}->{$role}) && in_array('sms', $settings->notifications->{$notification}->{$role}) ) checked @endif  @permission('admin.settings.notifications.edit')@else disabled @endpermission/>

                                    <a href="{{ route('admin.settings.notifications') }}?action=preview&status={{ str_replace('booking_', '', $notification) }}&role={{ $role }}&channel=sms" class="notification-preview notification-preview-sms" target="_blank" title="Preview">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                            <td style="color:#888;">
                                @if( in_array('push', $channels) )
                                    <input type="checkbox" name="notifications[{{ $notification }}][{{ $role }}][]" id="{{ $notification }}_{{ $role }}_push" value="push" @if( isset($settings->notifications->{$notification}->{$role}) && in_array('push', $settings->notifications->{$notification}->{$role}) ) checked @endif  @permission('admin.settings.notifications.edit')@else disabled @endpermission/>

                                    <a href="{{ route('admin.settings.notifications') }}?action=preview&status={{ str_replace('booking_', '', $notification) }}&role={{ $role }}&channel=push" class="notification-preview notification-preview-push" target="_blank" title="Preview">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                            {{-- <td style="color:#888;">
                                @if( in_array('db', $channels) )
                                    <input type="checkbox" name="notifications[{{ $notification }}][{{ $role }}][]" id="{{ $notification }}_{{ $role }}_db" value="db" @if( isset($settings->notifications->{$notification}->{$role}) && in_array('db', $settings->notifications->{$notification}->{$role}) ) checked @endif @permission('admin.settings.notifications.edit')@else disabled @endpermission/>
                                @endif
                            </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <div class="form-group field-notification_booking_pending_info">
            <label for="notification_booking_pending_info">
                {{ trans('admin/settings.notifications.notification_booking_pending_info') }}
                @if( trans('admin/settings.notifications.notification_booking_pending_info_help') )
                    <i class="ion-ios-information-outline" style="display:inline-block; float:right; margin-top:-2px; margin-left:5px; font-size:18px;" data-toggle="popover" data-title="{{ trans('admin/settings.notifications.notification_booking_pending_info') }}" data-content='{{ \App\Helpers\SiteHelper::nl2br2(trans('admin/settings.notifications.notification_booking_pending_info_help')) }}'></i>
                @endif
            </label>
            <textarea name="notification_booking_pending_info" id="notification_booking_pending_info" placeholder="{{ trans('notifications.booking_pending.info') }}" class="form-control" @permission('admin.settings.notifications.edit')@else disabled @endpermission>{{ config('site.notification_booking_pending_info') }}</textarea>
        </div>

        <div class="form-group field-notification_test_email hidden">
            <label for="notification_test_email">
                {{ trans('admin/settings.notifications.notification_test_email') }}
                @if( trans('admin/settings.notifications.notification_test_email_help') )
                    <i class="ion-ios-information-outline" style="display:inline-block; float:right; margin-top:-2px; margin-left:5px; font-size:18px;" data-toggle="popover" data-title="{{ trans('admin/settings.notifications.notification_test_email') }}" data-content='{{ \App\Helpers\SiteHelper::nl2br2(trans('admin/settings.notifications.notification_test_email_help')) }}'></i>
                @endif
            </label>
            <input type="email" name="notification_test_email" id="notification_test_email" value="{{ config('site.notification_test_email') }}" placeholder="{{ trans('admin/settings.notifications.notification_test_email_placeholder') }}" class="form-control" @permission('admin.settings.notifications.edit')@else disabled @endpermission/>
        </div>

        <div class="form-group field-notification_test_phone hidden">
            <label for="notification_test_phone">
                {{ trans('admin/settings.notifications.notification_test_phone') }}
                @if( trans('admin/settings.notifications.notification_test_phone_help') )
                    <i class="ion-ios-information-outline" style="display:inline-block; float:right; margin-top:-2px; margin-left:5px; font-size:18px;" data-toggle="popover" data-title="{{ trans('admin/settings.notifications.notification_test_phone') }}" data-content='{{ \App\Helpers\SiteHelper::nl2br2(trans('admin/settings.notifications.notification_test_phone_help')) }}'></i>
                @endif
            </label>
            <input type="text" name="notification_test_phone" id="notification_test_phone" value="{{ config('site.notification_test_phone') }}" placeholder="{{ trans('admin/settings.notifications.notification_test_phone_placeholder') }}" class="form-control" @permission('admin.settings.notifications.edit')@else disabled @endpermission/>
        </div>
        @permission('admin.settings.notifications.edit')
        <div class="clearfix">
            <button type="submit" class="btn btn-md btn-success button-save">
                <i class="fa fa-save"></i> <span>{{ trans('admin/settings.button.save') }}</span>
            </button>
            <div id="status-message"></div>
        </div>
        @endpermission
    </form>

</div>
@endsection


@section('subfooter')
<script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>

<script>
$(document).ready(function() {
    var isReady = 1;
    var form = $('#notifications-form');

    $('[data-toggle="popover"]').popover({
        placement: 'auto right',
        container: 'body',
        trigger: 'focus hover click',
        html: true
    });

    $('#modal-popup').modal({
        show: false,
    });

    $('.notification-preview').click(function(e) {
        var modal = $('#modal-popup');
        var url = $(this).attr('href');

        if ($(this).hasClass('notification-preview-email')) {
            modal.removeClass('modal-popup-notifications-preview-phone');
        }
        else {
            modal.addClass('modal-popup-notifications-preview-phone');
        }

        modal.find('.modal-title').html('Preview');
        modal.find('.modal-body').html('<iframe src="'+ url +'"></iframe>');
        modal.modal('show');
        e.preventDefault();
    });

    form.submit(function(e) {
        e.preventDefault();

        if (!isReady) {
            return false;
        }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('admin.settings.notifications', ['action' => 'save']) }}',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: form.serializeJSON(),
            success: function(response) {
                if( response.errors ) {
                    var errors = '';
                    $.each(response.errors, function(index, error) {
                        errors += (errors ? ', ' : '') + error;
                    });
                    form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> '+ errors +'</span>');
                }
                else {
                    isReady = 1;
                    form.find('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> {{ trans('admin/settings.message.saved') }}</span>');
                    setTimeout(function() {
                        form.find('#status-message').html('');
                    }, 5000);
                }
            },
            error: function(response) {
                form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ trans('admin/settings.message.connection_error') }}</span>');
            },
            beforeSend: function() {
                isReady = 0;
                form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('admin/settings.button.saving') }}');
            },
            complete: function() {
                isReady = 1;
                form.find('.button-save').html('<i class="fa fa-save"></i> {{ trans('admin/settings.button.save') }}');
            }
        });
    });
});
</script>
@endsection
