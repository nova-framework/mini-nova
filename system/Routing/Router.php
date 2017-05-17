<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Events\DispatcherInterface;
use Mini\Pipeline\Pipeline;
use Mini\Http\Exception\HttpResponseException;
use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\ControllerDispatcher;
use Mini\Routing\DependencyResolverTrait;
use Mini\Routing\Route;
use Mini\Routing\RouteCollection;
use Mini\Support\Arr;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;
use Closure;
use ReflectionFunction;


class Router
{
	use DependencyResolverTrait;

	/**
	 * The event dispatcher instance.
	 *
	 * @var \Mini\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The IoC container instance.
	 *
	 * @var \Mini\Container\Container
	 */
	protected $container;

	/**
	 * The currently dispatched Route instance.
	 *
	 * @var \Mini\Routing\Route
	 */
	protected $current;

	/**
	 * The request currently being dispatched.
	 *
	 * @var \Mini\Http\Request
	 */
	protected $currentRequest;

	/**
	 * All of the short-hand keys for middlewares.
	 *
	 * @var array
	 */
	protected $middleware = array();

	/**
	 * The instance of RouteCollection.
	 *
	 * @var \Mini\Routing\RouteCollection;
	 */
	protected $routes;

	/**
	 * The registered route value binders.
	 *
	 * @var array
	 */
	protected $binders = array();

	/**
	 * All of the wheres that have been registered.
	 *
	 * @var array
	 */
	protected $patterns = array();

	/**
	 * Array of Route Groups.
	 */
	protected $groupStack = array();

	/**
	 * An array of HTTP request methods.
	 *
	 * @var array
	 */
	public static $methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS');


	/**
	 * Construct a new Router instance.
	 *
	 * @return void
	 */
	public function __construct(DispatcherInterface $events = null, Container $container = null)
	{
		$this->events = $events;

		$this->container = $container ?: new Container();

		$this->routes = new RouteCollection();
	}

	/**
	 * Register a new GET route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function get($route, $action)
	{
		return $this->match(array('GET', 'HEAD'), $route, $action);
	}

	/**
	 * Register a new POST route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function post($route, $action)
	{
		return $this->match('POST', $route, $action);
	}

	/**
	 * Register a new PUT route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function put($route, $action)
	{
		return $this->match('PUT', $route, $action);
	}

	/**
	 * Register a new PATCH route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function patch($route, $action)
	{
		return $this->match('PATCH', $route, $action);
	}

	/**
	 * Register a new DELETE route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function delete($route, $action)
	{
		return $this->match('DELETE', $route, $action);
	}

	/**
	 * Register a new OPTIONS route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function options($route, $action)
	{
		return $this->match('OPTIONS', $route, $action);
	}

	/**
	 * Register a new route responding to all verbs.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function any($route, $action)
	{
		$methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');

		return $this->match($methods, $route, $action);
	}

	/**
	 * Register a new route with the given verbs.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function match($methods, $route, $action)
	{
		$route = $this->createRoute($methods, $route, $action);

		return $this->routes->addRoute($route);
	}

	/**
	 * Register a group of routes that share attributes.
	 *
	 * @param  array	$attributes
	 * @param  Closure  $callback
	 * @return void
	 */
	public function group($attributes, Closure $callback)
	{
		if (isset($attributes['middleware']) && is_string($attributes['middleware'])) {
			$attributes['middleware'] = explode('|', $attributes['middleware']);
		}

		if (! empty($this->groupStack)) {
			$attributes = $this->mergeGroup($attributes, end($this->groupStack));
		}

		$this->groupStack[] = $attributes;

		call_user_func($callback, $this);

		array_pop($this->groupStack);
	}

	/**
	 * Merge the given group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return array
	 */
	protected function mergeGroup($new, $old)
	{
		if (isset($new['namespace'])) {
			$new['namespace'] = isset($old['namespace'])
				? trim($old['namespace'], '\\') .'\\' .trim($new['namespace'], '\\')
				: trim($new['namespace'], '\\');
		} else {
			$new['namespace'] = isset($old['namespace']) ? $old['namespace'] : null;
		}

		if (isset($new['prefix'])) {
			$new['prefix'] = isset($old['prefix'])
				? trim($old['prefix'], '/') .'/' .trim($new['prefix'], '/')
				: trim($new['prefix'], '/');
		} else {
			$new['prefix'] = isset($old['prefix']) ? $old['prefix'] : null;
		}

		if (isset($old['middleware'])) {
			if (isset($new['middleware'])) {
				$new['middleware'] = array_merge($old['middleware'], $new['middleware']);
			} else {
				$new['middleware'] = $old['middleware'];
			}
		}

		$new['where'] = array_merge(
			isset($old['where']) ? $old['where'] : array(),
			isset($new['where']) ? $new['where'] : array()
		);

		if (isset($old['as'])) {
			$new['as'] = $old['as'] . (isset($new['as']) ? $new['as'] : '');
		}

		$attributes = Arr::except($old, array('namespace', 'prefix', 'where', 'as', 'middleware'));

		return array_merge_recursive($attributes, $new);
	}

