<?php

$headers = [];
$headers['created_date'] = trans('admin/bookings.created_at');
$headers['ref_number'] = trans('admin/bookings.ref_number');
$headers['status'] = trans('admin/bookings.status');
$headers['date'] = trans('admin/bookings.date');
$headers['flight_number'] = trans('admin/bookings.flight_number');
$headers['flight_landing_time'] = trans('admin/bookings.flight_landing_time');
$headers['departure_city'] = trans('admin/bookings.departure_city');
$headers['departure_flight_number'] = trans('admin/bookings.departure_flight_number');
$headers['departure_flight_time'] = trans('admin/bookings.departure_flight_time');
$headers['departure_flight_city'] = trans('admin/bookings.departure_flight_city');
$headers['contact_mobile'] = trans('admin/bookings.contact_mobile');
$headers['from'] = trans('admin/bookings.from');
$headers['to'] = trans('admin/bookings.to');
$headers['waypoints'] = trans('admin/bookings.via');
$headers['total'] = trans('admin/bookings.total');
// $headers['price'] = trans('admin/bookings.price');
// $headers['discount'] = trans('admin/bookings.discount_price');
// $headers['discount_code'] = trans('admin/bookings.discount_code');
$headers['commission'] = trans('admin/bookings.commission');
$headers['cash'] = trans('admin/bookings.cash');

if (config('eto.allow_fleet_operator')) {
		$headers['fleet_name'] = trans('admin/bookings.fleet_name');
		$headers['fleet_commission'] = trans('admin/bookings.fleet_commission');
}

$headers['driver_name'] = trans('admin/bookings.driver_name');
$headers['vehicle_name'] = trans('admin/bookings.vehicle_name');
$headers['vehicle'] = trans('admin/bookings.vehicle');
$headers['route'] = trans('admin/bookings.route');
$headers['waiting_time'] = trans('admin/bookings.waiting_time');
$headers['contact_name'] = trans('admin/bookings.contact_name');
$headers['contact_email'] = trans('admin/bookings.contact_email');
$headers['meet_and_greet'] = trans('admin/bookings.meet_and_greet');

if (config('site.allow_services')) {
		$headers['service_id'] = trans('admin/bookings.service_type');
		$headers['service_duration'] = trans('admin/bookings.service_duration');
}

$headers['source'] = trans('admin/bookings.source');
$headers['user_name'] = trans('admin/bookings.user_name');
$headers['department'] = trans('booking.departments');
$headers['lead_passenger_name'] = trans('admin/bookings.lead_passenger_name');
$headers['lead_passenger_email'] = trans('admin/bookings.lead_passenger_email');
$headers['lead_passenger_mobile'] = trans('admin/bookings.lead_passenger_mobile');
$headers['tracking_history'] = trans('booking.heading.status_history');
$headers['modified_date'] = trans('admin/bookings.updated_at');

if ($page_type == 'trash') {
		$headers['deleted_at']  = trans('admin/bookings.deleted_at');
}

$headers['id'] = trans('admin/bookings.id');
$headers['custom'] = !empty(config('eto_booking.custom_field.name')) ? config('eto_booking.custom_field.name') : trans('booking.customPlaceholder');
// $headers['user_id'] = trans('admin/bookings.user_id');
// $headers['driver_id'] = trans('admin/bookings.driver_id');
// $headers['vehicle_id'] = trans('admin/bookings.vehicle_id');
// $headers['site_id'] = trans('admin/bookings.site_id');

$cells = [];

$eColumns = $etoPost['columns'] ?: [];
$eColumnsVisibility = explode(',', $etoPost['columnsVisibility'] ?: []);
$eColumnsState = [];
if (!empty($eColumns) && !empty($eColumnsVisibility)) {
		foreach($eColumnsVisibility as $kV => $vV) {
				if (!empty($eColumns[$kV]['data'])) {
						$index = $eColumns[$kV]['data'];
						$eColumnsState[$index] = $vV == 'true' ? true : false;
				}
		}
}

