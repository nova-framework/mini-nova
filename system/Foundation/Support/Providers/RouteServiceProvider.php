<?php

namespace Mini\Foundation\Support\Providers;

use Mini\Routing\Router;
use Mini\Support\ServiceProvider;


class RouteServiceProvider extends ServiceProvider
{
    /**
     * The Controller namespace for the application.
     *
     * @var string|null
     */
    protected $namespace;


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutes();
    }

    /**
     * Load the application routes.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        if (method_exists($this, 'map')) {
            $router = $this->app['router'];

            call_user_func(array($this, 'map'), $router);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Pass dynamic methods onto the router instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $router = $this->app['router'];

        return call_user_func_array(array($router, $method), $parameters);
    }
}
