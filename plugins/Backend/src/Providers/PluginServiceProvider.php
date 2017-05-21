<?php

namespace Backend\Providers;

use Mini\Auth\Contracts\Access\GateInterface as Gate;
use Mini\Plugin\Support\Providers\PluginServiceProvider as ServiceProvider;
use Mini\Routing\Router;


class PluginServiceProvider extends ServiceProvider
{
	/**
	 * The additional provider class names.
	 *
	 * @var array
	 */
	protected $providers = array();

	/**
	 * The policy mappings for the plugin.
	 *
	 * @var array
	 */
	protected $policies = array(
		'Content\Models\SomeModel' => 'Content\Policies\ModelPolicy',
	);

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * @var string
	 */
	protected $namespace = 'Backend\Controllers';


    /**
     * Bootstrap the Application Events.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__ .'/../');

        // Configure the Package.
        $this->package('Backend', 'backend', $path);

		// Bootstrap the Plugin.
		require $path .DS .'Bootstrap.php';

		// Load the Plugin Policies.
		$gate = $this->app->make(Gate::class);

		foreach ($this->policies as $key => $value) {
			$gate->policy($key, $value);
		}

		// Load the Plugin Routes.
		$router = $this->app['router'];

		$router->group(array('namespace' => $this->namespace), function ($router) use ($path)
		{
			require  $path .DS .'Routes.php';
		});
    }

    /**
     * Register the Backend plugin Service Provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        //
    }

}
