<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Config\Repository;
use Mini\Foundation\AliasLoader;
use Mini\Foundation\Application;
use Mini\Support\Facades\Facade;


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

		// Set the default Timezone.
		date_default_timezone_set($config['app.timezone']);

		// Register the Facades.
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($app);

		// Register the class aliases.
		AliasLoader::getInstance($config->get('app.aliases'))->register();
	}
}
