<?php

//--------------------------------------------------------------------------
// Use Internally The UTF-8 Encoding
//--------------------------------------------------------------------------

mb_internal_encoding('UTF-8');

//--------------------------------------------------------------------------
// Load The Global Configuration
//--------------------------------------------------------------------------

require APPPATH .'Config.php';

//--------------------------------------------------------------------------
// Create New Application
//--------------------------------------------------------------------------

$app = new Mini\Foundation\Application();

//--------------------------------------------------------------------------
// Bind Paths
//--------------------------------------------------------------------------

$app->bindInstallPaths(array(
	'base'	=> BASEPATH,
	'app'	 => APPPATH,
	'public'  => WEBPATH,
	'storage' => STORAGE_PATH,
));

//--------------------------------------------------------------------------
// Bind Important Interfaces
//--------------------------------------------------------------------------

$app->singleton(
	'Mini\Http\Contracts\KernelInterface',
	'App\Kernel'
);

$app->singleton(
	'Nova\Console\Contracts\KernelInterface',
	'App\Console\Kernel'
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
