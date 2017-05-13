<?php

namespace Mini\Auth;

use Mini\Auth\GuardInterface;
use Mini\Auth\GuardTrait;
use Mini\Http\Request;


class RequestGuard implements GuardInterface
{
	use GuardTrait;

	/**
	 * The guard callback.
	 *
	 * @var callable
	 */
	protected $callback;

	/**
	 * The request instance.
	 *
	 * @var \Mini\Http\Request
	 */
	protected $request;


	/**
	 * Create a new authentication guard.
	 *
	 * @param  callable  $callback
	 * @param  \Mini\Http\Request  $request
	 * @return void
	 */
	public function __construct(callable $callback, Request $request)
	{
		$this->request  = $request;
		$this->callback = $callback;
	}

	/**
	 * Get the currently authenticated user.
	 *
	 * @return \Mini\Auth\UserInterface|null
	 */
	public function user()
	{
		if (! is_null($this->user)) {
			return $this->user;
		}

		return $this->user = call_user_func($this->callback, $this->request);
	}

	/**
	 * Validate a user's credentials.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validate(array $credentials = array())
	{
		$guard = new static($this->callback, $credentials['request']);

		return ! is_null($guard->user());
	}

	/**
	 * Set the current request instance.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return $this
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;

		return $this;
	}
}
