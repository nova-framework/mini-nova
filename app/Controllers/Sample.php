<?php

namespace App\Controllers;

use App\Core\BaseController;

use Mini\Routing\RouteCompiler;
use Mini\Support\Facades\Input;
use Mini\Support\Facades\Paginator;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Route;
use Mini\Support\Facades\Session;
use Mini\Support\Facades\View;
use Mini\Support\Arr;
use Mini\Support\Str;

use Closure;


class Sample extends BaseController
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
        return $this->view()
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

        Session::forget('test');

        //
        $content = '<pre>' .htmlentities(var_export($data, true)) .'</pre>';

        return View::make('Default')
            ->shares('title', 'Session')
            ->with('content', $content);
    }

    public function redirect()
    {
        Session::set('test', 'This is a test!');

        return Redirect::to('sample/session');
    }

    public function pagination()
    {
        // Populate the items.
        $items = array_map(function ($value)
        {
            $data = array(
                'name' => 'Blog post #' .$value,
                'url'  => 'posts/' .$value,
                'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi bibendum viverra aliquet. Cras sed auctor erat. Curabitur lobortis lacinia risus, et imperdiet dolor vehicula ac. Nullam venenatis lectus non nisl molestie iaculis. Pellentesque eleifend porta arcu et efficitur. Praesent pulvinar non nulla vitae consectetur. Curabitur a odio nec neque euismod luctus. Curabitur euismod felis sed lacus tempor pharetra.',
            );

            return $data;

        }, range(1, 100));

        //
        if (Input::get('mode', 'default') == 'simple') {
            $defaultMode = false;
        } else {
            $defaultMode = true;
        }

        //
        $page = Input::get('offset', 1);

        if (($page > count($items)) || ($page < 1)) {
            $page = 1;
        }

        //
        $perPage = 5;

        if ($defaultMode) {
            // We use the Standard Pagination.
            $offset = ($page * $perPage) - $perPage;

            $slices = array_slice($items, $offset, $perPage);

            $posts = Paginator::make($slices, count($items), $perPage);
        } else {
            // We use the Simple Pagination.
            $offset = ($page - 1) * $perPage;

            $slices = array_slice($items, $offset, $perPage + 1);

            $posts = Paginator::make($slices, $perPage);
        }

        //
        $posts->appends(array(
            'mode' => $defaultMode ? 'default' : 'simple',
        ));

        $content = $posts->links();

        foreach ($posts->getItems() as $post) {
            $content .= '<h4><a href="/' .$post['url'] .'"><strong>' .$post['name'] .'</strong></a></h4>';

            $content .= '<p style="text-align: justify">' .$post['body'] .'</p><br>';
        }

        $content .= $posts->links();

        return View::make('Default')
            ->shares('title', 'Pagination')
            ->with('content', $content);
    }
}

