<?php

namespace Mini\View;

use Mini\Filesystem\Filesystem;
use Mini\Support\Contracts\ArrayableInterface as Arrayable;
use Mini\Support\Arr;
use Mini\Support\Str;
use Mini\View\Engines\EngineResolver;
use Mini\View\View;

use BadMethodCallException;


class Factory
{
	/**
	 * The Engines Resolver instance.
	 *
	 * @var \Nova\View\Engines\EngineResolver
	 */
	protected $engines;

	/**
	* The Filesystem instance.
	*
	* @var \Mini\Filesystem\Filesystem
	*/
	protected $files;

	/**
	 * The array of Views that have been located.
	 *
	 * @var array
	 */
	protected $views = array();

	/**
	 * @var array Array of shared data
	 */
	protected $shared = array();

	/**
	 * The extension to Engine bindings.
	 *
	 * @var array
	 */
	protected $extensions = array('tpl' => 'template', 'php' => 'php');

	/**
	 * All of the finished, captured sections.
	 *
	 * @var array
	 */
	protected $sections = array();

	/**
	 * The stack of in-progress sections.
	 *
	 * @var array
	 */
	protected $sectionStack = array();

	/**
	 * The number of active rendering operations.
	 *
	 * @var int
	 */
	protected $renderCount = 0;


	/**
	 * Create new View Factory instance.
	 *
	 * @return void
	 */
	function __construct(EngineResolver $engines, Filesystem $files)
	{
		$this->engines = $engines;
		$this->files   = $files;

		$this->share('__env', $this);
	}

	/**
	 * Create a View instance
	 *
	 * @param string $path
	 * @param array|string $data
	 * @param string|null $module
	 * @return \Nova\View\View
	 * @throws \BadMethodCallException
	 */
	public function make($view, $data = array())
	{
		$path = $this->findViewFile($view);

		if (is_null($path) || ! is_readable($path)) {
			throw new BadMethodCallException("File path [$path] does not exist");
		}

		return new View($this, $this->getEngineFromPath($path), $view, $path, $this->parseData($data));
	}

	/**
	 * Get the rendered string contents of a View.
	 *
	 * @param mixed $view
	 * @param array $data
	 *
	 * @return string
	 */
	public function fetch($view, $data = array(), Closure $callback = null)
	{
		return $this->make($view, $data)->render($callback);
	}

	/**
	 * Parse the given data into a raw array.
	 *
	 * @param  mixed  $data
	 * @return array
	 */
	protected function parseData($data)
	{
		return ($data instanceof Arrayable) ? $data->toArray() : $data;
	}

	/**
	 * Check if the view file exists.
	 *
	 * @param	string	 $view
	 * @return	bool
	 */
	public function exists($view)
	{
		$path = $this->findViewFile($view);

		return ! is_null($path) && is_readable($path);
	}

	/**
	 * Get the rendered contents of a partial from a loop.
	 *
	 * @param  string  $view
	 * @param  array   $data
	 * @param  string  $iterator
	 * @param  string  $empty
	 * @return string
	 */
	public function renderEach($view, array $data, $iterator, $empty = 'raw|')
	{
		if (count($data) > 0) {
			$result = '';

			foreach ($data as $key => $value) {
				$data = array('key' => $key, $iterator => $value);

				$result .= $this->make($view, $data)->render();
			}
		}

		// There is no data in the array; we render the contents of the empty view.
		else if (! Str::startsWith($empty, 'raw|')) {
			$result = $this->make($empty)->render();
		} else {
			$result = substr($empty, 4);
		}

		return $result;
	}

	/**
	 * Get the appropriate View Engine for the given path.
	 *
	 * @param  string  $path
	 * @return \Nova\View\Engines\EngineInterface
	 */
	public function getEngineFromPath($path)
	{
		$extension = $this->getExtension($path);

		$engine = $this->extensions[$extension];

		return $this->engines->resolve($engine);
	}

	/**
	 * Get the extension used by the view file.
	 *
	 * @param  string  $path
	 * @return string
	 */
	protected function getExtension($path)
	{
		$extensions = array_keys($this->extensions);

		return Arr::first($extensions, function($key, $value) use ($path)
		{
			return Str::endsWith($path, $value);
		});
	}

