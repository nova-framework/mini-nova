<?php

namespace Mini\Routing;

use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Route;
use Mini\Support\Arr;

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
     * @return mixed
     */
    public function dispatchToRoute(Request $request)
    {
        $route = $this->findRoute($request);

        if (! is_null($route)) {
            $response = $route->run($request);

            return $this->prepareResponse($request, $response);
        }

        throw new NotFoundHttpException;
    }

    /**
     * Search the routes for the route matching a request.
     *
     * @param  \Mini\Http\Request  $request
     * @return Route
     */
    protected function findRoute(Request $request)
    {
        $uri = $request->path();

        $method = $request->method();

        //
        $path = ($uri === '/') ? '/' : '/' .$uri;

        $routes = $this->getRoutesByMethod($method);

        if (array_key_exists($path, $routes)) {
            $action = $routes[$path];

            $regex = '#^' .$path .'$#i';

            return $this->current = new Route($method, $path, $action, $regex);
        }

        foreach ($routes as $route => $action) {
            if (strpos($route, '{') === false) {
                continue;
            }

            $wheres = array_merge(
                $this->patterns,
                Arr::get($action, 'where', array())
            );

            $regex = static::compileRegex($route, $wheres);

            if (preg_match($regex, $path, $matches) === 1) {
                $parameters = array_filter($matches, function ($key)
                {
                    return is_string($key);

                }, ARRAY_FILTER_USE_KEY);

                return $this->current = new Route($method, $route, $action, $regex, $parameters);
            }
        }
    }

    /**
     * Compile the route's URI pattern to a valid regex.
     *
     * @param  string   $route
     * @param  array    $wheres
     * @return string
     *
     * @throw \LogicException
     */
    protected static function compileRegex($route, $wheres)
    {
        $path = '/' .ltrim($route, '/');

        //
        $regex = '#/{([a-z0-9\_\-]+)(?:(\?))?}#i';

        $params = array();

        $optionals = 0;

        $result = preg_replace_callback($regex, function ($matches) use ($path, $wheres, &$params, &$optionals)
        {
            $param = $matches[1];

            if (in_array($param, $params)) {
                $message = sprintf('Route pattern "%s" cannot reference parameter name "%s" more than once.', $path, $param);

                throw new \LogicException($message);
            }

            array_push($params, $param);

            //
            $pattern = Arr::get($wheres, $param, '[^/]+');

            if (isset($matches[2]) && ($matches[2] === '?')) {
                $prefix = '(?:';

                $optionals++;
            } else if ($optionals > 0) {
                $message = sprintf('Route pattern "%s" has standard parameter "%s" after one or more optionals.', $path, $param);

                throw new \LogicException($message);
            } else {
                $prefix = '';
            }

            return sprintf('%s/(?P<%s>%s)', $prefix, $param, $pattern);

        }, $path);

        if ($optionals > 0) {
            $result .= str_repeat(')?', $optionals);
        }

        return '#^' .$result .'$#i';
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
     * Grab all of the routes for a given request method.
     *
     * @param  string  $method
     * @return array
     */
    protected function getRoutesByMethod($method)
    {
        return Arr::get($this->routes, $method, array());
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
     * Set a global where pattern on all routes
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
