<?php
/**
 * CookieServiceProvider - Implements a Service Provider for CookieJar.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Mini\Cookie;

use Mini\Cookie\CookieJar;
use Mini\Support\ServiceProvider;


class CookieServiceProvider extends ServiceProvider
{
	/**
	 * Register the Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('cookie', function($app)
		{
			$config = $app['config']['session'];

			return (new CookieJar())->setDefaultPathAndDomain($config['path'], $config['domain']);
		});
	}
}
