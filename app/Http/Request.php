<?php
namespace App\Http;

use \Illuminate\Http\Request as Base;

/**
 * Custom Request class for proper ssl detection
 *
 * @author Justin van Schaick <me@domain.nl>
 */
class Request extends Base {
    /**
     * Explained on http://stackoverflow.com/questions/1175096/how-to-find-out-if-youre-using-https-without-serverhttps
     * http://www.code-examples.com/laravel-5-ssl-routes-behind-a-load-balancer/
     * @return boolean
     */
    public function isSecure() {
        $isSecure = parent::isSecure();

        if ($isSecure) {
            return true;
        }

        return is_ssl();
    }
}
