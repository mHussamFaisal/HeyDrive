<?php

namespace App\Helpers;

class ReportHelper
{
    public static function getServicesList($selected = [], $siteId = false) {
        $siteId = $siteId ?: config('site.site_id');

        $services = \App\Models\Service::where('relation_type', 'site')
            ->where('relation_id', $siteId)
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        foreach($services as $k => $v) {
            $params = $v->getParams('raw');
            $services[$k]->availability = (int)$params->availability;
            $services[$k]->hide_location = (int)$params->hide_location;
            $services[$k]->duration = (int)$params->duration;
            $services[$k]->duration_min = (int)$params->duration_min;
            $services[$k]->duration_max = (int)$params->duration_max;
            $services[$k]->selected = in_array($v->id, $selected);
        }

        $k = isset($k) ? $k+1 : 0;
        $services = $services ?: [];
        $services[$k] = new \App\Models\Service();
        $params = $services[$k]->getParams('raw');
        $services[$k]->id = 0;
        $services[$k]->name = trans('booking.booking_unassigned');
        $services[$k]->type = 'standard';
        $services[$k]->availability = (int)$params->availability;
        $services[$k]->hide_location = (int)$params->hide_location;
        $services[$k]->duration = (int)$params->duration;
        $services[$k]->duration_min = (int)$params->duration_min;
        $services[$k]->duration_max = (int)$params->duration_max;
        $services[$k]->selected = false;

        return $services;
    }

    public static function getSourcesList($selected = [], $siteId = false) {
        $siteId = $siteId ?: config('site.site_id');

        $sourceNotIn = [];
        $sourceList = [];

        $configSource = config('eto_booking.sources');

        foreach($configSource as $key => $value) {
            $sourceList[] = ['id'=> $value,'name'=> $value];
            $sourceNotIn[] = $value;
        }

        $resultsSites = \App\Models\Site::select('id', 'name')
            ->where('published', '1')
            ->orderBy('ordering', 'asc')
            ->orderBy('name', 'desc')
            ->get();

        foreach($resultsSites as $key => $value) {
            if (empty($value->name)) {
                $sourceList[] = ['id' => $value->name, 'name' => $value->name];
                $sourceNotIn[] = $value->name;
            }
        }

        $bookingSources = \App\Models\BookingRoute::select('source')
            ->distinct('source')
            ->whereNotIn('source', $sourceNotIn)
            ->orderBy('source', 'desc')
            ->get();

        foreach($bookingSources as $key => $value) {
            if (!empty($value->source)) {
                $sourceList[] = ['id' => $value->source, 'name' => $value->source];
            }
        }

        $sourceList[] = ['id' => '', 'name' => trans('booking.booking_unassigned')];

        foreach($sourceList as $k=>$v) {
            $sourceList[$k]['selected'] = in_array($v['id'], $selected);
        }

        return json_decode(json_encode($sourceList));
    }

    public static function getStatusesList($selected = []) {
        $statuses = (new \App\Models\BookingRoute)->getStatusList();

        foreach ($statuses as $k=>$v) {
            $statuses[$k]->selected = in_array($v->value, $selected);
        }

        return $statuses;
    }

    public static function getPaymentMethodsList($selected = [], $siteId = false) {
        $siteId = $siteId ?: config('site.site_id');
        $paymentMethod = \App\Models\Payment::where('site_id', '=', $siteId)->where('published', '=', '1')->get();

        foreach($paymentMethod as $k => $v) {
            $paymentMethod[$k]->selected = in_array($v->method, $selected);
        }

        return $paymentMethod;
    }

    public static function getPaymentStatusList($selected = []) {
        $statuses = (new \App\Models\Transaction)->getStatusList();

        foreach ($statuses as $k=>$v) {
            $statuses[$k]->selected = in_array($v->value, $selected);
        }

        return $statuses;
    }

    public static function getDriverList($selected = []) {
        $tnUser = (new \App\Models\User)->getTable();
        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        $drivers = \App\Models\User::join($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id')
            ->select($tnUser .'.*', $tnUserProfile .'.unique_id')
            ->role('driver.*')
            ->where($tnUser .'.status', 'approved')
            ->orderBy($tnUserProfile .'.unique_id', 'asc')
            ->orderBy($tnUser .'.name', 'asc')
            ->get();

        foreach ($drivers as $k=>$v) {
            $drivers[$k]->selected = in_array($v->id, $selected);
        }

        return $drivers;
    }

    public static function getCustomerList($selected = []) {
        $customers = \App\Models\Customer::where('type', 1)
            ->orderBy('name', 'asc')
            ->get();

        foreach ($customers as $k=>$v) {
            $customers[$k]->selected = in_array($v->id, $selected);
        }

        return $customers;
    }

    public static function getFleetList($selected = []) {
        $fleets = \App\Models\User::role('admin.fleet_operator')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($fleets as $k=>$v) {
            $fleets[$k]->selected = in_array($v->id, $selected);
        }

        return $fleets;
    }

    public static function getDateTypesList($selected = '') {
        return json_decode(json_encode([
            [
                'id' => '',
                'name' => '',
                'selected' => false
            ], [
                'id' => 'date',
                'name' => 'Journey Date',
                'selected' => 'date' == $selected,
            ], [
                'id' => 'created_date',
                'name' => 'Created Date',
                'selected' => 'created_date' == $selected,
            ],/* [
                'id' => 'modified_date',
                'name' => 'Modified Date',
                'selected' => 'modified_date' == $selected,
            ]*/
        ]));
    }
}
