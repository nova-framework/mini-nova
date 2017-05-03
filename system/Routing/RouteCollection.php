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

use Countable;
use ArrayIterator;
use IteratorAggregate;


class RouteCollection implements Countable, IteratorAggregate
{
    /**
     * All of the routes that have been registered.
     *
     * @var array
     */
    protected $routes = array();

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = array();

    /**
     * The route names that have been matched.
     *
     * @var array
     */
    protected $nameList = array();

    /**
     * The actions that have been reverse routed.
     *
     * @var array
     */
    protected $actionList = array();


    /**
     * Add a route to the router.
     *
     * @param  \Mini\Routing\Route  $route
     * @return void
     */
    public function addRoute($route)
    {
        $uri = $route->getUri();

        foreach ($route->getMethods() as $method) {
            $this->routes[$method][$uri] = $route;
        }

        $this->allRoutes[] = $route;

        //
        $this->addLookups($route);

        return $route;
    }

    /**
     * Add the route to any look-up tables if necessary.
     *
     * @param  \Nova\Routing\Route  $route
     * @return void
     */
    protected function addLookups($route)
    {
        $action = $route->getAction();

        if (isset($action['as'])) {
            $name = $action['as'];

            $this->nameList[$name] = $route;
        }

        if (isset($action['controller'])) {
            $controller = $action['controller'];

            $this->addToActionList($controller, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
     *
     * @param  array  $action
     * @param  \Nova\Routing\Route  $route
     * @return void
     */
    protected function addToActionList($action, $route)
    {
        if (! isset($this->actionList[$action])) {
            $this->actionList[$action] = $route;
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
        $routes = $this->get($request->getMethod());

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
            if (! is_null($route = $this->check($this->get($method), $request, false))) {
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
    protected function check(array $routes, $request, $includingMethod = true)
    {
        $path = '/' .ltrim($request->path(), '/');

        if (! is_null($route = Arr::get($routes, $path))) {
            // We have a direct URI match, and that's good, because is the faster way.
            $route->compile(false);

            return $route;
        }

        return Arr::first($routes, function($uri, $route) use ($request, $includingMethod)
        {
            //if (preg_match('/\{([\w\?]+?)\}/', $uri) !== 1) {
            if (strpos($uri, '{') === false) {
                // The Routes with no named parameters was already checked previously.
                return false;
            }

            return $route->matches($request, $includingMethod);
        });
    }

    /**
     * Get all of the routes in the collection.
     *
     * @param  string|null  $method
     * @return array
     */
    protected function get($method = null)
    {
        if (is_null($method)) {
            return $this->getRoutes();
        }

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
        return Arr::get($this->nameList, $name);
    }

    /**
     * Find the route that uses the given action.
     *
     * @param  string  $action
     * @return array
     */
    public function getByAction($action)
    {
        return Arr::get($this->actionList, $action);
     }

    /**
     * Get all of the registered routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->allRoutes;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getRoutes());
    }
}
