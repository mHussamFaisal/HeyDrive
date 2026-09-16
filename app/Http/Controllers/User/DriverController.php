<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\User\UserController;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DriverController extends UserController
{
    public function search($status = false) {
        $users = parent::__search('driver.*', $status);
        return $users;
    }

    public function vehicleList() {
        $driver_id = request('driver_id');
        $vehicles = [];
        $data = Vehicle::where('user_id', $driver_id)->where('status', 'activated')->get();
        $vehicles[0]['id'] = 0;
        $vehicles[0]['name'] = trans('booking.booking_unassigned');
        $vehicles[0]['selected'] = 1;
        foreach ($data as $vehicle) {
            $vehicles[$vehicle->id] = $vehicle;

            if ($vehicle->selected == '1') {
                unset($vehicles[0]['selected']);
            }
        }
        return $vehicles;
    }

    public function getVehicle($vehicle_id) {
        $vehicle = Vehicle::find($vehicle_id);
        if (empty($vehicle)) {
            $vehicle = new Vehicle;
        }
        return $vehicle;
    }

    public function get($driver_id) {
        $user = User::with('profile')->find($driver_id);

        if (empty($user)) {
            $user = new User;
            $user->profile = new UserProfile;
        }

        $user->avatar_path = $user->getAvatarPath();
        $user->displayName = $user->getName(true);

        return $user;
    }
}
