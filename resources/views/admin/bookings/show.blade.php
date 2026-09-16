@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber())
@section('subtitle', /*'<i class="fa fa-eye"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / '. $booking->getRefNumber())

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('css','eto.css') }}?_dc={{ config('app.timestamp') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','select2/select2.min.css') }}">
@endsection


@section('subcontent')
<div id="booking-show">
    @include('partials.alerts.success')
    @include('partials.alerts.errors')

    <div id="show-container">

        <div class="row">
            <div class="col-xs-12 col-sm-6">

                <table class="table table-condensed">
                <thead>
                    <tr>
                        <th colspan="2"><b>{{ trans('booking.heading.journey') }}</b></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $pickupFlightDetails = $booking->getFlightDetails('pickup', 'html');
                        $dropoffFlightDetails = $booking->getFlightDetails('dropoff', 'html');
                    @endphp

                    @if( $booking->getServiceType() )
                        <tr>
                            <td>{{ trans('booking.service_type') }}:</td>
                            <td>{{ $booking->getServiceType() }}</td>
                        </tr>
                    @endif
                    @if( $booking->getServiceDuration() )
                        <tr>
                            <td>{{ trans('booking.service_duration') }}:</td>
                            <td>{{ $booking->getServiceDuration() }}</td>
                        </tr>
                    @endif
                    @if( $booking->getScheduledRouteName() )
                        <tr>
                            <td>{{ trans('admin/bookings.scheduled_route_id') }}:</td>
                            <td>{{ $booking->getScheduledRouteName() }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="col-xs-4">{{ trans('booking.from') }}:</td>
                        <td class="col-xs-8">{!! $booking->getFrom() !!}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('booking.date') }}:</td>
                        <td>{{ App\Helpers\SiteHelper::formatDateTime($booking->date) }}</td>
                    </tr>
                    @if( $booking->flight_number )
                        <tr>
                            <td>{{ trans('booking.flight_number') }}:</td>
                            <td>{{ $booking->flight_number }}</td>
                        </tr>
                    @endif
                    @if( $booking->flight_landing_time )
                        <tr>
                            <td>{{ trans('booking.flight_landing_time') }}:</td>
                            <td>{{ $booking->flight_landing_time }}</td>
                        </tr>
                    @endif
                    @if( $booking->departure_city )
                        <tr>
                            <td>{{ trans('booking.departure_city') }}:</td>
                            <td>{{ $booking->departure_city }}</td>
                        </tr>
                    @endif
                    @if( $booking->waiting_time )
                        <tr>
                            <td>{{ trans('booking.waiting_time') }}:</td>
                            <td>{{ trans('booking.waiting_time_after', ['time' => $booking->waiting_time]) }}</td>
                        </tr>
                    @endif
                    @if( $pickupFlightDetails )
                        <tr>
                            <td colspan="2">
                                <a href="#" onclick="$('.pickupFlightDetails').toggle(); return false;">Show flight details</a>
                                <div class="pickupFlightDetails" style="display:none; margin:10px 0;">{!! $pickupFlightDetails !!}</div>
                            </td>
                        </tr>
                    @endif

                    @if( $booking->meet_and_greet )
                        <tr>
                            <td>{{ trans('booking.meet_and_greet') }}:</td>
                            <td>{{ trans('booking.meet_and_greet_required') }}</td>
                        </tr>
                    @endif
                    @if( $booking->meeting_point )
                        <tr>
                            <td>{{ trans('booking.meeting_point') }}:</td>
                            <td>{{ $booking->meeting_point }}</td>
                        </tr>
                    @endif
                    @if( $booking->getVia() )
                        <tr>
                            <td>{{ trans('booking.via') }}:</td>
                            <td>{!! $booking->getVia() !!}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>{{ trans('booking.to') }}:</td>
                        <td>{!! $booking->getTo() !!}</td>
                    </tr>
                    @if( $booking->departure_flight_number )
                        <tr>
                            <td>{{ trans('booking.departure_flight_number') }}:</td>
                            <td>{{ $booking->departure_flight_number }}</td>
                        </tr>
                    @endif
                    @if( $booking->departure_flight_time )
                        <tr>
                            <td>{{ trans('booking.departure_flight_time') }}:</td>
                            <td>{{ $booking->departure_flight_time }}</td>
                        </tr>
                    @endif
                    @if( $booking->departure_flight_city )
                        <tr>
                            <td>{{ trans('booking.departure_flight_city') }}:</td>
                            <td>{{ $booking->departure_flight_city }}</td>
                        </tr>
                    @endif
                    @if( $dropoffFlightDetails )
                        <tr>
                            <td colspan="2">
                                <a href="#" onclick="$('.dropoffFlightDetails').toggle(); return false;">Show flight details</a>
                                <div class="dropoffFlightDetails" style="display:none; margin:10px 0;">{!! $dropoffFlightDetails !!}</div>
                            </td>
                        </tr>
                    @endif

                    @if( $booking->getVehicleList() )
                        <tr>
                            <td>{{ trans('booking.vehicle') }}:</td>
                            <td>{!! $booking->getVehicleList() !!}</td>
                        </tr>
                    @endif
                    @if( $booking->passengers )
                        <tr>
                            <td>{{ trans('booking.passengers') }}:</td>
                            <td>{{ $booking->passengers }}</td>
                        </tr>
                    @endif
                    @if( $booking->luggage )
                        <tr>
                            <td>{{ trans('booking.luggage') }}:</td>
                            <td>{{ $booking->luggage }}</td>
                        </tr>
                    @endif
                    @if( $booking->hand_luggage )
                        <tr>
                            <td>{{ trans('booking.hand_luggage') }}:</td>
                            <td>{{ $booking->hand_luggage }}</td>
                        </tr>
                    @endif
                    @if( $booking->baby_seats )
                        <tr>
                            <td>{{ trans('booking.baby_seats') }}:</td>
                            <td>{{ $booking->baby_seats }}</td>
                        </tr>
                    @endif
                    @if( $booking->child_seats )
                        <tr>
                            <td>{{ trans('booking.child_seats') }}:</td>
                            <td>{{ $booking->child_seats }}</td>
                        </tr>
                    @endif
                    @if( $booking->infant_seats )
                        <tr>
                            <td>{{ trans('booking.infant_seats') }}:</td>
                            <td>{{ $booking->infant_seats }}</td>
                        </tr>
                    @endif
                    @if( $booking->wheelchair )
                        <tr>
                            <td>{{ trans('booking.wheelchair') }}:</td>
                            <td>{{ $booking->wheelchair }}</td>
                        </tr>
                    @endif
                    @if( $booking->requirements )
                        <tr>
                            <td>{{ trans('booking.requirements') }}:</td>
                            <td>{{ $booking->requirements }}</td>
                        </tr>
                    @endif
                </tbody>
                </table>

                @if( $booking->getContactFullName() || $booking->contact_email || $booking->contact_mobile )
                    <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th colspan="2"><b>{{ trans('booking.heading.customer') }}</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if( $booking->getContactFullName() )
                            <tr>
                                <td class="col-xs-4">{{ trans('booking.contact_name') }}:</td>
                                <td class="col-xs-8">{{ $booking->getContactFullName() }}</td>
                            </tr>
                        @endif
                        @if( $booking->contact_email )
                            <tr>
                                <td>{{ trans('booking.contact_email') }}:</td>
                                <td>{!! $booking->getEmailLink('contact_email', ['style'=>'color:#333333;']) !!}</td>
                            </tr>
                        @endif
                        @if( $booking->contact_mobile )
                            <tr>
                                <td>{{ trans('booking.contact_mobile') }}:</td>
                                <td>{!! $booking->getTelLink('contact_mobile', ['style'=>'color:#333333;']) !!}</td>
                            </tr>
                        @endif
                        @if(!empty($booking->department))
                            <tr>
                                <td>{{ trans('booking.department') }}:</td>
                                <td>{!! $booking->department !!}</td>
                            </tr>
                        @endif
                    </tbody>
                    </table>
                @endif

                @if( $booking->lead_passenger_name || $booking->lead_passenger_email || $booking->lead_passenger_mobile )
                    <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th colspan="2"><b>{{ trans('booking.heading.lead_passenger') }}</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if( $booking->lead_passenger_name )
                            <tr>
                                <td class="col-xs-4">{{ trans('booking.lead_passenger_name') }}:</td>
                                <td class="col-xs-8">{{ $booking->getLeadPassengerFullName() }}</td>
                            </tr>
                        @endif
                        @if( $booking->lead_passenger_email )
                            <tr>
                                <td class="col-xs-4">{{ trans('booking.lead_passenger_email') }}:</td>
                                <td>{!! $booking->getEmailLink('lead_passenger_email', ['style'=>'color:#333333;']) !!}</td>
                            </tr>
                        @endif
                        @if( $booking->lead_passenger_mobile )
                            <tr>
                                <td>{{ trans('booking.lead_passenger_mobile') }}:</td>
                                <td>{!! $booking->getTelLink('lead_passenger_mobile', ['style'=>'color:#333333;']) !!}</td>
                            </tr>
                        @endif
                    </tbody>
                    </table>
                @endif

                @if( $booking->scheduled_route_id && !$booking->parent_booking_id )
                    @php
                    $children = \App\Models\BookingRoute::where('parent_booking_id', $booking->id)->get();

                    $passengers = 0;
                    foreach($children as $child) {
                        $passengers += $child->passengers;
                    }
                    @endphp

                    @if( $children->count() )
                        <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <b>{{ trans('booking.heading.customers') }}</b>
                                    {!! $passengers > 1 ? '<span style="color:#888;">('. $passengers .')</span>' : '' !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($children as $child)
                                @php
                                $params = ['id' => $child->id];

                                if (request('tmpl')) {
                                    $params['tmpl'] = 'body';
                                }
                                @endphp
                                <tr>
                                    <td class="col-xs-4">
                                        <a href="{{ route('admin.bookings.show', $params) }}" class="text-default" title="{{ $child->getStatus() }}">
                                            <i class="fa fa-info-circle" style="color:{{ $child->getStatus('color_value') }}; margin-right:5px; opacity:0.6;"></i> {{ $child->ref_number }}
                                        </a>
                                    </td>
                                    <td class="col-xs-8">
                                        <a href="{{ route('admin.bookings.show', $params) }}" class="text-default">
                                            {{ $child->getContactFullName() }}
                                        </a>
                                        {!! $child->passengers > 1 ? '<span style="color:#888;">('. $child->passengers .')</span>' : '' !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    @endif
                @endif

                {!! $booking->getDriverStatusesHtml('admin', 'table') !!}

            </div>
            <div class="col-xs-12 col-sm-6">

                <table class="table table-condensed">
                <thead>
                    <tr>
                        <th colspan="2"><b>{{ trans('booking.heading.reservation') }}</b></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-xs-4">{{ trans('booking.ref_number') }}:</td>
                        <td class="col-xs-8">{{ $booking->getRefNumber() }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('booking.created_date') }}:</td>
                        <td>{{ App\Helpers\SiteHelper::formatDateTime($booking->created_date) }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('booking.status') }}:</td>
                        <td>{!! $booking->getStatus('label') !!}</td>
                    </tr>

                    @if( !$booking->scheduled_route_id )
                        <tr>
                            <td>{{ trans('booking.journey_type') }}:</td>
                            <td>{!! $booking->getRouteName() !!}</td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="2"><br></td>
                    </tr>

                    @if( !auth()->user()->hasRole('admin.fleet_operator') )

                        @if( $booking->getSummary() )
                            <tr>
                                <td>{{ trans('booking.summary') }}:</td>
                                <td>{!! $booking->getSummary() !!}</td>
                            </tr>
                        @endif

                        @php
                          	$payment_charge = $booking->getTotal('payment_charge', 'raw');
                            $payment_list = $booking->getTotal('payment_list', 'raw');
                        @endphp

                        @if( $booking->discount || $payment_charge )
                            <tr>
                                <td>{{ trans('booking.price') }}:</td>
                                <td>{{ $booking->getTotalPrice() }}</td>
                            </tr>
                        @endif

                        @if( $booking->discount )
                            <tr>
                                <td>{{ trans('booking.discount_price') }}:</td>
                                <td>
                                    {{ $booking->getDiscount() }}
                                    @if( $booking->discount_code )
                                        <span style="color:#888; margin-left:5px;">( {{ $booking->discount_code }} )</span>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if( $payment_charge )
                            <tr>
                                <td>{{ trans('booking.payment_price') }}:</td>
                                <td>{{ $booking->getTotal('payment_charge') }}</td>
                            </tr>
                        @endif

                        <tr>
                            <td>{{ trans('booking.total_price') }}:</td>
                            <td>{{ $booking->getTotal() }}</td>
                        </tr>

                        <tr>
                            <td>{{ trans('booking.payments') }}:</td>
                            <td>
                                @forelse($payment_list as $payment)
                                    @if( count($payment_list) > 1 )
                                        {{ $payment->title }}
                                    @endif
                                    {{ $payment->formatted->total }} <span style="color:#888;">( {{ $payment->name }} )</span> - {!! $payment->status_color !!}<br>
                                @empty
                                    {{ trans('admin/bookings.transaction.message.no_transactions') }}
                                @endforelse
                            </td>
                        </tr>

                    @endif

                    @if( !$booking->scheduled_route_id || !$booking->parent_booking_id )
                        <tr>
                            <td>{{ trans('admin/bookings.commission') }}:</td>
                            <td>{{ $booking->getCommission() }}</td>
                        </tr>
                    @endif

                    @if( !$booking->scheduled_route_id || $booking->parent_booking_id )
                        <tr>
                            <td>{{ trans('admin/bookings.cash') }}:</td>
                            <td>{{ $booking->getCash() }}</td>
                        </tr>
                    @endif

                    @php
                    $driver = $booking->assignedDriver();
                    $params = ['id' => $driver->id];
                    if (request('tmpl')) {
                        $params['tmpl'] = 'body';
                    }
                    @endphp

                    @if( !empty($driver->id) )
                        @if( $driver->getName(true) )
                            <tr>
                                <td>{{ trans('admin/bookings.driver_name') }}:</td>
                                <td>
                                    @permission('admin.users.driver.index')
                                        <a href="{{ route('admin.users.show', $params) }}" class="text-default">{{ $driver->getName(true) }}</a>
                                    @else
                                        {{ $driver->getName(true) }}
                                    @endpermission
                                </td>
                            </tr>
                        @endif

                        @if( $driver->profile->mobile_no )
                            <tr>
                                <td>{{ trans('admin/bookings.contact_mobile') }}:</td>
                                <td>{!! $driver->profile->getTelLink('mobile_no', ['class'=>'text-default']) !!}</td>
                            </tr>
                        @endif
                    @endif

                    @php
                    if (config('eto.allow_fleet_operator') && !auth()->user()->hasRole('admin.fleet_operator') && !empty($booking->id)) {
                        $fleet = $booking->bookingFleet;
                        if (!empty($fleet->id)) {
                            $fleetParams = ['id' => $fleet->id];
                            if (request('tmpl')) {
                                $fleetParams['tmpl'] = 'body';
                            }
                        }
                    }
                    @endphp

                    @if (config('eto.allow_fleet_operator') && !auth()->user()->hasRole('admin.fleet_operator') && !empty($fleet->id) && $fleet->getName())
                        <tr>
                            <td>{{ trans('admin/bookings.fleet_name') }}:</td>
                            <td>
                                @permission('admin.users.admin.index')
                                    <a href="{{ route('admin.users.show', $fleetParams) }}" class="text-default">{{ $fleet->getName() }}</a>
                                @else
                                    {{ $fleet->getName() }}
                                @endpermission
                            </td>
                        </tr>
                    @endif

                    @if( config('eto.allow_fleet_operator') && !empty($fleet->id) && (!$booking->scheduled_route_id || !$booking->parent_booking_id) )
                        <tr>
                            <td>{{ trans('admin/bookings.fleet_commission') }}:</td>
                            <td>{{ $booking->getFleetCommission() }}</td>
                        </tr>
                    @endif

                    @if( $booking->source && !auth()->user()->hasRole('admin.fleet_operator') )
                        <tr>
                            <td>{{ trans('admin/bookings.source') }}:</td>
                            <td>
                                {{ $booking->source }}
                                @if( $booking->source_details )
                                    ({!! $booking->source_details !!})
                                @endif
                            </td>
                        </tr>
                    @endif
                    @if( !empty($booking->locale) && !empty(config('app.locales')[$booking->locale]['name']) )
                        <tr>
                            <td>{{ trans('booking.bookingNotificationsPreferLanguagePlaceholder') }}:</td>
                            <td>{{ config('app.locales')[$booking->locale]['name'] }}</td>
                        </tr>
                    @endif
                    @if( !empty($booking->custom) )
                        <tr>
                            <td>
                                @if (!empty(config('eto_booking.custom_field.name')))
                                    {{ config('eto_booking.custom_field.name') }}:
                                @else
                                    {{ trans('booking.customPlaceholder') }}:
                                @endif
                            </td>
                            <td>{{ $booking->custom }}</td>
                        </tr>
                    @endif

                </tbody>
                </table>

                <div class="file_upload_container" style="margin-bottom:20px;">
                    <div style="margin: 0 0 5px 6px; font-weight:bold;">{{ trans('admin/users.files') }}</div>
                    {!! Form::model($booking, ['method' => 'patch', 'route' => ['admin.bookings.update', $booking->id], 'class' => 'form-master2', 'enctype' => 'multipart/form-data']) !!}
                        <div id="fileList"></div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>

    </div>


    @php
    $params = [
        'id' => $booking->id,
    ];

    if (request('tmpl')) {
        $params['tmpl'] = 'body';
    }
    @endphp

    <div class="btn-group dropup" role="group" aria-label="...">
      @permission('admin.bookings.edit')
      <a href="{{ route('admin.bookings.edit', $params) }}" class="btn btn-default btn-md">
          <span><i class="fa fa-pencil-square-o"></i></span>
          <span>{{ trans('admin/bookings.button.edit') }}</span>
      </a>
      @endpermission
      {{-- <a href="{{ route('admin.bookings.show', $params) }}" class="btn btn-default btn-md">
          <span><i class="fa fa-eye"></i></span>
          <span>{{ trans('admin/bookings.button.show') }}</span>
      </a> --}}
      <div class="btn-group pull-left" role="group">
          <button type="button" class="btn btn-default btn-md dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <span class="fa fa-angle-down"></span>
          </button>
          <ul class="dropdown-menu" role="menu">
              {{-- <li>
                  <a href="{{ route('admin.bookings.edit', $params) }}">
                      <span class="icon-box"><i class="fa fa-pencil-square-o"></i></span>
                      <span>{{ trans('admin/bookings.button.edit') }}</span>
                  </a>
              </li> --}}
              {{-- <li>
                  <a href="{{ route('admin.bookings.destroy', $params) }}">
                      <span class="icon-box"><i class="fa fa-trash"></i></span>
                      <span>{{ trans('admin/bookings.button.destroy') }}</span>
                  </a>
              </li> --}}
              @permission('admin.bookings.invoice')
              <li>
                  <a href="{{ route('admin.bookings.invoice', $params) }}">
                      <span class="icon-box"><i class="fa fa-file-pdf-o"></i></span>
                      <span>{{ trans('admin/bookings.button.invoice') }}</span>
                  </a>
              </li>
              @endpermission
              @permission('admin.bookings.tracking')
              <li>
                  <a href="javascript:void(0)" class="eto-btn-booking-tracking" style="padding:3px 8px;" data-eto-id="{{ $booking->id }}" data-original-title="{{ trans('admin/bookings.button.tracking') }} #{{ $booking->ref_number }}">
                      <span style="display:inline-block; width:20px; text-align:center;">
                          <i class="fa fa-map-marker"></i>
                      </span>
                      {{ trans('admin/bookings.button.tracking') }}
                  </a>
              </li>
              @endpermission
              @permission(['admin.transactions.index','admin.transactions.create', 'admin.transactions.edit', 'admin.transactions.destroy'])
              <li>
                  <a href="{{ route('admin.bookings.transactions', $params) }}">
                      <span class="icon-box"><i class="fa fa-credit-card"></i></span>
                      <span>{{ trans('admin/bookings.button.transactions') }}</span>
                  </a>
              </li>
              @endpermission
              @permission('admin.bookings.create')
              <li>
                  <a href="{{ route('admin.bookings.copy', $params) }}">
                      <span class="icon-box"><i class="fa fa-files-o"></i></span>
                      <span>{{ trans('admin/bookings.button.copy') }}</span>
                  </a>
              </li>
              @endpermission
              @permission('admin.bookings.sms')
              <li>
                  <a href="{{ route('admin.bookings.sms', $params) }}">
                      <span class="icon-box"><i class="fa fa-commenting"></i></span>
                      <span>{{ trans('admin/bookings.button.sms') }}</span>
                  </a>
              </li>
              @endpermission
              @permission('admin.feedback.show')
              <li>
                  <a href="{{ route('admin.feedback.create', array_merge($params, ['ref_number' => $booking->ref_number])) }}">
                      <span class="icon-box"><i class="fa fa-comments-o"></i></span>
                      <span>{{ trans('admin/bookings.button.feedback') }}</span>
                  </a>
              </li>
              @endpermission
              @if(config('site.booking_meeting_board_enabled') && auth()->user()->hasPermission('admin.bookings.meeting_board'))
                  <li>
                      <a href="{{ route('admin.bookings.meeting-board', $params) }}">
                          <span class="icon-box"><i class="fa fa-address-card-o"></i></span>
                          <span>{{ trans('admin/bookings.button.meeting_board') }}</span>
                      </a>
                  </li>
              @endif
              @permission('admin.bookings.notifications')
              <li>
                  <a href="#" class="eto-notifications" data-eto-id="{{$booking->id}}" data-title="{{ trans('admin/bookings.button.notifications') }} #{{$booking->ref_number}}" style="padding:3px 8px;">
                      <span class="icon-box"><i class="fa fa-bell"></i></span>
                      <span>{{ trans('admin/bookings.button.notifications') }}</span>
                  </a>
              </li>
              @endpermission

              @if(config('laravel-activitylog.enabled'))
              @permission('admin.activity.index')
              <li>
                  <a href="#" data-eto-url="{{ url('/activity?subject=booking&subject_id='.$booking->id) }}" class="eto-wrapper-booking-activity" onclick="ETO.modalIframe(this); return false;" data-title="#{{$booking->ref_number}} {{ trans('admin/bookings.button.activity') }}" style="padding:3px 8px;">
                      <span class="icon-box"><i class="fa fa-shield"></i></span>
                      <span>{{ trans('admin/bookings.button.activity') }}</span>
                  </a>
              </li>
              @endpermission
              @endif

              @permission('admin.bookings.show')
              <li>
                  <a href="#" onclick="printContent('show-container'); return false;">
                      <span class="icon-box"><i class="fa fa-print"></i></span>
                      <span>{{ trans('admin/bookings.button.print') }}</span>
                  </a>
              </li>
              @endpermission
          </ul>
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
                    <div class="eto-booking-tracking-map" style="height: 500px"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-popup" role="dialog" aria-hidden="true">
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
</div>
@stop


@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment.min.js') }}"></script>
    <script src="{{ asset_url('plugins','moment/moment-timezone-with-data.min.js') }}"></script>
    <script src="//maps.googleapis.com/maps/api/js?v=3&key={{ config('site.google_maps_javascript_api_key') }}&libraries=places,geometry&language={{ app()->getLocale() }}"></script>
    <script src="{{ asset_url('plugins','markerwithlabel/markerwithlabel.js') }}"></script>
    <script src="{{ asset_url('plugins','jquery-minicolors/jquery.minicolors.min.js') }}"></script>
    <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>

    <script src="{{ asset_url('js','eto/eto-notification.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-routehistory.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user.js') }}?_dc={{ config('app.timestamp') }}"></script>
    <script src="{{ asset_url('js','eto/eto-user-driver.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
    function printContent(el) {
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }

    $(document).ready(function() {
        // if (typeof ETO.Routehistory != "undefined" && typeof ETO.Routehistory.init != "undefined") {
        //     ETO.Routehistory.init({
        //         init: ['google', 'icons'],
        //         lang: ['booking'],
        //     });
        // } else {
        //     console.log('ETO.Routehistory is not initialized');
        // }

        if (typeof ETO.Notifications != "undefined") {
            if (typeof ETO.Notifications.init != "undefined") {
                ETO.Notifications.init({
                    init: ['google', 'icons'],
                    lang: ['booking'],
                });
            }
            if (typeof ETO.Routehistory.init != "undefined") {
                ETO.Routehistory.init({});
            }
        }

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
          <button type="button" class="btn btn-default btn-sm btnNewFile" style="margin-left:5px; margin-right:10px;">\
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

            if ($('#fileList table tbody tr').length > 0) {
                $('#fileList .btnSaveFiles').removeClass('hidden');
            }
            else {
                $('#fileList .btnSaveFiles').addClass('hidden');
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

        var files = {!! $booking->getFiles(true, 'admin') ?: '[]' !!};

        if (files) {
            $.each(files, function(key, value) {
                var index = newFile();
                var row = $('#fileList tr.fileRow'+ index);
                row.find('#file_id').val(value.id);
                row.find('#file_name').val(value.name);

                var file = row.find('#file_path');
                file.remove();
                row.find('.filename').html('<a href="'+ value.path +'" style="display:inline-block; padding:6px 0px;">{{ trans('admin/users.download') }}</a>');
            });
        }

        checkFiles();

    });
    </script>
@endsection
