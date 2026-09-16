@extends('driver.index')

@section('title', trans('driver/jobs.page_title') .' / '. $job->getRefNumber())
@section('subtitle', /*'<i class="fa fa-tasks"></i> '*/ '<a href="'. route('driver.jobs.index') .'">'. trans('driver/jobs.page_title') .'</a> / '. $job->getRefNumber())

@php
$availableButtons = [
    'accepted' => [
        'url' => route('driver.jobs.status', [$job->id, 'accepted']),
        'cls' => 'btn btn-block btn-lg btn-success btn-big',
        'icon' => 'fa fa-check',
        'name' => trans('driver/jobs.button.accepted'),
        'status' => 'accepted',
    ],
    'rejected' => [
        'url' => route('driver.jobs.status', [$job->id, 'rejected']),
        'cls' => 'btn btn-block btn-lg btn-danger btn-big button-rejected',
        'icon' => 'fa fa-times',
        'name' => trans('driver/jobs.button.rejected'),
        'status' => 'rejected',
    ],
    'onroute' => [
        'url' => route('driver.jobs.status', [$job->id, 'onroute']),
        'cls' => 'btn btn-block btn-lg btn-primary btn-big',
        'icon' => 'fa fa-car',
        'name' => trans('driver/jobs.button.onroute'),
        'status' => 'onroute',
    ],
    'arrived' => [
        'url' => route('driver.jobs.status', [$job->id, 'arrived']),
        'cls' => 'btn btn-block btn-lg btn-primary btn-big',
        'icon' => 'fa fa-flag-checkered',
        'name' => trans('driver/jobs.button.arrived'),
        'status' => 'arrived',
    ],
    'onboard' => [
        'url' => route('driver.jobs.status', [$job->id, 'onboard']),
        'cls' => 'btn btn-block btn-lg btn-primary btn-big',
        'icon' => 'fa fa-user-circle',
        'name' => trans('driver/jobs.button.onboard'),
        'status' => 'onboard',
    ],
    'completed' => [
        'url' => route('driver.jobs.status', [$job->id, 'completed']),
        'cls' => 'btn btn-block btn-lg btn-success btn-big',
        'icon' => 'fa fa-flag',
        'name' => trans('driver/jobs.button.completed'),
        'status' => 'completed',
    ],
    'restart' => [
        'url' => route('driver.jobs.status', [$job->id, 'accepted']),
        'cls' => 'btn btn-block btn-lg btn-danger btn-big',
        'icon' => 'fa fa-repeat',
        'name' => trans('driver/jobs.button.restart'),
        'status' => 'restart',
    ],
];

function getStatusButtons($status, $availableButtons, $scheduledId) {
    $buttons = [];

    switch ($status) {
        case 'assigned':
        case 'auto_dispatch':
            $buttons[] = $availableButtons['accepted'];

            if (config('site.driver_show_reject_button')) {
                $buttons[] = $availableButtons['rejected'];
            }
        break;
        case 'accepted':
            if (config('site.driver_show_onroute_button')) {
                $buttons[] = $availableButtons['onroute'];
            }
            else {
                $buttons = array_merge($buttons, getStatusButtons('onroute', $availableButtons, $scheduledId));
            }
        break;
        case 'onroute':
            if ($scheduledId) {
                $buttons = array_merge($buttons, getStatusButtons('onboard', $availableButtons, $scheduledId));
            }
            else {
                if (config('site.driver_show_arrived_button')) {
                    $buttons[] = $availableButtons['arrived'];
                }
                else {
                    $buttons = array_merge($buttons, getStatusButtons('arrived', $availableButtons, $scheduledId));
                }
            }
        break;
        case 'arrived':
            if (config('site.driver_show_onboard_button')) {
                $buttons[] = $availableButtons['onboard'];
            }
            else {
                $buttons = array_merge($buttons, getStatusButtons('onboard', $availableButtons, $scheduledId));
            }
        break;
        case 'onboard':
            $buttons[] = $availableButtons['completed'];

            if (config('site.driver_show_restart_button')) {
                $buttons[] = $availableButtons['restart'];
            }
        break;
    }

    return $buttons;
}

$buttons = getStatusButtons($job->status, $availableButtons, $job->scheduled_route_id);

if (!empty($job->expired_at) && in_array($job->status, ['assigned','auto_dispatch'])) {
    $date = \Carbon\Carbon::parse($job->expired_at);
    $now = \Carbon\Carbon::now();
    $diff = $date->diffInSeconds($now);

    if ($date > $now) {
        $countdown = '<div class="timer-countdown-show timer-countdown" data-seconds-left="'. $diff .'"></div>';
    }
    else {
        $countdown = '<div class="timer-countdown-show">'. trans('driver/jobs.auto_dispatch_time_up') .'</div>';
    }
}
else {
    $countdown = '';
}
@endphp

