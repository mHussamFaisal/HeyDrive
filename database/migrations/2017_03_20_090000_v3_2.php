<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V32 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_route', function (Blueprint $table) {
            if ( !Schema::hasColumn('booking_route', 'infant_seats') ) {
                $table->smallInteger('infant_seats')->default(0)->after('baby_seats');
            }

            if ( !Schema::hasColumn('booking_route', 'wheelchair') ) {
                $table->smallInteger('wheelchair')->default(0)->after('infant_seats');
            }

            if ( !Schema::hasColumn('booking_route', 'driver_status') ) {
                $table->string('driver_status')->nullable()->after('driver_vehicle_reg_no');
            }

            if ( !Schema::hasColumn('booking_route', 'driver_data') ) {
                $table->text('driver_data')->nullable()->after('driver_status');
            }

            if ( !Schema::hasColumn('booking_route', 'vehicle_id') ) {
                $table->integer('vehicle_id')->default(0)->after('driver_data');
            }

            if ( !Schema::hasColumn('booking_route', 'vehicle_data') ) {
                $table->text('vehicle_data')->nullable()->after('vehicle_id');
            }
        });

        Schema::table('vehicle', function (Blueprint $table) {
            if ( !Schema::hasColumn('vehicle', 'infant_seats') ) {
                $table->smallInteger('infant_seats')->default(0)->after('baby_seats');
            }

            if ( !Schema::hasColumn('vehicle', 'wheelchair') ) {
                $table->smallInteger('wheelchair')->default(0)->after('infant_seats');
            }
        });

        if ( !Schema::hasColumn('vehicle', 'infant_seats') ) {
            DB::table('vehicle')->update(['infant_seats' => '1']);
        }

        Schema::table('user', function (Blueprint $table) {
            if ( !Schema::hasColumn('user', 'is_company') ) {
                $table->tinyInteger('is_company')->default(0)->after('description');
            }
        });

        Schema::table('user_customer', function (Blueprint $table) {
            if ( !Schema::hasColumn('user_customer', 'company_number') ) {
                $table->string('company_number')->nullable()->after('company_name');
            }

            if ( !Schema::hasColumn('user_customer', 'company_tax_number') ) {
                $table->string('company_tax_number')->nullable()->after('company_number');
            }

            if ( Schema::hasColumn('user_customer', 'company_name') ) {
                $table->string('company_name')->nullable()->default(null)->change();
            }
        });

        Schema::table('vehicles', function (Blueprint $table) {
            if ( !Schema::hasColumn('vehicles', 'description') ) {
                $table->text('description')->nullable()->after('registered_keeper_address');
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            if ( !Schema::hasColumn('profiles', 'description') ) {
                $table->text('description')->nullable()->after('phv_licence_expiry_date');
            }

            if ( !Schema::hasColumn('profiles', 'profile_type') ) {
                $table->string('profile_type')->nullable()->after('country');
            }

            if ( !Schema::hasColumn('profiles', 'company_number') ) {
                $table->string('company_number')->nullable()->after('company_name');
            }

            if ( !Schema::hasColumn('profiles', 'company_tax_number') ) {
                $table->string('company_tax_number')->nullable()->after('company_number');
            }
        });

        $db = \DB::connection();
        $prefix = get_db_prefix();

        // Update 1
        if ( Schema::hasTable('user_driver') ) {
            $sql = "SELECT `a`.*, `a`.`id` AS `user_id_primary`, `b`.*
                    FROM `{$prefix}user` AS `a`
                    LEFT JOIN `{$prefix}user_driver` AS `b`
                    ON (`a`.`id`=`b`.`user_id`)
                    WHERE `a`.`type`='2'";

            $query = $db->select($sql);

            foreach($query as $key => $value) {
                // echo $value->type . '<br>';
                // echo $value->name . '<br>';

                $user = new \App\Models\User;
                $user->id = $value->user_id;
                $user->role = 'driver';
                $user->name = $value->name;
                $user->username = uniqid('user');
                $user->email = empty($value->email) ? strtolower(str_replace(' ', '', $value->name)).'@xxx.xx' : $value->email;
                $user->avatar = null;
                $user->password = bcrypt($value->password);
                $user->remember_token = null;
                $user->status = ($value->published) ? 'approved' : 'inactive';
                $user->save();

                $profile = new \App\Models\UserProfile;
                $profile->user_id = $value->user_id;
                $profile->title = $value->title;
                $profile->first_name = $value->first_name;
                $profile->last_name = $value->last_name;
                $profile->date_of_birth = null;
                $profile->mobile_no = $value->mobile_number;
                $profile->telephone_no = $value->telephone_number;
                $profile->emergency_no = $value->emergency_number;
                $profile->address = $value->address;
                $profile->city = $value->city;
                $profile->postcode = $value->postcode;
                $profile->state = $value->state;
                $profile->country = $value->country;
                $profile->profile_type = ($value->is_company) ? 'company' : 'private';
                $profile->company_name = $value->company_name;
                $profile->company_number = null;
                $profile->company_tax_number = null;
                $profile->national_insurance_no = $value->nin;
                $profile->bank_account = $value->bank_account;
                $profile->unique_id = $value->unique_id;
                $profile->commission = $value->commission;
                $profile->availability = $value->availability;
                $profile->insurance = $value->custom_field_3;
                $profile->insurance_expiry_date = ($value->custom_field_14 == '0000-00-00 00:00:00') ? null : $value->custom_field_14;
                $profile->driving_licence = $value->custom_field_1;
                $profile->driving_licence_expiry_date = ($value->custom_field_11 == '0000-00-00') ? null : $value->custom_field_11;
                $profile->pco_licence = $value->custom_field_2;
                $profile->pco_licence_expiry_date = ($value->custom_field_10 == '0000-00-00') ? null : $value->custom_field_10;
                $profile->phv_licence = $value->custom_field_9;
                $profile->phv_licence_expiry_date = ($value->custom_field_12 == '0000-00-00') ? null : $value->custom_field_12;
                $profile->description = $value->description;
                $profile->created_at = $value->created_date;
                $profile->updated_at = $value->created_date;
                $user->profile()->save($profile);

                $vehicle = new \App\Models\Vehicle;
                $vehicle->user_id = $value->user_id;
                $vehicle->name = $value->custom_field_5 .' - '. $value->vehicle_plates;
                $vehicle->image = null;
                $vehicle->registration_mark = $value->vehicle_plates;
                $vehicle->mot = $value->custom_field_4;
                $vehicle->mot_expiry_date = ($value->custom_field_13 == '0000-00-00 00:00:00') ? null : $value->custom_field_13;
                $vehicle->make = $value->custom_field_5;
                $vehicle->model = $value->custom_field_6;
                $vehicle->colour = $value->custom_field_7;
                $vehicle->body_type = $value->custom_field_8;
                $vehicle->no_of_passengers = 0;
                $vehicle->registered_keeper_name = null;
                $vehicle->registered_keeper_address = null;
                $vehicle->status = 'activated';
                $vehicle->selected = 0;
                $vehicle->created_at = $value->created_date;
                $vehicle->updated_at = $value->created_date;
                $vehicle->save();
            }

            Schema::rename('user_driver', 'user_driver_bak');
        }

        // Update 2
        $sql = "SELECT * FROM `{$prefix}booking_route` WHERE `driver_id`>'0'";

        $query = $db->select($sql);

        foreach($query as $key => $value) {
            $vehicle = \App\Models\Vehicle::where('user_id', '=', (int)$value->driver_id)->get()->first();
            $booking = \App\Models\BookingRoute::where('id', '=', (int)$value->id)->get()->first();

            if ( !empty($vehicle->id) && !empty($booking->id) ) {
                // echo $vehicle->id .'<br>';
                $booking->vehicle_id = $vehicle->id;
                $booking->save();
            }
        }

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
            if ( Schema::hasColumn('booking_route', 'infant_seats') ) {
                $table->dropColumn('infant_seats');
            }

            if ( Schema::hasColumn('booking_route', 'wheelchair') ) {
                $table->dropColumn('wheelchair');
            }

            if ( Schema::hasColumn('booking_route', 'driver_status') ) {
                $table->dropColumn('driver_status');
            }

            if ( Schema::hasColumn('booking_route', 'driver_data') ) {
                $table->dropColumn('driver_data');
            }

            if ( Schema::hasColumn('booking_route', 'vehicle_id') ) {
                $table->dropColumn('vehicle_id');
            }

            if ( Schema::hasColumn('booking_route', 'vehicle_data') ) {
                $table->dropColumn('vehicle_data');
            }
        });

        Schema::table('vehicle', function (Blueprint $table) {
            if ( Schema::hasColumn('vehicle', 'infant_seats') ) {
                $table->dropColumn('infant_seats');
            }

            if ( Schema::hasColumn('vehicle', 'wheelchair') ) {
                $table->dropColumn('wheelchair');
            }
        });

        Schema::table('user', function (Blueprint $table) {
            if ( Schema::hasColumn('user', 'is_company') ) {
                $table->dropColumn('is_company');
            }
        });

        Schema::table('user_customer', function (Blueprint $table) {
            if ( Schema::hasColumn('user_customer', 'company_number') ) {
                $table->dropColumn('company_number');
            }

            if ( Schema::hasColumn('user_customer', 'company_tax_number') ) {
                $table->dropColumn('company_tax_number');
            }

            if ( Schema::hasColumn('user_customer', 'company_name') ) {
                $table->dropColumn('company_name');
            }
        });

        Schema::table('vehicles', function (Blueprint $table) {
            if ( Schema::hasColumn('vehicles', 'description') ) {
                $table->dropColumn('description');
            }
        });

        Schema::table('profiles', function (Blueprint $table) {
            if ( Schema::hasColumn('profiles', 'description') ) {
                $table->dropColumn('description');
            }

            if ( Schema::hasColumn('profiles', 'profile_type') ) {
                $table->dropColumn('profile_type');
            }

            if ( Schema::hasColumn('profiles', 'company_number') ) {
                $table->dropColumn('company_number');
            }

            if ( Schema::hasColumn('profiles', 'company_tax_number') ) {
                $table->dropColumn('company_tax_number');
            }
        });
    }
}
