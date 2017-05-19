<?php

namespace Mini\View;

use Mini\View\Engines\EngineResolver;
use Mini\View\Engines\PhpEngine;
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
		$this->registerEngineResolver();

		$this->registerFactory();
	}

	/**
	 * Register the engine resolver instance.
	 *
	 * @return void
	 */
	public function registerEngineResolver()
	{
		$this->app->bindShared('view.engine.resolver', function($app)
		{
			$resolver = new EngineResolver();

			foreach (array('php', 'template') as $engine) {
				$method = 'register' .ucfirst($engine) .'Engine';

				call_user_func(array($this, $method), $resolver);
			}

			return $resolver;
		});
	}

	/**
	 * Register the PHP engine implementation.
	 *
	 * @param  \Nova\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerPhpEngine($resolver)
	{
		$resolver->register('php', function()
		{
			return new PhpEngine();
		});
	}

	/**
	 * Register the Template engine implementation.
	 *
	 * @param  \Nova\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerTemplateEngine($resolver)
	{
		$app = $this->app;

		$app->bindShared('template', function($app)
		{
			$cachePath = $app['config']['view.compiled'];

			return new Template($app['files'], $cachePath);
		});

		$resolver->register('template', function() use ($app)
		{
			return new TemplateEngine($app['template'], $app['files']);
		});
	}

	/**
	 * Register the View Factory.
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
		return array('view', 'view.section', 'template');
	}
}
