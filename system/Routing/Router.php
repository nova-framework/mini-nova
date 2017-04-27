<?php

namespace Mini\Routing;

use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Route;
use Mini\Routing\RouteCompiler;
use Mini\Support\Arr;
use Mini\Support\Str;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * All of the routes that have been registered.
     *
     * @var array
     */
    protected $routes = array(
        'GET'    => array(),
        'POST'   => array(),
        'PUT'    => array(),
        'DELETE' => array(),
        'PATCH'  => array(),
        'HEAD'   => array(),
        'OPTIONS'=> array(),
    );

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
    public static $methods = array('GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS');


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
     *      Router::register('GET', '/', function() { return 'Home!'; });
     * </code>
     *
     * @param  string|array  $method
     * @param  string        $route
     * @param  mixed         $action
     * @return void
     */
    public function register($method, $route, $action)
    {
        $uri = ($route == '/') ? '/' : '/' .ltrim($route, '/');

        if (is_string($method) && (strtoupper($method) === 'ANY')) {
            $methods = static::$methods;
        } else {
            $methods = array_map('strtoupper', (array) $method);
        }

        if (in_array('GET', $methods) && ! in_array('HEAD', $methods)) {
            array_push($methods, 'HEAD');
        }

        foreach ($methods as $method) {
            $this->addRoute($method, $uri, $action);
        }
    }

    /**
     * Add a route to the router.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  mixed   $action
     * @return void
     */
    protected function addRoute($method, $uri, $action)
    {
        $this->routes[$method][$uri] = $this->parseAction($action);

        if (! is_null($this->group)) {
            $this->routes[$method][$uri] += $this->group;
        }
    }

    /**
     * Convert a route action to a valid action array.
     *
     * @param  mixed  $action
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_callable($action) || is_string($action)) {
            return array('uses' => $action);
        }

        // Dig through the array to find a Closure instance.
        else if (! isset($action['uses'])) {
            $action['uses'] = $this->findClosure($action);
        }

        return $action;
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
     * Dispatch the request and return the response.
     *
     * @param  \Mini\Http\Request  $request
     *
     * @return mixed
     */
    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;

        try {
            $response = $this->dispatchToRoute($request);
        }
        catch (NotFoundHttpException $e) {
            $response = new Response('Page not found', 404);
        }

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

        if (is_null($route)) {
            throw new NotFoundHttpException();
        }

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
        $method = $request->method();

        // Get the routes registered for the current HTTP method.
        $routes = Arr::get($this->routes, $method, array());

        // Prepare a qualified URI path, which starts always with '/'.
        $uri = $request->path();

        $uri = ($uri === '/') ? '/' : '/' .$uri;

        // Of course literal route matches are the quickest to find, so we will check for those first.
        // If the destination key exists in the routes array we can just return that route right now.

        if (array_key_exists($uri, $routes)) {
            $action = $routes[$uri];

            return $this->current = new Route($method, $uri, $action);
        }

        // If we can't find a literal match we'll iterate through all of the registered routes to find
        // a matching route based on the regex pattern generated from route's parameters and patterns.
        if (! is_null($route = $this->matchRoute($method, $uri))) {
            return $route;
        }
    }

    /**
     * Iterate through every route to find a matching route.
     *
     * @param  string  $method
     * @param  string  $uri
     *
     * @return \Mini\Routing\Route|null
     */
    protected function matchRoute($method, $uri)
    {
        // Get the routes registered for the current HTTP method.
        $routes = Arr::get($this->routes, $method, array());

        foreach ($routes as $route => $action) {
            // We only need to check routes which have parameters since all others would have been able
            // to be matched by the search for literal matches we just did before we started searching.
            if (! Str::contains($route, '{')) {
                continue;
            }

            // Prepare the route patterns.
            $patterns = array_merge($this->patterns, Arr::get($action, 'where', array()));

            // Prepare the route pattern.
            $pattern = RouteCompiler::compile($route, $patterns);

            if (preg_match('#^' .$pattern .'$#i', $uri, $matches) === 1) {
                // Filter the route parameters from matches.
                $parameters = array_filter($matches, function($value)
                {
                    return is_string($value);

                }, ARRAY_FILTER_USE_KEY);

                return $this->current = new Route($method, $route, $action, $parameters, $pattern);
            }
        }
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
        return $this->routes;
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
     * Set a global where pattern on all routes.
     *
     * @param  string  $key
     * @param  string  $pattern
     * @return void
     */
    public function pattern($key, $pattern)
    {
        $this->patterns[$key] = $pattern;
    }

    /**
     * Set a group of global where patterns on all routes.
     *
     * @param  array  $patterns
     * @return void
     */
    public function patterns(array $patterns)
    {
        foreach ($patterns as $key => $pattern) {
            $this->pattern($key, $pattern);
        }
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

        return call_user_func_array(array($this, 'register'), $parameters);
    }
}
