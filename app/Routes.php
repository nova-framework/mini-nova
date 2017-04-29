<?php

use Mini\View\View;

// Sample Middleware.
$router->middleware('test', function($request, Closure $next)
{
    //echo '<pre>' .var_export($request, true) .'</pre>';
    echo '<pre style="margin: 10px;">Hello from the Routing Middleware!</pre>';

    return $next($request);
});

//
// General patterns for the route parameters.

$router->pattern('slug', '.*');

//
// The routes definition.

$router->any('/', function()
{
    $view = View::make('Default')
        ->shares('title', 'Mini-me!')
        ->with('content', 'Yep! It works.');

    return View::make('Layouts/Default')->with('content', $view);
});

$router->get('sample/{name}/{slug?}', array('middleware' => 'test', 'uses' => 'App\Controllers\Sample@index'));

$router->post('sample', 'App\Controllers\Sample@store');


$router->get('test/{id}/{name?}/{slug?}', array(function ($id, $name = null, $slug = null)
{
    return array('id' => $id, 'name' => $name, 'slug' => $slug);

}, 'where' => array('id' => '[0-9]+')));

// A Catch-All route.
$router->any('{slug}', function($slug)
{
    $view = View::make('Default')
        ->shares('title', 'Catch-All Route')
        ->with('content', $slug);

    return View::make('Layouts/Default')->with('content', $view);
});
