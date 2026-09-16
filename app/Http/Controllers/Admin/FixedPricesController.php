<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Excel;
use App\Models\FixedPrice;

class FixedPricesController extends Controller
{
    public function export(Request $request)
    {
        $site_id = config('site.site_id');

        if ( $request->get('action') ) {
            $message = '';
            $errors = [];
            $response = [];

            $validator = \Validator::make($request->all(), [
                //
            ]);

            if ( $validator->fails() ) {
                $errors = array_merge($errors, $validator->errors()->all());
            }
            else {
                $query = FixedPrice::where('site_id', $site_id);

                if ( $request->get('date_start') ) {
                    $query->where('start_date', '>=', Carbon::parse($request->get('date_start')));
                }

                if ( $request->get('date_end') ) {
                    $query->where('end_date', '<=', Carbon::parse($request->get('date_end')));
                }

                // if ( $request->get('services') ) {
                //     $services = [];
                //     foreach ($request->get('services') as $k => $v) {
                //         $services[] = (int)$v;
                //     }
                // }

                $prices = $query->get();

                $filename = 'ETO Fixed Prices '. Carbon::now()->format('Y-m-d H:i:s');

                return Excel::create($filename, function($excel) use ($prices) {
                    $excel->sheet('Prices', function($sheet) use ($prices) {
                        $sheet->fromArray($prices);
                    });
                })->download('xls');
            }

            $response['message'] = $message;

            if ( $errors ) {
                $response['errors'] = $errors;
            }

            if ( $request->ajax() ) {
                return $response;
            }
            else {
                if ( empty($errors) ) {
                    session()->flash('message', $message);
                }
                return redirect()->back()->withErrors($errors);
            }
        }
        else {
            $services = \App\Models\Service::where('relation_type', 'site')
              ->where('relation_id', $site_id)
              ->where('status', 'active')
              ->orderBy('order', 'asc')
              ->orderBy('name', 'asc')
              ->get();

            return view('admin.fixed_prices.export', compact('services'));
        }
    }

