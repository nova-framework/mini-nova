<?php

namespace Mini\Foundation\Auth;

use Mini\Foundation\Auth\ThrottlesLoginsTrait;
use Mini\Http\Request;
use Mini\Support\Facades\App;
use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Validator;
use Mini\Support\Facades\View;
use Mini\Validation\ValidationException;


trait AuthenticatesUsersTrait
{
	use ThrottlesLoginsTrait;

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
		$this->validateLogin($request);

		if ($this->hasTooManyLoginAttempts($request)) {
			return $this->sendLockoutResponse($request);
		}

		if ($this->attemptLogin($request)) {
			return $this->sendLoginResponse($request);
		}

		$this->incrementLoginAttempts($request);

		return $this->sendFailedLoginResponse($request);
	}

	/**
	 * Validate the user login request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	protected function validateLogin(Request $request)
	{
		$this->validate($request, array(
			$this->username() => 'required', 'password' => 'required',
		));
	}

	/**
	 * Attempt to log the user into the application.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return bool
	 */
	protected function attemptLogin(Request $request)
	{
		$credentials = $this->credentials($request);

		return $this->guard()->attempt($credentials, $request->has('remember'));
	}

	/**
	 * Send the response after the user was authenticated.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  bool  $throttles
	 * @return \Mini\Http\Response
	 */
	protected function sendLoginResponse(Request $request)
	{
		$request->session()->regenerate();

		$this->clearLoginAttempts($request);

		return $this->authenticated($request, $this->guard()->user())
			?: Redirect::intended($this->redirectPath());
	}

	/**
	 * The user has been authenticated.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  mixed  $user
	 * @return mixed
	 */
	protected function authenticated(Request $request, $user)
	{
		//
	}

	/**
	 * Get the failed login response instance.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return \Mini\Http\RedirectResponse
	 */
	protected function sendFailedLoginResponse(Request $request)
	{
		$error = __d('nova', 'These credentials do not match our records.');

		$errors = array($this->username() => $error);

		if ($request->json() || $request->expectsJson()) {
			return Response::json($errors, 422);
		}

		return Redirect::back()
			->withInput($request->only($this->username(), 'remember'))
			->withErrors($errors);
	}

	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return array
	 */
	protected function credentials(Request $request)
	{
		return $request->only($this->username(), 'password');
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return \Mini\Http\Response
	 */
	public function logout(Request $request)
	{
		$this->guard()->logout();

		$request->session()->flush();

		$request->session()->regenerate();

		return Redirect::to($this->loginPath());
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
	public function username()
	{
		return 'username';
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
	 * Get the guard to be used during authentication.
	 *
	 * @return \Mini\Auth\Contracts\GuardInterface
	 */
	protected function guard()
	{
		return Auth::guard();
	}

}
