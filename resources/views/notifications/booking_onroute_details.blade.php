@php
$fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;';
$style = [
    'data-table' => 'width:100%; margin-bottom:20px; line-height:20px; font-size:12px;'. $fontFamily,
    'data-heading' => 'padding:0px 0px 4px 0px; font-size:14px; font-weight:bold; color:#000000;'. $fontFamily,
    'data-name' => 'vertical-align:top; padding:2px 4px 2px 0px; line-height:20px; font-size:12px; width:120px;'. $fontFamily,
    'data-value' => 'vertical-align:top; padding:2px 4px 2px 0px; line-height:20px; font-size:12px;'. $fontFamily,
];
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="{{ $style['data-table'] }}">
@if( !empty($booking->id) )
    <tr>
        <td colspan="2" style="{{ $style['data-heading'] }}">
            {{ trans('booking.heading.booking') }}
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
    <tr>
        <td style="{{ $style['data-name'] }}">
            {{ trans('booking.from') }}:
        </td>
        <td style="{{ $style['data-value'] }}">
            {!! $booking->getFrom() !!}
        </td>
    </tr>
    @if( !empty($booking->getVia()) )
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
    <tr>
        <td style="{{ $style['data-name'] }}">
            {{ trans('booking.date') }}:
        </td>
        <td style="{{ $style['data-value'] }}">
            {{ App\Helpers\SiteHelper::formatDateTime($booking->date) }}
        </td>
    </tr>
    <tr>
        <td colspan="2"><br></td>
    </tr>
@endif

@if( !empty($driver->id) )
    <tr>
        <td colspan="2" style="{{ $style['data-heading'] }}">
            {{ trans('booking.heading.driver') }}
        </td>
    </tr>
    @if( $driver->avatar )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/users.avatar') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                <img src="{{ asset($driver->getAvatarPath()) }}" alt="" style="padding:0px; margin:0px; max-width:100px;" />
            </td>
        </tr>
    @endif
    @if( $driver->profile->getFullName() )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/users.name') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {{ $driver->profile->getFullName() }}
            </td>
        </tr>
    @endif
    @if( $driver->profile->mobile_no )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/users.mobile_no') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {!! $driver->profile->getTelLink('mobile_no', ['style'=>'color:#333333;']) !!}
            </td>
        </tr>
    @endif
    @if( $driver->profile->pco_licence )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/users.pco_licence') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {{ $driver->profile->pco_licence }}
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="2"><br></td>
    </tr>
@endif

@if( !empty($vehicle->id) )
    <tr>
        <td colspan="2" style="{{ $style['data-heading'] }}">
            {{ trans('booking.heading.vehicle') }}
        </td>
    </tr>
    @if( $vehicle->registration_mark )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/vehicles.registration_mark') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {{ $vehicle->registration_mark }}
            </td>
        </tr>
    @endif
    @if( $vehicle->make )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/vehicles.make') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {{ $vehicle->make }}
            </td>
        </tr>
    @endif
    @if( $vehicle->model )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/vehicles.model') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {{ $vehicle->model }}
            </td>
        </tr>
    @endif
    @if( $vehicle->colour )
        <tr>
            <td style="{{ $style['data-name'] }}">
                {{ trans('admin/vehicles.colour') }}:
            </td>
            <td style="{{ $style['data-value'] }}">
                {{ $vehicle->colour }}
            </td>
        </tr>
    @endif
@endif
</table>

<div style="clear:both;"></div>
