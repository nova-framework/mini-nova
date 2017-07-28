<?php

namespace Content\Providers;

use Mini\Plugins\Support\Providers\PluginServiceProvider as ServiceProvider;


class PluginServiceProvider extends ServiceProvider
{
    /**
     * The additional provider class names.
     *
     * @var array
     */
    protected $providers = array(
        //'Content\Providers\AuthServiceProvider',
        //'Content\Providers\EventServiceProvider',
        'Content\Providers\RouteServiceProvider',
    );


    /**
     * Bootstrap the Application Events.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__ .'/../');

        // Configure the Package.
        $this->package('Content', 'content', $path);

        // Bootstrap the Plugin.
        require $path .DS .'Bootstrap.php';
    }

    /**
     * Register the Content plugin Service Provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        //
    }
}
