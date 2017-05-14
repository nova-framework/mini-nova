<?php

//--------------------------------------------------------------------------
// Load The Options
//--------------------------------------------------------------------------

use App\Models\Option;

if (CONFIG_STORE === 'database') {
	// Retrieve the Option items, caching them for 24 hours.
	$options = Cache::remember('system_options', 1440, function()
	{
		return Option::all();
	});

	foreach ($options as $option) {
		$key = $option->group;

		if (! empty($option->item)) {
			$key .= '.' .$option->item;
		}

		Config::set($key, $option->value);
	}
} else if(CONFIG_STORE !== 'files') {
	throw new InvalidArgumentException('Invalid Config Store type.');
}

//--------------------------------------------------------------------------
// Additional Middlewares And Event Listeners
//--------------------------------------------------------------------------

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
