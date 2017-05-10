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
		list(, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

		// Calculate the View name from called method, which is capitalized, i.e: 'index' -> 'Index'
		$view = ucfirst($caller['function']);

		if (preg_match('#^App\\\\Controllers\\\\(.*)$#s', static::class, $matches)) {
			// The path inside the Views folder, i.e: 'App\Controllers\Admin\Users' -> 'Admin/Users'
			$path = str_replace('\\', '/', $matches[1]);

			return View::make($path .'/' .$view, $data);
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
