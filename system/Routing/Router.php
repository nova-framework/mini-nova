<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Events\DispatcherInterface;
use Mini\Pipeline\Pipeline;
use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\ResourceRegistrar;
use Mini\Routing\Route;
use Mini\Routing\RouteCollection;
use Mini\Support\Arr;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;
use Closure;


class Router
{
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
	 * The resource registrar instance.
	 *
	 * @var \Mini\Routing\ResourceRegistrar
	 */
	protected $registrar;


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
	 * Register a new route responding to all verbs.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Mini\Routing\Route
	 */
	public function any($route, $action)
	{
		$methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');

		return $this->addRoute($methods, $route, $action);
	}

	/**
	 * Register a route with the router.
	 *
	 * @param  string|array  $methods
	 * @param  string		$route
	 * @param  mixed		 $action
	 * @return void
	 */
	public function match($methods, $route, $action)
	{
		return $this->addRoute($methods, $uri, $action);
	}

	/**
	 * Route a resource to a controller.
	 *
	 * @param  string  $name
	 * @param  string  $controller
	 * @param  array   $options
	 * @return void
	 */
	public function resource($name, $controller, array $options = array())
	{
		$registrar = $this->getRegistrar();

		$registrar->register($name, $controller, $options);
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
		if (! empty($this->groupStack)) {
			$attributes = static::mergeGroup($attributes, end($this->groupStack));
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
	protected static function mergeGroup($new, $old)
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

		$new['where'] = array_merge(
			isset($old['where']) ? $old['where'] : array(),
			isset($new['where']) ? $new['where'] : array()
		);

		if (isset($old['as'])) {
			$new['as'] = $old['as'] . (isset($new['as']) ? $new['as'] : '');
		}

		return array_merge_recursive(Arr::except($old, array('namespace', 'prefix', 'where', 'as')), $new);
	}

	/**
	 * Add a route to the router.
	 *
	 * @param  string|array  $method
	 * @param  string		$uri
	 * @param  mixed		 $action
	 * @return void
	 */
	protected function addRoute($method, $uri, $action)
	{
		$route = $this->createRoute($method, $uri, $action);

		return $this->routes->addRoute($route);
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
			$action['uses'] = Arr::first($action, function($key, $value)
			{
				return is_callable($value);
			});
		}

		if (! empty($this->groupStack)) {
			$action = static::mergeGroup($action, end($this->groupStack));
		}

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

		$route = new Route($methods, $uri, $action, $patterns);

		return $route->setContainer($this->container);
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
		}

		return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
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

		$request->setRouteResolver(function() use ($route)
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
	 * @param  \Illuminate\Routing\Route	$route
	 * @param  \Illuminate\Http\Request		$request
	 * @return mixed
	 */
	protected function runRouteWithinStack(Route $route, Request $request)
	{
		$middleware = $this->gatherRouteMiddlewares($route);

		if (empty($middleware)) {
			return $route->run($request);
		}

		$pipeline = new Pipeline($this->container);

		return $pipeline->send($request)->through($middleware)->then(function ($request) use ($route)
		{
			return $route->run($request);
		});
	}

	/**
	 * Gather the middleware for the given route.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return array
	 */
	public function gatherRouteMiddlewares(Route $route)
	{
		return array_map(function ($name)
		{
			return $this->resolveMiddleware($name);

		}, $route->middleware());
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

		if (is_string($callable)) {
			$parameters = ! empty($parameters) ? ':' .$parameters : '';

			return $callable .$parameters;
		}

		// A closure with no parameters do not need addditional processing.
		else if (empty($parameters)) {
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
		$this->current = $route = $this->routes->match($request);

		return $this->substituteBindings($route);
	}

	/**
	 * Substitute the route bindings onto the route.
	 *
	 * @param  \Mini\Routing\Route  $route
	 * @return \Mini\Routing\Route
	 */
	protected function substituteBindings($route)
	{
		foreach ($route->parameters() as $key => $value) {
			if (isset($this->binders[$key])) {
				$route->setParameter($key, $this->performBinding($key, $value, $route));
			}
		}

		return $route;
	}

	/**
	 * Call the binding callback for the given key.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @param  \Mini\Routing\Route  $route
	 * @return mixed
	 */
	protected function performBinding($key, $value, $route)
	{
		return call_user_func($this->binders[$key], $value, $route);
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
	 * Register a model binder for a wildcard.
	 *
	 * @param  string  $key
	 * @param  string  $model
	 * @param  \Closure  $callback
	 * @return void
	 *
	 * @throws NotFoundHttpException
	 */
	public function model($key, $model, Closure $callback = null)
	{
		$this->bind($key, function($value) use ($className, $callback)
		{
			if (is_null($value)) {
				return null;
			}

			if ($model = call_user_func(array($model, 'find'), $value)) {
				return $model;
			} else if ($callback instanceof Closure) {
				return call_user_func($callback);
			}

			throw new NotFoundHttpException;
		});
	}

	/**
	 * Add a new route parameter binder.
	 *
	 * @param  string  $key
	 * @param  string|callable  $binder
	 * @return void
	 */
	public function bind($key, $binder)
	{
		if (is_string($binder)) {
			$binder = $this->createClassBinding($binder);
		}

		$key = str_replace('-', '_', $key);

		$this->binders[$key] = $binder;
	}

	/**
	 * Create a class based binding using the IoC container.
	 *
	 * @param  string	$binding
	 * @return \Closure
	 */
	public function createClassBinding($binding)
	{
		return function($value, $route) use ($binding)
		{
			$segments = explode('@', $binding);

			$method = (count($segments) == 2) ? $segments[1] : 'bind';

			$callable = array($this->container->make($segments[0]), $method);

			return call_user_func($callable, $value, $route);
		};
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
	 * Get a Resource Registrar instance.
	 *
	 * @return \Mini\Routing\ResourceRegistrar
	 */
	public function getRegistrar()
	{
		return $this->registrar ?: $this->registrar = new ResourceRegistrar($this);
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

	/**
	 * Dynamically handle calls into the query instance.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (in_array(strtoupper($method), static::$methods)) {
			array_unshift($parameters, $method);

			return call_user_func_array(array($this, 'addRoute'), $parameters);
		}

		throw new BadMethodCallException("Method [$method] does not exist.");
	}
}
