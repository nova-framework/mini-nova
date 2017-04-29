<?php

use Mini\Container\Container;
use Mini\Helpers\Profiler;
use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Router;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Closure;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

// Create the Container instance.
$app = new Container();

$app['app'] = $app;

// Create the Router instance.
$router = new Router($app);

$app['router'] = $router;

// Sample Middleware.
$router->middleware('test', function($request, Closure $next)
{
    //echo '<pre>' .var_export($request, true) .'</pre>';
    echo '<pre style="margin: 10px;">Hello from the Routing Middleware!</pre>';

    return $next($request);
});

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
