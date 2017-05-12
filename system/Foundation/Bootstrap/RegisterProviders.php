<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Foundation\Application;


class RegisterProviders
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$app->registerConfiguredProviders();
	}
}
