<?php

namespace Mini\Foundation\Auth;

use Mini\Http\Request;
use Mini\Support\Facades\App;
use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Validator;
use Mini\Support\Facades\View;
use Mini\Validation\ValidationException;


trait AuthenticatesUsersTrait
{

	/**
	 * Show the application login form.
	 *
	 * @return \Mini\Http\Response
	 */
	public function getLogin()
	{
		return $this->getView()->shares('title', __d('nova', 'User Login'));
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return \Mini\Http\Response
	 */
	public function postLogin(Request $request)
	{
		$this->validate($request, array(
			$this->loginUsername() => 'required', 'password' => 'required',
		));

		$throttles = $this->isUsingThrottlesLoginsTrait();

		if ($throttles && $this->hasTooManyLoginAttempts($request)) {
			return $this->sendLockoutResponse($request);
		}

		$credentials = $this->getCredentials($request);

		if (Auth::attempt($credentials, $request->has('remember'))) {
			return $this->handleUserWasAuthenticated($request, $throttles);
		}

		if ($throttles) {
			$this->incrementLoginAttempts($request);
		}

		$errors = array($this->loginUsername() => $this->getFailedLoginMessage());

		return Redirect::to($this->loginPath())
			->withInput($request->only($this->loginUsername(), 'remember'))
			->withErrors($errors);
	}

	/**
	 * Send the response after the user was authenticated.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  bool  $throttles
	 * @return \Mini\Http\Response
	 */
	protected function handleUserWasAuthenticated(Request $request, $throttles)
	{
		if ($throttles) {
			$this->clearLoginAttempts($request);
		}

		if (method_exists($this, 'authenticated')) {
			return $this->authenticated($request, Auth::user());
		}

		return Redirect::intended($this->redirectPath());
	}

	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return array
	 */
	protected function getCredentials(Request $request)
	{
		return $request->only($this->loginUsername(), 'password');
	}

	/**
	 * Get the failed login message.
	 *
	 * @return string
	 */
	protected function getFailedLoginMessage()
	{
		return __d('nova', 'These credentials do not match our records.');
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return \Mini\Http\Response
	 */
	public function getLogout()
	{
		Auth::logout();

		$uri = property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/auth/login';

		return Redirect::to($uri);
	}

	/**
	 * Get the path to the login route.
	 *
	 * @return string
	 */
	public function loginPath()
	{
		return property_exists($this, 'loginPath') ? $this->loginPath : '/auth/login';
	}

	/**
	 * Get the login username to be used by the controller.
	 *
	 * @return string
	 */
	public function loginUsername()
	{
		return property_exists($this, 'username') ? $this->username : 'username';
	}

	/**
	 * Get the post register / login redirect path.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		if (property_exists($this, 'redirectPath')) {
			return $this->redirectPath;
		}

		return property_exists($this, 'redirectTo') ? $this->redirectTo : '/admin/dashboard';
	}

	/**
	 * Determine if the class is using the ThrottlesLogins trait.
	 *
	 * @return bool
	 */
	protected function isUsingThrottlesLoginsTrait()
	{
		return in_array(
			'Mini\Foundation\Auth\ThrottlesLoginsTrait', class_uses_recursive(get_class($this))
		);
	}
}
