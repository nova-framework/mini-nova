<?php

namespace Mini\Log;

use Mini\Log\Writer;
use Mini\Support\ServiceProvider;

use Monolog\Logger;


class LogServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $logger = new Writer(
            new Logger('logger'), $this->app['events']
        );

        $this->app->instance('log', $logger);

        $this->app->bind('Psr\Log\LoggerInterface', function($app)
        {
            return $app['log']->getMonolog();
        });

        if (isset($this->app['log.setup'])) {
            call_user_func($this->app['log.setup'], $logger);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('log', 'Psr\Log\LoggerInterface');
    }

}
