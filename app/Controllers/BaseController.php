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
		if (is_null($response) && $this->autoRender()) {
			return $this->render();
		}

		if ($response instanceof RenderableInterface) {
			return $this->handleView($response);
		} else if (! $response instanceof SymfonyResponse) {
			return new Response($response);
		}

		return $response;
	}

	/**
	 * Instantiates the correct View instance, hands it its data, and uses it to render the view output.
	 *
	 * @param string|null $view View to use for rendering
	 * @param string|null $layout Layout to use
	 * @return \Mini\Http\Response A response object containing the rendered view.
	 */
	public function render($view = null, $layout = null)
	{
		$this->autoRender = false;

		if (! is_null($view)) {
			$view = View::make($view, $this->viewVars);
		} else {
			$view = $this->createView();
		}

		return $this->handleView($view, $layout);
	}

	/**
	 * Create and return a default View instance.
	 *
	 * @param  array  $data
	 * @return \Nova\View\View
	 * @throws \BadMethodCallException
	 */
	protected function createView(array $data = array())
	{
		$classPath = str_replace('\\', '/', static::class);

		if (preg_match('#^(.+)/Controllers/(.*)$#s', $classPath, $matches)) {
			$namespace = ($matches[1] !== 'App') ? $matches[1] .'::' : null;

			$view = $namespace .$matches[2] .'/' .ucfirst($this->action);

			return View::make($view, array_merge($this->viewVars, $data));
		}

		throw new BadMethodCallException('Invalid Controller namespace: ' .static::class);
	}

	/**
	 * Handle a RenderableInterface implementation.
	 *
	 * @param \Mini\Support\Contracts\RenderableInterface  $view
	 * @param string|null  $layout Layout to use
	 * @return \Mini\Http\Response
	 */
	protected function handleView(RenderableInterface $renderable, $layout = null)
	{
		$layout = $layout ?: $this->layout;

		if (empty($layout)) {
			return new Response($renderable);
		}

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
	 * Create and return a default View instance.
	 *
	 * @param  array  $data
	 * @return \Nova\View\View
	 * @throws \BadMethodCallException
	 */
	protected function getView(array $data = array())
	{
		$this->autoRender = false;

		return $this->createView($data);
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
	 * Sets/gets the auto-rendering mode.
	 *
	 * @param bool|null  $mode
	 * @return bool
	 */
	public function autoRender($mode = null)
	{
		if (is_null($mode)) {
			return $this->autoRender;
		}

		return $this->autoRender = (bool) $mode;
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
