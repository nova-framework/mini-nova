<?php

namespace Notifications;


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
	 * Send the given notification.
	 *
	 * @param  mixed  $instance
	 * @return void
	 */
	public function notify($instance)
	{
		app('Notifications\Dispatcher')->send($this, $instance);
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
}
