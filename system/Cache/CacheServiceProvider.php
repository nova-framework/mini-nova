<?php

namespace Mini\Cache;

use Mini\Support\ServiceProvider;


class CacheServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('cache', function($app)
		{
			return new CacheManager($app);
		});

		$this->app->bindShared('cache.store', function($app)
		{
			return $app['cache']->driver();
		});

		$this->app->bindShared('command.cache.clear', function($app)
		{
			return new Console\ClearCommand($app['cache'], $app['files']);
		});

		$this->commands('command.cache.clear');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'cache', 'cache.store', 'command.cache.clear'
		);
	}

}