$filteredHeaders = [];
foreach ($headers as $hK => $hV) {
		$isVisible = isset($eColumnsState[$hK]) && $eColumnsState[$hK] === true ? true : false;

		if ( $hK == 'fleet_name' && (isset($eColumnsState['fleet_btn']) && $eColumnsState['fleet_btn'] === true) ) {
				$isVisible = true;
		} elseif ( $hK == 'fleet_commission' && (isset($eColumnsState['fleet_commission_btn']) && $eColumnsState['fleet_commission_btn'] === true) ) {
				$isVisible = true;
		} elseif ( $hK == 'driver_name' && (isset($eColumnsState['driver_btn']) && $eColumnsState['driver_btn'] === true) ) {
				$isVisible = true;
		} elseif ( $hK == 'vehicle_name' && (isset($eColumnsState['vehicle_btn']) && $eColumnsState['vehicle_btn'] === true) ) {
				$isVisible = true;
		} elseif ( $hK == 'status' && (isset($eColumnsState['status_btn']) && $eColumnsState['status_btn'] === true) ) {
				$isVisible = true;
		} elseif ( $hK == 'cash' && (isset($eColumnsState['cash_btn']) && $eColumnsState['cash_btn'] === true) ) {
				$isVisible = true;
		} elseif ( $hK == 'commission' && (isset($eColumnsState['commission_btn']) && $eColumnsState['commission_btn'] === true) ) {
				$isVisible = true;
		} elseif ( in_array($hK, ['route', 'tracking_history', 'deleted_at']) ) {
				$isVisible = true;
		}
		if ($isVisible) {
				$filteredHeaders[$hK] = $hV;
		}
}

$mapKeys = [
	 'fleet_btn' => 'fleet_name',
	 'fleet_commission_btn' => 'fleet_commission',
	 'driver_btn' => 'driver_name',
	 'vehicle_btn' => 'vehicle_name',
	 'status_btn' => 'status',
	 'cash_btn' => 'cash',
	 'commission_btn' => 'commission',
];

$filteredHeadersAll = [];
foreach ($eColumnsState as $hK => $hV) {
		if (array_key_exists($hK, $mapKeys)) {
				$hK = $mapKeys[$hK];
		}
		if (array_key_exists($hK, $filteredHeaders)) {
				$filteredHeadersAll[$hK] = $filteredHeaders[$hK];
		}
}
foreach ($filteredHeaders as $hK => $hV) {
		if (!array_key_exists($hK, $filteredHeadersAll)) {
				$filteredHeadersAll[$hK] = $hV;
		}
}
$headers = $filteredHeadersAll;
// dd($eColumns, $eColumnsVisibility, $eColumnsState, $filteredHeaders);

function arrayValuePosition($value, $array) {
		return array_search($value, array_keys($array));
}
$priceIndexes = [];
$lastIndex = '';
if (array_key_exists('total', $headers)) {
		$lastIndex = 'total';
		$priceIndexes[] = \PHPExcel_Cell::stringFromColumnIndex(arrayValuePosition('total', $headers));
}
if (array_key_exists('fleet_commission', $headers)) {
		$lastIndex = 'fleet_commission';
		$priceIndexes[] = \PHPExcel_Cell::stringFromColumnIndex(arrayValuePosition('fleet_commission', $headers));
}
if (array_key_exists('commission', $headers)) {
		$lastIndex = 'commission';
		$priceIndexes[] = \PHPExcel_Cell::stringFromColumnIndex(arrayValuePosition('commission', $headers));
}
if (array_key_exists('cash', $headers)) {
		$lastIndex = 'cash';
		$priceIndexes[] = \PHPExcel_Cell::stringFromColumnIndex(arrayValuePosition('cash', $headers));
}

if ($lastIndex) {
		function array_splice_after_key($array, $key, $array_to_insert) {
				$key_pos = array_search($key, array_keys($array));
				if ($key_pos !== false) {
						$key_pos++;
						$second_array = array_splice($array, $key_pos);
						$array = array_merge($array, $array_to_insert, $second_array);
				}
				return $array;
		}

		$currency = [];
		if (config('site.currency_symbol')) {
				$currency['currency_symbol'] = trans('admin/bookings.currency_symbol');
		}
		if (config('site.currency_code')) {
				$currency['currency_code'] = trans('admin/bookings.currency_code');
		}
		if (count($currency)) {
				$headers = array_splice_after_key($headers, $lastIndex, $currency);
		}
}

$cells[] = $headers;

foreach ($booking as $bK => $bV) {
		$cell = [];

		foreach ($headers as $hK => $hV) {
				if (array_key_exists($hK, $bV)) {
						$val = $bV[$hK];

						if ($hK != 'tracking_history') {
								$val = trim(strip_tags($val));
								$val = str_replace(array("\r\n", "\r", "\n"), ' ', $val);
						}

						if (in_array($hK, ['total', 'cash', 'commission', 'fleet_commission'])) {
								$val = (float)$val;
								// $val = number_format($val, 2, '.', '');
								// $val = floatval($val);
								// $cell[$hK] = $val ? $val : '0.00';
								$cell[$hK] = $val;
						}
						else {
								$cell[$hK] = $val ? $val : ' ';
						}
				}
		}

		if (isset($cell)) {
				$cells[] = $cell;
		}
}

