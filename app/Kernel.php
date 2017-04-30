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
        //'Mini\Cookie\Middleware\AddQueuedCookiesToResponse',
        'App\Middleware\HandleStatistics',
    );

    /**
     * The Application's route Middleware.
     *
     * @var array
     */
    protected $routeMiddleware = array();
}
