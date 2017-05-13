<?php

namespace App\Middleware;

use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Config;
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
		$guard = $guard ?: Config::get('auth.defaults.guard', 'web');

		if (Auth::guard($guard)->check()) {
			$uri = Config::get("auth.guards.{$guard}.paths.dashboard", 'admin/dashboard');

			return Redirect::to($uri);
		}

		return $next($request);
	}
}
