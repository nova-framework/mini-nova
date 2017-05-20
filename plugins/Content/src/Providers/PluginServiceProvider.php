<?php

namespace Content\Providers;

use Mini\Support\ServiceProvider;


class PluginServiceProvider extends ServiceProvider
{

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

        // Load the Routes.
        require $path .DS .'Routes.php';
    }

    /**
     * Register the Content plugin Service Provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
