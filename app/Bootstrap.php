<?php

use Mini\Config\Repository as ConfigRepository;
use Mini\Foundation\AliasLoader;
use Mini\Foundation\Application;
use Mini\Helpers\Profiler;
use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Router;
use Mini\Support\Facades\Facade;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Closure;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

//--------------------------------------------------------------------------
// Set The System Path
//--------------------------------------------------------------------------

define('SYSPATH', BASEPATH .'system');

//--------------------------------------------------------------------------
// Set The Storage Path
//--------------------------------------------------------------------------

defined('STORAGE_PATH') || define('STORAGE_PATH', BASEPATH .'storage' .DS);

//--------------------------------------------------------------------------
// Create New Application
//--------------------------------------------------------------------------

$app = new Application();

//--------------------------------------------------------------------------
// Bind Paths
//--------------------------------------------------------------------------

$paths = array(
    'base'    => BASEPATH,
    'app'     => APPPATH,
    'public'  => WEBPATH,
    'storage' => STORAGE_PATH,
);

$app->bindInstallPaths($paths);

//--------------------------------------------------------------------------
// Bind The Application In The Container
//--------------------------------------------------------------------------

$app->instance('app', $app);

//--------------------------------------------------------------------------
// Register The Exception Handler
//--------------------------------------------------------------------------

$app->singleton(
    'Mini\Foundation\Contracts\ExceptionHandlerInterface',
    'App\Exceptions\Handler'
);

//--------------------------------------------------------------------------
// Load The Framework Facades
//--------------------------------------------------------------------------

Facade::setFacadeApplication($app);

//--------------------------------------------------------------------------
// Register The Config Manager
//--------------------------------------------------------------------------

$app->instance('config', $config = new ConfigRepository(
    $app->getConfigLoader()
));

//--------------------------------------------------------------------------
// Set The Default Timezone From Configuration
//--------------------------------------------------------------------------

$config = $app['config']['app'];

date_default_timezone_set($config['timezone']);

//--------------------------------------------------------------------------
// Register The Alias Loader
//--------------------------------------------------------------------------

$aliases = $config['aliases'];

AliasLoader::getInstance($aliases)->register();

//--------------------------------------------------------------------------
// Register The Core Service Providers
//--------------------------------------------------------------------------

$app->getProviderRepository()->load($app, $config['providers']);

//--------------------------------------------------------------------------
// Application Error Logger
//--------------------------------------------------------------------------

Log::useFiles(STORAGE_PATH .'logs' .DS .'error.log');

//--------------------------------------------------------------------------
// Load The Application Start Script
//--------------------------------------------------------------------------

$app->booted( function() use ($app)
{
    // Get the Router instance.
    $router = $app['router'];

    // Load the Events.
    require APPPATH .'Events.php';

    // Load the Routes.
    require APPPATH .'Routes.php';

    /*
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
    */
});

//--------------------------------------------------------------------------
// Run The Application
//--------------------------------------------------------------------------

$app->run();

