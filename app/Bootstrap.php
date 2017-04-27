<?php

use Mini\Http\Request;
use Mini\Routing\Router;


// Create the Router instance.
$router = new Router();

// Load the Events.
require APPPATH .'Events.php';

// Load the Routes.
require APPPATH .'Routes.php';

// Create the Request instance.
$request = Request::createFromGlobals();

// Dispatch the Request instance via Router.
$response = $router->dispatch($request);

// Send the Response.
$response->send();
