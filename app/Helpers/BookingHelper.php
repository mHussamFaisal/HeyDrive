<?php

namespace App\Helpers;

use App\Models\BookingRoute;

class BookingHelper
{
    public static function getBookingRouteInstance($booking) {
        if (!is_subclass_of($booking, 'Illuminate\Database\Eloquent\Model')) {
            try {
                return BookingRoute::hydrate([(array)$booking])[0];
            }
            catch (\Exception $e) {
                return null;
            }
        }
        return $booking;
    }

    public static function getChargesPerRoute($booking, $formatType = '', $dataType = '') {
        $booking = BookingHelper::getBookingRouteInstance($booking);
        if ($booking) {
            return $booking->getCharges($formatType, $dataType);
        }
        return false;
    }

    public static function getExcludePerRoute($booking) {
        $booking = BookingHelper::getBookingRouteInstance($booking);
        if ($booking) {
            return $booking->getExcludePerRoute();
        }
        return false;
    }

    public static function getDiscountPerRoute($bookingTmp, $discount)
    {
        $charges = [];
        $total_route1 = 0;
        $total_route2 = 0;
        $percent_route1 = 100;
        $percent_route2 = 0;
        $exclude_route1 = 0;
        $exclude_route2 = 0;
        $bookings = [];

        foreach($bookingTmp as $route=>$booking) {
            $bookings[$route] = self::getBookingRouteInstance($booking);
            if ($route == 'route1') {
                $total_route1 = $bookings[$route]->total_price ?: 0;
                $exclude_route1 = self::getExcludePerRoute($bookingTmp[$route]);
            }
            else {
                $total_route2 = $bookings[$route]->total_price ?: 0;
                $exclude_route2 = self::getExcludePerRoute($bookingTmp[$route]);
            }
        }

        if ((float)$total_route2 > 0) {
            $total = ($total_route1 - $exclude_route1) + ($total_route2 - $exclude_route2);
            $percent_route1 = (($total_route1 - $exclude_route1) * 100) / $total;
            $percent_route2 = 100 - $percent_route1;
        }

        foreach($bookings as $route=>$booking) {
            if ($route == 'route1') {
                $exclude = $exclude_route1;
                $totalRoute = $total_route1 - $exclude;
                $percentRoute = $percent_route1;
            }
            else {
                $exclude = $exclude_route2;
                $totalRoute = $total_route2 - $exclude;
                $percentRoute = $percent_route2;
            }

            $bookingTmp[$route]->discount = self::getDiscount($totalRoute, $percentRoute, $discount);
            $bookingTmp[$route]->totalWithoutDiscount = ($totalRoute - $bookingTmp[$route]->discount) + $exclude;

            if ($bookingTmp[$route]->totalWithoutDiscount <= 0) {
                $bookingTmp[$route]->totalWithoutDiscount = 0;
            }

            $booking->charges = $booking->getCharges('route');

            foreach($booking->charges as $charge=>$value) {
                if ((float)$value > 0) {
                    if (empty($charges[$charge])) {
                        $charges[$charge] = 0;
                    }
                    $charges[$charge] += $value;
                }
            }
        }
        $bookingTmp['discountExcludedInfo'] = self::getExcludedInfo($charges);

        return $bookingTmp;
    }

    protected static function getDiscount($total, $percent, $discount)
    {
        if ($discount->type == 1) {
            return abs(($total / 100) * $discount->value);
        }
        else {
            $maxDiscount = ($discount->value * $percent) / 100;
            if ($total - $maxDiscount < 0) {
                $maxDiscount = $total;
            }

            return abs($maxDiscount);
        }
    }

    public static function getExcludedInfo($charges)
    {
        $exclude = [];

        if ((int)$charges['child_seats'] > 0 && (int)config('eto_booking.discount.child_seats') === 0) {
            $exclude[] = trans('driver/jobs.child_seats');
        }
        if ((int)$charges['additional_items'] > 0 && (int)config('eto_booking.discount.additional_items') === 0) {
            $exclude[] = trans('admin/config.driver_income.additional_items');
        }
        if ((int)$charges['parking_charges'] > 0 && (int)config('eto_booking.discount.parking_charges') === 0) {
            $exclude[] = trans('admin/config.driver_income.parking_charges');
        }
        if ((int)$charges['payment_charges'] > 0 && (int)config('eto_booking.discount.payment_charges') === 0) {
            $exclude[] = trans('admin/config.driver_income.payment_charges');
        }
        if ((int)$charges['meet_and_greet'] > 0 && (int)config('eto_booking.discount.meet_and_greet') === 0) {
            $exclude[] = trans('driver/jobs.meet_and_greet');
        }

        if (count($exclude) > 0) {
            return trans('booking.charges_excluded') . " " . implode(', ', $exclude);
        }

        return false;
    }

