<?php

use Mini\View\View;

//
// General patterns for the route parameters.

$router->pattern('slug', '(.*)');

//
// The routes definition.

$router->any('/', function()
{
    $view = View::make('Default')
        ->shares('title', 'Mini-me!')
        ->with('content', 'Yep! It works.');

    return View::make('Layouts/Default')->with('content', $view);
});

$router->get('sample/{name?}/{slug?}', 'App\Controllers\Sample@index');

$router->post('sample', 'App\Controllers\Sample@store');


$router->get('test/{id}/{name?}/{slug?}', array(function ($id, $name = null, $slug = null)
{
    return array('id' => $id, 'name' => $name, 'slug' => $slug);

}, 'where' => array('id' => '([0-9]+)')));

// A Catch-All route.
$router->any('{slug}', function($slug)
{
    $view = View::make('Default')
        ->shares('title', 'Catch-All Route')
        ->with('content', $slug);

    return View::make('Layouts/Default')->with('content', $view);
});