$filename = 'ETO Bookings '. \Carbon\Carbon::now()->format('Y-m-d H:i:s');

$excel = \Excel::create($filename, function($excel) use ($cells, $priceIndexes) {
		$excel->sheet('Bookings', function($sheet) use ($cells, $priceIndexes) {
				$sheet->fromArray($cells, null, 'A1', true, false);
				$sheet->freezeFirstRow();
				$sheet->row(1, function($row) {
						$row->setBackground('#F5F5F5');
						$row->setFontWeight('bold');
				});
				$sheet->getDefaultStyle()->applyFromArray([
						'alignment' => [
								'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
						]
				]);

				$count = count($cells);
				foreach ($priceIndexes as $k => $v) {
						$sheet->getStyle($v.'2:'.$v.$count)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
						$sheet->getStyle($v.'2:'.$v.$count)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
		});
});

switch ($exportType) {
		case 'xlsx':
				$excel->download('xlsx');
		break;
		case 'xls':
				$excel->download('xls');
		break;
		case 'csv':
				$excel->download('csv');
		break;
		case 'pdf':
				$excel->download('pdf');
		break;
		// case 'ods':
		// 		$excel->download('ods');
		// break;
		// case 'html':
		// 		$excel->download('html');
		// break;
		case 'invoice_send':
		case 'invoice_download':

				$zipList = array();

				if ( $exportType == 'invoice_send' ) {
						class BookingRoute2
						{
								protected $userName = null;
								protected $ref_number = '';

								public function __construct($user, $ref_number) {
										$this->userName = $user;
										$this->ref_number = '';
								}
								public function getContactFullName()
								{
										return $this->userName;
								}
								public function getRefNumber()
								{
										return $this->ref_number;
								}
						}
				}

				$pBookings = [];

				foreach($booking as $key1 => $value1) {
						$pID = (int)$value1['site_id'];
						$uID = (int)$value1['user_id'];

						if ( !isset($pBookings[$pID]) ) {
								$pBookings[$pID] = [];
						}

						if ( !isset($pBookings[$pID][$uID]) ) {
								$pBookings[$pID][$uID] = [];
						}

						$pBookings[$pID][$uID][] = $value1;
				}

				foreach($pBookings as $pK => $pV) {
						if ( $exportType == 'invoice_send' ) {
								$qProfileConfig = \App\Models\Config::getBySiteId($pK)->mapData()->getData();
								$pConfig = (array)$qProfileConfig;

								$eCompany = (object)[
									'name' => $pConfig['company_name'],
									'phone' => $pConfig['company_telephone'],
									'email' => $pConfig['company_email'],
									'address' => \App\Helpers\SiteHelper::nl2br2($pConfig['company_address']),
									'url_home' => $pConfig['url_home'],
									'url_feedback' => $pConfig['url_feedback'],
									'url_contact' => $pConfig['url_contact'],
									'url_booking' => $pConfig['url_booking'],
									'url_customer' => $pConfig['url_customer']
								];

								$eSettings = (object)[
									'booking_summary_enable' => $pConfig['booking_summary_enable'],
								];
						}

						foreach($pV as $uK => $uV) {
								if ( $uK <= 0 ) {
									continue;
								}

								$invoiceREF = '';
								$tmplInvoice = '';
								$sendInvoice = 0;
								$invoiceEnabled = 0;
								$ids = [];

								foreach ($uV as $bK => $bV) {
										$ids[] = $bV['id'];
								}

								if ( $ids ) {
										$inv = (new \App\Models\BookingRoute)->getInvoice('both', $ids);
										$invoiceREF = $inv->raw->invoice_number;
										$tmplInvoice = $inv->html;
										$sendInvoice = 1;
								}

								$filename = (new \App\Models\BookingRoute)->getInvoiceFilename();
								$ext = '.pdf';
								if (in_array($filename.$ext, $zipList)) {
										$filename .= '_'. rand(1000,10000);
								}
								$filename .= $ext;
								$html = $tmplInvoice;

								$mpdf = new \Mpdf\Mpdf([
									'mode' => '',
									'format' => 'A4',
									'default_font_size' => 0,
									'default_font' => '',
									'margin_left' => 0,
									'margin_right' => 0,
									'margin_top' => 0,
									'margin_bottom' => 0,
									'margin_header' => 0,
									'margin_footer' => 0,
									'orientation' => 'P',
								]);
								$mpdf->WriteHTML($html);

								$pdfInvoiceFileName = $filename;

								if ( $exportType == 'invoice_download' ) {
									$pdfInvoice = $mpdf->Output(asset_path('tmp', $filename), \Mpdf\Output\Destination::FILE);
									$zipList[] = $filename;
								}
								else {
									$pdfInvoice = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

									// Attach invoice
									preg_match("~<body.*?>(.*?)<\/body>~is", $tmplInvoice, $match);

									if ( !empty($match[0]) ) {
										$invoiceEnabled = 1;
										$invoice = preg_replace('/(<body.*?>)(.*?)(<\/body>)/s', '$2', $match[0]);
									}
								}

								unset($mpdf);


								// Send invoices
								if ( $sendInvoice && $exportType == 'invoice_send' ) {
										$customer = \DB::table('user')
											->join('user_customer', 'user.id', '=', 'user_customer.user_id')
											->select('user.*', 'user_customer.title')
											->where('user.id', $uK)
											->first();

										$eBooking = new BookingRoute2( trim(ucfirst($customer->title) .' '. ucfirst($customer->name)), $invoiceREF);

										$sender = [
												$eCompany->email,
												$eCompany->name
										];

										$recipient = [
												$customer->email,
												$customer->name
										];

										$subject = trans('invoices.invoice') .' '. $invoiceREF;

										try {
												$sent = \Mail::send([
													'html' => 'emails.customer_payment_confirmed',
													// 'text' => 'emails.customer_payment_confirmed_plain'
												], [
													'subject' => $subject,
													'additionalMessage' => '',
													'company' => $eCompany,
													'settings' => $eSettings,
													'booking' => $eBooking,
													'invoice' => $invoice,
													'invoiceEnabled' => $invoiceEnabled
												],
													function ($message) use ($sender, $recipient, $subject, $invoiceEnabled, $pdfInvoice, $pdfInvoiceFileName) {
														$message->from($sender[0], $sender[1])
															->to($recipient[0], $recipient[1])
															->subject($subject);

														if ( !empty($invoiceEnabled) ) {
															$message->attachData($pdfInvoice, $pdfInvoiceFileName, [
																'mime' => 'application/pdf',
															]);
														}
													}
												);

												$sent = true;
										}
										catch (\Exception $e) {
												$sent = false;
										}

										$data['email_status'][] = 'Email status: '. ($sent == true ? 'OK' : 'Failed') .' | '. $customer->email .' | '. $customer->name;
								}
								// end
						}
				}

				if ( $exportType == 'invoice_download' && count($zipList) > 0 ) {
						$file_path = asset_path('tmp', '') .'/';

						if ( count($zipList) > 1 ) { // Multiply

								$archive_file_name = 'Invoices_'. date("Y-m-d_H-i",time()) .'.zip';

								$zip = new \ZipArchive();
								if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE )!==TRUE) {
									exit("cannot open <$archive_file_name>\n");
								}
								foreach($zipList as $files) {
									if ( file_exists($file_path.$files) ) {
										$zip->addFile($file_path.$files,$files);
									}
								}
								$zip->close();

								header("Content-Description: File Transfer");
								header("Content-type: application/zip");
								header("Content-Type: application/force-download");
								header("Content-Disposition: attachment; filename=". urlencode(basename($archive_file_name)));
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Pragma: public');
								header("Content-Length:". filesize($archive_file_name));
								ob_clean();
								flush();
								readfile($archive_file_name);

								// Now delete the temp file (some servers need this option)
								@unlink($archive_file_name);
								foreach($zipList as $files) {
										@unlink($file_path.$files);
								}
								exit;

						}
						else { // Single

								$file = $file_path.$zipList[0];

								if (file_exists($file)) {
										header('Content-Description: File Transfer');
										header('Content-Type: application/octet-stream');
										header("Content-Type: application/force-download");
										// header('Content-Disposition: attachment; filename='. urlencode(basename($file)));
										header('Content-Disposition: attachment; filename='. $filename);
										header('Expires: 0');
										header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
										header('Pragma: public');
										header('Content-Length:'. filesize($file));
										ob_clean();
										flush();
										readfile($file);

										@unlink($file);
										exit;
								}

						}
				}

		break;
}
