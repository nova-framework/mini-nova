<?php

namespace Mini\Auth\Contracts;

use Mini\Auth\Contracts\GuardInterface;
use Mini\Auth\Contracts\UserInterface;


interface StatefulGuardInterface extends GuardInterface
{
	/**
	 * Attempt to authenticate a user using the given credentials.
	 *
	 * @param  array  $credentials
	 * @param  bool   $remember
	 * @param  bool   $login
	 * @return bool
	 */
	public function attempt(array $credentials = array(), $remember = false, $login = true);

	/**
	 * Log a user into the application without sessions or cookies.
	 *
	 * @param  array  $credentials
	 * @return bool
	 */
	public function once(array $credentials = array());

	/**
	 * Log a user into the application.
	 *
	 * @param  \Mini\Auth\Contracts\UserInterface  $user
	 * @param  bool  $remember
	 * @return void
	 */
	public function login(UserInterface $user, $remember = false);

	/**
	 * Log the given user ID into the application.
	 *
	 * @param  mixed  $id
	 * @param  bool   $remember
	 * @return \Mini\Contracts\Auth\UserInterface
	 */
	public function loginUsingId($id, $remember = false);

	/**
	 * Log the given user ID into the application without sessions or cookies.
	 *
	 * @param  mixed  $id
	 * @return bool
	 */
	public function onceUsingId($id);

	/**
	 * Determine if the user was authenticated via "remember me" cookie.
	 *
	 * @return bool
	 */
	public function viaRemember();

	/**
	 * Log the user out of the application.
	 *
	 * @return void
	 */
	public function logout();
}
