<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace App\Controllers\Admin;

use App\Controllers\BackendController;


class Dashboard extends BackendController
{

	public function index()
	{
		$content = '';

		return $this->getView()
			->shares('title', __('Dashboard'))
			->with('debug', $content);
	}

}
