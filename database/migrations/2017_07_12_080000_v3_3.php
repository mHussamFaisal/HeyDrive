<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V33 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // update_columns_in_booking_route_table -------------------------------
        Schema::table('booking_route', function (Blueprint $table) {
            // Add items
            if ( !Schema::hasColumn('booking_route', 'items') ) {
                $table->string('items')->nullable()->after('wheelchair');
            }
            // Add service_id
            if ( !Schema::hasColumn('booking_route', 'service_id') ) {
                $table->unsignedInteger('service_id')->default(0)->after('booking_id');
            }
            // Add service_duration
            if ( !Schema::hasColumn('booking_route', 'service_duration') ) {
                $table->smallInteger('service_duration')->default(0)->after('service_id');
            }
            // Update distance
            if ( !Schema::hasColumn('booking_route', 'distance') ) {
                DB::table('booking_route')->where('distance', '')->update(['distance' => '0']);
                $table->float('distance', 8, 2)->default('0.00')->change();
            }
            // Update duration
            if ( !Schema::hasColumn('booking_route', 'duration') ) {
                DB::table('booking_route')->where('duration', '')->update(['duration' => '0']);
                $table->integer('duration')->default('0')->change();
            }
            // Add distance_base_start
            if ( !Schema::hasColumn('booking_route', 'distance_base_start') ) {
                $table->float('distance_base_start', 8, 2)->default('0.00')->after('duration');
            }
            // Add duration_base_start
            if ( !Schema::hasColumn('booking_route', 'duration_base_start') ) {
                $table->integer('duration_base_start')->default(0)->after('distance_base_start');
            }
            // Add distance_base_end
            if ( !Schema::hasColumn('booking_route', 'distance_base_end') ) {
                $table->float('distance_base_end', 8, 2)->default('0.00')->after('duration_base_start');
            }
            // Add duration_base_end
            if ( !Schema::hasColumn('booking_route', 'duration_base_end') ) {
                $table->integer('duration_base_end')->default(0)->after('distance_base_end');
            }
            // Add source
            if ( !Schema::hasColumn('booking_route', 'source') ) {
                $table->string('source')->nullable()->after('notified');
            }
            // Add source_details
            if ( !Schema::hasColumn('booking_route', 'source_details') ) {
                $table->string('source_details')->nullable()->after('source');
            }
            // Add discount
            if ( !Schema::hasColumn('booking_route', 'discount') ) {
                $table->double('discount', 8, 2)->default('0.00')->after('total_price');
            }
            // Add discount_code
            if ( !Schema::hasColumn('booking_route', 'discount_code') ) {
                $table->string('discount_code')->nullable()->after('discount');
            }
            // Add created_date
            if ( !Schema::hasColumn('booking_route', 'created_date') ) {
                $table->dateTime('created_date')->nullable()->after('modified_date');
            }
            // Add ip
            if ( !Schema::hasColumn('booking_route', 'ip') ) {
                $table->string('ip')->nullable()->after('source_details');
            }
        });

        // update_columns_in_booking_table --------------------------------
        $prefix = get_db_prefix();

        $sql = "
            ALTER TABLE `{$prefix}booking`
            CHANGE `routes` `bak_routes` TINYINT(3) NOT NULL DEFAULT '0',
            CHANGE `payment_id` `bak_payment_id` SMALLINT(5) NOT NULL DEFAULT '0',
            CHANGE `payment_method` `bak_payment_method` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `payment_name` `bak_payment_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `payment_price` `bak_payment_price` DOUBLE(5,2) NOT NULL DEFAULT '0.00',
            CHANGE `payment_status` `bak_payment_status` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `payment_response` `bak_payment_response` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `discount_code` `bak_discount_code` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `discount_price` `bak_discount_price` DOUBLE(5,2) NOT NULL DEFAULT '0.00',
            CHANGE `currency_id` `bak_currency_id` SMALLINT(5) NOT NULL DEFAULT '0',
            CHANGE `general_extra_charges_list` `bak_general_extra_charges_list` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `general_extra_charges_price` `bak_general_extra_charges_price` DOUBLE(5,2) NOT NULL DEFAULT '0.00',
            CHANGE `general_total_price` `bak_general_total_price` DOUBLE(10,2) NOT NULL DEFAULT '0.00',
            CHANGE `deposit_price` `bak_deposit_price` DOUBLE(5,2) NOT NULL DEFAULT '0.00',
            CHANGE `source` `bak_source` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `source_details` `bak_source_details` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `ip` `bak_ip` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
            CHANGE `created_date` `bak_created_date` DATETIME NULL DEFAULT NULL;
        ";

        if ( $sql ) {
            DB::statement($sql);
        }

        $bookings = \App\Models\Booking::with('routes')->get();

        foreach($bookings as $kB => $booking) {
            $total = 0;
            $deposit = $booking->bak_deposit_price;
            $discount_balance = $booking->bak_discount_price;

            foreach($booking->routes as $kR => $route) {
                // Discount
                $route->total_price = $route->total_price + $discount_balance;
                $route->discount = $discount_balance;
                $discount_balance = 0;
                $total += $route->total_price;

                // Other
                $route->source = $booking->bak_source;
                $route->source_details = $booking->bak_source_details;
                $route->discount_code = $booking->bak_discount_code;
                $route->created_date = $booking->bak_created_date;
                $route->ip = $booking->bak_ip;

                // Save
                $route->save();
            }

            // Payment
            $payment = DB::table('payment')->where('id', '=', $booking->bak_payment_id)->first();

            if ( empty($payment) ) {
                $payment = new \stdClass;
                $payment->id = 0;
                $payment->price = 0;
                $payment->params = null;
                $payment->factor_type = 0;
                $payment->method = 'none';
                $payment->name = 'None';
            }

            if ( !empty($payment) ) {

                if ( !empty($payment->params) ) {
                    $payment->params = json_decode($payment->params);
                }

                if ( $booking->bak_payment_status == 'paid' ) {
                    $status = 'paid';
                }
                elseif ( $booking->bak_payment_status == 'refunded' ) {
                    $status = 'refunded';
                }
                else {
                    $status = 'pending';
                }

                // Transactions
                if ( $total > 0 ) {
                    $balance = $total - $deposit;
                    $total_charge = 0;
                    $deposit_charge = 0;
                    $balance_charge = 0;

                    if ( $payment->price ) {
                        if ( $payment->factor_type == 1 ) {
                            $total_charge = ($total / 100) * $payment->price;
                            $deposit_charge = ($deposit / 100) * $payment->price;
                            $balance_charge = ($balance / 100) * $payment->price;
                        }
                        else {
                            $total_charge = $payment->price;
                            $deposit_charge = $payment->price;
                            $balance_charge = $payment->price;
                        }
                    }

                    if ( $deposit > 0 && $deposit < $total ) {
                        // Recalculate payment price
                        if ( $booking->bak_payment_price - $deposit_charge > 0 ) {
                            $balance_charge = $booking->bak_payment_price - $deposit_charge;
                        }
                        else {
                            $deposit_charge = $booking->bak_payment_price;
                            $balance_charge = 0;
                        }

                        DB::table('transactions')->insert([
                            [
                              'ref_type' => 'booking',
                              'ref_id' => $booking->id,
                              'unique_key' => md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000)),
                              'name' => trans('booking.transaction.deposit'),
                              'description' => '',
                              'payment_id' => $payment->id,
                              'payment_method' => $payment->method,
                              'payment_name' => $payment->name,
                              'payment_charge' => $deposit_charge,
                              'currency_id' => 0,
                              'amount' => $deposit,
                              'ip' => null,
                              'response' => $booking->bak_payment_response,
                              'requested_at' => null,
                              'status' => $status,
                            ],
                            [
                              'ref_type' => 'booking',
                              'ref_id' => $booking->id,
                              'unique_key' => md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000)),
                              'name' => trans('booking.transaction.balance'),
                              'description' => '',
                              'payment_id' => $payment->id,
                              'payment_method' => $payment->method,
                              'payment_name' => $payment->name,
                              'payment_charge' => $balance_charge,
                              'currency_id' => 0,
                              'amount' => $balance,
                              'ip' => null,
                              'response' => null,
                              'requested_at' => null,
                              'status' => $status,
                            ],
                        ]);

                    }
                    else {
                        // Recalculate payment price
                        $total_charge = $booking->bak_payment_price;

                        // Full amount
                        DB::table('transactions')->insert([
                            [
                              'ref_type' => 'booking',
                              'ref_id' => $booking->id,
                              'unique_key' => md5('transaction_'. date('Y-m-d H:i:s') . rand(1000, 100000)),
                              'name' => trans('booking.transaction.full_amount'),
                              'description' => '',
                              'payment_id' => $payment->id,
                              'payment_method' => $payment->method,
                              'payment_name' => $payment->name,
                              'payment_charge' => $total_charge,
                              'currency_id' => 0,
                              'amount' => $total,
                              'ip' => null,
                              'response' => $booking->bak_payment_response,
                              'requested_at' => null,
                              'status' => $status,
                            ],
                        ]);
                    }
                }
            }
        }

        // update_columns_in_fixed_prices_table --------------------------------
        Schema::table('fixed_prices', function (Blueprint $table) {
            // Add service_ids
            if ( !Schema::hasColumn('fixed_prices', 'service_ids') ) {
                $table->string('service_ids')->nullable()->after('profile_id');
            }
        });

        // update_columns_in_vehicle_table -------------------------------------
        Schema::table('vehicle', function (Blueprint $table) {
            // Add user_id
            if ( !Schema::hasColumn('vehicle', 'user_id') ) {
                $table->unsignedInteger('user_id')->default(0)->after('profile_id');
            }
            // Add service_ids
            if ( !Schema::hasColumn('vehicle', 'service_ids') ) {
                $table->string('service_ids')->nullable()->after('user_id');
            }
            // Add hourly_rate
            if ( !Schema::hasColumn('vehicle', 'hourly_rate') ) {
                $table->double('hourly_rate', 8, 2)->default('0.00')->after('service_ids');
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
        // update_columns_in_booking_route_table -------------------------------
        Schema::table('booking_route', function (Blueprint $table) {
            // Drop items
            if ( Schema::hasColumn('booking_route', 'items') ) {
                $table->dropColumn('items');
            }
            // Drop service_id
            if ( Schema::hasColumn('booking_route', 'service_id') ) {
                $table->dropColumn('service_id');
            }
            // Drop service_duration
            if ( Schema::hasColumn('booking_route', 'service_duration') ) {
                $table->dropColumn('service_duration');
            }
            // Drop distance_base_start
            if ( Schema::hasColumn('booking_route', 'distance_base_start') ) {
                $table->dropColumn('distance_base_start');
            }
            // Drop duration_base_start
            if ( Schema::hasColumn('booking_route', 'duration_base_start') ) {
                $table->dropColumn('duration_base_start');
            }
            // Drop distance_base_end
            if ( Schema::hasColumn('booking_route', 'distance_base_end') ) {
                $table->dropColumn('distance_base_end');
            }
            // Drop duration_base_end
            if ( Schema::hasColumn('booking_route', 'duration_base_end') ) {
                $table->dropColumn('duration_base_end');
            }
            // Drop source
            if ( Schema::hasColumn('booking_route', 'source') ) {
                $table->dropColumn('source');
            }
            // Drop source_details
            if ( Schema::hasColumn('booking_route', 'source_details') ) {
                $table->dropColumn('source_details');
            }
            // Drop discount
            if ( Schema::hasColumn('booking_route', 'discount') ) {
                $table->dropColumn('discount');
            }
            // Drop discount_code
            if ( Schema::hasColumn('booking_route', 'discount_code') ) {
                $table->dropColumn('discount_code');
            }
            // Drop created_date
            if ( Schema::hasColumn('booking_route', 'created_date') ) {
                $table->dropColumn('created_date');
            }
            // Drop ip
            if ( Schema::hasColumn('booking_route', 'ip') ) {
                $table->dropColumn('ip');
            }
        });

        // update_columns_in_fixed_prices_table --------------------------------
        Schema::table('fixed_prices', function (Blueprint $table) {
            // Drop service_ids
            if ( Schema::hasColumn('fixed_prices', 'service_ids') ) {
                $table->dropColumn('service_ids');
            }
        });

        // update_columns_in_vehicle_table -------------------------------------
        Schema::table('vehicle', function (Blueprint $table) {
            // Drop user_id
            if ( Schema::hasColumn('vehicle', 'user_id') ) {
                $table->dropColumn('user_id');
            }
            // Drop service_ids
            if ( Schema::hasColumn('vehicle', 'service_ids') ) {
                $table->dropColumn('service_ids');
            }
            // Drop hourly_rate
            if ( Schema::hasColumn('vehicle', 'hourly_rate') ) {
                $table->dropColumn('hourly_rate');
            }
        });
    }
}
