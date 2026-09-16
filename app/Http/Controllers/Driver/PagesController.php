<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Helpers\SiteHelper;

class PagesController extends Controller
{
    public function license()
    {
        try {
            $license = \File::get('LICENSE.txt');
            $license = SiteHelper::nl2br2($license);
            $license = str_replace('** ', '<b>** ', $license);
            $license = str_replace(' **', ' **</b>', $license);
        }
        catch (Illuminate\Contracts\Filesystem\FileNotFoundException $exception) {
            $license = trans('driver/pages.license.no_licence');
        }

        return view('driver.pages.license', compact('license'));
    }

    public function mobileApp()
    {
        return view('driver.pages.mobile_app');
    }
}
