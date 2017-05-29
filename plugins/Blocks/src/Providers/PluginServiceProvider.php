<?php

namespace Blocks\Providers;

use Mini\Auth\Contracts\Access\GateInterface as Gate;
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
		'Blocks\Providers\RouteServiceProvider'
	);

	/**
	 * The event listener mappings for the plugin.
	 *
	 * @var array
	 */
	protected $listen = array(
		'Blocks\Events\SomeEvent' => array(
			'Blocks\Listeners\EventListener',
		),
	);

	/**
	 * The policy mappings for the plugin.
	 *
	 * @var array
	 */
	protected $policies = array(
		'Blocks\Models\SomeModel' => 'Blocks\Policies\ModelPolicy',
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
		$path = $path .DS .'Bootstrap.php';

		$this->bootstrapFrom($path);

		// Register the Plugin Policies.
		$gate = $this->app->make(Gate::class);

		$this->registerPolicies($gate);

		//
		parent::boot();
	}

	/**
	 * Register the Blocks plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		parent::register();

		$this->app->singleton('blocks', function($app)
		{
			return new BlockManager($app, $app['request']);
		});

		// Register the Facades.
		$loader = AliasLoader::getInstance();

		$loader->alias('Blocks', 'Blocks\Support\Facades\Blocks');
	}

}
