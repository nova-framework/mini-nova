<?php

namespace App\Http\Middleware;

use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Redirect;

use Closure;


class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Mini\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return Redirect::to('admin/dashboard');
        }

        return $next($request);
    }
}
