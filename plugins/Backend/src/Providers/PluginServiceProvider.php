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
	 * The event listener mappings for the plugin.
	 *
	 * @var array
	 */
	protected $listen = array(
		'Backend\Events\SomeEvent' => array(
			'Backend\Listeners\EventListener',
		),
	);

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
		$bootstrap = $path .DS .'Bootstrap.php';

		$this->bootstrapFrom($bootstrap);

		// Register the Plugin Policies.
		$gate = $this->app->make(Gate::class);

		$this->registerPolicies($gate);

		// Load the Plugin Routes.
		$routes = $path .DS .'Routes.php';

		$this->loadRoutesFrom($routes, 'web');

		//
		parent::boot();
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
