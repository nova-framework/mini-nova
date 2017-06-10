<?php

namespace App\Controllers;

use Mini\Foundation\Auth\Access\AuthorizesRequestsTrait;
use Mini\Foundation\Bus\DispatchesCommandsTrait;
use Mini\Foundation\Validation\ValidatesRequestsTrait;
use Mini\Http\Response;
use Mini\Routing\Controller;
use Mini\Support\Contracts\RenderableInterface;
use Mini\Support\Facades\App;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\View;
use Mini\Support\Str;

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
	 * The View path for views of this Controller.
	 *
	 * @var array
	 */
	protected $viewPath;

	/**
	 * The View variables.
	 *
	 * @var array
	 */
	protected $viewData = array();


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

		// Call the requested method and store its returned value.
		$response = call_user_func_array(array($this, $method), $parameters);

		return $this->processResponse($response);
	}

	/**
	 * Method executed before any action.
	 *
	 * @return void
	 */
	protected function before() {}

	/**
	 * Process a response returned by the action execution.
	 *
	 * @param mixed  $response
	 * @return mixed
	 */
	protected function processResponse($response)
	{
		if (! $this->autoRender()) {
			return $response;
		} else if (is_null($response)) {
			$response = $this->createView();
		}

		if ($this->autoLayout() && ($response instanceof RenderableInterface)) {
			// Convert the used Theme (plugin name) to a View namespace.
			$namespace = ! empty($this->theme) ? $this->theme .'::' : '';

			// Compute the full name of View used as Layout.
			$view = $namespace .'Layouts/' .$this->layout;

			return View::make($view, $this->viewData)->with('content', $response);
		}

		return $response;
	}

	/**
	 * Create a View instance for the implicit (or specified) View name.
	 *
	 * @param  array  $data
	 * @param  string|null  $custom
	 * @return \Nova\View\View
	 */
	protected function createView($data = array(), $custom = null)
	{
		$action = $custom ?: $this->action;

		// Compute the full View name.
		$view = $this->getViewPath() .'/' .ucfirst($action);

		return View::make($view, array_merge($this->viewData, $data));
	}

	/**
	 * Gets the View path for views of this Controller.
	 *
	 * @return string
	 * @throws \BadMethodCallException
	 */
	protected function getViewPath()
	{
		if (isset($this->viewPath)) {
			return $this->viewPath;
		}

		$appPath = str_replace('\\', '/', App::getNamespace());

		$classPath = str_replace('\\', '/', static::class);

		if (preg_match('#^(.+)/Controllers/(.*)$#s', $classPath, $matches) !== 1) {
			throw new BadMethodCallException('Invalid class namespace');
		}

		if ($matches[1] === $appPath) {
			$viewPath = $matches[2];
		} else {
			// This Controller lives within a Plugin.
			$viewPath = $matches[1] .'::' .$matches[2];
		}

		return $this->viewPath = $viewPath;
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

		$this->viewData = $data + $this->viewData;

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

	/**
	 * Return the current View data.
	 *
	 * @return string
	 */
	public function getViewData()
	{
		return $this->viewData;
	}
}
