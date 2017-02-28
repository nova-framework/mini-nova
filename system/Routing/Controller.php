<?php

namespace Mini\Routing;

use Mini\View\View;


abstract class Controller
{
    /**
     * The currently used Layout.
     *
     * @var string
     */
    protected $layout = 'Default';


    public function __construct()
    {
        //
    }

    public function callAction($method, array $params = array())
    {
        if (! method_exists($this, $method)) {
            throw new \BadMethodCallException("Method [$method] does not exist");
        }

        $response = call_user_func_array(array($this, $method), $params);

        if ($response instanceof View) {
            $layout = 'Layouts/' .$this->layout;

            $view = View::make($layout, array(
                'content' => $response->render()
            ));

            echo $view->render();
        }
    }
}
