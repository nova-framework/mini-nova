<?php

namespace Mini\Routing;

use Mini\Container\Container;
use Mini\Foundation\Pipeline;
use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Route;
use Mini\Routing\RouteCollection;
use Mini\Support\Arr;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;
use Closure;


class Router
{
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
     * @var \Nova\Http\Request
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
     * @var \Nova\Routing\RouteCollection;
     */
    protected $routes;

    /**
     * The current attributes being shared by routes.
     */
    protected $group;

    /**
     * All of the wheres that have been registered.
     *
     * @var array
     */
    protected $patterns = array();

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
    public function __construct(Container $container = null)
    {
        $this->container = $container?: new Container();

        $this->routes = new RouteCollection($this);
    }

    /**
     * Register a group of routes that share attributes.
     *
     * @param  array    $attributes
     * @param  Closure  $callback
     * @return void
     */
    public static function group($attributes, Closure $callback)
    {
        $this->group = $attributes;

        call_user_func($callback, $this);

        $this->group = null;
    }

    /**
     * Register a route with the router.
     *
     * <code>
     *      // Register a Route with the Router
     *      Router::match('GET', '/', function() { return 'Home!'; });
     * </code>
     *
     * @param  string|array  $methods
     * @param  string        $route
     * @param  mixed         $action
     * @return void
     */
    public function match($methods, $route, $action)
    {
        $this->addRoute($methods, $uri, $action);
    }

    /**
     * Add a route to the router.
     *
     * @param  string|array  $method
     * @param  string        $uri
     * @param  mixed         $action
     * @return void
     */
    protected function addRoute($method, $uri, $action)
    {
        if (is_string($method) && (strtoupper($method) === 'ANY')) {
            $methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
        } else {
            $methods = array_map('strtoupper', (array) $method);
        }

        // When the Action references a Controller, convert it to a Controller Action.
        if ($this->actionReferencesController($action)) {
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

        // If a group is being registered, we'll merge all of the group options into the action,
        // giving preference to the action for options that are specified in both.

        if (! is_null($this->group)) {
            $group = (array) $this->group;

            if (isset($group['prefix'])) {
                $uri = trim($group['prefix'], '/') .'/' .trim($uri, '/');
            }

            if (isset($group['namespace'])) {
                $action['uses'] = $group['namespace'] .'\\' .$action['uses'];
            }

            $action = array_merge_recursive(array_except($group, array('namespace', 'prefix')), $action);
        }

        $uri = '/' .trim($uri, '/');

        $this->routes->addRoute($methods, $uri, $action);
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param  array  $action
     * @return bool
     */
    protected function actionReferencesController($action)
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

        $response = $this->runRouteWithinStack($route, $request);

        return $this->prepareResponse($request, $response);
    }

    /**
     * Run the given route within a Stack "onion" instance.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function runRouteWithinStack(Route $route, Request $request)
    {
        $middleware = $this->gatherRouteMiddlewares($route);

        // Create a Pipeline instance.
        $pipeline = new Pipeline($this->container);

        return $pipeline->send($request)->through($middleware)->then(function ($request) use ($route)
        {
            $response = $route->run($request);

            return $this->prepareResponse($request, $response);
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
            $middleware = $this->resolveMiddleware($name);

            return (array) $middleware;

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
        $map = $this->middleware;

        list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

        // Adjust the name and the parameters.
        if (isset($map[$name])) {
            $name = $map[$name];
        }

        if ($name instanceof Closure) {
            return array('callback' => $name, 'parameters' => $parameters);
        }

        $parameters = ! is_null($parameters) ? ':' .$parameters : '';

        return $name .$parameters;
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

        return $route->setContainer($this->container);
    }

    /**
     * Create a response instance from the given value.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  mixed  $response
     * @return \Nova\Http\Response
     */
    public function prepareResponse($request, $response)
    {
        if (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        }

        return $response->prepare($request);
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
     * Get all of the registered routes.
     *
     * @return array
     */
    public function routes()
    {
        return $this->routes->getRoutes();
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
    public function hasGroup()
    {
        return ! is_null($this->group);
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
        array_unshift($parameters, $method);

        return call_user_func_array(array($this, 'addRoute'), $parameters);
    }
}
