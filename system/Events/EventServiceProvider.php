<?php

namespace Mini\Events;

use Mini\Events\Dispatcher;
use Mini\Support\ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['events'] = $this->app->share(function($app)
		{
			return new Dispatcher($app);
		});
	}

}
