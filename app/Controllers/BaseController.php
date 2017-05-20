<?php

namespace App\Controllers;

use Mini\Foundation\Auth\Access\AuthorizesRequestsTrait;
use Mini\Foundation\Validation\ValidatesRequestsTrait;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Request;
use Mini\Support\Facades\Response;
use Mini\Support\Facades\View;
use Mini\Validation\ValidationException;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;


class BaseController extends Controller
{
	use AuthorizesRequestsTrait, ValidatesRequestsTrait;

	/**
	 * The currently used Theme.
	 *
	 * @var string
	 */
	protected $theme;

	/**
	 * The currently used Layout.
	 *
	 * @var string
	 */
	protected $layout = 'Default';


	/**
	 * Create a new Controller instance.
	 */
	public function __construct()
	{
		// Setup the used Theme to default, if it is not already defined.
		if (! isset($this->theme)) {
			$this->theme = Config::get('app.theme', 'Bootstrap');
		}
	}

	/**
	 * Execute an action on the controller.
	 *
	 * @param string  $method
	 * @param array   $params
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function callAction($method, array $parameters)
	{
		$response = call_user_func_array(array($this, $method), $parameters);

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
				$view = $this->theme .'::Layouts/' .$this->layout;

				$content = View::fetch($view, array('content' => $response));
			} else {
				$content = $response->render();
			}

			return Response::make($content);
		} else if (! $response instanceof SymfonyResponse) {
			return Response::make($response);
		}

		return $response;
	}

	/**
	 * Create and return a default View instance.
	 *
	 * @return \Nova\View\View
	 * @throws \BadMethodCallException
	 */
	protected function getView(array $data = array())
	{
		list(, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

		$method = $caller['function'];

		$classPath = str_replace('\\', '/', static::class);

		if (preg_match('#^(.+)/Controllers/(.*)$#s', $classPath, $matches)) {
			$hint = ($matches[1] !== 'App') ? $matches[1] .'::' : null;

			$path = str_replace('\\', '/', $matches[2]);

			$view = $hint .$path .'/' .ucfirst($method);

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
