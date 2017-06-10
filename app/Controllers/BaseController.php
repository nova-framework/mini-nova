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
	 * Method executed before any action.
	 *
	 * @return void
	 */
	protected function before()
	{
		//
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

		//
		// Process the response returned from action.

		if (! $this->autoRender()) {
			return $response;
		} else if (is_null($response)) {
			$response = $this->createView();
		}

		if (($response instanceof RenderableInterface) && $this->autoLayout()) {
			return $this->renderWhithinLayout($response);
		}

		return $response;
	}

	/**
	 * Create a View instance for a layout and which embed the given View.
	 *
	 * @param  \Mini\Support\Contracts\RenderableInterface  $renderable
	 * @return \Mini\Http\Response
	 */
	protected function renderWhithinLayout(RenderableInterface $renderable)
	{
		// Convert the used theme to a View namespace.
		$namespace = ! empty($this->theme) ? $this->theme .'::' : '';

		// Compute the full name of View used as layout.
		$view = $namespace .'Layouts/' .$this->layout;

		// Get the composite View data.
		$data = array_merge($this->viewVars, array(
			'content' => $renderable
		));

		return View::make($view, $data);
	}

	/**
	 * Create and return a (default) View instance.
	 *
	 * @param  array  $data
	 * @param  string|null  $view
	 * @return \Nova\View\View
	 */
	protected function createView(array $data = array(), $view = null)
	{
		if (is_null($view)) {
			$view = $this->getView();
		}

		// If we have an "absolute" view name, it points to the app's Views.
		else if (Str::startsWith($view, '/')) {
			$view = ltrim($view, '/');
		}

		// If we have a non namespaced View name, it has an alternate naming.
		else if (! Str::contains($view, '::')) {
			$view = $this->getView($view);
		}

		return View::make($view, array_merge($this->viewVars, $data));
	}

	/**
	 * Gets the qualified View name for the current (or specified) action.
	 *
	 * @param  string|null  $action
	 * @return string
	 * @throws \BadMethodCallException
	 */
	protected function getView($action = null)
	{
		if (is_null($action)) {
			$action = $this->action;
		}

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
			$data = is_array($two) ? array_combine($one, $two) : $one;
		} else {
			$data = array($one => $two);
		}

		$this->viewVars = $data + $this->viewVars;

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
}
