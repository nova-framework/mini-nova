<?php

namespace Mini\Routing;

use Mini\Http\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use BadMethodCallException;


abstract class Controller
{
    /**
     * The currently called Method.
     *
     * @var mixed
     */
    private $method;


    /**
     * Method executed before any action.
     *
     * @return void
     */
    protected function before() {
        //
    }

    /**
     * Method executed after any action.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    protected function after($response)
    {
        if (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        }

        return $response;
    }

    /**
     * Execute an action on the controller.
     *
     * @param string  $method
     * @param array   $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, array $parameters = array())
    {
        $this->method = $method;

        // Execute the Before method.
        $response = $this->before();

        // If no response is given by the Before stage, execute the requested action.
        if (is_null($response)) {
            $response = call_user_func_array(array($this, $method), $parameters);
        }

        // Execute the After method and return the result.
        return $this->after($response);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function missingMethod($parameters = array())
    {
        throw new NotFoundHttpException("Controller method not found.");
    }

    /**
     * Returns the currently called Method.
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
