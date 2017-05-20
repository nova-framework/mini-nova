<?php

namespace App\Http;

use Mini\Foundation\Http\Kernel as HttpKernel;


class Kernel extends HttpKernel
{
	/**
	 * The Application's global HTTP Middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = array(
		'Mini\Routing\Middleware\ServeAsset',
		'Mini\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'App\Http\Middleware\HandleProfiling',
		'Mini\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Mini\Session\Middleware\StartSession',
		'Mini\Foundation\Http\Middleware\SetupLanguage',
		'Mini\View\Middleware\ShareErrorsFromSession',
		'App\Http\Middleware\VerifyCsrfToken',
	);

	/**
	 * The Application's route Middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = array(
		'auth'		=> 'Mini\Auth\Middleware\Authenticate',
		'guest'		=> 'App\Http\Middleware\RedirectIfAuthenticated',
	);
}
