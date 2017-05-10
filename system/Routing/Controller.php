<?php

namespace Mini\Routing;

use Mini\Http\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use BadMethodCallException;


abstract class Controller
{
	/**
	 * The middleware registered on the controller.
	 *
	 * @var array
	 */
	protected $middleware = array();


	/**
	 * Register middleware on the controller.
	 *
	 * @param  string  $middleware
	 * @param  array   $options
	 * @return void
	 */
	public function middleware($middleware, array $options = array())
	{
		$this->middleware[$middleware] = $options;
	}

	/**
	 * Get the middleware assigned to the controller.
	 *
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * Execute an action on the controller.
	 *
	 * @param string  $method
	 * @param array   $params
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function callAction($method, array $parameters = array())
	{
		return call_user_func_array(array($this, $method), $parameters);
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function missingMethod($parameters = array())
	{
		throw new NotFoundHttpException("Controller method not found.");
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		throw new BadMethodCallException("Method [$method] does not exist.");
	}
}
