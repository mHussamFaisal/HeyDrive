<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Setting;
use Image;
use Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('driver.account.index', compact('user'));
    }

    public function edit()
    {
        if (!config('site.driver_show_edit_profile_button')) {
            return redirect()->route('driver.account.index');
        }

        $timezoneList = \App\Helpers\SiteHelper::getTimezoneList('group');
        $user = auth()->user();
        $profileTypes = $user->profile->profileTypeOptions;

        $locales = [];
        foreach (config('app.locales') as $code => $locale) {
            $locales[$code] = $locale['name'] . ' ('. $locale['native'] .')';
        }

        return view('driver.account.edit', compact('user', 'profileTypes', 'locales', 'timezoneList'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $tnUser = (new \App\Models\User)->getTable();

        $rules = [
            'name' => 'required|max:255',
            'username' => [
                'required',
                'max:255',
                Rule::unique($tnUser)->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique($tnUser)->ignore($user->id),
            ],
            'password' => 'min:6|confirmed',
            'avatar' => 'mimes:jpg,jpeg,gif,png'
        ];

        $rules = array_merge($rules, [
            'profile.first_name' => 'required|max:255',
            'profile.last_name' => 'required|max:255',
            'profile.date_of_birth' => 'date',
            'profile.insurance_expiry_date' => 'date',
            'profile.driving_licence_expiry_date' => 'date',
            'profile.pco_licence_expiry_date' => 'date',
            'profile.phv_licence_expiry_date' => 'date',
        ]);

        $messages = [];

        $attributeNames = [
            'profile.title' => 'profile title',
            'profile.first_name' => 'profile first name',
            'profile.last_name' => 'profile last name',
        ];

        $validator = $this->validate($request, $rules, $messages, $attributeNames);

        // $user->name = trim($request->get('first_name') .' '. $request->get('last_name'));
        $user->name = $request->get('name');
        // $user->username = $request->get('username');
        $user->email = $request->get('email');
        $user->updated_at = Carbon::now();

        if ( $request->get('password') ) {
            $user->password = bcrypt($request->get('password'));
        }

        if ( $request->hasFile('avatar') || $request->get('avatar_delete') ) {
            if ( Storage::disk('avatars')->exists($user->avatar) ) {
                Storage::disk('avatars')->delete($user->avatar);
                $user->avatar = null;
            }
        }

      	if ( $request->hasFile('avatar') ) {
        		$file = $request->file('avatar');
            $filename = \App\Helpers\SiteHelper::generateFilename('avatar') .'.'. $file->getClientOriginalExtension();

            $img = Image::make($file);

            if ($img->width() > config('site.image_dimensions.avatar.width')) {
                $img->resize(config('site.image_dimensions.avatar.width'), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($img->height() > config('site.image_dimensions.avatar.height')) {
                $img->resize(null, config('site.image_dimensions.avatar.height'), function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $img->save(asset_path('uploads','avatars/'. $filename));

        		$user->avatar = $filename;
      	}

        $user->save();

        // Profile
        if ( $request->get('profile') && $user->id ) {
            $p = (object)$request->get('profile');

            $profile = UserProfile::where('user_id', $user->id)->first();

            if ( is_null($profile) ) {
                $profile = new UserProfile;
            }

            $profile->user_id = $user->id;
            $profile->title = $p->title;
            $profile->first_name = $p->first_name;
            $profile->last_name = $p->last_name;
            $profile->date_of_birth = $p->date_of_birth;
            $profile->mobile_no = $p->mobile_no;
            $profile->telephone_no = $p->telephone_no;
            $profile->emergency_no = $p->emergency_no;
            $profile->address = $p->address;
            $profile->city = $p->city;
            $profile->postcode = $p->postcode;
            $profile->state = $p->state;
            $profile->country = $p->country;
            $profile->profile_type = $p->profile_type;

            if ( $p->profile_type == 'company' ) {
                $profile->company_name = $p->company_name;
                $profile->company_number = $p->company_number;
                $profile->company_tax_number = $p->company_tax_number;
            }
            else {
                $profile->company_name = null;
                $profile->company_number = null;
                $profile->company_tax_number = null;
            }

            $profile->national_insurance_no = $p->national_insurance_no;
            $profile->bank_account = $p->bank_account;
            $profile->insurance = $p->insurance;
            $profile->insurance_expiry_date = $p->insurance_expiry_date;
            $profile->driving_licence = $p->driving_licence;
            $profile->driving_licence_expiry_date = $p->driving_licence_expiry_date;
            $profile->pco_licence = $p->pco_licence;
            $profile->pco_licence_expiry_date = $p->pco_licence_expiry_date;
            $profile->phv_licence = $p->phv_licence;
            $profile->phv_licence_expiry_date = $p->phv_licence_expiry_date;
            $profile->updated_at = Carbon::now();
            $profile->save();
        }

        // Settings
        if ( $request->get('settings') && $user->id ) {
            $settings = (object)$request->get('settings');

            if (isset($settings->locale)) {
                settings_save('app.locale', $settings->locale, 'user', $user->id);
            }

            if (isset($settings->timezone)) {
                settings_save('app.timezone', $settings->timezone, 'user', $user->id, true);
            }
        }

        \Cache::store('file')->forget('driver_check_user_documents_'. $user->id);

        session()->flash('message', trans('driver/account.message.update_success'));
        return redirect()->back();
    }
}
