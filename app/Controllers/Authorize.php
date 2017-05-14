<?php
/**
 * Authorize - A Controller for managing the User Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace App\Controllers;

use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Hash;
use Mini\Support\Facades\Input;
use Mini\Support\Facades\Redirect;

use App\Controllers\BaseController;


class Authorize extends BaseController
{
	protected $layout = 'Authorize';


	/**
	 * Display the login view.
	 *
	 * @return Response
	 */
	public function login()
	{
		return $this->getView()
			->shares('title', __('User Login'));
	}

	/**
	 * Handle a POST request to login the User.
	 *
	 * @return Response
	 */
	public function postLogin()
	{
		// Retrieve the Authentication credentials.
		$credentials = Input::only('username', 'password');

		// Prepare the 'remember' parameter.
		$remember = (Input::get('remember') == 'remember');

		// Make an attempt to login the Guest with the given credentials.
		if(! Auth::attempt($credentials, $remember)) {
			// An error has happened on authentication.
			$status = __('Wrong username or password.');

			return Redirect::back()
				->withInput(Input::except('password'))
				->with('warning', $status);
		}

		// The User is authenticated now; retrieve his Model instance.
		$user = Auth::user();

		if (Hash::needsRehash($user->password)) {
			$password = $credentials['password'];

			$user->password = Hash::make($password);

			// Save the User Model instance.
			$user->save();
		}

		// Prepare the flash message.
		$status = __('<b>{0}</b>, you have successfully logged in.', $user->username);

		// Redirect to the User's Dashboard.
		return Redirect::intended('admin/dashboard')->with('success', $status);
	}

	/**
	 * Handle a GET request to logout the current User.
	 *
	 * @return Response
	 */
	public function logout()
	{
		Auth::logout();

		return Redirect::to('auth/login');
	}

}
