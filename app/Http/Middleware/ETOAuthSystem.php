<?php

namespace App\Http\Middleware;

use Closure;

class ETOAuthSystem
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role = '')
    {
        if (empty($request->system_token)
            || ((string) $request->system_token !== (string) eto_config('AUTH_ETO_API_SYSTEM_TOKEN', null))
        ) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Not Found.'], 404);
            } else {
                abort(404);
            }
        }

        return $next($request);
    }
}
