<?php

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

//--------------------------------------------------------------------------
// Define the absolute paths for Application directories
//--------------------------------------------------------------------------

define('BASEPATH', realpath(__DIR__ .'/../') .DS);

define('WEBPATH', realpath(__DIR__) .DS);

define('APPPATH', BASEPATH .'app' .DS);

//--------------------------------------------------------------------------
// Load the Composer Autoloader
//--------------------------------------------------------------------------

require BASEPATH .'vendor/autoload.php';

//--------------------------------------------------------------------------
// Bootstrap the Framework and get the Application instance
//--------------------------------------------------------------------------

$app = require_once APPPATH .'Bootstrap.php';

//--------------------------------------------------------------------------
// Run the Application
//--------------------------------------------------------------------------

$kernel = $app->make('Mini\Http\Contracts\KernelInterface');

$response = $kernel->handle(
	$request = Mini\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
