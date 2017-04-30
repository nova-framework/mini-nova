<?php

namespace App\Controllers;

use App\Core\Controller;

use Mini\View\View;

use Closure;


class Sample extends Controller
{

    public function __construct()
    {
        $this->middleware('@testing');
    }

    public function testing($request, Closure $next)
    {
        echo '<pre style="margin: 10px;">Hello from the Controller Middleware!</pre>';

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
