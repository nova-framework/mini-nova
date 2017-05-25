<?php

namespace Widgets\Providers;

use Mini\Foundation\AliasLoader;
use Mini\Support\ServiceProvider;

use Widgets\Widget;


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
		$this->package('Widgets', 'widgets', $path);

		// Register the Plugin's Facades.
		AliasLoader::getInstance()->alias('Widget', 'Widgets\Facades\Widget');
	}

	/**
	 * Register the Widgets plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('widgets', function($app)
		{
			return new Widget($app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('widgets');
	}
}
