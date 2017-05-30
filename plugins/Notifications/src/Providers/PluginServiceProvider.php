<?php

namespace Notifications\Providers;

use Mini\Support\ServiceProvider;

use Notifications\Dispatcher;


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
		$this->package('Notifications', 'notifications', $path);

		//
	}

	/**
	 * Register the Notifications plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('Notifications\Dispatcher', function ($app)
		{
			return new Dispatcher($app);
		});
	}

}
