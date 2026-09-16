<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Helpers\DriverHelper;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('driver.jobs.index')) {
            return redirect_no_permission();
        }
        
        $userId = auth()->user()->id;
        $counts = DriverHelper::getBookingCounts($userId);

        $data = [
            'assigned' => $counts->assigned,
            'accepted' => $counts->accepted,
            'completed' => $counts->completed,
            'canceled' => $counts->canceled,
            'current' => $counts->current,
            'currentJobUrl' => DriverHelper::getCurrentJobUrl($userId, $counts->current),
        ];

        return request('action') == 'counts' ? $data : view('driver.dashboard.index', $data);
    }
}
