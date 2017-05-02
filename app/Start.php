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
// Global Constants
//--------------------------------------------------------------------------

define('MINIME_START', microtime(true));

/**
 * PREFER to be used in Database calls or storing Session data, default is 'mini_'
 */
define('PREFIX', 'mini_');

//--------------------------------------------------------------------------
// Set PHP Error Reporting Options
//--------------------------------------------------------------------------

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

Facade::setFacadeApplication($app);

//--------------------------------------------------------------------------
// Register Facade Aliases To Full Classes
//--------------------------------------------------------------------------

$app->registerCoreContainerAliases();

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

// Get the Router instance.
$router = $app['router'];

//--------------------------------------------------------------------------
// Load The Global Application Script
//--------------------------------------------------------------------------

require APPPATH .'Global.php';

});

//--------------------------------------------------------------------------
// Return The Application
//--------------------------------------------------------------------------

return $app;
