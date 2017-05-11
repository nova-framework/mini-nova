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
		'App\Middleware\InsertStatistics',
		'Mini\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Mini\Session\Middleware\StartSession',
		'Mini\Foundation\Http\Middleware\SetupLanguage',
		'App\Middleware\VerifyCsrfToken',
	);

	/**
	 * The Application's route Middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = array();
}
