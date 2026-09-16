<?php

namespace App\Helpers;

use Carbon\Carbon;

class AdminHelper
{
    public static function getSitesList()
    {
        $data = [];

        $sites = \App\Models\Site::select('id', 'name')
          ->where('published', 1)
          ->orderBy('ordering', 'asc')
          ->orderBy('name', 'asc')
          ->get();

        foreach($sites as $site) {
            $data[] = (object)[
                'value' => $site->id,
                'text' => $site->name,
                'selected' => config('site.site_id') == $site->id ? true : false,
            ];
        }

        return $data;
    }

    public static function getFeedbackCounts($list = null)
    {
        $list = !empty($list) ? (is_array($list) ? $list : [$list]) : [];

        $map = [
            'comment' => ['comment'],
            'lost_found' => ['lost_found'],
            'complaint' => ['complaint'],
        ];

        $types = [];
        $sql = [];

        foreach ($map as $k => $v) {
            if (empty($list) || in_array($k, $list)) {
                $types = array_merge($types, $v);

                $case = [];
                foreach ($v as $v2) { $case[] = "`type` = '{$v2}'"; }
                $case = implode(' or ', $case);
                $sql[] = "sum(case when ({$case}) then 1 else 0 end) AS `{$k}`";
            }
        }

        $sql = implode(',', $sql);

        $query = \App\Models\Feedback::selectRaw($sql)
          ->whereIn('type', $types)
          ->where('is_read', 0)
          ->first();

        $counts = (object)$query->toArray();

        $data = [
            'comment' => !empty($counts->comment) ? (int)$counts->comment : 0,
            'lost_found' => !empty($counts->lost_found) ? (int)$counts->lost_found : 0,
            'complaint' => !empty($counts->complaint) ? (int)$counts->complaint : 0,
        ];

        return (object)$data;
    }

    public static function getBookingCounts($list = null)
    {
        $list = !empty($list) ? (is_array($list) ? $list : [$list]) : [];

        $map = [
            'latest' => [
                'pending', 'confirmed', 'assigned', 'auto_dispatch', 'accepted',
                'rejected', 'onroute', 'arrived', 'onboard', 'quote'
            ],
            'requested' => ['requested'],
            // 'completed' => ['completed'],
            // 'canceled' => ['canceled'],
        ];

        $status = [];
        $sql = [];

        // $sql[] = "sum(case when `is_read` = 0 then 1 else 0 end) AS `latest`";

        foreach ($map as $k => $v) {
            if (empty($list) || in_array($k, $list)) {
                $status = array_merge($status, $v);

                $case = [];
                foreach ($v as $v2) { $case[] = "`status` = '{$v2}'"; }
                $case = implode(' or ', $case);

                // || $k == 'requested'
                if ($k == 'latest') {
                    $case = "(`is_read` = 0 and ({$case}))";
                }

                $sql[] = "sum(case when ({$case}) then 1 else 0 end) AS `{$k}`";
            }
        }

        $sql = implode(',', $sql);

        $query = \App\Models\BookingRoute::selectRaw($sql)->whereIn('status', $status);
        // ->orWhere('is_read', 0);

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $query->where('fleet_id', auth()->user()->id);
        }

        // dd($query->toSql());
        $query = $query->first();
        $counts = (object)$query->toArray();

        $data = [
            'next24' => 0,
            'latest' => !empty($counts->latest) ? (int)$counts->latest : 0,
            'requested' => !empty($counts->requested) ? (int)$counts->requested : 0,
            'completed' => !empty($counts->completed) ? (int)$counts->completed : 0,
            'canceled' => !empty($counts->canceled) ? (int)$counts->canceled : 0,
        ];

        return (object)$data;
    }

    public static function checkUserDocuments()
    {
        if (!auth()->user()->hasPermission(['admin.users.driver.index', 'admin.users.driver.show'])) {
            return '';
        }

        $cacheData = \Cache::store('file')->get('admin_check_user_documents');
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
        $query = \App\Models\User::with('profile')->role('driver.*')->where($tnUser .'.status', 'approved');

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

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $query->where($tnUser .'.fleet_id', auth()->user()->id);
        }

        $users = $query->get();

        $expired = "";

        foreach($users as $user) {
            $item = "";

            foreach($check as $k => $v) {
                if ( !empty($user->profile) && $user->profile->getOriginal($k) ) {
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
                $expired .= "<div>{$user->getName(true)}: ". rtrim($item, ', ') ."</div>";
            }
        }

        if ( $expired ) {
            $expired = "<div style='font-size:11px; text-align:left;'><div style='font-weight:bold; margin-bottom:5px;'>Expiring Documents:</div>{$expired}</div>";
        }

        \Cache::store('file')->put('admin_check_user_documents', $expired, config('eto_booking.admin_check_user_documents_cache_time'));

        return $expired;
    }

    public static function checkVehicleDocuments()
    {
        $cacheData = \Cache::store('file')->get('admin_check_vehicle_documents');
        if (!empty($cacheData)) {
            return $cacheData;
        }

        $check = [
            'mot_expiry_date' => 'MOT'
        ];

        $query = \App\Models\Vehicle::with(['user', 'user.profile'])->where('status', 'activated');

        if ( !empty($check) ) {
            $query->where(function($query) use($check) {
                foreach($check as $k => $v) {
                    $query->orWhere([
                        [$k, '<=', Carbon::now()->addDays(config('site.document_warning'))],
                        [$k, '<>', null]
                    ]);
                }
            });
        }

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $query->where('fleet_id', auth()->user()->id);
        }

        $vehicles = $query->get();

        $expired = "";

        foreach($vehicles as $vehicle) {
            $item = "";

            foreach($check as $k => $v) {
                if ( $vehicle->getOriginal($k) ) {
                    $class = "";

                    if ( Carbon::parse($vehicle->getOriginal($k)) <= Carbon::now()->addDays(config('site.document_expired')) ) {
                        $class = "text-red";
                    }
                    elseif ( Carbon::parse($vehicle->getOriginal($k)) <= Carbon::now()->addDays(config('site.document_warning')) ) {
                        $class = "text-yellow";
                    }

                    if ( $class ) {
                        $item .= "<span class='{$class}'>{$v}</span>, ";
                    }
                }
            }

            if ($item) {
                $ownerName = 'Unknown';
                if ($vehicle->user) {
                    $ownerName = $vehicle->user->getName(true);
                }
                $expired .= "<div>{$ownerName}: ". rtrim($item, ', ') ."</div>";
            }
        }

        if ( $expired ) {
            $expired = "<div style='font-size:11px; text-align:left;'><div style='font-weight:bold; margin-bottom:5px;'>Expiring Documents:</div>{$expired}</div>";
        }

        \Cache::store('file')->put('admin_check_vehicle_documents', $expired, config('eto_booking.admin_check_vehicle_documents_cache_time'));

        return $expired;
    }
}
