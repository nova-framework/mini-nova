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

// The default Auth Routes.
$router->get( 'auth/login',  array('middleware' => 'guest', 'uses' => 'Authorize@login'));
$router->post('auth/login',  array('middleware' => 'guest', 'uses' => 'Authorize@postLogin'));
$router->post('auth/logout', array('middleware' => 'auth',  'uses' => 'Authorize@logout'));

// The Adminstration Routes.
$router->group(array('prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Admin'), function($router)
{
	// The User's Dashboard
	$router->get('/',					'Dashboard@index');
	$router->get('dashboard',			'Dashboard@index');

	// The User's Profile.
	$router->get( 'profile',			'Profile@index');
	$router->post('profile',			'Profile@update');

	// Server Side Processor for Users DataTable.
	$router->post('users/data',			'Users@data');

	// The Users CRUD.
	$router->get( 'users',				'Users@index');
	$router->get( 'users/create',		'Users@create');
	$router->post('users',				'Users@store');
	$router->get( 'users/{id}',			'Users@show');
	$router->get( 'users/{id}/edit',	'Users@edit');
	$router->post('users/{id}',			'Users@update');
	$router->post('users/{id}/destroy',	'Users@destroy');


	// Server Side Processor for Roles DataTable.
	$router->post('roles/data', 		'Roles@data');

	// The Roles CRUD.
	$router->get( 'roles',				'Roles@index');
	$router->get( 'roles/create',		'Roles@create');
	$router->post('roles',				'Roles@store');
	$router->get( 'roles/{id}',			'Roles@show');
	$router->get( 'roles/{id}/edit',	'Roles@edit');
	$router->post('roles/{id}',			'Roles@update');
	$router->post('roles/{id}/destroy',	'Roles@destroy');
});

$router->group(array('prefix' => 'sample'), function ($router)
{
	$router->get('/', 'Sample@index');

	$router->get('{name}/{slug?}', array('middleware' => 'test', 'prefix' => 'test', 'uses' => 'Sample@index'));

	$router->get('routes',		'Sample@routes');
	$router->get('session',		'Sample@session');
	$router->get('redirect',	'Sample@redirect');
	$router->get('pagination',	'Sample@pagination');
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
