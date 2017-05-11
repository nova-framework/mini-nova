<?php

namespace Mini\View;

use Mini\View\Factory;
use Mini\Support\ServiceProvider;


class ViewServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the Provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('view', function($app)
		{
			return new Factory($app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('view');
	}
}
