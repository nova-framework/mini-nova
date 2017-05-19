<?php

namespace Mini\View\Engines;

use Mini\View\Engines\EngineInterface;

use Exception;


class Engine implements EngineInterface
{

	/**
	 * Get the evaluated contents of the View.
	 *
	 * @param  string  $path
	 * @param  array   $data
	 * @return string
	 */
	public function get($path, array $data = array())
	{
		return $this->evaluatePath($path, $data);
	}

	/**
	 * Get the evaluated contents of the View at the given path.
	 *
	 * @param  string  $__path
	 * @param  array   $__data
	 * @return string
	 */
	protected function evaluatePath($__path, $__data)
	{
		$obLevel = ob_get_level();

		//
		ob_start();

		// Extract the rendering variables.
		foreach ($__data as $__variable => $__value) {
			${$__variable} = $__value;
		}

		// Housekeeping...
		unset($__variable, $__value);

		try {
			include $__path;

		} catch (\Exception $e) {
			$this->handleViewException($e, $obLevel);
		} catch (\Throwable $e) {
			$this->handleViewException($e, $obLevel);
		}

		return ltrim(ob_get_clean());
	}

	/**
	 * Handle a View Exception.
	 *
	 * @param  \Exception  $e
	 * @param  int  $obLevel
	 * @return void
	 *
	 * @throws $e
	 */
	protected function handleViewException($e, $obLevel)
	{
		while (ob_get_level() > $obLevel) {
			ob_end_clean();
		}

		throw $e;
	}
}
