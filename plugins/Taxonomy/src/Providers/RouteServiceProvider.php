<?php

namespace Taxonomy\Providers;

use Mini\Routing\Router;
use Mini\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Mini\Support\Facades\Cache;

use Taxonomy\Models\Vocabulary;


class RouteServiceProvider extends ServiceProvider
{
	/**
	 * The controller namespace for the module.
	 *
	 * @var string|null
	 */
	protected $namespace = 'Taxonomy\Controllers';


	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Mini\Routing\Router  $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		parent::boot($router);

		// Setup the dynamic Routes for Vocabularies.
		$vocabularies = Cache::remember('taxonomy_routed_vocabularies', 1440, function()
		{
			return Vocabulary::all();
		});

		$container = $this->app;

		foreach($vocabularies as $vocabulary) {
			$slug = $vocabulary->slug;

			$router->get("$slug/{term?}", array('middleware' => 'web', 'uses' => function ($term = null) use ($container, $slug)
			{
				$parameters = array_filter(array($slug, $term), function($value)
				{
					return ! empty($value);
				});

				$controller = $container->make('Taxonomy\Controllers\Handler');

				return $controller->callAction('show', $parameters);
			}));
		}
	}

	/**
	 * Define the routes for the module.
	 *
	 * @param  \Illuminate\Routing\Router $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(array('middleware' => 'web', 'namespace' => $this->namespace), function($router)
		{
			require plugin_path('Taxonomy', 'Routes.php');
		});
	}
}
