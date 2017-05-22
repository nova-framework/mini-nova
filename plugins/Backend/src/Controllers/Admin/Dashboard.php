<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace Backend\Controllers\Admin;

use Backend\Controllers\BaseController;
use Backend\Models\OnlineUser;


class Dashboard extends BaseController
{

	public function index()
	{
		$content = '';

		$onlineUsers = OnlineUser::registered()->get();

		return $this->getView()
			->shares('title', __d('backend', 'Dashboard'))
			->with('onlineUsers', $onlineUsers)
			->with('debug', $content);
	}

}
