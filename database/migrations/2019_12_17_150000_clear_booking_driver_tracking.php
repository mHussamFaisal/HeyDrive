<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\DatabaseHelper;

class ClearBookingDriverTracking extends Migration
{
    public function up()
    {
        Schema::dropIfExists('booking_tracking');

        $ids = [];
        $untracking_statuses = ['completed', 'canceled', 'unfinished', 'incomplete'];
        $trackingActive = \DB::table('booking_driver_tracking')->distinct()->get(['booking_id'])->pluck('booking_id');
        $bookings = \DB::table('booking_route')
          ->whereIn('id', $trackingActive)
          ->where(function($query) use($untracking_statuses) {
              $query->whereIn('status', $untracking_statuses);
              $query->orWhereRaw('DATE_ADD(`date`, INTERVAL 1 WEEK) <= NOW()');
          })
          ->get(['id', 'status']);

        foreach ($bookings as $booking) {
            // if (in_array($booking->status, $untracking_statuses)) {
                $dir = 'bookings'  .DIRECTORY_SEPARATOR . $booking->id . DIRECTORY_SEPARATOR . 'drivers';

                if ($tracking = \DB::table('booking_driver_tracking')->where('booking_id', $booking->id)->orderBy('timestamp', 'asc')->get()) {
                    $coordinates = [];

                    foreach ($tracking as $item) {
                        if (!empty($item->driver_id)) {
                            $coordinates[$item->driver_id][] = [
                                $item->lat,
                                $item->lng,
                                $item->timestamp,
                            ];
                        }
                    }

                    if (!empty($coordinates)) {
                        foreach ($coordinates as $driver => $item) {
                            $path = $dir . DIRECTORY_SEPARATOR . $driver;
                            if (!is_dir(asset_path('archive', $path))) {
                                \Storage::disk('archive')->makeDirectory($path, 0755, true, true);
                            }
                            \Storage::disk('archive')->put($path . DIRECTORY_SEPARATOR . 'tracking.json', json_encode($coordinates[$driver]));
                        }
                    }
                }

                $ids[] = $booking->id;
            // }
        }

        DB::table('booking_driver_tracking')->whereIn('booking_id', $ids)->delete();
    }

    public function down()
    {
        //
    }
}
