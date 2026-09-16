<!DOCTYPE html>
<html>
<head>
	<title>{{ trans('invoices.invoice') }}</title>
	<style type="text/css">
	body.invoice {
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
<body class="invoice" style="margin:0; padding:0; font-family:'Helvetica Neue', Arial, Helvetica, sans-serif; font-size:14px; line-height:20px;">

	<div>
		<table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
			<tr>
				<td width="60%" style="width:60%; padding:20px 10px; vertical-align:middle; text-align:left; background-color:{{ config('site.invoice_styles_default_bg_color') }}; color:{{ config('site.invoice_styles_default_text_color') }}; font-weight:300; line-height:30px; font-size:30px; text-transform:uppercase;">
					{{ trans('invoices.invoice') }}
				</td>
				<td width="40%" style="width:40%; padding:20px 10px; vertical-align:middle; text-align:center; background-color:{{ config('site.invoice_styles_active_bg_color') }}; color:{{ config('site.invoice_styles_active_text_color') }}; font-weight:300; line-height:30px;">
					@if( isset($invoice->amount_due) )
						<div style="display:inline-block;">
							<div style="font-size:16px; line-height:20px; margin-bottom:5px;">
								{{ trans('invoices.amount_due') }}
							</div>
							<div style="font-size:30px;">
								@if( $invoice->amount_due != 0 )
									{{ App\Helpers\SiteHelper::formatPrice($invoice->amount_due) }}
								@else
									{{ trans('invoices.invoice_paid') }}
								@endif
							</div>
						</div>
					@endif
				</td>
			</tr>
		</table>
	</div>

	@if( $invoice->type == 'group' && ($invoice->logo || $invoice->bill_from) )
		<div style="padding:15px 0px 15px 0px; border-bottom:1px solid #E8E8E8;">
			<table border="0" cellspacing="0" cellpadding="0" style="font-size:14px; line-height:20px;" autosize="1">
				<tr>
					@if( $invoice->logo )
						<td style="padding:4px 10px; vertical-align:top; text-align:left;">
							<img src="{{ asset_url('uploads','logo/'. $invoice->logo) }}" style="max-height:100px; max-width:400px; margin-right:40px;"/>
						</td>
					@endif
					@if( $invoice->bill_from )
						<td style="padding:4px 10px; vertical-align:top; text-align:left;">
							{{--<b>{{ trans('invoices.bill_from') }}:</b><br>--}}
							{!! $invoice->bill_from !!}
						</td>
					@endif
				</tr>
			</table>
		</div>
	@endif

	<div style="padding:15px 0px 15px 0px;">
		<table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
			<tr>
				<td width="60%" style="width:60%; padding:4px 10px; vertical-align:top; text-align:left;">
					@if( $invoice->bill_to )
						<b>{{ trans('invoices.bill_to') }}:</b><br>
						{!! $invoice->bill_to !!}
					@endif
				</td>
				<td width="40%" style="width:40%; padding:0px 10px; vertical-align:top; text-align:center;">
					<table border="0" cellspacing="0" cellpadding="0" align="center" style="font-size:14px; line-height:20px;" autosize="1" class="small-devices-innertable">
						@if( $invoice->invoice_number )
							<tr>
								<td style="padding:4px 10px; vertical-align:top; text-align:right;">
									<b>{{ trans('invoices.invoice_number') }}:</b>
								</td>
								<td style="padding:4px 10px; vertical-align:top; text-align:left;">
									{{ $invoice->invoice_number }}
								</td>
							</tr>
						@endif
						@if( $invoice->invoice_date )
							<tr>
								<td style="padding:4px 10px; vertical-align:top; text-align:right;">
									<b>{{ trans('invoices.invoice_date') }}:</b>
								</td>
								<td style="padding:4px 10px; vertical-align:top; text-align:left;">
									{{ $invoice->invoice_date }}
								</td>
							</tr>
						@endif
						@if( $invoice->payment_date )
							<tr>
								<td style="padding:4px 10px; vertical-align:top; text-align:right;">
									<b>{{ trans('invoices.payment_date') }}:</b>
								</td>
								<td style="padding:4px 10px; vertical-align:top; text-align:left;">
									{{ $invoice->payment_date }}
								</td>
							</tr>
						@endif
					</table>
				</td>
			</tr>
		</table>
	</div>

	<div style="padding:15px 0px 15px 0px; border-bottom:1px solid #E8E8E8;">
		<table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
			<tr>
				{{--
				<th width="10%" style="width:10%; padding:5px 10px; vertical-align:top; text-align:left; font-weight:bold;">
					{{ trans('invoices.index') }}
				</th>
				--}}
				<th width="80%" style="width:80%; padding:5px 10px; vertical-align:top; text-align:left; font-weight:bold;">
					{{ trans('invoices.description') }}
				</th>
				<th width="20%" style="width:20%; padding:5px 10px; vertical-align:top; text-align:right; font-weight:bold;">
					{{ trans('invoices.amount') }}
				</th>
			</tr>
		</table>
		@foreach( $invoice->items as $item )
			<table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
				<tr>
					{{--
					@if( $loop->iteration % 2 == 0 ) style="background:#f5f5f5;" @endif
					<td width="10%" style="width:10%; padding:5px 10px; vertical-align:top; text-align:left;">
						{{ $loop->iteration }}
					</td>
					--}}
					<td width="80%" style="width:80%; padding:5px 10px; vertical-align:top; text-align:left;">
						<span style="display:none; font-weight:bold;" class="small-devices">{{ trans('invoices.description') }}</span>
						@if( $item->name )
							<div style="font-weight:normal;">{!! $item->name !!}</div>
						@endif
						@if( $item->description )
							<div>{!! $item->description !!}</div>
						@endif
					</td>
					<td width="20%" style="width:20%; padding:5px 10px; vertical-align:top; text-align:right;">
						<span style="display:none; font-weight:bold;" class="small-devices">{{ trans('invoices.amount') }}</span>
						{{ App\Helpers\SiteHelper::formatPrice($item->amount) }}
					</td>
				</tr>
			</table>
		@endforeach
	</div>

	<div style="padding:15px 0px 15px 0px;">
		<table border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%; font-size:14px; line-height:20px;" autosize="1">
			<tr>
				<td>
					@if( !empty($invoice->payments) )
						<table border="0" cellspacing="0" cellpadding="0" align="left" style="float:left; font-size:14px; line-height:20px;" autosize="1">
							<tr>
								<td colspan="2" style="padding:4px 10px; vertical-align:top; text-align:left; font-weight:bold;">
									{{ trans('invoices.payments') }}:
								</td>
							</tr>
							@foreach( $invoice->payments as $payment )
								<tr>
									<td style="padding:4px 10px; vertical-align:top; text-align:left;">
										<span>{!! $payment->title !!} ( {!! $payment->name !!} )</span>
										<span style="font-size:14px;">- {!! $payment->status !!}</span>:
									</td>
									<td colspan="2" style="padding:4px 10px; vertical-align:top; text-align:left;">
										{{ App\Helpers\SiteHelper::formatPrice($payment->total) }}
									</td>
								</tr>
							@endforeach
						</table>
					@endif
				</td>
				<td>
					<table border="0" cellspacing="0" cellpadding="0" align="right" style="float:right; font-size:14px; line-height:20px;" autosize="1">
						@if( $invoice->discount || $invoice->payment_charge )
							@if( $invoice->subtotal )
								<tr>
									<td style="padding:4px 10px; vertical-align:top; text-align:right;">
										{{ trans('invoices.subtotal') }}:
									</td>
									<td style="padding:4px 10px; vertical-align:top; text-align:right;">
										{{ App\Helpers\SiteHelper::formatPrice($invoice->subtotal) }}
									</td>
								</tr>
							@endif
							@if( $invoice->discount )
								<tr>
									<td style="padding:4px 10px; vertical-align:top; text-align:right;">
										{{ trans('invoices.discount') }}:
									</td>
									<td style="padding:4px 10px; vertical-align:top; text-align:right;">
										-{{ App\Helpers\SiteHelper::formatPrice($invoice->discount) }}
									</td>
								</tr>
							@endif
							@if( $invoice->payment_charge )
								<tr>
									<td style="padding:4px 10px; vertical-align:top; text-align:right;">
										{{ trans('invoices.payment_charge') }}:
									</td>
									<td style="padding:4px 10px; vertical-align:top; text-align:right;">
										{{ App\Helpers\SiteHelper::formatPrice($invoice->payment_charge) }}
									</td>
								</tr>
							@endif
						@endif
						<tr>
							<td style="padding:4px 10px; vertical-align:top; text-align:right; font-weight:bold;">
								{{ trans('invoices.total') }}:
							</td>
							<td style="padding:4px 10px; vertical-align:top; text-align:right; font-weight:bold;">
								{{ App\Helpers\SiteHelper::formatPrice($invoice->total) }}
							</td>
						</tr>
						@if( !empty($invoice->taxes) )
							@foreach( $invoice->taxes as $tax )
								<tr>
									<td colspan="2" style="padding:4px 10px; vertical-align:top; text-align:right; font-size:12px;">
										{{ trans('invoices.tax_included') }} {{ $tax->name }} ( {{ $tax->percent }}% ) {{ App\Helpers\SiteHelper::formatPrice($tax->amount) }}
									</td>
								</tr>
							@endforeach
						@endif
					</table>
				</td>
			</tr>
		</table>
	</div>

	@if( $invoice->additional_info )
		<div style="padding:15px 10px 15px 10px;">
			{!! $invoice->additional_info !!}
		</div>
	@endif

	@if( $invoice->type == 'single' && ($invoice->logo || $invoice->bill_from) )
		<div style="padding:15px 0px 15px 0px; border-top:1px solid #E8E8E8;">
			<table border="0" cellspacing="0" cellpadding="0" style="font-size:14px; line-height:20px;" autosize="1">
				<tr>
					@if( $invoice->logo )
						<td style="padding:4px 10px; vertical-align:top; text-align:left;">
							<img src="{{ asset_url('uploads','logo/'. $invoice->logo) }}" style="max-height:100px; max-width:400px; margin-right:40px;"/>
						</td>
					@endif
					@if( $invoice->bill_from )
						<td style="padding:4px 10px; vertical-align:top; text-align:left;">
							{{--<b>{{ trans('invoices.bill_from') }}:</b><br>--}}
							{!! $invoice->bill_from !!}
						</td>
					@endif
				</tr>
			</table>
		</div>
	@endif

</body>
</html>
