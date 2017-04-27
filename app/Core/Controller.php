<?php

namespace App\Core;

use Mini\Http\Response;
use Mini\Routing\Controller as BaseController;
use Mini\Support\Contracts\RenderableInterface;
use Mini\View\View;


class Controller extends BaseController
{
    /**
     * The currently used Layout.
     *
     * @var string
     */
    protected $layout = 'Default';


    /**
     * Method executed after any action.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    protected function after($response)
    {
        if (! $response instanceof RenderableInterface) {
            return parent::after($response);
        }

        if (! empty($this->layout)) {
            $view = 'Layouts/' .$this->layout;

            $content = View::fetch($view, array(
                'content' => $response->render()
            ));
        } else {
            $content = $response->render();
        }

        return new Response($content);
    }

}
