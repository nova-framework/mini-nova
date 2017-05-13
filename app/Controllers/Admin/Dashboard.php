<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace App\Controllers\Admin;

use Nova\Support\Facades\Config;
use Nova\Support\Facades\View;

use App\Controllers\Admin\Controller;


class Dashboard extends Controller
{

	public function index()
	{
		$debug = '';

		return $this->getView()
			->shares('title', __('Dashboard'))
			->with('debug', $debug);
	}

}
