<?php

namespace App\Traits\Filters;

use App\Helpers\SiteHelper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;

trait Users
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getUsersFilters($config)
    {
        $roles = Role::getListing()->where(function ($q) {
            $q->where('slug', 'like', 'admin.%')->orWhere('slug', 'like', 'driver.%');
        });

        if (!config('eto.allow_fleet_operator')) {
            $roles->where(function ($q) {
                $q->where('slug', '!=', 'admin.fleet_operator')->orWhere('slug', '!=', 'admin.fleet_operator.%');
            });
        }

        $roles = $roles->get();
        $config->params->role = clone $this->select;
        $config->params->role->items = new \stdClass();
        $config->params->role->type = 'select';
        $config->params->role->multiple = true;
        $config->params->role->translations = 'roles.names.';

        foreach ($roles as $role) {
            $slug = $role->slug;
            $config->params->role->items->$slug = [
                'name' => $role->getName()
            ];
        }
        return $config;
    }

    public function parseUsersFilters($filters, $name) {
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

    public function getUsersData($config)
    {
        $tnUser = (new \App\Models\User)->getTable();
        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        $users = User::select($tnUser .'.*')->with([
            'roles' => function($query) use ($config) {
                $query->select('name');
            },
            'profile' => function($query) use ($config) {
                //
            }
        ]);
        // ->leftJoin($tnUserProfile, $tnUserProfile .'.user_id', $tnUser .'.id');

        if (!empty($config['role'])) {
            $users->role($config['role']);
        }

        if (!empty($config['status'])) {
            $users->whereIn('status', $config['status']);
        }

        // if (!empty($config['profile_type'])) {
        //    $users->whereIn($tnUserProfile .'.profile_type', $config['profile_type']);
        // }

        if (!empty($config['profile_created_from']) && !empty($config['profile_created_to'])) {
            $users->whereBetween($tnUser .'.created_at', [$config['profile_created_from'], $config['profile_created_to']]);
        }
        elseif (!empty($config['profile_created_from'])) {
            $users->where($tnUser .'.created_at', '>=', $config['profile_created_from']);
        }
        elseif (!empty($config['profile_created_to'])) {
            $users->where($tnUser .'.created_at', '<=', $config['profile_created_to']);
        }

        $data = $users->get();

        return $data;
    }

    public function getUsersFile($data, $columns, $format, $folder)
    {
        $headers = [];

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                $headers[$column] = trans('export.column.users.' . $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $user) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        if ($hK == 'roles') {
                            $val = $user->$hK->pluck('name')->toArray();
                            $val = implode(', ', $val);
                        } elseif ($hK == 'profile.availability') {
                            $dates = [];

                            foreach (json_decode($user->profile->availability) as $availability) {
                                $dates[] = trans('export.fields.availability.start_date') . ': '
                                    . SiteHelper::formatDateTime($availability->start_date, 'date') . ', '
                                    . trans('export.fields.availability.end_date') . ': '
                                    . SiteHelper::formatDateTime($availability->end_date, 'date') . ', '
                                    . trans('export.fields.availability.available_date') . ': '
                                    . SiteHelper::formatDateTime($availability->available_date, 'date');
                            }

                            $val = implode(' | ', $dates);
                        } else {
                            $val = Arr::get($user, $hK);

                            if ($val instanceof \Carbon\Carbon) {
                                $val = format_date_time($val);
                            } elseif ($hK == 'status') {
                                $val = trans('common.user_status_options.' . $val);
                            } elseif ($hK == 'profile.profile_type') {
                                $val = trans('common.user_profile_type_options.' . $val);
                            } elseif ($hK == 'profile.availability_status') {
                                $val = (int)$user->profile->availability_status === 1
                                    ? trans('export.fields.availability_status.available')
                                    : trans('export.fields.availability_status.inaccessible');
                            }
                        }

                        if ($hK == 'avatar') {
                            if (\Storage::disk('avatars')->exists($user->avatar)) {
                                if (!\File::exists($folder . DIRECTORY_SEPARATOR . 'avatars')) {
                                    \File::makeDirectory($folder . DIRECTORY_SEPARATOR . 'avatars', 0755, true);
                                }
                                @copy(disk_path('avatars', $user->avatar),
                                    $folder . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $user->avatar);
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

                $this->setFilePerChunkSection('Users', $chunkId, $format, $cells);
            }
        }
    }
}
