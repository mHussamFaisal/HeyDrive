<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrepareRoles extends Migration
{
    public function up()
    {
        $prefix = get_db_prefix();

        if (Schema::hasTable('subscriptions')) {
            DB::statement("ALTER TABLE `{$prefix}subscriptions` ENGINE = InnoDB");
        }
        if (Schema::hasTable('subscription_modules')) {
            DB::statement("ALTER TABLE `{$prefix}subscription_modules` ENGINE = InnoDB");
        }
        if (Schema::hasTable('modules')) {
            DB::statement("ALTER TABLE `{$prefix}modules` ENGINE = InnoDB");
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                if (!Schema::hasColumn('roles', 'subscription_id')) {
                    $table->integer('subscription_id')->after('id')->unsigned()->nullable();
                }
                if (!Schema::hasColumn('roles', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            Schema::table('permissions', function (Blueprint $table) {
                if (Schema::hasColumn('permissions', 'name')) {
                    $table->string('name')->nullable()->change();
                }
                if (!Schema::hasColumn('permissions', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            Schema::table('users', function (Blueprint $table) use ($prefix) {
                $table->engine = 'InnoDB';
                if (!Schema::hasColumn('users', 'used_role')) {
                    $table->integer('used_role')->after('id')->unsigned()->nullable();

                    $table->foreign('used_role', $prefix . 'users_used_role')
                        ->references('id')
                        ->on('roles')
                        ->onDelete('cascade');
                }
            });

            DB::table('roles')->insert([
                ['name' => 'Admin', 'slug' => 'admin.root', 'level' => 100000],
                ['name' => 'Manager', 'slug' => 'admin.manager', 'level' => 10000],
                ['name' => 'Operator', 'slug' => 'admin.operator', 'level' => 1000],
                ['name' => 'Fleet Operator', 'slug' => 'admin.fleet_operator', 'level' => 500],
                ['name' => 'Driver', 'slug' => 'driver.root', 'level' => 100],
                ['name' => 'Customer', 'slug' => 'customer.root', 'level' => 50]
            ]);

            $roles = DB::table('roles')->get(['id','slug']);

            foreach($roles as $role) {
                if ($role->slug == 'admin.root') {
                    $roleCollection['admin'] = $role->id;
                } elseif ($role->slug == 'admin.manager') {
                    $roleCollection['manager'] = $role->id;
                } elseif ($role->slug == 'admin.operator') {
                    $roleCollection['operator'] = $role->id;
                } elseif ($role->slug == 'admin.fleet_operator') {
                    $roleCollection['fleet_operator'] = $role->id;
                } elseif ($role->slug == 'driver.root') {
                    $roleCollection['driver'] = $role->id;
                } elseif ($role->slug == 'customer.root') {
                    $roleCollection['customer'] = $role->id;
                }
            }

            $rolePermissions = [];
            $permissionsToDB = [];

            $groups = [
                'admin.backups' => 'Backups',
                'admin.bookings' => 'Bookings',
                'admin.discounts' => 'Discounts',
                'admin.users.admin' => 'Admins',
                'admin.users.customer' => 'Customers',
                'admin.users.driver' => 'Drivers',
                'admin.feedback' => 'Feedback',
                'admin.fixed_prices' => 'Fixed Prices',
                'admin.roles' => 'Roles',
                'admin.settings.mileage_time' => 'Distance & Time Pricing settings',
                'admin.settings.deposit_payments' => 'Deposit Payments settings',
                'admin.settings.night_surcharge' => 'Night Surcharge settings',
                'admin.settings.holiday_surcharge' => 'Holiday / Rush Hours Surcharge settings',
                'admin.settings.additional_charges' => 'Additional Charges settings',
                'admin.settings.charges' => 'Parking charge settings',
                'admin.settings.other_discount' => 'Return Journey and Account Discounts settings',
                'admin.settings.tax' => 'Tax settings',
                'admin.settings.booking' => 'Booking settings',
                'admin.settings.general' => 'General settings',
                'admin.settings.localization' => 'Localization',
                'admin.settings.web_booking_widget' => 'Web Booking Widget settings',
                'admin.settings.google' => 'Google settings',
                'admin.settings.notifications' => 'Notifications settings',
                'admin.settings.bases' => 'Operating Areas settings',
                'admin.settings.invoices' => 'Invoices settings',
                'admin.settings.users' => 'Users settings',
                'admin.settings.styles' => 'Styles settings',
                'admin.settings.driver_income' => 'Driver Incone',
                'admin.settings.airport_detection' => 'Airport detection',
                'admin.settings.integration' => 'Integration',
                'admin.settings.export' => 'Export',
                'admin.services' => 'Services',
                'admin.scheduled_routes' => 'Scheduled routes',
                'admin.translations' => 'Translations',
                'admin.vehicles' => 'Vehicles',
                'admin.vehicle_types' => 'Types of Vehicles',
                'admin.locations' => 'Locations',
                'admin.categories' => 'Location categories',
                'admin.excluded_routes' => 'Restricted Areas',
                'admin.meeting_points' => 'Meeting Points',
                'admin.payments' => 'Payment Methods',
                'admin.subscription' => 'Subscription section',
                'admin.reports' => 'Reports',
                'admin.zones' => 'Zones',
                'admin.transactions' => 'Transactions',
                'driver.calendar' => 'Driver calendar',
                'driver.jobs' => 'Driver Jobs',
            ];
            $haveTrash = [
                'admin.bookings',
                'admin.roles',
                'admin.reports'
            ];
            $notListing = [
                'admin.reports'
            ];
            $notShow = [
                'admin.backups',
                'admin.settings.mileage_time',
                'admin.settings.deposit_payments',
                'admin.settings.night_surcharge',
                'admin.settings.holiday_surcharge',
                'admin.settings.additional_charges',
                'admin.settings.other_discount',
                'admin.settings.tax',
                'admin.settings.general',
                'admin.settings.localization',
                'admin.settings.booking',
                'admin.translations',
                'admin.settings.web_booking_widget',
                'admin.settings.google',
                'admin.settings.notifications',
                'admin.settings.bases',
                'admin.settings.driver_income',
                'admin.settings.invoices',
                'admin.settings.users',
                'admin.settings.styles',
                'admin.settings.airport_detection',
                'admin.subscription',
                'admin.settings.integration',
                'admin.settings.export',
                'admin.transactions',
                'driver.calendar',
            ];
            $notEdit = [
                'admin.backups',
                'admin.reports',
                'admin.settings.export',
                'admin.subscription',
            ];
            $notCreate = [
                'admin.settings.mileage_time',
                'admin.settings.deposit_payments',
                'admin.settings.night_surcharge',
                'admin.settings.holiday_surcharge',
                'admin.settings.additional_charges',
                'admin.settings.other_discount',
                'admin.settings.tax',
                'admin.settings.general',
                'admin.settings.localization',
                'admin.settings.booking',
                'admin.translations',
                'admin.settings.web_booking_widget',
                'admin.settings.google',
                'admin.settings.notifications',
                'admin.settings.bases',
                'admin.settings.driver_income',
                'admin.settings.invoices',
                'admin.settings.users',
                'admin.settings.styles',
                'admin.settings.airport_detection',
                'admin.subscription',
                'admin.settings.integration',
                'admin.settings.export',
                'driver.jobs',
            ];
            $notDestroy = [
                'admin.settings.mileage_time',
                'admin.settings.deposit_payments',
                'admin.settings.night_surcharge',
                'admin.settings.holiday_surcharge',
                'admin.settings.additional_charges',
                'admin.settings.other_discount',
                'admin.settings.tax',
                'admin.settings.general',
                'admin.settings.localization',
                'admin.settings.booking',
                'admin.settings.web_booking_widget',
                'admin.settings.google',
                'admin.settings.notifications',
                'admin.settings.bases',
                'admin.settings.driver_income',
                'admin.settings.invoices',
                'admin.settings.users',
                'admin.settings.styles',
                'admin.settings.airport_detection',
                'admin.settings.integration',
                'admin.settings.export',
                'driver.jobs',
            ];
            // ----------------------------

            foreach ($groups as $key=>$name) {
                if (!in_array($key, $notListing)) {
                    $permissionsToDB[] = ['slug' => $key . '.index'];
                }

                if (!in_array($key, $notShow)) {
                    $permissionsToDB[] = ['slug' => $key . '.show'];
                }

                if (!in_array($key, $notCreate)) {
                    $permissionsToDB[] = ['slug' => $key . '.create'];
                }

                if (!in_array($key, $notEdit)) {
                    $permissionsToDB[] = ['slug' => $key . '.edit'];
                }

                if (in_array($key, $haveTrash)) {
                    $permissionsToDB[] = ['slug' => $key . '.trash'];
                }

                if (!in_array($key, $notDestroy)) {
                    $permissionsToDB[] = ['slug' => $key . '.destroy'];
                }

                if (in_array($key, $haveTrash)) {
                    $permissionsToDB[] = ['slug' => $key . '.restore'];
                }
            }

            $permissionsToDB = array_merge($permissionsToDB, [
                ['slug' => 'admin.reports.send'],
                ['slug' => 'admin.reports.export'],
                ['slug' => 'admin.dispatch.index'],
                ['slug' => 'admin.activity.index'],
                ['slug' => 'admin.backups.move'],
                ['slug' => 'admin.web_widget.index'],
                ['slug' => 'admin.mobile_app.index'],
                ['slug' => 'admin.logs.index'],
                ['slug' => 'admin.subscription.deactivation'],
                ['slug' => 'admin.subscription.install'],
                ['slug' => 'admin.subscription.update'],
                ['slug' => 'admin.news.index'],
                ['slug' => 'admin.settings.getting_started.index'],
                ['slug' => 'admin.bookings.tracking'],
                ['slug' => 'admin.bookings.filter'],
                ['slug' => 'admin.bookings.export'],
                ['slug' => 'admin.bookings.invoice'],
                ['slug' => 'admin.bookings.sms'],
                ['slug' => 'admin.bookings.notifications'],
                ['slug' => 'admin.bookings.assign_driver'],
                ['slug' => 'admin.bookings.meeting_board'],
                ['slug' => 'admin.bookings.mark_as_read'],
                ['slug' => 'admin.sites.switch'],
                ['slug' => 'admin.documentation.index'],
                ['slug' => 'admin.settings.debug.index'],
            ]);

            DB::table('permissions')->insert($permissionsToDB);

            $users = \DB::table('users')->orderBy('id', 'asc')->get();

            foreach ($users as $user) {
                $muser = \App\Models\User::where('id', $user->id)->first();
                if ($user->role && !$muser->hasRole($user->role)) {
                    $muser->attachRole($roleCollection[$user->role]);
                    $muser->used_role = $roleCollection[$user->role];
                    $muser->save();
                }
            }

            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }

    public function down()
    {
        //
    }
}
