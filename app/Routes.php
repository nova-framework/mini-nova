<?php

//
// General patterns for the route parameters.

$router->pattern('slug', '.*');

//
// The routes definition.

$router->any('/', function()
{
    $view = View::make('Default')
        ->shares('title', 'Welcome')
        ->with('content', 'Yep! It works.');

    return View::make('Layouts/Default')->with('content', $view);
});

$router->group(array('prefix' => 'sample'), function ($router)
{
    $router->get('/', 'Sample@index');

    $router->get('{name}/{slug?}', array('middleware' => 'test', 'prefix' => 'test', 'uses' => 'Sample@index'));

    $router->get('routes',     'Sample@routes');
    $router->get('session',    'Sample@session');
    $router->get('redirect',   'Sample@redirect');
    $router->get('pagination', 'Sample@pagination');
});

$router->post('sample', 'Sample@store');


$router->get('test/{id}/{name?}/{slug?}', array(function ($id, $name = null, $slug = null)
{
    return array('id' => $id, 'name' => $name, 'slug' => $slug);

}, 'where' => array('id' => '[0-9]+')));



$router->group(array('prefix' => 'admin', 'namespace' => 'Admin'), function ($router)
{
    $router->get('users', 'Users@index');
});

/*
// A Catch-All route.
$router->any('{slug}', function($slug)
{
    $view = View::make('Default')
        ->shares('title', 'Catch-All Route')
        ->with('content', $slug);

    return View::make('Layouts/Default')->with('content', $view);
});
*/
