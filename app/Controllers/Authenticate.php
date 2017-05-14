<?php
/**
 * Authorize - A Controller for managing the User Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace App\Controllers;

use Mini\Foundation\Auth\AuthenticatesUsersTrait;
use Mini\Foundation\Auth\ThrottlesLoginsTrait;
use Mini\Http\Request;
use Mini\Support\Facades\Redirect;

use App\Controllers\BaseController;


class Authenticate extends BaseController
{
	use AuthenticatesUsersTrait, ThrottlesLoginsTrait;

	//
	protected $layout = 'Authorize';


	protected function authenticated(Request $request, $user)
	{
		$status = __('<b>{0}</b>, you have successfully logged in.', $user->username);

		return Redirect::intended($this->redirectPath())->with('success', $status);
	}
}
