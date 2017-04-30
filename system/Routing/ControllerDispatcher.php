<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Http\Request;
use Mini\Pipeline\Pipeline;
use Mini\Routing\Router;
use Mini\Support\Str;

use Closure;


class ControllerDispatcher
{
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
        $parameters = $route->parameters();

        // Create a Controller instance using the IoC container.
        $instance = $this->container->make($controller);

        // Gather the controller's middleware.
        $middleware = $this->getMiddleware($instance, $method);

        if (empty($middleware)) {
            return $this->call($instance, $request, $method, $parameters);
        }

        return $this->callWithinStack($instance, $middleware, $request, $method, $parameters);
    }

    /**
     * Call the given controller instance method.
     *
     * @param  \Mini\Routing\Controller $instance
     * @param  array                    $middleware
     * @param  \Mini\Routing\Route      $route
     * @param  \Mini\Http\Request       $request
     * @param  string                   $method
     * @return mixed
     */
    protected function callWithinStack($instance, $middleware, $request, $method, $parameters)
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline->send($request)->through($middleware)->then(function ($request) use ($instance, $method, $parameters)
        {
            return $this->call($instance, $request, $method, $parameters);
        });
    }

    /**
     * Call the given controller instance method.
     *
     * @param  \Nova\Routing\Controller $instance
     * @param  \Nova\Routing\Route      $route
     * @param  string                   $method
     * @param  array                    $parameters
     * @return mixed
     */
    protected function call($instance, $request, $method, $parameters)
    {
        $response = $instance->callAction($method, $parameters);

        return $this->router->prepareResponse($request, $response);
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
            throw new \InvalidArgumentException("Middleware method [$name] does not exist.");
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

}
