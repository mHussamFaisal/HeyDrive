<?php

namespace App\Http\Controllers\User;

use App\Models\Customer;
use Carbon\Carbon;

class CustomerController extends UserController
{
    public function getConverter($userData) {
        $convertUsers = new \stdClass();
        $convertUsers->id = $userData->id;
        $convertUsers->role = 'customer';
        $convertUsers->name = $userData->name;
        $convertUsers->username = '';
        $convertUsers->email = $userData->email;
        $convertUsers->avatar = '';
        $convertUsers->avatar_path = '';
        $convertUsers->status = $userData->published == 0 ? 'inactive' : 'approved';
        $convertUsers->lat = '';
        $convertUsers->lng = '';
        $convertUsers->accuracy = '';
        $convertUsers->heading = '';
        $convertUsers->last_seen_at = $userData->last_visit_date;
        $convertUsers->created_at = $userData->created_date;
        $convertUsers->departments = $userData->departments;
        $convertUsers->updated_at = '';
        $profile = $userData->profile;

        $convertUsers->profile = new \stdClass();
        $convertUsers->profile->id = $profile->id;
        $convertUsers->profile->user_id = $profile->user_id;
        $convertUsers->profile->title = $profile->title;
        $convertUsers->profile->first_name = $profile->first_name;
        $convertUsers->profile->last_name = $profile->last_name;
        $convertUsers->profile->date_of_birth = '';
        $convertUsers->profile->mobile_no = $profile->mobile_number;
        $convertUsers->profile->telephone_no = $profile->telephone_number;
        $convertUsers->profile->emergency_no = $profile->emergency_number;
        $convertUsers->profile->address = $profile->address;
        $convertUsers->profile->city = $profile->city;
        $convertUsers->profile->postcode = $profile->postcode;
        $convertUsers->profile->state = $profile->state;
        $convertUsers->profile->country = $profile->country;
        $convertUsers->profile->profile_type = $userData->is_company === 1 ? 'company' : 'private';
        $convertUsers->profile->company_name = $profile->company_name;
        $convertUsers->profile->company_number = $profile->company_number;
        $convertUsers->profile->company_tax_number = $profile->company_tax_number;
        $convertUsers->profile->national_insurance_no = '';
        $convertUsers->profile->bank_account = '';
        $convertUsers->profile->unique_id = '';
        $convertUsers->profile->commission = '';
        $convertUsers->profile->availability = '';
        $convertUsers->profile->availability_status = 0;
        $convertUsers->profile->insurance = '';
        $convertUsers->profile->insurance_expiry_date = '';
        $convertUsers->profile->driving_licence = '';
        $convertUsers->profile->driving_licence_expiry_date = '';
        $convertUsers->profile->pco_licence = '';
        $convertUsers->profile->pco_licence_expiry_date = '';
        $convertUsers->profile->phv_licence = '';
        $convertUsers->profile->phv_licence_expiry_date = '';
        $convertUsers->profile->description = '';
        $convertUsers->profile->created_at = '';
        $convertUsers->profile->updated_at = '';

        return $convertUsers;
    }

    public function setConverter($userData) {
        $convertUsers = new \stdClass();
    }

    public function getList($customer_id = false) {
        $convertedUsers = [];
        $query = Customer::with('profile');

        if ($customer_id) {
            $query->where('id', $customer_id);
        }
        $users = $query->get();

        foreach( $users as $userData ) {
            $convertedUsers[$userData->id] = $this->getConverter($userData);
            $convertedUsers[$userData->id] = $this->getAvatarPath($convertedUsers[$userData->id]);
        }

        return $convertedUsers;
    }

    public function get($customer_id = false) {
        if ($customer_id === false) { return false; }
        $customer = $this->getList($customer_id);

        if (empty($customer[$customer_id])) {
            return [];
        }

        return $customer[$customer_id];
    }

