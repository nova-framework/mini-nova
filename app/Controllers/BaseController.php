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
	 * Handle a RenderableInterface implementation.
	 *
	 * @param \Mini\Support\Contracts\RenderableInterface  $view
	 * @return \Mini\Http\Response
	 */
	protected function renderWhithinLayout(RenderableInterface $renderable)
	{
		// Convert the used theme to a View namespace.
		$namespace = ! empty($this->theme) ? $this->theme .'::' : '';

		// Compute the name of View used as layout.
		$view = sprintf('%sLayouts/%s', $namespace, $this->layout);

		// Compute the composite View data.
		$data = array_merge($this->viewVars, array(
			'content' => $renderable
		));

		// Create and render the layout's View.
		$content = View::make($view, $data)->render();

		return new Response($content);
	}

	/**
	 * Create and return a default View instance.
	 *
	 * @param  array  $data
	 * @param  string}null  $view
	 * @return \Nova\View\View
	 * @throws \BadMethodCallException
	 */
	protected function createView(array $data = array(), $view = null)
	{
		$view = $action ?: $this->action;

		if (preg_match('#^(.+)/Controllers/(.*)$#s', str_replace('\\', '/', static::class), $matches)) {
			$namespace = ($matches[1] !== 'App') ? $matches[1] .'::' : '';

			$view = $namespace .$matches[2] .'/' .ucfirst($view);

			return View::make($view, array_merge($this->viewVars, $data));
		}

		throw new BadMethodCallException('Invalid Controller namespace: ' .static::class);
	}

	/**
	 * Create and return a default View instance.
	 *
	 * @param  array  $data
	 * @return \Nova\View\View
	 * @throws \BadMethodCallException
	 */
	protected function getView(array $data = array())
	{
		list(, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

		$method = $caller['function'];

		return $this->createView($data, $method);
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
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return View
	 */
	protected function set($key, $value = null)
	{
		if (is_array($key)) {
			$this->viewVars = array_merge($this->viewVars, $key);
		} else {
			$this->viewVars[$key] = $value;
		}

		return $this;
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
