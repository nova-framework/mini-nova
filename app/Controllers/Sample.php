<?php

namespace App\Controllers;

use App\Core\Controller;

use Mini\Routing\RouteCompiler;
use Mini\Support\Facades\Route;
use Mini\Support\Facades\View;
use Mini\Support\Arr;
use Mini\Support\Str;

use Closure;


class Sample extends Controller
{

    public function __construct()
    {
        $this->middleware('@testing', array(
            'only' => 'index'
        ));
    }

    public function testing($request, Closure $next)
    {
        echo sprintf('<pre style="margin: 10px;">BEFORE, on the [%s] Middleware!</pre>', str_replace('::', '@', __METHOD__));

        return $next($request);
    }

    public function index($name = null, $slug = null)
    {
        return View::make('Sample/Index')
            ->shares('title', 'Sample')
            ->with('name', $name)
            ->with('slug', $slug);
    }

    public function store()
    {
        //
    }

    public function routes()
    {
        $routes = Route::getRoutes();

        $results = array();

        foreach($routes->getRoutes() as $route) {
            $options = array_filter($route, function ($value)
            {
                return is_string($value);

            }, ARRAY_FILTER_USE_KEY);

            if ($options['uses'] instanceof Closure) {
                $options['uses'] = 'Closure';
            }

            $patterns = array_merge(Route::patterns(), Arr::get($options, 'where', array()));

            $options['where'] = $patterns;

            //
            $uri = $options['uri'];

            if (preg_match('/\{([\w\?]+?)\}/', $uri) === 1) {
                $options['regex'] = RouteCompiler::compile($uri, $patterns);
            } else {
                $options['regex'] = RouteCompiler::computeRegexp($uri);
            }

            ksort($options);

            $results[] = $options;
        }

        //
        $content = '';

        foreach($results as $route) {
            $content .= '<pre>' .htmlentities(var_export($route, true)) .'</pre>';
        }

        return View::make('Default')
            ->shares('title', 'Routes')
            ->with('content', $content);
    }
}
