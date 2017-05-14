<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Foundation\Application;
use Mini\Log\Writter;

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
	 * @return \Mini\Log\Writter
	 */
	protected function registerLogger(Application $app)
	{
		$app->instance('log', $log = new Writter(
			new Monolog('mini-nova'), $app['events'])
		);

		return $log;
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Log\Writter  $log
	 * @return void
	 */
	protected function configureHandlers(Application $app, Writter $log)
	{
		$method = 'configure' .ucfirst($app['config']['app.log']) .'Handler';

		call_user_func(array($this, $method), $app, $log);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Log\Writter  $log
	 * @return void
	 */
	protected function configureSingleHandler(Application $app, Writter $log)
	{
		$log->useFiles($app->make('path.storage') .DS .'logs' .DS .'framework.log');
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Log\Writter  $log
	 * @return void
	 */
	protected function configureDailyHandler(Application $app, Writter $log)
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
	 * @param  \Mini\Log\Writter  $log
	 * @return void
	 */
	protected function configureSyslogHandler(Application $app, Writter $log)
	{
		$log->useSyslog('mini-nova');
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @param  \Mini\Log\Writter  $log
	 * @return void
	 */
	protected function configureErrorlogHandler(Application $app, Writter $log)
	{
		$log->useErrorLog();
	}
}
