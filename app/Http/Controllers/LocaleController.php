<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\SiteHelper;

class LocaleController extends Controller
{
    public static function change($locale, $type = '')
    {
        $locale = self::check($locale);

        if ($locale) {
            session(['locale' => $locale]);
        }

        if ($type == 'status') {
            return $locale ? true : false;
        }

        $url = url()->previous();
        $url = SiteHelper::removeParam($url, 'locale');
        $url = SiteHelper::removeParam($url, 'lang');

        return redirect()->to($url);
    }

    public static function check($locale)
    {
        if (!empty($locale)) {
            $locale = str_replace('_', '-', $locale);

            if (array_key_exists($locale, config('app.locales'))) {
                return $locale;
            }
            else {
                $locale = explode('-', $locale)[0];

                if (array_key_exists($locale, config('app.locales'))) {
                    return $locale;
                }
            }
        }

        return '';
    }
}
