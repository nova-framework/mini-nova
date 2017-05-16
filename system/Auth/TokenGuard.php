<?php

namespace Mini\Auth;

use Mini\Auth\Contracts\GuardInterface;
use Mini\Auth\GuardTrait;
use Mini\Http\Request;


class TokenGuard implements GuardInterface
{
	use GuardTrait;

	/**
	 * The ORM user model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * The request instance.
	 *
	 * @var \Mini\Http\Request
	 */
	protected $request;

	/**
	 * The name of the field on the request containing the API token.
	 *
	 * @var string
	 */
	protected $inputKey;

	/**
	 * The name of the token "column" in persistent storage.
	 *
	 * @var string
	 */
	protected $storageKey;


	/**
	 * Create a new authentication guard.
	 *
	 * @param  \Mini\Database\Connection  $connection
	 * @param  \Mini\Http\Request  $request
	 * @param  string  $table
	 * @return void
	 */
	public function __construct($model, Request $request)
	{
		$this->model   = $model;
		$this->request = $request;

		$this->inputKey   = 'api_token';
		$this->storageKey = 'api_token';
	}

	/**
	 * Get the currently authenticated user.
	 *
	 * @return \Mini\Auth\Contracts\UserInterface|null
	 */
	public function user()
	{
		if (! is_null($this->user)) {
			return $this->user;
		}

		$token = $this->getTokenForRequest();

		if (! empty($token)) {
			return $this->user = $this->retrieveUserByToken($token);
		}
	}

	/**
	 * Get the token for the current request.
	 *
	 * @return string
	 */
	protected function getTokenForRequest()
	{
		$token = $this->request->input($this->inputKey);

		if (empty($token)) {
			$token = $this->request->bearerToken();
		}

		if (empty($token)) {
			$token = $this->request->getPassword();
		}

		return $token;
	}

	/**
	 * Validate a user's credentials.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validate(array $credentials = array())
	{
		$key = $this->inputKey;

		if (empty($credentials[$key)) {
			return false;
		}

		$user = $this->retrieveUserByToken($credentials[$key]);

		return ! is_null($user);
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return \Nova\Auth\Contracts\UserInterface|null
	 */
	public function retrieveUserByToken($token)
	{
		$model = $this->createModel();

		return $model->newQuery()
			->where($this->storageKey, $token)
			->first();
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

	/**
	 * Create a new instance of the model's Query Buider.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function createModel()
	{
		$model = '\\' .ltrim($this->model, '\\');

		return new $model;
	}
}
