<?php

namespace App;

use Mini\Foundation\Http\Kernel as HttpKernel;


class Kernel extends HttpKernel
{
	/**
	 * The Application's global HTTP Middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = array(
		'Mini\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'App\Middleware\HandleProfiling',
		'Mini\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Mini\Session\Middleware\StartSession',
		'Mini\Foundation\Http\Middleware\SetupLanguage',
		'Mini\View\Middleware\ShareErrorsFromSession',
		'App\Middleware\VerifyCsrfToken',
	);

	/**
	 * The Application's route Middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = array(
		'auth'		=> 'Mini\Auth\Middleware\Authenticate',
		'guest'		=> 'App\Middleware\RedirectIfAuthenticated',
	);
}
