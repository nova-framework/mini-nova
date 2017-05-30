<?php

namespace Notifications\Database\ORM;

use Mini\Database\ORM\Collection as BaseCollection;


class Collection extendeds BaseCollection
{
	/**
	 * Mark all notification as read.
	 *
	 * @return void
	 */
	public function markAsRead()
	{
		$this->each(function ($notification) {
			$notification->markAsRead();
		});
	}
}
