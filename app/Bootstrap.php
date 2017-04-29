<?php

use Mini\Container\Container;
use Mini\Helpers\Profiler;
use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Router;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Create the Container instance.
$app = new Container();

$app['app'] = $app;

// Create the Router instance.
$router = new Router();

$app['router'] = $router;

// Load the Events.
require APPPATH .'Events.php';

// Load the Routes.
require APPPATH .'Routes.php';

// Create the Request instance.
$request = Request::createFromGlobals();

$app['request'] = $request;

// Dispatch the Request instance via Router.
try {
    $response = $router->dispatch($request);
}
catch (NotFoundHttpException $e) {
    $response = new Response('Page not found', 404);
}

// Insert the Profiler report into response content.
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
