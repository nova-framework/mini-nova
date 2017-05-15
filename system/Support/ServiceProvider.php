<?php

namespace Mini\Support;


abstract class ServiceProvider
{
	/**
	 * The application instance.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;


	/**
	 * Create a new service provider instance.
	 *
	 * @param  \Mini\Foundation\Application	 $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	abstract public function register();

	/**
	 * Register the package's custom Forge commands.
	 *
	 * @param  array  $commands
	 * @return void
	 */
	public function commands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();

		$this->app['events']->listen('forge.start', function($forge) use ($commands)
		{
			$forge->resolveCommands($commands);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

	/**
	 * Determine if the provider is deferred.
	 *
	 * @return bool
	 */
	public function isDeferred()
	{
		return $this->defer;
	}
}
