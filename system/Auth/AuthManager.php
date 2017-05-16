<?php

namespace Mini\Auth;

use Mini\Auth\RequestGuard;
use Mini\Auth\SessionGuard;
use Mini\Auth\TokenGuard;
use Mini\Foundation\Application;

use Closure;
use InvalidArgumentException;


class AuthManager
{
	/**
	 * The application instance.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * The registered custom driver creators.
	 *
	 * @var array
	 */
	protected $customCreators = array();

	/**
	 * The array of created "drivers".
	 *
	 * @var array
	 */
	protected $guards = array();

	/**
	 * The user resolver shared by various services.
	 *
	 * Determines the default user for Request, and the UserInterface.
	 *
	 * @var \Closure
	 */
	protected $userResolver;


	/**
	 * Create a new manager instance.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;

		$this->userResolver = function ($guard = null)
		{
			return $this->guard($guard)->user();
		};
	}

	/**
	 * Attempt to get the guard from the local cache.
	 *
	 * @param  string  $name
	 * @return \Mini\Auth\Guard
	 */
	public function guard($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();

		if (! isset($this->guards[$name])) {
			$this->guards[$name] = $this->resolve($name);
		}

		return $this->guards[$name];
	}

	/**
	 * Resolve the given guard.
	 *
	 * @param  string  $name
	 * @return \Mini\Auth\Guard
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function resolve($name)
	{
		$config = $this->getConfig($name);

		if (is_null($config)) {
			throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
		}

		if (isset($this->customCreators[$config['driver']])) {
			return $this->callCustomCreator($name, $config);
		}

		$method = 'create' .ucfirst($config['driver']) .'Driver';

		if (! method_exists($this, $method)) {
			throw new InvalidArgumentException("Auth guard driver [{$config['driver']}] is not defined.");
		}

		return call_user_func(array($this, $method), $name, $config);
	}

	/**
	 * Call a custom driver creator.
	 *
	 * @param  string  $name
	 * @param  array  $config
	 * @return mixed
	 */
	protected function callCustomCreator($name, array $config)
	{
		$driver = $config['driver'];

		$callback = $this->customCreators[$driver];

		return call_user_func($callback, $this->app, $name, $config);
	}

	/**
	 * Create an instance of the database driver.
	 *
	 * @return \Mini\Auth\Guard
	 */
	public function createSessionDriver($name, array $config)
	{
		$guard = new SessionGuard(
			$name, $config['model'],
			$this->app['session.store'], $this->app['hash'], $this->app['encrypter'], $this->app['cookie'], $this->app['events']
		);

		if (method_exists($guard, 'setRequest')) {
			$guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
		}

		return $guard;
	}

	/**
	 * Create a token based authentication guard.
	 *
	 * @param  string  $name
	 * @param  array  $config
	 * @return \Mini\Auth\TokenGuard
	 */
	public function createTokenDriver($name, $config)
	{
		$guard = new TokenGuard($config['model'], $this->app['request']);

		$this->app->refresh('request', $guard, 'setRequest');

		return $guard;
	}

	/**
	 * Get the guard configuration.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function getConfig($name)
	{
		return $this->app['config']["auth.guards.{$name}"];
	}

	/**
	 * Get the default authentication driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->app['config']['auth.default'];
	}

	/**
	 * Set the default guard driver the factory should serve.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function shouldUse($name)
	{
		$this->setDefaultDriver($name);

		$this->userResolver = function ($name = null)
		{
			return $this->guard($name)->user();
		};
	}

	/**
	 * Set the default authentication driver name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->app['config']['auth.default'] = $name;
	}

	/**
	 * Register a new callback based request guard.
	 *
	 * @param  string  $driver
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function viaRequest($driver, Closure $callback)
	{
		return $this->extend($driver, function () use ($callback)
		{
			$guard = new RequestGuard($callback, $this->app['request']);

			$this->app->refresh('request', $guard, 'setRequest');

			return $guard;
		});
	}

	/**
	 * Get the user resolver callback.
	 *
	 * @return \Closure
	 */
	public function userResolver()
	{
		return $this->userResolver;
	}

	/**
	 * Set the callback to be used to resolve users.
	 *
	 * @param  \Closure  $userResolver
	 * @return $this
	 */
	public function resolveUsersUsing(Closure $userResolver)
	{
		$this->userResolver = $userResolver;

		return $this;
	}

	/**
	 * Register a custom driver creator Closure.
	 *
	 * @param  string  $driver
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function extend($driver, Closure $callback)
	{
		$this->customCreators[$driver] = $callback;

		return $this;
	}

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->guard(), $method), $parameters);
	}
}
