<?php

namespace Mini\Routing;

use Mini\Routing\ControllerDispatcher;
use Mini\Routing\Router;
use Mini\Support\ServiceProvider;


class RoutingServiceProvider extends ServiceProvider
{

    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouter();

        $this->registerCustomDispatcher();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app['router'] = $this->app->share(function($app)
        {
            return new Router($app['events'], $app);
        });
    }

    /**
     * Register the URL generator service.
     *
     * @return void
     */
    protected function registerCustomDispatcher()
    {
        $this->app->singleton('framework.route.dispatcher', function ($app)
        {
            return new ControllerDispatcher($app['router'], $app);
        });
    }
    
}
