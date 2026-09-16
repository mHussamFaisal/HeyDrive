<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRoute;
use App\Models\User;
use DB;

class CallerIDController extends Controller
{
    public function index()
    {
        $phoneNumber = trim(request('phoneNumber', ''));
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        $bookings = [];

        if ($phoneNumber) {
            $customers = DB::table('user')
            ->join('user_customer', 'user.id', '=', 'user_customer.user_id')
            ->select('user.id')
            ->whereRaw("REPLACE(`mobile_number`, ' ', '') LIKE '{$phoneNumber}%'")
            ->orWhereRaw("REPLACE(`telephone_number`, ' ', '') LIKE '{$phoneNumber}%'")
            ->orWhereRaw("REPLACE(`emergency_number`, ' ', '') LIKE '{$phoneNumber}%'")
            ->pluck('id')
            ->toArray();

            $drivers = User::whereHas('profile', function($query) use ($phoneNumber) {
                $query->whereRaw("REPLACE(`mobile_no`, ' ', '') LIKE '{$phoneNumber}%'");
                $query->orWhereRaw("REPLACE(`telephone_no`, ' ', '') LIKE '{$phoneNumber}%'");
                $query->orWhereRaw("REPLACE(`emergency_no`, ' ', '') LIKE '{$phoneNumber}%'");
            })
            ->pluck('id')
            ->toArray();

            $bookings = BookingRoute::whereRaw("REPLACE(`contact_mobile`, ' ', '') LIKE '{$phoneNumber}%'")
            ->orWhereRaw("REPLACE(`lead_passenger_mobile`, ' ', '') LIKE '{$phoneNumber}%'")
            ->orWhereHas('booking', function($query) use ($customers) {
                $query->whereIn('user_id', $customers);
            })
            ->orWhereIn('driver_id', $drivers)
            ->orderBy('created_date', 'desc')
            ->take(5)
            ->get();
        }

        return view('admin.callerid.index', compact('phoneNumber', 'bookings'));
    }
}
