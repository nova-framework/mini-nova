<?php

namespace Blocks\Providers;

use Mini\Foundation\AliasLoader;
use Mini\Plugins\Support\Providers\PluginServiceProvider as ServiceProvider;

use Blocks\Support\BlockManager;


class PluginServiceProvider extends ServiceProvider
{
	/**
	 * The additional provider class names.
	 *
	 * @var array
	 */
	protected $providers = array(
		//'Blocks\Providers\AuthServiceProvider',
		//'Blocks\Providers\EventServiceProvider',
		'Blocks\Providers\RouteServiceProvider'
	);


	/**
	 * Bootstrap the Application Events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$path = realpath(__DIR__ .'/../');

		// Configure the Package.
		$this->package('Blocks', 'blocks', $path);

		// Bootstrap the Plugin.
		require $path .DS .'Bootstrap.php';
	}

	/**
	 * Register the Blocks plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		parent::register();


		$this->app->bindShared('blocks', function($app)
		{
			return new BlockManager($app);
		});

		// Register the Facades.
		$loader = AliasLoader::getInstance();

		$loader->alias('Blocks', 'Blocks\Support\Facades\Blocks');
	}

}
