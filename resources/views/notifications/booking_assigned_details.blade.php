@php
$fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;';
$style = [
    'data-table' => 'width:100%; margin-bottom:20px; line-height:20px; font-size:12px;'. $fontFamily,
    'data-heading' => 'padding:0px 0px 4px 0px; font-size:14px; font-weight:bold; color:#000000;'. $fontFamily,
    'data-name' => 'vertical-align:top; padding:2px 4px 2px 0px; line-height:20px; font-size:12px; width:120px;'. $fontFamily,
    'data-value' => 'vertical-align:top; padding:2px 4px 2px 0px; line-height:20px; font-size:12px;'. $fontFamily,
];
@endphp


<!--[if mso]>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td width="50%" valign="top">
<![endif]-->

<table width="364" border="0" cellpadding="0" cellspacing="0" align="left" class="force-row" style="font-size:12px;line-height:20px;">
    <tr>
        <td class="col" valign="top" style="font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:20px;text-align:left;color:#333333;width:100%">

            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="{{ $style['data-table'] }}">
                <tr>
                    <td colspan="2" style="{{ $style['data-heading'] }}">
                        {{ trans('booking.heading.journey') }}
                    </td>
                </tr>
                @if( $booking->getServiceType() )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.service_type') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->getServiceType() }}
                        </td>
                    </tr>
                @endif
                @if( $booking->getServiceDuration() )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.service_duration') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->getServiceDuration() }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('booking.from') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {!! $booking->getFrom() !!}
                    </td>
                </tr>
                <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('booking.date') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {{ App\Helpers\SiteHelper::formatDateTime($booking->date) }}
                    </td>
                </tr>
                @if( $booking->flight_number )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.flight_number') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->flight_number }}
                        </td>
                    </tr>
                @endif
                @if( $booking->flight_landing_time )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.flight_landing_time') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->flight_landing_time }}
                        </td>
                    </tr>
                @endif
                @if( $booking->departure_city )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.departure_city') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->departure_city }}
                        </td>
                    </tr>
                @endif
                @if( $booking->waiting_time )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.waiting_time') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ trans('booking.waiting_time_after', [
                                'time' => $booking->waiting_time
                            ]) }}
                        </td>
                    </tr>
                @endif
                @if( $booking->meet_and_greet )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.meet_and_greet') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ trans('booking.meet_and_greet_required') }}
                        </td>
                    </tr>
                @endif
                @if( $booking->meeting_point )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.meeting_point') }}:
                        </td>
                        <td style="{{ $style['data-value'] }} color:#008000;">
                            {{ $booking->meeting_point }}
                        </td>
                    </tr>
                @endif
                @if( $booking->getVia() )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.via') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {!! $booking->getVia() !!}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('booking.to') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {!! $booking->getTo() !!}
                    </td>
                </tr>
                @if( $booking->departure_flight_number )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.departure_flight_number') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->departure_flight_number }}
                        </td>
                    </tr>
                @endif
                @if( $booking->departure_flight_time )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.departure_flight_time') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->departure_flight_time }}
                        </td>
                    </tr>
                @endif
                @if( $booking->departure_flight_city )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.departure_flight_city') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->departure_flight_city }}
                        </td>
                    </tr>
                @endif
                @if( $booking->getVehicleList() )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.vehicle') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {!! $booking->getVehicleList() !!}
                        </td>
                    </tr>
                @endif
                @if( $booking->passengers )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.passengers') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->passengers }}
                        </td>
                    </tr>
                @endif
                @if( $booking->baby_seats )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.baby_seats') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->baby_seats }}
                        </td>
                    </tr>
                @endif
                @if( $booking->child_seats )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.child_seats') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->child_seats }}
                        </td>
                    </tr>
                @endif
                @if( $booking->infant_seats )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.infant_seats') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->infant_seats }}
                        </td>
                    </tr>
                @endif
                @if( $booking->wheelchair )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.wheelchair') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->wheelchair }}
                        </td>
                    </tr>
                @endif
                @if( $booking->luggage )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.luggage') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->luggage }}
                        </td>
                    </tr>
                @endif
                @if( $booking->hand_luggage )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.hand_luggage') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->hand_luggage }}
                        </td>
                    </tr>
                @endif
                @if( $booking->requirements )
                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.requirements') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->requirements }}
                        </td>
                    </tr>
                @endif
            </table>

            @if( $booking->getContactFullName() || $booking->contact_email || $booking->contact_mobile )
                <table border="0" cellspacing="0" cellpadding="0" width="100%" style="{{ $style['data-table'] }}">
                    <tr>
                        <td colspan="2" style="{{ $style['data-heading'] }}">
                            {{ trans('booking.heading.customer') }}
                        </td>
                    </tr>
                    @if( $booking->getContactFullName() )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.contact_name') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {{ $booking->getContactFullName() }}
                            </td>
                        </tr>
                    @endif
                    @if( $booking->contact_email && config('site.driver_show_passenger_email') )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.contact_email') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {!! $booking->getEmailLink('contact_email', ['style'=>'color:#333333;']) !!}
                            </td>
                        </tr>
                    @endif
                    @if( $booking->contact_mobile && config('site.driver_show_passenger_phone_number') )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.contact_mobile') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {!! $booking->getTelLink('contact_mobile', ['style'=>'color:#333333;']) !!}
                            </td>
                        </tr>
                    @endif
                </table>
            @endif

            @if( $booking->lead_passenger_name ||
                ($booking->lead_passenger_email && config('site.driver_show_passenger_email')) ||
                ($booking->lead_passenger_mobile && config('site.driver_show_passenger_phone_number'))
            )
                <table border="0" cellspacing="0" cellpadding="0" width="100%" style="{{ $style['data-table'] }}">
                    <tr>
                        <td colspan="2" style="{{ $style['data-heading'] }}">
                            {{ trans('booking.heading.lead_passenger') }}
                        </td>
                    </tr>
                    @if( $booking->lead_passenger_name )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.lead_passenger_name') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {{ $booking->getLeadPassengerFullName() }}
                            </td>
                        </tr>
                    @endif
                    @if( $booking->lead_passenger_email && config('site.driver_show_passenger_email') )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.lead_passenger_email') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {!! $booking->getEmailLink('lead_passenger_email', ['style'=>'color:#333333;']) !!}
                            </td>
                        </tr>
                    @endif
                    @if( $booking->lead_passenger_mobile && config('site.driver_show_passenger_phone_number') )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.lead_passenger_mobile') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {!! $booking->getTelLink('lead_passenger_mobile', ['style'=>'color:#333333;']) !!}
                            </td>
                        </tr>
                    @endif
                </table>
            @endif

        </td>
    </tr>
