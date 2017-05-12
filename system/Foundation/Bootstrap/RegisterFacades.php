<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Foundation\AliasLoader;
use Mini\Foundation\Application;
use Mini\Support\Facades\Facade;


class RegisterFacades
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		// Register the Facades.
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($app);

		//
		$aliases = $app->make('config')->get('app.aliases');

		AliasLoader::getInstance($aliases)->register();
	}
}
