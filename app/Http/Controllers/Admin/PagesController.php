<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SiteHelper;

class PagesController extends Controller
{
    public function gettingStarted()
    {
        if (!auth()->user()->hasPermission('admin.settings.getting_started.index')) {
            return redirect_no_permission();
        }

        $errors = [];

        if (request('action') == 'update') {

            $admin_default_page = request('admin_default_page');

            if (!empty($admin_default_page) && in_array($admin_default_page, [
                'getting-started',
                'dispatch',
                'bookings-next24',
                'bookings-latest',
                'bookings-unconfirmed',
            ])) {
                $config = \App\Models\Config::ofSite()->whereKey('admin_default_page')->first();

                if ( empty($config->id) ) {
                    $config = new \App\Models\Config;
                    $config->site_id = config('site.site_id');
                    $config->key = 'admin_default_page';
                    $config->type = 'string';
                    $config->browser = 0;
                }

                $config->value = $admin_default_page;
                $config->save();
            }

            return [];
        }
        elseif (request('action') == 'create_customer') {

            try {
                $rand = rand(1000, 10000);
                $url = route('customer.index');
                $email = 'u'. $rand .'.'. config('site.company_email');
                $password = \App\Helpers\SiteHelper::generateRandomString(8, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*?_~');

                $check = \App\Models\Customer::where('email', $email)->first();

                if (!empty($check->id)) {
                    throw new \Exception(trans('admin/pages.getting_started.user_creation_email_taken', [
                        'email' => $email,
                    ]));
                }

                $user = new \App\Models\Customer;
                $user->site_id = config('site.site_id');
                $user->name = 'John Doe';
                $user->email = $email;
                $user->password = md5($password);
                $user->type = 1;
                $user->ip = '';
                $user->token_password = '';
                $user->token_activation = '';
                $user->description = '';
                $user->is_company = 1;
                $user->activated = 1;
                $user->published = 1;
                $user->created_date = \Carbon\Carbon::now();
                $user->save();

                if (!empty($user->id)) {
                    $profile = new \App\Models\CustomerProfile;
                    $profile->user_id = $user->id;
                    $profile->title = '';
                    $profile->first_name = 'John';
                    $profile->last_name = 'Doe';
                    $profile->mobile_number = '+448005550199';
                    $profile->telephone_number = '+448005550199';
                    $profile->emergency_number = '+448005550199';
                    $profile->company_name = 'Company Name';
                    $profile->company_number = '9123456';
                    $profile->company_tax_number = 'GB123456789';
                    $profile->is_account_payment = 1;
                    $profile->address = '25 Primrose Cottage';
                    $profile->city = 'London';
                    $profile->postcode = 'TW5 9AF';
                    $profile->state = 'London';
                    $profile->country = 'United Kingdom';
                    $profile->save();
                }

                session()->flash('message', trans('admin/pages.getting_started.user_creation_success', [
                    'url' => '<a href="'. $url .'" target="_blank">'. $url .'</a>',
                    'email' => $email,
                    'password' => $password,
                ]));
            }
            catch (\Exception $e) {
                if ($e->getMessage()) {
                    $errors[] = $e->getMessage();
                }
                else {
                    $errors[] = trans('admin/pages.getting_started.user_creation_failure');
                }
            }

            return redirect()->route('admin.getting-started')->withErrors($errors);
        }
        elseif (request('action') == 'create_driver') {

            try {
                $rand = rand(1000, 10000);
                $url = route('driver.index');
                $username = 'u'. $rand;
                $email = 'u'. $rand .'.'. config('site.company_email');
                $password = \App\Helpers\SiteHelper::generateRandomString(8, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*?_~');

                $check = \App\Models\User::where('email', $email)->first();

                if (!empty($check->id)) {
                    throw new \Exception(trans('admin/pages.getting_started.user_creation_email_taken', [
                        'email' => $email,
                    ]));
                }

                $user = new \App\Models\User;
                $user->name = 'John Doe';
                $user->username = $username;
                $user->email = $email;
                $user->password = bcrypt($password);
                $user->status = 'approved';
                $user->save();

                $user->attachRoleSlug('driver.root');

                if (!empty($user->id)) {
                    $profile = new \App\Models\UserProfile;
                    $profile->user_id = $user->id;
                    $profile->title = '';
                    $profile->first_name = 'John';
                    $profile->last_name = 'Doe';
                    $profile->date_of_birth = '';
                    $profile->mobile_no = '+448005550199';
                    $profile->telephone_no = '+448005550199';
                    $profile->emergency_no = '+448005550199';
                    $profile->address = '25 Primrose Cottage';
                    $profile->city = 'London';
                    $profile->postcode = 'TW5 9AF';
                    $profile->state = 'London';
                    $profile->country = 'United Kingdom';
                    $profile->profile_type = 'private';
                    $profile->company_name = 'Company Name';
                    $profile->company_number = '9123456';
                    $profile->company_tax_number = 'GB123456789';
                    $profile->national_insurance_no = '';
                    $profile->bank_account = '';
                    $profile->unique_id = 'D'. $rand;
                    $profile->commission = 20.00;
                    $profile->availability = '[]';
                    $profile->availability_status = 1;
                    $profile->insurance = '';
                    $profile->insurance_expiry_date = null;
                    $profile->driving_licence = '';
                    $profile->driving_licence_expiry_date = null;
                    $profile->pco_licence = '';
                    $profile->pco_licence_expiry_date = null;
                    $profile->phv_licence = '';
                    $profile->phv_licence_expiry_date = null;
                    $profile->save();
                }

                session()->flash('message', trans('admin/pages.getting_started.user_creation_success', [
                    'url' => '<a href="'. $url .'" target="_blank">'. $url .'</a>',
                    'email' => $email,
                    'password' => $password,
                ]));
            }
            catch (\Exception $e) {
                if ($e->getMessage()) {
                    $errors[] = $e->getMessage();
                }
                else {
                    $errors[] = trans('admin/pages.getting_started.user_creation_failure');
                }
            }

            return redirect()->route('admin.getting-started')->withErrors($errors);
        }

        return view('admin.pages.getting_started');
    }

    public function webWidget()
    {
        if (!auth()->user()->hasPermission('admin.web_widget.index')) {
            return redirect_no_permission();
        }

        return view('admin.pages.web_widget');
    }

    public function mobileApp()
    {
        if (!auth()->user()->hasPermission('admin.mobile_app')) {
            return redirect_no_permission();
        }

        return view('admin.pages.mobile_app');
    }

    public function license()
    {
        try {
            $filePath = base_path('LICENSE.txt');
            $license = \File::exists($filePath) ? \File::get($filePath) : '';
            $license = SiteHelper::nl2br2($license);
            $license = str_replace('** ', '<b>** ', $license);
            $license = str_replace(' **', ' **</b>', $license);
        }
        catch (Illuminate\Contracts\Filesystem\FileNotFoundException $exception) {
            $license = trans('admin/pages.license.no_licence');
        }

        return view('admin.pages.license', compact('license'));
    }
}
