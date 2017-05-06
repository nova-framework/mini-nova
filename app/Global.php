<?php

define('ROLE_ADMIN',   1);
define('ROLE_MANAGER', 2);
define('ROLE_REGUSER', 3);


// A sample Middleware.
Route::middleware('test', function($request, Closure $next)
{
    //echo '<pre>' .var_export($request, true) .'</pre>';
    echo '<pre style="margin: 10px;">BEFORE, on the Routing\'s [test] Middleware!</pre>';

    return $next($request);
});