	/**
	 * Create a new route instance.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  mixed   $action
	 * @return \Mini\Routing\Route
	 */
	protected function createRoute($methods, $uri, $action)
	{
		$methods = array_map('strtoupper', (array) $methods);

		if (in_array('GET', $methods) && ! in_array('HEAD', $methods)) {
			array_push($methods, 'HEAD');
		}

		// When the Action references a Controller, convert it to a Controller Action.
		if ($this->routingToController($action)) {
			$action = $this->convertToControllerAction($action);
		}

		// When the Action is given as a Closure, transform it on valid Closure Action.
		else if ($action instanceof Closure) {
			$action = array('uses' => $action);
		}

		// When the 'uses' is not defined into Action, find the Closure in the array.
		else if (! isset($action['uses'])) {
			$action['uses'] = $this->findActionClosure($action);
		}

		if (isset($action['middleware']) && is_string($action['middleware'])) {
			$action['middleware'] = explode('|', $action['middleware']);
		}

		if (! empty($this->groupStack)) {
			$attributes = end($this->groupStack);

			if (isset($attributes['prefix'])) {
				$uri = trim($attributes['prefix'], '/') .'/' .trim($uri, '/');
			}

			$action = $this->mergeGroup($action, $attributes);
		}

		$uri = '/'.trim($uri, '/');

		return $this->newRoute($methods, $uri, $action);
	}

	/**
	 * Create a new Route object.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  mixed  $action
	 * @return \Mini\Routing\Route
	 */
	protected function newRoute($methods, $uri, $action)
	{
		$patterns = array_merge($this->patterns, Arr::get($action, 'where', array()));

		return new Route($methods, $uri, $action, $patterns);
	}

	/**
	 * Find the Closure in an action array.
	 *
	 * @param  array  $action
	 * @return \Closure
	 */
	protected function findActionClosure(array $action)
	{
		return Arr::first($action, function($key, $value)
		{
			return is_callable($value);
		});
	}

	/**
	 * Determine if the action is routing to a controller.
	 *
	 * @param  array  $action
	 * @return bool
	 */
	protected function routingToController($action)
	{
		if ($action instanceof Closure) {
			return false;
		} else if (is_string($action)) {
			return true;
		}

		return isset($action['uses']) && is_string($action['uses']);
	}

	/**
	 * Add a controller based route action to the action array.
	 *
	 * @param  array|string  $action
	 * @return array
	 */
	protected function convertToControllerAction($action)
	{
		if (is_string($action)) {
			$action = array('uses' => $action);
		}

		if (! empty($this->groupStack)) {
			$group = end($this->groupStack);

			if (isset($group['namespace'])) {
				$action['uses'] = $group['namespace'] .'\\' .$action['uses'];
			}
		}

		$action['controller'] = $action['uses'];

		return $action;
	}

	/**
	 * Dispatch the request and return the response.
	 *
	 * @param  \Mini\Http\Request  $request
	 *
	 * @return mixed
	 */
	public function dispatch(Request $request)
	{
		$this->currentRequest = $request;

		$response = $this->dispatchToRoute($request);

		return $this->prepareResponse($request, $response);
	}

	/**
	 * Dispatch the request to a route and return the response.
	 *
	 * @param  \Mini\Http\Request  $request
	 *
	 * @return mixed
	 */
	public function dispatchToRoute(Request $request)
	{
		$route = $this->findRoute($request);

		$request->setRouteResolver(function () use ($route)
		{
			return $route;
		});

		$this->events->fire('router.matched', array($route, $request));

		$response = $this->runRouteWithinStack($route, $request);

		return $this->prepareResponse($request, $response);
	}

