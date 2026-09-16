<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $drivers = [
            '-- Select --'
        ];

        $customers = [
            '-- Select --'
        ];

        $query = \App\Models\User::role('driver.*')->where('status', 'approved')->get();

        foreach($query as $key => $value) {
            $drivers[$value->id] = $value->name;
        }

        $bookings = \App\Models\BookingRoute::where('id', '!=', 0);

        $driver_id = $request->get('drivers', 0);

        if ( $driver_id ) {
            $bookings->where('driver_id', $driver_id);
        }

        $bookings->orderBy('created_date', 'desc');

        foreach($bookings->get() as $key => $value) {
            echo $value->ref_number .' | '. $value->driver_id .'<br />';
        }

        return view('admin.reports.index', compact('drivers', 'customers'));
    }
}
