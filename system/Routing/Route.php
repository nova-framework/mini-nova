<?php

namespace Mini\Routing;

use Mini\Http\Exception\HttpResponseException;
use Mini\Http\Request;
use Mini\Support\Arr;


class Route
{
    /**
     * The URI the route responds to.
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
     * The regex the route responds to.
     *
     * @var string
     */
    protected $regex;


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
     * Compile an URI pattern to a valid regex.
     *
     * @param  string   $uri
     * @param  array    $patterns
     * @return string
     *
     * @throw \LogicException
     */
    public static function compile($uri, $patterns = array())
    {
        $path = '/' .ltrim($uri, '/');

        //
        $params = array();

        $optionals = 0;

        $result = preg_replace_callback('#/{(\w+)(?:(\?))?}#i', function ($matches) use ($path, $patterns, &$params, &$optionals)
        {
            $param = $matches[1];

            if (in_array($param, $params)) {
                $message = sprintf('Route pattern "%s" cannot reference parameter name "%s" more than once.', $path, $param);

                throw new \LogicException($message);
            }

            array_push($params, $param);

            //
            $pattern = Arr::get($patterns, $param, '[^/]+');

            if (isset($matches[2]) && ($matches[2] === '?')) {
                $prefix = '(?:';

                $optionals++;
            } else if ($optionals > 0) {
                $message = sprintf('Route pattern "%s" cannot have standard parameter "%s" after optionals.', $path, $param);

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
