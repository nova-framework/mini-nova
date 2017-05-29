<?php

namespace Blocks\Controllers\Admin;

use Mini\Support\Facades\View;

use Backend\Controllers\BaseController;


class Blocks extends BaseController
{
	public function __construct()
	{
		parent::__construct();

		// Setup the Middleware.
		$this->middleware('role:administrator');
	}

	public function index()
	{
		return View::make('Default')
			->shares('title', __d('blocks', 'Blocks'))
			->with('content', __d('blocks', 'Nothing here, yet!'));
	}
}
