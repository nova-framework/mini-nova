<?php

namespace Mini\Auth\Middleware;

use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\Response;
use Mini\Support\Facades\Redirect;

use Closure;


class Authenticate
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
		$guard = $guard ?: Config::get('auth.default', 'web');

		if (Auth::guard($guard)->guest()) {
			if ($request->ajax() || $request->wantsJson()) {
				return Response::make('Unauthorized.', 401);
			}

			$uri = Config::get("auth.guards.{$guard}.paths.authorize", 'auth/login');

			return Redirect::guest($uri);
		}

		return $next($request);
	}
}
