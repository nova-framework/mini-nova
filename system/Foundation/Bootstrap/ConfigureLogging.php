<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Foundation\Application;
use Mini\Foundation\Logger;

use Monolog\Logger as Monolog;


class ConfigureLogging
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$log = $this->registerLogger($app);

		$this->configureHandlers($app, $log);
	}

	/**
	 * Register the logger instance in the container.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return \Mini\Foundation\Logger
	 */
	protected function registerLogger(Application $app)
	{
		$app->instance('log', $log = new Logger(
			new Monolog('mini-nova'), $app['events'])
		);

		return $log;
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Foundation\Logger  $log
	 * @return void
	 */
	protected function configureHandlers(Application $app, Logger $log)
	{
		$method = 'configure' .ucfirst($app['config']['app.log']) .'Handler';

		call_user_func(array($this, $method), $app, $log);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Foundation\Logger  $log
	 * @return void
	 */
	protected function configureSingleHandler(Application $app, Logger $log)
	{
		$log->useFiles($app->make('path.storage') .DS .'logs' .DS .'framework.log');
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Foundation\Logger  $log
	 * @return void
	 */
	protected function configureDailyHandler(Application $app, Logger $log)
	{
		$log->useDailyFiles(
			$app->make('path.storage') .DS .'logs' .DS .'framework.log',
			$app->make('config')->get('app.log_max_files', 5)
		);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Foundation\Logger  $log
	 * @return void
	 */
	protected function configureSyslogHandler(Application $app, Logger $log)
	{
		$log->useSyslog('mini-nova');
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Foundation\Logger  $log
	 * @return void
	 */
	protected function configureErrorlogHandler(Application $app, Logger $log)
	{
		$log->useErrorLog();
	}
}
