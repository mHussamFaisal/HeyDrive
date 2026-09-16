@php
$commission = 0;
$company_total_paid = 0;
$totalAll = 0;
@endphp

@extends('emails.template')

@section('title', $subject)

@section('content')
    <h3 style="font-weight: normal;">{{ trans('emails.fleet_report.greeting', ['name' => $fleet->name]) }}</h3><br />
    <table style="">
        <thead>
        <tr>
            <th style="padding: 0; width: 200px; text-align:left;">{{ trans('reports.columns.ref_number') }}</th>
{{--            @if(config('eto_report.email.total') === true)--}}
{{--                <th style="padding: 0; width: 200px; text-align:left;">{{ trans('reports.columns.total') }}</th>--}}
{{--            @endif--}}
{{--            @if(config('eto_report.email.company_take') === true)--}}
{{--                <th style="padding: 0; width: 200px; text-align:left;">{{ trans('reports.columns.company_take') }}</th>--}}
{{--            @endif--}}
            <th style="padding: 0; width: 200px; text-align:left;">{{ trans('reports.columns.fleet_commission') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach( $fleet->bookings as $idBooking=>$hash)
            @php
                $booking = $bookings->$hash;

                if(config('eto_report.email.total') === true) {
                    $totalAll += $booking->total_price;
                }
                if (config('eto_report.email.company_take') === true) {
                    $company_take = '<table><tbody>';
                    foreach ($booking->transactions as $paymentId=>$statuses) {
                        foreach ($statuses as $status=>$values) {
                            $paymentName = $payments->$paymentId->name;
                            $statusColor = $statusColors->$status->color;
                            $statusName = $statusColors->$status->name;
                            $totalTransaction = $values->payment_charge + $values->amount;
                            $company_take .= '<tr>
                                        <td title="'.$statusName.'" style="color: '.$statusColor.'">
                                            ' . format_price($totalTransaction) . '
                                        </td>
                                        <td style="color:#ccc;padding-left: 5px;">
                                            ' . $paymentName . '
                                        </td>
                                    </tr>';
                            if($status == 'paid') {
                                $company_total_paid += $totalTransaction;
                            }
                        }
                    }

                    $company_take .= '</tbody></table>';
                }

                $commission += $booking->fleet_commission;
            @endphp
            <tr>
                <td style="padding: 0; color:darkblue"><a href="{{ url('/admin/bookings/'.$booking->id) }}">{{ $booking->ref_number }}</a></td>
{{--                @if(config('eto_report.email.total') === true)--}}
{{--                    <td style="padding: 0;">{{ format_price($booking->total_price) }}</td>--}}
{{--                @endif--}}
{{--                @if(config('eto_report.email.company_take') === true)--}}
{{--                    <td style="padding: 0;">{!! $company_take !!}</td>--}}
{{--                @endif--}}
                <td style="padding: 0;">{{ format_price($booking->fleet_commission) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="padding: 0; font-weight: bold; margin-top: 10px">{{ trans('reports.columns.total') }}</td>
{{--            @if(config('eto_report.email.total') === true)--}}
{{--                <td style="padding: 0; font-weight: bold; margin-top: 10px">{{ format_price($totalAll) }}</td>--}}
{{--            @endif--}}
{{--            @if(config('eto_report.email.company_take') === true)--}}
{{--                <td style="padding: 0; font-weight: bold; margin-top: 10px">{!! format_price($company_total_paid) !!}</td>--}}
{{--            @endif--}}
            <td style="padding: 0; font-weight: bold; margin-top: 10px">{{ format_price($commission) }}</td>
        </tr>
        </tbody>
    </table>
    <br>

    @php
        $whoPay = $commission > 0
            ? trans('reports.company_payd_fleet')
            : trans('reports.fleet_payd_company');
    @endphp
    <h3 style="font-weight: normal;">{{ trans('reports.final_balance') }}: {{ $whoPay }} {{ format_price(abs($commission)) }}</h3>
@stop
