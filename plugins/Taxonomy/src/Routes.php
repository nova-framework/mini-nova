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
	// The Vocabularies CRUD.
	$router->get( 'taxonomy', 				'Vocabularies@index');
	$router->get( 'taxonomy/create',		'Vocabularies@create');
	$router->post('taxonomy', 				'Vocabularies@store');
	$router->get( 'taxonomy/{id}/edit',		'Vocabularies@edit');
	$router->post('taxonomy/{id}',			'Vocabularies@update');
	$router->post('taxonomy/{id}/destroy',	'Vocabularies@destroy');

	// Order the Terms from a Vocabulary.
	$router->post('taxonomy/{vid}/terms/order', 'Terms@orderTerms');

	// The Terms CRUD.
	$router->get( 'taxonomy/{vid}/terms',				'Terms@index');
	$router->get( 'taxonomy/{vid}/terms/create',		'Terms@create');
	$router->post('taxonomy/{vid}/terms', 				'Terms@store');
	$router->get( 'taxonomy/{vid}/terms/{id}/edit',		'Terms@edit');
	$router->post('taxonomy/{vid}/terms/{id}',			'Terms@update');
	$router->post('taxonomy/{vid}/terms/{id}/destroy',	'Terms@destroy');
});
