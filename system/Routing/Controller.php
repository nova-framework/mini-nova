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
	 * Get the middleware for a given method.
	 *
	 * @param  string  $method
	 * @return array
	 */
	public function getMiddlewareForMethod($method)
	{
		$middleware = array();

		foreach ($this->middleware as $name => $options) {
			if (isset($options['only']) && ! in_array($method, (array) $options['only'])) {
				continue;
			}

			if (isset($options['except']) && in_array($method, (array) $options['except'])) {
				continue;
			}

			$middleware[] = $name;
		}

		return $middleware;
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
