<?php

namespace Mini\View;

use Mini\View\Engines\Engine as DefaultEngine;
use Mini\View\Engines\EngineResolver;
use Mini\View\Engines\TemplateEngine;
use Mini\View\Factory;
use Mini\View\Template;
use Mini\Support\ServiceProvider;


class ViewServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the Provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the Service Provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerTemplate();

		$this->registerEngineResolver();

		$this->registerFactory();
	}

	/**
	 * Register the Template compiler instance.
	 *
	 * @return void
	 */
	public function registerTemplate()
	{
		$this->app->bindShared('template', function($app)
		{
			$cachePath = $app['config']['view.compiled'];

			return new Template($app['files'], $cachePath);
		});
	}

	/**
	 * Register the Engine Resolver instance.
	 *
	 * @return void
	 */
	public function registerEngineResolver()
	{
		$this->app->bindShared('view.engine.resolver', function($app)
		{
			$resolver = new EngineResolver();

			// Register the Default Engine instance.
			$resolver->register('default', function()
			{
				return new DefaultEngine();
			});

			// Register the Template Engine instance.
			$resolver->register('template', function() use ($app)
			{
				return new TemplateEngine($app['template'], $app['files']);
			});

			return $resolver;
		});
	}

	/**
	 * Register the View Factory instance.
	 *
	 * @return void
	 */
	public function registerFactory()
	{
		$this->app->bindShared('view', function($app)
		{
			$resolver = $app['view.engine.resolver'];

			$factory = new Factory($resolver, $app['files']);

			$factory->share('app', $app);

			return $factory;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('view', 'template');
	}
}
