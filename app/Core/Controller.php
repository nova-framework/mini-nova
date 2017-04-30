<?php

namespace App\Core;

use Mini\Http\Response;
use Mini\Routing\Controller as BaseController;
use Mini\Support\Contracts\RenderableInterface;
use Mini\View\View;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;


class Controller extends BaseController
{
    /**
     * The currently called Method.
     *
     * @var mixed
     */
    protected $method;

    /**
     * The currently used Layout.
     *
     * @var string
     */
    protected $layout = 'Default';


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
        if ($response instanceof RenderableInterface) {
            if (! empty($this->layout)) {
                $view = 'Layouts/' .$this->layout;

                $content = View::fetch($view, array(
                    'content' => $response->render()
                ));
            } else {
                $content = $response->render();
            }

            return new Response($content);
        } else if (! $response instanceof SymfonyResponse) {
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
     * Returns the currently called Method.
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }
}
