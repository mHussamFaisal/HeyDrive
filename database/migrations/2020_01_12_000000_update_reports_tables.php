<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReportsTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('report_payments')) {
            $payments = \DB::table('report_payments')->get();

            foreach ($payments as $payment) {
                $bookingReport = \DB::table('report_payment_bookings')
                    ->select('payment.id as payment_id')
                    ->join('booking_route', 'booking_route.id', 'report_payment_bookings.booking_id')
                    ->join('booking', 'booking.id', 'booking_route.booking_id')
                    ->join('payment', 'payment.site_id', 'booking.site_id')
                    ->where('report_payment_id', $payment->id)
                    ->where('payment.name', $payment->payment_name)
                    ->limit(1)->first();

                if (!empty($bookingReport->payment_id)) {
                    \DB::table('report_payments')->where('id', $payment->id)->update(['payment_id' => $bookingReport->payment_id]);
                }
            }
        }

        if (Schema::hasTable('report_drivers')) {
            $drivers = \DB::table('report_drivers')->get();
            $emails = [];

            foreach ($drivers as $driver) {
                if (!empty($driver->email) && !in_array($driver->email, $emails)) {
                    $user = \DB::table('users')->select('id')
                        ->where('email', $driver->email)
                        ->limit(1)->first();

                    if (!empty($user->id)) {
                        $emails[] = $driver->email;
                        \DB::table('report_drivers')->where('email', $driver->email)->update(['user_id' => $user->id]);
                    }
                }
            }

            \DB::table('report_drivers')->where('email', null)->orWhere('email', '')->update(['user_id' => null]);
        }
    }

    public function down()
    {
        //
    }
}
