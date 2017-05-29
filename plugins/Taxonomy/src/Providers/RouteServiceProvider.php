<?php

namespace Taxonomy\Providers;

use Mini\Database\QueryException;
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

		//
		// Setup the dynamic routes for Vocabularies.

		try {
			$slugs = Cache::remember('taxonomy_routed_vocabularies', 1440, function ()
			{
				return Vocabulary::lists('slug');
			});
		}
		catch (QueryException $e) {
			return;
		}

		$wheres = array(
			'group'	=> '(' .implode('|', $slugs) .')',
			'slug'	=> '(.*)',
		);

		$router->group(array('middleware' => 'web', 'namespace' => $this->namespace), function($router) use ($wheres)
		{
			$router->get("{group}/{slug?}", array('uses' => 'Handler@handle', 'wheres' => $wheres));
		});
	}
}