    public function search() {
        $sites = [];
        foreach(request()->system->sites as $site) {
            $sites[] = $site->id;
        }

        $search = request('search', '');
        $convertedUsers = [];
        $query = Customer::with('profile')
            ->where('activated', 1)
            ->where('name', 'like', '%'. $search .'%')
            ->orWhere('email', 'like', '%'. $search .'%')
            ->orWhereHas ('profile', function ($query) use($search) {
                $query->where('first_name', 'like', '%'. $search .'%')
                    ->orWhere('last_name', 'like', '%'. $search .'%')
                    ->orWhere('mobile_number', 'like', '%'. $search .'%'); // mobile_no
            })->whereIn('site_id', $sites);

        $usersCount = $query->get()->count();

        if (request('perPage')){
            $query->skip((request('page', 1) - 1) * request('perPage'))->take(request('perPage', 10));
        }
        $users = $query->get();

        foreach( $users as $userData ) {
            $convertedUsers[$userData->id] = $this->getConverter($userData);
            $convertedUsers[$userData->id] = $this->getAvatarPath($convertedUsers[$userData->id]);
        }

        return ['items'=>$convertedUsers, 'count_items'=>$usersCount];
    }

    public function searchPassenger() {
        $dbPrefix = get_db_prefix();

        $leadPassenger = \App\Models\BookingRoute::select(
            'lead_passenger_name as passenger_name',
            'lead_passenger_email as passenger_email',
            'lead_passenger_mobile as passenger_phone'
        )->where('lead_passenger_name', '!=', '');

        if (request('search')) {
            $leadPassenger->where(function ($query) {
                $query->where('lead_passenger_name', 'like', '%'.request('search').'%')
                    ->orWhere('lead_passenger_email', 'like', '%'.request('search').'%')
                    ->orWhere('lead_passenger_mobile', 'like', '%'.request('search').'%');
            });
        }

        $customer = \App\Models\Customer::select('name as passenger_name',
            'email as passenger_email',
            \DB::raw('(SELECT
                  CASE
                    WHEN `'.$dbPrefix.'user_customer`.`mobile_number` != \'\' THEN `'.$dbPrefix.'user_customer`.`mobile_number`
                    WHEN `'.$dbPrefix.'user_customer`.`telephone_number` != \'\' THEN `'.$dbPrefix.'user_customer`.`telephone_number`
                    ELSE \'\'
                  END
                 FROM `'.$dbPrefix.'user_customer` WHERE `'.$dbPrefix.'user`.id = `'.$dbPrefix.'user_customer`.`user_id`) as `passengerPhone`')
        )->where('name', '!=', '');

        if (request('search')) {
            $customer->where(function ($query) {
                $query->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('email', 'like', '%'.request('search').'%');
            });
        }

        $passengers = \App\Models\BookingRoute::select('contact_name as passenger_name',
            'contact_email as passenger_email',
            'contact_mobile as passenger_phone')
            ->union($leadPassenger->getQuery())
            ->union($customer->getQuery())
            ->where('contact_name', '!=', '');

        if (request('search')) {
            $passengers->where(function ($query) {
                $query->where('contact_name', 'like', '%'.request('search').'%')
                    ->orWhere('contact_email', 'like', '%'.request('search').'%')
                    ->orWhere('contact_mobile', 'like', '%'.request('search').'%');
            });
        }

        $passengers->orderBy('passenger_name','asc');
        $passengersCount = $passengers->get()->count();

        if (request('perPage')) {
            $passengers->skip((request('page', 1) - 1) * request('perPage'))->take(request('perPage', 10));
        }

        $items = $passengers->get();

        if (!empty($items)) {
            $count = $items->count();
            $items = $items->toArray();

            foreach ($items as $id => $item) {
//                $items[$id]['id'] = str_slug($item['passenger_name'], '_');
                $items[$id]['id'] = $item['passenger_name'];
                $items[$id]['name'] = $item['passenger_name'];
                $items[$id]['role'] = 'passenger';
            }
        }

        return ['items'=>$items, 'count_items'=>$passengersCount, 'count'=>$count];
    }

    public function getAvatarPath($customer)
    {
        if (\Storage::disk('avatars')->exists($customer->avatar)) {
            $customer->avatar_path = asset_url('uploads','avatars/'. $customer->avatar);
        }
        else {
            $customer->avatar_path = asset_url('images','placeholders/avatar.png');
        }

        return $customer;
    }

