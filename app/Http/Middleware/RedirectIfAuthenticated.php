<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if (Auth::guard($guard)->user()->hasRole('admin.*')) {
                if (config('site.allow_dispatch')) {
                    return redirect()->route('dispatch.index');
                }
                else {
                    return redirect()->route('admin.index');
                }
            }
            elseif (Auth::guard($guard)->user()->hasRole('driver.*')) {
                return redirect()->route('driver.index');
            }
             elseif (Auth::guard($guard)->user()->hasRole('supplier.*')) {
                return redirect()->route('supplier.index');
            }
            else {
                return redirect('/');
            }
        }

        return $next($request);
    }
}
