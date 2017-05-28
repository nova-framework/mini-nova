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
	$router->get( 'taxonomy', 				'Taxonomy@index');
	$router->get( 'taxonomy/create',		'Taxonomy@create');
	$router->post('taxonomy', 				'Taxonomy@store');
	$router->get( 'taxonomy/{id}/edit',		'Taxonomy@edit');
	$router->post('taxonomy/{id}',			'Taxonomy@update');
	$router->post('taxonomy/{id}/destroy',	'Taxonomy@destroy');

	// Order the Terms from a Vocabulary.
	$router->post('taxonomy/{id}/order-terms', 'Taxonomy@orderTerms');

	// The Terms CRUD.
	$router->get( 'taxonomy/{vid}/terms',				'Terms@index');
	$router->get( 'taxonomy/{vid}/terms/create',		'Terms@create');
	$router->post('taxonomy/{vid}/terms', 				'Terms@store');
	$router->get( 'taxonomy/{vid}/terms/{id}/edit',		'Terms@edit');
	$router->post('taxonomy/{vid}/terms/{id}',			'Terms@update');
	$router->post('taxonomy/{vid}/terms/{id}/destroy',	'Terms@destroy');
});