    public static function getDriverList($type = 'none')
    {
        $data = [];
        $tnUser = (new \App\Models\User)->getTable();
        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        $query = \App\Models\User::join($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id')
        ->select($tnUser .'.id', $tnUser .'.name', $tnUserProfile .'.unique_id', $tnUserProfile .'.commission')
        ->role('driver.*')
        ->where($tnUser .'.status', 'approved');

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $query->where('fleet_id', auth()->user()->id);
        }

        $query = $query->orderBy($tnUserProfile .'.unique_id', 'asc')->orderBy($tnUser .'.name', 'asc')->get();

        foreach($query as $k => $v) {
            $data[] = (object)[
                'value' => $v->id,
                'text' => $v->getName(true),
                'commission' => !empty($v->commission) ? $v->commission : 0
            ];
        }

        $data[] = (object)[
            'value' => 0,
            'text' => trans('admin/bookings.assign_driver'),
            'commission' => 0
        ];

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getVehicleList($id = null, $type = 'none')
    {
        $data = [];

        $query = \App\Models\Vehicle::select('id', 'user_id', 'name')->where('status', 'activated');
        if ($id) {
            $query->where('user_id', $id);
        }
        $query = $query->orderBy('selected', 'desc')->orderBy('name', 'asc')->get();

        foreach($query as $k => $v) {
            $data[] = (object)[
                'value' => $v->id,
                'text' => $v->getName(),
                'driver_id' => $v->user_id
            ];
        }

        if (empty($data)) {
            $data[] = (object)[
                'value' => 0,
                'text' => trans('admin/bookings.assign_vehicle'),
                'driver_id' => 0
            ];
        }

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getPaymentMethodList($type = 'none')
    {
        $data = [];

        $query = \App\Models\Payment::select('method', 'name')
          // ->where('site_id', $siteId)
          ->where('published', '1')
          ->distinct('method')
          ->orderBy('name', 'asc')
          ->get();

        foreach($query as $k => $v) {
            $data[] = (object)[
                'value' => $v->method,
                'text' => $v->name
            ];
        }

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getSourceList($type = 'none')
    {
        $data = [];

        $query = \App\Models\BookingRoute::select('source')
          // ->where('site_id', $siteId)
          ->distinct('source')
          ->orderBy('source', 'asc')
          ->get();

        foreach($query as $k => $v) {
            $data[] = (object)[
                'value' => $v->source,
                'text' => $v->source
            ];
        }

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getServiceList($type = 'none')
    {
        $data = [];

        $query = \App\Models\Service::where('relation_type', 'site')
        	// ->where('relation_id', $siteId)
        	->where('status', 'active')
        	->orderBy('order', 'asc')
        	->orderBy('name', 'asc')
        	->get();

        foreach($query as $k => $v) {
            $params = $v->getParams('raw');

            $data[] = (object)[
                'id' => (int)$v->id,
            		'name' => (string)$v->name,
            		'type' => (string)$v->type,
            		'availability' => (int)$params->availability,
            		'hide_location' => (int)$params->hide_location,
            		'duration' => (int)$params->duration,
            		'duration_min' => (int)$params->duration_min,
            		'duration_max' => (int)$params->duration_max,
            		'selected' => $v->is_featured
            ];
        }

        $data[] = (object)[
            'id' => 0,
          	'name' => 'Unassigned',
          	'type' => 'standard',
          	'hide_location' => 0,
          	'availability' => 0,
          	'duration' => 0,
          	'duration_min' => 1,
          	'duration_max' => 10,
          	'selected' => 0
        ];

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getCustomerList($type = 'none')
    {
        $data = [];

        $query = \App\Models\Customer::with('profile')
        	->where('type', 1)
        	->orderBy('name', 'asc')
        	->get();

        foreach($query as $k => $v) {
            $data[] = (object)[
                'value' => $v->id,
                'text' => (!empty($v->profile->company_name) && config('site.user_show_company_name') ? trim($v->profile->company_name) . ' - ' : '') . (!empty($v->name) ? $v->name : 'Unknown')
            ];
        }

        $data[] = (object)[
            'value' => 0,
            'text' => 'Unassigned'
        ];

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getFleetList($type = 'none')
    {
        $data = [];

        if (config('eto.allow_fleet_operator')) {
            $tnUser = (new \App\Models\User)->getTable();

            $query = \App\Models\User::select($tnUser .'.id', $tnUser .'.name')
                ->with('profile')
                ->role('admin.fleet_operator')
                ->orderBy($tnUser .'.name', 'asc')
                ->get();

            foreach($query as $k => $v) {
                $data[] = (object)[
                    'value' => $v->id,
                    'text' => $v->getName(false),
                    'commission' => $v->profile->commission
                ];
            }
        }

        $data[] = (object)[
            'value' => 0,
            'text' => trans('admin/users.fleet_select')
        ];

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function getDateTypeList($type = 'none')
    {
        $data = (object)[
            [
                'value' => '',
                'text' => ''
            ], [
                'value' => 'date',
                'text' => 'Journey Date'
            ], [
                'value' => 'created_date',
                'text' => 'Created Date'
            ]
        ];

        if ($type == 'json') {
            $data = json_encode($data);
        }

        return $data;
    }

    public static function setActiveDriver($booking, $newStatus, $userId)
    {
        if (config('eto.stats_active_drivers_created')) {
            $dates = calculate_billing_date(config('eto.last_driver_count'));

            if ($dates !== null && $newStatus == 'completed' && $booking->status != $newStatus) {
                $date = end($dates);
                $stats = \App\Models\StatsActiveDriver::where('driver_id', $userId);

                if ($date->to) {
                    if ($date->from) {
                        $stats->whereBetween('created_at', [$date->from, $date->to]);
                    } else {
                        $stats->where('created_at', '<=', $date->to);
                    }
                }

                $stats = $stats->first();

                if (!$stats) {
                    $user = get_user($userId);
                    $stats = new \App\Models\StatsActiveDriver();
                    $stats->subscription_id = request()->system->subscription->id;
                    $stats->driver_id = $userId;
                    $stats->driver_name = $user->getName();
                    $stats->ref_number = $booking->ref_number;
                    $stats->job_count = 1;
                } else {
                    $stats->job_count = $stats->job_count + 1;
                }

                $stats->save();
            }
        }
    }
}
