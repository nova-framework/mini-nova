<?php

// Sample Middleware.
$router->middleware('test', function($request, Closure $next)
{
    //echo '<pre>' .var_export($request, true) .'</pre>';
    echo '<pre style="margin: 10px;">Hello from the Routing Middleware!</pre>';

    return $next($request);
});
