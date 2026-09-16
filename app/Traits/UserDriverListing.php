<?php

namespace App\Traits;

trait UserDriverListing
{
    public function getDriverStatus($id, $availabilityStatus = 0, $driverBookings = false) {
        if ($availabilityStatus == 1) {
            if ((!$driverBookings || !is_numeric($driverBookings)) && (int)$driverBookings !== 0) {
                $driverBookings = \App\Models\BookingRoute::whereDriver($id)
                    ->whereIn('status', ['onroute', 'arrived', 'onboard'])
                    ->count();
            }

            if ($driverBookings > 0) {
                $status = 'busy';
            }
            else {
                $status = 'available';
            }
        }
        elseif ($availabilityStatus == 2) {
            $status = 'away';
        }
        else {
            $status = 'unavailable';
        }

        return $status;
    }
}
