<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepareReportsTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reports')) {
            Schema::table('reports', function (Blueprint $table) {
                if (!Schema::hasColumn('reports', 'name')) {
                    $table->string('name')->after('type')->nullable();
                }
            });
        }

        if (Schema::hasTable('report_drivers')) {
            if (!is_dir(asset_path('archive', 'reports'))) {
                \Storage::disk('archive')->makeDirectory('reports', 0755, true, true);
            }

            $reports = \DB::table('reports')->get();
            $sitesData = \DB::table('profile')->get();
            $sites = [];

            foreach ($sitesData as $k => $v) {
                $sites[$v->id] = $v->name;
            }

            if (count($sites) > 1) {
                $payments = \DB::table('payment')->get();
                $paymentData = [];

                foreach($payments as $k=>$v) {
                    $paymentData[$v->id] = $v;
                }
            }

            foreach ($reports as $report) {
                $report->filters = json_decode($report->filters, true);

                foreach ($report->filters as $fid => $filter) {
                    if (empty($filter)) {
                        unset($report->filters[$fid]);
                    }
                }

                $reporeJsonArray = (array)$report;
                unset($reporeJsonArray['id']);
                unset($reporeJsonArray['updated_at']);
                unset($reporeJsonArray['deleted_at']);
                $reporeJsonArray['version'] = 1;

                if ($report->type == 'driver') {
                    $drivers = \DB::table('report_drivers')->where('report_id', $report->id)->get();

                    foreach ($drivers as $driver) {
                        $driver->user_id = (int)$driver->user_id;
                        $reporeJsonArray['drivers'][$driver->user_id] = (array)$driver;
                        unset($reporeJsonArray['drivers'][$driver->user_id]['id']);
                        unset($reporeJsonArray['drivers'][$driver->user_id]['user_id']);
                        unset($reporeJsonArray['drivers'][$driver->user_id]['report_id']);
                        $driverBookings = \DB::table('report_driver_bookings')->where('report_driver_id', $driver->id)->get();

                        foreach ($driverBookings as $booking) {
                            $key = md5($booking->booking_id . $booking->booking_ref . strtotime($booking->date));

                            if (empty($reporeJsonArray['bookings'][$key])) {
                                $reporeJsonArray['bookings'][$key]['id'] = $booking->booking_id;
                                $reporeJsonArray['bookings'][$key]['ref_number'] = $booking->booking_ref;
                                $reporeJsonArray['bookings'][$key]['commission'] = $booking->commission;
                                $reporeJsonArray['bookings'][$key]['cash'] = $booking->cash;
                                $reporeJsonArray['bookings'][$key]['transactions'] = [];
                                $reporeJsonArray['bookings'][$key]['date'] = \Carbon\Carbon::parse($booking->date)->format('Y-m-d');
                            }

                            $reporeJsonArray['bookings'][$key]['drivers'][] = $driver->user_id;
                        }
                    }
                }
                elseif ($report->type == 'payment') {
                    $payments = \DB::table('report_payments')->where('report_id', $report->id)->get();

                    foreach ($payments as $pid => $payment) {
                        $payment->payment_id = (int)$payment->payment_id;

                        if (empty($reporeJsonArray['payments'][$payment->payment_id])) {
                            $reporeJsonArray['payments'][$payment->payment_id] = [
                                'id' => $payment->payment_id,
                                'name' => $payment->payment_name,
                                'status' => [],
                            ];

                            if (count($sites) > 1 && !empty($paymentData[$payment->payment_id]->site_id) && !empty($sites[$paymentData[$payment->payment_id]->site_id])) {
                                $reporeJsonArray['payments'][$payment->payment_id]['site'] = $sites[$paymentData[$payment->payment_id]->site_id];
                            }
                        }

                        $status = '';

                        foreach ((new \App\Models\Transaction())->options->status as $k => $v) {
                            if ($k == $payment->transaction_status) {
                                $status = $v['name'];
                            }
                        }

                        if (empty($reporeJsonArray['payments'][$payment->payment_id]['status'][$payment->transaction_status])) {
                            $reporeJsonArray['payments'][$payment->payment_id]['status'][$payment->transaction_status] = [
                                'name' => '',
                                'total' => 0,
                            ];
                        }

                        $reporeJsonArray['payments'][$payment->payment_id]['status'][$payment->transaction_status]['name'] = $status;
                        $reporeJsonArray['payments'][$payment->payment_id]['status'][$payment->transaction_status]['total'] += round($payment->total, 2);

                        $paymentBookings = \DB::table('report_payment_bookings')->where('report_payment_id', $payment->id)->get();

                        foreach ($paymentBookings as $booking) {
                            $key = md5($booking->booking_id . $booking->booking_ref . strtotime($booking->date));
                            $reporeJsonArray['payments'][$payment->payment_id]['status'][$payment->transaction_status]['bookings'][] = $key;

                            if (empty($reporeJsonArray['bookings'][$key])) {
                                $reporeJsonArray['bookings'][$key]['id'] = $booking->booking_id;
                                $reporeJsonArray['bookings'][$key]['ref_number'] = $booking->booking_ref;
                                $reporeJsonArray['bookings'][$key]['cash'] = null;
                                $reporeJsonArray['bookings'][$key]['commission'] = null;
                                $reporeJsonArray['bookings'][$key]['transactions'] = [];
                                $reporeJsonArray['bookings'][$key]['date'] = \Carbon\Carbon::parse($booking->date)->format('Y-m-d');
                            }

                            $reporeJsonArray['bookings'][$key]['transactions'][$payment->payment_id][$payment->transaction_status] = $booking->amount;
                        }
                    }
                }

                \Storage::disk('archive')->put('reports' . DIRECTORY_SEPARATOR . $report->id . '.json', json_encode($reporeJsonArray));
            }

            Schema::dropIfExists('report_driver_bookings');
            Schema::dropIfExists('report_drivers');
            Schema::dropIfExists('report_payment_bookings');
            Schema::dropIfExists('report_payments');
        }
    }

    public function down()
    {
        //
    }
}
