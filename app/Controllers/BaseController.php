<?php

namespace App\Controllers;

use Mini\Foundation\Auth\Access\AuthorizesRequestsTrait;
use Mini\Foundation\Bus\DispatchesCommandsTrait;
use Mini\Foundation\Validation\ValidatesRequestsTrait;
use Mini\Http\Response;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
use Mini\Support\Facades\Config;
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

		// Execute the BEFORE stage.
		$this->before();

		// Call the requested method and store its returned value.
		$response = call_user_func_array(array($this, $method), $parameters);

		//
		// Process the response and optionally execute the auto-rendering.

		if ($this->autoRender()) {
			// Create an implicit View instance when the response is null.
			$response = $response ?: $this->createView();

			if ($this->autoLayout() && ($response instanceof RenderableInterface)) {
				$response = $this->createLayoutView()->with('content', $response);
			}
		}

		if (! $response instanceof SymfonyResponse) {
			$response = new Response($response);
		}

		return $response;
	}

	/**
	 * Create a View instance for a Layout, from the implicit (or specified) Theme.
	 *
	 * @param  string|null  $layout
	 * @param  string|null  $theme
	 * @return \Mini\View\View
	 */
	protected function createLayoutView($layout = null, $theme = null)
	{
		$layout = $layout ?: $this->layout;
		$theme  = $theme  ?: $this->theme;

		// Convert the used theme (plugin name) to a View namespace.
		$namespace = ! empty($theme) ? $theme .'::' : '';

		// Compute the full name of View used as layout.
		$view = $namespace .'Layouts/' .$layout;

		return View::make($view, $this->viewVars);
	}

	/**
	 * Create a View instance for the implicit (or specified) View name.
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
	 * @param  string|null  $custom
	 * @return string
	 * @throws \BadMethodCallException
	 */
	protected function getView($custom = null)
	{
		$action = $custom ?: $this->action;

		//
		$path = str_replace('\\', '/', static::class);

		if (preg_match('#^(.+)/Controllers/(.*)$#s', $path, $matches) === 1) {
			// Compute the View namespace.
			$namespace = ($matches[1] !== 'App') ? $matches[1] .'::' : '';

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
	public function set($one, $two = null)
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
