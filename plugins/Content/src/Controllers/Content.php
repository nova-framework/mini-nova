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
		$title = __d('content', 'Welcome to the Content Plugin');

		$content = __d('content', 'Yep! It works.');

		//
		$this->set(compact('title', 'content'));

		// Render the alternate View.
		$this->render('OtherIndex');

		/*
		return $this->createView(compact('content'), 'OtherIndex')
			->shares('title', __d('content', 'Welcome to the Content Plugin'));
		*/
	}

}
