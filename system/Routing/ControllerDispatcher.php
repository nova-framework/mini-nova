<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Http\Request;
use Mini\Pipeline\Pipeline;
use Mini\Routing\Router;

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
        $instance = $this->container->make($controller);

        return $this->callWithinStack($instance, $route, $request, $method);
    }

    /**
     * Call the given controller instance method.
     *
     * @param  \Mini\Routing\Controller  $instance
     * @param  \Mini\Routing\Route  $route
     * @param  \Mini\Http\Request  $request
     * @param  string  $method
     * @return mixed
     */
    protected function callWithinStack($instance, $route, $request, $method)
    {
        $parameters = $route->parameters();

        $middleware = $this->getMiddleware($instance, $method);

        //
        $pipeline = new Pipeline($this->container);

        $response = $pipeline->send($request)->through($middleware)->then(function ($request) use ($instance, $method, $parameters)
        {
            return $this->router->prepareResponse(
                $request, $instance->callAction($method, $parameters)
            );
        });

        return $response;
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
            if (! $this->methodExcludedByOptions($method, $options)) {
                $results[] = $this->router->resolveMiddleware($name);
            }
        }

        return $results;
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
