<?php

namespace Mini\Routing;

use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Route;
use Mini\Routing\RouteCompiler;
use Mini\Routing\Router;
use Mini\Support\Arr;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use Countable;
use ArrayIterator;
use IteratorAggregate;


class RouteCollection implements Countable, IteratorAggregate
{
	/**
	 * All of the routes that have been registered.
	 *
	 * @var array
	 */
	protected $routes = array();

	/**
	 * An flattened array of all of the routes.
	 *
	 * @var array
	 */
	protected $allRoutes = array();

	/**
	 * A look-up table of routes by their names.
	 *
	 * @var array
	 */
	protected $nameList = array();

	/**
	 * A look-up table of routes by controller action.
	 *
	 * @var array
	 */
	protected $actionList = array();


	/**
	 * Add a route to the router.
	 *
	 * @param  \Mini\Routing\Route  $route
	 * @return void
	 */
	public function addRoute($route)
	{
		$uri = $route->getUri();

		foreach ($route->getMethods() as $method) {
			$this->routes[$method][$uri] = $route;
		}

		$this->allRoutes[] = $route;

		// Add the Route instance to lookup lists.
		$this->addLookups($route);

		return $route;
	}

	/**
	 * Add the route to any look-up tables if necessary.
	 *
	 * @param  \Mini\Routing\Route  $route
	 * @return void
	 */
	protected function addLookups($route)
	{
		$action = $route->getAction();

		if (isset($action['as'])) {
			$name = $action['as'];

			$this->nameList[$name] = $route;
		}

		if (isset($action['controller'])) {
			$key = $action['controller'];

			if (! isset($this->actionList[$key])) {
				$this->actionList[$key] = $route;
			}
		}
	}

	/**
	 * Iterate through every route to find a matching route.
	 *
	 * @param \Mini\Http\Request $request
	 *
	 * @return \Mini\Routing\Route|null
	 */
	public function match(Request $request)
	{
		$routes = $this->get($request->getMethod());

		if (! is_null($route = $this->check($routes, $request))) {
			return $route;
		}

		// No Route match found; check for the alternate HTTP Methods.
		$others = $this->checkForAlternateMethods($request);

		if (count($others) > 0) {
			return $this->getOtherMethodsRoute($request, $others);
		}

		throw new NotFoundHttpException();
	}

	/**
	 * Determine if a route in the array matches the request.
	 *
	 * @param  array  $routes
	 * @param  \Mini\Http\Request  $request
	 * @return \Mini\Routing\Route|null
	 */
	protected function check(array $routes, Request $request)
	{
		$path = '/' .ltrim($request->path(), '/');

		if (isset($routes[$path])) {
			// Direct match found, and that's good because it is faster.
			return $routes[$path];
		}

		return Arr::first($routes, function($uri, $route) use ($path)
		{
			return $route->matches($path);
		});
	}

	/**
	 * Determine if any routes match on another HTTP verb.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return array
	 */
	protected function checkForAlternateMethods(Request $request)
	{
		$methods = array_diff(Router::$methods, (array) $request->getMethod());

		$others = array();

		foreach ($methods as $method) {
			if (! is_null($route = $this->check($this->get($method), $request))) {
				$others[] = $method;
			}
		}

		return $others;
	}

	/**
	 * Get a route (if necessary) that responds when other available methods are present.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  array  $others
	 * @return \Mini\Routing\Route
	 *
	 * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedHttpException
	 */
	protected function getOtherMethodsRoute(Request $request, array $others)
	{
		if ($request->getMethod() !== 'OPTIONS') {
			throw new MethodNotAllowedHttpException($others);
		}

		return new Route('OPTIONS', $request->path(), function() use ($others)
		{
			return new Response('', 200, array('Allow' => implode(',', $others)));
		});
	}

	/**
	 * Get all of the routes in the collection.
	 *
	 * @param  string|null  $method
	 * @return array
	 */
	protected function get($method = null)
	{
		if (is_null($method)) {
			return $this->getRoutes();
		}

		return Arr::get($this->routes, $method, array());
	}

	/**
	 * Determine if the route collection contains a given named route.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function hasNamedRoute($name)
	{
		return ! is_null($this->getByName($name));
	}

	/**
	 * Get a route instance by its name.
	 *
	 * @param  string  $name
	 * @return \Mini\Routing\Route|null
	 */
	public function getByName($name)
	{
		return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
	}

	/**
	 * Get a route instance by its controller action.
	 *
	 * @param  string  $action
	 * @return \Mini\Routing\Route|null
	 */
	public function getByAction($action)
	{
		return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
	}

	/**
	 * Get all of the registered routes.
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->allRoutes;
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getRoutes());
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->getRoutes());
	}
}
