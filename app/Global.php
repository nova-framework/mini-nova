<?php

/** Define Route Middleware. */

/**
 * Permit the access only to Administrators.
 */
Route::middleware('admin', function($request, Closure $next, $guard = null)
{
    $guard = $guard ?: Config::get('auth.default', 'web');

    $user = Auth::guard($guard)->user();

    if (! is_null($user) && ! $user->hasRole('administrator')) {
        if ($request->ajax() || $request->wantsJson()) {
            // On an AJAX Request; just return Error 403 (Access denied)
            return Response::make('Forbidden', 403);
        }

        $uri = Config::get("auth.guards.{$guard}.paths.dashboard", 'admin/dashboard');

        $status = __('You are not authorized to access this resource.');

        return Redirect::to($uri)->with('warning', $status);
    }

    return $next($request);
});

/**
 * Role-based Authorization Middleware.
 */
Route::middleware('role', function($request, Closure $next, $role)
{
    $roles = array_slice(func_get_args(), 2);

    //
    $guard = Config::get('auth.default', 'web');

    $user = Auth::guard($guard)->user();

    if (! is_null($user) && ! $user->hasRole($roles)) {
        $uri = Config::get("auth.guards.{$guard}.paths.dashboard", 'admin/dashboard');

        $status = __('You are not authorized to access this resource.');

        return Redirect::to($uri)->with('warning', $status);
    }

    return $next($request);
});

/**
 * Request's Referer Middleware.
*/
Route::middleware('referer', function($request, Closure $next)
{
	$referrer = $request->header('referer');

	if (! Str::startsWith($referrer, Config::get('app.url'))) {
		return Redirect::back();
	}

	return $next($request);
});


// Add a Listener Closure to the Event 'router.matched'.
Event::listen('router.matched', function($route, $request)
{
	// Share the Views the current URI.
	View::share('currentUri', $request->path());

	// Share the Views the Backend's base URI.
	$path = '';

	$segments = $request->segments();

	if(! empty($segments)) {
		// Make the path equal with the first part if it exists, i.e. 'admin'
		$path = array_shift($segments);

		$segment = ! empty($segments) ? array_shift($segments) : '';

		if (($path == 'admin') && empty($segment)) {
			$path = 'admin/dashboard';
		} else if (! empty($segment)) {
			$path .= '/' .$segment;
		}
	}

	View::share('baseUri', $path);
});
