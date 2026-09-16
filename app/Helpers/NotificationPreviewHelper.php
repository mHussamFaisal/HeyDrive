<?php

namespace App\Helpers;

class NotificationPreviewHelper extends \App\Models\BookingRoute
{
    function __construct($status = 'pending')
    {
        $this->__getBookingRoute($status);
    }

    public function assignedDriver()
    {
        return $this->__getUser('driver');
    }

    public function assignedVehicle()
    {
        return $this->__getVehicle();
    }

    public function getOriginal($key = null, $default = null)
    {
        return \Illuminate\Support\Arr::get($this->attributes, $key, $default);
    }

    public function __getBooking()
    {
         $booking = new \App\Models\Booking;
         $booking->id = 100000000;
         $booking->site_id = config('site.site_id');
         $booking->user_id = 100000000;
         $booking->unique_key = md5(time());
         $booking->ref_number = "B-123";

         return $booking;
    }

    public function __getBookingRoute($status)
    {
        $attributes = new \stdClass;
        $attributes->id = 100000000;
        $attributes->booking_id = 100000000;
        $attributes->parent_booking_id = 0;
        $attributes->scheduled_route_id = 0;
        $attributes->params = '{"new_status":"requested"}';
        $attributes->service_id = 0;
        $attributes->service_duration = 0;
        $attributes->driver_id = 100000000;
        $attributes->driver_data = null;
        $attributes->driver_notes = null;
        $attributes->vehicle_id = 100000000;
        $attributes->vehicle_data = null;
        $attributes->commission = 20.0;
        $attributes->cash = 0.0;
        $attributes->status = $status;
        $attributes->status_notes = null;
        $attributes->ref_number = "B-123";
        $attributes->route = 1;
        $attributes->category_start = "5";
        $attributes->category_type_start = "address";
        $attributes->location_start = "Birmingham International Airport, B26 3QJ";
        $attributes->address_start = "Birmingham International Airport, B26 3QJ";
        $attributes->address_start_complete = "";
        $attributes->coordinate_start_lat = 52.452381;
        $attributes->coordinate_start_lon = -1.743507;
        $attributes->waypoints = "[]";
        $attributes->waypoints_complete = "[]";
        $attributes->category_end = "5";
        $attributes->category_type_end = "address";
        $attributes->location_end = "City Airport, E16 2PX";
        $attributes->address_end = "City Airport, E16 2PX";
        $attributes->address_end_complete = "";
        $attributes->coordinate_end_lat = 51.504845;
        $attributes->coordinate_end_lon = 0.049518;
        $attributes->distance = 116.9;
        $attributes->duration = 206;
        $attributes->distance_base_start = 0.0;
        $attributes->duration_base_start = 0;
        $attributes->distance_base_end = 0.0;
        $attributes->duration_base_end = 0;
        $attributes->date = date('Y-m-d H:i:s');
        $attributes->flight_number = "";
        $attributes->flight_landing_time = "";
        $attributes->departure_city = "";
        $attributes->departure_flight_number = "";
        $attributes->departure_flight_time = "";
        $attributes->departure_flight_city = "";
        $attributes->meet_and_greet = 0;
        $attributes->meeting_point = "";
        $attributes->waiting_time = 0;
        $attributes->vehicle = '[{"id":0,"amount":1}]';
        $attributes->vehicle_list = "Saloon";
        $attributes->passengers = 0;
        $attributes->luggage = 0;
        $attributes->hand_luggage = 0;
        $attributes->child_seats = 0;
        $attributes->baby_seats = 0;
        $attributes->infant_seats = 0;
        $attributes->wheelchair = 0;
        $attributes->items = '[{"type":"journey","name":"","value":300,"amount":1}]';
        $attributes->extra_charges_list = "";
        $attributes->extra_charges_price = 0.0;
        $attributes->total_price = 300.0;
        $attributes->discount = 0.0;
        $attributes->discount_code = "";
        $attributes->contact_title = "";
        $attributes->contact_name = "John Doe";
        $attributes->contact_email = "johndoe@example.com";
        $attributes->contact_mobile = "+448005550199";
        $attributes->lead_passenger_title = "";
        $attributes->lead_passenger_name = "";
        $attributes->lead_passenger_email = "";
        $attributes->lead_passenger_mobile = "";
        $attributes->requirements = "";
        $attributes->notified = 0;
        $attributes->source = "Admin";
        $attributes->source_details = "";
        $attributes->ip = "";
        $attributes->job_reminder = 0;
        $attributes->notes = null;
        $attributes->custom = null;
        $attributes->locale = "en-GB";
        $attributes->is_read = 1;
        $attributes->modified_date = null;
        $attributes->created_date = date('Y-m-d H:i:s');
        $attributes->booking = $this->__getBooking();

        foreach ($attributes as $k => $v) {
            $this->attributes[$k] = $v;
        }
    }

