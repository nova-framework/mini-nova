<?php

namespace Notifications;

use Mini\Support\Contracts\ArrayableInterface;


class Notification implements ArrayableInterface
{
	/**
	 * The unique identifier for the notification.
	 *
	 * @var string
	 */
	public $id;


	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array();
	}
}
