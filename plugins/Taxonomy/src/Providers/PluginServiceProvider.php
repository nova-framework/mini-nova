<?php

namespace Taxonomy\Providers;

use Mini\Auth\Contracts\Access\GateInterface as Gate;
use Mini\Foundation\AliasLoader;
use Mini\Plugins\Support\Providers\PluginServiceProvider as ServiceProvider;

use Taxonomy\Support\Taxonomy;


class PluginServiceProvider extends ServiceProvider
{
    /**
     * The additional provider class names.
     *
     * @var array
     */
    protected $providers = array(
        //'Taxonomy\Providers\AuthServiceProvider',
        //'Taxonomy\Providers\EventServiceProvider',
        'Taxonomy\Providers\RouteServiceProvider'
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
        $this->package('Taxonomy', 'taxonomy', $path);

        // Bootstrap the Plugin.
        require $path .DS .'Bootstrap.php';
    }

    /**
     * Register the Taxonomy plugin Service Provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();


        $this->app->bindShared('taxonomy', function ($app)
        {
            return new Taxonomy();
        });

        // Register the Facades.
        $loader = AliasLoader::getInstance();

        $loader->alias('Taxonomy', 'Taxonomy\Support\Facades\Taxonomy');
    }

}
