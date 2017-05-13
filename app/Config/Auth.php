<?php
/**
 * Auth configuration
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 4.0
 */


return array(

	/*
	|--------------------------------------------------------------------------
	| Authentication Defaults
	|--------------------------------------------------------------------------
	|
	| The default authentication "guard" used by your application.
	|
	*/

	'default' => 'web',

	/*
	|--------------------------------------------------------------------------
	| Authentication Guards
	|--------------------------------------------------------------------------
	|
	| There you may define every authentication guard for your application.
	|
	| Supported: "session", "token"
	|
	*/

	'guards' => array(
		'web' => array(
			'driver' => 'session',
			'model'  => 'App\Models\User',
		),
		'api' => array(
			'driver' => 'token',
			'table'  => 'users',
		),
	),

);
