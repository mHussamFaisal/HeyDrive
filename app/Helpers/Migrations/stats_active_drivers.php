<?php

use Illuminate\Database\Schema\Blueprint;

$request = request();
$billingDate = !empty($request->system->subscription->params->billingStartDate) ? $request->system->subscription->params->billingStartDate : null;

if ($billingDate && !\Schema::hasTable('stats_active_drivers')) {
    \Schema::create('stats_active_drivers', function (Blueprint $table) {
        $table->engine = 'InnoDB';
        $table->bigIncrements('id');
        $table->unsignedInteger('subscription_id');
        $table->unsignedInteger('driver_id');
        $table->string('driver_name');
        $table->string('ref_number');
        $table->smallInteger('job_count')->default(0);
        $table->timestamps();
    });

    if ($firstBooking = \DB::table('booking_route')->orderBy('created_date', 'asc')->first(['created_date'])) {
        $dates = calculate_billing_date($firstBooking->created_date);

        if ($dates !== null) {
            $prefix = get_db_prefix();
            $user = [];

            foreach ($dates as $date) {
                $items = \DB::table('users')->get([
                    \DB::raw("(SELECT
                                COUNT(`".$prefix."booking_route`.`id`)
                            FROM `".$prefix."booking_route`
                            WHERE `".$prefix."booking_route`.`driver_id` = `".$prefix."users`.`id`
                                AND `status` = 'completed'
                                AND `created_date` BETWEEN '".$date->from."' AND  '".$date->to."'
                        ) as `bookings`"),
                    \DB::raw("(SELECT
                                ref_number
                            FROM `".$prefix."booking_route`
                            WHERE `".$prefix."booking_route`.`driver_id` = `eto_users`.`id`
                                AND `status` = 'completed'
                                AND `created_date` BETWEEN '".$date->from."' AND  '".$date->to."'
                            ORDER BY `created_date` ASC
                            LIMIT 1
                        ) as `ref_number`"),
                    "name",
                    "id"
                ]);

                if (!empty($items)) {
                    foreach ($items as $item) {
                        if ($item->bookings && $item->bookings > 0) {
                            if (empty($user[$item->id])) {
                                $user[$item->id] = get_user($item->id);
                            }

                            $user = $user[$item->id];
                            $stats = new \App\Models\StatsActiveDriver();
                            $stats->subscription_id = $request->system->subscription->id;
                            $stats->driver_id = $item->id;
                            $stats->driver_name = $user->getName();
                            $stats->ref_number = $item->ref_number;
                            $stats->job_count = $item->bookings;
                            $stats->created_at = $date->from;
                            $stats->save();
                        }
                    }
                }
            }
        }
    }

    settings_save('eto.stats_active_drivers_created', true, 'system', 0);
}
