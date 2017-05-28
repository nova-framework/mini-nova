<?php

namespace Taxonomy\Providers;

use Mini\Auth\Contracts\Access\GateInterface as Gate;
use Mini\Foundation\AliasLoader;
use Mini\Plugin\Support\Providers\PluginServiceProvider as ServiceProvider;
use Mini\Routing\Router;

use Taxonomy\Support\Taxonomy;


class PluginServiceProvider extends ServiceProvider
{
	/**
	 * The additional provider class names.
	 *
	 * @var array
	 */
	protected $providers = array(
		'Taxonomy\Providers\RouteServiceProvider'
	);

	/**
	 * The event listener mappings for the plugin.
	 *
	 * @var array
	 */
	protected $listen = array(
		'Taxonomy\Events\SomeEvent' => array(
			'Taxonomy\Listeners\EventListener',
		),
	);

	/**
	 * The policy mappings for the plugin.
	 *
	 * @var array
	 */
	protected $policies = array(
		'Taxonomy\Models\SomeModel' => 'Taxonomy\Policies\ModelPolicy',
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
		$this->package('Taxonomy', 'taxonomy', $path);

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
	 * Register the Taxonomy plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		parent::register();

		/*
		$this->app['taxonomy'] = $this->app->bindShared(function ($app)
		{
			return new Taxonomy();
		});

		// Register the Facades.
		$loader = AliasLoader::getInstance();

		$loader->alias('Taxonomy', 'Taxonomy\Support\Facades\Taxonomy');
		*/
	}

}
