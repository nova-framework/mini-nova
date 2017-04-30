<?php

namespace App\Controllers;

use App\Core\Controller;

use Mini\View\View;

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

}
