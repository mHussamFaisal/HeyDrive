<?php

namespace App\Helpers;

use Carbon\Carbon;

class DriverHelper
{
    public static function getBookingCounts($userId = 0, $list = null)
    {
        $userId = $userId ?: auth()->user()->id;
        $list = !empty($list) ? (is_array($list) ? $list : [$list]) : [];

        $map = [
            'assigned' => ['assigned', 'auto_dispatch'],
            'accepted' => ['accepted', 'onroute', 'arrived', 'onboard'],
            'completed' => ['completed'],
            'canceled' => ['canceled', 'unfinished'],
            'current' => ['onroute', 'arrived', 'onboard'],
        ];

        $status = [];
        $sql = [];
        $sqlStatus = \App\Models\BookingRoute::__driverStatusSql();

        foreach ($map as $k => $v) {
            if (empty($list) || in_array($k, $list)) {
                $status = array_merge($status, $v);

                $case = [];
                foreach ($v as $v2) { $case[] = "{$sqlStatus} = '{$v2}'"; }
                $case = implode(' or ', $case);
                $sql[] = "sum(case when ({$case}) then 1 else 0 end) AS `{$k}`";
            }
        }

        $sql = implode(',', $sql);

        $query = \App\Models\BookingRoute::selectRaw($sql)
          ->withBookingDriver($userId, false)
          ->where('parent_booking_id', 0)
          ->whereInDriverStatus($status)
          ->whereDriver($userId)
          ->first();

        $counts = (object)$query->toArray();

        $data = [
            'assigned' => !empty($counts->assigned) ? (int)$counts->assigned : 0,
            'accepted' => !empty($counts->accepted) ? (int)$counts->accepted : 0,
            'completed' => !empty($counts->completed) ? (int)$counts->completed : 0,
            'canceled' => !empty($counts->canceled) ? (int)$counts->canceled : 0,
            'current' => !empty($counts->current) ? (int)$counts->current : 0,
        ];

        return (object)$data;
    }

    public static function getCurrentJobUrl($userId = 0, $amount = 0)
    {
        $userId = $userId ?: auth()->user()->id;

        if ($amount == 1) {
            $query = \App\Models\BookingRoute::where('parent_booking_id', 0)
              ->whereIn('status', ['onroute', 'arrived', 'onboard'])
              ->whereDriver($userId)
              ->first();

            if (!empty($query->id)) {
                return route('driver.jobs.show', $query->id);
            }
        }

        return route('driver.jobs.index') .'?status=current';
    }

    public static function checkUserDocuments($userId = 0)
    {
        $userId = $userId ?: auth()->user()->id;

        $cacheData = \Cache::store('file')->get('driver_check_user_documents_'. $userId);
        if (!empty($cacheData)) {
            return $cacheData;
        }

        $check = [
            'insurance_expiry_date' => 'Insurance',
            'driving_licence_expiry_date' => 'Driving Licence',
            'pco_licence_expiry_date' => 'PCO Licence',
            'phv_licence_expiry_date' => 'PHV Licence'
        ];

        $tnUser = (new \App\Models\User)->getTable();
        $query = \App\Models\User::query();
        $query->where($tnUser .'.id', '=', $userId);
        $query->role('driver.*');
        $query->where($tnUser .'.status', 'approved');

        if ( !empty($check) ) {
            $query->whereHas('profile', function($query) use($check) {
                foreach($check as $k => $v) {
                    $query->orWhere([
                        [$k, '<=', Carbon::now()->addDays(config('site.document_warning'))],
                        [$k, '<>', null]
                    ]);
                }
            });
        }

        $users = $query->get();

        $expired = "";

        foreach($users as $user) {
            $item = "";

            foreach($check as $k => $v) {
                if ( $user->profile->getOriginal($k) ) {
                    $class = "";

                    if ( Carbon::parse($user->profile->getOriginal($k)) <= Carbon::now()->addDays(config('site.document_expired')) ) {
                        $class = "text-red";
                    }
                    elseif ( Carbon::parse($user->profile->getOriginal($k)) <= Carbon::now()->addDays(config('site.document_warning')) ) {
                        $class = "text-yellow";
                    }

                    if ( $class ) {
                        $item .= "<span class='{$class}'>{$v}</span>, ";
                    }
                }
            }

            if ( $item ) {
                $expired .= "<div>". rtrim($item, ', ') ."</div>";
            }
        }

        if ( $expired ) {
            $expired = "<div style='font-size:11px; text-align:left;'><div style='font-weight:bold; margin-bottom:5px;'>Expiring Documents:</div>{$expired}</div>";
        }

        \Cache::store('file')->put('driver_check_user_documents_'. $userId, $expired, config('eto_booking.driver_check_user_documents_cache_time'));

        return $expired;
    }

