<?php

namespace App\Controllers;

use App\Core\Controller;

use Mini\Routing\RouteCompiler;
use Mini\Support\Facades\Route;
use Mini\Support\Facades\View;
use Mini\Support\Arr;

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

        foreach($routes->getRoutes() as $method => $items) {
            foreach($items as $route => $action) {
                $callable = $action['uses'];

                if ($callable instanceof Closure) {
                    $action['uses'] = 'closure: ' .spl_object_hash($callable);
                }

                $options = array_filter($action, function ($value)
                {
                    return is_string($value);

                }, ARRAY_FILTER_USE_KEY);

                $options['uri'] = $route;

                //
                $hash = sha1(serialize($options));

                $methods = array($method);

                if (array_key_exists($hash, $results)) {
                    $result = $results[$hash];

                    $methods = array_merge(Arr::get($result, 'methods', array()), $methods);
                }

                $options['methods'] = $methods;

                //
                $patterns = array_merge(Route::patterns(), Arr::get($options, 'where', array()));

                if (preg_match('/\{([\w\?]+?)\}/', $route) === 1) {
                    $options['regex'] = RouteCompiler::compile($route, $patterns);
                } else {
                    $options['regex'] = RouteCompiler::computeRegexp($route);
                }

                $options['where'] = $patterns;

                ksort($options);

                $results[$hash] = $options;
            }
        }

        //
        $content = '';

        foreach($results as $hash => $route) {
            $content .= '<pre>' .htmlentities(var_export($route, true)) .'</pre>';
        }

        return View::make('Default')
            ->shares('title', 'Routes')
            ->with('content', $content);
    }
}
