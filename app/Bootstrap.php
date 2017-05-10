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


//--------------------------------------------------------------------------
// Set The Framework Starting Time
//--------------------------------------------------------------------------

define('FRAMEWORK_START', microtime(true));

//--------------------------------------------------------------------------
// Set PHP Error Reporting Options
//--------------------------------------------------------------------------

error_reporting(-1);

//--------------------------------------------------------------------------
// Set The System Path
//--------------------------------------------------------------------------

define('SYSPATH', BASEPATH .'system');

//--------------------------------------------------------------------------
// Load The Global Configuration
//--------------------------------------------------------------------------

require APPPATH .'Config.php';

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
// Bind Important Interfaces
//--------------------------------------------------------------------------

$app->singleton(
    'Mini\Http\Contracts\KernelInterface',
    'App\Kernel'
);

$app->singleton(
    'Mini\Foundation\Contracts\ExceptionHandlerInterface',
    'App\Exceptions\Handler'
);

//--------------------------------------------------------------------------
// Load The Framework Facades
//--------------------------------------------------------------------------

Facade::clearResolvedInstances();

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
// Load The Application Start Script
//--------------------------------------------------------------------------

$app->booted( function() use ($app)
{

//--------------------------------------------------------------------------
// Load The Global Application Script
//--------------------------------------------------------------------------

$path = APPPATH .'Global.php';

if (is_readable($path)) require $path;

});

//--------------------------------------------------------------------------
// Return The Application
//--------------------------------------------------------------------------

return $app;
