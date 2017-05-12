<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Config\Repository as ConfigRepository;
use Mini\Foundation\AliasLoader;
use Mini\Foundation\Application;
use Mini\Support\Facades\Facade;


class CommonSetup
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		// Use internally the UTF-8 encoding.
		mb_internal_encoding('UTF-8');

		// Binds the paths to application.
		$app->bindInstallPaths(array(
			'base'		=> BASEPATH,
			'app'		=> APPPATH,
			'public'	=> WEBPATH,
			'storage'	=> STORAGE_PATH,
		));

		// Register the Config Repository.
		$app->instance('config', $config = new ConfigRepository(
			$app->getConfigLoader()
		));

		// Set the default Timezone from configuration.
		date_default_timezone_set($config['app.timezone']);

		// Register the core Service Providers.
		$app->getProviderRepository()->load($app, $config['app.providers']);

		// Register the Facades.
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($app);

		AliasLoader::getInstance($config['app.aliases'])->register();
	}
}
