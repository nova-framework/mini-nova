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

		// Register the Facades.
		$loader = AliasLoader::getInstance();

		$loader->alias('Widget', 'Widgets\Facades\Widget');
	}
}
