<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Config\Repository;
use Mini\Foundation\Application;


class LoadConfiguration
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		// Register the Config Repository.
		$app->instance('config', $config = new Repository(
			$app->getConfigLoader()
		));

		// Set the default Timezone from configuration.
		date_default_timezone_set($config['app.timezone']);
	}
}