    public function import(Request $request)
    {
        $site_id = config('site.site_id');

        switch ($request->get('action')) {
            case 'download':

                $rows = [[
                    'A1' => 'First name',
                    'A2' => 'Last name',
                    'A3' => 'Phone number',
                    'A4' => 'Email',
                    'A5' => 'Registration date',
                ]];

                for ($i=0; $i < 10; $i++) {
                    $rows[] = [
                        'A1' => 'John',
                        'A2' => 'Doe',
                        'A3' => '+447413004455',
                        'A4' => 'john@doe.com',
                        'A5' => date('Y-m-d'),
                    ];
                }

                $filename = 'Customers - Standard import template';

                return \Excel::create($filename, function($excel) use ($rows) {
                    $excel->sheet('Standard', function($sheet) use ($rows) {
                        $sheet->fromArray($rows, null, 'A1', false, false);
                    });
                })->download('csv');

            break;
            case 'save':

                $message = '';
                $errors = [];
                $response = [];

                $validator = \Validator::make($request->all(), [
                    'file' => 'required',
                ]);

                if ( $validator->fails() ) {
                    $errors = array_merge($errors, $validator->errors()->all());
                }
                elseif ( $request->hasFile('file') ) {
                    config(['excel.import.heading' => 'original']);
                    config(['excel.csv.delimiter' => ($request->get('delimiter') ?: ';')]);

                    $path = $request->file('file')->getRealPath();
                    $data = \Excel::selectSheetsByIndex(0)->load($path, function($reader) use ($request, &$message, $site_id) {
                        $results = $reader->all();
                        $skipped = 0;
                        $imported = 0;
                        $importList = [];

                        if ( !empty($results) && $results->count() ) {
                            foreach ($results as $row ) {
                                $first_name = isset($row["First name"]) ? trim($row["First name"]) : '';
                                $last_name = isset($row["Last name"]) ? trim($row["Last name"]) : '';
                                $name = trim($first_name .' '. $last_name);
                                $mobile_number = isset($row["Phone number"]) ? trim($row["Phone number"]) : '';
                                $email = isset($row["Email"]) ? trim($row["Email"]) : '';
                                $created_date = isset($row["Registration date"]) ? Carbon::parse(trim($row["Registration date"]))->format('Y-m-d') : null;

                                if (empty($name) || empty($email)) { continue; }

                                try {
                                    $customer = \App\Models\Customer::where('email', $email)->where('site_id', $site_id)->first();

                                    if (!$customer) {
                                        $customer = new \App\Models\Customer();
                                        $customer->name = $name;
                                        $customer->created_date = $created_date;
                                        $customer->site_id = $site_id;
                                        $customer->email = $email;
                                        $customer->password = md5($email . time() . rand(10000,100000));
                                        $customer->type = 1;
                                        $customer->ip = '';
                                        $customer->token_password = '';
                                        $customer->token_activation = '';
                                        $customer->description = '';
                                        $customer->is_company = 0;
                                        $customer->activated = 1;
                                        $customer->published = 1;
                                        $customer->save();

                                        $customerProfile = \App\Models\CustomerProfile::find($customer->id);
                                        if (!$customerProfile) {
                                            $customerProfile = new \App\Models\CustomerProfile();
                                            $customerProfile->user_id = $customer->id;
                                            $customerProfile->first_name = $first_name;
                                            $customerProfile->last_name = $last_name;
                                            $customerProfile->mobile_number = $mobile_number;
                                            $customerProfile->telephone_number = '';
                                            $customerProfile->emergency_number = '';
                                            $customerProfile->address = '';
                                            $customerProfile->city = '';
                                            $customerProfile->postcode = '';
                                            $customerProfile->state = '';
                                            $customerProfile->country = '';
                                            $customerProfile->save();
                                        }

                                        $imported++;
                                    }
                                    else {
                                        $skipped++;
                                    }
                                }
                                catch (\Exception $e) {
                                    $skipped++;
                                }
                            }
                            $message = trans('admin/users.message.imported', [
                                'imported' => $imported,
                                'skipped' => $skipped
                            ]);
                        }
                    })->get();

                    // dd($data);
                }

                $response['message'] = $message;

                if ( $errors ) {
                    $response['errors'] = $errors;
                }

                if ( $request->ajax() ) {
                    return $response;
                }
                else {
                    if ( empty($errors) ) {
                        session()->flash('message', $message);
                    }
                    return redirect()->back()->withErrors($errors);
                }

            break;
            default:

                return view('admin.customers.import');

            break;
        }
    }
}
