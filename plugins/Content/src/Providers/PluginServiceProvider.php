<?php

namespace Content\Providers;

use Mini\Support\ServiceProvider;


class PluginServiceProvider extends ServiceProvider
{
	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * @var string
	 */
	protected $namespace = 'Content\Controllers';


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

		// Load the Plugin Bootstrap.
		require $path .DS .'Bootstrap.php';

		// Load the Plugin Routes.
		$router = $this->app['router'];

		$router->group(array('namespace' => $this->namespace), function ($router) use ($path)
		{
			require $path .DS .'Routes.php';
		});
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
