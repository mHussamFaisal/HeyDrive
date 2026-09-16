
{{ trans('notifications.greeting.general', [
  'name' => !empty($driver->getName(true)) ? $driver->getName(true) : '',
]) }}
{{ trans('notifications.'. ($booking->status == 'auto_dispatch' ? 'booking_auto_dispatch' : 'booking_assigned') .'.subject', [
  'ref_number' => $booking->getRefNumber()
]) }}<separator>


{{-- {{ trans('booking.heading.journey') }} --}}
@if( $booking->getServiceType() )
    {{ trans('booking.service_type') }}: {{ $booking->getServiceType() }}<separator>
@endif
@if( $booking->getServiceDuration() )
    {{ trans('booking.service_duration') }}: {{ $booking->getServiceDuration() }}<separator>
@endif

{{ trans('booking.from') }}: {!! $booking->getFrom('no_html') !!}<separator>
{{ trans('booking.date') }}: {{ App\Helpers\SiteHelper::formatDateTime($booking->date) }}<separator>

@if( $booking->flight_number )
    {{ trans('booking.flight_number') }}: {{ $booking->flight_number }}<separator>
@endif
@if( $booking->flight_landing_time )
    {{ trans('booking.flight_landing_time') }}: {{ $booking->flight_landing_time }}<separator>
@endif
@if( $booking->departure_city )
    {{ trans('booking.departure_city') }}: {{ $booking->departure_city }}<separator>
@endif
@if( $booking->waiting_time )
    {{ trans('booking.waiting_time') }}: {{ trans('booking.waiting_time_after', [
        'time' => $booking->waiting_time
    ]) }}<separator>
@endif
@if( $booking->meet_and_greet )
    {{ trans('booking.meet_and_greet') }}: {{ trans('booking.meet_and_greet_required') }}<separator>
@endif
{{-- @if( $booking->meeting_point )
    {{ trans('booking.meeting_point') }}: {{ $booking->meeting_point }}<separator>
@endif --}}

@if( $booking->getVia('no_html') )
    {{ trans('booking.via') }}: {!! $booking->getVia('no_html') !!}<separator>
@endif
{{ trans('booking.to') }}: {!! $booking->getTo('no_html') !!}<separator>
@if( $booking->departure_flight_number )
    {{ trans('booking.departure_flight_number') }}: {{ $booking->departure_flight_number }}<separator>
@endif
@if( $booking->departure_flight_time )
    {{ trans('booking.departure_flight_time') }}: {{ $booking->departure_flight_time }}<separator>
@endif
@if( $booking->departure_flight_city )
    {{ trans('booking.departure_flight_city') }}: {{ $booking->departure_flight_city }}<separator>
@endif
@if( $booking->getVehicleList() )
    {{ trans('booking.vehicle') }}: {!! $booking->getVehicleList() !!}<separator>
@endif
@if( $booking->passengers )
    {{ trans('booking.passengers') }}: {{ $booking->passengers }}<separator>
@endif
@if( $booking->baby_seats )
    {{ trans('booking.baby_seats') }}: {{ $booking->baby_seats }}<separator>
@endif
@if( $booking->child_seats )
    {{ trans('booking.child_seats') }}: {{ $booking->child_seats }}<separator>
@endif
@if( $booking->infant_seats )
    {{ trans('booking.infant_seats') }}: {{ $booking->infant_seats }}<separator>
@endif
@if( $booking->wheelchair )
    {{ trans('booking.wheelchair') }}: {{ $booking->wheelchair }}<separator>
@endif
@if( $booking->luggage )
    {{ trans('booking.luggage') }}: {{ $booking->luggage }}<separator>
@endif
@if( $booking->hand_luggage )
    {{ trans('booking.hand_luggage') }}: {{ $booking->hand_luggage }}<separator>
@endif
@if( $booking->requirements )
    {{ trans('booking.requirements') }}: {{ $booking->requirements }}<separator>
@endif

@if( $booking->getContactFullName() || $booking->contact_email || $booking->contact_mobile )
    {{ trans('booking.heading.customer') }}
    @if( $booking->getContactFullName() )
        {{ trans('booking.contact_name') }}: {{ $booking->getContactFullName() }}<separator>
    @endif
    {{-- @if( $booking->contact_email )
        {{ trans('booking.contact_email') }}: {!! $booking->contact_email !!}<separator>
    @endif --}}
    @if( $booking->contact_mobile )
        {{ trans('booking.contact_mobile') }}: {!! $booking->contact_mobile !!}<separator>
    @endif
@endif

@if( $booking->getLeadPassengerFullName() || $booking->lead_passenger_email || $booking->lead_passenger_mobile )
    {{ trans('booking.heading.lead_passenger') }}
    @if( $booking->getLeadPassengerFullName() )
        {{ trans('booking.lead_passenger_name') }}: {{ $booking->getLeadPassengerFullName() }}<separator>
    @endif
    {{-- @if( $booking->lead_passenger_email )
        {{ trans('booking.lead_passenger_email') }}: {{ $booking->lead_passenger_email }}<separator>
    @endif --}}
    @if( $booking->lead_passenger_mobile )
        {{ trans('booking.lead_passenger_mobile') }}: {!! $booking->lead_passenger_mobile !!}<separator>
    @endif
@endif

{{-- {{ trans('booking.heading.reservation') }} --}}
{{-- {{ trans('booking.ref_number') }}: {{ $booking->getRefNumber() }}<separator> --}}
{{-- {{ trans('booking.created_date') }}: {{ App\Helpers\SiteHelper::formatDateTime($booking->created_date) }} --}}

@if( config('site.driver_show_total') )
    {{-- @if( $booking->getSummary() && config('site.booking_summary_enable') )
        {{ trans('booking.summary') }}: {!! $booking->getSummary() !!}<separator>
    @endif --}}

    {{-- @php
      	$payment_charge = $booking->getTotal('payment_charge', 'raw');
        $payment_list = $booking->getTotal('payment_list', 'raw');
    @endphp --}}

    {{-- @if( $booking->discount || $payment_charge )
        {{ trans('booking.price') }}: {{ $booking->getTotalPrice() }}<separator>
    @endif

    @if( $booking->discount )
        {{ trans('booking.discount_price') }}: {{ $booking->getDiscount() }}<separator>
        @if( $booking->discount_code )
            <span style="color:#888; margin-left:5px;">( {{ $booking->discount_code }} )</span><separator>
        @endif
    @endif

    @if( $payment_charge )
        {{ trans('booking.payment_price') }}: {{ $booking->getTotal('payment_charge') }}<separator>
    @endif --}}

    {{ trans('booking.total_price') }}: {{ $booking->getTotal() }}<separator>

    {{-- {{ trans('booking.payments') }}:
    @forelse($payment_list as $payment)
        @if( count($payment_list) > 1 )
            {{ $payment->title }}
        @endif
        {{ $payment->formatted->total }} <span style="color:#888;">( {{ $payment->name }} )</span> - {!! $payment->status_color !!}<br>
    @empty
        {{ trans('admin/bookings.transaction.message.no_transactions') }}
    @endforelse
    <separator> --}}
@endif

{{ trans('driver/jobs.commission') }}: {!! $booking->getCommission() !!}<separator>
{{ trans('driver/jobs.cash') }}: {!! ($booking->cash) ? $booking->getCash() : trans('driver/jobs.already_paid') !!}
