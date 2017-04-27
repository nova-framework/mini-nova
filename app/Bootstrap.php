<?php

use Mini\Helpers\Profiler;
use Mini\Http\Request;
use Mini\Http\Response;
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

if ($response instanceof Response) {
    $requestTime = $request->server('REQUEST_TIME_FLOAT');

    $content = str_replace('<!-- DO NOT DELETE! - Profiler -->',
        Profiler::getReport($requestTime),
        $response->getContent()
    );

    $response->setContent($content);
}

// Send the Response.
$response->send();
