<?php

namespace App\Controllers;

use App\Core\Controller;

use Mini\View\View;


class Sample extends Controller
{

    public function index($name = null, $slug = null)
    {
        return View::make('Sample/Index')
            ->shares('title', 'Sample')
            ->with('name', $name)
            ->with('slug', $slug);
    }

}
