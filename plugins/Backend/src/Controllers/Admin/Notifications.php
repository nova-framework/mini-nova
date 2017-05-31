<?php

namespace Backend\Controllers\Admin;

use Mini\Support\Facades\Auth;

use Backend\Controllers\BaseController;

use Backend\Models\User;


class Notifications extends BaseController
{

	public function index()
	{
		$perPage = 15;

		$authUser = Auth::user();

		// Retrieve the unread notifications for the current User.
		$totalUnread = $authUser->unreadNotifications->count();

		$notificationCount = ($totalUnread > $perPage)
			? ($totalUnread - $perPage)
			: $totalUnread;

		//
		$notifications = $authUser->unreadNotifications()->paginate($perPage);

		// Mark all unread notifications as read.
		$notifications->markAsRead();

		return $this->getView()
			->shares('title', __d('backend', 'Notifications'))
			->shares('notificationCount', $notificationCount)
			->with('authUser', $authUser)
			->with('notifications', $notifications);
	}

}
