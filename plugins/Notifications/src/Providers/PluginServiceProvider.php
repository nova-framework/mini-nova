<?php

namespace Notifications\Providers;

use Mini\Foundation\AliasLoader;
use Mini\Support\ServiceProvider;

use Notifications\Contracts\DispatcherInterface;
use Notifications\ChannelManager;


class PluginServiceProvider extends ServiceProvider
{

	/**
	 * Bootstrap the Application Events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$path = realpath(__DIR__ .'/../');

		// Configure the Package.
		$this->package('Notifications', 'notifications', $path);

		//
	}

	/**
	 * Register the Notifications plugin Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(ChannelManager::class, function ($app)
		{
			return new ChannelManager($app);
		});

		$this->app->alias(
			ChannelManager::class, DispatcherInterface::class
		);

		// Register the Facades.
		$loader = AliasLoader::getInstance();

		$loader->alias('Notification', 'Notifications\Support\Facades\Notification');
	}

}
