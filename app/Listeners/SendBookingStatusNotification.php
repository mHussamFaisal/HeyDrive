<?php

namespace App\Listeners;

use App\Events\BookingStatusChanged;

class SendBookingStatusNotification
{
    public function handle(BookingStatusChanged $event)
    {
        $booking = $event->booking;
        $editMode = $event->editMode;

        if (!empty($event->notifications)) {
            $notifications = $event->notifications;
        }
        else {
            $notifications = [
                ['type' => 'all']
            ];
        }

        if (!empty($booking->id) ) {
            // \App\Models\Config::ofSite($booking->booking->site_id)->toConfig();

            if (!empty($booking->booking->site_id)) {
                $config = \App\Models\Config::getBySiteId($booking->booking->site_id);
                if (!empty($booking->locale)) {
                    $config->loadLocale($booking->locale);
                }
                $config = $config->mapData()->getData();
            }

            foreach ($notifications as $notification) {
                $type = null;
                $role = [];
                $message = '';

                if (!empty($notification['message'])) {
                    $message = $notification['message'];
                }

                if (empty($notification['type']) || $notification['type'] == 'all') {
                    $notification['type'] = $booking->status;
                }

                if (!empty($notification['type'])) {
                    if ($notification['type'] != 'incomplete') {
                        $type = new \App\Notifications\BookingStatus($notification['type'], $booking, $message);
                    }
                }

                if (!empty($notification['role'])) {
                    $role = $notification['role'];
                }

                if ($type) {
                    if (array_key_exists('admin', $role) || empty($role)) {
                        // $used_role = \App\Models\Role::select('id')->where('slug', 'admin.root')->first();

                        $admin = new \App\Models\User;
                        // $admin->used_role = !empty($used_role->id) ? $used_role->id : 0;
                        $admin->role = 'admin';
                        $admin->name = config('site.company_name');
                        $admin->email = config('site.company_email');
                        $admin->profile = new \App\Models\UserProfile;
                        $admin->profile->mobile_no = config('site.company_telephone');

                        // if (!empty(auth()->user()->role) && $editMode == true ? auth()->user()->role != $admin->role : true) {
                        if (auth()->check() && $editMode == true ? !auth()->user()->hasRole('admin.*') : true) {
                            if (!config('site.allow_driver_app')) {
                                $admin->push_token = '';
                            }
                            $admin->notify($type);
                        }
                    }

                    if (array_key_exists('driver', $role) || empty($role)) {
                        $driver = \App\Models\User::find($booking->driver_id);

                        if (!empty($driver->id) && (
                            !empty(auth()->user()->id) ? auth()->user()->id != $driver->id : true
                        )) {
                            // $used_role = \App\Models\Role::select('id')->where('slug', 'driver.root')->first();

                            // $driver->used_role = !empty($used_role->id) ? $used_role->id : 0;
                            $driver->role = 'driver';

                            if ($driver->getSetting('app.locale') !== null) {
                                app()->setLocale($driver->getSetting('app.locale'));
                            }
                            elseif (!empty($booking->booking->site_id)) {
                                $settings = \App\Models\Config::ofSite($booking->booking->site_id)->whereKeys(['language'])->toObject();
                                if (!empty($settings->language)) {
                                    app()->setLocale($settings->language);
                                }
                            }

                            if (!empty($booking->booking->site_id) && !(!empty($config) && $config->language == app()->getLocale())) {
                                $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->loadLocale()->mapData()->getData();
                            }

                            if (!config('site.allow_driver_app')) {
                                $driver->push_token = '';
                            }
                            $driver->notify($type);
                        }
                    }

                    if (array_key_exists('customer', $role) || empty($role)) {
                        if (!empty($booking->locale)) {
                            app()->setLocale($booking->locale);
                        }
                        elseif (!empty($booking->booking->site_id)) {
                            $settings = \App\Models\Config::ofSite($booking->booking->site_id)->whereKeys(['language'])->toObject();
                            if (!empty($settings->language)) {
                                app()->setLocale($settings->language);
                            }
                        }

                        if (!empty($booking->booking->site_id) && !(!empty($config) && $config->language == app()->getLocale())) {
                            $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->loadLocale()->mapData()->getData();
                        }
// dd('xx', $config->language, $booking->locale, $booking);
                        // $used_role = \App\Models\Role::select('id')->where('slug', 'customer.root')->first();

                        $customer = new \App\Models\User;
                        // $customer->used_role = !empty($used_role->id) ? $used_role->id : 0;
                        $customer->role = 'customer';
                        $customer->name = $booking->getContactFullName();
                        $customer->email = $booking->contact_email;
                        $customer->profile = new \App\Models\UserProfile;
                        $customer->profile->mobile_no = $booking->contact_mobile;

                        if (!config('site.allow_customer_app')) {
                            $customer->push_token = '';
                        }
                        else {
                            if ($booking->booking->user_id) {
                                $db = \DB::connection();
                                $dbPrefix = get_db_prefix();

                                $sql = "SELECT `push_token`
                                    FROM `{$dbPrefix}user`
                                    WHERE `id`='". $booking->booking->user_id ."'
                                    LIMIT 1";

                                $query = $db->select($sql);
                                if (!empty($query[0])) {
                                    $query = $query[0];
                                }

                                if (!empty($query) && $query->push_token) {
                                    $customer->push_token = $query->push_token;
                                }
                            }
                        }

                        // if (!empty(session('etoUserId'))) {
                            $customer->notify($type);
                        // }
										}

                    if (array_key_exists('other', $role)) {
                        if (!empty($booking->locale)) {
                            app()->setLocale($booking->locale);
                        }
                        elseif (!empty($booking->booking->site_id)) {
                            $settings = \App\Models\Config::ofSite($booking->booking->site_id)->whereKeys(['language'])->toObject();
                            if (!empty($settings->language)) {
                                app()->setLocale($settings->language);
                            }
                        }

                        if (!empty($booking->booking->site_id) && !(!empty($config) && $config->language == app()->getLocale())) {
                            $config = \App\Models\Config::getBySiteId($booking->booking->site_id)->loadLocale()->mapData()->getData();
                        }

                        $override = [];
                        $name = '';
                        $email = '';
                        $phone = '';

                        if (!empty($role['other']['name'])) {
                            $name = $role['other']['name'];
                        }

                        if (!empty($role['other']['email'])) {
                            $override[] = 'email';
                            $email = $role['other']['email'];
                        }

                        if (!empty($role['other']['phone'])) {
                            $override[] = 'sms';
                            $phone = $role['other']['phone'];
                        }

                        // $used_role = \App\Models\Role::select('id')->where('slug', 'customer.root')->first();

                        $other = new \App\Models\User;
                        // $other->used_role = !empty($used_role->id) ? $used_role->id : 0;
                        $other->role = 'customer';
                        $other->name = $name;
                        $other->email = $email;
                        $other->profile = new \App\Models\UserProfile;
                        $other->profile->mobile_no = $phone;
                        $other->override_channels = $override;
                        $other->notify($type);
                    }
                }
            }
        }
    }
}
