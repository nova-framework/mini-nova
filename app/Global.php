<?php

// Route Middleware for checking the Request's referrer.
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
