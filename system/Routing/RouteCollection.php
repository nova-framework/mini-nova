<?php

namespace Mini\Routing;

use Mini\Http\Request;
use Mini\Routing\Route;
use Mini\Routing\RouteCompiler;
use Mini\Routing\Router;
use Mini\Support\Arr;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class RouteCollection
{
    /**
     * The instance of Router.
     *
     * @var \Mini\Routing\Router;
     */
    protected $router;

    /**
     * The route names that have been matched.
     *
     * @var array
     */
    protected $names = array();

    /**
     * The actions that have been reverse routed.
     *
     * @var array
     */
    protected $uses = array();

    /**
     * All of the routes that have been registered.
     *
     * @var array
     */
    protected $routes = array(
        'GET'    => array(),
        'HEAD'   => array(),
        'POST'   => array(),
        'PUT'    => array(),
        'PATCH'  => array(),
        'DELETE' => array(),
        'OPTIONS'=> array(),
    );


    /**
     * Construct a new RouteCollection instance.
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Add a route to the router.
     *
     * @param  string|array  $method
     * @param  string        $uri
     * @param  mixed         $action
     * @return void
     */
    public function addRoute($method, $uri, $action)
    {
        if (is_string($method) && (strtoupper($method) === 'ANY')) {
            $methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
        } else {
            $methods = array_map('strtoupper', (array) $method);
        }

        if (in_array('GET', $methods) && ! in_array('HEAD', $methods)) {
            array_push($methods, 'HEAD');
        }

        foreach ($methods as $method) {
            $this->routes[$method][$uri] = $action;
        }
    }

    /**
     * Iterate through every route to find a matching route.
     *
     * @param \Mini\Http\Request $request
     *
     * @return \Mini\Routing\Route|null
     */
    public function match(Request $request)
    {
        // Prepare a qualified URI path, which starts always with '/'.
        $uri = $request->path();

        $path = ($uri === '/') ? '/' : '/' .$uri;

        //
        $method = $request->method();

        $routes = Arr::get($this->routes, $method, array());

        // Of course literal route matches are the quickest to find, so we will check for those first.
        // If the destination key exists in the routes array we can just return that route right now.

        if (array_key_exists($path, $routes)) {
            $action = $routes[$uri];

            return new Route($method, $path, $action);
        }

        // If we can't find a literal match we'll iterate through all of the registered routes to find
        // a matching route based on the regex pattern generated from route's parameters and patterns.

        foreach ($routes as $route => $action) {
            // We only need to check routes which have parameters since all others would have been able
            // to be matched by the search for literal matches we just did before we started searching.

            //if (preg_match('/\{([\w\?]+?)\}/', $route) !== 1) {
            if (strpos($route, '{') === false) {
                continue;
            }

            // Prepare the patterns used for route compilation.
            $wheres = Arr::get($action, 'where', array());

            $patterns = array_merge($this->router->patterns(), $wheres);

            // Prepare the route pattern.
            $pattern = RouteCompiler::compile($route, $patterns);

            if (preg_match($pattern, $path, $matches) === 1) {
                // Retrieve the parameters from matches, looking for the string keys.
                $parameters = array_filter($matches, function ($value)
                {
                    return is_string($value);

                }, ARRAY_FILTER_USE_KEY);

                return new Route($method, $route, $action, $parameters, $pattern);
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Determine if the route collection contains a given named route.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasNamedRoute($name)
    {
        return ! is_null($this->getByName($name));
    }
    
    /**
     * Find a route by the route's assigned name.
     *
     * @param  string  $name
     * @return array
     */
    public function getByName($name)
    {
        if (isset($this->names[$name])) {
            return $this->names[$name];
        }

        // To find a named route, we will iterate through every route defined for the application.
        // We will cache the routes by name so we can load them very quickly the next time.
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route => $options) {
                if (isset($options['as']) && ($options['as'] === $name)) {
                    $options['method'] = $method;

                    return $this->names[$name] = array($route => $options);
                }
            }
        }
    }

    /**
     * Find the route that uses the given action.
     *
     * @param  string  $action
     * @return array
     */
    public function getByAction($action)
    {
        if (isset($this->uses[$action])) {
            return $this->uses[$action];
        }

        // To find the route, we'll simply spin through the routes looking for a route with a
        // "uses" key matching the action, and if we find one, we cache and return it.
         foreach ($this->routes as $method => $routes)  {
            foreach ($routes as $route => $options) {
                if (isset($options['controller']) && ($options['controller'] === $action)) {
                    $options['method'] = $method;

                    return $this->uses[$action] = array($route => $options);
                }
            }
        }
    }

    /**
     * Get all of the registered routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

}
