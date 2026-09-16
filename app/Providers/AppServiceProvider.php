<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Relation mapping
        Relation::morphMap([
            'user' => 'App\Models\User',
            'site' => 'App\Models\Site',
            'setting' => 'App\Models\Setting',
            'service' => 'App\Models\Service',
            'location' => 'App\Models\Location',
            'scheduled_route' => 'App\Models\ScheduledRoute',
            'feedback' => 'App\Models\Feedback',
            'booking' => 'App\Models\BookingRoute',
            'field' => 'App\Models\Field',
        ]);

        // Switch for blade
        \Blade::extend(function($value, $compiler) {
            $value = preg_replace('/(?<=\s)@switch\((.*)\)(\s*)@case\((.*)\)(?=\s)/', '<?php switch($1):$2case $3: ?>', $value);
            $value = preg_replace('/(?<=\s)@endswitch(?=\s)/', '<?php endswitch; ?>', $value);
            $value = preg_replace('/(?<=\s)@case\((.*)\)(?=\s)/', '<?php case $1: ?>', $value);
            $value = preg_replace('/(?<=\s)@default(?=\s)/', '<?php default: ?>', $value);
            $value = preg_replace('/(?<=\s)@break(?=\s)/', '<?php break; ?>', $value);
            return $value;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        // Cashier::useCustomerModel(\App\Models\User::class);
    }
}
