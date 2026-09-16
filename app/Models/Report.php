<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $bookingRouteColumns = [
        'id',
        'ref_number',
        'route',
        'date',
        'address_start',
        'address_end',
        'vehicle_list',
        'meet_and_greet',
        'contact_title',
        'contact_name',
        'contact_email',
        'contact_mobile',
        'lead_passenger_title',
        'lead_passenger_name',
        'lead_passenger_email',
        'lead_passenger_mobile',
        'driver_id',
        'vehicle_id',
        'commission',
        'cash',
        'flight_number',
        'flight_landing_time',
        'departure_city',
        'departure_flight_number',
        'departure_flight_time',
        'departure_flight_city',
        'waiting_time',
        'total_price',
        'discount',
        'discount_code',
        'status',
        'modified_date',
        'created_date',
        'service_id',
        'service_duration',
        'scheduled_route_id',
        'source',
        'is_read'
    ];

    private $versiomModule = 2;
    private $bookingList = [];
    private $unassignedbookingList = [];
    private $typeReport = '';

    protected $dates = [
        'from_date',
        'to_date'
    ];

    function __construct()
    {
        parent::__construct();
    }

    /**
     * methot getReports() use for view or generate new report
     *
     * @param $query
     * @param bool $withTrashed
     * @param bool $id
     * @return mixed
     */
    public function scopeGetReports($query, $withTrashed = false, $id = false )
    {
        if (!empty(request()->get('type'))) {
            $query->where('type', request()->get('type'));
        }
        if ($withTrashed) {
            $query->onlyTrashed();
        }
        if ($id) {
            $query->where('id', $id);
        }

        return $query;
    }

    /**
     * @param $type
     * @param bool $relationId
     * @return $this
     */
    public function renderReports($type, $relationId = false)
    {
        $this->typeReport = $type;
        $request = request();
        $path = 'reports' .  DIRECTORY_SEPARATOR . $this->id . '.json';
        $dates = [];
        $data = new \stdClass();

        if ($this->id && \Storage::disk('archive')->exists($path) && $file = \Storage::disk('archive')->get($path)) {
            $data = json_decode($file);
            $this->version = $data->version ?: $this->versiomModule;
            $this->filters = $data->filters;

            if (!empty($data->bookings)) {
                $this->bookingList = $data->bookings;
            }

            if ($type == 'fleet' && !empty($data->fleets)) {
                $this->fleets = $this->fleetReport($data->fleets, $relationId);
                if (!empty($data->payments)) {
                    $this->payments = $data->payments;
                }
            }
            elseif ($type == 'driver' && !empty($data->drivers)) {
                $this->drivers = $this->driverReport($data->drivers, $relationId);
                if ((int)$this->version > 1 && !empty($data->payments)) {
//                    $this->payments = $this->paymentReport($data->payments, $relationId);
                    $this->payments = $data->payments;
                }
            }
            elseif ($type == 'customers') {
                $this->customers = $this->customerReport($data->customers, $relationId);
            }
            elseif ($type == 'payment') {
                $this->payments = !empty($data->payments) ?$data->payments : [];
            }
        }
        elseif ($filters = json_decode(json_encode($request->filters))) {
            $this->version = $this->versiomModule;
            if ($dataBookings = $this->getFilteredBookings($request, $filters)) {
                $this->bookingList = $this->parseBookings($dataBookings);

                if ($type == 'fleet') {
                    $this->fleets = $this->generateFleetReport($dataBookings);
                    $this->payments = $this->generatePaymentReport($dataBookings, $filters);
                    $this->unassignedList = $this->unassignedbookingList;
                }
                elseif ($type == 'driver') {
                    $this->drivers = $this->generateDriverReport($dataBookings);
                    $this->payments = $this->generatePaymentReport($dataBookings, $filters);
                    $this->unassignedList = $this->unassignedbookingList;
                }
                elseif ($type == 'customers') {
                    $this->customers = $this->generateCustomerReport($dataBookings, $filters);
                }
                elseif ($type == 'payment') {
                    $this->payments = $this->generatePaymentReport($dataBookings, $filters);
                    $this->unassignedList = $this->unassignedbookingList;
                }
                else {
                    abort(404);
                }
            }

            $this->type = $type;
            $filters = (array)$filters;

            foreach ($filters as $fid => $filter) {
                if (in_array($fid, ['from_date', 'to_date'])) {
                    $this->$fid = Carbon::parse($filter);
                    unset($filters[$fid]);
                }
                if (empty($filter)) {
                    unset($filters[$fid]);
                }
            }

            $this->filters = (object)$filters;
        }

        if (!empty($this->bookingList)) {
            $bookings = (array)$this->bookingList;
            foreach ($bookings as $ref => $booking) {
                $dates[] = $booking->date;
                $date = Carbon::parse($booking->date)->format('Y-m-d');
                $bookings[$ref]->date = $date;
                $bookings[$ref]->timestamp = strtotime($date);
            }

            $this->bookings = (object)$bookings;

            $dates = $this->sortDates($dates);
            $this->from = format_date_time($dates[0], 'date');
            $this->to = format_date_time($dates[count($dates) - 1], 'date');
            $this->from_date_timestamp = $this->from_date->timestamp;
            $this->to_date_timestamp = $this->to_date->timestamp;
        }

        $this->status_colors = (new Transaction())->options->status;

        if (empty($this->fleets) && empty($this->drivers) && empty($this->payments) && empty($this->customers)) {
            $this->status_report = false;
            if (!empty($this->id)) {
                $this->status_message = trans('reports.file_deleted');
            }
        } else {
            $this->status_report = true;
        }

        return $this;
    }

    /**
     * @param $report
     * @return mixed
     */
    private function fleetReport($fleets, $fleetId = false)
    {
        $fleets = (object)$fleets;

        $fleetList = [];
        $bookings = [];

        foreach ($fleets as $key => $value) {
            $fleetList[(int)$key] = $value;
        }

        foreach ($this->bookingList as $hash=>$booking) {
            foreach ($booking->fleets as $id=>$bFleet) {
                if ($fleetId && $fleetId != $bFleet) {
                    continue;
                }

                if (!empty($fleetList[$bFleet])) {
                    if (!isset($fleetList[$bFleet]->commission)) {
                        $fleetList[$bFleet]->commission  = 0;
                        $fleetList[$bFleet]->bookings = [];
                    }

                    $fleetList[$bFleet]->commission += $booking->fleet_commission;
                    $bookings[$bFleet][] = $hash;
                }
            }
        }

        foreach ($bookings as $id=>$data) {
            $fleetList[$id]->bookings = array_unique($data);
        }

        return (object)$fleetList;
    }


    /**
     * @param $report
     * @return mixed
     */
    private function driverReport($drivers, $driverId = false)
    {
        $drivers = (object)$drivers;

        $driverList = [];
        $bookings = [];

        foreach ($drivers as $key => $value) {
            $driverList[(int)$key] = $value;
        }

        foreach ($this->bookingList as $hash=>$booking) {
            foreach ($booking->drivers as $id=>$bDriver) {
                if ($driverId && $driverId != $bDriver) {
                    continue;
                }

                if (!empty($driverList[$bDriver])) {
                    if (!isset($driverList[$bDriver]->cash)) {
                        $driverList[$bDriver]->cash  = 0;
                        $driverList[$bDriver]->commission  = 0;
                        $driverList[$bDriver]->bookings = [];
                    }

                    $driverList[$bDriver]->cash += $booking->cash;
                    $driverList[$bDriver]->commission += $booking->commission;
                    $bookings[$bDriver][] = $hash;
                }
            }
        }

        foreach ($bookings as $id=>$data) {
            $driverList[$id]->bookings = array_unique($data);
        }

        return (object)$driverList;
    }

    /**
     * @param $report
     * @return mixed
     */
    private function customerReport($customers, $customerId = false)
    {
        return $customers;
    }

    /**
     * @param $data
     * @return array
     */
    public function generateFleetReport($data)
    {
        $bookings = $this->bookingList;
        $fleets = [];
        $fleetIds = [];

        foreach($data as $key => $value) {
            if ( empty($fleetIds[$value->fleet_id]) ) {
                $fleetIds[] = $value->fleet_id;
            }
        }

        $fleetList = \App\Helpers\ReportHelper::getFleetList($fleetIds);

        foreach($fleetList as $fleet) {
            $fleets[$fleet->id] = new \stdClass();
            $fleets[$fleet->id]->name =!empty($fleet->name) ?  $fleet->name : 'Unassigned';
            $fleets[$fleet->id]->email =!empty($fleet->email) ?  $fleet->email : '';
            $fleets[$fleet->id]->percent = !empty($fleet->profile->commission) ? $fleet->profile->commission : 0;
            $fleets[$fleet->id]->is_notified = 0;
            $fleets[$fleet->id]->bookings = [];
            $fleets[$fleet->id]->commission = 0;
        }

        foreach($data as $key => $value) {
            $fleets[$value->fleet_id]->commission += $value->fleet_commission;
            $fleets[$value->fleet_id]->bookings[] = $value->hash;

            $bookings[$value->hash]->fleets[] = $value->fleet_id;
        }

        foreach ($fleets as $id => $driver) {
            if (count($fleets[$id]->bookings) === 0) {
                unset($fleets[$id]);
            } else {
                $fleets[$id]->commission = round($driver->commission, 2);
            }
        }

        $this->bookingList = $bookings;

        return $fleets;
    }

    /**
     * @param $data
     * @return array
     */
    public function generateDriverReport($data)
    {
        $bookings = $this->bookingList;
        $drivers = [];
        $driverList = [];

        foreach($data as $key => $value) {
            if (!empty($value->driver_id) && !in_array($value->driver_id, $driverList)) {
                $driverList[] = $value->driver_id;
            }
        }

        $users = User::with('profile')->whereIn('id', $driverList)->get();
        $driverList = [];

        foreach ($users as $driver) {
            $driverList[(int)$driver->id] = $driver;
        }

        foreach($data as $key => $value) {
            if ( empty($drivers[$value->driver_id]) ) {
                if (!empty($driverList[(int)$value->driver_id])) {
                    $driver = $driverList[(int)$value->driver_id];
                } else {
                    $driver = json_decode($value->driver_data);
                }

                $drivers[$value->driver_id] = new \stdClass();
                $drivers[$value->driver_id]->name =!empty($driver->name) ?  $driver->name : 'Unassigned';
                $drivers[$value->driver_id]->email =!empty($driver->email) ?  $driver->email : '';
                $drivers[$value->driver_id]->percent = !empty($driver->profile->commission) ? $driver->profile->commission : 0;
                $drivers[$value->driver_id]->is_notified = 0;
                $drivers[$value->driver_id]->bookings = [];
                $drivers[$value->driver_id]->cash = 0;
                $drivers[$value->driver_id]->commission = 0;
            }

            $drivers[$value->driver_id]->cash += $value->cash;
            $drivers[$value->driver_id]->commission += $value->commission;
            $drivers[$value->driver_id]->bookings[] = $value->hash;

            $bookings[$value->hash]->drivers[] = $value->driver_id;
        }

        foreach ($drivers as $id => $driver) {
            $drivers[$id]->cash = round($driver->cash, 2);
            $drivers[$id]->commission = round($driver->commission, 2);
        }

        $this->bookingList = $bookings;

        return $drivers;
    }

    /**
     * @param $dataBookings
     * @param $filters
     * @return array
     */
    public function generateCustomerReport($dataBookings, $filters)
    {
        return [];
    }

    /**
     * @param $dataBookings
     * @param $filters
     * @return array
     */
    public function generatePaymentReport($dataBookings, $filters)
    {
        $bookings = $this->bookingList;
        $payments = [];
        $sitesData = Site::all();
        $paymentsListModel = [];
        $sites = [];

        foreach (Payment::all() as $p) {
            $paymentsListModel[$p->id] = $p;
        }

        foreach($sitesData as $k=>$v) {
            $sites[$v->id] = $v->name;
        }

        foreach ($dataBookings as $value) {
            $returnRoute = null;
            $totalWithDiscount = $value->total_price - $value->discount;
            $paymentCharge = 0;
            $paymentTotal = 0;
            $returnTotalWithDiscount = 0;

            foreach ($value->bookingAllRoutes as $k => $v) {
                if ($v->id != $value->id) {
                    $returnRoute = $v;
                    break;
                }
            }

            if (!empty($returnRoute)) {
                $returnTotalWithDiscount = $returnRoute->total_price - $returnRoute->discount;
            }

            foreach ($value->bookingTransactions as $transaction) {
                if ((!empty($filters->payment_method) && !in_array($transaction->payment_method, $filters->payment_method))
                    || (!empty($filters->payment_status) && !in_array($transaction->status, $filters->payment_status))
                ) {
                    continue;
                }

                if (empty($payments[$transaction->payment_id])) {
                    if ($transaction->payment_method == 'none' ) {
                        $transaction->payment_name = 'Unassigned Payment Method';
                    }

                    $payments[$transaction->payment_id] = [
                        'name' => $transaction->payment_name,
                        'method' => $transaction->payment_method,
                    ];
                    $paymentData = !empty($paymentsListModel[$transaction->payment_id]) ? $paymentsListModel[$transaction->payment_id] : new \stdClass();

                    if ($this->typeReport == 'payment') {
                        $payments[$transaction->payment_id]['id'] = $transaction->payment_id;
                    }

                    if (count($sites) > 1 && !empty($paymentData->site_id) && !empty($sites[$paymentData->site_id])) {
                        $payments[$transaction->payment_id]['site']= $sites[$paymentData->site_id];
                    }
                }

                if (!empty($returnRoute) && ($totalWithDiscount + $returnTotalWithDiscount)) {
                    $grandTotalWithDiscount = $totalWithDiscount + $returnTotalWithDiscount;
                    $amount = ($transaction->amount / $grandTotalWithDiscount) * $totalWithDiscount;
                    $charge = ($transaction->payment_charge / $grandTotalWithDiscount) * $totalWithDiscount;
                }
                else {
                    $amount = $transaction->amount;
                    $charge = $transaction->payment_charge;
                }

                $totalTransaction = round($amount + $charge, 2);
                $paymentCharge += round($charge, 2);
                $paymentTotal += $totalTransaction;

                if ($this->typeReport == 'payment') {
                    $status = '';

                    if (empty($payments[$transaction->payment_id]['status'][$transaction->status])) {
                        foreach ($transaction->options->status as $k => $v) {
                            if ($k == $transaction->status) {
                                $status = $v['name'];
                            }
                        }
                        $payments[$transaction->payment_id]['status'][$transaction->status] = [
                            'name' => $status,
                            'total' => 0,
                        ];
                    }

                    $payments[$transaction->payment_id]['status'][$transaction->status]['total'] += $totalTransaction;
                    if (empty($payments[$transaction->payment_id]['status'][$transaction->status]['bookings'])
                        || !in_array($value->hash, $payments[$transaction->payment_id]['status'][$transaction->status]['bookings'])
                    ) {
                        $payments[$transaction->payment_id]['status'][$transaction->status]['bookings'][] = $value->hash;
                    }
                }

                if (empty($bookings[$value->hash]->transactions[$transaction->payment_id][$transaction->status])) {
                    $bookings[$value->hash]->transactions[$transaction->payment_id][$transaction->status] = (object)[
                        'payment_charge' => 0,
                        'amount' => 0,
                    ];
                }

                $bookings[$value->hash]->transactions[$transaction->payment_id][$transaction->status]->payment_charge += $charge;
                $bookings[$value->hash]->transactions[$transaction->payment_id][$transaction->status]->amount += $amount;
            }

            $paymentTotal = round($paymentTotal, 2);
            $total = round($totalWithDiscount + $paymentCharge, 2);


            if ($total != $paymentTotal) {
                $this->unassignedbookingList[$value->id] = [
                    'ref_number' => $value->hash,
                    'amount' => (float)($paymentTotal - $total),
                ];
            }
        }

        if ($this->typeReport == 'payment') {
            foreach ($payments as $id => $payment) {
                ksort($payments[$id]['status']);
            }
            usort($payments, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        }

        $this->bookingList = $bookings;

        return $payments;
    }

    /**
     * @param $dates
     * @return mixed
     */
    private  function sortDates($dates)
    {
        usort($dates, function($a, $b) {
            $dateTimestamp1 = strtotime($a);
            $dateTimestamp2 = strtotime($b);
            return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
        });

        return $dates;
    }

    /**
     * @param $dataBookings
     * @return array
     */
    private  function parseBookings($dataBookings)
    {
        $bookings = [];

        foreach ($dataBookings as $booking) {
            $bookings[$booking->hash] =  (object) [
                'id' => $booking->id,
                'ref_number' => $booking->route_ref_number,
                'total_price' => $booking->total_price,
                'cash' => $booking->cash,
                'commission' => $booking->commission,
                'fleet_commission' => $booking->fleet_commission,
                'date' => $booking->date->format('Y-m-d'),
                'drivers' => [],
                'transactions' => [],
            ];
        }
        return $bookings;
    }

    /**
     * @param $booking
     * @return string
     */
    private  function getIndex($booking)
    {
        try {
            return md5($booking->id . $booking->route_ref_number . $booking->date->timestamp);
        }
        catch (\Exception $e) {
            return md5($booking->id . $booking->route_ref_number . strtotime($booking->date));
        }
    }

    /**
     * @param $request
     * @param $filters
     * @return mixed
     */
    protected function getFilteredBookings($request, $filters)
    {
        $bookingRouteTable = (new BookingRoute())->getTable();
        $bookingRouteTableWithPrefix = get_db_prefix() . $bookingRouteTable;

        $bookings = BookingRoute::select('cash', 'commission', 'date', 'fleet_id', 'fleet_commission', 'driver_id', 'driver_data', 'total_price', 'discount', 'booking_id', 'id', 'ref_number as route_ref_number')
            ->with([
                'bookingTransactions',
                'bookingAllRoutes',
            ]);

        if (!empty($filters->scheduled_route)) {
            $filters->scheduled_route = explode(',', preg_replace('#(, | )#', ',', $filters->scheduled_route));
            $bookings->whereIn('scheduled_route_id', $filters->scheduled_route);
        }

        if (!empty($filters->booking_type)) {
            if (in_array('parent', $filters->booking_type) && !in_array('child', $filters->booking_type)) {
                $bookings->where('parent_booking_id', 0);
            }
            elseif (!in_array('parent', $filters->booking_type) && in_array('child', $filters->booking_type)) {
                if ( !empty($filters->parent_booking) ) {
                    $filters->parent_booking = explode(',', preg_replace('#(, | )#', ',', $filters->parent_booking));
                    $bookings->whereIn('parent_booking_id', $filters->parent_booking);
                }
                else {
                    $bookings->where('parent_booking_id', '>', 0);
                }
            }
        }

        if (!empty($filters->services) && count((array)$filters->services) > 0) {
            foreach ($filters->services as $sid=>$s) {
                if (empty($s)) {
                    unset($filters->services[$sid]);
                }
            }

            if (count((array)$filters->services) > 0) {
                $bookings->whereIn('service_id', (array)$filters->services);
            }
        }

        if (!empty($filters->source) && count((array)$filters->source) > 0) {
            foreach ($filters->source as $sid=>$s) {
                if (empty($s)) {
                    unset($filters->source[$sid]);
                }
            }

            if (count((array)$filters->source) > 0) {
                $bookings->whereIn('source', (array)$filters->source);
            }
        }

        if (!empty($filters->status) && count((array)$filters->status) > 0) {
            foreach ($filters->status as $sid=>$s) {
                if (empty($s)) {
                    unset($filters->status[$sid]);
                }
            }

            if (count((array)$filters->status) > 0) {
                $bookings->whereIn('status', (array)$filters->status);
            }
        }

        if (!empty($filters->payment_method) || !empty($filters->payment_status)) {
            $bookings->whereIn('booking_id', function($query) use (
                $filters,
                $bookingRouteTableWithPrefix
            ) {
                $paymentStatus = !empty($filters->payment_status) ? $filters->payment_status : [];
                $paymentMethod = !empty($filters->payment_method) ? $filters->payment_method : [];

                $transactionTable =  (new \App\Models\Transaction())->getTable();
                $query->select('relation_id')
                    ->from($transactionTable)
                    ->where('relation_type', 'booking')
                    ->whereRaw('relation_id = '.$bookingRouteTableWithPrefix.'.booking_id');

                if (!empty($paymentMethod)) {
                    $query->whereIn('payment_method', (array)$paymentMethod);
                }

                if (!empty($paymentStatus)) {
                    $query->whereIn('status', (array)$paymentStatus);
                }
            });
        }

        if (!empty($filters->fleets) ) {
            $bookings->whereIn('fleet_id', (array)$filters->fleets);
        }
        if (!empty($request->type) && $request->type == 'fleet') {
            $bookings->where('fleet_id', '!=', '0');
        }

        if (!empty($filters->drivers) ) {
            $bookings->whereIn('driver_id', (array)$filters->drivers);
        }
        if (!empty($filters->customers) ) {
            $bookings->whereIn('user_id', (array)$filters->customers);
        }
        if (!empty($filters->from_date) || !empty($filters->to_date)) {
            $dateType = $filters->date_type;
            $column = 'date';
            if (in_array($dateType, ['created_date', 'date', 'modified_date'])) {
                $column = $dateType;
            }
            if (!empty($filters->from_date) && $fromDate = \Carbon\Carbon::parse($filters->from_date)) {
                $bookings->where($column, '>=', $fromDate->toDateTimeString());
            }
            if (!empty($filters->to_date) && $toDate = \Carbon\Carbon::parse($filters->to_date)) {
                $bookings->where($column, '<=', $toDate->toDateTimeString());
            }
        }

        if (!empty($filters->search)) {
            $search = \App\Helpers\SiteHelper::makeStrSafe( (string) $filters->search );
            $bookings->where(function($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%');
                $query->orWhere('code', 'like', '%' . $search . '%');
                $query->orWhere('start_date', 'like', '%' . $search . '%');
                $query->orWhere('end_date', 'like', '%' . $search . '%');
                $query->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $ordered = false;

        if (!empty($request->sort) ) {
            foreach ($request->sort as $key => $value) {
                if (in_array($value['property'], $this->bookingRouteColumns)) {
                    $duration = in_array((string)$value['direction'], ['asc','desc']) ? $value['direction'] : 'asc';
                    $bookings->orderBy($value['property'], $duration);
                    $ordered = true;
                }
            }
        }
        if (!$ordered) {
            $bookings->orderBy('date', 'asc');
        }

        $bookings = $bookings->get();

        foreach ($bookings as $bid=>$booking) {
            $bookings[$bid]->hash = $this->getIndex($booking);
        }

        return $bookings;
    }
}
