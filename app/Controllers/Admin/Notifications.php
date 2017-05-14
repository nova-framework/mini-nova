<?php

namespace App\Controllers\Admin;

use Mini\Support\Facades\Auth;

use App\Controllers\BackendController;

use App\Models\User;


class Notifications extends BackendController
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
			->shares('title', __d('system', 'Notifications'))
			->with('authUser', $authUser)
			->with('notifications', $notifications);
	}

}
