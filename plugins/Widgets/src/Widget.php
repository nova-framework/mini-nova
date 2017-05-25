<?php

namespace Widgets;

use Mini\Container\Container;


class Widget
{
	/**
	 * The container implementation.
	 *
	 * @var \Mini\Container\Container
	 */
	protected $container;

	/**
	 * classes registered widgets
	 *
	 * @var array
	 */
	protected $classes = array();

	/**
	 * Prepared instances of widgets
	 *
	 * @var array
	 */
	protected $instances = array();

	/**
	 * Positions for widgets
	 *
	 * @var array
	 */
	protected $positions = array();


	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param  string $class
	 * @param  string $name
	 * @return void
	 */
	public function register($class, $name, $position = null, $order = null)
	{
		$this->classes[$name] = $class;

		if (! is_null($position)) {
			$this->positions[$position][] = compact('name', 'order');
		}
	}

	/**
	 * @param  string $name
	 * @return mixed|null
	 */
	public function render($name)
	{
		if (! array_key_exists($name, $this->classes)) {
			return null;
		}

		if (! array_key_exists($name, $this->instances)) {
			$widget = $this->classes[$name];

			$instance = $this->container->make($widget);

			$this->addInstance($instance, $name);
		} else {
			$instance = $this->instances[$name];
		}

		$parameters = array_slice(func_get_args(), 1);

		return call_user_func_array(array($instance, 'render'), $parameters);
	}

	public function renderPosition($position)
	{
		if (! array_key_exists($position, $this->positions)) {
			return null;
		}

		usort($this->positions[$position], function ($a, $b)
		{
			if ($a['order'] == $b['order']) return 0;

			return ($a['order'] > $b['order']) ? -1 : 1;
		});

		$arguments = array_slice(func_get_args(), 1);

		//
		$result = '';

		foreach ($this->positions[$position] as $widget) {
			$parameters = $arguments;

			array_unshift($parameters, $widget['name']);

			$result .= call_user_func_array(array($this, 'render'), $parameters);
		}

		return $result;
	}

	public function exists($name)
	{
		return array_key_exists($name, $this->classes);
	}

	public function isEmptyPosition($position)
	{
		if (! array_key_exists($position, $this->positions)) {
			return true;
		} else if (! count($this->positions[$position])) {
			return true;
		}

		return false;
	}

	/**
	 * @param		$widget
	 * @param string $name
	 * @return void
	 */
	protected function addInstance($widget, $name)
	{
		$this->instances[$name] = $widget;
	}

	/**
	 * @param string $method
	 * @param array  $arguments
	 * @return mixed
	 */
	public function __call($method, array $arguments)
	{
		array_unshift($arguments, $method);

		return call_user_func_array(array($this, 'render'), $arguments);
	}
}

