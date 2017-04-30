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
     * The currently used Layout.
     *
     * @var string
     */
    protected $layout = 'Default';


    /**
     * Execute an action on the controller.
     *
     * @param string  $method
     * @param array   $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, array $parameters = array())
    {
        $response = parent::callAction($method, $parameters);

        return $this->processResponse($response);
    }

    /**
     * Method executed after any action.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    protected function processResponse($response)
    {
        if ($response instanceof RenderableInterface) {
            if (! empty($this->layout)) {
                $view = 'Layouts/' .$this->layout;

                $content = View::fetch($view, array('content' => $response->render()));
            } else {
                $content = $response->render();
            }

            return new Response($content);
        } else if (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        }

        return $response;
    }

}