    public function import(Request $request)
    {
        $site_id = config('site.site_id');

        switch ($request->get('action')) {
            case 'download':

                switch ($request->get('tmpl')) {
                    case 'standard':

                        $rows = [
                            [
                                'A1' => 'From',
                                'A2' => 'To',
                                'A3' => 'Price',
                            ],
                            [
                                'A1' => 'N15',
                                'A2' => 'E2,E3,E4',
                                'A3' => '10',
                            ],
                            [
                                'A1' => 'N16',
                                'A2' => 'W12,W13',
                                'A3' => '30',
                            ],
                            [
                                'A1' => 'N17',
                                'A2' => 'EC1V,EC1Y',
                                'A3' => '60',
                            ],
                        ];

                        $filename = 'FixedPrices - Standard import template';

                        return Excel::create($filename, function($excel) use ($rows) {
                            $excel->sheet('Standard', function($sheet) use ($rows) {
                                $sheet->fromArray($rows, null, 'A1', false, false);
                            });
                        })->download('csv');

                    break;
                    case 'standard-vehicles':

                        $vehicles = \App\Models\VehicleType::where('site_id', $site_id)
                          ->where('published', 1)
                          ->orderBy('ordering', 'asc')
                          ->orderBy('name', 'asc')
                          ->get();

                        $price = 10;

                        $rows = [
                            [
                                'A1' => 'From',
                                'A2' => 'To',
                                'A3' => 'Price',
                            ],
                            [
                                'A1' => 'W12,W13',
                                'A2' => 'EC1V,EC1Y',
                                'A3' => $price,
                            ],
                        ];

                        $i = 4;

                        foreach($vehicles as $kV => $vV) {
                            $price += 5;
                            // $rows[0]['A'.$i] = $vV->id;
                            $rows[0]['A'.$i] = $vV->name;
                            $rows[1]['A'.$i] = $price;
                            $i++;
                        }

                        $filename = 'FixedPrice - Standard import template';

                        return Excel::create($filename, function($excel) use ($rows) {
                            $excel->sheet('Standard', function($sheet) use ($rows) {
                                $sheet->fromArray($rows, null, 'A1', false, false);
                            });
                        })->download('csv');

                    break;
                    case 'cross':

                        $rows = [
                            [
                                'A1' => 'Postcodes',
                                'A2' => 'E2,E3,E4',
                                'A3' => 'W12,W13',
                                'A4' => 'EC1V,EC1Y',
                            ],
                            [
                                'A1' => 'N15',
                                'A2' => '10',
                                'A3' => '',
                                'A4' => '',
                            ],
                            [
                                'A1' => 'N16',
                                'A2' => '',
                                'A3' => '20',
                                'A4' => '30',
                            ],
                            [
                                'A1' => 'N17',
                                'A2' => '60',
                                'A3' => '',
                                'A4' => '',
                            ],
                        ];

                        $filename = 'FixedPrices - Cross import template';

                        return Excel::create($filename, function($excel) use ($rows) {
                            $excel->sheet('Standard', function($sheet) use ($rows) {
                                $sheet->fromArray($rows, null, 'A1', false, false);
                            });
                        })->download('csv');

                    break;
                }

            break;
            case 'save':

                $message = '';
                $errors = [];
                $response = [];

                // dd($request->all());

                // Add form validation
                // Disble deposit and services
                // Correct deposit display in fixed price edit tab.
                // Add download example generated based on current settings on the fly

                $validator = \Validator::make($request->all(), [
                    'file' => 'required',
                    // 'file' => 'required|mimes:xls,xlsx,csv',
                    // 'file' => 'required|mimetypes:application/vnd.ms-excel',
                ]);

                if ( $validator->fails() ) {
                    $errors = array_merge($errors, $validator->errors()->all());
                }
                elseif ( $request->hasFile('file') ) {

                    config(['excel.import.heading' => 'original']);
                    config(['excel.csv.delimiter' => ($request->get('delimiter') ?: ';')]);

                    $path = $request->file('file')->getRealPath();
                    $data = Excel::selectSheetsByIndex(0)->load($path, function($reader) use ($request, &$message, $site_id) {
                        // $reader->takeRows(2);
                        // $reader->takeColumns(2);
                        // $reader->dump();
                        // $reader->dd();
                        // dd($reader->toArray(), $reader->toObject());

                        $vehicleList = \App\Models\VehicleType::where('site_id', $site_id)
                          ->where('published', 1)
                          ->orderBy('ordering', 'asc')
                          ->orderBy('name', 'asc')
                          ->get();

                        $results = $reader->all();
                        $skipped = 0;
                        $imported = 0;
                        $importList = [];

                        if ( !empty($results) && $results->count() ) {
                            $vehicles = [];
                            if ( $request->get('vehicles') ) {
                                foreach ($request->get('vehicles') as $k => $v) {
                                    $vehicles[] = [
                                        'id' => $k,
                                        'value' => isset($v['price']) ? (float)$v['price'] : 0,
                                        'deposit' => isset($v['deposit']) ? (float)$v['deposit'] : 0,
                                    ];
                                }
                            }

                            $services = [];
                            if ( $request->get('services') ) {
                                foreach ($request->get('services') as $k => $v) {
                                    $services[] = (int)$v;
                                }
                            }

                            $params = [
                                'factor_type' => (int)$request->get('factor_type', 2),
                                'deposit' => 0,
                                'vehicle' => $vehicles,
                            ];

                            foreach ($results as $rows ) {
                                switch ($request->get('import_type')) {
                                    case 'cross':

                                        $first_key = $rows->keys()->first();
                                        $first_value = $rows->first();
                                        // dd($first_key, $first_value);

                                        foreach ($rows as $key => $value) {
                                            if ($key != (string)$first_key) {
                                                if (!empty($value)) {
                                                    $start = $first_value ? str_replace('  ', ' ', (string)$first_value) : 'ALL';
                                                    if (strtoupper($start) == 'ALL') {
                                                        $start = 'ALL';
                                                    }
                                                    $start = trim($start);
                                                    $start = strtoupper($start);

                                                    $end = $key ? str_replace('  ', ' ', (string)$key) : 'ALL';
                                                    if (strtoupper($end) == 'ALL') {
                                                        $end = 'ALL';
                                                    }
                                                    $end = trim($end);
                                                    $end = strtoupper($end);

                                                    $price = $value;

                                                    $importList[] = [$start, $end, $price];
                                                    $imported++;
                                                }
                                                else {
                                                    $skipped++;
                                                }
                                            }
                                        }

                                    break;
                                    case 'standard':

                                        $veh = [];
                                        $row = [];

                                        foreach ($rows as $key => $value) {
                                            $excelColumn = trim($key);
                                            $row[] = $value;

                                            if (!empty($excelColumn)) {
                                                for ($i=3; $i < count($rows) ; $i++) {
                                                    $vehicle_id = 0;
                                                    $vehicle_name = '';

                                                    foreach ($vehicleList as $kV => $vV) {
                                                        if ((is_int($excelColumn) && $excelColumn == $vV->id) || (!is_int($excelColumn) && $excelColumn == $vV->name)) {
                                                            $vehicle_id = $vV->id;
                                                            $vehicle_name = $vV->name;
                                                            break;
                                                        }
                                                    }

                                                    // $veh[] = [$excelColumn, $vehicle_id, $vehicle_name, $value];
                                                    $veh[$vehicle_id] = $value;
                                                    break;
                                                }
                                            }
                                        }

                                        // dd([$row, $key, $rows, $veh]);

                                        if (!empty($row[2]) || !empty($veh)) {
                                            $start = !empty($row[0]) ? str_replace('  ', ' ', (string)$row[0]) : 'ALL';
                                            if ( strtoupper($start) == 'ALL' ) {
                                                $start = 'ALL';
                                            }
                                            $start = trim($start);
                                            $start = strtoupper($start);

                                            $end = !empty($row[1]) ? str_replace('  ', ' ', (string)$row[1]) : 'ALL';
                                            if ( strtoupper($end) == 'ALL' ) {
                                                $end = 'ALL';
                                            }
                                            $end = trim($end);
                                            $end = strtoupper($end);

                                            $price = !empty($row[2]) ? (float)$row[2] : 0;

                                            $importList[] = [$start, $end, $price, $veh];
                                            $imported++;
                                        }
                                        else {
                                            $skipped++;
                                        }

                                    break;
                                }
                            }

                            // dd($importList, $imported, $skipped);

                            foreach ($importList as $k => $v) {

                                if (!empty($params['vehicle']) && !empty($v[3])) {
                                    foreach ($params['vehicle'] as $kV => $vV) {
                                        foreach ($v[3] as $kV2 => $vV2) {
                                            if ($vV['id'] == $kV2) {
                                                // $params['factor_type'] = 2;
                                                $params['vehicle'][$kV]['value'] = !is_null($vV2) ? (float)$vV2 : (float)$v[2];
                                                break;
                                            }
                                        }
                                    }
                                }

                                $price = new FixedPrice();
                                $price->site_id = $site_id;
                                $price->service_ids = $services ? json_encode($services) : null;
                                $price->type = 1;
                                $price->start_type = 0;
                                $price->start_postcode = (string)$v[0];
                                $price->start_date = $request->get('date_start') ? Carbon::parse($request->get('date_start')) : null;
                                $price->direction = $request->get('direction') ? 1 : 0;
                                $price->end_type = 0;
                                $price->end_postcode = (string)$v[1];
                                $price->end_date = $request->get('date_end') ? Carbon::parse($request->get('date_end')) : null;
                                $price->value = (float)$v[2];
                                $price->params = json_encode($params);
                                $price->modified_date = Carbon::now();
                                $price->ordering = 0;
                                $price->published = $request->get('status') == 'active' ? 1 : 0;

                                // dd([$v, $params, $price]);

                                $price->save();
                            }

                            $message = trans('admin/fixed_prices.message.imported', [
                                'imported' => $imported,
                                'skipped' => $skipped
                            ]);
                        }
                    })->get();

                    // dd($data);
                }

                $response['message'] = $message;

                if ( $errors ) {
                    $response['errors'] = $errors;
                }

                if ( $request->ajax() ) {
                    return $response;
                }
                else {
                    if ( empty($errors) ) {
                        session()->flash('message', $message);
                    }
                    return redirect()->back()->withErrors($errors);
                }

            break;
            case 'clear':

                $errors = [];
                $prices = FixedPrice::where('site_id', $site_id)->delete();

                // dd($prices);

                if ($prices) {
                    session()->flash('message', 'Deleted!');
                }
                else {
                    $errors[] = 'Not deleted!';
                }

                return redirect()->route('admin.fixed-prices.import')->withErrors($errors);
                // return redirect()->back()->withErrors($errors);

            break;
            case 'copy_site':

                $vehicleMap = [
                    // 0 => 0,
                ];

                $fromSiteId = 0; // $site_id
                $toSiteId = 0;

                $errors = [];
                $prices = FixedPrice::where('site_id', $fromSiteId)->get();

                // dd($prices);
                // SELECT id, site_id, name, ordering, (SELECT id FROM `eto_vehicle` as b WHERE b.name = a.name AND b.site_id != a.site_id LIMIT 1) as `new_id` FROM `eto_vehicle` as a ORDER BY `a`.`name` DESC
                // SELECT CONCAT(id, ' => ', (SELECT id FROM `eto_vehicle` as b WHERE b.name = a.name AND b.site_id != a.site_id LIMIT 1), ',') as `new_id`, site_id, name, ordering FROM `eto_vehicle` as a WHERE a.site_id=1 ORDER BY `a`.`name` DESC
                // admin/fixed-prices/import?action=copy_site

                if (!empty($vehicleMap) && !empty($toSiteId)) {
                    foreach ($prices as $k => $v) {
                        $params = [];

                        if (!empty($v->params)) {
                            $params = json_decode($v->params);
                            $vehicle = [];

                            if (!empty($params->vehicle)) {
                                foreach($params->vehicle as $k1 => $v1) {
                                    $v1->id = isset($vehicleMap[$v1->id]) ? $vehicleMap[$v1->id] : $v1->id;
                                    $vehicle[] = $v1;
                                }
                            }

                            $params->vehicle = $vehicle;
                        }

                        $price = $v->replicate();
                        $price->site_id = $toSiteId;
                        $price->params = json_encode($params);
                        $price->save();

                        if (!empty($price->id)) {
                            $zones = $v->zones()->get();
                            $sync = [];
                            foreach ($zones as $k => $v) {
                                $sync[] = [
                                  'relation_type' => $v->pivot->relation_type,
                                  'relation_id' => $price->id,
                                  'target_id' => $v->pivot->target_id,
                                  'type' => $v->pivot->type
                                ];
                            }
                            $price->zones()->sync($sync);
                        }

                        // dump($price->toArray());
                    }
                }

                if ($prices) {
                    session()->flash('message', 'Copied: '. $prices->count());
                }
                else {
                    $errors[] = 'Not copied!';
                }

                return redirect()->route('admin.fixed-prices.import')->withErrors($errors);
                // return redirect()->back()->withErrors($errors);

            break;
            default:

                $vehicles = \App\Models\VehicleType::where('site_id', $site_id)
                  ->where('published', 1)
                  ->orderBy('ordering', 'asc')
                  ->orderBy('name', 'asc')
                  ->get();

                $services = \App\Models\Service::where('relation_type', 'site')
                  ->where('relation_id', $site_id)
                  ->where('status', 'active')
                  ->orderBy('order', 'asc')
                  ->orderBy('name', 'asc')
                  ->get();

                return view('admin.fixed_prices.import', compact('vehicles', 'services'));

            break;
        }
    }

}
