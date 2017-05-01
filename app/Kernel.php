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
        'App\Middleware\HandleStatistics',
        'Mini\Cookie\Middleware\AddQueuedCookiesToResponse',
    );

    /**
     * The Application's route Middleware.
     *
     * @var array
     */
    protected $routeMiddleware = array();
}
