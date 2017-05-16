<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Http\Request;
use Mini\Pipeline\Pipeline;
use Mini\Routing\DependencyResolverTrait;
use Mini\Routing\Router;
use Mini\Support\Str;

use Closure;
use InvalidArgumentException;


class ControllerDispatcher
{
	use DependencyResolverTrait;

	/**
	 * The routing filterer implementation.
	 *
	 * @var \Mini\Routing\Router  $router
	 */
	protected $router;

	/**
	 * The IoC container instance.
	 *
	 * @var \Mini\Container\Container
	 */
	protected $container;

	/**
	 * Create a new controller dispatcher instance.
	 *
	 * @param  \Mini\Routing\Router $router
	 * @param  \Mini\Container\Container  $container
	 * @return void
	 */
	public function __construct(Router $router, Container $container = null)
	{
		$this->router = $router;

		$this->container = $container ?: new Container();
	}

	/**
	 * Dispatch a request to a given controller and method.
	 *
	 * @param  \Mini\Routing\Route  $route
	 * @param  \Mini\Http\Request  $request
	 * @param  string  $controller
	 * @param  string  $method
	 * @return mixed
	 */
	public function dispatch(Route $route, Request $request, $controller, $method)
	{
		$instance = $this->makeController($controller);

		return $this->callWithinStack($instance, $route, $request, $method);
	}

	/**
	 * Make a controller instance via the IoC container.
	 *
	 * @param  string  $controller
	 * @return mixed
	 */
	protected function makeController($controller)
	{
		return $this->container->make($controller);
	}

	/**
	 * Call the given controller instance method.
	 *
	 * @param  \Mini\Routing\Controller	$instance
	 * @param  \Mini\Routing\Route		$instance
	 * @param  \Mini\Http\Request		$request
	 * @param  string					$method
	 * @return mixed
	 */
	protected function callWithinStack($instance, $route, $request, $method)
	{
		$middleware = $this->getMiddleware($instance, $method);

		if (empty($middleware)) {
			return $this->call($instance, $route, $method);
		}

		$pipeline = new Pipeline($this->container);

		return $pipeline->send($request)->through($middleware)->then(function ($request) use ($instance, $route, $method)
		{
			return $this->call($instance, $route, $method);
		});
	}

	/**
	 * Get the middleware for the controller instance.
	 *
	 * @param  \Mini\Routing\Controller  $instance
	 * @param  string  $method
	 * @return array
	 */
	protected function getMiddleware($instance, $method)
	{
		$results = array();

		foreach ($instance->getMiddleware() as $name => $options) {
			if ($this->methodExcludedByOptions($method, $options)) {
				continue;
			}

			if (Str::startsWith($name, '@')) {
				$results[] = $this->resolveInstanceMiddleware($instance, $name);
			} else {
				$results[] = $this->router->resolveMiddleware($name);
			}
		}

		return $results;
	}

	/**
	 * Resolve the middleware whithin controller instance.
	 *
	 * @param  \Mini\Routing\Controller  $instance
	 * @param  string  $name
	 * @return \Closure
	 */
	protected function resolveInstanceMiddleware($instance, $name)
	{
		if (! method_exists($instance, $method = substr($name, 1))) {
			throw new InvalidArgumentException("Middleware method [$name] does not exist.");
		}

		return function ($passable, $stack) use ($instance, $method)
		{
			return call_user_func(array($instance, $method), $passable, $stack);
		};
	}

	/**
	 * Determine if the given options exclude a particular method.
	 *
	 * @param  string  $method
	 * @param  array  $options
	 * @return bool
	 */
	public function methodExcludedByOptions($method, array $options)
	{
		return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
			   (! empty($options['except']) && in_array($method, (array) $options['except']));
	}

	/**
	 * Call the given controller instance method.
	 *
	 * @param  \Nova\Routing\Controller $instance
	 * @param  \Nova\Routing\Route	  	$route
	 * @param  string					$method
	 * @return mixed
	 */
	protected function call($instance, $route, $method)
	{
		$parameters = $this->resolveClassMethodDependencies(
			$route->parameters(), $instance, $method
		);

		return $instance->callAction($method, $parameters);
	}
}
