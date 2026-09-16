<?php

namespace App\Traits\Filters;

use App\Helpers\SiteHelper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;

trait FixedPrices
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getFixedPricesFilters($config)
    {
        return $config;
    }

    public function parseFixedPricesFilters($filters, $name) {
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

        if (!empty($filters['profile_type'])) {
            $data[$key]['data']['profile_type'] = $filters['profile_type'];
        }

        if (!empty($filters['profile_created_from'])) {
            $data[$key]['data']['profile_created_from'] = $filters['profile_created_from'];
        }

        if (!empty($filters['profile_created_to'])) {
            $data[$key]['data']['profile_created_to'] = $filters['profile_created_to'];
        }

        if (!empty($filters['role'])) {
            $data[$key]['data']['role'] = $filters['role'];
        }

        return $data;
    }

    public function getFixedPricesData($config)
    {
        $fixedPrice = \App\Models\FixedPrice::whereIn('site_id', $this->sites);

        if (!empty($config['status'])) {
            $fixedPrice->whereIn('published', $config['status']);
        }

        if (!empty($config['direction'])) {
            $fixedPrice->whereIn('direction', $config['direction']);
        }

        if (!empty($config['is_zone'])) {
            $fixedPrice->whereIn('is_zone', $config['is_zone']);
        }

        $data = $fixedPrice->get();

        return $data;
    }

    public function getFixedPricesFile($data, $columns, $format, $folder)
    {
        $headers = [];

        $fixedPricesHelper = new \App\Helpers\FixedPricesHelper($this->sites);

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                $headers[$column] = trans('export.column.fixedprices.' . $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $fixedPrice) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        if ($hK == 'service_ids') {
                            if ($service_ids = json_decode($fixedPrice->$hK)) {
                                $services = $fixedPricesHelper->getServices();
                                $serviceNames = [];

                                foreach ($services as $k2 => $v2) {
                                    if (in_array($v2->id, $service_ids)) {
                                        $serviceNames[] = $v2->name;
                                    }
                                }
                                $val = implode(' | ', $serviceNames);
                            } else {
                                $val = 'All';
                            }
//                    } elseif ($hK == 'type') {
//                        if (!empty($fixedPricesHelper->typeList)) {
//                            foreach($fixedPricesHelper->typeList as $k2 => $v2) {
//                                if ($fixedPrice->$hK == $v2['value']) {
//                                    $val = $v2['text'];
//                                    break;
//                                }
//                            }
//                        } else {
//                            $val = 'Unknown';
//                        }
                        } elseif ($hK == 'start_postcode' || $hK == 'end_postcode') {
                            $postcodeList = $fixedPricesHelper->getLocationPostcodeList(
                                [],
                                false,
                                "location.address",
                                "location.name");

                            if ($hK == 'start_postcode') {
                                if ($fixedPrice->is_zone == 0) {
                                    $fromTemp = explode(',', $fixedPrice->start_postcode);

                                    foreach ($fromTemp as $k1 => $v1) {
                                        $fromTemp[$k1] = $fixedPricesHelper->getLocationNameByAddress($postcodeList, $v1, 'text', 'value');
                                    }

                                    $val = implode(' | ', $fromTemp);
                                } else {
                                    $from = [];
                                    $zones = $fixedPrice->zones('from')->get();

                                    foreach ($zones as $zone) {
                                        $from[] = $zone->name;
                                    }

                                    $val = !empty($from) ? implode('<br>', $from) : 'All';
                                }
                            } elseif ($hK == 'end_postcode') {
                                if ($fixedPrice->is_zone == 0) {
                                    $toTemp = explode(',', $fixedPrice->end_postcode);

                                    foreach ($toTemp as $k1 => $v1) {
                                        $toTemp[$k1] = $fixedPricesHelper->getLocationNameByAddress($postcodeList, $v1, 'text', 'value');
                                    }

                                    $val = implode(' | ', $toTemp);
                                } else {
                                    $to = [];
                                    $zones = $fixedPrice->zones('to')->get();
                                    foreach ($zones as $zone) {
                                        $to[] = $zone->name;
                                    }
                                    $val = !empty($to) ? implode('<br>', $to) : 'All';
                                }
                            }
                        } elseif ($hK == 'direction') {
                            $val = $fixedPrice->$hK == 1
                                ? trans('export.fields.direction.from_to')
                                : trans('export.fields.direction.both_ways');
                        } elseif ($hK == 'is_zone') {
                            $val = $fixedPrice->$hK == 1
                                ? trans('export.fields.is_zone.zones')
                                : trans('export.fields.is_zone.postcodes');
                        } else {
                            $val = Arr::get($fixedPrice, $hK);

                            if ($val instanceof \Carbon\Carbon) {
                                $val = format_date_time($val);
                            }
                        }

                        $val = trim(strip_tags($val));
                        $val = str_replace(array("\r\n", "\r", "\n"), ' ', $val);
                        $cell[$hK] = $val ? $val : '';
                    }

                    if ($params = json_decode($fixedPrice->params)) {
                        $display_type = $fixedPrice->type == 1 ? 0 : 1;
                        $price = (float)$fixedPrice->value;
                        $price_type = 0;
                        $price_type_name = 'Multiply';
                        $price_type_prefix = '*';

                        if (isset($params->factor_type)) {
                            if ($params->factor_type == 2) {
                                $price_type = 2;
                                $price_type_name = 'Override';
                                $price_type_prefix = '=';
                            } elseif ($params->factor_type == 1) {
                                $price_type = 1;
                                $price_type_name = 'Add';
                                $price_type_prefix = '+';
                            }
                        }

                        $deposit = !empty($params->deposit) ? (float)$params->deposit : 0;
                        $deposit_type = 0;
                        $deposit_type_name = 'Percent';
                        $deposit_type_prefix = '';
                        $deposit_type_suffix = '%';

                        if (config('site.fixed_prices_deposit_type') == 1) {
                            $deposit_type = 1;
                            $deposit_type_name = 'Flat';
                            $deposit_type_prefix = '=';
                            $deposit_type_suffix = '';
                        }

                        $priceList = [];
                        $depositList = [];

                        if ($display_type) {
                            $priceList[] = 'Base price: ' . $price . ', Action:' . $price_type_name;
                            $depositList[] = 'Base deposit: ' . $deposit . ', Action: ' . $deposit_type_name;
                        }

                        if (isset($params->vehicle)) {
                            $vehicleList = $fixedPricesHelper->getVehicleList();
                            foreach ($params->vehicle as $k1 => $v1) {
                                // Vehicle name
                                $name = 'Unknown';
                                foreach ($vehicleList as $kV => $vV) {
                                    if ($v1->id == $vV['id']) {
                                        $name = $vV['name'];
                                        break;
                                    }
                                }

                                if ($price_type == 2) {
                                    $vP = $v1->value;
                                } elseif ($price_type == 1) {
                                    $vP = $price + $v1->value;
                                } else {
                                    $vP = $price * $v1->value;
                                }

                                if ($display_type) {
                                    $text = $price_type_prefix . (!empty($v1->value) ? $v1->value : 0);
                                } else {
                                    $text = SiteHelper::formatPrice($vP, 2, 1);
                                }

                                $priceList[] = $name . ': ' . $text;

                                if (!empty($v1->deposit)) {
                                    $deposit = (float)$v1->deposit;
                                }

                                if ($deposit_type == 1) {
                                    $vD = $deposit;
                                } else {
                                    $vD = ($vP / 100) * $deposit;
                                }

                                if ($display_type) {
                                    $text = $deposit_type_prefix . (!empty($v1->value) ? $v1->deposit : 0) . $deposit_type_suffix;
                                } else {
                                    $text = SiteHelper::formatPrice($vD, 2, 1);
                                }

                                $depositList[] = $name . ': ' . $text;
                            }
                        }

                        $cell['price'] = implode(' | ', $priceList);
                        $cell['deposit'] = implode(' | ', $depositList);
                    }

                    $cell['status'] = $fixedPrice->published == 1
                        ? trans('export.fields.published.active')
                        : trans('export.fields.published.inactive');

                    if (!empty($cell)) {
                        $cells[] = $cell;
                    }
                }

                $this->setFilePerChunkSection('FixedPreces', $chunkId, $format, $cells);
            }
        }
    }
}
