<?php

namespace Mini\Routing;

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
    public function __construct()
    {
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
        $uri = '/' .ltrim($uri, '/');

        if (is_string($method) && (strtoupper($method) === 'ANY')) {
            $methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
        } else {
            $methods = array_map('strtoupper', (array) $method);
        }

        // When the Action references a Controller, convert it on a Controller Action.
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }
        // When the Action given is a Closure, convert it on a proper Closure Action.
        else if ($action instanceof Closure) {
            $action = array('uses' => $action);
        }
        // When the 'uses' is not defined into Action, find the Closure in the array.
        else if (! isset($action['uses'])) {
            $action['uses'] = $this->findClosure($action);
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

        $this->routes->addRoute($methods, $uri, $action);
    }

    /**
     * Find the Closure in an action array.
     *
     * @param  array  $action
     * @return \Closure
     */
    protected function findClosure(array $action)
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

        $response = $route->run($request);

        return $this->prepareResponse($request, $response);
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
