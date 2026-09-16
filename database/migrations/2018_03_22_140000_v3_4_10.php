<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V3410 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Change items type
        Schema::table('booking_route', function (Blueprint $table) {
            if ( Schema::hasColumn('booking_route', 'items') ) {
                $table->text('items')->nullable()->default(null)->change();
            }
        });

        // Create backup item column
        Schema::table('booking_route', function (Blueprint $table) {
            if ( !Schema::hasColumn('booking_route', 'bak_items') ) {
                $table->text('bak_items')->nullable()->after('items');
            }
        });

        // Make backup
        Schema::table('booking_route', function (Blueprint $table) {
            $bookings = DB::table('booking_route')->get();

            foreach ($bookings as $booking) {
                if (is_null($booking->bak_items)) {
                    DB::table('booking_route')->where('id', $booking->id)->update(['bak_items' => $booking->items]);
                }
            }
        });

        // Update items
        Schema::table('booking_route', function (Blueprint $table) {
            $bookings = DB::table('booking_route')->get();

            foreach ($bookings as $booking) {
                $items = [];
                $total = $booking->total_price;
                $itemsTotal = 0;

                if (!empty($booking->bak_items)) {
                    $booking->bak_items = json_decode($booking->bak_items);

                    foreach ($booking->bak_items as $k => $v) {
                        $items[] = [
                            'type' => 'other',
                            'name' => $v->name,
                            'value' => $v->value,
                            'amount' => $v->amount,
                        ];
                        $itemsTotal += $v->value * $v->amount;
                    }
                }

                if (!empty($booking->extra_charges_list) || !empty($booking->extra_charges_price)) {
                    $items[] = [
                        'type' => 'other',
                        'name' => (string)str_replace("\n", ", ", $booking->extra_charges_list),
                        'value' => $booking->extra_charges_price,
                        'amount' => 1,
                    ];
                    $itemsTotal += $booking->extra_charges_price;
                }

                if ($total - $itemsTotal > 0) {
                    $journeyTotal = $total - $itemsTotal;
                }
                else {
                    $journeyTotal = 0;
                }

                $journey = [
                    'type' => 'journey',
                    'name' => '',
                    'value' => $journeyTotal,
                    'amount' => 1,
                ];
                array_unshift($items, $journey);

                DB::table('booking_route')->where('id', $booking->id)->update(['items' => json_encode($items)]);
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
        //
    }
}
