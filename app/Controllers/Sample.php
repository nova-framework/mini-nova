<?php

namespace App\Controllers;

use App\Core\Controller;

use Mini\Routing\RouteCompiler;
use Mini\Support\Facades\Route;
use Mini\Support\Facades\Session;
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
        $routeCollection = Route::getRoutes();

        //
        $content = '';

        foreach($routeCollection->getRoutes() as $route) {
            $route->compile();

            $action = array_filter($route->getAction(), function ($value)
            {
                return is_string($value);

            }, ARRAY_FILTER_USE_KEY);

            if ($action['uses'] instanceof Closure) {
                $action['uses'] = 'Closure';
            }

            $result = array(
                'methods' => $route->getMethods(),
                'uri'     => $route->getUri(),
                'action'  => $action,
                'wheres'  => $route->getWheres(),
                'regex'   => $route->getRegex(),
            );

            $content .= '<pre>' .htmlentities(var_export($result, true)) .'</pre>';
        }

        return View::make('Default')
            ->shares('title', 'Routes')
            ->with('content', $content);
    }

    public function session()
    {
        $data = Session::all();

        $content = '<pre>' .htmlentities(var_export($data, true)) .'</pre>';

        return View::make('Default')
            ->shares('title', 'Session')
            ->with('content', $content);
    }
}

