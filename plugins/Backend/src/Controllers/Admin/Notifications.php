<?php

namespace Backend\Controllers\Admin;

use Mini\Support\Facades\Auth;

use Backend\Controllers\BaseController;

use Backend\Models\User;


class Notifications extends BaseController
{

	public function index()
	{
		$authUser = Auth::user();

		// Retrieve the unread notifications for the current User.
		$notifications = $authUser->notifications()->unread()->get();

		// Mark all notifications as read.
		$notifications->each(function($model) {
			$model->markAsRead();
		});

		return $this->getView()
			->shares('title', __d('backend', 'Notifications'))
			->with('authUser', $authUser)
			->with('notifications', $notifications);
	}

}
