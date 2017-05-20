<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace Content\Controllers;

use App\Controllers\BaseController;


class Content extends BaseController
{

	public function index()
	{
		return $this->getView()
			->shares('title', __d('content', 'Welcome to Content Plugin'))
			->with('content', __d('content', 'Yep! It works.'));
	}

}
