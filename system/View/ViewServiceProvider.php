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
		$this->registerEngines();

		$this->registerFactory();
	}

	/**
	 * Register the engine resolver instance.
	 *
	 * @return void
	 */
	public function registerEngines()
	{
		$this->app->bindShared('template', function($app)
		{
			$cachePath = $app['config']['view.compiled'];

			return new Template($app['files'], $cachePath);
		});

		$this->app->bindShared('view.engine.resolver', function($app)
		{
			$resolver = new EngineResolver();

			foreach (array('default', 'template') as $engine) {
				$method = 'register' .ucfirst($engine) .'Engine';

				call_user_func(array($this, $method), $resolver);
			}

			return $resolver;
		});
	}

	/**
	 * Register the PHP Engine implementation.
	 *
	 * @param  \Mini\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerDefaultEngine($resolver)
	{
		$resolver->register('default', function()
		{
			return new DefaultEngine();
		});
	}

	/**
	 * Register the Template Engine implementation.
	 *
	 * @param  \Mini\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerTemplateEngine($resolver)
	{
		$app = $this->app;

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
		return array('view', 'template');
	}
}
