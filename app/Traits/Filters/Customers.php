<?php

namespace App\Traits\Filters;

use App\Models\Customer;
use Illuminate\Support\Arr;

trait Customers
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getCustomersFilters($config)
    {
        return $config;
    }

    public function parseCustomersFilters($filters, $name) {
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

        if (!empty($filters['profile_created_from'])) {
            $data[$key]['data']['profile_created_from'] = $filters['profile_created_from'];
        }

        if (!empty($filters['profile_created_to'])) {
            $data[$key]['data']['profile_created_to'] = $filters['profile_created_to'];
        }
        return $data;
    }

    public function getCustomersData($config)
    {
        $dbPrefix = get_db_prefix();
        $siteTable = (new \App\Models\Site())->getTable();
        $customerTable = (new \App\Models\Customer)->getTable();
        $users = Customer::select(
                '*',
            \DB::raw("(SELECT name
        					FROM " . $dbPrefix . $siteTable . "
        					WHERE id=" . $dbPrefix . $customerTable . ".site_id
        					LIMIT 1
        				) AS site"),
                \DB::raw("CASE
                    WHEN type = 1 THEN 'customer.root'
                    WHEN type = 2 THEN 'driver.root'
                    ELSE 'customer.root'
                END AS roles"),
                \DB::raw("CASE
                    WHEN published = 0 THEN 'inactive'
                    WHEN published = 1 THEN 'approved'
                    ELSE 'approved'
                END AS status"),
                'last_visit_date as last_seen_at'
        )->with('profile');

        if (!empty($config['status'])) {
            $users->whereIn('status', $config['status']);
        }

        if (!empty($config['profile_created_from']) && !empty($config['profile_created_to'])) {
            $users->whereBetween($customerTable.'.created_date', [$config['profile_created_from'], $config['profile_created_to']]);
        }
        elseif (!empty($config['profile_created_from'])) {
            $users->where($customerTable.'.created_date', '>=', $config['profile_created_from']);
        }
        elseif (!empty($config['profile_created_to'])) {
            $users->where($customerTable.'.created_date', '<=', $config['profile_created_to']);
        }
        $data = $users->get();

        return $data;
    }

    public function getCustomersFile($data, $columns, $format, $folder)
    {
        $headers = [];

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                $headers[$column] = trans('export.column.customers.'. $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $user) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        $val = Arr::get($user, $hK);

                        if ($val instanceof \Carbon\Carbon) {
                            $val = format_date_time($val);
                        } elseif ($hK == 'activated') {
                            $val = (int)$user->activated === 1
                                ? trans('export.fields.default.yes')
                                : trans('export.fields.default.no');
                        } elseif ($hK == 'status') {
                            $val = trans('common.user_status_options.' . $val);
                        } elseif ($hK == 'roles') {
                            $val = trans('roles.names.' . $val);
                        }

                        $val = trim(strip_tags($val));
                        $val = str_replace(array("\r\n", "\r", "\n"), ' ', $val);
                        $cell[$hK] = $val ? $val : '';
                    }

                    if (!empty($cell)) {
                        $cells[] = $cell;
                    }
                }

                $this->setFilePerChunkSection('Customers', $chunkId, $format, $cells);
            }
        }
    }
}
