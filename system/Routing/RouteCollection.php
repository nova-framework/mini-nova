<?php

namespace Mini\Routing;

use Mini\Http\Request;
use Mini\Http\Response;
use Mini\Routing\Route;
use Mini\Routing\RouteCompiler;
use Mini\Routing\Router;
use Mini\Support\Arr;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


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
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = array();


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
     * @param  array         $action
     * @return void
     */
    public function addRoute($method, $uri, array $action)
    {
        if (is_string($method) && (strtoupper($method) === 'ANY')) {
            $methods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
        } else {
            $methods = array_map('strtoupper', (array) $method);
        }

        if (in_array('GET', $methods) && ! in_array('HEAD', $methods)) {
            array_push($methods, 'HEAD');
        }

        $action['methods'] = $methods;

        //
        $uri = '/' .trim(trim(Arr::get($action, 'prefix'), '/') .'/' .trim($uri, '/'), '/');

        $action['uri'] = $uri;

        foreach ($methods as $method) {
            $this->routes[$method][$uri] = $action;
        }

        $this->allRoutes[$method .$uri] = $action;
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
        $routes = $this->get($request->method());

        if (! is_null($route = $this->check($routes, $request))) {
            return $route;
        }

        // No Route match found; check for the alternate HTTP Methods.
        $others = $this->checkForAlternateMethods($request);

        if (count($others) > 0) {
            return $this->getOtherMethodsRoute($request, $others);
        }

        throw new NotFoundHttpException();
    }

    /**
     * Determine if any routes match on another HTTP verb.
     *
     * @param  \Nova\Http\Request  $request
     * @return array
     */
    protected function checkForAlternateMethods($request)
    {
        $methods = array_diff(Router::$methods, array($request->getMethod()));

        //
        $others = array();

        foreach ($methods as $method) {
            if (! is_null($route = $this->check($this->get($method), $request))) {
                $others[] = $method;
            }
        }

        return $others;
    }

    /**
     * Get a route (if necessary) that responds when other available methods are present.
     *
     * @param  \Nova\Http\Request  $request
     * @param  array  $others
     * @return \Nova\Routing\Route
     *
     * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedHttpException
     */
    protected function getOtherMethodsRoute($request, array $others)
    {
        if ($request->method() == 'OPTIONS') {
            return (new Route('OPTIONS', $request->path(), function() use ($others)
            {
                return new Response('', 200, array('Allow' => implode(',', $others)));

            }));
        }

        $this->methodNotAllowed($others);
    }

    /**
     * Throw a method not allowed HTTP exception.
     *
     * @param  array  $others
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function methodNotAllowed(array $others)
    {
        throw new MethodNotAllowedHttpException($others);
    }

    /**
     * Determine if a route in the array matches the request.
     *
     * @param  array  $routes
     * @param  \Nova\Http\Request  $request
     * @param  bool  $includingMethod
     * @return \Nova\Routing\Route|null
     */
    protected function check(array $routes, $request)
    {
        $uri = $request->path();

        $method = $request->method();

        //
        $path = ($uri === '/') ? '/' : '/' .$uri;

        // Of course literal route matches are the quickest to find, so we will check for those first.
        // If the destination key exists in the routes array we can just return that route right now.

        if (array_key_exists($path, $routes)) {
            $action = $routes[$path];

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
    }

    /**
     * Get all of the routes in the collection.
     *
     * @param  string|null  $method
     * @return array
     */
    protected function get($method = null)
    {
        if (is_null($method)) return $this->getRoutes();

        return Arr::get($this->routes, $method, array());
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

        foreach ($this->getRoutes() as $route) {
            if (isset($route['as']) && ($route['as'] === $name)) {
                return $this->names[$name] = $route;
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

        foreach ($this->getRoutes() as $route) {
            if (isset($route['controller']) && ($route['controller'] === $action)) {
                return $this->uses[$action] = $route;
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
        return array_values($this->allRoutes);
    }

}
