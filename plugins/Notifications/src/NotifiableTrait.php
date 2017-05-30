<?php

namespace Notifications;

use Mini\Support\Facades\App;
use Mini\Support\Str;


trait NotifiableTrait
{
	/**
	 * Get the entity's notifications.
	 */
	public function notifications()
	{
		return $this->morphMany('Notifications\Models\Notification', 'notifiable')->orderBy('created_at', 'desc');
	}

	/**
	 * Get the entity's read notifications.
	 */
	public function readNotifications()
	{
		return $this->notifications()->whereNotNull('read_at');
	}

	/**
	 * Get the entity's unread notifications.
	 */
	public function unreadNotifications()
	{
		return $this->notifications()->whereNull('read_at');
	}

	/**
	 * Send the given notification.
	 *
	 * @param  mixed  $instance
	 * @return void
	 */
	public function notify($instance)
	{
		$dispatcher = App::make('Notifications\Contracts\DispatcherInterface');

		return $dispatcher->send($this, $instance);
	}

	/**
	 * Get the notification routing information for the given driver.
	 *
	 * @param  string  $driver
	 * @return mixed
	 */
	public function routeNotificationFor($driver)
	{
		if (method_exists($this, $method = 'routeNotificationFor'. Str::studly($driver))) {
			return call_user_func(array($this, $method));
		}

		switch ($driver) {
			case 'database':
				return $this->notifications();

			case 'mail':
				return $this->email;
		}
	}

}
