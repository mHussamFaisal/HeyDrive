<?php

namespace App\Traits\Filters;

use Illuminate\Support\Arr;
use App\Helpers\SettingsHelper;

trait Bookings
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getBookingsFilters($config)
    {
        $siteId = config('site.site_id');
        $sources = SettingsHelper::getSourceList($siteId);
        $services = SettingsHelper::getServices($siteId);
        $payments = SettingsHelper::getPaymentTypeList($siteId);
        $paymentStatus = (new \App\Models\Transaction)->getStatusList();
        $users = (new \App\Http\Controllers\User\UserController)->__search(); // drivers and admins
        $customers = (new \App\Http\Controllers\User\CustomerController())->search(); // customers

        $config->params->source = clone $this->select;
        $config->params->source->items = new \stdClass();
        $config->params->service = clone $this->select;
        $config->params->service->items = new \stdClass();
        $config->params->payment = clone $this->select;
        $config->params->payment->items = new \stdClass();
        $config->params->payment_status = clone $this->select;
        $config->params->payment_status->items = new \stdClass();
        $config->params->driver = clone $this->select;
        $config->params->driver->items = new \stdClass();
        $config->params->customer = clone $this->select;
        $config->params->customer->items = new \stdClass();

        foreach ($sources['data'] as $source=>$sourceData) {
            $config->params->source->items->$source = new \stdClass();
        }

        if (count((array)$services['data']) > 1) {
            foreach ($services['data'] as $service => $serviceData) {
                $config->params->service->items->$service = new \stdClass();
                $config->params->service->items->$service->name = $serviceData['name'];
            }
        }

        foreach ($payments['data'] as $payment) {
            $paymentId = $payment->id;
            $config->params->payment->items->$paymentId = new \stdClass();
            $config->params->payment->items->$paymentId->name = $payment->name;
        }

        foreach ($paymentStatus as $status) {
            $statusvalue = $status->value;
            $config->params->payment_status->items->$statusvalue = new \stdClass();
            $config->params->payment_status->items->$statusvalue->name = $status->text;
        }

        foreach ($users['items'] as $user) {
            $role = $user->role;
            $userId = $user->id;
            if ($user->role == 'admin' || $user->role === null) {
                $role = 'driver';
            }
            $config->params->$role->items->$userId = new \stdClass();
            $config->params->$role->items->$userId->name = $user->name;
        }

        foreach ($customers['items'] as $user) {
            $role = $user->role;
            $userId = $user->id;

            $config->params->$role->items->$userId = new \stdClass();
            $config->params->$role->items->$userId->name = $user->name;
        }
        return $config;
    }

    public function parseBookingsFilters($filters, $name) {
        $key = $name;
        $data = [
            $key => [
                'name' => $name,
                'type' => 'custom',
                'data' => []
            ]
        ];

        if (!empty($filters['status'])) {
            $data[$key]['data']['status'] = $filters['status'];
        }

        if (!empty($filters['from'])) {
            $data[$key]['data']['from'] = $filters['from'];
        }

        if (!empty($filters['to'])) {
            $data[$key]['data']['to'] = $filters['to'];
        }

        if (!empty($filters['date_type'])) {
            $data[$key]['data']['date_type'] = $filters['date_type'];
        }

        if (!empty($filters['source'])) {
            $data[$key]['data']['source'] = $filters['source'];
        }

        if (!empty($filters['service'])) {
            $data[$key]['data']['service'] = $filters['service'];
        }

        if (!empty($filters['payment'])) {
            $data[$key]['data']['payment'] = $filters['payment'];
        }

        if (!empty($filters['payment_status'])) {
            $data[$key]['data']['payment_status'] = $filters['payment_status'];
        }

        if (!empty($filters['driver'])) {
            $data[$key]['data']['driver'] = $filters['driver'];
        }

        if (!empty($filters['customer'])) {
            $data[$key]['data']['customer'] = $filters['customer'];
        }
        return $data;
    }

    public function getBookingsData($config)
    {
        $bookingTable = (new \App\Models\Booking)->getTable();
        $bookingRouteTable = (new \App\Models\BookingRoute)->getTable();
        $customerProfileTable = (new \App\Models\CustomerProfile)->getTable();
        $customerTable = (new \App\Models\Customer)->getTable();
        $usersTable = (new \App\Models\User)->getTable();
        $vehicleTable = (new \App\Models\Vehicle)->getTable();
        $bookingIds = [];

        if (!empty($config['payment'])) {
            $paymentBookingIds = \App\Models\Transaction::select('relation_id')->whereIn('payment_id', $config['payment']);

            if (!empty($config['from']) && !empty($config['to'])) {
                $paymentBookingIds->whereBetween('created_at', [$config['from'], $config['to']]);
            }
            else if (!empty($config['from'])) {
                $paymentBookingIds->where('created_at', '>=', $config['from']);
            }
            else if (!empty($config['to'])) {
                $paymentBookingIds->where('created_at', '<=', $config['to']);
            }

            $transactions = $paymentBookingIds->get();
            foreach($transactions as $transaction) {
                $bookingIds[] = $transaction->relation_id;
            }
            $bookingIds = array_values($bookingIds);
        }

        if (!empty($config['payment_status'])) {
            $paymentBookingIds = \App\Models\Transaction::select('relation_id')->whereIn('status', $config['payment_status']);

            if (!empty($config['from']) && !empty($config['to'])) {
                $paymentBookingIds->whereBetween('created_at', [$config['from'], $config['to']]);
            }
            else if (!empty($config['from'])) {
                $paymentBookingIds->where('created_at', '>=', $config['from']);
            }
            else if (!empty($config['to'])) {
                $paymentBookingIds->where('created_at', '<=', $config['to']);
            }

            $transactions = $paymentBookingIds->get();
            foreach($transactions as $transaction) {
                $bookingIds[] = $transaction->relation_id;
            }
            $bookingIds = array_values($bookingIds);
        }

        $dbPrefix = get_db_prefix();

        $bookingList = \App\Models\BookingRoute::select($bookingTable . '.*',
            $bookingRouteTable . '.*',
            $bookingRouteTable . '.id AS route_id',
            $bookingRouteTable . '.ref_number AS route_ref_number',
            \DB::raw("(SELECT name
        					FROM " . $dbPrefix . $customerTable . "
        					WHERE id=" . $dbPrefix . $bookingTable . ".user_id
        					LIMIT 1
        				) AS user_name"),
                    \DB::raw("(SELECT d.company_name
        					FROM " . $dbPrefix . $customerTable . " AS c
        					LEFT JOIN " . $dbPrefix . $customerProfileTable . " AS d
        					ON c.id=d.user_id
        					WHERE c.id=" . $dbPrefix . $bookingTable . ".user_id
        					LIMIT 1
        				) AS company_name"),
                    \DB::raw("(SELECT name
        					FROM " . $dbPrefix . $usersTable . "
        					WHERE id=" . $dbPrefix . $bookingRouteTable . ".driver_id
        					LIMIT 1
        				) AS driver_name"),
                    \DB::raw("(SELECT name
        					FROM " . $dbPrefix . $vehicleTable . "
        					WHERE id=" . $dbPrefix . $bookingRouteTable . ".vehicle_id
        					LIMIT 1
        				) AS vehicle_name"))
            ->join($bookingTable,  $bookingTable. '.id', '=', $bookingRouteTable.'.booking_id');

        if (!empty($config['status'])) {
            $bookingList->whereIn('status', $config['status']);
        }

        if (!empty($config['date_type'])) {
            if (!empty($config['from']) && !empty($config['to'])) {
                $bookingList->whereBetween($config['date_type'], [$config['from'], $config['to']]);
            }
            else if (!empty($config['from'])) {
                $bookingList->where($config['date_type'], '>=', $config['from']);
            }
            else if (!empty($config['to'])) {
                $bookingList->where($config['date_type'], '<=', $config['to']);
            }
        }

        if (!empty($config['source'])) {
            $bookingList->whereIn('source', $config['source']);
        }

        if (!empty($config['service'])) {
            $bookingList->whereIn('service_id', $config['service']);
        }

        if ((!empty($config['payment']) || !empty($config['payment_status'])) && !empty($bookingIds)) {
            $bookingList->whereIn($bookingRouteTable.'.id', $bookingIds);
        }

        if (!empty($config['driver'])) {
            $bookingList->whereIn('driver_id', $config['driver']);
        }

        if (!empty($config['customer'])) {
            $bookingList->whereIn('user_id', $config['customer']);
        }

        $bookingList = $bookingList->whereHas('booking', function ($query) {
            $query->whereIn('site_id', $this->sites);
        })->get();

        return $bookingList;
    }

    public function getBookingsFile($data, $columns, $format, $folder)
    {
        $headers = [];

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                if (!config('site.allow_services') && ($column == 'service_id' || $column == 'service_duration')) {
                    continue;
                }

                if ($column == 'vehicle') {
                    $vehicles = [];
                    $vehicleTypes = \App\Models\VehicleType::select(['id', 'name'])
                        ->orderBy('ordering', 'asc')
                        ->orderBy('name', 'asc')
                        ->get();
                    foreach($vehicleTypes as $vehicle) {
                        $vehicles[$vehicle->id] = $vehicle->name;
                    }
                }

                $headers[$column] = trans('export.column.bookings.' . $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $booking) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        $val = Arr::get($booking, $hK);

                        if ($val instanceof \Carbon\Carbon) {
                            $val = format_date_time($val);
                        } else if ($hK == 'waypoints') {
                            $val = implode(' | ', json_decode($val, true));
                        } else if ($hK == 'vehicle' && !empty($vehicles)) {
                            $usedVehicles = [];

                            foreach (json_decode($val) as $vehicle) {
                                if (!empty($vehicles[$vehicle->id])) {
                                    if ((int)$vehicle->amount > 1) {
                                        $usedVehicles[] = $vehicles[$vehicle->id] . ' x' . $vehicle->amount;
                                    } else {
                                        $usedVehicles[] = $vehicles[$vehicle->id];
                                    }
                                }
                            }
                            $val = implode(' | ', $usedVehicles);
                        }

                        $val = trim(strip_tags($val));
                        $val = str_replace(array("\r\n", "\r", "\n"), ' ', $val);
                        $cell[$hK] = $val ? $val : '';
                    }

                    $cell['total'] = $booking->getTotal();
                    if (!empty($cell)) {
                        $cells[] = $cell;
                    }
                }

                $this->setFilePerChunkSection('Bookings', $chunkId, $format, $cells);
            }
        }
    }
}