@section('subcontent')
    @include('partials.modals.delete')

    @if (session('isMobileApp'))
      @include('partials.modals.popup', [
          'id' => 'modal-attachment',
          'class' => 'modal-attachment'
      ])
    @endif

    <div class="row" id="jobs">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            @include('partials.loader')
            @include('partials.alerts.success')
            @include('partials.alerts.errors')

            <div class="widget-user-2">
                <div class="widget-user-header clearfix text-center">
                    <h3 class="widget-user-username" style="margin-left:0;">
                        {{ $job->scheduled_route_id && !$job->parent_booking_id ? $job->getScheduledRouteName() : $job->getContactFullName() }}
                    </h3>
                    <h5 class="widget-user-desc" style="margin-left:0;">{{ $job->getRefNumber() }}</h5>

                    {!! $countdown !!}

                    @if( !$job->parent_booking_id )
                        @permission('driver.jobs.edit')
                        <div id="status-buttons" class="eto-job-buttons" style="margin-top:20px;">
                            <div class="row">
                                @foreach($buttons as $button)
                                    @php
                                    $button = (object)$button;
                                    $span = 12;
                                    switch(count($buttons)) {
                                        case 3:
                                            $span = 4;
                                        break;
                                        case 2:
                                            $span = 6;
                                        break;
                                    }
                                    @endphp
                                    <div class="col-xs-{{ $span }}">
                                        <a href="{{ $button->url }}" class="{{ $button->cls }}" data-eto-status="{{ $button->status }}">
                                            <i class="{{ $button->icon }}"></i> <span>{!! $button->name !!}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endpermission
                        <div class="eto-job-status-warning eto-job-unavailable hidden">
                            {{ trans('driver/jobs.statusUnavailable') }}
                        </div>
                        <div class="eto-job-status-warning eto-job-incomplete hidden">
                            {{ trans('driver/jobs.message.status_jobs_incomplete') }}
                        </div>
                    @endif

                </div>
                <div>

                    <ul class="list-group list-group-unbordered details-list">
                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/jobs.date') }}:</span>
                            <span class="details-list-value">{{ App\Helpers\SiteHelper::formatDateTime($job->date) }}</span>
                        </li>

                        <li class="list-group-item driver-notes-list-container" @if( !in_array($job->status, ['accepted', 'onroute', 'arrived', 'onboard', 'completed']) ) style="display:none;" @endif>
                            <span class="details-list-title">{{ trans('driver/jobs.notes') }}:</span>
                            <span class="details-list-value">
                                @if( $job->notes )
                                    <div class="notes-container">{{ trans('driver/jobs.admin_notes') }}: {!! App\Helpers\SiteHelper::nl2br2($job->notes) !!}</div>
                                @endif

                                <div class="driver-notes-container">
                                    <div id="note-text">
                                        @if( $job->driver_notes )
                                            {{ trans('driver/jobs.driver_notes') }}: {!! App\Helpers\SiteHelper::nl2br2($job->driver_notes) !!}
                                        @endif
                                    </div>
                                    @permission('driver.jobs.edit')
                                    <a href="#" onclick="$('#job-note-form').toggle(); $('.driver-notes-container').hide();" class="button-edit-note" @if( !in_array($job->status, ['accepted', 'onroute', 'arrived', 'onboard']) ) style="display:none;" @endif>
                                        <i class="fa fa-pencil-square-o"></i> <span>{{ trans('driver/jobs.button.edit_note') }}</span>
                                    </a>
                                    @endpermission
                                </div>
                                @permission('driver.jobs.edit')
                                <form method="post" action="{{ route('driver.jobs.update', $job->id) }}" id="job-note-form" autocomplete="off" style="display:none;">
                                    {{ csrf_field() }}
                                    {{ method_field('PUT') }}
                                    <div class="form-group field-driver_notes">
                                        <textarea name="driver_notes" id="driver_notes" class="form-control">{{ old('driver_notes', $job->driver_notes)}}</textarea>
                                    </div>
                                    <div class="clearfix">
                                        <button type="submit" class="btn btn-xs btn-success button-save">
                                            <i class="fa fa-save"></i> <span>{{ trans('driver/jobs.button.save') }}</span>
                                        </button>
                                        <a href="#" onclick="$('#job-note-form').toggle(); $('.driver-notes-container').show();" class="btn btn-xs btn-link button-cancel-note">
                                            <span>{{ trans('driver/jobs.button.cancel') }}</span>
                                        </a>
                                        <div id="status-message"></div>
                                    </div>
                                </form>
                                @endpermission
                            </span>
                        </li>

                        @if( config('site.driver_show_total') )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.total') }}:</span>
                                <span class="details-list-value">{!! $job->getTotal() !!}</span>
                            </li>
                        @endif

                        @if( !$job->scheduled_route_id || !$job->parent_booking_id )
                            <li class="list-group-item commission-placeholder-container" @if( in_array($job->status, ['onroute', 'arrived', 'onboard']) ) style="display:none;" @endif>
                                <span class="details-list-title">{{ trans('driver/jobs.commission') }}:</span>
                                <span class="details-list-value">
                                    <span class="text-green">{!! $job->getCommission() !!}</span>
                                    <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px; margin-left:5px;" title="{{ trans('driver/jobs.commission_info') }}"></i>
                                </span>
                            </li>
                        @endif

                        @if( !$job->scheduled_route_id || $job->parent_booking_id )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.cash') }}:</span>
                                <span class="details-list-value">
                                    <span class="text-danger">{!! ($job->cash) ? $job->getCash() : trans('driver/jobs.already_paid') !!}</span>
                                    <i class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px; margin-left:5px;" title="{{ trans('driver/jobs.cash_info') }}"></i>
                                </span>
                            </li>
                        @endif

                        @if( $job->getServiceType() )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('booking.service_type') }}:</span>
                                <span class="details-list-value">{{ $job->getServiceType() }}</span>
                            </li>
                        @endif

                        @if( $job->getServiceDuration() )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('booking.service_duration') }}:</span>
                                <span class="details-list-value">{{ $job->getServiceDuration() }}</span>
                            </li>
                        @endif

                        @php
                            $from = $job->getFrom('raw');
                            $to = $job->getTo('raw');

                            $pickupFlightDetails = $job->getFlightDetails('pickup', 'html');
                            $dropoffFlightDetails = $job->getFlightDetails('dropoff', 'html');
                        @endphp

                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/jobs.from') }}:</span>
                            <span class="details-list-value">
                                <span style="display:inline-block; max-width:340px;">
                                    {!! $from->address . (($from->complete) ? ', <span style="color:gray;">'. $from->complete .'</span>': '') !!}
                                    <div style="margin-top:2px;">
                                        {!! App\Helpers\SiteHelper::navigateLink([
                                            'address' => $from->address,
                                            '_name' => trans('booking.navigate_to_pickup'),
                                        ]) !!}
                                    </div>
                                </span>
                            </span>
                        </li>

                        @foreach ($job->getVia('raw') as $via)
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.via') }}:</span>
                                <span class="details-list-value">
                                    {!! $via->address . (($via->complete) ? ', <span style="color:gray;">'. $via->complete .'</span>': '') !!}
                                    <div style="margin-top:2px;">
                                        {!! App\Helpers\SiteHelper::navigateLink([
                                            'address' => $via->address,
                                            '_name' => trans('booking.navigate_to_via'),
                                        ]) !!}
                                    </div>
                                </span>
                            </li>
                        @endforeach

                        <li class="list-group-item">
                            <span class="details-list-title">{{ trans('driver/jobs.to') }}:</span>
                            <span class="details-list-value">
                                <span style="display:inline-block; max-width:340px;">
                                    {!! $to->address . (($to->complete) ? ', <span style="color:gray;">'. $to->complete .'</span>': '') !!}
                                    <div style="margin-top:2px;">
                                        {!! App\Helpers\SiteHelper::navigateLink([
                                            'address' => $to->address,
                                            '_name' => trans('booking.navigate_to_dropoff'),
                                        ]) !!}
                                    </div>
                                </span>
                            </span>
                        </li>

                        @if( $job->vehicle_list )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.vehicle') }}:</span>
                                <span class="details-list-value">{!! $job->getVehicleList() !!}</span>
                            </li>
                        @endif

                        @if( $job->flight_number )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.flight_number') }}:</span>
                                <span class="details-list-value">{{ $job->flight_number }}</span>
                            </li>
                        @endif
                        @if( $job->flight_landing_time )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.flight_landing_time') }}:</span>
                                <span class="details-list-value">{{ $job->flight_landing_time }}</span>
                            </li>
                        @endif
                        @if( $job->departure_city )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.departure_city') }}:</span>
                                <span class="details-list-value">{{ $job->departure_city }}</span>
                            </li>
                        @endif
                        @if( $pickupFlightDetails )
                            <li class="list-group-item">
                                <a href="#" onclick="$('.pickupFlightDetails').toggle(); return false;">Show flight details</a>
                                <div class="pickupFlightDetails" style="display:none; margin:10px 0;">{!! $pickupFlightDetails !!}</div>
                            </li>
                        @endif

                        @if( $job->departure_flight_number )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.departure_flight_number') }}:</span>
                                <span class="details-list-value">{{ $job->departure_flight_number }}</span>
                            </li>
                        @endif
                        @if( $job->departure_flight_time )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.departure_flight_time') }}:</span>
                                <span class="details-list-value">{{ $job->departure_flight_time }}</span>
                            </li>
                        @endif
                        @if( $job->departure_flight_city )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.departure_flight_city') }}:</span>
                                <span class="details-list-value">{{ $job->departure_flight_city }}</span>
                            </li>
                        @endif
                        @if( $dropoffFlightDetails )
                            <li class="list-group-item">
                                <a href="#" onclick="$('.dropoffFlightDetails').toggle(); return false;">Show flight details</a>
                                <div class="dropoffFlightDetails" style="display:none; margin:10px 0;">{!! $dropoffFlightDetails !!}</div>
                            </li>
                        @endif

                        @if( $job->meeting_point )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.meeting_point') }}:</span>
                                <span class="details-list-value">{{ $job->meeting_point }}</span>
                            </li>
                        @endif
                        @if( $job->meet_and_greet )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.meet_and_greet') }}:</span>
                                <span class="details-list-value">{{ trans('driver/jobs.meet_and_greet_required') }}</span>
                            </li>
                        @endif
                        @if( $job->waiting_time )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.waiting_time') }}:</span>
                                <span class="details-list-value">{{ trans('driver/jobs.waiting_time_after', ['time' => $job->waiting_time]) }}</span>
                            </li>
                        @endif

                        {{--
                        @if( $job->contact_name )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.contact_name') }}:</span>
                                <span class="details-list-value">{{ $job->getContactFullName() }}</span>
                            </li>
                        @endif
                        --}}

                        @if( $job->contact_mobile && config('site.driver_show_passenger_phone_number') )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.contact_mobile') }}:</span>
                                <span class="details-list-value">{!! $job->getTelLink('contact_mobile', ['class'=>'text-default']) !!}</span>
                            </li>
                        @endif

                        @if( $job->contact_email && config('site.driver_show_passenger_email') )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.contact_email') }}:</span>
                                <span class="details-list-value">{!! $job->getEmailLink('contact_email', ['class'=>'text-default']) !!}</span>
                            </li>
                        @endif

                        @if( $job->getLeadPassengerFullName() )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.lead_passenger_name') }}:</span>
                                <span class="details-list-value">{{ $job->getLeadPassengerFullName() }}</span>
                            </li>
                        @endif

                        @if( $job->lead_passenger_mobile && config('site.driver_show_passenger_phone_number') )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.lead_passenger_mobile') }}:</span>
                                <span class="details-list-value">{!! $job->getTelLink('lead_passenger_mobile', ['class'=>'text-default']) !!}</span>
                            </li>
                        @endif

                        @if( $job->lead_passenger_email && config('site.driver_show_passenger_email') )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.lead_passenger_email') }}:</span>
                                <span class="details-list-value">{!! $job->getEmailLink('lead_passenger_email', ['class'=>'text-default']) !!}</span>
                            </li>
                        @endif

                        @if( $job->passengers )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.passengers') }}:</span>
                                <span class="details-list-value">{{ $job->passengers }}</span>
                            </li>
                        @endif

                        @if( $job->luggage )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.luggage') }}:</span>
                                <span class="details-list-value">{{ $job->luggage }}</span>
                            </li>
                        @endif

                        @if( $job->hand_luggage )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.hand_luggage') }}:</span>
                                <span class="details-list-value">{{ $job->hand_luggage }}</span>
                            </li>
                        @endif

                        @if( $job->baby_seats )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.baby_seats') }}:</span>
                                <span class="details-list-value">{{ $job->baby_seats }}</span>
                            </li>
                        @endif

                        @if( $job->child_seats )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.child_seats') }}:</span>
                                <span class="details-list-value">{{ $job->child_seats }}</span>
                            </li>
                        @endif

                        @if( $job->infant_seats )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.infant_seats') }}:</span>
                                <span class="details-list-value">{{ $job->infant_seats }}</span>
                            </li>
                        @endif

                        @if( $job->wheelchair )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.wheelchair') }}:</span>
                                <span class="details-list-value">{{ $job->wheelchair }}</span>
                            </li>
                        @endif

                        @if( $job->requirements )
                            <li class="list-group-item">
                                <span class="details-list-title">{{ trans('driver/jobs.requirements') }}:</span>
                                <span class="details-list-value">{{ $job->requirements }}</span>
                            </li>
                        @endif

                        <li class="list-group-item status-placeholder-container" @if( in_array($job->status, ['assigned', 'auto_dispatch', 'accepted', 'onroute', 'arrived', 'onboard']) ) style="display:none;" @endif>
                            <span class="details-list-title">{{ trans('driver/jobs.status') }}:</span>
                            <span class="details-list-value status-placeholder">
                                {!! $job->getStatus( in_array($job->status, ['assigned', 'auto_dispatch', 'accepted', 'onroute', 'arrived', 'onboard']) ? 'none' : 'label') !!}

                                @if( $job->status_notes && in_array($job->status, ['canceled', 'unfinished', 'rejected']) )
                                    <i title="{{ $job->status_notes }}" class="ion-ios-information-outline" style="color:#a0a0a0; font-size:16px; margin-left:5px;"></i>
                                @endif
                            </span>
                        </li>
                    </ul>

                    {!! $job->getDriverStatusesHtml('driver', 'list', auth()->user()->id) !!}

                    @if( $job->scheduled_route_id && !$job->parent_booking_id )
                        @php
                        $children = \App\Models\BookingRoute::where('parent_booking_id', $job->id)->scheduledConfirmed()->get();

                        $passengers = 0;
                        foreach($children as $child) {
                            $passengers += $child->passengers;
                        }
                        @endphp

                        @if( $children->count() )
                          <div class="passanger-list-container" @if( !in_array($job->status, ['accepted', 'onroute', 'arrived', 'onboard']) ) style="display:none;" @endif>
                            <div style="margin-bottom:6px;">
                                {{ trans('booking.heading.customers') }}
                                {!! $passengers > 1 ? '<span style="color:#888;">('. $passengers .')</span>' : '' !!}
                            </div>
                            <ul class="list-group list-group-unbordered details-list">
                                @foreach($children as $child)
                                    @php
                                    $params = ['id' => $child->id];

                                    if (request('tmpl')) {
                                        $params['tmpl'] = 'body';
                                    }
                                    @endphp

                                    <li class="list-group-item" style="padding-top:4px; padding-bottom:4px;">
                                        <span class="details-list-title">
                                            <a href="{{ route('driver.jobs.show', $params) }}" class="text-default">
                                                {{ $child->ref_number }}
                                            </a>
                                        </span>
                                        <span class="details-list-value">
                                            <a href="{{ route('driver.jobs.show', $params) }}" class="text-default">
                                                {{ $child->getContactFullName() }}
                                                {!! $child->passengers > 1 ? '<span style="color:#888;">('. $child->passengers .')</span>' : '' !!}
                                            </a>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                          </div>
                        @endif
                    @endif

                    <div class="file_upload_container" style="margin-bottom:20px;">
                        <div style="margin: 0 0 5px 0; color:#808080;">{{ trans('admin/users.files') }}</div>
                        {!! Form::model($job, ['method' => 'patch', 'route' => ['driver.jobs.update', $job->id], 'class' => 'form-master2', 'enctype' => 'multipart/form-data']) !!}
                            <div id="fileList"></div>
                        {!! Form::close() !!}
                    </div>

                    @php
                    $params = [
                        'id' => $job->id,
                    ];

                    if (request('tmpl')) {
                        $params['tmpl'] = 'body';
                    }
                    @endphp

                    <div class="btn-group dropup dropdown-menu-container" role="group" aria-label="..." style="@if(!in_array($job->status, ['accepted', 'onroute', 'arrived', 'onboard'])) display:none; @endif">
                      <div class="btn-group pull-left" role="group">
                          <button type="button" class="btn btn-default btn-md dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                              <span class="fa fa-bars"></span>
                          </button>
                          <ul class="dropdown-menu" role="menu">
                              @if(config('site.booking_meeting_board_enabled') && !$job->scheduled_route_id)
                              <li>
                                  <a href="{{ route('driver.jobs.meeting-board', $params) }}">
                                      <span class="icon-box"><i class="fa fa-address-card-o"></i></span>
                                      <span>{{ trans('driver/jobs.button.meeting_board') }}</span>
                                  </a>
                              </li>
                              @endif

                              @if(config('site.driver_allow_cancel') != 2)
                              <li>
                                  <a href="{{ route('driver.jobs.status', array_merge($params, config('site.driver_allow_cancel') == 1 ? ['canceled'] : ['unfinished'])) }}" onclick="statusButtons(this); return false;" class="button-canceled">
                                      <span class="icon-box"><i class="fa fa-times"></i></span>
                                      <span>{{ trans('driver/jobs.button.canceled') }}</span>
                                  </a>
                              </li>
                              @endif
                              {{-- <li>
                                  <a href="javascript:void(0)" class="eto-btn-booking-tracking" data-eto-id="{{ $job->id }}" data-original-title="{{ trans('admin/bookings.button.tracking') }} #{{ $job->ref_number }}">
                                      <span style="display:inline-block; width:20px; text-align:center;">
                                          <i class="fa fa-map-marker"></i>
                                      </span>
                                      {{ trans('admin/bookings.button.tracking') }}
                                  </a>
                              </li> --}}
                          </ul>
                      </div>
                    </div>
                    <a href="javascript:void(0)" class="eto-btn-booking-tracking btn btn-default" data-eto-id="{{ $job->id }}" title="{{ trans('admin/bookings.button.tracking') }} {{ $job->getRefNumber() }}">
                        <span style="display:inline-block; text-align:center;">
                            <i class="fa fa-map-marker"></i> <span>{{ trans('admin/bookings.button.tracking') }}</span>
                        </span>
                    </a>

                    @if( request()->get('tmpl') != 'body' )
                        <a href="{{ $job->parent_booking_id ? route('driver.jobs.show', $job->parent_booking_id) : route('driver.jobs.index') }}" class="btn btn-default">
                          <span>{{ trans('driver/jobs.button.back') }}</span>
                        </a>
                    @endif

                </div>
            </div>

        </div>
    </div>

    <div class="eto-modal-booking-tracking modal" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="eto-booking-tracking-map" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('subfooter')
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
    <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-serializejson/jquery.serializejson.min.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-simple-timer/jquery.simple.timer.js') }}"></script>
    <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
    <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script type="text/javascript">
    var isReady = 1;
    var bookingStatus = '{{ $job->status }}';
    var runingJob = {!! json_encode($runingJob) !!};
    var jobId = {{ $job->id }};
    var statusChange = true;

    function displayContainer() {
        $('.eto-job-buttons').addClass('hidden');
        $('.eto-job-unavailable').addClass('hidden');
        $('.eto-job-incomplete').addClass('hidden');

        if ($.inArray(bookingStatus, ['canceled', 'unfinished', 'rejected']) >= 0) {
            $('.eto-btn-booking-tracking').addClass('hidden');
        }
        else {
            $('.eto-btn-booking-tracking').removeClass('hidden');
        }

        if ($.inArray(bookingStatus, ['canceled', 'unfinished', 'rejected', 'completed']) >= 0) {
            // Hide all
        }
        else if (driverStatus == 1) {
            if (statusChange !== false && ((runingJob.length >= 0 && $.inArray(jobId, runingJob) >= 0) || $.inArray(bookingStatus, ['assigned', 'auto_dispatch']) >= 0)) {
                $('.eto-job-buttons').removeClass('hidden');
            }
            else {
                $('.eto-job-incomplete').removeClass('hidden');
            }
        }
        else {
            $('.eto-job-unavailable').removeClass('hidden');
        }
    }

    function submitForm(that) {
        if( !isReady ) { return false; }
        var form = $('#job-note-form');

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: '{{ route('driver.jobs.update', $job->id) }}',
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
                    var text = form.find('#driver_notes').val();
                    text = text.replace(/\r\n|\r|\n/g,'<br>');
                    if( text ) {
                        text = '{{ trans('driver/jobs.driver_notes') }}: '+ text;
                    }
                    $('#note-text').html(text);
                    $('#job-note-form').toggle();
                    $('.driver-notes-container').show();

                    isReady = 1;
                    form.find('#status-message').html('<span class="text-green"><i class="fa fa-check"></i> {{ trans('driver/jobs.message.saved') }}</span>');
                    setTimeout(function() {
                        form.find('#status-message').html('');
                    }, 5000);
                }
            },
            error: function(response) {
                form.find('#status-message').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ trans('driver/jobs.message.connection_error') }}</span>');
            },
            beforeSend: function() {
                isReady = 0;
                form.find('.button-save').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('driver/jobs.button.saving') }}');
            },
            complete: function() {
                isReady = 1;
                form.find('.button-save').html('<i class="fa fa-save"></i> {{ trans('driver/jobs.button.save') }}');
            }
        });
    }

    function allowFileUpload() {
        if (
            {{ config('eto_driver.booking_file_upload') ? 1 : 0 }} &&
            $.inArray(bookingStatus, ['accepted', 'onroute', 'arrived', 'onboard', 'completed']) >= 0
        ) {
            $('.file_upload_container').removeClass('hidden');
        }
        else {
            $('.file_upload_container').addClass('hidden');
        }
    }

    function getStatusButtons(status, availableButtons, scheduledId) {
        var buttons = [];

        switch (status) {
            case 'assigned':
            case 'auto_dispatch':
                buttons.push(availableButtons.accepted);

                if ({{ config('site.driver_show_reject_button') }}) {
                    buttons.push(availableButtons.rejected);
                }
            break;
            case 'accepted':
                @if (config('site.driver_show_onroute_button'))
                    buttons.push(availableButtons.onroute);
                @else
                    buttons = buttons.concat(getStatusButtons('onroute', availableButtons, scheduledId));
                @endif
            break;
            case 'onroute':
                if (scheduledId) {
                    buttons = buttons.concat(getStatusButtons('onboard', availableButtons, scheduledId));
                }
                else {
                    @if (config('site.driver_show_arrived_button'))
                        buttons.push(availableButtons.arrived);
                    @else
                        buttons = buttons.concat(getStatusButtons('arrived', availableButtons, scheduledId));
                    @endif
                }
            break;
            case 'arrived':
                @if (config('site.driver_show_onboard_button'))
                    buttons.push(availableButtons.onboard);
                @else
                    buttons = buttons.concat(getStatusButtons('onboard', availableButtons, scheduledId));
                @endif
            break;
            case 'onboard':
                buttons.push(availableButtons.completed);

                @if (config('site.driver_show_restart_button'))
                    buttons.push(availableButtons.restart);
                @endif
            break;
        }

        return buttons;
    }

    function statusButtons(that) {
        if( !isReady ) { return false; }
        var url = $(that).attr('href');
        if( url == '#' ) { return false; }
        var status = $(that).data('etoStatus');

        if( $(that).hasClass('button-canceled') ) {
            var note = prompt('{{ trans('driver/jobs.status_notes') }}'); // Customer did not arrived.
            if ( note ) {
                url += '?note='+ note;
            } else {
                return false;
            }

            $('#loader').show();
        }
        else if( $(that).hasClass('button-rejected') ) {
            var note = prompt('{{ trans('driver/jobs.status_notes') }}');
            if( note != null ) {
                url += '?note='+ note;
            }
            else {
                return false;
            }
        }

        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: url,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
                bookingStatus = response.status;
                runingJob = response.runingJob;
                statusChange = response.statusChange;

                if (parseBoolean(response.statusChange) === true) {
                    var html = '';
                    var availableButtons = {!! json_encode($availableButtons) !!};
                    var scheduledId = {{ $job->scheduled_route_id }};
                    var buttons = getStatusButtons(response.status, availableButtons, scheduledId);
                    allowFileUpload();

                    $.each(buttons, function (index, button) {
                        var span = 12;
                        switch (buttons.length) {
                            case 3:
                                span = 4;
                            break;
                            case 2:
                                span = 6;
                            break;
                        }
                        html += '<div class="col-xs-' + span + '">\
                        <a href="' + button.url + '" class="' + button.cls + '" data-eto-status="'+button.status+'">\
                            <i class="' + button.icon + '"></i> <span>' + button.name + '</span>\
                        </a>\
                     </div>';
                    });

                    if (html) {
                        $('#status-buttons').html('<div class="row">' + html + '</div>').show();
                    } else {
                        $('#status-buttons').html('').hide();
                    }

                    $('.status-placeholder').html(response.status_formatted);

                    if ($.inArray(response.status, ['onroute', 'arrived', 'onboard']) >= 0) {
                        $('.commission-placeholder-container').hide();
                    } else {
                        $('.commission-placeholder-container').show();
                    }

                    if ($.inArray(response.status, ['assigned', 'auto_dispatch', 'accepted', 'onroute', 'arrived', 'onboard']) >= 0) {
                        $('.status-placeholder-container').hide();
                    } else {
                        $('.status-placeholder-container').show();
                    }

                    if ($.inArray(response.status, ['accepted', 'onroute', 'arrived', 'onboard']) >= 0) {
                        $('.dropdown-menu-container').show();
                        $('.passanger-list-container').show();
                        $('.button-edit-note').show();
                    } else {
                        $('.dropdown-menu-container').hide();
                        $('.passanger-list-container').hide();
                        $('.button-edit-note').hide();
                    }

                    if ($.inArray(response.status, ['accepted', 'onroute', 'arrived', 'onboard', 'completed']) >= 0) {
                        $('.driver-notes-list-container').show();
                    } else {
                        $('.driver-notes-list-container').hide();
                    }

                    if ($.inArray(response.status, ['assigned', 'auto_dispatch']) >= 0) {
                        $('.timer-countdown').show();
                    } else {
                        $('.timer-countdown').hide();
                    }

                    if ($(that).hasClass('button-canceled')) {
                        $(that).find('i').removeClass('class', 'fa fa-spinner fa-spin status-loading');
                        $(that).find('i').attr('class', 'fa fa-times');
                    }

                    $('#loader').hide();

                    $('#status-buttons a').on('click', function (e) {
                        e.preventDefault();
                        statusButtons(this);
                    });
                }
                else {
                    $(that).find('i').removeClass('fa fa-spinner fa-spin status-loading');
                }

                displayContainer();
            },
            error: function(response) {
                alert('An error occurred while processing your request.');
            },
            beforeSend: function() {
                isReady = 0;
                $(that).find('i').attr('class', 'fa fa-spinner fa-spin status-loading');
            },
            complete: function() {
                isReady = 1;
            }
        });
    }

    $(document).ready(function(){

        $('body').on('click', 'a.eto-navigate-link-button', function(e) {
            if (typeof navigator.geolocation !== 'undefined') {
                var that = this;
                var url = $(that).attr('href');
                $(that).find('i.navigation-staus-loading').remove();
                $(that).append('<i class="fa fa-spinner fa-spin navigation-staus-loading" style="margin-left:5px;"></i>');

                navigator.geolocation.getCurrentPosition(function(position) {
                    var currentLocation = position.coords.latitude +','+ position.coords.longitude;
                    // url = url.replace('My+Location', currentLocation);
                    url = url.replace('Current+Location', currentLocation);

                    // console.log('OK: ', url);
                    $(that).attr('href', url).find('i.navigation-staus-loading').remove();

                    @if (session('isMobileApp'))
                        window.location.href = url;
                    @else
                        var a = document.createElement('a');
                        a.href = url;
                        a.target = '_blank';
                        document.body.appendChild(a);
                        a.click();
                    @endif
                },
                function(positionError) {
                    console.log('Error: ' + positionError.message);
                    $(that).find('i.navigation-staus-loading').remove();

                    @if (session('isMobileApp'))
                        window.location.href = url;
                    @else
                        var a = document.createElement('a');
                        a.href = url;
                        a.target = '_blank';
                        document.body.appendChild(a);
                        a.click();
                    @endif
                }, {
                    enableHighAccuracy: true,
                    timeout: 3 * 1000 // seconds
                });

                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        $('.eto-btn-booking-tracking').click(function(e){
            e.preventDefault();
            if (typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
                ETO.Routehistory.init({
                    init: ['google', 'icons'],
                    lang: ['booking'],
                });
            } else {
                console.log('ETO.Routehistory is not initialized');
            }
        });

        if (typeof driverStatus === "undefined") {
            driverStatus = 0;

            $.ajax({
                headers : {
                    'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                url: EasyTaxiOffice.appPath +'/driver/get-status',
                type: 'POST',
                dataType: 'json',
                cache: false,
                async: false,
                data: {},
                success: function(response) {
                    if (response.success) {
                        var statusId = parseInt(response.statusId);
                        $('#eto-availability-status').val(statusId);
                        driverStatus = statusId;
                        displayContainer();
                    }
                    else {
                        alert('Could not switch to this status');
                    }
                },
                error: function(response) {
                    alert('An error occurred while processing your request');
                }
            });
        }

        displayContainer();

        $('#eto-availability-status').on('select2:select', function(e) {
            displayContainer();
        });

        $('#job-note-form').submit(function(e) {
            e.preventDefault();
            submitForm();
        });

        $('#status-buttons a').on('click', function(e){
            e.preventDefault();
            statusButtons(this);
        });

        @if (!empty($countdown))
        $('.timer-countdown').startTimer({
            onComplete: function(element) {
                element.addClass('timer-countdown-done').html('{{ trans('driver/jobs.auto_dispatch_time_up') }}');

                if ($.inArray(bookingStatus, ['assigned', 'auto_dispatch', 'accepted', 'rejected']) >= 0) {
                    $('.eto-job-buttons').addClass('hidden');
                }
            }
        });
        @endif

        $('.form-delete').on('click', function(e){
            e.preventDefault();
            var $form = $(this);
            $('#modal-delete').modal().on('click', '#delete-btn', function(){
                $form.submit();
            });
        });


        // File table
        $('#fileList').html(
          '<div class="table-responsive">\
            <table class="table table-condensed table-hover" cellspacing="0" width="100%" style="margin-bottom:10px;">\
            <thead class="hidden">\
              <tr>\
                <th>{{ trans('admin/users.files_name') }}</th>\
                <th>{{ trans('admin/users.files_file') }}</th>\
                <th></th>\
              </tr>\
            </thead>\
            <tbody></tbody>\
            </table>\
          </div>\
          <button type="button" class="btn btn-default btn-sm btnNewFile" style="margin-right:10px;">\
            <i class="fa fa-plus"></i> <span>{{ trans('admin/users.files_new') }}</span>\
          </button>'+
          '{!! Form::button('<i class="fa fa-save"></i> <span>'. trans('admin/feedback.button.update') .'</span>', [
            'type' => 'submit',
            'class' => 'btn btn-default btn-sm btnSaveFiles',
          ]) !!}'
        );

        $('#fileList .btnNewFile').on('click', function(e) {
            newFile();
            e.preventDefault();
        });

        $('.file_upload_container form').on('submit', function(e) {
            $('#fileList .btnSaveFiles i.fa-refresh').remove();
            $('#fileList .btnSaveFiles span').after('<i class="fa fa-refresh fa-spin" style="margin-left:5px;"></i>');
        });

        $('body').on('click', '#fileList table .btnDelete', function(e) {
            var tr = $(this).closest('tr');
            tr.find('#file_delete').val(1);
            tr.find('#file_name, #file_path').removeAttr('required');
            if (tr.find('.filename').html()) {
                tr.hide();
            }
            else {
                tr.remove();
            }
            checkFiles();
            e.preventDefault();
        });

        var lastIndex2 = 0;

        function checkFiles() {
            if ($('#fileList table tbody tr').find('#file_delete[value=0]').length > 0) {
                $('#fileList table').removeClass('hidden');
            }
            else {
                $('#fileList table').addClass('hidden');
            }

            // if ($('#fileList table tbody tr').length > 0) {
            if ($('#fileList table tbody tr').find('#file_id[value=0]').length > 0) {
                $('#fileList .btnSaveFiles').removeClass('hidden');
            }
            else {
                $('#fileList .btnSaveFiles').addClass('hidden');
            }

            if ({{ $job->status == 'completed' && config('eto_driver.booking_file_upload_auto_lock') && \Carbon\Carbon::parse($job->date)->addHours(config('eto_driver.booking_file_upload_auto_lock'))->lt(\Carbon\Carbon::now()) ? 1 : 0}}) {
                $('#fileList .btnNewFile').addClass('hidden');
            }
            else {
                $('#fileList .btnNewFile').removeClass('hidden');
            }
        }

        function newFile() {
            $('#fileList table tbody').append(
              '<tr class="fileRow'+ lastIndex2 +'">\
                <td>\
                  <input type="hidden" name="files['+ lastIndex2 +'][id]" id="file_id" value="0" required class="form-control">\
                  <input type="hidden" name="files['+ lastIndex2 +'][delete]" id="file_delete" value="0" required class="form-control">\
                  <input type="text" name="files['+ lastIndex2 +'][name]" id="file_name" placeholder="{{ trans('admin/users.files_name') }}" value="" required class="form-control">\
                </td>\
                <td>\
                  <input type="file" name="files['+ lastIndex2 +'][file]" id="file_path" required class="form-control">\
                  <div class="filename"></div>\
                </td>\
                <td>\
                  <button type="button" onclick="return false;" class="btn btn-default btn-sm btnDelete" title="{{ trans('admin/users.button.destroy') }}">\
                    <i class="fa fa-trash"></i>\
                  </button>\
                </td>\
              </tr>'
            );

            checkFiles();

            // Delete button
            $('#fileList').find('button.btnDelete').hover(
                function() {
                    $(this).removeClass('btn-default').addClass('btn-danger');
                },
                function() {
                    $(this).removeClass('btn-danger').addClass('btn-default');
                }
            );

            var index = lastIndex2;
            lastIndex2++;
            return index;
        }

        var files = {!! $job->getFiles(true) ?: '[]' !!};

        if (files) {
            $.each(files, function(key, value) {
                var index = newFile();
                var row = $('#fileList tr.fileRow'+ index);
                row.find('#file_id').val(value.id);
                row.find('#file_name').val(value.name);

                if (value.path) {
                    row.find('#file_name').hide().after('<div style="display:inline-block; padding:6px 0;">'+ value.name +'</div>');
                    row.find('.btnDelete').hide();
                }

                var file = row.find('#file_path');
                file.remove();
                row.find('.filename').html('<a href="'+ value.path +'" style="display:inline-block; padding:6px 0;" class="eto-attachment-link">{{ trans('admin/users.download') }}</a>');
            });
        }

        checkFiles();
        allowFileUpload();

        @if (session('isMobileApp'))
            $('#modal-attachment').modal({
                show: false
            });

            $('body').on('click', '.eto-attachment-link', function(e) {
                e.preventDefault();
                var modalAttachment = $('#modal-attachment');
                modalAttachment.find('.modal-title').html($(this).text());
                modalAttachment.find('.modal-body').html('<iframe src="'+ $(this).attr('href') +'?type=show" frameborder="0" height="400" width="100%" />');
                modalAttachment.modal('show');
            });
        @endif
    });

    $(window).load(function() {
        $('#loader').hide();
    });
    </script>
@stop
