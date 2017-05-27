<?php

namespace FileManager\Providers;

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
	protected $providers = array(
		'FileManager\Providers\RouteServiceProvider'
	);

	/**
	 * The event listener mappings for the plugin.
	 *
	 * @var array
	 */
	protected $listen = array(
		'FileManager\Events\SomeEvent' => array(
			'FileManager\Listeners\EventListener',
		),
	);

	/**
	 * The policy mappings for the plugin.
	 *
	 * @var array
	 */
	protected $policies = array(
		'FileManager\Models\SomeModel' => 'FileManager\Policies\ModelPolicy',
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
		$this->package('FileManager', 'file_manager', $path);

		// Bootstrap the Plugin.
		$path = $path .DS .'Bootstrap.php';

		$this->bootstrapFrom($path);

		// Register the Plugin Policies.
		$gate = $this->app->make(Gate::class);

		$this->registerPolicies($gate);

		//
		parent::boot();
	}

	/**
	 * Register the FileManager plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		parent::register();

		//
	}

}
