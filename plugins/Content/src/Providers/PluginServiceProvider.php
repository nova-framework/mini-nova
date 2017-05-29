<?php

namespace Content\Providers;

use Mini\Auth\Contracts\Access\GateInterface as Gate;
use Mini\Plugins\Support\Providers\PluginServiceProvider as ServiceProvider;
use Mini\Routing\Router;


class PluginServiceProvider extends ServiceProvider
{
	/**
	 * The additional provider class names.
	 *
	 * @var array
	 */
	protected $providers = array(
		'Content\Providers\RouteServiceProvider',
	);

	/**
	 * The event listener mappings for the plugin.
	 *
	 * @var array
	 */
	protected $listen = array(
		'Content\Events\SomeEvent' => array(
			'Content\Listeners\EventListener',
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
		$path = $path .DS .'Bootstrap.php';

		$this->bootstrapFrom($path);

		// Load the Plugin Policies.
		$gate = $this->app->make(Gate::class);

		$this->registerPolicies($gate);

		//
		parent::boot();
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
