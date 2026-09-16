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
    $version = $report->version;
    $bookings = $report->bookings;
    $totals = [];
@endphp
@foreach($report->payments as $id=>$payment)
    @php
       $paymentId = $payment->id;
       $site = !empty($payment->site) ? ' <span style="color: #eee">('.$payment->site.')</span>' : '';
    @endphp
		<div style="padding:15px;">
        <p style="font-weight: bold">{{ $payment->name }}</p>

        @foreach($payment->status as $statusId=>$status)
            <div style="font-weight: normal;">{{ $status->name }}</div>
		        @php
		            $totalAmount = 0;
		            if (empty($totals[$statusId])) {
		                $totals[$statusId] = (object)[
		                    'name' => $status->name,
		                    'total' => 0,
		                ];
		            }
								$totals[$statusId]->total += $status->total;
		        @endphp
		        <table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
		            <tbody>
		            @foreach( $status->bookings as $idBooking=>$hash)
		                @php
		                $booking = $bookings->$hash;
                    if (!empty($booking->transactions->$paymentId)) {
                    		$transaction = $booking->transactions->$paymentId->$statusId;
                    }
										elseif (!empty($booking->transactions[$paymentId])) {
                        $transaction = $booking->transactions[$paymentId]->$statusId;
                    }
		                $total = 0;

		                if ($report->version === 1) {
		                    $total = $transaction;
		                } else {
		                    $total = $transaction->payment_charge + $transaction->amount;
		                }

		                $totalAmount += $total;
		                @endphp
		                <tr>
		                    <td style="width:200px; color:darkblue;"><a href="{{ url('/admin/bookings/'.$idBooking) }}">{{ $booking->ref_number }}</a></td>
		                    <td style="width:200px;">{{ format_price($total) }}</td>
		                </tr>
		            @endforeach
		            <tr>
		                <td style="width:200px; border-top: #ccc 1px solid; font-weight: bold; margin-top: 10px;">{{ trans('reports.columns.total') }}</td>
		                <td style="width:200px; border-top: #ccc 1px solid; font-weight: bold; margin-top: 10px;">{{ format_price($totalAmount) }}</td>
		            </tr>
		            </tbody>
		        </table>
        @endforeach
	    {{-- <hr> --}}
    </div>
@endforeach
<div style="padding:15px;">
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
        <thead>
        <tr>
            <td colspan="2" style="font-weight: bold;">{{ trans('reports.titles.all_payments') }}</td>
        </tr>
        </thead>
        <tbody>
        @foreach($totals as $key=>$value)
            <tr>
                <td width="50%" style="width:200px;">{{ $value->name }}</td>
                <td width="50%" style="width:200px;">{{ format_price($value->total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