    public static function getIncome(\stdClass $routeCharges, int $bookingId, int $driverId = 0)
    {
        $excluded = 0;

//        foreach ($routeCharges->routes as $route) {
//            if ((int)$route->id === $bookingId) {
//                if (!empty($route->fleet_commission)) {
//                    $excluded = $route->fleet_commission;
//                } elseif ((int)config('eto_driver.income.child_seats') === 0
//                    || (int)config('eto_driver.income.additional_items') === 0
//                    || (int)config('eto_driver.income.parking_charges') === 0
//                    || (int)config('eto_driver.income.payment_charges') === 0
//                    || (int)config('eto_driver.income.meet_and_greet') === 0
//                    || (int)config('eto_driver.income.discounts') === 0
//                ) {
//                    if ((int)config('eto_driver.income.child_seats') === 0) {
//                        $excluded += $route->child_seats;
//                    }
//                    if ((int)config('eto_driver.income.additional_items') === 0) {
//                        $excluded += $route->additional_items;
//                    }
//                    if ((int)config('eto_driver.income.parking_charges') === 0) {
//                        $excluded += $route->parking_charges;
//                    }
//                    if ((int)config('eto_driver.income.payment_charges') === 0) {
//                        $excluded += $route->payment_charges;
//                    }
//                    if ((int)config('eto_driver.income.meet_and_greet') === 0) {
//                        $excluded += $route->meet_and_greet;
//                    }
//                    if ((int)config('eto_driver.income.discounts') === 1) {
//                        $excluded += $route->discounts;
//                    }
//                }
//
//                break;
//            }
//        }

        if ((int)config('eto_driver.income.child_seats') === 0
            || (int)config('eto_driver.income.additional_items') === 0
            || (int)config('eto_driver.income.parking_charges') === 0
            || (int)config('eto_driver.income.payment_charges') === 0
            || (int)config('eto_driver.income.meet_and_greet') === 0
            || (int)config('eto_driver.income.discounts') === 0
        ) {
            foreach ($routeCharges->routes as $route) {
                if ((int)$route->id === $bookingId) {
                    if ((int)config('eto_driver.income.child_seats') === 0) {
                        $excluded += $route->child_seats;
                    }
                    if ((int)config('eto_driver.income.additional_items') === 0) {
                        $excluded += $route->additional_items;
                    }
                    if ((int)config('eto_driver.income.parking_charges') === 0) {
                        $excluded += $route->parking_charges;
                    }
                    if ((int)config('eto_driver.income.payment_charges') === 0) {
                        $excluded += $route->payment_charges;
                    }
                    if ((int)config('eto_driver.income.meet_and_greet') === 0) {
                        $excluded += $route->meet_and_greet;
                    }
                    if ((int)config('eto_driver.income.discounts') === 1) {
                        $excluded += $route->discounts;
                    }
                }
            }
        }

        if ($excluded > 0 && $driverId > 0 && $driver = \App\Models\User::find($driverId)) {
            return ($excluded * $driver->profile->commission) / 100;
        }

        return $excluded;
    }
}
