<?php

namespace Mini\Auth;

use Mini\Auth\GuardInterface;
use Mini\Auth\GuardTrait;
use Mini\Database\Connection;
use Mini\Http\Request;


class TokenGuard implements GuardInterface
{
	use GuardTrait;

    /**
     * The active database connection.
     *
     * @var \Mini\Database\Connection
     */
    protected $connection;

	/**
	 * The table containing the users.
	 *
	 * @var string
	 */
	protected $table;

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
	public function __construct(Connection $connection, Request $request, $table)
	{
		$this->connection = $connection;
		$this->request    = $request;
		$this->table      = $table;

		$this->inputKey   = 'api_token';
		$this->storageKey = 'api_token';
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
		if (empty($credentials[$this->inputKey])) {
			return false;
		}

		$token = $credentials[$this->inputKey];

		if (! is_null($user = $this->retrieveUserByToken($token))) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array $credentials
	 * @return \Nova\Auth\UserInterface|null
	 */
	public function retrieveUserByToken($token)
	{
		return $this->newQuery()
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
	 * Get a new Query Builder instance.
	 *
	 * @return \Mini\Database\Query\Builder
	 */
	protected function newQuery()
	{
		return $this->connection->table($this->table);
	}
}
