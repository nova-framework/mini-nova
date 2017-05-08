<?php
/**
 * DatabaseServiceProvider - Implements a Service Provider for Database.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Mini\Database;

use Mini\Database\DatabaseManager;
use Mini\Database\ORM\Model;
use Mini\Support\ServiceProvider;


class DatabaseServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the Application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Setup the Model.
		Model::setConnectionResolver($this->app['db']);

		Model::setEventDispatcher($this->app['events']);
	}

	/**
	 * Register the Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('db', function($app)
		{
			return new DatabaseManager($app);
		});
	}

}
