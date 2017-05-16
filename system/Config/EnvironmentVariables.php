<?php

namespace Mini\Config;

use Mini\Foundation\Application;


class EnvironmentVariables
{
	/**
	 * The path to the configuration files.
	 *
	 * @var string
	 */
	protected $path;


	/**
	 * Create a new file environment loader instance.
	 *
	 * @param  \Nova\Foundation\Application  $files
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->path = $app->make('path.base');
	}

	/**
	 * Load the environment variables for the given environment.
	 *
	 * @param  string  $environment
	 * @return array
	 */
	public function load($environment = null)
	{
		$variables = $this->getVariables($environment);

		foreach ($variables as $key => $value) {
			$_ENV[$key] = $value;

			$_SERVER[$key] = $value;

			putenv("{$key}={$value}");
		}
	}

	/**
	 * Get the variables for the given environment.
	 *
	 * @param  string  $environment
	 * @return array
	 */
	protected function getVariables($environment)
	{
		if (is_null($environment) || ($environment === 'production')) {
			$path = $this->path .DS .'.env.php';
		} else {
			$path = $this->path .DS .'.env.' .$environment .'.php';
		}

		if (! is_readable($path)) {
			return array();
		}

		return require $path;
	}
}
