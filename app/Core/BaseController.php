<?php

namespace App\Core;

use Mini\Http\Response;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
use Mini\Support\Facades\View;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;


class BaseController extends Controller
{
    /**
     * The currently used Layout.
     *
     * @var string
     */
    protected $layout = 'Default';


    /**
     * Method executed before any action.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    protected function before() {
        //
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
        $response = $this->before();

        if (is_null($response)) {
            $response = call_user_func_array(array($this, $method), $parameters);
        }

        return $this->after($response);
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

    /**
     * Create and return a default View instance.
     *
     * @return \Nova\View\View
     * @throws \BadMethodCallException
     */
    protected function view(array $data = array())
    {
        // Get the currently called Action.
        list(, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $method = $caller['function'];

         // Transform the complete class name on a path like variable.
        $classPath = str_replace('\\', '/', static::class);

        // Check for a valid controller on Application.
        if (preg_match('#^(?:.+)/Controllers/(.*)$#s', $classPath, $matches)) {
            $view = $matches[1] .'/' .ucfirst($method);

            return View::make($view, $data);
        }

        throw new BadMethodCallException('Invalid Controller namespace: ' .static::class);
    }

    /**
     * Return the current Layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
