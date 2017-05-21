<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace Backend\Controllers\Admin;

use Backend\Controllers\BaseController;


class Dashboard extends BaseController
{

	public function index()
	{
		$content = '';

		return $this->getView()
			->shares('title', __d('backend', 'Dashboard'))
			->with('debug', $content);
	}

}
