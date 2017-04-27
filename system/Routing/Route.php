<?php

namespace Mini\Routing;

use Mini\Http\Exception\HttpResponseException;
use Mini\Http\Request;
use Mini\Support\Arr;


class Route
{
    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $uri;

    /**
     * The request method the route responds to.
     *
     * @var string
     */
    protected $method;

    /**
     * The action that is assigned to the route.
     *
     * @var mixed
     */
    protected $action;

    /**
     * The parameters that will be passed to the route callback.
     *
     * @var array
     */
    protected $parameters;

    /**
     * The regex pattern the route responds to.
     *
     * @var string
     */
    protected $regex;


    /**
     * Create a new Route instance.
     *
     * @param  string        $method
     * @param  string        $uri
     * @param  array         $action
     * @param  string|null   $regex
     * @param  array         $parameters
     */
    public function __construct($method, $uri, $action, $parameters = array(), $regex = null)
    {
        $this->uri        = $uri;
        $this->method     = $method;
        $this->action     = $action;
        $this->parameters = $parameters;

        // If no regex value is given, because the route is a direct match, we fallback to URI.
        $this->regex = $regex ?: $uri;

    }

    /**
     * Run the route action and return the response.
     *
     * @param  \Mini\Http\Request  $request
     * @return mixed
     */
    public function run(Request $request)
    {
        try {
            if (! is_string($this->action['uses'])) {
                return $this->runCallable($request);
            }

            return $this->runController($request);
        }
        catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Run the route action and return the response.
     *
     * @param  \Mini\Http\Request  $request
     * @return mixed
     */
    protected function runCallable(Request $request)
    {
        $parameters = $this->parameters();

        //
        $callable = $this->action['uses'];

        return call_user_func_array($callable, $parameters);
    }

    /**
     * Run the route action and return the response.
     *
     * @param  \Mini\Http\Request  $request
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function runController(Request $request)
    {
        $parameters = $this->parameters();

        //
        list($controller, $method) = explode('@', $this->action['uses']);

        if (! method_exists($instance = new $controller(), $method)) {
            throw new NotFoundHttpException;
        }

        return $instance->callAction($method, $parameters);
    }

    /**
     * Get the uri for the route.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Get the method for the route.
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Get the action array for the route.
     *
     * @return array
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * Get a given parameter from the route.
     *
     * @param  string  $name
     * @param  mixed   $default
     * @return string
     */
    public function parameter($name, $default = null)
    {
        $parameters = $this->parameters();

        return Arr::get($parameters, $name, $default);
    }

    /**
     * Get the key / value list of parameters for the route.
     *
     * @return array
     */
    public function parameters()
    {
        return array_map(function($value)
        {
            return is_string($value) ? rawurldecode($value) : $value;

        }, $this->parameters);
    }

    /**
     * Get the regex for the route.
     *
     * @return string
     */
    public function regex()
    {
        return $this->regex;
    }

    /**
     * Dynamically access route parameters.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->parameter($key);
    }

}
