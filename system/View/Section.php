<?php

namespace Mini\View;

use Mini\View\Factory;


class Section
{
	/**
	 * The View Factory instance.
	 *
	 * @var \Mini\View\Factory
	 */
	protected $factory;


	/**
	 * Constructor
	 *
	 * @param \Mini\View\Factory $factory
	 * @param array $data
	 */
	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * Get the string contents of a section.
	 *
	 * @param  string  $section
	 * @param  string  $default
	 * @return string
	 */
	public function get($section, $default = '')
	{
		return $this->factory->yieldContent($section, $default);
	}

	/**
	 * Start injecting content into a section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function start($section, $content = '')
	{
		$this->factory->startSection($section, $content);
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
		return $this->factory->startSection($section, $content);
	}

	/**
	 * Stop injecting content into a section and append it.
	 *
	 * @return string
	 */
	public function append()
	{
		return $this->factory->appendSection();
	}

	/**
	 * Append content to a given section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 * @return void
	 */
	public function extend($section, $content)
	{
		return $this->factory->extendSection($section, $content);
	}

	/**
	 * Stop injecting content into a section and return its contents.
	 *
	 * @param  string  $section
	 * @param  string  $default
	 * @return string
	 */
	public function show()
	{
		return $this->factory->yieldSection();
	}

	/**
	 * Stop injecting content into a section.
	 *
	 * @param  bool  $overwrite
	 * @return string
	 */
	public function stop()
	{
		return $this->factory->stopSection();
	}

	/**
	 * Stop injecting content into a section.
	 *
	 * @param  bool  $overwrite
	 * @return string
	 */
	public function overwrite()
	{
		return $this->factory->stopSection(true);
	}
}
