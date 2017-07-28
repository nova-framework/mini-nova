<?php

namespace App\Http;

use Mini\Foundation\Http\Kernel as HttpKernel;


class Kernel extends HttpKernel
{
    /**
     * The Application's Middleware stack.
     *
     * @var array
     */
    protected $middleware = array(
        'Mini\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Mini\Routing\Middleware\DispatchAssetFiles',
    );

    /**
     * The Application's route Middleware Groups.
     *
     * @var array
     */
    protected $middlewareGroups = array(
        'web' => array(
            'App\Http\Middleware\HandleProfiling',
            'Mini\Cookie\Middleware\AddQueuedCookiesToResponse',
            'Mini\Session\Middleware\StartSession',
            'Mini\Foundation\Http\Middleware\SetupLanguage',
            'Mini\View\Middleware\ShareErrorsFromSession',
            'App\Http\Middleware\VerifyCsrfToken',
        ),
        'api' => array(
            'throttle:60,1',
        )
    );

    /**
     * The Application's route Middleware.
     *
     * @var array
     */
    protected $routeMiddleware = array(
        'auth'        => 'Mini\Auth\Middleware\Authenticate',
        'guest'        => 'App\Http\Middleware\RedirectIfAuthenticated',
        'throttle'    => 'Mini\Routing\Middleware\ThrottleRequests',
    );
}
