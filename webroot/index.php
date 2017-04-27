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
// Run the Application
//--------------------------------------------------------------------------

use Mini\Http\Request;
use Mini\Routing\Router;


// Create the Router instance.
$router = new Router();

require APPPATH .'Routes.php';

// Create the Request instance.
$request = Request::createFromGlobals();

// Dispatch the Request instance via Router.
$response = $router->dispatch($request);

// Send the Response.
$response->send();
