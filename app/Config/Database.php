<?php
/**
 * Database Configuration.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */


return array(

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may desire to retrieve records in an
	| array format for simplicity. Here you can tweak the fetch style.
	|
	*/

	'fetch' => PDO::FETCH_CLASS,

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => 'primary',

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Nova is shown below to make development simple.
	|
	|
	| All database work in Nova is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => array(
		'primary' => array(
			'host'	  => 'localhost',
			'database'  => 'mininova',
			'username'  => 'mininova',
			'password'  => 'password',
			'prefix'	=> PREFIX,
			'charset'   => 'utf8',
			'collation' => 'utf8_general_ci',
		),
	),

);
