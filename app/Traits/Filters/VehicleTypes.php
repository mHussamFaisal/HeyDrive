<?php

namespace App\Traits\Filters;

use Illuminate\Support\Arr;

trait VehicleTypes
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getVehicleTypesFilters($config)
    {
        return $config;
    }

    public function parseVehicleTypesFilters($filters, $name) {
        $key = $name;
        $data = [ $key => [
                'name' => $name,
                'type' => 'custom',
                'data' => []
            ]
        ];

        if (!empty($filters['status'])) {
            $data[$key]['data']['status'] = $filters['status'];
        }

        if (!empty($filters['is_backend'])) {
            $data[$key]['data']['is_backend'] = $filters['is_backend'];
        }

        if (!empty($filters['capacity'])) {
            $data[$key]['data']['capacity'] = $filters['capacity'];
        }

        if (!empty($filters['factor_type'])) {
            $data[$key]['data']['factor_type'] = $filters['factor_type'];
        }

        return $data;
    }

    public function getVehicleTypesData($config)
    {
        $dbPrefix = get_db_prefix();
        $siteTable = (new \App\Models\Site())->getTable();
        $userTable = (new \App\Models\User())->getTable();
        $vehicleTypeTable = (new \App\Models\VehicleType)->getTable();
        $vehicleTypes = \App\Models\VehicleType::select(
            '*',
            \DB::raw("
                (SELECT name
					FROM " . $dbPrefix . $siteTable . "
					WHERE id=" . $dbPrefix . $vehicleTypeTable . ".site_id
					LIMIT 1
				) AS site"),
            \DB::raw("
                (SELECT name
					FROM " . $dbPrefix . $userTable . "
					WHERE id=" . $dbPrefix . $vehicleTypeTable . ".user_id
					LIMIT 1
				) AS driver"),
            \DB::raw("
                CASE
                    WHEN `factor_type` = 0 THEN '".trans('export.fields.factor_type.flat')."'
                    WHEN `factor_type` = 1 THEN '".trans('export.fields.factor_type.multiply')."'
                    ELSE '".trans('export.fields.factor_type.flat')."'
                END AS factor_type"),
            \DB::raw("
                CASE
                    WHEN `published` = 0 THEN '".trans('export.fields.published.inactive')."'
                    WHEN `published` = 1 THEN '".trans('export.fields.published.active')."'
                    ELSE '".trans('export.fields.published.active')."'
                END AS status"),
            \DB::raw("
             CASE
                    WHEN `default` = 0 THEN '".trans('export.fields.default.no')."'
                    WHEN `default` = 1 THEN '".trans('export.fields.default.yes')."'
                    ELSE '".trans('export.fields.default.no')."'
                END AS `default`"),
            \DB::raw("
                CASE
                    WHEN `is_backend` = 0 THEN '".trans('export.fields.is_backend.backend_frontend')."'
                    WHEN `is_backend` = 1 THEN '".trans('export.fields.is_backend.backend')."'
                    ELSE '".trans('export.fields.is_backend.backend_frontend')."'
                END AS is_backend")
            );

        if (!empty($config['status'])) {
            $vehicleTypes->whereIn('published', $config['status']);
        }

        if (!empty($config['is_backend'])) {
            $vehicleTypes->whereIn('is_backend', $config['is_backend']);
        }

        if (!empty($config['capacity'])) {
            $vehicleTypes->whereIn('capacity', $config['capacity']);
        }

        if (!empty($config['factor_type'])) {
            $vehicleTypes->whereIn('factor_type', $config['factor_type']);
        }

        $data = $vehicleTypes->get();

        return $data;
    }

    public function getVehicleTypesFile($data, $columns, $format, $folder)
    {
        $headers = [];

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                $headers[$column] = trans('export.column.vehicletypes.' . $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $vehicleType) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        if ($hK == 'service_ids') {
                            if ($service_ids = json_decode($vehicleType->$hK)) {
                                $fixedPricesHelper = new \App\Helpers\FixedPricesHelper($this->sites);
                                $services = $fixedPricesHelper->getServices();
                                $serviceNames = [];

                                foreach ($services as $k2 => $v2) {
                                    if (in_array($v2->id, $service_ids)) {
                                        $serviceNames[] = $v2->name;
                                    }
                                }
                                $val = implode(' | ', $serviceNames);
                            }
                            else {
                                $val = 'All';
                            }
                        }
                        else {
                            $val = Arr::get($vehicleType, $hK);

                            if ($val instanceof \Carbon\Carbon) {
                                $val = format_date_time($val);
                            }
                        }

                        if ($hK == 'image') {
                            if (\Storage::disk('uploads')->exists('vehicles-types' . DIRECTORY_SEPARATOR . $vehicleType->image)) {
                                if (!\File::exists($folder . DIRECTORY_SEPARATOR . 'vehicle_type')) {
                                    \File::makeDirectory($folder . DIRECTORY_SEPARATOR . 'vehicle_type', 0755, true);
                                }
                                @copy(disk_path('uploads', 'vehicles-types' . DIRECTORY_SEPARATOR . $vehicleType->image),
                                    $folder . DIRECTORY_SEPARATOR . 'vehicle_type' . DIRECTORY_SEPARATOR . $vehicleType->image);
                            }
                        }

                        $val = trim(strip_tags($val));
                        $val = str_replace(array("\r\n", "\r", "\n"), ' ', $val);
                        $cell[$hK] = $val ? $val : '';
                    }

                    if (!empty($cell)) {
                        $cells[] = $cell;
                    }
                }

                $this->setFilePerChunkSection('VehicleTypes', $chunkId, $format, $cells);
            }
        }
    }
}
