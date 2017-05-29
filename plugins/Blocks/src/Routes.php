<?php

/*
|--------------------------------------------------------------------------
| Plugin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the Routes for the Plugin.
|
*/


// The Adminstration Routes.
$router->group(array('prefix' => 'admin', 'middleware' => 'auth', 'namespace' => 'Admin'), function($router)
{
	// The Blocks CRUD.
	$router->get( 'blocks', 				'Blocks@index');
	/*
	$router->get( 'blocks/create',			'Blocks@create');
	$router->post('blocks', 				'Blocks@store');
	$router->get( 'blocks/{id}/edit',		'Blocks@edit');
	$router->post('blocks/{id}',			'Blocks@update');
	$router->post('blocks/{id}/destroy',	'Blocks@destroy');
	*/
});
