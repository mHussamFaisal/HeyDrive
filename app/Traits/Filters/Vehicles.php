<?php

namespace App\Traits\Filters;

use Illuminate\Support\Arr;

trait Vehicles
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getVehiclesFilters($config)
    {
        return $config;
    }

    public function parseVehiclesFilters($filters, $name) {
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

        return $data;
    }

    public function getVehiclesData($config)
    {
        $dbPrefix = get_db_prefix();
        $userTable = (new \App\Models\User())->getTable();
        $vehicleTable = (new \App\Models\Vehicle)->getTable();
        $vehicles = \App\Models\Vehicle::select(
            '*',
            \DB::raw("
                (SELECT name
					FROM " . $dbPrefix . $userTable . "
					WHERE id=" . $dbPrefix . $vehicleTable . ".user_id
					LIMIT 1
				) AS driver")
        );

        if (!empty($config['status'])) {
            $vehicles->whereIn('status', $config['status']);
        }

        $data = $vehicles->get();

        return $data;
    }

    public function getVehiclesFile($data, $columns, $format, $folder)
    {
        $headers = [];

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                $headers[$column] = trans('export.column.vehicles.' . $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $vehicleType) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        $val = Arr::get($vehicleType, $hK);

                        if ($val instanceof \Carbon\Carbon) {
                            $val = format_date_time($val);
                        }
                        elseif ($hK == 'status') {
                            $val = trans('common.vehicle_status_options.' . $val);
                        }
                        elseif ($hK == 'selected') {
                            $val = (int)$vehicleType->selected === 1
                                ? trans('export.fields.default.yes')
                                : trans('export.fields.default.no');
                        }

                        if ($hK == 'image') {
                            if (\Storage::disk('vehicles')->exists($vehicleType->image)) {
                                if (!\File::exists($folder . DIRECTORY_SEPARATOR . 'vehicles')) {
                                    \File::makeDirectory($folder . DIRECTORY_SEPARATOR . 'vehicles', 0755, true);
                                }
                                @copy(disk_path('vehicles', $vehicleType->image),
                                    $folder . DIRECTORY_SEPARATOR . 'vehicles' . DIRECTORY_SEPARATOR . $vehicleType->image);
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

                $this->setFilePerChunkSection('Vehicles', $chunkId, $format, $cells);
            }
        }
    }
}
