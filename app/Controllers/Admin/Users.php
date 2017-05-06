<?php

namespace App\Controllers\Admin;

use Mini\Support\Facades\View;

use App\Core\Controller;
use App\Models\Users as Model;


class Users extends Controller
{
    protected $model;


    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        $users = $this->model->all();

        $content = '<pre>' .htmlentities(var_export($users, true)) .'</pre>';

        return View::make('Default')
            ->shares('title', 'Users')
            ->with('content', $content);
    }
}

