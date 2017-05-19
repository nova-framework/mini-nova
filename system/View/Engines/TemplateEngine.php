<?php

namespace Mini\View\Engines;

use Mini\View\Engines\Engine;
use Mini\View\Template;
use Mini\Support\Str;

use Symfony\Component\Debug\Exception\FatalThrowableError;

use ErrorException;


class TemplateEngine extends Engine
{
	/**
	 * The Template instance.
	 *
	 * @var \Nova\View\Template
	 */
	protected $compiler;

	/**
	 * A stack of the last compiled templates.
	 *
	 * @var array
	 */
	protected $lastCompiled = array();


	/**
	 * Create a new Template Engine instance.
	 *
	 * @param  \Nova\View\Template  $template
	 * @return void
	 */
	public function __construct(Template $template)
	{
		$this->template = $template;
	}

	/**
	 * Get the evaluated contents of the View.
	 *
	 * @param  string  $path
	 * @param  array   $data
	 * @return string
	 */
	public function get($path, array $data = array())
	{
		$this->lastCompiled[] = $path;

		if ($this->template->isExpired($path)) {
			$this->template->compile($path);
		}

		$compiled = $this->template->getCompiledPath($path);

		$results = $this->evaluatePath($compiled, $data);

		array_pop($this->lastCompiled);

		return $results;
	}

	/**
	 * Handle a view exception.
	 *
	 * @param  \Exception  $e
	 * @param  int  $obLevel
	 * @return void
	 *
	 * @throws $e
	 */
	protected function handleViewException($e, $obLevel)
	{
		if (! $e instanceof \Exception) {
			$e = new FatalThrowableError($e);
		}

		$e = new \ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

		parent::handleViewException($e, $obLevel);
	}

	/**
	 * Get the exception message for an exception.
	 *
	 * @param  \Exception  $e
	 * @return string
	 */
	protected function getMessage($e)
	{
		$path = last($this->lastCompiled);

		return $e->getMessage() .' (View: ' .realpath($path) .')';
	}

	/**
	 * Get the Template implementation.
	 *
	 * @return \Nova\View\Template
	 */
	public function getCompiler()
	{
		return $this->template;
	}
}
