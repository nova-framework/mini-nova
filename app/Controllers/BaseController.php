<?php

namespace App\Controllers;

use Mini\Foundation\Auth\Access\AuthorizesRequestsTrait;
use Mini\Foundation\Validation\ValidatesRequestsTrait;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
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
				$view = 'Layouts/' .$this->layout;

				$content = View::fetch($view, array('content' => $response->render()));
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
	 * Handle a ValidationException instance.
	 *
	 * @param \Mini\Validation\ValidationException $exception
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function handleValidationException(ValidationException $exception)
	{
		if(Request::ajax() || Request::wantsJson()) {
			return Response::json(array('errors' => $exception->errors()), 422);
		}

		return Redirect::back()->withInput()->withErrors($exception->errors());
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

		// Calculate the View name from called method, which is capitalized, i.e: 'index' -> 'Index'
		$method = $caller['function'];

		$classPath = str_replace('\\', '/', static::class);

		if (preg_match('#^(.+)/Controllers/(.*)$#s', $classPath, $matches)) {
			// The path inside the Views folder, i.e: 'App\Controllers\Admin\Users' -> 'Admin/Users'
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
