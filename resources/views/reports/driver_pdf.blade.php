<!DOCTYPE html>
<html>
<head>
	<title>{{ trans('invoices.invoice') }}</title>
	<style type="text/css">
	body.report {
		margin:0;
		padding:0;
		font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
		font-size: 14px;
		line-height: 20px;
	}
	table {
		font-size: 14px;
		line-height: 20px;
	}
	th {
		vertical-align: top;
		text-align: left;
	}
	td {
		vertical-align: top;
		text-align: left;
	}
	.small-devices {
		display: none;
	}
	</style>
</head>
<body class="report">
@php
    $version = (int)$report->version;
    $bookings = $report->bookings;
    if ($report->version > 1) {
        if (config('eto_report.export.company_take') === true) {
            $payments = $report->payments;
            $statusColors = json_decode(json_encode($report->status_colors));
        }
    }
@endphp
@foreach( $report->drivers as $driver_id=>$driver)
    @php
        if ($exportDriverId !== false && (int)$exportDriverId !== (int)$driver_id) {
            continue;
        }
        $commission = 0;
        $cash = 0;
        $company_total_paid = 0;
        $totalAll = 0;
    @endphp
	<div style="padding:15px;">
        <p style="font-weight: normal;">{{ trans('reports.form.driver') }}: <span style="font-weight: bold">{{ $driver->name }}</span></p>
        <p style="font-weight: normal;">{{ trans('reports.driver_income_set_to') }} {{ $driver->percent }}%</p>
        <table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
            <thead>
            <tr>
                <th style="width: 200px; text-align:left;">{{ trans('reports.columns.ref_number') }}</th>
                @if($version > 1)
                    @if(config('eto_report.export.total') === true)
                        <th style="width: 200px; text-align:left;">{{ trans('reports.columns.total') }}</th>
                    @endif
                    @if(config('eto_report.export.company_take') === true)
                        <th style="width: 200px; text-align:left;">{{ trans('reports.columns.company_take') }}</th>
                    @endif
                @endif
                <th style="width: 200px; text-align:left;">{{ trans('reports.columns.cash') }}</th>
                <th style="width: 200px; text-align:left;">{{ trans('reports.columns.commission') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach( $driver->bookings as $idBooking=>$hash)
                @php
                    $booking = $bookings->$hash;

                    if ($version > 1) {
                        if(config('eto_report.export.total') === true) {
                            $totalAll += $booking->total_price;
                        }
                        if (config('eto_report.export.company_take') === true) {
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
                    }

                    $cash += $booking->cash;
                    $commission += $booking->commission;
                @endphp
                <tr>
                    @if((int)$driver_id > 0 && $exportDriverId !== false)
                    <td style=" color:darkblue"><a href="{{ url('/driver/jobs/'.$booking->id) }}">{{ $booking->ref_number }}</a></td>
                    @else
                    <td style=" color:darkblue"><a href="{{ url('/admin/bookings/'.$booking->id) }}">{{ $booking->ref_number }}</a></td>
                    @endif
                    @if($version > 1)
                        @if(config('eto_report.export.total') === true)
                            <td style="">{{ format_price($booking->total_price) }}</td>
                        @endif
                        @if(config('eto_report.export.company_take') === true)
                            <td style="">{!! $company_take !!}</td>
                        @endif
                    @endif
                    <td style="">{{ format_price($booking->cash) }}</td>
                    <td style="">{{ format_price($booking->commission) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="font-weight: bold; margin-top: 10px">{{ trans('reports.columns.total') }}</td>
                @if($version > 1)
                    @if(config('eto_report.export.total') === true)
                        <td style="font-weight: bold; margin-top: 10px;">{{ format_price($totalAll) }}</td>
                    @endif
                    @if(config('eto_report.export.company_take') === true)
                        <td style="font-weight: bold; margin-top: 10px;">{!! format_price($company_total_paid) !!}</td>
                    @endif
                @endif
                <td style="font-weight: bold; margin-top: 10px;">{{ format_price($cash) }}</td>
                <td style="font-weight: bold; margin-top: 10px;">{{ format_price($commission) }}</td>
            </tr>
            </tbody>
        </table>
        <br>
        @php
            $balance = $commission - $cash;
            $whoPay = $balance > 0
                ? trans('reports.company_payd_driver')
                : trans('reports.driver_payd_company');
        @endphp
        <p style="font-weight: normal;">{{ trans('reports.final_balance') }}: {{ $whoPay }} {{ format_price(abs($balance)) }}</p>
	    <hr>
    </div>
@endforeach
</body>
</html>
