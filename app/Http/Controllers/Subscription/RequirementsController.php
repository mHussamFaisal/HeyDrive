<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;

class RequirementsController extends Controller
{
    public $required;

    public function __construct()
    {
        $this->required = config('eto_requirements');
    }

    public function etoCompare($name, $version1, $version2, $version3 = null) {
        if (version_compare($version1, $version2, '<=')) {
            $validation[] = sprintf("The required %s version is %s, Your current version %s is too low", $name, $version2, $version1);
        }
        if ($version3 !== null && version_compare($version1, $version3, '>=')) {
            $validation[] = sprintf("The maximum version of s% (%s) is less than yours (%s)", $name, $version1, $version3);
        }
        if (isset($validation)) {
            return implode('<br>', $validation);
        }
        return true;
    }

    public function etoExtension($extension) {
        if (!extension_loaded($extension)) {
            return "The \"$extension\" extension is disabled in the server configuration, it must be enabled for proper ststa operation.";
        }
        return true;
    }

    public function etoClass($class) {
        if (!class_exists($class)) {
            return "Your PHP does not contain \"$class\", it is required for some functionalities to work properly.";
        }
        return true;
    }

    public function etoIni($ini) {
        if (!ini_get($ini)) {
            return "Your server has the \"$ini\" option turned off, without this option we will not be able to perform operations on files such as creating security logs.";
        }
        return true;
    }

    public function etoFunction($function) {
        if (!function_exists($function)) {
            return "Your PHP does not contain \"$function\", it is required for some functionalities to work properly.";
        }
        return true;
    }

    public function etoDisableFunctions() {
        if (ini_get('disable_functions')) {
            return explode(',', ini_get('disable_functions'));
        }
        return true;
    }
}
