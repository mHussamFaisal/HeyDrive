<?php

$sql = "SELECT `a`.*,
				`b`.*,
				`a`.`id` AS `route_id`,
				`a`.`ref_number` AS `route_ref_number`, (
					SELECT `name`
					FROM `{$dbPrefix}user`
					WHERE `id`=`b`.`user_id`
					AND `site_id`=`b`.`site_id`
					LIMIT 1
				 ) AS `user_name`
		FROM `{$dbPrefix}booking_route` AS `a`
		LEFT JOIN `{$dbPrefix}booking` AS `b`
		ON `a`.`booking_id`=`b`.`id`
		WHERE 1 ";

if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
		$sql .= " AND `a`.`fleet_id`='". auth()->user()->id ."' ";
}

$sql .= " ". $sqlSearch . " " . $sqlSearchFilter . " ORDER BY " . $sqlSort . " `a`.`date` ASC";

$querySummary = $db->select($sql);

if ( !empty($querySummary) ) {
	$drivers = [];
	$payments = [];
	$unassignedList = [];

	foreach($querySummary as $key => $value) {
		// Drivers
		if ( empty($drivers[$value->driver_id]) ) {
			$driver = \App\Models\User::find($value->driver_id);

			if ( !empty($driver) ) {
				$name = $driver->name;
				$percent = $driver->profile->commission;
			}
			else {
				$name = 'Unassigned';
				$percent = 0;
			}

			$drivers[$value->driver_id] = [
				'id' => $value->driver_id,
				'name' => $name,
				'percent' => $percent,
				'cash' => 0,
				'commission' => 0,
			];
		}
		$drivers[$value->driver_id]['cash'] += $value->cash;
		$drivers[$value->driver_id]['commission'] += $value->commission;

		// Payments
		$returnRoute = \App\Models\BookingRoute::where('booking_id', $value->booking_id)->where('id', '!=', $value->route_id)->first();

		if ( !empty($returnRoute) ) {
			$returnTotalWithDiscount = $returnRoute->total_price - $returnRoute->discount;
		}
		else {
			$returnTotalWithDiscount = 0;
		}

		$totalWithDiscount = $value->total_price - $value->discount;
		$paymentCharge = 0;
		$paymentTotal = 0;

		$transactions = \App\Models\Transaction::where('relation_type', 'booking')->where('relation_id', $value->booking_id)->get();

		foreach($transactions as $transaction) {
			if ( empty($payments[$transaction->payment_id]) ) {
				if ( $transaction->payment_method == 'none' ) {
					$transaction->payment_name = 'Unassigned<br>Payment Method';
				}

				$payments[$transaction->payment_id] = [
					'id' => $transaction->payment_id,
					'name' => $transaction->payment_name,
					'method' => $transaction->payment_method,
					'status' => [],
				];
			}

			$status = '';
			foreach ($transaction->options->status as $k => $v) {
				if ( $k == $transaction->status ) {
					$status = $v['name'];
				}
			}

			if ( !empty($returnRoute) && ($totalWithDiscount + $returnTotalWithDiscount) ) {
				$grandTotalWithDiscount = $totalWithDiscount + $returnTotalWithDiscount;
				$amount = ($transaction->amount / $grandTotalWithDiscount) * $totalWithDiscount;
				$charge = ($transaction->payment_charge / $grandTotalWithDiscount) * $totalWithDiscount;
			}
			else {
				$amount = $transaction->amount;
				$charge = $transaction->payment_charge;
			}

			$amount = round($amount, 2);
			$charge = round($charge, 2);
			$total = round($amount + $charge, 2);

			$paymentCharge += $charge;
			$paymentTotal += $total;

			$payments[$transaction->payment_id]['status'][$transaction->status]['name'] = $status;
			$payments[$transaction->payment_id]['status'][$transaction->status]['amount'] += $amount;
			$payments[$transaction->payment_id]['status'][$transaction->status]['charge'] += $charge;
			$payments[$transaction->payment_id]['status'][$transaction->status]['total'] += $total;
		}

		$total = round($totalWithDiscount + $paymentCharge, 2);
		$paymentTotal = round($paymentTotal, 2);
		$remaining = $paymentTotal - $total;

		if ( $total != $paymentTotal ) {
			$unassignedList[] = [
				'ref_number' => $value->route_ref_number,
				'amount' => $remaining,
			];
		}
	}

	// Driver report
	$html = '';
	$totalCash = 0;
	$totalCommission = 0;

	foreach($drivers as $key => $value) {
		$cash = round($value['cash'], 2);
		$commission = round($value['commission'], 2);

		$totalCash += $cash;
		$totalCommission += $commission;

		if ( !empty($html) ) {
			$html .= '<tr><td colspan="2" style="padding-top:10px;"></td></tr>';
		}

		$html .= '<tr><td colspan="2"><b>'. $value['name'] .'</b> <span style="color:gray;">( '. $value['percent'] .'% )</span></td></tr>';
		$html .= '<tr>';
			$html .= '<td style="width:200px;">'. trans('admin/bookings.cash') .':</td>';
			$html .= '<td>';
				$formatted = config('site.currency_symbol') . $cash . config('site.currency_code');
				if ( $cash > 0 ) {
					$formatted = '-'. $formatted;
				}
				$html .= '<span style="color:red;">'. $formatted .'</span>';
			$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
			$html .= '<td>'. trans('admin/bookings.commission') .':</td>';
			$html .= '<td>';
				$formatted = config('site.currency_symbol') . $commission . config('site.currency_code');
				if ( $commission > 0 ) {
					$formatted = '+'. $formatted;
				}
				$html .= '<span style="color:green;">'. $formatted .'</span>';
			$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
			$html .= '<td>Balance:</td>';
			$html .= '<td>';
				$html .= config('site.currency_symbol') . ($commission - $cash) . config('site.currency_code');
			$html .= '</td>';
		$html .= '</tr>';
	}

	if ( !empty($drivers) && count($drivers) > 1 ) {
		$html .= '<tr><td colspan="2"><hr><b>All Drivers</b></td></tr>';
		$html .= '<tr>';
			$html .= '<td>'. trans('admin/bookings.cash') .':</td>';
			$html .= '<td>';
				$formatted = config('site.currency_symbol') . $totalCash . config('site.currency_code');
				if ( $totalCash > 0 ) {
					$formatted = '-'. $formatted;
				}
				$html .= '<span style="color:red;">'. $formatted .'</span>';
			$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
			$html .= '<td>'. trans('admin/bookings.commission') .':</td>';
			$html .= '<td>';
				$formatted = config('site.currency_symbol') . $totalCommission . config('site.currency_code');
				if ( $totalCommission > 0 ) {
					$formatted = '+'. $formatted;
				}
				$html .= '<span style="color:green;">'. $formatted .'</span>';
			$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
			$html .= '<td>Balance:</td>';
			$html .= '<td>';
				$html .= config('site.currency_symbol') . ($totalCommission - $totalCash) . config('site.currency_code');
			$html .= '</td>';
		$html .= '</tr>';
	}

	if ( $html ) {
		$html = '<div class="box box-success collapsed-box1" style="margin-top:20px; margin-bottom:0px;">
			<div class="box-header with-border">
				<h3 class="box-title">Driver Report</h3>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse">
						<i class="fa fa-minus"></i>
					</button>
				</div>
			</div>
			<div class="box-body">
				<table>'. $html .'</table>
			</div>
		</div>';
	}

	$htmlDrivers = $html;


	// Payment report
	$html = '';
	$totals = [];

	foreach($payments as $key => $value) {
		if ( !empty($html) ) {
			$html .= '<tr><td colspan="2" style="padding-top:10px;"></td></tr>';
		}

		$html .= '<tr><td colspan="2"><b>'. $value['name'] .'</b></td></tr>';

		ksort($value['status']);

		foreach($value['status'] as $k => $v) {
			if ( empty($totals[$k]) ) {
				$totals[$k] = [
					'name' => $v['name'],
					'amount' => 0,
					'charge' => 0,
					'total' => 0,
				];
			}

			$totals[$k]['amount'] += $v['amount'];
			$totals[$k]['charge'] += $v['charge'];
			$totals[$k]['total'] += $v['total'];

			$html .= '<tr>';
				$html .= '<td style="width:200px;">'. $v['name'] .':</td>';
				$html .= '<td>'. config('site.currency_symbol') . $v['total'] . config('site.currency_code') .'</td>';
			$html .= '</tr>';
		}
	}

	if ( $totals ) {
		$html .= '<tr><td colspan="2"><hr><b>All Payments</b></td></tr>';
	}

	if ( $totals ) {
		foreach($totals as $key => $value) {
			$html .= '<tr>';
				$html .= '<td>'. $value['name'] .':</td>';
				$html .= '<td>'. config('site.currency_symbol') . $value['total'] . config('site.currency_code') .'</td>';
			$html .= '</tr>';
		}
	}

	if ( !empty($unassignedList) ) {
		$info = '';
		foreach($unassignedList as $k => $v) {
			$formatted = config('site.currency_symbol') . $v['amount'] . config('site.currency_code');
			if ( $v['amount'] > 0 ) {
				$formatted = '<span style="color:green;">'. $formatted .'</span>';
			}
			else {
				$formatted = '<span style="color:red;">'. $formatted .'</span>';
			}
			$info .= '#'. $v['ref_number'] .': '. $formatted .'<br>';
		}
		$info = '<div style=\'text-align:left;\'>'. $info .'</div>';

		$html .= '<tr>';
			$html .= '<td colspan="2">';
				$html .= '<br><span style="color:#f39c12;">Warning! There are some bookings where total price do not match payment transactions, this might cause miscalculation.<br>Please click info icon to see booking reference number and missing amount.</span>';
				$html .= '<i class="fa fa-info-circle" title="Show / Hide" style="margin-left:5px; font-size:20px; cursor:pointer;" onclick="$(\'#missing_amounts\').toggle();"></i>';
				$html .= '<div id="missing_amounts" style="display:none; margin-top:10px;">'. $info .'</div>';
			$html .= '</td>';
		$html .= '</tr>';
	}

	if ( $html ) {
		$html = '<div class="box box-success collapsed-box1" style="margin-top:0px;">
			<div class="box-header with-border">
				<h3 class="box-title">Payment Report</h3>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse">
						<i class="fa fa-minus"></i>
					</button>
				</div>
			</div>
			<div class="box-body">
				<table>'. $html .'</table>
			</div>
		</div>';
	}

	$htmlPayments = $html;

	// Display
	$html = '<div style="color:#d47f01; margin-bottom:20px; border:1px #ffcb7e solid; padding:20px; background:#ffedd3;"><b>Important!</b> Please note that in the next upcoming update this report section will be permanently replaced by new report module available in the sidebar menu. New report module offers more options and will be expanded with new functionalities in the future, therefore we strongly recommend to start using this module instead. Your feedback will be greatly appreciated and you can send it to this email address <a href="mailto:support@easytaxioffice.co.uk" style="color:#d47f01; text-decoration:underline;">support@easytaxioffice.co.uk</a>. Thank you.</div>';
	$html .= $htmlPayments .''. $htmlDrivers;

	$data['summary'] = $html;
}