	/**
	 * Run the given route within a Stack "onion" instance.
	 *
	 * @param  \Mini\Routing\Route	$route
	 * @param  \Mini\Http\Request	$request
	 * @return mixed
	 */
	protected function runRouteWithinStack(Route $route, Request $request)
	{
		if (empty($middleware = $this->gatherRouteMiddlewares($route))) {
			return $this->callAction($route, $request);
		}

		$pipeline = new Pipeline($this->container);

		return $pipeline->send($request)->through($middleware)->then(function ($request) use ($route)
		{
			return $this->callAction($route, $request);
		});
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @param  \Mini\Routing\Route	$route
	 * @param  \Mini\Http\Request	$request
	 * @return mixed
	 */
	public function callAction(Route $route, Request $request)
	{
		try {
			if (! is_string($callable = $route->getCallable())) {
				$parameters = $this->resolveMethodDependencies(
					$route->parameters(), new ReflectionFunction($callable)
				);

				return call_user_func_array($callable, $parameters);
			}

			return $this->dispatchToController($route, $request);
		}
		catch (HttpResponseException $e) {
			return $e->getResponse();
		}
	}

	/**
	 * Send the request and route to controller dispatcher for handling.
	 *
	 * @param string				$action
	 * @param  \Mini\Routing\Route	$route
	 * @param  \Mini\Http\Request	$request
	 * @return mixed
	 */
	protected function dispatchToController(Route $route, Request $request)
	{
		$dispatcher = new ControllerDispatcher($this, $this->container);

		return $dispatcher->dispatch($route, $request);
	}

	/**
	 * Gather the middleware for the given route.
	 *
	 * @param  \Mini\Routing\Route  $route
	 * @return array
	 */
	public function gatherRouteMiddlewares(Route $route)
	{
		if (empty($middleware = $route->middleware())) {
			return array();
		}

		return array_map(function ($name)
		{
			return $this->resolveMiddleware($name);

		}, $middleware);
	}

	/**
	 * Resolve the middleware name to a class name preserving passed parameters.
	 *
	 * @param  string  $name
	 * @return string
	 */
	public function resolveMiddleware($name)
	{
		list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

		//
		$callable = Arr::get($this->middleware, $name, $name);

		if (empty($parameters)) {
			return $callable;
		} else if (is_string($callable)) {
			$callable .= ! empty($parameters) ? ':' .$parameters : '';

			return $callable;
		}

		return function ($passable, $stack) use ($callable, $parameters)
		{
			$parameters = array_merge(array($passable, $stack), explode(',', $parameters));

			return call_user_func_array($callable, $parameters);
		};
	}

	/**
	 * Search the routes for the route matching a request.
	 *
	 * @param  \Mini\Http\Request  $request
	 *
	 * @return \Mini\Routing\Route|null
	 */
	protected function findRoute(Request $request)
	{
		return $this->current = $this->routes->match($request);
	}

	/**
	 * Register a route matched event listener.
	 *
	 * @param  string|callable  $callback
	 * @return void
	 */
	public function matched($callback)
	{
		$this->events->listen('router.matched', $callback);
	}

	/**
	 * Get all of the defined middleware short-hand names.
	 *
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * Register a short-hand name for a middleware.
	 *
	 * @param  string  $name
	 * @param  string|\Closure  $middleware
	 * @return $this
	 */
	public function middleware($name, $middleware)
	{
		$this->middleware[$name] = $middleware;

		return $this;
	}

	/**
	 * Create a response instance from the given value.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @param  mixed  $response
	 * @return \Mini\Http\Response
	 */
	public function prepareResponse($request, $response)
	{
		if (! $response instanceof SymfonyResponse) {
			$response = new Response($response);
		}

		return $response->prepare($request);
	}

	/**
	 * Return the current Matched Route, if there are any.
	 *
	 * @return null|Route
	 */
	public function getCurrentRoute()
	{
		return $this->current();
	}

	/**
	 * Get the currently dispatched route instance.
	 *
	 * @return \Mini\Routing\Route
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Check if a Route with the given name exists.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function has($name)
	{
		return $this->routes->hasNamedRoute($name);
	}

	/**
	 * Get the current route name.
	 *
	 * @return string|null
	 */
	public function currentRouteName()
	{
		if (! is_null($route = $this->current())) {
			return $route->getName();
		}
	}

	/**
	 * Get a route parameter for the current route.
	 *
	 * @param  string  $key
	 * @param  string  $default
	 * @return mixed
	 */
	public function input($key, $default = null)
	{
		return $this->current()->parameter($key, $default);
	}

	/**
	 * Set/get a global where pattern on all routes.
	 *
	 * @param  string  $key
	 * @param  string  $pattern
	 * @return void
	 */
	public function pattern($key, $pattern = null)
	{
		if (is_null($pattern)) {
			return Arr::get($this->patterns, $key);
		}

		$this->patterns[$key] = $pattern;
	}

	/**
	 * Set/get a group of global where patterns on all routes.
	 *
	 * @param  array  $patterns
	 * @return void
	 */
	public function patterns($patterns = null)
	{
		if (is_null($patterns)) {
			return $this->patterns;
		}

		foreach ($patterns as $key => $pattern) {
			$this->patterns[$key] = $pattern;
		}
	}

	/**
	 * Determine if the router currently has a group defined.
	 *
	 * @return bool
	 */
	public function hasGroupStack()
	{
		return ! empty($this->groupStack);
	}

	/**
	 * Get the request currently being dispatched.
	 *
	 * @return \Mini\Http\Request
	 */
	public function getCurrentRequest()
	{
		return $this->currentRequest;
	}

	/**
	 * Return the available Routes.
	 *
	 * @return \Mini\Routing\RouteCollection
	 */
	public function getRoutes()
	{
		return $this->routes;
	}
}
