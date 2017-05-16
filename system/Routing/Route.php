<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Http\Exception\HttpResponseException;
use Mini\Http\Request;
use Mini\Routing\DependencyResolverTrait;
use Mini\Routing\RouteCompiler;
use Mini\Support\Arr;

use ReflectionFunction;


class Route
{
	use DependencyResolverTrait;

	/**
	 * The URI pattern the route responds to.
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 * Supported HTTP methods.
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * The action that is assigned to the route.
	 *
	 * @var mixed
	 */
	protected $action;

	/**
	 * The regular expression requirements.
	 *
	 * @var array
	 */
	protected $wheres = array();

	/**
	 * The parameters that will be passed to the route callback.
	 *
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * The parameter names for the route.
	 *
	 * @var array|null
	 */
	protected $parameterNames;

	/**
	 * The regex pattern the route responds to.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * The container instance used by the route.
	 *
	 * @var \Mini\Container\Container
	 */
	protected $container;


	/**
	 * Create a new Route instance.
	 *
	 * @param  array 	$method
	 * @param  string	$uri
	 * @param  array	 $action
	 * @param  array	 $wheres
	 */
	public function __construct(array $methods, $uri, array $action, array $wheres = array())
	{
		$this->uri		= $uri;
		$this->methods	= $methods;
		$this->action	= $action;
		$this->wheres	= $wheres;
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return mixed
	 */
	public function run(Request $request)
	{
		$this->container = $this->container ?: new Container();

		try {
			if (! is_string($this->action['uses'])) {
				return $this->runCallable($request);
			} else if ($this->customDispatcherIsBound()) {
				return $this->runWithCustomDispatcher($request);
			}

			return $this->runController($request);
		}
		catch (HttpResponseException $e) {
			return $e->getResponse();
		}
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return mixed
	 */
	protected function runCallable(Request $request)
	{
		$callable = $this->action['uses'];

		$parameters = $this->resolveMethodDependencies(
			$this->parameters(), new ReflectionFunction($callable)
		);

		return call_user_func_array($callable, $parameters);
	}

	/**
	 * Run the route action and return the response.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function runController(Request $request)
	{
		list($controller, $method) = explode('@', $this->action['uses']);

		$parameters = $this->resolveClassMethodDependencies(
			$this->parameters(), $class, $method
		);

		if (! method_exists($instance = $this->container->make($controller), $method)) {
			throw new NotFoundHttpException();
		}

		return $instance->callAction($method, $parameters);
	}

	/**
	 * Determine if a custom route dispatcher is bound in the container.
	 *
	 * @return bool
	 */
	protected function customDispatcherIsBound()
	{
		return $this->container->bound('framework.route.dispatcher');
	}

	/**
	 * Send the request and route to a custom dispatcher for handling.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return mixed
	 */
	protected function runWithCustomDispatcher(Request $request)
	{
		list($controller, $method) = explode('@', $this->action['uses']);

		$dispatcher = $this->container->make('framework.route.dispatcher');

		return $dispatcher->dispatch($this, $request, $controller, $method);
	}

	/**
	 * Checks if a request path matches the Route pattern.
	 *
	 * @param string $path
	 * @return bool
	 */
	public function matches($path)
	{
		$pattern = $this->compile();

		if (preg_match($pattern, $path, $matches) === 1) {
			$this->parameters = $this->matchToParameters($matches);

			return true;
		}

		return false;
	}

	/**
	 * Compile the Route pattern for matching.
	 *
	 * @return string
	 * @throws \LogicException
	 */
	public function compile()
	{
		if (isset($this->regex)) {
			return $this->regex;
		}

		return $this->regex = RouteCompiler::compile($this->uri, $this->wheres);
	}

	/**
	 * Get the Route parameters from the matches.
	 *
	 * @param  array  $matches
	 * @return array
	 */
	protected function matchToParameters(array $matches)
	{
		return array_filter($parameters, function($value, $key)
		{
			return is_string($key) && is_string($value) && (strlen($value) > 0);

		}, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Get or set the middlewares attached to the route.
	 *
	 * @param  array|string|null $middleware
	 * @return $this|array
	 */
	public function middleware($middleware = null)
	{
		$availMiddleware = Arr::get($this->action, 'middleware', array());

		if (is_null($middleware)) {
			return $availMiddleware;
		}

		if (is_string($middleware)) {
			$middleware = array($middleware);
		}

		$this->action['middleware'] = array_merge(
			$availMiddleware, $middleware
		);

		return $this;
	}

	/**
	 * Get a given parameter from the route.
	 *
	 * @param  string  $name
	 * @param  mixed   $default
	 * @return string
	 */
	public function parameter($name, $default = null)
	{
		$parameters = $this->parameters();

		return Arr::get($parameters, $name, $default);
	}

	/**
	 * Get the key / value list of parameters for the route.
	 *
	 * @return array
	 */
	public function parameters()
	{
		return array_map(function($value)
		{
			return is_string($value) ? rawurldecode($value) : $value;

		}, $this->parameters);
	}

	/**
	 * Set a regular expression requirement on the route.
	 *
	 * @param  array|string  $name
	 * @param  string  $expression
	 * @return $this
	 * @throws \BadMethodCallException
	 */
	public function where($name, $expression = null)
	{
		foreach ($this->parseWhere($name, $expression) as $name => $expression) {
			$this->wheres[$name] = $expression;
		}

		return $this;
	}

	/**
	 * Parse arguments to the where method into an array.
	 *
	 * @param  array|string  $name
	 * @param  string  $expression
	 * @return array
	 */
	protected function parseWhere($name, $expression)
	{
		return is_array($name) ? $name : array($name => $expression);
	}

	/**
	 * Determine if the route only responds to HTTP requests.
	 *
	 * @return bool
	 */
	public function httpOnly()
	{
		return in_array('http', $this->action, true);
	}

	/**
	 * Determine if the route only responds to HTTPS requests.
	 *
	 * @return bool
	 */
	public function httpsOnly()
	{
		return $this->secure();
	}

	/**
	 * Determine if the route only responds to HTTPS requests.
	 *
	 * @return bool
	 */
	public function secure()
	{
		return in_array('https', $this->action, true);
	}

	/**
	 * Get the regular expression requirements on the route.
	 *
	 * @return array
	 */
	public function getWheres()
	{
		return $this->wheres;
	}

	/**
	 * Get the regex for the route.
	 *
	 * @return string
	 */
	public function getRegex()
	{
		return $this->regex;
	}

	/**
	 * @return array
	 */
	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * Get the URI associated with the route.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->uri;
	}

	/**
	 * Get the uri of the route instance.
	 *
	 * @return string|null
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Get the name of the route instance.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Arr::get($this->action, 'as');
	}

	/**
	 * Get the action name for the route.
	 *
	 * @return string
	 */
	public function getActionName()
	{
		return Arr::get($this->action, 'controller', 'Closure');
	}

	/**
	 * Return the Action array.
	 *
	 * @return array
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Set the container instance on the route.
	 *
	 * @param  \Mini\Container\Container  $container
	 * @return $this
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * Dynamically access route parameters.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->parameter($key);
	}

}
