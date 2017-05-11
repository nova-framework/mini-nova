<?php

namespace Mini\Foundation\Http;

use Mini\Http\Contracts\KernelInterface;
use Mini\Foundation\Application;
use Mini\Pipeline\Pipeline;
use Mini\Routing\Router;
use Mini\Support\Facades\Facade;

use Symfony\Component\Debug\Exception\FatalThrowableError;

use Closure;
use Exception;


class Kernel implements KernelInterface
{
	/**
	 * The Application instance.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * The Router instance.
	 *
	 * @var \Routing\Router
	 */
	protected $router;

	/**
	 * The application's middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = array();

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = array();


	/**
	 * Create a new HTTP kernel instance.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app, Router $router)
	{
		$this->app = $app;

		$this->router = $router;

		foreach($this->routeMiddleware as $name => $middleware) {
			$this->router->middleware($name, $middleware);
		}
	}

	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return \Mini\Http\Response
	 */
	public function handle($request)
	{
		try {
			$request->enableHttpMethodParameterOverride();

			$response = $this->sendRequestThroughRouter($request);
		}
		catch (\Exception $e) {
			$this->reportException($e);

			$response = $this->renderException($request, $e);
		}
		catch (\Throwable $e) {
			$e = new FatalThrowableError($e);

			$this->reportException($e);

			$response = $this->renderException($request, $e);
		}

		$this->app['events']->fire('kernel.handled', array($request, $response));

		return $response;
	}

	/**
	 * Send the given request through the middleware / router.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	protected function sendRequestThroughRouter($request)
	{
		$this->app->instance('request', $request);

		Facade::clearResolvedInstance('request');

		$this->bootstrap();

		//
		$pipeline = new Pipeline($this->app);

		return $pipeline->send($request)->through($this->middleware)->then(function ($request)
		{
			$this->app->instance('request', $request);

			return $this->router->dispatch($request);
		});
	}

	/**
	 * Call the terminate method on any terminable middleware.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Mini\Http\Response  $response
	 * @return void
	 */
	public function terminate($request, $response)
	{
		$middlewares = array_merge(
			$this->gatherRouteMiddlewares($request),
			$this->middleware
		);

		foreach ($middlewares as $middleware) {
			if (! is_string($middleware)) {
				continue;
			}

			list($name, $parameters) = $this->parseMiddleware($middleware);

			$instance = $this->app->make($name);

			if (method_exists($instance, 'terminate')) {
				$instance->terminate($request, $response);
			}
		}

		$this->app->terminate($request, $response);
	}

	/**
	 * Gather the route middleware for the given request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function gatherRouteMiddlewares($request)
	{
		if (! is_null($route = $request->route())) {
			return $this->router->gatherRouteMiddlewares($route);
		}

		return array();
	}

	/**
	 * Parse a middleware string to get the name and parameters.
	 *
	 * @param  string  $middleware
	 * @return array
	 */
	protected function parseMiddleware($middleware)
	{
		list($name, $parameters) = array_pad(explode(':', $middleware, 2), 2, array());

		if (is_string($parameters)) {
			$parameters = explode(',', $parameters);
		}

		return array($name, $parameters);
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 *
	 * @return void
	 */
	public function bootstrap()
	{
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($this->app);

		$this->app->boot();
	}

	/**
	 * Determine if the kernel has a given middleware.
	 *
	 * @param  string  $middleware
	 * @return bool
	 */
	public function hasMiddleware($middleware)
	{
		return in_array($middleware, $this->middleware);
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function reportException(Exception $e)
	{
		$this->getExceptionHandler()->report($e);
	}

	/**
	 * Render the exception to a response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderException($request, Exception $e)
	{
		return $this->getExceptionHandler()->render($request, $e);
	}

	/**
	 * Get the Nova application instance.
	 *
	 * @return \Mini\Foundation\Contracts\ExceptionHandlerInterface
	 */
	public function getExceptionHandler()
	{
		return $this->app['Mini\Foundation\Contracts\ExceptionHandlerInterface'];
	}

	/**
	 * Get the Nova application instance.
	 *
	 * @return \Mini\Foundation\Application
	 */
	public function getApplication()
	{
		return $this->app;
	}
}
