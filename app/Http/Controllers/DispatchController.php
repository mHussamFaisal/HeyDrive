<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    // use \App\Traits\TestDispatch; // Test
    use \App\Traits\UserDriverListing;

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('admin.dispatch.index')) {
            return redirect_no_permission();
        }
        return view('dispatch.index');
    }

    public function mapDrivers()
    {
        // return $this->_mapDrivers(); // Test

        $users = [];
        $userIds = [];
        $userBookingCount = [];
        $tnUser = (new \App\Models\User)->getTable();
        $query = User::with('profile')->role('driver.*')->where($tnUser .'.status', 'approved');

        if (config('eto.allow_teams') && auth()->user()->hasRole('admin.*')) {
            $teamIds = [];
            $teamId = 0;
            $teamIds = !empty($teamId) ? [$teamId] : auth()->user()->teams->pluck('id')->toArray();

            if (!empty($teamIds)) {
                $query->whereHas('teams', function ($q) use ($teamIds) {
                    $q->whereIn('team_id', $teamIds);
                });
            }
        }

        if (config('eto.allow_fleet_operator') && auth()->user()->hasRole('admin.fleet_operator')) {
            $query->where($tnUser .'.fleet_id', auth()->user()->id);
        }

        $usersToPrepare = $query->orderBy($tnUser .'.name', 'asc')->get();

        foreach($usersToPrepare as $user) {
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

        foreach($usersToPrepare as $user) {
            $profile = $user->profile;

            if (config('eto_map.view.show_inactive_drivers') === false && $profile->availability_status == '0' ) {
                continue;
            }

            $count = !empty($userBookingCount[$user->id]) ? $userBookingCount[$user->id] : 0;
            $user->driver_status = $this->getDriverStatus($user->id, $profile->availability_status, $count);
            $isActiveOnRoute = check_expire(\Carbon\Carbon::parse($user->last_seen_at)->addHours(1));

            if (config('site.allow_driver_app') != 1 || ($profile->availability_status == 'unavailable' && $isActiveOnRoute->isExpire)) {
                $user->lat = null;
                $user->lng = null;
            }

            $users[] = (object)[
                'id' => $user->id,
                'name' => $user->getName(true),
                'displayName' => !empty($user->profile->unique_id) ? $user->profile->unique_id : $user->getName(true),
                'unique_id' => !empty($user->profile->unique_id) ? $user->profile->unique_id : '',
                'lat' => $user->lat,
                'lng' => $user->lng,
                'accuracy' => $user->accuracy,
                'heading' => $user->heading,
                'url' => route('admin.users.show', $user->id),
                'availability' => $user->driver_status
            ];
        }

        return $users;
    }
}
