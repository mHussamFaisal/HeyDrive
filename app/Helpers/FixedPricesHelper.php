<?php

namespace App\Helpers;

class FixedPricesHelper
{
    protected $siteId;

    protected $columns = [
        'id' => 'id',
        'service_ids' => 'service_ids',
        'is_zone' => 'is_zone',
        'site_id' => 'site_id',
        'type' => 'type',
        'start_type' => 'start_type',
        'start_postcode' => 'start_postcode',
        'start_date' => 'start_date',
        'direction' => 'direction',
        'end_type' => 'end_type',
        'end_postcode' => 'end_postcode',
        'end_date' => 'end_date',
        'value' => 'value',
        'params' => 'params',
        'modified_date' => 'modified_date',
        'ordering' => 'ordering',
        'published' => 'published'
    ];

    public $typeList = [
        [
            'value' => '1',
            'text' =>  'Fixed Price'
        ], [
            'value' => '2',
            'text' =>  'Modify Mileage Price'
        ]
    ];

    public function __construct($siteId = false)
    {
        if (!empty($siteId) && !is_array($siteId)) {
            $siteId = [$siteId];
        }

        $this->siteId = $siteId;
    }

    public function getLocationList() {
        $zones = \App\Models\Location::select('id', 'name')
            ->where('relation_type', 'site')
            ->where('relation_id', '0')
            ->ofType('zone')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')->get();

        $zoneList = [];
        foreach ($zones as $key => $zone) {
            $zoneList[] = [
                'value' => $zone->id,
                'text' =>  $zone->name
            ];
        }
        return $zoneList;
    }

    public function getLocationPostcodeList($categoryIds = [], $joinCategory = false, $select = 'location.id', $select2 = 'location.name') {
        $postcodeList = [];

        $postCodeLocations = \App\Models\LocationPostcode::select($select, $select2);
        if ($this->siteId) {
            $postCodeLocations->whereIn('location.site_id', $this->siteId);
        }

        if (!empty($categoryIds)) {
            $postCodeLocations->whereIn('category_id', $categoryIds);
        }
        if ($joinCategory) {
            $postCodeLocations->join('category', 'location.category_id', '=', 'category.id');
        }

        $data = $postCodeLocations->orderBy('location.category_id', 'asc')
            ->orderBy('location.ordering', 'asc')
            ->orderBy('location.name', 'asc')
            ->get();

        if ( !empty($data) ) {
            foreach($data as $k => $v) {
                if ( empty($v->name) && empty($v->address) ) {
                    $text = 'Unknown';
                }
                else if ( $v->name == $v->address ) {
                    $text = $v->name;
                }
                else {
                    $text = empty($v->name) ? $v->address : $v->name .' ('. $v->address .')';
                }

                $postcodeList[] = [
                    'value' => $v->address,
                    'text' =>  $text
                ];
            }
        }

        return $postcodeList;
    }

    public function getCustomLocationList($postcodeList = []) {
        $fixedPrice = \App\Models\FixedPrice::select('start_postcode', 'end_postcode');

        if ($this->siteId) {
            $fixedPrice->whereIn('site_id', $this->siteId);
        }

        $fixedPrice = $fixedPrice->get();

        $tempList = [];
        foreach($fixedPrice as $k => $v) {
            $tempList = array_merge($tempList, explode(',', $v->start_postcode));
            $tempList = array_merge($tempList, explode(',', $v->end_postcode));
        }

        foreach($tempList as $k => $v) {
            $postcode = $v;
            foreach($postcodeList as $k2 => $v2) {
                if ( $v == $v2['value'] ) {
                    $postcode = '';
                }
            }
            if ( $postcode && $postcode != 'ALL' ) {
                $postcodeList[] = [
                    'value' => $postcode,
                    'text' =>  $postcode
                ];
            }
        }
        return $postcodeList;
    }

    public function getServices() {
        $services = \App\Models\Service::where('relation_type', 'site');

        if ($this->siteId) {
            $services->whereIn('relation_id', $this->siteId);
        }

        $services->where('status', 'active')
            ->orderBy('order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();

        return $services;
    }

    public function getServiceNames($services) {
        $servicesList = [];
        foreach($services as $k => $v) {
            $servicesList[] = [
                'id' => (int)$v->id,
                'name' => (string)$v->name
            ];
        }
        return $servicesList;
    }

    public function getVehicleList() {
        $vehicleList = \App\Models\VehicleType::select('id', 'name');

        if ($this->siteId) {
            $vehicleList->whereIn('site_id', $this->siteId);
            $vehicleList->orderBy('ordering', 'asc');
        }

        $vehicleList = $vehicleList->get();

        if (!empty($vehicleList) && count($vehicleList) > 0) {
            return \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($vehicleList), true);
        }
        else {
            return [];
        }
    }

