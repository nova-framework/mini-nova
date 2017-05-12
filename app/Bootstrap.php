<?php

use Mini\Foundation\Application;


//--------------------------------------------------------------------------
// Set The Framework Starting Time
//--------------------------------------------------------------------------

define('FRAMEWORK_START', microtime(true));

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
// Register The After Booting Handler
//--------------------------------------------------------------------------

$app->booted(function () use ($app)
{
	$path = APPPATH .'Global.php';

	if (is_readable($path)) require $path;
});

//--------------------------------------------------------------------------
// Return The Application
//--------------------------------------------------------------------------

return $app;
