<?php


// A sample Middleware.
Route::middleware('test', function($request, Closure $next)
{
	//echo '<pre>' .var_export($request, true) .'</pre>';
	echo '<pre style="margin: 10px;">BEFORE, on the Routing\'s [test] Middleware!</pre>';

	return $next($request);
});
