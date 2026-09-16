<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportExportController extends Controller
{
    private $filename;
    private $folder;
    private $format;

    public function exportFleet($id = false, $fleet = false, $format = 'xslx')
    {
        if (!auth()->user()->hasPermission('admin.reports.export')) {
            return redirect_no_permission();
        }

        if (request()->isMethod('get') && $report = Report::getReports(false, $id)->first()) {
            $report->renderReports($report->type);
        }
        elseif (request()->isMethod('post') && !empty(request('data'))) {
            $format = $fleet;
            $fleet = $id;
            $report = json_decode(request('data'));
        }

        if (!empty($report)) {
            $this->generateName();
            $this->format = $format;

            if (in_array($format, ['xls', 'xlsx'])) {
                config(['excel.export.store.path' => $this->folder]);
                return $this->generateExcelFleets($report, $format, $fleet);
            }
            elseif ($format == 'pdf') {
                return $this->generatePDFFleets($report, $fleet);
            }
        }

        abort(404);
    }

    public function exportDriver($id = false, $driver = false, $format = 'xslx')
    {
        if (!auth()->user()->hasPermission('admin.reports.export')) {
            return redirect_no_permission();
        }

        if (request()->isMethod('get') && $report = Report::getReports(false, $id)->first()) {
            $report->renderReports($report->type);
        }
        elseif (request()->isMethod('post') && !empty(request('data'))) {
            $format = $driver;
            $driver = $id;
            $report = json_decode(request('data'));
        }

        if (!empty($report)) {
            $this->generateName();
            $this->format = $format;

            if (in_array($format, ['xls', 'xlsx'])) {
                config(['excel.export.store.path' => $this->folder]);
                return $this->generateExcelDrivers($report, $format, $driver);
            }
            elseif ($format == 'pdf') {
                return $this->generatePDFDrivers($report, $driver);
            }
        }

        abort(404);
    }

    public function exportAll($id = false, $format = 'xslx')
    {
        if (!auth()->user()->hasPermission('admin.reports.export')) {
            return redirect_no_permission();
        }

        if (request()->isMethod('get') && $report = Report::getReports(false, $id)->first()) {
            $report->renderReports($report->type);
        }
        elseif (request()->isMethod('post') && !empty(request('data'))) {
            $format = $id;
            $report = json_decode(request('data'));
        }

        if (!empty($report)) {
            $this->generateName();
            $this->format = $format;
            if (in_array($format, ['xls', 'xlsx'])) {
                config(['excel.export.store.path' => $this->folder]);
                if ($report->type == 'fleet') {
                    return $this->generateExcelFleets($report, $format);
                }
                elseif ($report->type == 'driver') {
                    return $this->generateExcelDrivers($report, $format);
                }
                elseif ($report->type == 'customer') {
                    // return $this->generateExcelCustomers($report, $format);
                }
                elseif ($report->type == 'payment') {
                    return $this->generateExcelPayments($report, $format);
                }
            }
            elseif ($format == 'pdf') {
                if ($report->type == 'fleet') {
                    return $this->generatePDFFleets($report);
                }
                elseif ($report->type == 'driver') {
                    return $this->generatePDFDrivers($report);
                }
                elseif ($report->type == 'customer') {
                    // return $this->generatePDFCustomers($report, $format);
                }
                elseif ($report->type == 'payment') {
                    return $this->generatePDFPayments($report);
                }
            }
        }

        abort(404);
    }

    public function downloadTempFile($fileName)
    {
        if (!auth()->user()->hasPermission('admin.reports.export')) {
            return redirect_no_permission();
        }

        $parh = parse_path('tmp/' . $fileName);

        return response()->download($parh, $fileName)->deleteFileAfterSend(true);
    }

    private function generateExcelFleets($report, $format, $exportFleetId = false)
    {
        $headers = $this->headersFleets();
        $fleetcells = [];

        foreach ($report->fleets as $fleetId=>$fleet) {
            $totalTransactionsPaid = 0;
            $total = 0;
            $cash = 0;
            $commission = 0;
            $fleetTransactionStatuses = new \stdClass();

            if ( $exportFleetId !== false && (int)$exportFleetId !== (int)$fleetId) {
                continue;
            }

            $fleetcells[$fleetId][] = [
                0 => trans('reports.form.fleet') . ': ' . $fleet->name
            ];
            $fleetcells[$fleetId][] = [
                0 => trans('reports.fleet_income_set_to') . ' ' . $fleet->percent . '%'
            ];
            $fleetcells[$fleetId][] = [];
            $fleetcells[$fleetId][] = $headers;

            foreach ($fleet->bookings as $id=>$index) {
                $booking = $report->bookings->$index;
                $cell = [
                    'ref_number' => $booking->ref_number,
//                    'total' => 0,
//                    'company_take' => '',
                    'commission' => format_price($booking->fleet_commission),
                ];

                $commission += $booking->fleet_commission;

                if ((int)$report->version > 1
                    && config('eto_report.export.total') === true
                ) {
                    $cell['total'] = format_price($booking->total_price);
                    $total += $booking->total_price;
                }
                else {
                    unset($cell['total']);
                }
                if ((int)$report->version > 1
                    && config('eto_report.export.company_take') === true
                ) {
                    $report->status_colors = json_decode(json_encode($report->status_colors), true);
                    $companyTake = [];

                    foreach ($booking->transactions as $paymentId=>$statuses) {
                        $paymentName = $report->payments->$paymentId->name;

                        foreach ($statuses as $status=>$values) {
                            $statusName = $report->status_colors[$status]['name'];
                            $totalTransaction = (float)$values->amount + (float)$values->payment_charge;

                            if (!isset($fleetTransactionStatuses->$fleetId)) {
                                $fleetTransactionStatuses->$fleetId = new \stdClass();
                            }
                            if (!isset($fleetTransactionStatuses->$fleetId->status)) {
                                $fleetTransactionStatuses->$fleetId->status = 0;
                            }

                            $fleetTransactionStatuses->$fleetId->status += $totalTransaction;

                            if ($status == 'paid') {
                                $totalTransactionsPaid += $totalTransaction;
                            }
                            $companyTake[$paymentName. "-" .$statusName] = format_price($totalTransaction) . " (" .$paymentName. " - " .$statusName. ") ";
                        }
                    }

                    $cell['company_take'] = implode(PHP_EOL, array_unique($companyTake));
                }
                else {
                    unset($cell['company_take']);
                }

                $fleetcells[$fleetId][] = $cell;
            }
            $totalRow = [
                'ref_number' => trans('reports.columns.total'),
//                'total' => format_price($total),
//                'company_take' => format_price($totalTransactionsPaid),
                'commission' => format_price($commission),
            ];
//            if (config('eto_report.export.total') !== true) {
//                unset($totalRow['total']);
//            }
//            if (config('eto_report.export.company_take') !== true) {
//                unset($totalRow['company_take']);
//            }

            $whoPay = $commission > 0
                ? trans('reports.company_payd_fleet')
                : trans('reports.fleet_payd_company');

            $fleetcells[$fleetId][] = $totalRow;
            $fleetcells[$fleetId][] = [];
            $fleetcells[$fleetId][] = [
                0 => trans('reports.final_balance') . ': ' . $whoPay . ' ' . format_price(abs($commission)),
            ];
        }

        $excel = \Excel::create($this->filename, function ($excel) use ($report, $fleetcells) {
            $cols = [
                'A','B',/*'C','D','E'*/
            ];
            $lastColl = 1; /*4*/

//            if (config('eto_report.export.total') !== true) {
//                $lastColl--;
//            }
//
//            if (config('eto_report.export.company_take') !== true) {
//                $lastColl--;
//            }

            $report->fleets = (object)$report->fleets;
            $fleetsList = [];
            foreach ($report->fleets as $key => $value) {
                $fleetsList[(int)$key] = $value;
            }

            foreach($fleetcells as $fleetId => $cells) {
                $name = $fleetsList[$fleetId]->name;
                $excel->sheet($name, function ($sheet) use ($cells, $cols, $lastColl) {
                    $sheet->fromArray($cells, null, 'A1', false, false);
                    $sheet->row(4, function ($row) {
                        $row->setBackground('#F5F5F5');
                        $row->setFontWeight('bold');
                    });
                    $sheet->row((count((array)$sheet->data)-2), function ($row) {
                        $row->setBackground('#F5F5F5');
                        $row->setFontWeight('bold');
                    });
                    $sheet->row(count((array)$sheet->data), function ($row) {
                        $row->setBackground('#F5F5F5');
                    });
                    $sheet->mergeCells('A1:'.$cols[$lastColl].'1');
                    $sheet->mergeCells('A2:'.$cols[$lastColl].'2');
                    $sheet->mergeCells('A' . count((array)$sheet->data) . ':' . $cols[$lastColl] . count((array)$sheet->data));
                    $sheet->getStyle('A' . (count((array)$sheet->data)-2) . ':' . $cols[$lastColl] . (count((array)$sheet->data)-2))
                        ->getBorders()->getTop()->setBorderStyle('1px solid');
                });
            }
        });

        $excel->store($format);

        if (!empty($report->id)) {
            $parh = $this->folder . '/' .$this->filename . '.' . $this->format;
            return response()->download($parh, $this->filename . '.' . $this->format)->deleteFileAfterSend(true);
        }
        else {
            return response()->json(['downloadUrl' => $this->filename . '.' . $this->format], 200);
        }
    }

    private function generatePDFFleets($report, $exportFleetId = false)
    {
        $config = [
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
        ];
        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->WriteHTML(view('reports.fleet_pdf', ['report' => $report, 'exportFleetId' => $exportFleetId])->render());

        if (!empty($report->id)) {
            $mpdf->Output($this->filename . '.' . $this->format, "D");
        }
        else {
            // ob_clean();
            $name = $this->filename . '.' . $this->format;
            \Storage::disk('tmp')->put($name, $mpdf->Output($name, "S"));
            return response()->json(['downloadUrl'=>$name], 200);
        }
    }

    private function headersFleets()
    {
        $headersFleet = [
            'ref_number' => trans('reports.columns.ref_number'),
//            'total' => trans('reports.columns.total'),
//            'company_take' => trans('reports.columns.company_take'),
            'commission' => trans('reports.columns.fleet_commission'),
        ];

//        if (config('eto_report.export.total') !== true) {
//            unset($headersFleet['total']);
//        }
//
//        if (config('eto_report.export.company_take') !== true) {
//            unset($headersFleet['company_take']);
//        }

        return$headersFleet;
    }

    private function generateExcelDrivers($report, $format, $exportDriverId = false)
    {
        $headers = $this->headersDriver();
        $drivercells = [];

        if ((int)$report->version === 1) {
            unset($headers['total']);
            unset($headers['company_take']);
        }

        foreach ($report->drivers as $driverId=>$driver) {
            $totalTransactionsPaid = 0;
            $total = 0;
            $cash = 0;
            $commission = 0;
            $driverTransactionStatuses = new \stdClass();

            if ( $exportDriverId !== false && (int)$exportDriverId !== (int)$driverId) {
                continue;
            }

            $drivercells[$driverId][] = [
                0 => trans('reports.form.driver') . ': ' . $driver->name
            ];
            $drivercells[$driverId][] = [
                0 => trans('reports.driver_income_set_to') . ' ' . $driver->percent . '%'
            ];
            $drivercells[$driverId][] = [];
            $drivercells[$driverId][] = $headers;

            foreach ($driver->bookings as $id=>$index) {
                $booking = $report->bookings->$index;
                $cell = [
                    'ref_number' => $booking->ref_number,
                    'total' => 0,
                    'company_take' => '',
                    'cash' => format_price($booking->cash),
                    'commission' => format_price($booking->commission),
                ];

                $cash += $booking->cash;
                $commission += $booking->commission;

                if ((int)$report->version > 1
                    && config('eto_report.export.total') === true
                ) {
                    $cell['total'] = format_price($booking->total_price);
                    $total += $booking->total_price;
                }
                else {
                    unset($cell['total']);
                }
                if ((int)$report->version > 1
                    && config('eto_report.export.company_take') === true
                ) {
                    $report->status_colors = json_decode(json_encode($report->status_colors), true);
                    $companyTake = [];

                    foreach ($booking->transactions as $paymentId=>$statuses) {
                        $paymentName = $report->payments->$paymentId->name;

                        foreach ($statuses as $status=>$values) {
                            $statusName = $report->status_colors[$status]['name'];
                            $totalTransaction = (float)$values->amount + (float)$values->payment_charge;

                            if (!isset($driverTransactionStatuses->$driverId)) {
                                $driverTransactionStatuses->$driverId = new \stdClass();
                            }
                            if (!isset($driverTransactionStatuses->$driverId->status)) {
                                $driverTransactionStatuses->$driverId->status = 0;
                            }

                            $driverTransactionStatuses->$driverId->status += $totalTransaction;

                            if ($status == 'paid') {
                                $totalTransactionsPaid += $totalTransaction;
                            }
                            $companyTake[$paymentName. "-" .$statusName] = format_price($totalTransaction) . " (" .$paymentName. " - " .$statusName. ") ";
                        }
                    }

                    $cell['company_take'] = implode(PHP_EOL, array_unique($companyTake));
                }
                else {
                    unset($cell['company_take']);
                }

                $drivercells[$driverId][] = $cell;
            }
            $totalRow = [
                'ref_number' => trans('reports.columns.total'),
                'total' => format_price($total),
                'company_take' => format_price($totalTransactionsPaid),
                'cash' => format_price($cash),
                'commission' => format_price($commission),
            ];
            if (((int)$report->version !== 1
                && config('eto_report.export.total') !== true)
                || (int)$report->version === 1
            ) {
                unset($totalRow['total']);
            }
            if (((int)$report->version !== 1
                && config('eto_report.export.company_take') !== true)
                || (int)$report->version === 1
            ) {
                unset($totalRow['company_take']);
            }

            $balance = ($commission - $cash);
            $whoPay = $balance > 0
                ? trans('reports.company_payd_driver')
                : trans('reports.driver_payd_company');

            $drivercells[$driverId][] = $totalRow;
            $drivercells[$driverId][] = [];
            $drivercells[$driverId][] = [
                0 => trans('reports.final_balance') . ': ' . $whoPay . ' ' . format_price(abs($balance)),
            ];
        }

        $excel = \Excel::create($this->filename, function ($excel) use ($report, $drivercells) {
            $cols = [
                'A','B','C','D','E'
            ];
            $lastColl = 4;

            if (((int)$report->version !== 1
                    && config('eto_report.export.total') !== true)
                || (int)$report->version === 1
            ) {
                $lastColl--;
            }

            if (((int)$report->version !== 1
                    && config('eto_report.export.company_take') !== true)
                || (int)$report->version === 1
            ) {
                $lastColl--;
            }

            $report->drivers = (object)$report->drivers;
            $driversList = [];
            foreach ($report->drivers as $key => $value) {
                $driversList[(int)$key] = $value;
            }

            foreach($drivercells as $driverId => $cells) {
                $name = $driversList[$driverId]->name;
                // $name = $report->drivers->$driverId->name;
                $excel->sheet($name, function ($sheet) use ($cells, $cols, $lastColl) {
                    $sheet->fromArray($cells, null, 'A1', false, false);
                    $sheet->row(4, function ($row) {
                        $row->setBackground('#F5F5F5');
                        $row->setFontWeight('bold');
                    });
                    $sheet->row((count((array)$sheet->data)-2), function ($row) {
                        $row->setBackground('#F5F5F5');
                        $row->setFontWeight('bold');
                    });
                    $sheet->row(count((array)$sheet->data), function ($row) {
                        $row->setBackground('#F5F5F5');
                    });
                    $sheet->mergeCells('A1:'.$cols[$lastColl].'1');
                    $sheet->mergeCells('A2:'.$cols[$lastColl].'2');
                    $sheet->mergeCells('A' . count((array)$sheet->data) . ':' . $cols[$lastColl] . count((array)$sheet->data));
                    $sheet->getStyle('A' . (count((array)$sheet->data)-2) . ':' . $cols[$lastColl] . (count((array)$sheet->data)-2))
                        ->getBorders()->getTop()->setBorderStyle('1px solid');
                });
            }
        });

        $excel->store($format);

        if (!empty($report->id)) {
            $parh = $this->folder . '/' .$this->filename . '.' . $this->format;
            return response()->download($parh, $this->filename . '.' . $this->format)->deleteFileAfterSend(true);
        }
        else {
            return response()->json(['downloadUrl' => $this->filename . '.' . $this->format], 200);
        }
    }

    private function generatePDFDrivers($report, $exportDriverId = false)
    {
        $config = [
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
            // 'tempDir' => $this->folder,
        ];
        $mpdf = new \Mpdf\Mpdf($config);
        $mpdf->WriteHTML(view('reports.driver_pdf', ['report' => $report, 'exportDriverId' => $exportDriverId])->render());

        if (!empty($report->id)) {
            $mpdf->Output($this->filename . '.' . $this->format, "D");
        }
        else {
            // ob_clean();
            $name = $this->filename . '.' . $this->format;
            \Storage::disk('tmp')->put($name, $mpdf->Output($name, "S"));
            return response()->json(['downloadUrl'=>$name], 200);
        }
    }

    private function headersDriver()
    {
        $headersDriver = [
            'ref_number' => trans('reports.columns.ref_number'),
            'total' => trans('reports.columns.total'),
            'company_take' => trans('reports.columns.company_take'),
            'cash' => trans('reports.columns.cash'),
            'commission' => trans('reports.columns.commission'),
        ];

        if (config('eto_report.export.total') !== true) {
            unset($headersDriver['total']);
        }

        if (config('eto_report.export.company_take') !== true) {
            unset($headersDriver['company_take']);
        }

        return$headersDriver;
    }

	private function generateExcelPayments($report, $format)
	{
  		$totals = [];
  		$cells = [];
  		$iBoldFirst = [];
  		$iMerge = [];
  		$iBold = [];
  		$iBackgroung = [];
  		$statusColorId = [];
  		$i = 1;
      $status_colors = json_decode(json_encode($report->status_colors), true);

  		foreach($report->payments as $paymentId=>$payment) {
    			$cells[$i] = [
    				0 => $payment->name,
    				1 => !empty($payment->site) ? "({$payment->site})" : ''
    			];
    			$iBoldFirst[] = $i;
    			$i++;

    			foreach($payment->status as $statusId=>$status) {
      				$cells[$i] = [
  					     0 => $status->name
      				];
      				$iMerge[] = $i;
      				$iBackgroung[] = $i;
              $statusColorId[$i] = $status_colors[$statusId]['color'];
      				$i++;

      				if (empty($totals[$statusId])) {
                  $totals[$statusId] = [
                      'name' => $status->name,
                      'total' => 0,
                  ];
              }

      				$totals[$statusId]['total'] += $status->total;
      				$totalAmount = 0;

      				foreach($status->bookings as $key=>$index) {
        					$booking = $report->bookings->$index;
        					$pId = $payment->id;
        					$transaction = $booking->transactions->$pId->$statusId;
        					$total = 0;

        					if ($report->version === 1) {
    						      $total = $transaction;
        					}
                  else {
    						      $total = $transaction->payment_charge + $transaction->amount;
        					}

        					$cells[$i] = [
          						0 => $booking->ref_number,
          						1 => format_price($total)
        					];
        					$i++;

        					$totalAmount += $total;
      				}

      				$cells[$i] = [
        					0 => trans('reports.total'),
        					1 => format_price($totalAmount)
      				];
      				$iBold[] = $i;
      				$i++;

      				$cells[$i] = [];
      				$iMerge[] = $i;
      				$i++;
    			}

    			$cells[$i] = [];
    			$iMerge[] = $i;
    			$i++;
  		}

  		$cells[$i] = [
          0 => trans('reports.titles.all_payments')
  		];
  		$iBold[] = $i;
  		$iMerge[] = $i;
  		$i++;

  		foreach($totals as $key=>$value) {
    			$cells[$i] = [
    				0 => $value['name'],
    				1 => format_price($value['total'])
    			];
    			$iBackgroung[] = $i;
    			$i++;
  		}

      $excel = \Excel::create($this->filename, function ($excel) use ($report, $cells, $iMerge, $iBoldFirst, $iBold, $iBackgroung, $statusColorId) {
          $excel->sheet(trans('reports.titles.payment'), function ($sheet) use ($cells, $iMerge, $iBoldFirst, $iBold, $iBackgroung, $statusColorId) {
              $sheet->fromArray($cells, null, 'A1', false, false);

        				foreach($iMerge as $i) {
        					$sheet->mergeCells('A' . $i . ':B' . $i);
        				}

        				foreach($iBold as $i) {
        					$sheet->row($i, function ($row) {
        						$row->setBackground('#F5F5F5');
        						$row->setFontWeight('bold');
        					});
        				}

        				foreach($iBoldFirst as $i) {
        					$sheet->row($i, function ($row) {
        						$row->setBackground('#F5F5F5');
        					});

        					$sheet->getStyle('A' . $i)->getFont()->setBold(true);
        				}

        				foreach($iBackgroung as $i) {
        					$sheet->row($i, function ($row) {
        						$row->setBackground('#F5F5F5');
        					});
        				}

        				foreach($statusColorId as $i=>$color) {
                  $styleArray = [
                      'font' => [
                          'color' => ['rgb' => strtoupper(str_replace('#', '', $color))],
                      ]
                  ];

                  $sheet->getStyle('A' . $i)->applyFromArray($styleArray);
        				}
            });
        });

        $excel->store($format);

        if (!empty($report->id)) {
            $parh = $this->folder . '/' .$this->filename . '.' . $this->format;
            return response()->download($parh, $this->filename . '.' . $this->format)->deleteFileAfterSend(true);
        }
        else {
            return response()->json(['downloadUrl' => $this->filename . '.' . $this->format], 200);
        }
    }

  	private function generatePDFPayments($report)
  	{
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
            // 'tempDir' => $this->folder,
        ]);
        $mpdf->WriteHTML(view('reports.payments_pdf', ['report' => $report])->render());

        if (!empty($report->id)) {
            $mpdf->Output($this->filename, "D");
        }
        else {
            // ob_clean();
            $name = $this->filename . '.' . $this->format;
            \Storage::disk('tmp')->put($name, $mpdf->Output($name, "S"));
            return response()->json(['downloadUrl'=>$name], 200);
        }
  	}

    private function generateName() {
        $this->filename = 'ETO_report_' . Carbon::now()->format('Y-m-d_H-i');
        $this->folder = parse_path('tmp', 'public');
    }
}
