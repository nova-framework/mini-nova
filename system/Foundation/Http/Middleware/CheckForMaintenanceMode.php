<?php

namespace Mini\Foundation\Http\Middleware;

use Mini\Foundation\Application;
use Mini\Http\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Closure;


class CheckForMaintenanceMode
{
    /**
     * The application implementation.
     *
     * @var \Mini\Foundation\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  \Mini\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Mini\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            throw new HttpException(503);
        }

        return $next($request);
    }
}
