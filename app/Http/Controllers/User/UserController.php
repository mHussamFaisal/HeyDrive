<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    use \App\Traits\UserDriverListing;

    public function __search($role = false, $status = false)
    {
        $search = request('search');
        $convertedUsers = [];
        $sortedUsers = [];
        $tnUser = (new \App\Models\User)->getTable();
        $tnUserProfile = (new \App\Models\UserProfile)->getTable();

        $query = User::with('profile')
            ->select($tnUser .'.*', $tnUserProfile .'.unique_id', $tnUserProfile .'.availability_status')
            ->leftJoin($tnUserProfile, $tnUserProfile .'.user_id', '=', $tnUser .'.id');

        if ($role) {
            $query->role($role);
        }

        $query->where($tnUser .'.status', 'approved');

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $query->where($tnUser .'.fleet_id', auth()->user()->id);
        }

        if (!empty($search)) {
            $query->where(function ($query) use ($search, $tnUser) {
                $query->where($tnUser .'.name', 'like', '%' . $search . '%')
                    ->orWhere($tnUser .'.email', 'like', '%' . $search . '%')
                    ->orWhereHas('profile', function ($query) use ($search) {
                        $query->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('mobile_no', 'like', '%' . $search . '%');
                    });
            });
        }

        if (config('eto_booking.form.view.show_inactive_drivers_form') === false) {
            $query->where('availability_status', '!=', 0);
        }

        if (config('eto_booking.form.view.instant_dispatch_color_system') === true) {
            $query->addSelect(\DB::raw("(CASE
                WHEN `availability_status` = '1' THEN '0'
                WHEN `availability_status` = '2' THEN '1'
                WHEN `availability_status` = '0' THEN '2'
                ELSE '3' END) AS `availability_status_order`"));

            $query->orderBy('availability_status_order', 'asc');
        }

        $query->orderBy($tnUserProfile .'.unique_id', 'asc');
        $query->orderBy($tnUser .'.name', 'asc');
        $usersCount = $query->get()->count();

        $showInactiveDriversForm = config('eto_booking.form.view.show_inactive_drivers_form');

        if (request('perPage') && !$showInactiveDriversForm) {
            $query->skip((request('page', 1) - 1) * request('perPage'))->take(request('perPage', 10));
        }

        $users = $query->get();
        $userIds = [];
        $userBookingCount = [];

        foreach($users as $user) {
            $userIds[] = $user->id;
        }

        $driverBookings = \App\Models\BookingRoute::select('driver_id', 'id')
            ->whereIn('driver_id', $userIds)
            ->whereIn('status', ['onroute', 'arrived', 'onboard'])
            ->get();

        foreach ($driverBookings as $driverBooking) {
            if (empty($userBookingCount[$driverBooking->driver_id])) {
                $userBookingCount[$driverBooking->driver_id] = 0;
            }

            $userBookingCount[$driverBooking->driver_id]++;
        }

        foreach ($users as $userData) {
            $userData->avatar_path = $userData->getAvatarPath();
            $userData->displayName = $userData->getName(true);

            if (preg_match('/^driver\..++/m', $role)) {
                $count = !empty($userBookingCount[$userData->id]) ? $userBookingCount[$userData->id] : 0;
                $userData->driver_status = $this->getDriverStatus($userData->id, $userData->profile->availability_status, $count);
            }
            $convertedUsers[] = $userData;
        }

        if (request('perPage') && $showInactiveDriversForm) {
            $convertedUsers = collect($convertedUsers)->slice((request('page', 1) - 1) * request('perPage'), request('perPage', 10));
        }

        if (preg_match('/^driver\..++/m', $role) && config('eto_booking.form.view.instant_dispatch_color_system') === true) {
            $sort = [];
            foreach ($convertedUsers as $user) {
                $sort[$user->driver_status][] = $user;
            }
            if (!empty($sort['available'])) {
                $sortedUsers = array_merge($sortedUsers, $sort['available']);
            }
            if (!empty($sort['busy'])) {
                $sortedUsers = array_merge($sortedUsers, $sort['busy']);
            }
            if (!empty($sort['away'])) {
                $sortedUsers = array_merge($sortedUsers, $sort['away']);
            }
            if (!empty($sort['unavailable'])) {
                $sortedUsers = array_merge($sortedUsers, $sort['unavailable']);
            }
        }
        else {
            $sortedUsers = $convertedUsers;
        }

        return ['items'=>$sortedUsers, 'count_items'=>$usersCount, 'count'=>$users->count()];
    }

    public function getFleetList($fleet_id = false) {
        $items = Collection::class;
        $usersCount = 0;

        if (config('eto.allow_fleet_operator')) {
            $tnUser = (new \App\Models\User)->getTable();

            $query = \App\Models\User::with('profile')
                ->role('admin.fleet_operator')
                ->orderBy($tnUser .'.name', 'asc');

            $usersCount = $query->get()->count();

            if ($fleet_id) {
                $query->where($tnUser .'.id', $fleet_id);
            }

            if (request('perPage')) {
                $query->skip((request('page', 1) - 1) * request('perPage'))->take(request('perPage', 10));
            }

            if ($fleet_id) {
                $items = $query->first();
            }
            else {
                $items = $query->get();
            }

            if ($fleet_id) {
                return $items;
            }
            elseif (!empty($items)) {
                foreach ($items as $id => $user) {
                    $items[$id]->avatar_path = $user->getAvatarPath();
                    $items[$id]->displayName = $user->getName(false);
                }
            }
        }

        return ['items'=>$items, 'count_items'=>$usersCount, 'count'=>$items->count()];
    }

    public function getFleet($fleet_id) {
        $user =  $this->getFleetList($fleet_id);

        if (empty($user)) {
            $user = new User;
            $user->profile = new UserProfile;
        }

        $user->avatar_path = $user->getAvatarPath();
        $user->displayName = $user->getName(false);

        return $user;
    }
}
