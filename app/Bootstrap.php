<?php

use Mini\Http\Request;

/**
 * Request's Referer Middleware.
 */
Route::middleware('referer', function(Request $request, Closure $next)
{
	$referrer = $request->header('referer');

	if (! Str::startsWith($referrer, Config::get('app.url'))) {
		return Redirect::back();
	}

	return $next($request);
});