	/**
	 * Add a piece of shared data to the Factory.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function share($key, $value = null)
	{
		if (! is_array($key)) {
			return $this->shared[$key] = $value;
		}

		foreach ($key as $innerKey => $innerValue) {
			$this->share($innerKey, $innerValue);
		}
	}

	/**
	 * Get an item from the shared data.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function shared($key, $default = null)
	{
		return Arr::get($this->shared, $key, $default);
	}

	/**
	 * Start injecting content into a section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function startSection($section, $content = '')
	{
		if ($content === '') {
			if (ob_start()) {
				$this->sectionStack[] = $section;
			}
		} else {
			$this->extendSection($section, $content);
		}
	}

	/**
	 * Inject inline content into a section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function inject($section, $content)
	{
		return $this->startSection($section, $content);
	}

	/**
	 * Stop injecting content into a section and return its contents.
	 *
	 * @return string
	 */
	public function yieldSection()
	{
		return $this->yieldContent($this->stopSection());
	}

	/**
	 * Stop injecting content into a section.
	 *
	 * @param  bool  $overwrite
	 * @return string
	 */
	public function stopSection($overwrite = false)
	{
		$last = array_pop($this->sectionStack);

		if ($overwrite) {
			$this->sections[$last] = ob_get_clean();
		} else {
			$this->extendSection($last, ob_get_clean());
		}

		return $last;
	}

	/**
	 * Stop injecting content into a section and append it.
	 *
	 * @return string
	 */
	public function appendSection()
	{
		$last = array_pop($this->sectionStack);

		if (isset($this->sections[$last]))  {
			$this->sections[$last] .= ob_get_clean();
		} else {
			$this->sections[$last] = ob_get_clean();
		}

		return $last;
	}

	/**
	 * Append content to a given section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function extendSection($section, $content)
	{
		if (isset($this->sections[$section])) {
			$content = str_replace('@parent', $content, $this->sections[$section]);
		}

		$this->sections[$section] = $content;
	}

	/**
	 * Get the string contents of a section.
	 *
	 * @param  string  $section
	 * @param  string  $default
	 * @return string
	 */
	public function yieldContent($section, $default = '')
	{
		$sectionContent = $default;

		if (isset($this->sections[$section])) {
			$sectionContent = $this->sections[$section];
		}

		return str_replace('@parent', '', $sectionContent);
	}

	/**
	 * Flush all of the section contents.
	 *
	 * @return void
	 */
	public function flushSections()
	{
		$this->renderCount = 0;

		$this->sections = array();

		$this->sectionStack = array();
	}

	/**
	 * Flush all of the section contents if done rendering.
	 *
	 * @return void
	 */
	public function flushSectionsIfDoneRendering()
	{
		if ($this->doneRendering()) {
			$this->flushSections();
		}
	}

	/**
	 * Increment the rendering counter.
	 *
	 * @return void
	 */
	public function incrementRender()
	{
		$this->renderCount++;
	}

	/**
	 * Decrement the rendering counter.
	 *
	 * @return void
	 */
	public function decrementRender()
	{
		$this->renderCount--;
	}

	/**
	 * Check if there are no active render operations.
	 *
	 * @return bool
	 */
	public function doneRendering()
	{
		return ($this->renderCount == 0);
	}

	/**
	 * Get the extension to engine bindings.
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * Get the engine resolver instance.
	 *
	 * @return \Mini\View\Engines\EngineResolver
	 */
	public function getEngineResolver()
	{
		return $this->engines;
	}

	/**
	 * Get all of the shared data for the Factory.
	 *
	 * @return array
	 */
	public function getShared()
	{
		return $this->shared;
	}

	/**
	 * Get the view file.
	 *
	 * @param	string	 $view
	 * @return	string
	 */
	protected function findViewFile($view)
	{
		if (isset($this->views[$view])) {
			return $this->views[$view];
		}

		$extensions = array_keys($this->extensions);

		foreach($extensions as $extension) {
			$path = APPPATH .str_replace('/', DS, "Views/$view.$extension");

			if ($this->files->exists($path)) {
				return $this->views[$view] = $path;
			}
		}
	}
}
