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

// The Language Changer.
$router->get('language/{language}', array('middleware' => 'referer', function($language)
{
	$languages = Config::get('languages');

	if (in_array($language, array_keys($languages))) {
		Session::set('language', $language);

		// Store also the current Language in a Cookie lasting five years.
		Cookie::queue(PREFIX .'language', $language, 2628000);
	}

	return Redirect::back();

}))->where('language', '([a-z]{2})');


// The default Auth Routes.
$router->get( 'auth/login',  array('middleware' => 'guest', 'uses' => 'Authorize@login'));
$router->post('auth/login',  array('middleware' => 'guest', 'uses' => 'Authorize@postLogin'));
$router->post('auth/logout', array('middleware' => 'auth',  'uses' => 'Authorize@logout'));

// The Adminstration Routes.
$router->group(array('prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Admin'), function($router)
{
	// The User's Dashboard
	$router->get('/',			'Dashboard@index');
	$router->get('dashboard',	'Dashboard@index');

	// The User's Profile.
	$router->get( 'profile',	'Profile@index');
	$router->post('profile',	'Profile@update');

	// The User's Messages.
	$router->get( 'messages',					'Messages@index');
	$router->get( 'messages/create', 			'Messages@create');
	$router->post('messages',					'Messages@store');
	$router->get( 'messages/{threadId}',		'Messages@show');
	//$router->post('messages/{postId}/destroy',	'Messages@destroy');

	$router->post('messages/{threadId}',		'Messages@reply');

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
