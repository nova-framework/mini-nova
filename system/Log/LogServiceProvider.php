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
        $this->app->instance('log', $log = new Writer(
            new Logger('framework'), $this->app['events']
        ));

        $log->useFiles(STORAGE_PATH .'logs' .DS .'error.log');

        $this->app->bind('Psr\Log\LoggerInterface', function($app)
        {
            return $app['log'];
        });
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
