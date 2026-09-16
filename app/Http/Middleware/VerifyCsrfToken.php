<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'etov2',
        'mobile/*',
        'booking/notify/*',
        'install/*',
        'auto-update',
        'auto-update/*',
    ];
}
