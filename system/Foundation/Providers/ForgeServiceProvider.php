<?php

namespace Mini\Foundation\Providers;

use Mini\Foundation\Console\UpCommand;
use Mini\Foundation\Console\DownCommand;
use Mini\Foundation\Console\ServeCommand;
//use Mini\Foundation\Console\OptimizeCommand;
use Mini\Foundation\Console\RouteListCommand;
use Mini\Foundation\Console\KeyGenerateCommand;
use Mini\Foundation\Console\ViewClearCommand;

use Mini\Support\ServiceProvider;


class ForgeServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * The commands to be registered.
	 *
	 * @var array
	 */
	protected $commands = array(
		'Down'			=> 'command.down',
		'KeyGenerate'	=> 'command.key.generate',
		//'Optimize'		=> 'command.optimize',
		'RouteList'		=> 'command.route.list',
		'Serve'			=> 'command.serve',
		'Up'			=> 'command.up',
		'ViewClear'		=> 'command.view.clear'
	);

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		foreach (array_keys($this->commands) as $command) {
			$method = "register{$command}Command";

			call_user_func(array($this, $method));
		}

		$this->commands(array_values($this->commands));
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerDownCommand()
	{
		$this->app->singleton('command.down', function ()
		{
			return new DownCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerKeyGenerateCommand()
	{
		$this->app->singleton('command.key.generate', function ($app)
		{
			return new KeyGenerateCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerOptimizeCommand()
	{
		$this->app->singleton('command.optimize', function ($app)
		{
			return new OptimizeCommand($app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteListCommand()
	{
		$this->app->singleton('command.route.list', function ($app)
		{
			return new RouteListCommand($app['router']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerServeCommand()
	{
		$this->app->singleton('command.serve', function ()
		{
			return new ServeCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerUpCommand()
	{
		$this->app->singleton('command.up', function ()
		{
			return new UpCommand;
		});
	}

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerViewClearCommand()
    {
        $this->app->singleton('command.view.clear', function ($app) {
            return new ViewClearCommand($app['files']);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array_values($this->commands);
	}
}
