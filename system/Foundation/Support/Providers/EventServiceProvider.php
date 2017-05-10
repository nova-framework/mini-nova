<?php

namespace Mini\Foundation\Support\Providers;

use Mini\Events\Dispatcher;

use Mini\Support\ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = array();

	/**
	 * The subscriber classes to register.
	 *
	 * @var array
	 */
	protected $subscribe = array();


	/**
	 * Register the application's event listeners.
	 *
	 * @return void
	 */
	public function boot()
	{
		$events = $this->app['events'];

		foreach ($this->listen as $event => $listeners) {
			foreach ($listeners as $listener) {
				$events->listen($event, $listener);
			}
		}

		foreach ($this->subscribe as $subscriber) {
			$events->subscribe($subscriber);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the events and handlers.
	 *
	 * @return array
	 */
	public function listens()
	{
		return $this->listen;
	}
}