    public function getSortQuery($post, $query) {
        $sort = dt_parse_sort($post);
        if ( !empty($sort) ) {

            foreach($sort as $k => $v) {
                if ( !empty($this->columns[$v->property]) ) {
                    $query->orderBy($this->columns[$v->property], $v->direction);
                }
            }
        }

        return $query;
    }

    public function getKeywordsQuery($post, $query) {
         // SELECT * FROM `eto_fixed_prices` WHERE `site_id`='1' AND (`start_postcode` LIKE '%test%' OR `start_date` LIKE '%test%' OR `end_postcode` LIKE '%test%' OR `end_date` LIKE '%test%') AND (`service_ids` REGEXP '"1"') AND (`type`='1') ORDER BY `start_postcode` ASC, `end_postcode` ASC, `direction` ASC LIMIT 0,10

        $searchData = $post['search']['value'];
        parse_str($searchData, $output);
        $serviceIds = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode(isset($output['filter-service_ids']) ? $output['filter-service_ids'] : []));
        $keywords = trim(SiteHelper::makeStrSafe(isset($output['filter-keywords']) ? (string)$output['filter-keywords'] : ''));
        $types = \GuzzleHttp\json_decode(\GuzzleHttp\json_encode(isset($output['filter-type']) ? $output['filter-type'] : []));

        // Keywords
        if ( !empty($keywords) ) {
            $query->where(function ($query) use ($keywords) {
                $query->where('start_postcode', 'LIKE', '%'.$keywords.'%');
                $query->orWhere('start_date', 'LIKE', '%'.$keywords.'%');
                $query->orWhere('end_postcode', 'LIKE', '%'.$keywords.'%');
                $query->orWhere('end_date', 'LIKE', '%'.$keywords.'%');
                $query->orWhereHas('zones', function ($query) use ($keywords) {
                    $query->where('name', 'LIKE', '%'.$keywords.'%');
                    $query->orWhere('address', 'LIKE', '%'.$keywords.'%');
                });
            });
        }

        // Services
        if ( !empty($serviceIds) ) {
            $query->where(function ($query) use ($serviceIds) {
                $method = 'where';
                foreach($serviceIds as $k => $v) {
                    if ( !empty($v) ) {
                        $query->$method('service_ids', 'REGEXP', (int)$v);
                        $method = 'orWhere';
                    }
                }
            });
        }

        // Types
        if ( !empty($types) ) {
            $query->where(function ($query) use ($types) {
                $method = 'where';
                foreach($types as $k => $v) {
                    if ( !empty($v) ) {
                        $query->$method('type', (int)$v);
                        $method = 'orWhere';
                    }
                }
            });
        }

        return $query;
    }

    public function getFixedPrices($post, $start, $limit, $type = 'totalCount', $getAllData = false) {
        if (!$getAllData) {
            $fixedPrice = \App\Models\FixedPrice::select('start_postcode', 'end_postcode');
        }
        else {
            $fixedPrice = \App\Models\FixedPrice::query();
        }

        if ($this->siteId) {
            $fixedPrice->whereIn('site_id', $this->siteId);
        }

        if ($type == 'list' || $type == 'filteredCount') {
            $fixedPrice =  $this->getKeywordsQuery($post, $fixedPrice);
        }
        if ($type == 'list') {
            $fixedPrice = $this->getSortQuery($post, $fixedPrice);
            $fixedPrice->offset($start)->limit($limit);
        }

        $fixedPriceList = $fixedPrice->get();

        if ($type == 'totalCount' || $type == 'filteredCount') {
            return count($fixedPriceList);
        }

        return $fixedPriceList;
    }

    public function getLocationNameByAddress($locations, $address, $val = 'name', $key = 'address') {
        if ( !empty($locations) ) {
            foreach($locations as $k => $value) {
                $value = (object)$value;
                if ( $address == $value->$key && !empty($value->$val) ) {
                    if ($value->$val == $value->$key) {
                        // $address = $value->$key;
                    }
                    else {
                        $address = $value->$val ; //. ' ('. $value->$key .')';
                    }
                    break;
                }
            }
        }
        return $address;
    }
}
