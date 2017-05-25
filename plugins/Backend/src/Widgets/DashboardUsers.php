<?php

namespace Backend\Widgets;

use Mini\Support\Facades\View;

use Backend\Models\User;


class DashboardUsers
{
	protected $something;


	public function __construct()
	{
		//
	}


	public function render()
	{
		$users = User::count();

		$data = array(
			'type'  => 'primary',
			'icon'  => 'users',
			'count' => $users,
			'title' => __d('backend', 'Registered Users'),
			'link'  => site_url('admin/users'),
		);

		return View::fetch('Backend::Widgets/DashboardPanel', $data);
	}
}
