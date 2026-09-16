<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3317 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add new columns to booking table
        Schema::table('booking_route', function (Blueprint $table) {
            if ( !Schema::hasColumn('booking_route', 'commission') ) {
                $table->double('commission', 8, 2)->default('0.00')->after('vehicle_data');
            }

            if ( !Schema::hasColumn('booking_route', 'cash') ) {
                $table->double('cash', 8, 2)->default('0.00')->after('commission');
            }

            if ( !Schema::hasColumn('booking_route', 'notes') ) {
                $table->text('notes')->nullable()->after('job_reminder');
            }
        });

        // Update booking data
        Schema::table('booking_route', function (Blueprint $table) {
            $bookings = \App\Models\BookingRoute::all();

            foreach($bookings as $kB => $booking)
            {
                if ( Schema::hasColumn('booking_route', 'driver_name') && Schema::hasColumn('booking_route', 'driver_data') ) {
                    if ( !empty($booking->driver_name) && empty($booking->driver_data) ) {
                        $booking->driver_data = json_encode(['name' => $booking->driver_name]);
                    }
                }

                if ( Schema::hasColumn('booking_route', 'driver_vehicle_reg_no') && Schema::hasColumn('booking_route', 'vehicle_data') ) {
                    if ( !empty($booking->driver_vehicle_reg_no) && empty($booking->vehicle_data) ) {
                        $booking->vehicle_data = json_encode(['registration_mark' => $booking->driver_vehicle_reg_no]);
                    }
                }

                if ( Schema::hasColumn('booking_route', 'commission') ) {
                    if ( !empty($booking->driver_id) ) {
                        $driver = \App\Models\User::find($booking->driver_id);

                        if ( !empty($driver->profile) && !empty($driver->profile->commission) ) {
                            $booking->commission = round( ($booking->total_price / 100) * $driver->profile->commission, 2);
                        }
                    }
                }

                // Status mapping
                if ( Schema::hasColumn('booking_route', 'status') ) {
                    // Booking status
                    $mapStatus = [
                        'created' => 'pending',
                        'confirmed' => 'confirmed',
                        'assigned' => 'assigned',
                        'accepted' => 'accepted',
                        'declined' => 'rejected',
                        'onroute' => 'onroute',
                        'onboard' => 'onboard',
                        'completed' => 'completed',
                        'canceled' => 'canceled',
                        'incomplete' => 'incomplete',
                        'requested' => 'requested',
                        'quote' => 'quote',
                        'custom_1' => 'canceled', // Client did not arrive
                        'custom_2' => 'onroute', // Driver on route
                        'custom_3' => 'pending', // Driver Pob
                        'custom_4' => 'pending', // Driver clear
                    ];

                    if ( $booking->status == 'custom_1' ) {
                        $booking->notes = 'Client did not arrive';
                    }
                    elseif ( $booking->status == 'custom_4' ) {
                        $booking->notes = 'Driver clear';
                    }

                    if ( !empty($mapStatus[$booking->status]) ) {
                        $booking->status = $mapStatus[$booking->status];
                    }
                    else {
                        $booking->status = 'pending';
                    }

                    // Driver status
                    if ( Schema::hasColumn('booking_route', 'driver_status') ) {
                        if ( $booking->driver_status && !in_array($booking->status, ['completed', 'canceled', 'incomplete']) ) {
                            $booking->status = $booking->driver_status;
                        }
                    }
                }

                $booking->save();
            }
        });

        // Rename columns
        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'driver_name') ) {
                $table->renameColumn('driver_name', 'bak_driver_name');
            }

            if ( Schema::hasColumn('booking_route', 'driver_vehicle_reg_no') ) {
                $table->renameColumn('driver_vehicle_reg_no', 'bak_driver_vehicle_reg_no');
            }

            if ( Schema::hasColumn('booking_route', 'driver_status') ) {
                $table->renameColumn('driver_status', 'bak_driver_status');
            }
        });

        // Set columns to null
        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'bak_driver_name') ) {
                $table->string('bak_driver_name')->nullable()->default(null)->change();
            }

            if ( Schema::hasColumn('booking_route', 'bak_driver_vehicle_reg_no') ) {
                $table->string('bak_driver_vehicle_reg_no')->nullable()->default(null)->change();
            }

            if ( Schema::hasColumn('booking_route', 'bak_driver_status') ) {
                $table->string('bak_driver_status')->nullable()->default(null)->change();
            }
        });

        // Add column to vehcile table
        Schema::table('vehicle', function (Blueprint $table) {
            if ( !Schema::hasColumn('vehicle', 'is_backend') ) {
                $table->tinyInteger('is_backend')->default(0)->after('published');
            }
        });

        // Add column to payment table
        Schema::table('payment', function (Blueprint $table) {
            if ( !Schema::hasColumn('payment', 'is_backend') ) {
                $table->tinyInteger('is_backend')->default(0)->after('published');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'commission') ) {
                $table->dropColumn('commission');
            }

            if ( Schema::hasColumn('booking_route', 'cash') ) {
                $table->dropColumn('cash');
            }

            if ( Schema::hasColumn('booking_route', 'notes') ) {
                $table->dropColumn('notes');
            }
        });

        Schema::table('vehicle', function (Blueprint $table) {
            if ( Schema::hasColumn('vehicle', 'is_backend') ) {
                $table->dropColumn('is_backend');
            }
        });

        Schema::table('payment', function (Blueprint $table) {
            if ( Schema::hasColumn('payment', 'is_backend') ) {
                $table->dropColumn('is_backend');
            }
        });
    }
}