    public function __getUser($type = 'customer')
    {
        switch ($type) {
            case 'admin':
                $role = 'admin';
                // $used_role = \App\Models\Role::select('id')->where('slug', 'admin.root')->first();
                $name = 'Admin';
                $first_name = 'Admin';
                $last_name = '';
                $username = 'admin';
            break;
            case 'driver':
                $role = 'driver';
                // $used_role = \App\Models\Role::select('id')->where('slug', 'driver.root')->first();
                $name = 'John Doe';
                $first_name = 'John';
                $last_name = 'Doe';
                $username = 'johndoe';
            break;
            case 'customer':
            default:
                $role = 'customer';
                // $used_role = \App\Models\Role::select('id')->where('slug', 'customer.root')->first();
                $name = 'Ben Smith';
                $first_name = 'Ben';
                $last_name = 'Smith';
                $username = 'bensmith';
            break;
        }

        $user = new \App\Models\User;
        $user->id = 100000000;
        // $user->used_role = !empty($used_role->id) ? $used_role->id : 0;
        $user->role = $role;
        $user->name = $name;
        $user->username = $username;
        $user->email = $username ."@example.com";
        $user->avatar = null;
        $user->password = '';
        $user->remember_token = "";
        $user->push_token = null;
        $user->status = "approved";
        $user->lat = null;
        $user->lng = null;
        $user->accuracy = null;
        $user->heading = null;
        $user->last_seen_at = null;
        $user->created_at = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');

        $profile = new \App\Models\UserProfile;
        $profile->id = 100000000;
        $profile->user_id = 100000000;
        $profile->title = "";
        $profile->first_name = $first_name;
        $profile->last_name = $last_name;
        $profile->date_of_birth = null;
        $profile->mobile_no = "+448005550199";
        $profile->telephone_no = "+448005550199";
        $profile->emergency_no = "+448005550199";
        $profile->address = "25 Primrose Cottage";
        $profile->city = "London";
        $profile->postcode = "TW5 9AF";
        $profile->state = "London";
        $profile->country = "United Kingdom";
        $profile->profile_type = "private";
        $profile->company_name = "Company Name";
        $profile->company_number = "9123456";
        $profile->company_tax_number = "GB123456789";
        $profile->national_insurance_no = "";
        $profile->bank_account = "";
        $profile->unique_id = "D4378";
        $profile->commission = 20.0;
        $profile->availability = "[]";
        $profile->availability_status = 1;
        $profile->insurance = "";
        $profile->insurance_expiry_date = null;
        $profile->driving_licence = "";
        $profile->driving_licence_expiry_date = null;
        $profile->pco_licence = "";
        $profile->pco_licence_expiry_date = null;
        $profile->phv_licence = "";
        $profile->phv_licence_expiry_date = null;
        $profile->description = null;
        $profile->created_at = date('Y-m-d H:i:s');
        $profile->updated_at = date('Y-m-d H:i:s');

        $user->profile = $profile;

        return $user;
    }

    public function __getVehicle()
    {
        $vehicle = new \App\Models\Vehicle;
        $vehicle->id = 100000000;
        $vehicle->user_id = 100000000;
        $vehicle->name = "Skoda";
        $vehicle->image = null;
        $vehicle->registration_mark = "GB12345";
        $vehicle->mot = "";
        $vehicle->mot_expiry_date = date('Y-m-d H:i:s');
        $vehicle->make = "Ford";
        $vehicle->model = "Fiest";
        $vehicle->colour = "Red";
        $vehicle->body_type = "";
        $vehicle->no_of_passengers = 5;
        $vehicle->registered_keeper_name = "";
        $vehicle->registered_keeper_address = "";
        $vehicle->description = "";
        $vehicle->status = "activated";
        $vehicle->selected = 0;
        $vehicle->created_at = date('Y-m-d H:i:s');
        $vehicle->updated_at = date('Y-m-d H:i:s');

        return $vehicle;
    }
}
