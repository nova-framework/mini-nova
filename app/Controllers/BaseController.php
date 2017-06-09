<?php

namespace App\Controllers;

use Mini\Foundation\Auth\Access\AuthorizesRequestsTrait;
use Mini\Foundation\Bus\DispatchesCommandsTrait;
use Mini\Foundation\Validation\ValidatesRequestsTrait;
use Mini\Http\Response;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Request;
use Mini\Support\Facades\View;
use Mini\Support\Str;
use Mini\Validation\ValidationException;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use BadMethodCallException;


class BaseController extends Controller
{
	use AuthorizesRequestsTrait, ValidatesRequestsTrait, DispatchesCommandsTrait;

	/**
	 * The currently called action.
	 *
	 * @var string
	 */
	protected $action;

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
	 * True when the auto-rendering is active.
	 *
	 * @var bool
	 */
	protected $autoRender = true;

	/**
	 * True when the auto-layouting is active.
	 *
	 * @var bool
	 */
	protected $autoLayout = true;

	/**
	 * The View variables.
	 *
	 * @var array
	 */
	protected $viewVars = array();

	/**
	 * The (alternative) Response instance.
	 *
	 * @var \Mini\Http\Response|null
	 */
	protected $response;


	/**
	 * Create a new Controller instance.
	 */
	public function __construct()
	{
		// Setup the used Theme to default, if it is not already defined.
		if (is_null($this->theme)) {
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
		$this->action = $method;

		//
		$this->before();

		$response = call_user_func_array(array($this, $method), $parameters);

		return $this->after($response);
	}

	/**
	 * Method executed before any action.
	 *
	 * @return void
	 */
	protected function before()
	{
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
		$response = $response ?: $this->response;

		if (! $this->autoRender()) {
			return $this->prepareResponse($response);
		} else if (is_null($response)) {
			$response = $this->createView();
		}

		if (($response instanceof RenderableInterface) && $this->autoLayout()) {
			return $this->renderWhithinLayout($response);

		}

		return $this->prepareResponse($response);
	}

	/**
	 * Internally redirects one action to another.
	 *
	 * @param string $action The new action to be 'redirected' to.
	 * @return mixed Returns the return value of the called action
	*/
	public function setAction($action)
	{
		$this->action = $action;

		$parameters = array_shift(func_get_args());

		return call_user_func_array(array($this, $action), $parameters);
	}

	/**
	 * Instantiates the correct View, hands it its data, and uses it to render the view output.
	 *
	 * @param string $view View to use for rendering
	 * @param string $layout Layout to use
	 * @return \Mini\Http\Response A response object containing the rendered view.
	 */
	public function render($view = null, $layout = null)
	{
		$this->autoRender = false;

		if (is_null($view)) {
			$view = $this->getView();
		} else if (Str::startsWith($view, '/')) {
			$view = ltrim($view, '/');
		} else if (! Str::contains($view, '::')) {
			$view = $this->getView($view);
		}

		$view = View::make($view, $this->viewVars);

		if ($this->autoLayout()) {
			$response = $this->renderWhithinLayout($view, $layout);
		} else {
			$response = new Response($view);
		}

		return $this->response = $response;
	}

	/**
	 * Handle a RenderableInterface implementation.
	 *
	 * @param \Mini\Support\Contracts\RenderableInterface  $view
	 * @return \Mini\Http\Response
	 */
	protected function renderWhithinLayout(RenderableInterface $renderable, $layout = null)
	{
		$layout = $layout ?: $this->layout;

		// Convert the used theme to a View namespace.
		$namespace = ! empty($this->theme) ? $this->theme .'::' : '';

		// Compute the name of View used as layout.
		$view = sprintf('%sLayouts/%s', $namespace, $layout);

		// Compute the composite View data.
		$data = array_merge($this->viewVars, array(
			'content' => $renderable
		));

		// Create and render the layout's View.
		$content = View::make($view, $data)->render();

		return new Response($content);
	}

	/**
	 * Create and return a (default) View instance.
	 *
	 * @param  array  $data
	 * @param  string}null  $custom
	 * @return \Nova\View\View
	 */
	protected function createView(array $data = array(), $view = null)
	{
		$view = $this->getView($view);

		return View::make($view, array_merge($this->viewVars, $data));
	}

	/**
	 * Add a piece of shared view data and returns the standard View instance.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return View
	 */
	protected function shares($key, $value = null)
	{
		View::share($key, $value);

		return $this->createView();
	}

	/**
	 * Add a key / value pair to the view data.
	 *
	 * Bound data will be available to the view as variables.
	 *
	 * @param  string|array  $one
	 * @param  string|array  $two
	 * @return View
	 */
	protected function set($one, $two = null)
	{
		if (is_array($one)) {
			if (is_array($two)) {
				$data = array_combine($one, $two);
			} else {
				$data = $one;
			}
		} else {
			$data = array($one => $two);
		}

		$this->viewVars = $data + $this->viewVars;

		return $this;
	}

	/**
	 * Prepare and returns a response.
	 *
	 * @param mixed  $response
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function prepareResponse($response)
	{
		if (! $response instanceof SymfonyResponse) {
			return new Response($response);
		}

		return $response;
	}

	/**
	 * Turns on or off Nova's conventional mode of auto-rendering.
	 *
	 * @param bool|null  $enable
	 * @return bool
	 */
	public function autoRender($enable = null)
	{
		if (! is_null($enable)) {
			$this->autoRender = (bool) $enable;

			return $this;
		}

		return $this->autoRender;
	}

	/**
	 * Turns on or off Nova's conventional mode of applying layout files.
	 *
	 * @param bool|null  $enable
	 * @return bool
	 */
	public function autoLayout($enable = null)
	{
		if (! is_null($enable)) {
			$this->autoLayout = (bool) $enable;

			return $this;
		}

		return $this->autoLayout;
	}

	/**
	 * Gets the qualified View name for the current or specified action.
	 *
	 * @param  string|null  $action
	 * @return string
	 * @throws \BadMethodCallException
	 */
	protected function getView($action = null)
	{
		$action = $action ?: $this->action;

		//
		$path = str_replace('\\', '/', static::class);

		if (preg_match('#^(.+)/Controllers/(.*)$#s', $path, $matches) === 1) {
			// Compute the View namespace.
			$namespace = ($matches[1] !== 'App') ? $matches[1] .'::' : '';

			// Compute and return the complete View name.
			return $namespace .$matches[2] .'/' .ucfirst($action);
		}

		throw new BadMethodCallException('Invalid Controller namespace: ' .static::class);
	}

	/**
	 * Return the current called action.
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Return the current Theme.
	 *
	 * @return string
	 */
	public function getTheme()
	{
		return $this->theme;
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

	/**
	 * Return the current View data.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->viewVars;
	}
}
