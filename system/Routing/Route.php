<?php

namespace Mini\Routing;

use Mini\Http\Exception\HttpResponseException;
use Mini\Http\Request;


class Route
{
    /**
     * The URI the route responds to.
     *
     * @var string
     */
    public $uri;

    /**
     * The request method the route responds to.
     *
     * @var string
     */
    public $method;

    /**
     * The action that is assigned to the route.
     *
     * @var mixed
     */
    public $action;

    /**
     * The parameters that will be passed to the route callback.
     *
     * @var array
     */
    public $parameters;

    /**
     * The regex the route responds to.
     *
     * @var string
     */
    public $regex;


    /**
     * Create a new Route instance.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array   $action
     * @param  array   $regex
     * @param  array   $parameters
     */
    public function __construct($method, $uri, $action, $regex, $parameters = array())
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->action = $action;
        $this->regex  = $regex;

        //
        $this->parameters = $parameters;
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
     * Get the key / value list of parameters for the route.
     *
     * @return array
     *
     * @throws \LogicException
     */
    public function parameters()
    {
        return array_map(function($value)
        {
            return is_string($value) ? rawurldecode($value) : $value;

        }, $this->parameters);
    }
}
