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
$router->group(array('prefix' => 'admin', 'namespace' => 'Admin'), function($router)
{
	// The User's Dashboard
	$router->get('dashboard',				array('middleware' => 'auth', 'uses' => 'Dashboard@index'));

	// The User's Profile.
	//$router->get( 'profile',				array('middleware' => 'auth', 'uses' => 'Profile@index'));
	//$router->post('profile',				array('middleware' => 'auth', 'uses' => 'Profile@update'));

	// Server Side Processor for Users DataTable.
	$router->post('users/data',				array('middleware' => 'auth', 'uses' => 'Users@data'));

	// The Users CRUD.
	$router->get( 'users',					array('middleware' => 'auth', 'uses' => 'Users@index'));
	$router->get( 'users/create',			array('middleware' => 'auth', 'uses' => 'Users@create'));
	$router->post('users',					array('middleware' => 'auth', 'uses' => 'Users@store'));
	$router->get( 'users/{id}',				array('middleware' => 'auth', 'uses' => 'Users@show'));
	$router->get( 'users/{id}/edit',		array('middleware' => 'auth', 'uses' => 'Users@edit'));
	$router->post('users/{id}',				array('middleware' => 'auth', 'uses' => 'Users@update'));
	$router->post('users/{id}/destroy',		array('middleware' => 'auth', 'uses' => 'Users@destroy'));


	// Server Side Processor for Roles DataTable.
	$router->post('roles/data', 			array('middleware' => 'auth', 'uses' => 'Roles@data'));

	// The Roles CRUD.
	$router->get( 'roles',					array('middleware' => 'auth', 'uses' => 'Roles@index'));
	$router->get( 'roles/create',			array('middleware' => 'auth', 'uses' => 'Roles@create'));
	$router->post('roles',					array('middleware' => 'auth', 'uses' => 'Roles@store'));
	$router->get( 'roles/{id}',				array('middleware' => 'auth', 'uses' => 'Roles@show'));
	$router->get( 'roles/{id}/edit',		array('middleware' => 'auth', 'uses' => 'Roles@edit'));
	$router->post('roles/{id}',				array('middleware' => 'auth', 'uses' => 'Roles@update'));
	$router->post('roles/{id}/destroy',		array('middleware' => 'auth', 'uses' => 'Roles@destroy'));
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