</table>

<!--[if mso]>
</td>
<td width="50%" valign="top">
<![endif]-->

<table width="364" border="0" cellpadding="0" cellspacing="0" align="left" class="force-row" style="font-size:12px;line-height:20px;">
    <tr>
        <td class="col" valign="top" style="font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:20px;text-align:left;color:#333333;width:100%">

            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="{{ $style['data-table'] }}">
                <tr>
                    <td colspan="2" style="{{ $style['data-heading'] }}">
                        {{ trans('booking.heading.reservation') }}
                    </td>
                </tr>
                <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('booking.ref_number') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {{ $booking->getRefNumber() }}
                    </td>
                </tr>
                {{-- <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('booking.created_date') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {{ App\Helpers\SiteHelper::formatDateTime($booking->created_date) }}
                    </td>
                </tr> --}}

                @if( config('site.driver_show_total') )
                    @if( $booking->getSummary() && config('site.booking_summary_enable') )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.summary') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {!! $booking->getSummary() !!}
                            </td>
                        </tr>
                    @endif

                    @php
                      	$payment_charge = $booking->getTotal('payment_charge', 'raw');
                        $payment_list = $booking->getTotal('payment_list', 'raw');
                    @endphp

                    @if( $booking->discount || $payment_charge )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.price') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {{ $booking->getTotalPrice() }}
                            </td>
                        </tr>
                    @endif

                    @if( $booking->discount )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.discount_price') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {{ $booking->getDiscount() }}
                                @if( $booking->discount_code )
                                    <span style="color:#888; margin-left:5px;">( {{ $booking->discount_code }} )</span>
                                @endif
                            </td>
                        </tr>
                    @endif

                    @if( $payment_charge )
                        <tr>
                            <td style="{{ $style['data-name'] }}">
                                {{ trans('booking.payment_price') }}:
                            </td>
                            <td style="{{ $style['data-value'] }}">
                                {{ $booking->getTotal('payment_charge') }}
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.total_price') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
                            {{ $booking->getTotal() }}
                        </td>
                    </tr>

                    <tr>
                        <td style="{{ $style['data-name'] }}">
                            {{ trans('booking.payments') }}:
                        </td>
                        <td style="{{ $style['data-value'] }}">
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

                <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('driver/jobs.commission') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {!! $booking->getCommission() !!}
                    </td>
                </tr>
                <tr>
                    <td style="{{ $style['data-name'] }}">
                        {{ trans('driver/jobs.cash') }}:
                    </td>
                    <td style="{{ $style['data-value'] }}">
                        {!! ($booking->cash) ? $booking->getCash() : trans('driver/jobs.already_paid') !!}
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

<!--[if mso]>
        </td>
    </tr>
</table>
<![endif]-->

<div style="clear:both;"></div>
